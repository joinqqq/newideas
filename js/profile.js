// js/profile.js
class ProfilePage {
    constructor() {
        this.currentTab = 'bookings';
        this.isEditing = false;
        
        this.init();
    }

    init() {
        this.setupTabNavigation();
        this.setupEventListeners();
        this.loadUserData();
    }

    setupTabNavigation() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to current button and content
                button.classList.add('active');
                document.getElementById(`${tabId}Tab`).classList.add('active');
                
                this.currentTab = tabId;
            });
        });
    }

    setupEventListeners() {
        // Edit profile button
        document.getElementById('editProfile').addEventListener('click', () => {
            this.toggleEditMode();
        });

        document.getElementById('enableEdit').addEventListener('click', () => {
            this.toggleEditMode();
        });

        // Cancel booking buttons
        document.querySelectorAll('.btn-cancel').forEach(button => {
            button.addEventListener('click', (e) => {
                this.cancelBooking(e.target.closest('.booking-card'));
            });
        });

        // Bonus exchange buttons
        document.querySelectorAll('.bonus-option .btn').forEach(button => {
            button.addEventListener('click', (e) => {
                this.exchangeBonus(e.target.closest('.bonus-option'));
            });
        });

        // Repeat booking buttons
        document.querySelectorAll('.btn-text').forEach(button => {
            button.addEventListener('click', (e) => {
                if (e.target.textContent.includes('Повторить')) {
                    this.repeatBooking(e.target.closest('.history-item'));
                } else if (e.target.textContent.includes('Отзыв')) {
                    this.leaveReview(e.target.closest('.history-item'));
                }
            });
        });
    }

    loadUserData() {
        // В реальном приложении здесь был бы запрос к API
        const userData = {
            name: 'Алексей',
            surname: 'Геймеров',
            email: 'alexey.gamer@email.ru',
            phone: '+7 (999) 123-45-67',
            bookings: 12,
            hours: 47,
            bonuses: 1240
        };

        // Можно использовать данные для обновления интерфейса
        console.log('User data loaded:', userData);
    }

    toggleEditMode() {
        this.isEditing = !this.isEditing;
        const inputs = document.querySelectorAll('.settings-form input');
        
        inputs.forEach(input => {
            input.readOnly = !this.isEditing;
            if (this.isEditing) {
                input.style.background = 'var(--white)';
                input.style.color = 'var(--dark)';
            } else {
                input.style.background = '#f8f9fa';
                input.style.color = 'var(--gray)';
            }
        });

        const editButton = document.getElementById('enableEdit');
        if (this.isEditing) {
            editButton.textContent = '💾 Сохранить';
            editButton.classList.remove('btn-outline');
            editButton.classList.add('btn-primary');
        } else {
            editButton.textContent = '✏️ Редактировать';
            editButton.classList.remove('btn-primary');
            editButton.classList.add('btn-outline');
            this.saveProfile();
        }
    }

    saveProfile() {
        // Имитация сохранения профиля
        this.showNotification('Профиль успешно обновлен', 'success');
        
        // В реальном приложении здесь был бы запрос к API
        setTimeout(() => {
            console.log('Profile saved');
        }, 1000);
    }

    cancelBooking(bookingCard) {
        const bookingTitle = bookingCard.querySelector('h3').textContent;
        
        if (confirm(`Вы уверены, что хотите отменить бронирование в "${bookingTitle}"?`)) {
            // Имитация отмены бронирования
            bookingCard.style.opacity = '0.5';
            bookingCard.style.pointerEvents = 'none';
            
            this.showNotification('Бронирование отменено', 'success');
            
            // В реальном приложении здесь был бы запрос к API
            setTimeout(() => {
                bookingCard.remove();
                this.updateStats();
            }, 1500);
        }
    }

    exchangeBonus(bonusOption) {
        const bonusName = bonusOption.querySelector('h4').textContent;
        const bonusCost = bonusOption.querySelector('p').textContent;
        
        if (confirm(`Обменять ${bonusCost} на "${bonusName}"?`)) {
            this.showNotification('Бонусы успешно обменяны!', 'success');
            
            // В реальном приложении здесь был бы запрос к API
            setTimeout(() => {
                console.log(`Bonus exchanged: ${bonusName}`);
            }, 1000);
        }
    }

    repeatBooking(historyItem) {
        const clubName = historyItem.querySelector('h4').textContent;
        this.showNotification(`Создаем новую бронь в "${clubName}"...`, 'info');
        
        // В реальном приложении здесь был бы переход на страницу бронирования
        setTimeout(() => {
            window.location.href = 'booking.html';
        }, 1500);
    }

    leaveReview(historyItem) {
        const clubName = historyItem.querySelector('h4').textContent;
        
        // В реальном приложении здесь было бы модальное окно для отзыва
        const review = prompt(`Оставьте отзыв о "${clubName}":`);
        
        if (review) {
            this.showNotification('Спасибо за ваш отзыв!', 'success');
            
            // В реальном приложении здесь был бы запрос к API
            setTimeout(() => {
                console.log('Review submitted:', review);
            }, 1000);
        }
    }

    updateStats() {
        // Обновление статистики после отмены брони
        const stats = document.querySelectorAll('.stat-number');
        if (stats[0]) {
            let currentBookings = parseInt(stats[0].textContent);
            stats[0].textContent = Math.max(0, currentBookings - 1);
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
    new ProfilePage();
});