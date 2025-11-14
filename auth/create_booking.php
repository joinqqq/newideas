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
    $club_id = $_POST['club_id'] ?? null;
    $computer_id = $_POST['computer_id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
    $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;

    if (!$computer_id || !$club_id || !$booking_date || !$start_time || $duration <= 0) {
        $_SESSION['error'] = "Пожалуйста, заполните все поля корректно.";
        header("Location: ../booking.php?club_id=" . $club_id);
        exit();
    }

    // Проверяем существование компьютера
    $check_computer = $db->prepare("SELECT COUNT(*) FROM computers WHERE id = ?");
    $check_computer->execute([$computer_id]);
    if ($check_computer->fetchColumn() == 0) {
        $_SESSION['error'] = "Указанный компьютер не найден в базе данных.";
        header("Location: ../booking.php?club_id=" . $club_id);
        exit();
    }

    // Рассчитываем время окончания
    $start_datetime = new DateTime($booking_date . ' ' . $start_time);
    $end_datetime = clone $start_datetime;
    $end_datetime->modify("+{$duration} hours");
    $end_time = $end_datetime->format('H:i:s');

    // Генерируем уникальный QR-код
    $qr_code = uniqid('CB_');

    // Проверяем доступность компьютера
    $availability_query = "
        SELECT COUNT(*) FROM bookings 
        WHERE computer_id = ? 
        AND booking_date = ? 
        AND status = 'active'
        AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
    ";
    $availability_stmt = $db->prepare($availability_query);
    $availability_stmt->execute([$computer_id, $booking_date, $start_time, $start_time, $end_time, $end_time]);

    if ($availability_stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Выбранное время уже занято. Пожалуйста, выберите другое время.";
        header("Location: ../booking.php?club_id=" . $club_id);
        exit();
    }

    // Создаём бронирование
    $query = "
        INSERT INTO bookings 
        (user_id, club_id, computer_id, booking_date, start_time, end_time, duration, total_price, qr_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $db->prepare($query);

    if ($stmt->execute([$user_id, $club_id, $computer_id, $booking_date, $start_time, $end_time, $duration, $total_price, $qr_code])) {
        $booking_id = $db->lastInsertId();

        // Начисляем бонусы (5% от стоимости)
        $bonus_amount = floor($total_price * 0.05);
        $bonus_query = "INSERT INTO bonuses (user_id, amount, type, description, booking_id) VALUES (?, ?, 'earned', 'Бонусы за бронирование', ?)";
        $bonus_stmt = $db->prepare($bonus_query);
        $bonus_stmt->execute([$user_id, $bonus_amount, $booking_id]);

        $_SESSION['booking_id'] = $booking_id;
        header("Location: ../success.php");
        exit();
    } else {
        $_SESSION['error'] = "Ошибка при создании бронирования.";
        header("Location: ../booking.php?club_id=" . $club_id);
        exit();
    }
}
?>
