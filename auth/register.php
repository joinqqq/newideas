<?php
session_start();
require_once '../config/database.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    
    // Проверка существования пользователя
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Пользователь с таким email уже существует";
        header("Location: ../register.html");
        exit();
    }
    
    // Хеширование пароля
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Создание пользователя
    $query = "INSERT INTO users (email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$email, $hashed_password, $first_name, $last_name, $phone])) {
        $_SESSION['success'] = "Регистрация успешна! Войдите в систему.";
        header("Location: ../login.php");
        exit();
    } else {
        $_SESSION['error'] = "Ошибка регистрации";
        header("Location: ../register.html");
        exit();
    }
}
?>