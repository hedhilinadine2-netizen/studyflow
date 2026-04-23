<?php
require_once '../includes/config.php';
requireLogin();

if (empty($_GET['id']) || empty($_GET['status'])) {
    header('Location: ../vacations.php');
    exit;
}

$id = (int) $_GET['id'];
$status = $_GET['status'];

if (!in_array($status, ['upcoming', 'completed'], true)) {
    header('Location: ../vacations.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE vacations SET status = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$status, $id, $_SESSION['user_id']]);

header('Location: ../vacations.php');
exit;