<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strict Admin Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_designers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'designer'")->fetchColumn();
$total_requests = $pdo->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(budget) FROM service_requests WHERE status = 'completed'")->fetchColumn() ?? 0;
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Admin Dashboard</h2>
                <p>Platform overview and management.</p>
            </div>
            <a href="logout.php" class="search-btn" style="text-decoration: none; padding: 10px 20px; background: var(--danger);">Logout</a>
        </div>

        <!-- Stats Grid -->
        <div class="grid-4" style="margin-bottom: 40px;">
            <div class="designer-card" style="text-align: center;">
                <h3 style="font-size: 2rem; color: var(--text-primary);"><?= $total_users ?></h3>
                <p style="color: var(--text-muted);">Total Users</p>
            </div>
            <div class="designer-card" style="text-align: center;">
                <h3 style="font-size: 2rem; color: var(--text-primary);"><?= $total_designers ?></h3>
                <p style="color: var(--text-muted);">Designers</p>
            </div>
            <div class="designer-card" style="text-align: center;">
                <h3 style="font-size: 2rem; color: var(--text-primary);"><?= $total_requests ?></h3>
                <p style="color: var(--text-muted);">Total Requests</p>
            </div>
            <div class="designer-card" style="text-align: center;">
                <h3 style="font-size: 2rem; color: var(--accent);">$<?= number_format($total_revenue) ?></h3>
                <p style="color: var(--text-muted);">Completed Value</p>
            </div>
        </div>

        <!-- Management Links -->
        <div class="grid-3">
            <a href="admin_users.php" class="designer-card" style="text-decoration: none; color: inherit; text-align: center; padding: 40px;">
                <h3 style="color: var(--text-primary); margin-bottom: 8px;">Manage Users</h3>
                <p style="color: var(--text-muted);">View, verify, or remove accounts.</p>
            </a>
            <a href="admin_requests.php" class="designer-card" style="text-decoration: none; color: inherit; text-align: center; padding: 40px;">
                <h3 style="color: var(--text-primary); margin-bottom: 8px;">Manage Requests</h3>
                <p style="color: var(--text-muted);">Oversee all platform projects.</p>
            </a>
            <a href="admin_categories.php" class="designer-card" style="text-decoration: none; color: inherit; text-align: center; padding: 40px;">
                <h3 style="color: var(--text-primary); margin-bottom: 8px;">Categories</h3>
                <p style="color: var(--text-muted);">Manage service categories.</p>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>