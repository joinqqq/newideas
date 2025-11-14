<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Проверяем текущий пароль
    $check_query = "SELECT password FROM users WHERE id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$user_id]);
    $user = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "Неверный текущий пароль";
        header("Location: ../profile.php?tab=settings");
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Новые пароли не совпадают";
        header("Location: ../profile.php?tab=settings");
        exit();
    }
    
    // Обновляем пароль
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = ? WHERE id = ?";
    $update_stmt = $db->prepare($update_query);
    
    if ($update_stmt->execute([$hashed_password, $user_id])) {
        $_SESSION['success'] = "Пароль успешно изменен";
    } else {
        $_SESSION['error'] = "Ошибка при изменении пароля";
    }
}

header("Location: ../profile.php?tab=settings");
exit();
?>