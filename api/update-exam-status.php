<?php
require_once '../includes/config.php';
requireLogin();

$id = (int)$_GET['id'];
$status = $_GET['status'];

echo "Exam ID: $id, Status: $status<br>";

try {
    // Drop old status column
    $pdo->exec("ALTER TABLE exams DROP COLUMN status");
    echo "Dropped old column<br>";
} catch (Exception $e) {
    echo "Drop error (maybe didn't exist): " . $e->getMessage() . "<br>";
}

try {
    // Add new column
    $pdo->exec("ALTER TABLE exams ADD COLUMN status VARCHAR(50) DEFAULT 'current'");
    echo "Added new column<br>";
} catch (Exception $e) {
    echo "Add error: " . $e->getMessage() . "<br>";
}

// Try update now
$pdo->exec("UPDATE exams SET status = '$status' WHERE id = $id");

// Verify
$check = $pdo->prepare("SELECT status FROM exams WHERE id = ?");
$check->execute([$id]);
$exam = $check->fetch();

echo "Final status: " . $exam['status'];