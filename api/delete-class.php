<?php
require_once '../includes/config.php';
requireLogin();

if (empty($_GET['id'])) {
    header('Location: ../classes.php');
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM classes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

header('Location: ../classes.php');
exit;