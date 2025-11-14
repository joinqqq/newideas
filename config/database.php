<?php
class Database {
    // Упрощенные настройки для Beget
    private $host = "localhost";
    private $db_name = "a91661tv_gmail"; // ЗАМЕНИТЕ на вашу БД
    private $username = "a91661tv_gmail";   // ЗАМЕНИТЕ на вашего пользователя
    private $password = "Dimaslava2005";   // ЗАМЕНИТЕ на ваш пароль
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // Простое сообщение без деталей для безопасности
            error_log("Database connection failed");
            return null;
        }
        
        return $this->conn;
    }
}
?>