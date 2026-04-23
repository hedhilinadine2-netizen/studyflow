<?php
require_once '../includes/config.php';
requireLogin();

if (empty($_GET['id'])) {
    header('Location: ../vacations.php');
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM vacations WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

header('Location: ../vacations.php');
exit;