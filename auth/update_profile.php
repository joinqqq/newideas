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
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    
    $query = "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$first_name, $last_name, $phone, $user_id])) {
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['success'] = "Профиль успешно обновлен";
    } else {
        $_SESSION['error'] = "Ошибка при обновлении профиля";
    }
}

header("Location: ../profile.php?tab=settings");
exit();
?>