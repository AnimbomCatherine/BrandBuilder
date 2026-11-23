<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header("Location: notifications.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2>Notifications</h2>
            <?php if (!empty($notifications)): ?>
                <a href="notifications.php?mark_read=1" style="color: var(--accent); font-weight: 600; font-size: 0.875rem;">Mark all as read</a>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="designer-card" style="text-align: center; padding: 40px;">
                <p style="color: var(--text-muted);">You have no notifications.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($notifications as $notif): ?>
                    <a href="<?= htmlspecialchars($notif['link'] ?? '#') ?>" style="text-decoration: none; color: inherit;">
                        <div class="designer-card" style="padding: 16px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid <?= $notif['is_read'] ? 'var(--border-light)' : 'var(--accent)' ?>;">
                            <div>
                                <p style="font-weight: <?= $notif['is_read'] ? '400' : '600' ?>; margin-bottom: 4px; color: var(--text-primary);"><?= htmlspecialchars($notif['message']) ?></p>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></span>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <span style="width: 10px; height: 10px; background: var(--accent); border-radius: 50%;"></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>