<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $req_id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE service_requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $req_id]);
    header("Location: admin_requests.php");
    exit;
}

// Fetch all requests
$sql = "SELECT sr.*, c.full_name as client_name, d.full_name as designer_name 
        FROM service_requests sr 
        JOIN users c ON sr.client_id = c.id 
        JOIN users d ON sr.designer_id = d.id 
        ORDER BY sr.created_at DESC";
$stmt = $pdo->query($sql);
$requests = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Manage Requests</h2>
            <a href="admin_dashboard.php" style="color: var(--accent); font-weight: 600;">← Back to Admin</a>
        </div>

        <div class="designer-card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--bg-tertiary); border-bottom: 1px solid var(--border-light);">
                    <tr>
                        <th style="padding: 16px; font-size: 0.875rem;">Title</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Client</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Designer</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Budget</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Status</th>
                        <th style="padding: 16px; font-size: 0.875rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr style="border-bottom: 1px solid var(--border-light);">
                            <td style="padding: 16px; font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($req['service_title']) ?></td>
                            <td style="padding: 16px; color: var(--text-muted);"><?= htmlspecialchars($req['client_name']) ?></td>
                            <td style="padding: 16px; color: var(--text-muted);"><?= htmlspecialchars($req['designer_name']) ?></td>
                            <td style="padding: 16px; color: var(--text-primary);">$<?= number_format($req['budget']) ?></td>
                            <td style="padding: 16px;">
                                <span class="status-<?= $req['status'] === 'in_progress' ? 'in-progress' : $req['status'] ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;"><?= ucfirst($req['status']) ?></span>
                            </td>
                            <td style="padding: 16px;">
                                <form method="POST" style="display: flex; gap: 4px;">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <select name="status" class="search-input" style="padding: 4px; font-size: 0.75rem;">
                                        <option value="pending" <?= $req['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="in_progress" <?= $req['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= $req['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $req['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="search-btn" style="padding: 4px 8px; font-size: 0.75rem;">Set</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>