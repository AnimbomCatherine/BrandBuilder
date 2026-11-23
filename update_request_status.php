<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'designer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    $designer_id = $_SESSION['user_id'];

    // Validate status
    if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
        header("Location: dashboard.php");
        exit;
    }

    // Update the request only if it belongs to this designer
    $stmt = $pdo->prepare("UPDATE service_requests SET status = ? WHERE id = ? AND designer_id = ?");
    $stmt->execute([$status, $request_id, $designer_id]);
}

header("Location: dashboard.php");
exit;
?>