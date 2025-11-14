<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Получение списка клубов
        $query = "SELECT * FROM clubs ORDER BY rating DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Добавляем компьютеры для каждого клуба
        foreach ($clubs as &$club) {
            $computers_query = "SELECT COUNT(*) as total_computers FROM computers WHERE club_id = ? AND is_active = TRUE";
            $computers_stmt = $db->prepare($computers_query);
            $computers_stmt->execute([$club['id']]);
            $club['total_computers'] = $computers_stmt->fetchColumn();
        }
        
        echo json_encode($clubs);
        break;
        
    case 'POST':
        // Создание нового клуба (для админов)
        break;
}
?>