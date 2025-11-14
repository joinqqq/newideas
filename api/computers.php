<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$club_id = $_GET['club_id'] ?? null;
$zone = $_GET['zone'] ?? null;

$query = "SELECT c.*, cl.name as club_name, cl.hourly_rate 
          FROM computers c 
          JOIN clubs cl ON c.club_id = cl.id 
          WHERE c.is_active = TRUE";
          
$params = [];

if ($club_id) {
    $query .= " AND c.club_id = ?";
    $params[] = $club_id;
}

if ($zone && $zone !== 'all') {
    $query .= " AND c.zone = ?";
    $params[] = $zone;
}

$stmt = $db->prepare($query);
$stmt->execute($params);

$computers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Проверяем доступность компьютеров
foreach ($computers as &$computer) {
    $availability_query = "SELECT COUNT(*) FROM bookings 
                          WHERE computer_id = ? 
                          AND booking_date = CURDATE() 
                          AND status = 'active' 
                          AND start_time <= NOW() 
                          AND end_time >= NOW()";
    $availability_stmt = $db->prepare($availability_query);
    $availability_stmt->execute([$computer['id']]);
    $computer['is_occupied'] = $availability_stmt->fetchColumn() > 0;
}

echo json_encode($computers);
?>