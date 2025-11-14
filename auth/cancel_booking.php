<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

if ($_POST && isset($_POST['booking_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];
    
    // Проверяем, что бронирование принадлежит пользователю
    $check_query = "SELECT id FROM bookings WHERE id = ? AND user_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$booking_id, $user_id]);
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Бронирование не найдено";
        header("Location: ../profile.php");
        exit();
    }
    
    // Отменяем бронирование
    $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
    $update_stmt = $db->prepare($update_query);
    
    if ($update_stmt->execute([$booking_id])) {
        $_SESSION['success'] = "Бронирование успешно отменено";
    } else {
        $_SESSION['error'] = "Ошибка при отмене бронирования";
    }
}

header("Location: ../profile.php");
exit();
?>