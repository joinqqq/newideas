<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['booking_id'])) {
    header("Location: profile.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$booking_id = $_SESSION['booking_id'];

// Получаем детали бронирования
$booking_query = "SELECT b.*, c.name as club_name, c.address, comp.number as computer_number, 
                         comp.cpu, comp.gpu, comp.ram, comp.monitor
                  FROM bookings b 
                  JOIN clubs c ON b.club_id = c.id 
                  JOIN computers comp ON b.computer_id = comp.id 
                  WHERE b.id = ?";
$booking_stmt = $db->prepare($booking_query);
$booking_stmt->execute([$booking_id]);
$booking = $booking_stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: profile.php");
    exit();
}

// Очищаем сессию после показа
unset($_SESSION['booking_id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование подтверждено - CyberBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/success.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">CyberBook</a>
                <div class="nav-links">
                    <a href="clubs.php">Клубы</a>
                    <a href="index.php#how-it-works">Как работает</a>
                    <a href="index.php#features">Преимущества</a>
                    <a href="profile.php" class="btn-outline">👤 <?php echo $_SESSION['user_name']; ?></a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Success Content -->
    <section class="success-section">
        <div class="container">
            <div class="success-content">
                <!-- Success Icon -->
                <div class="success-icon">
                    <div class="icon-circle">
                        ✅
                    </div>
                </div>

                <!-- Success Message -->
                <div class="success-message">
                    <h1>Бронирование подтверждено!</h1>
                    <p>Ваше место успешно забронировано. Приходите в клуб и покажите QR-код на ресепшене.</p>
                </div>

                <!-- Booking Details -->
                <div class="booking-details-card">
                    <div class="details-header">
                        <h2>Детали бронирования</h2>
                        <div class="booking-id">#<?php echo $booking['qr_code']; ?></div>
                    </div>
                    
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Клуб:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['club_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Адрес:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['address']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Дата и время:</span>
                            <span class="detail-value">
                                <?php echo date('d.m.Y', strtotime($booking['booking_date'])); ?>, 
                                <?php echo substr($booking['start_time'], 0, 5); ?> - <?php echo substr($booking['end_time'], 0, 5); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Компьютер:</span>
                            <span class="detail-value">#<?php echo $booking['computer_number']; ?> (<?php echo $booking['cpu']; ?>/<?php echo $booking['gpu']; ?>)</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Продолжительность:</span>
                            <span class="detail-value"><?php echo $booking['duration']; ?> часа</span>
                        </div>
                        <div class="detail-item total">
                            <span class="detail-label">Итого:</span>
                            <span class="detail-value"><?php echo $booking['total_price']; ?> ₽</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="qr-section">
                    <div class="qr-card">
                        <h3>QR-код для входа</h3>
                        <div class="qr-code">
                            <div class="qr-placeholder">
                                <div class="qr-pattern"></div>
                                <span><?php echo $booking['qr_code']; ?></span>
                            </div>
                        </div>
                        <p class="qr-note">Покажите этот код на ресепшене для подтверждения брони</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary" id="downloadTicket">
                        📄 Скачать билет
                    </button>
                    <button class="btn btn-outline" id="shareBooking">
                        📤 Поделиться
                    </button>
                    <a href="profile.php" class="btn btn-outline">
                        👤 В личный кабинет
                    </a>
                </div>

                <!-- Additional Info -->
                <div class="additional-info">
                    <div class="info-card">
                        <div class="info-icon">💡</div>
                        <div class="info-content">
                            <h4>Что нужно знать</h4>
                            <ul>
                                <li>Приходите за 5-10 минут до начала сессии</li>
                                <li>Иметь при себе документ, удостоверяющий личность</li>
                                <li>Отмена брони возможна за 2 часа до начала</li>
                                <li>Вы получили <?php echo floor($booking['total_price'] * 0.05); ?> бонусных баллов</li>
                            </ul>
                        </div>
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

    <script src="js/script.js"></script>
    <script src="js/success.js"></script>
</body>
</html>