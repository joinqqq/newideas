<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $db->prepare($user_query);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Статистика пользователя
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'active') as active_bookings,
    (SELECT SUM(duration) FROM bookings WHERE user_id = ? AND status = 'completed') as total_hours,
    (SELECT COALESCE(SUM(amount), 0) FROM bonuses WHERE user_id = ? AND type = 'earned') - 
    (SELECT COALESCE(SUM(amount), 0) FROM bonuses WHERE user_id = ? AND type = 'spent') as bonus_balance";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$user_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Активные бронирования
$bookings_query = "SELECT b.*, c.name as club_name, c.address, comp.number as computer_number 
                   FROM bookings b 
                   JOIN clubs c ON b.club_id = c.id 
                   JOIN computers comp ON b.computer_id = comp.id 
                   WHERE b.user_id = ? AND b.status = 'active' 
                   ORDER BY b.booking_date, b.start_time";
$bookings_stmt = $db->prepare($bookings_query);
$bookings_stmt->execute([$user_id]);
$active_bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// История бронирований
$history_query = "SELECT b.*, c.name as club_name, c.address, comp.number as computer_number 
                  FROM bookings b 
                  JOIN clubs c ON b.club_id = c.id 
                  JOIN computers comp ON b.computer_id = comp.id 
                  WHERE b.user_id = ? AND b.status IN ('completed', 'cancelled')
                  ORDER BY b.created_at DESC 
                  LIMIT 10";
$history_stmt = $db->prepare($history_query);
$history_stmt->execute([$user_id]);
$booking_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

// История бонусов
$bonus_history_query = "SELECT * FROM bonuses 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 10";
$bonus_history_stmt = $db->prepare($bonus_history_query);
$bonus_history_stmt->execute([$user_id]);
$bonus_history = $bonus_history_stmt->fetchAll(PDO::FETCH_ASSOC);

// Текущая вкладка
$current_tab = $_GET['tab'] ?? 'bookings';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - CyberBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
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

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container">
            <div class="profile-overview">
                <div class="profile-avatar">
                    <div class="avatar-image">👤</div>
                    <div class="avatar-status online"></div>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="profile-stats">
                        <div class="stat">
                            <div class="stat-number"><?php echo $user_stats['active_bookings']; ?></div>
                            <div class="stat-label">Активных броней</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $user_stats['total_hours'] ?? 0; ?></div>
                            <div class="stat-label">Часов игры</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $user_stats['bonus_balance']; ?></div>
                            <div class="stat-label">Бонусов</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Profile Navigation -->
    <section class="profile-nav">
        <div class="container">
            <nav class="profile-tabs">
                <a href="?tab=bookings" class="tab-btn <?php echo $current_tab === 'bookings' ? 'active' : ''; ?>">Текущие брони</a>
                <a href="?tab=history" class="tab-btn <?php echo $current_tab === 'history' ? 'active' : ''; ?>">История посещений</a>
                <a href="?tab=bonuses" class="tab-btn <?php echo $current_tab === 'bonuses' ? 'active' : ''; ?>">Бонусы</a>
                <a href="?tab=settings" class="tab-btn <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">Настройки</a>
            </nav>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="profile-content">
        <div class="container">
            <?php if ($current_tab === 'bookings'): ?>
                <!-- Current Bookings Tab -->
                <div class="tab-content active">
                    <div class="section-header">
                        <h2>Текущие бронирования</h2>
                        <div class="section-actions">
                            <a href="clubs.php" class="btn btn-primary">🎮 Забронировать снова</a>
                        </div>
                    </div>

                    <div class="bookings-grid">
                        <?php if (empty($active_bookings)): ?>
                            <div class="no-bookings">
                                <div class="no-results-icon">🎮</div>
                                <h3>Нет активных бронирований</h3>
                                <p>Найдите подходящий клуб и забронируйте свое игровое время</p>
                                <a href="clubs.php" class="btn btn-primary">Найти клуб</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($active_bookings as $booking): ?>
                            <div class="booking-card active">
                                <div class="booking-header">
                                    <div class="booking-info">
                                        <h3><?php echo htmlspecialchars($booking['club_name']); ?></h3>
                                        <div class="booking-meta">
                                            <span class="meta-item">📍 <?php echo htmlspecialchars($booking['address']); ?></span>
                                            <span class="meta-item">🖥️ #<?php echo htmlspecialchars($booking['computer_number']); ?></span>
                                        </div>
                                    </div>
                                    <div class="booking-status active">
                                        <?php
                                        $booking_date = new DateTime($booking['booking_date']);
                                        $today = new DateTime();
                                        $diff = $today->diff($booking_date);
                                        
                                        if ($diff->days == 0) {
                                            echo 'Сегодня';
                                        } elseif ($diff->days == 1) {
                                            echo 'Завтра';
                                        } else {
                                            echo $booking_date->format('d.m');
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Дата и время:</span>
                                        <span class="detail-value">
                                            <?php echo date('d.m.Y', strtotime($booking['booking_date'])); ?>, 
                                            <?php echo substr($booking['start_time'], 0, 5); ?> - <?php echo substr($booking['end_time'], 0, 5); ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Продолжительность:</span>
                                        <span class="detail-value"><?php echo $booking['duration']; ?> часа</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Стоимость:</span>
                                        <span class="detail-value"><?php echo $booking['total_price']; ?> ₽</span>
                                    </div>
                                </div>
                                <div class="booking-actions">
                                    <a href="success.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-outline btn-small">
                                        📱 Показать QR-код
                                    </a>
                                    <form action="auth/cancel_booking.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-outline btn-small btn-cancel" 
                                                onclick="return confirm('Вы уверены, что хотите отменить бронирование?')">
                                            ❌ Отменить
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($current_tab === 'history'): ?>
                <!-- History Tab -->
                <div class="tab-content active">
                    <div class="section-header">
                        <h2>История посещений</h2>
                    </div>

                    <div class="history-list">
                        <?php if (empty($booking_history)): ?>
                            <div class="no-bookings">
                                <div class="no-results-icon">📊</div>
                                <h3>История пуста</h3>
                                <p>Здесь будут отображаться ваши завершенные бронирования</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($booking_history as $history): ?>
                            <div class="history-item">
                                <div class="history-main">
                                    <div class="club-info">
                                        <h4><?php echo htmlspecialchars($history['club_name']); ?></h4>
                                        <div class="history-meta">
                                            <span>#<?php echo $history['computer_number']; ?> • <?php echo $history['duration']; ?> часа • <?php echo $history['total_price']; ?> ₽</span>
                                        </div>
                                    </div>
                                    <div class="history-date">
                                        <?php echo date('d.m.Y', strtotime($history['booking_date'])); ?>
                                        <div class="history-status <?php echo $history['status']; ?>">
                                            <?php echo $history['status'] === 'completed' ? 'Завершено' : 'Отменено'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="history-actions">
                                    <a href="clubs.php" class="btn-text">Повторить бронь</a>
                                    <?php if ($history['status'] === 'completed'): ?>
                                        <button class="btn-text" onclick="leaveReview(<?php echo $history['id']; ?>, '<?php echo htmlspecialchars($history['club_name']); ?>')">
                                            Оставить отзыв
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($current_tab === 'bonuses'): ?>
                <!-- Bonuses Tab -->
                <div class="tab-content active">
                    <div class="bonuses-header">
                        <div class="bonus-balance">
                            <div class="balance-amount"><?php echo $user_stats['bonus_balance']; ?></div>
                            <div class="balance-label">бонусных баллов</div>
                        </div>
                        <div class="bonus-info">
                            <p>1 бонус = 1 рубль. Бонусами можно оплатить до 50% стоимости бронирования</p>
                        </div>
                    </div>

                    <div class="bonus-history">
                        <h3>История операций</h3>
                        <div class="history-items">
                            <?php if (empty($bonus_history)): ?>
                                <div class="no-bonuses">
                                    <p>У вас пока нет операций с бонусами</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($bonus_history as $bonus): ?>
                                <div class="bonus-history-item <?php echo $bonus['type'] === 'earned' ? 'positive' : 'negative'; ?>">
                                    <div class="bonus-details">
                                        <div class="bonus-description"><?php echo htmlspecialchars($bonus['description']); ?></div>
                                        <div class="bonus-date"><?php echo date('d.m.Y', strtotime($bonus['created_at'])); ?></div>
                                    </div>
                                    <div class="bonus-amount">
                                        <?php echo $bonus['type'] === 'earned' ? '+' : '-'; ?>
                                        <?php echo $bonus['amount']; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($current_tab === 'settings'): ?>
                <!-- Settings Tab -->
                <div class="tab-content active">
                    <div class="settings-section">
                        <h3>Личная информация</h3>
                        <form class="settings-form" action="auth/update_profile.php" method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Фамилия</label>
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background: #f8f9fa;">
                            </div>
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                💾 Сохранить изменения
                            </button>
                        </form>
                    </div>

                    <div class="settings-section">
                        <h3>Смена пароля</h3>
                        <form class="settings-form" action="auth/change_password.php" method="POST">
                            <div class="form-group">
                                <label>Текущий пароль</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label>Новый пароль</label>
                                <input type="password" name="new_password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label>Подтвердите новый пароль</label>
                                <input type="password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                🔒 Сменить пароль
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
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
    function leaveReview(bookingId, clubName) {
        const rating = prompt(`Оцените клуб "${clubName}" от 1 до 5 звезд:`);
        if (rating && rating >= 1 && rating <= 5) {
            const comment = prompt('Оставьте комментарий (необязательно):');
            // Здесь можно добавить AJAX запрос для сохранения отзыва
            alert('Спасибо за ваш отзыв!');
        }
    }
    </script>
    <script src="js/script.js"></script>
    <script src="js/profile.js"></script>
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