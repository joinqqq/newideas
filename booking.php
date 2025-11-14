<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$club_id = $_GET['club_id'] ?? null;

if (!$club_id) {
    header("Location: clubs.php");
    exit();
}

// Получаем информацию о клубе
$club_query = "SELECT * FROM clubs WHERE id = ?";
$club_stmt = $db->prepare($club_query);
$club_stmt->execute([$club_id]);
$club = $club_stmt->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    header("Location: clubs.php");
    exit();
}

// Получаем компьютеры клуба
$computers_query = "SELECT * FROM computers WHERE club_id = ? AND is_active = TRUE";
$computers_stmt = $db->prepare($computers_query);
$computers_stmt->execute([$club_id]);
$computers = $computers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование - CyberBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/booking.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    .header {
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
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

    <!-- Booking Process -->
    <section class="booking-process">
        <div class="container">
            <div class="process-steps">
                <div class="process-step active">
                    <div class="step-number">1</div>
                    <span>Выбор времени</span>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <span>Выбор места</span>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <span>Подтверждение</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Booking -->
    <section class="booking-main">
        <div class="container">
            <div class="booking-layout">
                <!-- Left Column - Club Info & Calendar -->
                <div class="booking-left">
                    <!-- Club Info -->
                    <div class="club-info-card">
                        <div class="club-header">
                            <div class="club-image"
                                style="background: var(--gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                🎮
                            </div>
                            <div class="club-details">
                                <h1><?php echo htmlspecialchars($club['name']); ?></h1>
                                <div class="club-rating">
                                    ⭐ <?php echo $club['rating']; ?> (<?php echo rand(50, 200); ?> отзывов)
                                </div>
                                <div class="club-address">
                                    📍 <?php echo htmlspecialchars($club['address']); ?>
                                </div>
                                <div class="club-hours">
                                    🕐
                                    <?php echo $club['is_24h'] ? 'Работает 24/7' : 'Работает с ' . substr($club['open_time'], 0, 5) . ' до ' . substr($club['close_time'], 0, 5); ?>
                                </div>
                            </div>
                        </div>
                        <div class="club-features-list">
                            <div class="feature-item">🎮 RTX 40 series</div>
                            <div class="feature-item">🖥️ 240Hz мониторы</div>
                            <div class="feature-item">🍕 Еда и напитки</div>
                            <div class="feature-item">🎤 Стриминг</div>
                        </div>
                    </div>

                    <!-- Date Selection -->
                    <div class="date-selection">
                        <h3>Выберите дату</h3>
                        <div class="calendar-nav">
                            <button class="nav-btn prev-month">‹</button>
                            <div class="current-month" id="currentMonth">Декабрь 2024</div>
                            <button class="nav-btn next-month">›</button>
                        </div>
                        <div class="calendar" id="calendar">
                            <!-- Calendar will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Time Selection -->
                    <div class="time-selection">
                        <h3>Выберите время</h3>
                        <div class="time-slots" id="timeSlots">
                            <!-- Time slots will be generated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Right Column - Computers & Booking Summary -->
                <div class="booking-right">
                    <!-- Duration Selection -->
                    <div class="duration-selection">
                        <h3>Продолжительность</h3>
                        <div class="duration-options">
                            <button class="duration-option active" data-hours="1">1 час</button>
                            <button class="duration-option" data-hours="2">2 часа</button>
                            <button class="duration-option" data-hours="3">3 часа</button>
                            <button class="duration-option" data-hours="4">4 часа</button>
                            <button class="duration-option custom-duration">
                                <input type="number" min="1" max="12" placeholder="Другое">
                                <span>часов</span>
                            </button>
                        </div>
                    </div>

                    <!-- Computers Grid -->
                    <div class="computers-section">
                        <div class="section-header">
                            <h3>Выберите компьютер</h3>
                            <div class="computers-filter">
                                <select class="filter-select" id="zoneFilter">
                                    <option value="all">Все зоны</option>
                                    <option value="gaming">Игровая</option>
                                    <option value="vip">VIP</option>
                                    <option value="streaming">Стриминг</option>
                                </select>
                            </div>
                        </div>

                        <div class="computers-grid" id="computersGrid">
                            <?php foreach ($computers as $computer): ?>
                                <div class="computer-item" data-computer-id="<?php echo $computer['id']; ?>"
                                    data-zone="<?php echo $computer['zone']; ?>">
                                    <div class="computer-number">#<?php echo $computer['number']; ?></div>
                                    <div class="computer-spec">
                                        <?php echo $computer['cpu']; ?>/<?php echo $computer['gpu']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="computer-details" id="computerDetails" style="display: none;">
                            <div class="details-header">
                                <h4>Компьютер <span id="selectedPcNumber">#A1</span></h4>
                                <button class="btn-close-details">✕</button>
                            </div>
                            <div class="details-specs">
                                <div class="spec-item">
                                    <span class="spec-label">Процессор:</span>
                                    <span class="spec-value" id="spec-cpu">-</span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Видеокарта:</span>
                                    <span class="spec-value" id="spec-gpu">-</span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Оперативная память:</span>
                                    <span class="spec-value" id="spec-ram">-</span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Монитор:</span>
                                    <span class="spec-value" id="spec-monitor">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Summary -->
                    <div class="booking-summary">
                        <div class="summary-header">
                            <h3>Ваше бронирование</h3>
                        </div>
                        <div class="summary-content">
                            <div class="summary-item">
                                <span>Клуб:</span>
                                <span><?php echo htmlspecialchars($club['name']); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Дата:</span>
                                <span id="summaryDate">Не выбрано</span>
                            </div>
                            <div class="summary-item">
                                <span>Время:</span>
                                <span id="summaryTime">Не выбрано</span>
                            </div>
                            <div class="summary-item">
                                <span>Продолжительность:</span>
                                <span id="summaryDuration">Не выбрано</span>
                            </div>
                            <div class="summary-item">
                                <span>Компьютер:</span>
                                <span id="summaryComputer">Не выбран</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-total">
                                <span>Итого:</span>
                                <span id="summaryTotal">0 ₽</span>
                            </div>
                        </div>
                        <form action="auth/create_booking.php" method="POST" id="bookingForm">
                            <input type="hidden" name="club_id" value="<?= $club_id ?>">
                            <input type="hidden" name="computer_id" id="inputComputerId">
                            <input type="hidden" name="booking_date" id="inputBookingDate">
                            <input type="hidden" name="start_time" id="inputStartTime">
                            <input type="hidden" name="duration" id="inputDuration">
                            <input type="hidden" name="total_price" id="inputTotalPrice">

                            <button type="button" class="btn btn-primary" id="confirmBooking" disabled>
                                Перейти к оплате
                            </button>
                        </form>
                    </div>
                </div>
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

    <script>
        // Передаем данные о компьютерах в JavaScript
        const computersData = <?php echo json_encode($computers); ?>;
        const hourlyRate = <?php echo $club['hourly_rate']; ?>;
    </script>
    <script src="js/script.js"></script>
    <script src="js/booking.js"></script>
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