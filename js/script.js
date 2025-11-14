// js/script.js
class CyberBook {
    constructor() {
        this.init();
    }

    init() {
        this.setupScrollEffects();
        this.setupAnimations();
        this.setupHeader();
    }

    setupHeader() {
        const header = document.getElementById('header');
        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            if (currentScroll > lastScroll && currentScroll > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            
            lastScroll = currentScroll;
        });
    }

    setupScrollEffects() {
        // Плавная прокрутка
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    setupAnimations() {
        // Intersection Observer для анимаций
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    
                    // Специфичные анимации для разных элементов
                    if (entry.target.classList.contains('feature-card')) {
                        this.animateFeatureCard(entry.target);
                    }
                    if (entry.target.classList.contains('club-card')) {
                        this.animateClubCard(entry.target);
                    }
                }
            });
        }, observerOptions);

        // Наблюдаем за всеми анимируемыми элементами
        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    }

    animateFeatureCard(card) {
        const icon = card.querySelector('.feature-icon');
        if (icon) {
            icon.style.transform = 'scale(0)';
            icon.style.transition = 'transform 0.5s ease 0.2s';
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
            }, 50);
        }
    }

    animateClubCard(card) {
        card.style.transform = 'scale(0.95)';
        card.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            card.style.transform = 'scale(1)';
        }, 150);
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new CyberBook();
});

// Дополнительные эффекты для кнопок
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', function(e) {
        const x = e.pageX - this.getBoundingClientRect().left;
        const y = e.pageY - this.getBoundingClientRect().top;
        
        this.style.setProperty('--x', x + 'px');
        this.style.setProperty('--y', y + 'px');
    });
});