// js/success.js
class SuccessPage {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadBookingData();
    }

    setupEventListeners() {
        // Скачивание билета
        document.getElementById('downloadTicket').addEventListener('click', () => {
            this.downloadTicket();
        });

        // Поделиться бронированием
        document.getElementById('shareBooking').addEventListener('click', () => {
            this.shareBooking();
        });
    }

    loadBookingData() {
        // Загрузка данных о текущем бронировании
        const bookingData = JSON.parse(localStorage.getItem('currentBooking') || '{}');
        
        if (Object.keys(bookingData).length > 0) {
            this.updateBookingDetails(bookingData);
        }
    }

    updateBookingDetails(bookingData) {
        // В реальном приложении здесь были бы реальные данные
        console.log('Booking data:', bookingData);
        
        // Можно обновить детали на странице на основе bookingData
    }

    downloadTicket() {
        this.showNotification('Подготавливаем ваш билет...', 'info');
        
        // Имитация подготовки билета
        setTimeout(() => {
            this.showNotification('Билет готов к скачиванию!', 'success');
            
            // В реальном приложении здесь был бы PDF билет
            const link = document.createElement('a');
            link.href = '#';
            link.download = 'cyberbook-ticket.pdf';
            link.click();
        }, 2000);
    }

    shareBooking() {
        if (navigator.share) {
            navigator.share({
                title: 'Мое бронирование в CyberBook',
                text: 'Я забронировал место в киберспортивном клубе через CyberBook!',
                url: window.location.href
            })
            .then(() => this.showNotification('Бронирование успешно отправлено!', 'success'))
            .catch(() => this.showNotification('Не удалось поделиться', 'error'));
        } else {
            // Fallback для браузеров без поддержки Web Share API
            navigator.clipboard.writeText(window.location.href)
                .then(() => this.showNotification('Ссылка скопирована в буфер обмена!', 'success'))
                .catch(() => this.showNotification('Не удалось скопировать ссылку', 'error'));
        }
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
    new SuccessPage();
});