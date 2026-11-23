<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

$role = $_SESSION['role'];
$name = htmlspecialchars($_SESSION['full_name']);
$user_id = $_SESSION['user_id'];

$my_requests = [];
$my_favorites = [];
$my_services_count = 0;
$my_portfolio_count = 0;

if ($role === 'client') {
    $req_stmt = $pdo->prepare("SELECT sr.*, u.full_name as designer_name FROM service_requests sr JOIN users u ON sr.designer_id = u.id WHERE sr.client_id = ? ORDER BY sr.created_at DESC");
    $req_stmt->execute([$user_id]);
    $my_requests = $req_stmt->fetchAll();

    $fav_stmt = $pdo->prepare("SELECT u.id, u.full_name, dp.location FROM favorites f JOIN users u ON f.designer_id = u.id LEFT JOIN designer_profiles dp ON u.id = dp.user_id WHERE f.client_id = ?");
    $fav_stmt->execute([$user_id]);
    $my_favorites = $fav_stmt->fetchAll();
} elseif ($role === 'designer') {
    $req_stmt = $pdo->prepare("SELECT sr.*, u.full_name as client_name FROM service_requests sr JOIN users u ON sr.client_id = u.id WHERE sr.designer_id = ? ORDER BY sr.created_at DESC");
    $req_stmt->execute([$user_id]);
    $my_requests = $req_stmt->fetchAll();

    $srv_stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE user_id = ?");
    $srv_stmt->execute([$user_id]);
    $my_services_count = $srv_stmt->fetchColumn();

    $port_stmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_items WHERE user_id = ?");
    $port_stmt->execute([$user_id]);
    $my_portfolio_count = $port_stmt->fetchColumn();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Welcome, <?= $name ?>!</h2>
                <p>You are logged in as a <strong><?= ucfirst($role) ?></strong>.</p>
            </div>
            <?php if ($role === 'client'): ?>
                <a href="browse.php" class="search-btn" style="text-decoration: none; padding: 10px 20px;">Find a Designer</a>
            <?php endif; ?>
        </div>

        <?php if ($role === 'client'): ?>
            
            <!-- Service Requests Section -->
            <div style="margin-top: 40px;">
                <h3 style="margin-bottom: 16px;">My Service Requests</h3>
                <?php if (empty($my_requests)): ?>
                    <div class="designer-card" style="text-align: center; padding: 40px;">
                        <p style="color: var(--text-muted);">You haven't sent any requests yet.</p>
                        <a href="browse.php" class="search-btn" style="display: inline-block; margin-top: 16px; text-decoration: none;">Browse Designers</a>
                    </div>
                <?php else: ?>
                    <div class="grid-2">
                        <?php foreach ($my_requests as $req): ?>
                            <div class="designer-card">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                    <h4 style="font-size: 1.125rem; color: var(--text-primary);"><?= htmlspecialchars($req['service_title']) ?></h4>
                                    <span class="status-<?= $req['status'] === 'in_progress' ? 'in-progress' : ($req['status'] ?? 'pending') ?>" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px;">
                                        <?= ucfirst(str_replace('_', ' ', $req['status'] ?? 'pending')) ?>
                                    </span>
                                </div>
                                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 12px;">
                                    <strong style="color: var(--text-primary);">Designer:</strong> <?= htmlspecialchars($req['designer_name']) ?><br>
                                    <strong style="color: var(--text-primary);">Budget:</strong> $<?= number_format($req['budget']) ?><br>
                                    <strong style="color: var(--text-primary);">Date:</strong> <?= date('M d, Y', strtotime($req['created_at'])) ?><br>
                                    <strong style="color: var(--text-primary);">Payment:</strong> <?= htmlspecialchars($req['payment_method'] ?? 'N/A') ?>
                                </p>
                                <p style="font-size: 0.875rem; color: var(--text-muted); font-style: italic; margin-bottom: 16px;">
                                    "<?= htmlspecialchars(substr($req['description'], 0, 80)) ?>..."
                                </p>

                                <?php if ($req['status'] === 'completed'): ?>
                                    <?php
                                        $rev_check = $pdo->prepare("SELECT id FROM reviews WHERE client_id = ? AND designer_id = ? AND created_at >= ?");
                                        $rev_check->execute([$user_id, $req['designer_id'], $req['created_at']]);
                                        $already_reviewed = $rev_check->fetch();
                                    ?>
                                    
                                    <?php if ($already_reviewed): ?>
                                        <div class="alert-success" style="padding: 8px; border-radius: 8px; text-align: center; font-size: 0.875rem;">
                                            ★ Review submitted. Thank you!
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" action="submit_review.php" style="margin-top: 16px; border-top: 1px solid var(--border-light); padding-top: 16px;">
                                            <input type="hidden" name="designer_id" value="<?= $req['designer_id'] ?>">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <label style="display: block; font-size: 0.875rem; margin-bottom: 4px; font-weight: 500; color: var(--text-primary);">Leave a Review:</label>
                                            <select name="rating" class="search-input" style="width: 100%; padding: 8px; margin-bottom: 8px;" required>
                                                <option value="5">★★★★★ (5/5) Excellent</option>
                                                <option value="4">★★★★☆ (4/5) Good</option>
                                                <option value="3">★★★☆☆ (3/5) Average</option>
                                                <option value="2">★★☆☆☆ (2/5) Poor</option>
                                                <option value="1">★☆☆☆☆ (1/5) Terrible</option>
                                            </select>
                                            <textarea name="comment" class="search-input" style="width: 100%; padding: 8px; min-height: 60px; margin-bottom: 8px;" placeholder="Write your feedback..." required></textarea>
                                            <button type="submit" class="search-btn" style="width: 100%; padding: 8px;">Submit Review</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favorites Section -->
            <div style="margin-top: 48px;">
                <h3 style="margin-bottom: 16px;">Saved Designers</h3>
                <?php if (empty($my_favorites)): ?>
                    <div class="designer-card" style="text-align: center; padding: 40px;">
                        <p style="color: var(--text-muted);">You haven't saved any designers yet.</p>
                    </div>
                <?php else: ?>
                    <div class="grid-3">
                        <?php foreach ($my_favorites as $fav): ?>
                            <div class="designer-card" style="text-align: center; padding: 24px;">
                                <div class="designer-avatar" style="margin: 0 auto 12px; width: 60px; height: 60px; font-size: 1.5rem;">
                                    <?= strtoupper(substr($fav['full_name'], 0, 1)) ?>
                                </div>
                                <h4 style="margin-bottom: 4px; color: var(--text-primary);"><?= htmlspecialchars($fav['full_name']) ?></h4>
                                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 16px;"><?= htmlspecialchars($fav['location'] ?? 'Remote') ?></p>
                                <a href="designer_profile.php?id=<?= $fav['id'] ?>" class="search-btn" style="display: block; text-decoration: none; padding: 8px;">View Profile</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($role === 'designer'): ?>
            
            <!-- DESIGNER DASHBOARD -->
            <div class="grid-3" style="margin-top: 32px;">
                <div class="designer-card" style="padding: 24px; text-align: center;">
                    <h3 style="font-size: 2rem; color: var(--accent);"><?= $my_services_count ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 16px;">Active Services</p>
                    <a href="manage_services.php" class="search-btn" style="display: block; text-decoration: none; padding: 8px;">Manage Services</a>
                </div>
                <div class="designer-card" style="padding: 24px; text-align: center;">
                    <h3 style="font-size: 2rem; color: var(--accent);"><?= $my_portfolio_count ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 16px;">Portfolio Items</p>
                    <a href="manage_portfolio.php" class="search-btn" style="display: block; text-decoration: none; padding: 8px;">Manage Portfolio</a>
                </div>
                <div class="designer-card" style="padding: 24px; text-align: center;">
                    <h3 style="font-size: 2rem; color: var(--accent);"><?= count($my_requests) ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 16px;">Total Requests</p>
                    <a href="#requests" class="search-btn" style="display: block; text-decoration: none; padding: 8px;">View Requests</a>
                </div>
            </div>

            <div id="requests" style="margin-top: 48px;">
                <h3 style="margin-bottom: 16px;">Incoming Client Requests</h3>
                <?php if (empty($my_requests)): ?>
                    <div class="designer-card" style="text-align: center; padding: 40px;">
                        <p style="color: var(--text-muted);">No requests yet.</p>
                    </div>
                <?php else: ?>
                    <div class="grid-2">
                        <?php foreach ($my_requests as $req): ?>
                            <div class="designer-card">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                    <h4 style="font-size: 1.125rem; color: var(--text-primary);"><?= htmlspecialchars($req['service_title']) ?></h4>
                                    <span class="status-<?= $req['status'] === 'in_progress' ? 'in-progress' : ($req['status'] ?? 'pending') ?>" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px;">
                                        <?= ucfirst(str_replace('_', ' ', $req['status'] ?? 'pending')) ?>
                                    </span>
                                </div>
                                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 12px;">
                                    <strong style="color: var(--text-primary);">Client:</strong> <?= htmlspecialchars($req['client_name']) ?><br>
                                    <strong style="color: var(--text-primary);">Budget:</strong> $<?= number_format($req['budget']) ?><br>
                                    <strong style="color: var(--text-primary);">Date:</strong> <?= date('M d, Y', strtotime($req['created_at'])) ?><br>
                                    <strong style="color: var(--text-primary);">Payment:</strong> <?= htmlspecialchars($req['payment_method'] ?? 'N/A') ?>
                                </p>
                                <p style="font-size: 0.875rem; color: var(--text-muted); font-style: italic; margin-bottom: 16px;">
                                    "<?= htmlspecialchars(substr($req['description'], 0, 100)) ?>..."
                                </p>
                                <form method="POST" action="update_request_status.php" style="display: flex; gap: 8px;">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <select name="status" class="search-input" style="flex: 1; padding: 6px; font-size: 0.875rem;">
                                        <option value="pending" <?= $req['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="in_progress" <?= $req['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= $req['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $req['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="search-btn" style="padding: 6px 12px; font-size: 0.875rem;">Update</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>