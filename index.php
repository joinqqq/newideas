<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Получаем популярные клубы
$query = "SELECT * FROM clubs ORDER BY rating DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$popular_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Статистика
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM clubs) as total_clubs,
    (SELECT COUNT(*) FROM bookings WHERE status = 'active' AND booking_date >= CURDATE()) as active_bookings,
    (SELECT AVG(rating) FROM reviews) as avg_rating";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberBook - Онлайн запись в компьютерные клубы</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    .header {
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 70px;
    }
    
    .logo {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        text-decoration: none;
    }
    
    .nav-links {
        display: flex;
        align-items: center;
        gap: 30px;
    }
    
    .nav-links a {
        text-decoration: none;
        color: #333;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .nav-links a:hover {
        color: #007bff;
    }
    
    .btn-outline {
        border: 2px solid #007bff;
        padding: 8px 16px;
        border-radius: 6px;
        color: #007bff;
        transition: all 0.3s ease;
    }
    
    .btn-outline:hover {
        background: #007bff;
        color: white;
    }
    
    /* Бургер-меню */
    .burger-menu {
        display: none;
        flex-direction: column;
        cursor: pointer;
        width: 30px;
        height: 20px;
        position: relative;
    }
    
    .burger-menu span {
        display: block;
        height: 3px;
        width: 100%;
        background: #333;
        border-radius: 3px;
        transition: all 0.3s ease;
        transform-origin: center;
    }
    
    .burger-menu span:nth-child(1) {
        position: absolute;
        top: 0;
    }
    
    .burger-menu span:nth-child(2) {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .burger-menu span:nth-child(3) {
        position: absolute;
        bottom: 0;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .burger-menu {
            display: flex;
        }
        
        .nav-links {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            background: white;
            flex-direction: column;
            padding: 20px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            gap: 15px;
        }
        
        .nav-links.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .nav-links a {
            padding: 12px 0;
            width: 100%;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .nav-links a:last-child {
            border-bottom: none;
        }
        
        /* Анимация бургер-меню при открытии */
        .burger-menu.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }
        
        .burger-menu.active span:nth-child(2) {
            opacity: 0;
        }
        
        .burger-menu.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }
    }
    
    /* Дополнительные медиа-запросы для очень маленьких экранов */
    @media (max-width: 480px) {
        .container {
            padding: 0 15px;
        }
        
        .logo {
            font-size: 20px;
        }
        
        .nav {
            height: 60px;
        }
        
        .nav-links {
            top: 60px;
        }
    }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">CyberBook</a>
                
                <!-- Бургер-меню иконка -->
                <div class="burger-menu" id="burgerMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <!-- Навигационные ссылки -->
                <div class="nav-links" id="navLinks">
                    <a href="clubs.php">Клубы</a>
                    <a href="#how-it-works">Как это работает</a>
                    <a href="#features">Преимущества</a>
                    <?php if (isset($_SESSION['logged_in'])): ?>
                        <a href="profile.php" class="btn-outline">👤 <?php echo $_SESSION['user_name']; ?></a>
                    <?php else: ?>
                        <a href="login.php" class="btn-outline">Войти</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Забронируйте место в лучших компьютерных клубах</h1>
                <p>Быстрая онлайн-запись, выбор конкретного компьютера и моментальное подтверждение</p>
                <a href="clubs.php" class="btn btn-primary">Найти клуб</a>
                
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['total_clubs'] ?? '12'; ?></div>
                        <div class="stat-label">Клубов партнеров</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['active_bookings'] ?? '156'; ?></div>
                        <div class="stat-label">Броней сегодня</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo number_format($stats['avg_rating'] ?? 4.8, 1); ?></div>
                        <div class="stat-label">Рейтинг сервиса</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="container">
            <h2>Почему выбирают CyberBook</h2>
            <div class="features-grid">
                <div class="feature-card fade-in">
                    <div class="feature-icon">⚡</div>
                    <h3>Мгновенная запись</h3>
                    <p>Забронируйте место онлайн за 2 минуты. Никаких звонков и ожидания. Мгновенное подтверждение брони.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">🎯</div>
                    <h3>Выбор места</h3>
                    <p>Смотрите планировку клуба и выбирайте конкретный компьютер с нужными характеристиками.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">🛡️</div>
                    <h3>Гарантия брони</h3>
                    <p>Ваше место зарезервировано до вашего прихода. Отмена брони без штрафа за 2 часа до начала.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">💎</div>
                    <h3>Премиум клубы</h3>
                    <p>Только проверенные клубы с современным оборудованием и высоким уровнем сервиса.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">📱</div>
                    <h3>Умное управление</h3>
                    <p>Переносите, отменяйте брони и управляйте предоплатой прямо из личного кабинета.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">🎁</div>
                    <h3>Бонусная система</h3>
                    <p>Получайте кешбэк за каждую бронь и обменивайте бонусы на игровое время и напитки.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Clubs -->
    <section class="popular-clubs">
        <div class="container">
            <h2>Популярные клубы</h2>
            <div class="clubs-grid">
                <?php foreach ($popular_clubs as $club): ?>
                <div class="club-card fade-in">
                    <div class="club-image" style="background: var(--gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                        🎮
                    </div>
                    <div class="club-info">
                        <h3><?php echo htmlspecialchars($club['name']); ?></h3>
                        <div class="rating">⭐ <?php echo $club['rating']; ?> (<?php echo rand(50, 200); ?> отзывов)</div>
                        <p><?php echo htmlspecialchars($club['address']); ?></p>
                        <p><strong><?php echo $club['hourly_rate']; ?> ₽/час</strong></p>
                        <a href="booking.php?club_id=<?php echo $club['id']; ?>" class="btn btn-primary btn-small">Забронировать</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="clubs.php" class="btn btn-outline">Все клубы</a>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2>Как работает CyberBook</h2>
            <div class="steps">
                <div class="step fade-in">
                    <div class="step-number">1</div>
                    <h3>Выберите клуб</h3>
                    <p>Найдите подходящий клуб по местоположению, рейтингу и оборудованию</p>
                </div>
                <div class="step fade-in">
                    <div class="step-number">2</div>
                    <h3>Выберите место</h3>
                    <p>Посмотрите планировку и выберите конкретный компьютер с нужными характеристиками</p>
                </div>
                <div class="step fade-in">
                    <div class="step-number">3</div>
                    <h3>Забронируйте</h3>
                    <p>Выберите дату, время и подтвердите бронь онлайн-оплатой</p>
                </div>
                <div class="step fade-in">
                    <div class="step-number">4</div>
                    <h3>Играйте!</h3>
                    <p>Приходите в клуб, покажите QR-код и занимайте своё место</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Готовы начать играть?</h2>
                <p>Присоединяйтесь к тысячам геймеров, которые уже используют CyberBook для комфортной игры</p>
                <?php if (isset($_SESSION['logged_in'])): ?>
                    <a href="clubs.php" class="btn btn-primary btn-large">🎮 Начать бронирование</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary btn-large">🎮 Создать аккаунт</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>CyberBook</h4>
                    <p>Передовая система бронирования для киберспортивных клубов. Комфорт, удобство и надежность.</p>
                </div>
                <div class="footer-section">
                    <h4>Компания</h4>
                    <ul class="footer-links">
                        <li><a href="about.php">О нас</a></li>
                        <li><a href="business.php">Для бизнеса</a></li>
                        <li><a href="contacts.php">Контакты</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Поддержка</h4>
                    <ul class="footer-links">
                        <li><a href="help.php">Помощь</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="rules.php">Правила</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Контакты</h4>
                    <p>📧 support@cyberbook.ru<br>📞 +7 (495) 123-45-67<br>📍 Москва, ул. Геймерская, 15</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 CyberBook. Все права защищены. Сделано с ❤️ для геймеров</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const burgerMenu = document.getElementById('burgerMenu');
        const navLinks = document.getElementById('navLinks');
        
        burgerMenu.addEventListener('click', function() {
            burgerMenu.classList.toggle('active');
            navLinks.classList.toggle('active');
            
            // Блокировка скролла при открытом меню
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        });
        
        // Закрытие меню при клике на ссылку
        const links = navLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function() {
                burgerMenu.classList.remove('active');
                navLinks.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Закрытие меню при ресайзе окна (если перешли на десктоп)
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                burgerMenu.classList.remove('active');
                navLinks.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    </script>
</body>
</html>