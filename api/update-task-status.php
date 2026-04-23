<?php
require_once '../includes/config.php';
requireLogin();

if (!isset($_GET['id'], $_GET['status'])) {
    header('Location: ../tasks.php');
    exit;
}

$id = (int) $_GET['id'];
$status = $_GET['status'];

if (!in_array($status, ['pending', 'done'], true)) {
    header('Location: ../tasks.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$status, $id, $_SESSION['user_id']]);

header('Location: ../tasks.php');
exit;