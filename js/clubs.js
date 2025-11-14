// js/clubs.js
class ClubsPage {
    constructor() {
        this.clubs = [];
        this.filteredClubs = [];
        this.currentPage = 1;
        this.clubsPerPage = 6;
        this.filters = {
            search: '',
            city: '',
            rating: '',
            price: '',
            services: ''
        };
        
        this.init();
    }

    init() {
        this.loadClubs();
        this.setupEventListeners();
        this.setupFilters();
    }

    async loadClubs() {
        // Имитация загрузки данных
        this.showLoading();
        
        // В реальном приложении здесь был бы fetch запрос
        setTimeout(() => {
            this.clubs = this.generateMockClubs();
            this.applyFilters();
            this.hideLoading();
        }, 1500);
    }

    generateMockClubs() {
        const cities = ['moscow', 'spb', 'kazan', 'ekb'];
        const cityNames = {
            'moscow': 'Москва',
            'spb': 'Санкт-Петербург',
            'kazan': 'Казань',
            'ekb': 'Екатеринбург'
        };

        const clubs = [];
        const names = [
            'Cyber Arena Pro', 'Game Hub Elite', 'Neon Nexus', 'Quantum Gaming',
            'Pixel Palace', 'Matrix Club', 'Nexus Arena', 'Digital Domain',
            'Tech Temple', 'Virtual Venture', 'Code Castle', 'Data Dungeon',
            'Byte Base', 'Circuit Central', 'Pixel Paradise', 'Cyber Core'
        ];

        const features = ['vr', 'tournaments', 'streaming', 'food', 'console', 'vip'];

        for (let i = 0; i < 24; i++) {
            const city = cities[Math.floor(Math.random() * cities.length)];
            const rating = (4 + Math.random() * 1).toFixed(1);
            const price = Math.floor(200 + Math.random() * 800);
            
            clubs.push({
                id: i + 1,
                name: names[Math.floor(Math.random() * names.length)] + ' ' + (i % 4 + 1),
                city: city,
                cityName: cityNames[city],
                address: `ул. Игровая, ${Math.floor(Math.random() * 100) + 1}`,
                rating: parseFloat(rating),
                price: price,
                reviews: Math.floor(Math.random() * 200) + 20,
                features: features.slice(0, Math.floor(Math.random() * 3) + 2),
                is24h: Math.random() > 0.3,
                hasFood: Math.random() > 0.5,
                computers: Math.floor(Math.random() * 50) + 10,
                image: `images/club${(i % 6) + 1}.jpg`
            });
        }

        return clubs;
    }

    setupEventListeners() {
        // Поиск
        document.getElementById('searchInput').addEventListener('input', (e) => {
            this.filters.search = e.target.value;
            this.applyFilters();
        });

        document.getElementById('clearSearch').addEventListener('click', () => {
            document.getElementById('searchInput').value = '';
            this.filters.search = '';
            this.applyFilters();
        });

        // Фильтры
        document.getElementById('cityFilter').addEventListener('change', (e) => {
            this.filters.city = e.target.value;
            this.applyFilters();
        });

        document.getElementById('ratingFilter').addEventListener('change', (e) => {
            this.filters.rating = e.target.value;
            this.applyFilters();
        });

        document.getElementById('priceFilter').addEventListener('change', (e) => {
            this.filters.price = e.target.value;
            this.applyFilters();
        });

        document.getElementById('servicesFilter').addEventListener('change', (e) => {
            this.filters.services = e.target.value;
            this.applyFilters();
        });

        // Сброс фильтров
        document.getElementById('resetFilters').addEventListener('click', () => {
            this.resetFilters();
        });

        document.getElementById('resetAllFilters').addEventListener('click', () => {
            this.resetFilters();
        });

        // Сортировка
        document.getElementById('sortSelect').addEventListener('change', (e) => {
            this.sortClubs(e.target.value);
            this.renderClubs();
        });

        // Загрузка еще
        document.getElementById('loadMore').addEventListener('click', () => {
            this.loadMoreClubs();
        });

        // Модальное окно
        document.getElementById('suggestClub').addEventListener('click', () => {
            this.openSuggestionModal();
        });

        document.getElementById('closeModal').addEventListener('click', () => {
            this.closeSuggestionModal();
        });

        document.getElementById('cancelSuggestion').addEventListener('click', () => {
            this.closeSuggestionModal();
        });

        document.getElementById('submitSuggestion').addEventListener('click', () => {
            this.submitSuggestion();
        });

        // Закрытие модального окна по клику вне его
        document.getElementById('suggestionModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('suggestionModal')) {
                this.closeSuggestionModal();
            }
        });
    }

    setupFilters() {
        // Инициализация активных фильтров
        this.updateActiveFilters();
    }

    applyFilters() {
        this.currentPage = 1;
        
        this.filteredClubs = this.clubs.filter(club => {
            // Поиск
            if (this.filters.search) {
                const searchTerm = this.filters.search.toLowerCase();
                const clubText = `${club.name} ${club.address} ${club.cityName}`.toLowerCase();
                if (!clubText.includes(searchTerm)) return false;
            }

            // Город
            if (this.filters.city && club.city !== this.filters.city) return false;

            // Рейтинг
            if (this.filters.rating && club.rating < parseFloat(this.filters.rating)) return false;

            // Цена
            if (this.filters.price) {
                const [min, max] = this.filters.price.split('-').map(val => 
                    val.endsWith('+') ? parseInt(val) : parseInt(val)
                );
                if (max && (club.price < min || club.price > max)) return false;
                if (!max && club.price < min) return false;
            }

            // Услуги
            if (this.filters.services && !club.features.includes(this.filters.services)) return false;

            return true;
        });

        this.updateActiveFilters();
        this.renderClubs();
    }

    sortClubs(sortBy) {
        switch (sortBy) {
            case 'rating':
                this.filteredClubs.sort((a, b) => b.rating - a.rating);
                break;
            case 'price-asc':
                this.filteredClubs.sort((a, b) => a.price - b.price);
                break;
            case 'price-desc':
                this.filteredClubs.sort((a, b) => b.price - a.price);
                break;
            case 'name':
                this.filteredClubs.sort((a, b) => a.name.localeCompare(b.name));
                break;
        }
    }

    renderClubs() {
        const grid = document.getElementById('clubsGrid');
        const countElement = document.getElementById('clubsCount');
        const noResults = document.getElementById('noResults');
        const loadMore = document.getElementById('loadMore');

        // Обновляем счетчик
        countElement.textContent = this.filteredClubs.length;

        // Показываем/скрываем сообщение "нет результатов"
        if (this.filteredClubs.length === 0) {
            noResults.style.display = 'block';
            grid.style.display = 'none';
            loadMore.style.display = 'none';
            return;
        } else {
            noResults.style.display = 'none';
            grid.style.display = 'grid';
        }

        // Определяем какие клубы показывать на текущей странице
        const startIndex = 0;
        const endIndex = this.currentPage * this.clubsPerPage;
        const clubsToShow = this.filteredClubs.slice(startIndex, endIndex);

        // Рендерим клубы
        grid.innerHTML = clubsToShow.map(club => this.createClubCard(club)).join('');

        // Показываем/скрываем кнопку "Загрузить еще"
        if (endIndex >= this.filteredClubs.length) {
            loadMore.style.display = 'none';
        } else {
            loadMore.style.display = 'block';
        }

        // Анимация появления
        this.animateClubCards();
    }

    createClubCard(club) {
        const featuresMap = {
            'vr': 'VR зона',
            'tournaments': 'Турниры',
            'streaming': 'Стриминг',
            'food': 'Еда/напитки',
            'console': 'Консоли',
            'vip': 'VIP зона'
        };

        const featuresHTML = club.features.map(feature => 
            `<span class="feature-tag">${featuresMap[feature]}</span>`
        ).join('');

        return `
            <div class="club-card-enhanced fade-in" data-club-id="${club.id}">
                <div class="club-card-header">
                    ${club.rating >= 4.8 ? '<div class="club-card-badge">Топ</div>' : ''}
                </div>
                <div class="club-card-content">
                    <div class="club-card-title">
                        <div>
                            <h3>${club.name}</h3>
                            <div class="club-rating">
                                ⭐ ${club.rating} <span style="color: var(--gray); font-weight: normal;">(${club.reviews})</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="club-meta-enhanced">
                        <div class="meta-item-enhanced">
                            <span class="icon">📍</span>
                            <span>${club.cityName}</span>
                        </div>
                        <div class="meta-item-enhanced">
                            <span class="icon">🕐</span>
                            <span>${club.is24h ? '24/7' : '10:00-02:00'}</span>
                        </div>
                        <div class="meta-item-enhanced">
                            <span class="icon">💻</span>
                            <span>${club.computers} ПК</span>
                        </div>
                        <div class="meta-item-enhanced">
                            <span class="icon">🍕</span>
                            <span>${club.hasFood ? 'Есть еда' : 'Без еды'}</span>
                        </div>
                    </div>

                    <div class="club-features">
                        ${featuresHTML}
                    </div>

                    <div class="club-card-footer">
                        <div>
                            <div class="club-price">${club.price} ₽</div>
                            <div class="club-price-period">за час</div>
                        </div>
                        <a href="booking.html?club=${club.id}" class="btn btn-primary btn-small">
                            Забронировать
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    animateClubCards() {
        const cards = document.querySelectorAll('.club-card-enhanced');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    updateActiveFilters() {
        const container = document.getElementById('activeFilters');
        const activeFilters = [];

        Object.entries(this.filters).forEach(([key, value]) => {
            if (value) {
                const filterNames = {
                    search: `Поиск: "${value}"`,
                    city: `Город: ${document.getElementById('cityFilter').options[document.getElementById('cityFilter').selectedIndex].text}`,
                    rating: `Рейтинг: ${value}+ ⭐`,
                    price: `Цена: ${document.getElementById('priceFilter').options[document.getElementById('priceFilter').selectedIndex].text}`,
                    services: `Услуга: ${document.getElementById('servicesFilter').options[document.getElementById('servicesFilter').selectedIndex].text}`
                };

                activeFilters.push({
                    key,
                    value,
                    display: filterNames[key]
                });
            }
        });

        if (activeFilters.length === 0) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = activeFilters.map(filter => `
            <div class="filter-tag">
                ${filter.display}
                <button class="filter-tag-remove" data-filter="${filter.key}">✕</button>
            </div>
        `).join('');

        // Добавляем обработчики для удаления фильтров
        container.querySelectorAll('.filter-tag-remove').forEach(button => {
            button.addEventListener('click', (e) => {
                const filterKey = e.target.dataset.filter;
                this.removeFilter(filterKey);
            });
        });
    }

    removeFilter(filterKey) {
        switch (filterKey) {
            case 'search':
                document.getElementById('searchInput').value = '';
                this.filters.search = '';
                break;
            case 'city':
                document.getElementById('cityFilter').value = '';
                this.filters.city = '';
                break;
            case 'rating':
                document.getElementById('ratingFilter').value = '';
                this.filters.rating = '';
                break;
            case 'price':
                document.getElementById('priceFilter').value = '';
                this.filters.price = '';
                break;
            case 'services':
                document.getElementById('servicesFilter').value = '';
                this.filters.services = '';
                break;
        }
        this.applyFilters();
    }

    resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('cityFilter').value = '';
        document.getElementById('ratingFilter').value = '';
        document.getElementById('priceFilter').value = '';
        document.getElementById('servicesFilter').value = '';
        
        this.filters = {
            search: '',
            city: '',
            rating: '',
            price: '',
            services: ''
        };
        
        this.applyFilters();
    }

    loadMoreClubs() {
        this.currentPage++;
        this.renderClubs();
    }

    showLoading() {
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('clubsGrid').style.display = 'none';
    }

    hideLoading() {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('clubsGrid').style.display = 'grid';
    }

    openSuggestionModal() {
        document.getElementById('suggestionModal').classList.add('show');
    }

    closeSuggestionModal() {
        document.getElementById('suggestionModal').classList.remove('show');
    }

    submitSuggestion() {
        const form = document.querySelector('.suggestion-form');
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#ef4444';
            } else {
                input.style.borderColor = '#e2e8f0';
            }
        });

        if (isValid) {
            // Имитация отправки формы
            this.showNotification('Предложение отправлено! Мы свяжемся с вами в ближайшее время.', 'success');
            this.closeSuggestionModal();
            form.reset();
        } else {
            this.showNotification('Пожалуйста, заполните все обязательные поля', 'error');
        }
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${type === 'success' ? '✅' : '⚠️'}</span>
                <span>${message}</span>
            </div>
        `;

        // Стили для уведомления (можно вынести в CSS)
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 2rem;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
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
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new ClubsPage();
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