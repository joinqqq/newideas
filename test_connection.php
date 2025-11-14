<?php
// Простой тест подключения
echo "🔄 Test...<br>";

// Отключаем вывод ошибок для чистоты
ini_set('display_errors', 0);

try {
    $host = "localhost";
    $dbname = "a91661tv_gmail"; // ЗАМЕНИТЕ
    $username = "a91661tv_gmail"; // ЗАМЕНИТЕ  
    $password = "Dimaslava2005"; // ЗАМЕНИТЕ
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Good<br>";
    
    // Проверяем таблицы
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Find tables: " . count($tables) . "<br>";
    
    if (count($tables) > 0) {
        echo "📊 Tables: " . implode(", ", $tables) . "<br>";
    } else {
        echo "ℹ️ No tables.<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error " . $e->getMessage() . "<br>";
    echo "💡 check::<br>";
    echo "   - name<br>";
    echo "   - name user<br>"; 
    echo "   - passowrd<br>";
    echo "   - u have bd? Beget<br>";
}

// Проверяем версию PHP
echo "<br>🐘 PHP VERSION - : " . PHP_VERSION . "<br>";

// Проверяем расширение PDO
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL goodbr>";
} else {
    echo "❌ PDO MySQL not goodbr>";
}
?>