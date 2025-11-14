<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Получаем параметры фильтрации
$city = $_GET['city'] ?? '';
$rating = $_GET['rating'] ?? '';
$price = $_GET['price'] ?? '';

// Базовый запрос
$query = "SELECT * FROM clubs WHERE 1=1";
$params = [];

if ($city) {
    $query .= " AND city = ?";
    $params[] = $city;
}

if ($rating) {
    $query .= " AND rating >= ?";
    $params[] = $rating;
}

if ($price) {
    list($minPrice, $maxPrice) = explode('-', $price);
    $query .= " AND hourly_rate BETWEEN ? AND ?";
    $params[] = $minPrice;
    $params[] = $maxPrice;
}

$query .= " ORDER BY rating DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем уникальные города для фильтра
$cities_query = "SELECT DISTINCT city FROM clubs ORDER BY city";
$cities_stmt = $db->prepare($cities_query);
$cities_stmt->execute();
$cities = $cities_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клубы - CyberBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/clubs.css">
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Найдите свой идеальный клуб</h1>
                <p><?php echo count($clubs); ?>+ премиальных киберспортивных клубов с лучшим оборудованием</p>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" action="clubs.php" class="filters-grid">
                <!-- Search -->
                <div class="search-box">
                    <div class="search-icon">🔍</div>
                    <input type="text" name="search" placeholder="Поиск по названию клуба или адресу..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </div>

                <!-- Filters Row -->
                <div class="filters-row">
                    <div class="filter-group">
                        <label>Город</label>
                        <select class="filter-select" name="city">
                            <option value="">Все города</option>
                            <?php foreach ($cities as $city_item): ?>
                                <option value="<?php echo $city_item; ?>" <?php echo ($city === $city_item) ? 'selected' : ''; ?>>
                                    <?php echo $city_item; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Рейтинг</label>
                        <select class="filter-select" name="rating">
                            <option value="">Любой рейтинг</option>
                            <option value="4.5" <?php echo ($rating === '4.5') ? 'selected' : ''; ?>>4.5+ ⭐</option>
                            <option value="4.0" <?php echo ($rating === '4.0') ? 'selected' : ''; ?>>4.0+ ⭐</option>
                            <option value="3.5" <?php echo ($rating === '3.5') ? 'selected' : ''; ?>>3.5+ ⭐</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Цена за час</label>
                        <select class="filter-select" name="price">
                            <option value="">Любая цена</option>
                            <option value="0-300" <?php echo ($price === '0-300') ? 'selected' : ''; ?>>до 300 ₽</option>
                            <option value="300-500" <?php echo ($price === '300-500') ? 'selected' : ''; ?>>300-500 ₽</option>
                            <option value="500-800" <?php echo ($price === '500-800') ? 'selected' : ''; ?>>500-800 ₽</option>
                            <option value="800-10000" <?php echo ($price === '800-10000') ? 'selected' : ''; ?>>от 800 ₽</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Применить</button>
                        <a href="clubs.php" class="btn btn-outline">Сбросить</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Clubs Grid -->
    <section class="clubs-listing">
        <div class="container">
            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    Найдено <span id="clubsCount"><?php echo count($clubs); ?></span> клубов
                </div>
            </div>

            <!-- Clubs Grid -->
            <div class="clubs-grid-enhanced">
                <?php if (empty($clubs)): ?>
                    <div class="no-results">
                        <div class="no-results-icon">🎮</div>
                        <h3>Клубы не найдены</h3>
                        <p>Попробуйте изменить параметры фильтрации</p>
                        <a href="clubs.php" class="btn btn-primary">Сбросить фильтры</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($clubs as $club): ?>
                    <div class="club-card-enhanced fade-in">
                        <div class="club-card-header">
                            <div class="club-card-badge">⭐ <?php echo $club['rating']; ?></div>
                        </div>
                        <div class="club-card-content">
                            <div class="club-card-title">
                                <div>
                                    <h3><?php echo htmlspecialchars($club['name']); ?></h3>
                                    <div class="club-rating">
                                        ⭐ <?php echo $club['rating']; ?> <span style="color: var(--gray); font-weight: normal;">(<?php echo rand(50, 200); ?>)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="club-meta-enhanced">
                                <div class="meta-item-enhanced">
                                    <span class="icon">📍</span>
                                    <span><?php echo htmlspecialchars($club['city']); ?></span>
                                </div>
                                <div class="meta-item-enhanced">
                                    <span class="icon">🕐</span>
                                    <span><?php echo $club['is_24h'] ? '24/7' : substr($club['open_time'], 0, 5) . '-' . substr($club['close_time'], 0, 5); ?></span>
                                </div>
                                <div class="meta-item-enhanced">
                                    <span class="icon">💻</span>
                                    <span><?php echo rand(10, 50); ?> ПК</span>
                                </div>
                                <div class="meta-item-enhanced">
                                    <span class="icon">💰</span>
                                    <span><?php echo $club['hourly_rate']; ?> ₽/час</span>
                                </div>
                            </div>

                            <div class="club-features">
                                <span class="feature-tag">RTX 40 series</span>
                                <span class="feature-tag">240Hz</span>
                                <span class="feature-tag">Механика</span>
                            </div>

                            <div class="club-card-footer">
                                <div>
                                    <div class="club-price"><?php echo $club['hourly_rate']; ?> ₽</div>
                                    <div class="club-price-period">за час</div>
                                </div>
                                <a href="booking.php?club_id=<?php echo $club['id']; ?>" class="btn btn-primary btn-small">
                                    Забронировать
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Не нашли подходящий клуб?</h2>
                <p>Мы постоянно добавляем новые партнерские клубы. Оставьте заявку и мы найдем для вас идеальный вариант!</p>
                <button class="btn btn-primary" id="suggestClub">
                    🎯 Предложить клуб
                </button>
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
    <script src="js/clubs.js"></script>
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