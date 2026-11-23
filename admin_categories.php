<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cat'])) {
    $name = trim($_POST['category_name']);
    if (!empty($name)) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) UNIQUE)");
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: admin_categories.php");
    exit;
}

// Handle Delete Category
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: admin_categories.php");
    exit;
}

// Fetch Categories
$pdo->exec("CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) UNIQUE)");
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Manage Categories</h2>
            <a href="admin_dashboard.php" style="color: var(--accent); font-weight: 600;">← Back to Admin</a>
        </div>

        <div class="grid-2">
            <!-- Add Category Form -->
            <div class="designer-card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">Add New Category</h3>
                <form method="POST" action="" style="display: flex; gap: 8px;">
                    <input type="text" name="category_name" class="search-input" style="flex: 1; padding: 10px;" placeholder="e.g. Logo Design, Web Design" required>
                    <button type="submit" name="add_cat" class="search-btn" style="padding: 10px 20px;">Add</button>
                </form>
            </div>

            <!-- Category List -->
            <div class="designer-card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">Existing Categories</h3>
                <?php if (empty($categories)): ?>
                    <p style="color: var(--text-muted);">No categories added yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($categories as $cat): ?>
                            <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                                <span style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($cat['name']) ?></span>
                                <a href="admin_categories.php?delete_id=<?= $cat['id'] ?>" style="color: var(--danger); font-size: 0.875rem;" onclick="return confirm('Delete category?')">Delete</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>