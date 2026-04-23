<?php
require_once '../includes/config.php';
requireLogin();

$id = (int)$_GET['id'];
$status = $_GET['status'];

// Drop old status column and add fresh one
try {
    $pdo->exec("ALTER TABLE exams DROP COLUMN status");
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE exams ADD COLUMN status VARCHAR(50) DEFAULT 'current'");
} catch (Exception $e) {}

// Now do update
$pdo->exec("UPDATE exams SET status = '$status' WHERE id = $id");

// Verify
$check = $pdo->prepare("SELECT status FROM exams WHERE id = ?");
$check->execute([$id]);
$exam = $check->fetch();

echo "SUCCESS! Status changed to: " . $exam['status'];