<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'designer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_portfolio'])) {
    $title = trim($_POST['title']);
    $image_url = trim($_POST['image_url']);
    $project_url = trim($_POST['project_url']);

    if (empty($title) || empty($image_url)) {
        $error = "Title and Image URL are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, image_url, project_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $title, $image_url, $project_url])) {
            $success = "Portfolio item added successfully!";
        } else {
            $error = "Failed to add portfolio item.";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM portfolio_items WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$delete_id, $user_id])) {
        $success = "Portfolio item deleted.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$portfolio = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Manage Portfolio</h2>
            <a href="dashboard.php" style="color: var(--accent); font-weight: 600;">← Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="alert-error" style="padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success" style="padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Add Portfolio Form -->
            <div class="designer-card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">Add New Project</h3>
                <form method="POST" action="">
                    <div style="margin-bottom: 12px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Project Title</label>
                        <input type="text" name="title" class="search-input" style="width:100%; padding: 10px;" required>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Image URL (e.g., https://via.placeholder.com/400)</label>
                        <input type="text" name="image_url" class="search-input" style="width:100%; padding: 10px;" required>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Project Link (Optional)</label>
                        <input type="text" name="project_url" class="search-input" style="width:100%; padding: 10px;">
                    </div>
                    <button type="submit" name="add_portfolio" class="search-btn" style="width:100%;">Add Project</button>
                </form>
            </div>

            <!-- Existing Portfolio List -->
            <div>
                <h3 style="margin-bottom: 16px;">Your Projects</h3>
                <?php if (empty($portfolio)): ?>
                    <p style="color: var(--text-muted);">You haven't added any portfolio items yet.</p>
                <?php else: ?>
                    <?php foreach ($portfolio as $item): ?>
                        <div class="designer-card" style="padding: 12px; margin-bottom: 12px; display: flex; gap: 16px; align-items: center;">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Thumb" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                            <div style="flex: 1;">
                                <h4 style="font-size: 1rem; color: var(--text-primary);"><?= htmlspecialchars($item['title']) ?></h4>
                                <?php if ($item['project_url']): ?>
                                    <a href="<?= htmlspecialchars($item['project_url']) ?>" target="_blank" style="font-size: 0.75rem; color: var(--accent);">View Link</a>
                                <?php endif; ?>
                            </div>
                            <a href="manage_portfolio.php?delete_id=<?= $item['id'] ?>" style="color: var(--danger); font-weight: 600; font-size: 0.875rem;" onclick="return confirm('Delete this item?')">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>