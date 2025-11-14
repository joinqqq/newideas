// js/booking.js
class BookingPage {
    constructor() {
        this.selectedDate = null;
        this.selectedTime = null;
        this.selectedDuration = 1;
        this.selectedComputer = null;
        this.hourlyRate = 500; // Цена за час

        this.currentMonth = new Date().getMonth();
        this.currentYear = new Date().getFullYear();

        this.init();
    }

    init() {
        this.generateCalendar();
        this.generateTimeSlots();
        this.generateComputers();
        this.setupEventListeners();
        this.updateBookingSummary();
    }

    setupEventListeners() {
        // Навигация календаря
        document.querySelector('.prev-month').addEventListener('click', () => {
            this.changeMonth(-1);
        });

        document.querySelector('.next-month').addEventListener('click', () => {
            this.changeMonth(1);
        });

        // Выбор продолжительности
        document.querySelectorAll('.duration-option:not(.custom-duration)').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.selectDuration(e.target);
            });
        });

        // Кастомная продолжительность
        const customInput = document.querySelector('.custom-duration input');
        customInput.addEventListener('input', (e) => {
            const hours = parseInt(e.target.value);
            if (hours >= 1 && hours <= 12) {
                this.selectedDuration = hours;
                this.updateDurationSelection();
                this.updateBookingSummary();
            }
        });

        // Фильтр зон
        document.getElementById('zoneFilter').addEventListener('change', (e) => {
            this.filterComputers(e.target.value);
        });

        // Закрытие деталей компьютера
        document.querySelector('.btn-close-details').addEventListener('click', () => {
            this.hideComputerDetails();
        });

        // Подтверждение брони
        document.getElementById('confirmBooking').addEventListener('click', () => {
            this.confirmBooking();
        });
    }

    generateCalendar() {
        const calendar = document.getElementById('calendar');
        const monthNames = [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ];

        // Обновляем заголовок
        document.getElementById('currentMonth').textContent =
            `${monthNames[this.currentMonth]} ${this.currentYear}`;

        // Получаем первый день месяца и количество дней
        const firstDay = new Date(this.currentYear, this.currentMonth, 1);
        const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        // Корректируем день недели (воскресенье = 0)
        const adjustedStartingDay = startingDay === 0 ? 6 : startingDay - 1;

        calendar.innerHTML = '';

        // Добавляем дни недели
        const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        weekDays.forEach(day => {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day week-day';
            dayElement.textContent = day;
            calendar.appendChild(dayElement);
        });

        // Добавляем пустые ячейки для начала месяца
        for (let i = 0; i < adjustedStartingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day other-month';
            calendar.appendChild(emptyDay);
        }

        // Добавляем дни месяца
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            dayElement.dataset.date = `${this.currentYear}-${this.currentMonth + 1}-${day}`;

            // Проверяем сегодняшний день
            if (day === today.getDate() &&
                this.currentMonth === today.getMonth() &&
                this.currentYear === today.getFullYear()) {
                dayElement.classList.add('today');
            }

            // Отключаем прошедшие дни
            const currentDate = new Date(this.currentYear, this.currentMonth, day);
            if (currentDate < today) {
                dayElement.classList.add('disabled');
            } else {
                dayElement.addEventListener('click', () => this.selectDate(dayElement));
            }

            calendar.appendChild(dayElement);
        }
    }

    changeMonth(direction) {
        this.currentMonth += direction;

        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        } else if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        }

        this.generateCalendar();
    }

    selectDate(dayElement) {
        // Снимаем выделение с предыдущей даты
        document.querySelectorAll('.calendar-day.selected').forEach(day => {
            day.classList.remove('selected');
        });

        // Выделяем новую дату
        dayElement.classList.add('selected');
        this.selectedDate = dayElement.dataset.date;

        // Форматируем дату для отображения
        const date = new Date(this.selectedDate);
        const formattedDate = date.toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'long',
            weekday: 'long'
        });

        this.updateBookingSummary();

        // Обновляем доступное время
        this.generateTimeSlots();
    }

    generateTimeSlots() {
        const timeSlots = document.getElementById('timeSlots');
        timeSlots.innerHTML = '';

        const slots = [];
        const startHour = 8; // 8:00
        const endHour = 2;   // 2:00 следующего дня

        // Генерируем слоты с 8:00 до 00:00
        for (let hour = startHour; hour < 24; hour++) {
            slots.push(`${hour.toString().padStart(2, '0')}:00`);
            slots.push(`${hour.toString().padStart(2, '0')}:30`);
        }

        // Добавляем слоты после полуночи до 2:00
        for (let hour = 0; hour < endHour; hour++) {
            slots.push(`${hour.toString().padStart(2, '0')}:00`);
            slots.push(`${hour.toString().padStart(2, '0')}:30`);
        }

        slots.forEach(slot => {
            const slotElement = document.createElement('div');
            slotElement.className = 'time-slot';
            slotElement.textContent = slot;
            slotElement.dataset.time = slot;

            // Случайным образом делаем некоторые слоты недоступными
            const isAvailable = Math.random() > 0.2; // 80% слотов доступны

            if (!isAvailable) {
                slotElement.classList.add('disabled');
            } else {
                slotElement.addEventListener('click', () => this.selectTime(slotElement));
            }

            timeSlots.appendChild(slotElement);
        });
    }

    selectTime(slotElement) {
        // Снимаем выделение с предыдущего времени
        document.querySelectorAll('.time-slot.selected').forEach(slot => {
            slot.classList.remove('selected');
        });

        // Выделяем новое время
        slotElement.classList.add('selected');
        this.selectedTime = slotElement.dataset.time;

        this.updateBookingSummary();
        this.checkBookingReady();
    }

    selectDuration(button) {
        // Снимаем выделение с предыдущей продолжительности
        document.querySelectorAll('.duration-option').forEach(btn => {
            btn.classList.remove('active');
        });

        // Выделяем новую продолжительность
        button.classList.add('active');
        this.selectedDuration = parseInt(button.dataset.hours);

        this.updateBookingSummary();
    }

    updateDurationSelection() {
        document.querySelectorAll('.duration-option').forEach(btn => {
            btn.classList.remove('active');
        });
    }

    generateComputers() {
        const grid = document.getElementById('computersGrid');
        grid.innerHTML = '';

        const zones = ['gaming', 'vip', 'streaming'];
        const specs = {
            'gaming': 'i7/RTX 4070',
            'vip': 'i9/RTX 4090',
            'streaming': 'i9/RTX 4080'
        };

        for (let i = 1; i <= 18; i++) {
            const zone = zones[Math.floor(Math.random() * zones.length)];
            const isOccupied = Math.random() < 0.3; // 30% компьютеров заняты
            const isUnavailable = Math.random() < 0.1; // 10% компьютеров недоступны

            const computer = document.createElement('div');
            computer.className = `computer-item ${isOccupied ? 'occupied' : ''} ${isUnavailable ? 'unavailable' : ''}`;
            computer.dataset.computerId = i;
            computer.dataset.zone = zone;

            if (!isOccupied && !isUnavailable) {
                computer.addEventListener('click', () => this.selectComputer(computer));
            }

            computer.innerHTML = `
                <div class="computer-number">#${zone.toUpperCase()}${i}</div>
                <div class="computer-spec">${specs[zone]}</div>
                ${isOccupied ? '<div class="computer-badge">Занят</div>' : ''}
                ${isUnavailable ? '<div class="computer-badge">Ремонт</div>' : ''}
            `;

            grid.appendChild(computer);
        }
    }

    filterComputers(zone) {
        const computers = document.querySelectorAll('.computer-item');

        computers.forEach(computer => {
            if (zone === 'all' || computer.dataset.zone === zone) {
                computer.style.display = 'flex';
            } else {
                computer.style.display = 'none';
            }
        });
    }

    selectComputer(computerElement) {
        // Снимаем выделение с предыдущего компьютера
        document.querySelectorAll('.computer-item.selected').forEach(comp => {
            comp.classList.remove('selected');
        });

        // Выделяем новый компьютер
        computerElement.classList.add('selected');
        this.selectedComputer = computerElement.dataset.computerId;

        // Показываем детали компьютера
        this.showComputerDetails(computerElement);

        this.updateBookingSummary();
        this.checkBookingReady();
    }

    showComputerDetails(computerElement) {
        const details = document.getElementById('computerDetails');
        const pcNumber = computerElement.querySelector('.computer-number').textContent;

        document.getElementById('selectedPcNumber').textContent = pcNumber;
        details.style.display = 'block';
    }

    hideComputerDetails() {
        document.getElementById('computerDetails').style.display = 'none';
    }

    updateBookingSummary() {
        // Обновляем дату
        if (this.selectedDate) {
            const date = new Date(this.selectedDate);
            const formattedDate = date.toLocaleDateString('ru-RU', {
                day: 'numeric',
                month: 'long',
                weekday: 'long'
            });
            document.getElementById('summaryDate').textContent = formattedDate;
        }

        // Обновляем время
        if (this.selectedTime) {
            document.getElementById('summaryTime').textContent = this.selectedTime;
        }

        // Обновляем продолжительность
        if (this.selectedDuration) {
            document.getElementById('summaryDuration').textContent =
                `${this.selectedDuration} ${this.getHoursText(this.selectedDuration)}`;
        }

        // Обновляем компьютер
        if (this.selectedComputer) {
            const computerElement = document.querySelector(`[data-computer-id="${this.selectedComputer}"]`);
            const pcNumber = computerElement.querySelector('.computer-number').textContent;
            document.getElementById('summaryComputer').textContent = pcNumber;
        }

        // Обновляем итоговую стоимость
        const total = this.selectedDuration * this.hourlyRate;
        document.getElementById('summaryTotal').textContent = `${total} ₽`;
    }

    getHoursText(hours) {
        if (hours === 1) return 'час';
        if (hours >= 2 && hours <= 4) return 'часа';
        return 'часов';
    }

    checkBookingReady() {
        const button = document.getElementById('confirmBooking');
        const isReady = this.selectedDate && this.selectedTime && this.selectedComputer;

        button.disabled = !isReady;
    }

    confirmBooking() {
    if (!this.selectedDate || !this.selectedTime || !this.selectedComputer) {
        this.showNotification('Пожалуйста, заполните все поля', 'error');
        return;
    }

    // Заполняем скрытые поля формы
    document.getElementById('inputBookingDate').value = this.selectedDate;
    document.getElementById('inputStartTime').value = this.selectedTime;
    document.getElementById('inputDuration').value = this.selectedDuration;
    document.getElementById('inputComputerId').value = this.selectedComputer;
    document.getElementById('inputTotalPrice').value = this.selectedDuration * this.hourlyRate;

    // Находим форму и отправляем
    const form = document.getElementById('bookingForm');
    if (!form) {
        console.error("❌ Форма не найдена!");
        alert("Ошибка: форма не найдена");
        return;
    }

    console.log("✅ Отправляем форму с данными:", {
        date: this.selectedDate,
        time: this.selectedTime,
        duration: this.selectedDuration,
        computer: this.selectedComputer
    });

    form.submit();
}


    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${this.getNotificationIcon(type)}</span>
                <span>${message}</span>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 2rem;
            background: ${this.getNotificationColor(type)};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            z-index: 1001;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    getNotificationIcon(type) {
        const icons = {
            'success': '✅',
            'error': '❌',
            'info': 'ℹ️',
            'warning': '⚠️'
        };
        return icons[type] || 'ℹ️';
    }

    getNotificationColor(type) {
        const colors = {
            'success': '#10b981',
            'error': '#ef4444',
            'info': '#3b82f6',
            'warning': '#f59e0b'
        };
        return colors[type] || '#3b82f6';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new BookingPage();
});

// Добавляем стили для анимации уведомлений
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);