<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_SESSION['user_id'];
    $designer_id = (int)$_POST['designer_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (client_id, designer_id, rating, comment) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$client_id, $designer_id, $rating, $comment])) {
            // Add notification to designer
            $notif_msg = "You received a new " . $rating . "-star review!";
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, 'dashboard.php')");
            $notif_stmt->execute([$designer_id, $notif_msg]);
        }
    }
}

header("Location: dashboard.php");
exit;
?>