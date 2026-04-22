<?php
require_once '../includes/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
        exit();
    }
    
    $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Exam not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>