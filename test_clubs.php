<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Тест clubs.php</h1>";

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("❌ Не удалось подключиться к БД");
    }
    
    echo "✅ Подключение к БД успешно<br>";
    
    // Проверяем таблицу clubs
    $tables_query = "SHOW TABLES LIKE 'clubs'";
    $tables_stmt = $db->query($tables_query);
    
    if ($tables_stmt->rowCount() > 0) {
        echo "✅ Таблица 'clubs' существует<br>";
        
        // Проверяем данные в таблице
        $count_query = "SELECT COUNT(*) as count FROM clubs";
        $count_stmt = $db->query($count_query);
        $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Записей в таблице clubs: " . $count['count'] . "<br>";
        
        // Показываем первые 3 клуба
        $clubs_query = "SELECT * FROM clubs LIMIT 3";
        $clubs_stmt = $db->query($clubs_query);
        $clubs = $clubs_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Примеры клубов:</h3>";
        foreach ($clubs as $club) {
            echo "🎮 " . $club['name'] . " - " . $club['city'] . " - " . $club['hourly_rate'] . "₽<br>";
        }
        
        // Проверяем города
        $cities_query = "SELECT DISTINCT city FROM clubs";
        $cities_stmt = $db->query($cities_query);
        $cities = $cities_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Доступные города:</h3>";
        echo implode(", ", $cities) . "<br>";
        
    } else {
        echo "❌ Таблица 'clubs' не существует<br>";
        echo "💡 Нужно создать таблицу через phpMyAdmin или скрипт инициализации<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<a href='clubs.php'>Перейти к clubs.php</a>";
?>