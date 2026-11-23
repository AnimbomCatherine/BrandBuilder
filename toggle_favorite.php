<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$designer_id = isset($_POST['designer_id']) ? (int)$_POST['designer_id'] : 0;

if ($designer_id > 0) {
    // Check if already favorited
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE client_id = ? AND designer_id = ?");
    $stmt->execute([$client_id, $designer_id]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Remove from favorites
        $del = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $del->execute([$fav['id']]);
    } else {
        // Add to favorites
        $ins = $pdo->prepare("INSERT INTO favorites (client_id, designer_id) VALUES (?, ?)");
        $ins->execute([$client_id, $designer_id]);
    }
}

// Redirect back to the designer profile
header("Location: designer_profile.php?id=" . $designer_id);
exit;
?>