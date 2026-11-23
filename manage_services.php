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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $starting_price = (float)$_POST['starting_price'];
    $category = trim($_POST['category']);

    if (empty($title) || empty($description) || empty($category)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (user_id, title, description, starting_price, category) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $title, $description, $starting_price, $category])) {
            $success = "Service added successfully!";
        } else {
            $error = "Failed to add service.";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$delete_id, $user_id])) {
        $success = "Service deleted.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$services = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Manage Services</h2>
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
            <!-- Add Service Form -->
            <div class="designer-card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">Add New Service</h3>
                <form method="POST" action="">
                    <div style="margin-bottom: 12px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Service Title</label>
                        <input type="text" name="title" class="search-input" style="width:100%; padding: 10px;" required>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Category</label>
                        <input type="text" name="category" class="search-input" style="width:100%; padding: 10px;" placeholder="e.g. Logo Design, Web Design" required>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Starting Price ($)</label>
                        <input type="number" name="starting_price" class="search-input" style="width:100%; padding: 10px;" required>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom: 4px; font-size: 0.875rem; color: var(--text-primary);">Description</label>
                        <textarea name="description" class="search-input" style="width:100%; padding: 10px; min-height: 80px;" required></textarea>
                    </div>
                    <button type="submit" name="add_service" class="search-btn" style="width:100%;">Add Service</button>
                </form>
            </div>

            <!-- Existing Services List -->
            <div>
                <h3 style="margin-bottom: 16px;">Your Services</h3>
                <?php if (empty($services)): ?>
                    <p style="color: var(--text-muted);">You haven't added any services yet.</p>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="designer-card" style="padding: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="color: var(--text-primary);"><?= htmlspecialchars($service['title']) ?></h4>
                                <p style="font-size: 0.875rem; color: var(--text-muted);"><?= htmlspecialchars($service['category']) ?> · $<?= number_format($service['starting_price']) ?></p>
                            </div>
                            <a href="manage_services.php?delete_id=<?= $service['id'] ?>" style="color: var(--danger); font-weight: 600;" onclick="return confirm('Delete this service?')">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>