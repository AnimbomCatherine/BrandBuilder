<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle Verification Toggle
if (isset($_GET['verify_id'])) {
    $verify_id = (int)$_GET['verify_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_verified = NOT is_verified WHERE id = ? AND role = 'designer'");
    $stmt->execute([$verify_id]);
    header("Location: admin_users.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($delete_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
    }
    header("Location: admin_users.php");
    exit;
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Manage Users</h2>
            <a href="admin_dashboard.php" style="color: var(--accent); font-weight: 600;">← Back to Admin</a>
        </div>

        <div class="designer-card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--bg-tertiary); border-bottom: 1px solid var(--border-light);">
                    <tr>
                        <th style="padding: 16px; font-size: 0.875rem;">Name</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Email</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Role</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Verified</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid var(--border-light);">
                            <td style="padding: 16px; color: var(--text-primary);"><?= htmlspecialchars($user['full_name']) ?></td>
                            <td style="padding: 16px; color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></td>
                            <td style="padding: 16px;"><span style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; background: var(--bg-tertiary); color: var(--text-primary); text-transform: capitalize;"><?= $user['role'] ?></span></td>
                            <td style="padding: 16px;">
                                <?php if ($user['role'] === 'designer'): ?>
                                    <?php if ($user['is_verified']): ?>
                                        <span style="color: var(--success-text); font-weight: 600;">✔ Verified</span>
                                    <?php else: ?>
                                        <span style="color: var(--warning-text);">Pending</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 16px; display: flex; gap: 8px;">
                                <?php if ($user['role'] === 'designer'): ?>
                                    <a href="admin_users.php?verify_id=<?= $user['id'] ?>" style="font-size: 0.875rem; color: var(--text-primary); font-weight: 600;">
                                        <?= $user['is_verified'] ? 'Unverify' : 'Verify' ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <a href="admin_users.php?delete_id=<?= $user['id'] ?>" style="font-size: 0.875rem; color: var(--danger); font-weight: 600;" onclick="return confirm('Delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>