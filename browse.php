<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT u.id, u.full_name, dp.bio, dp.location, dp.starting_price, 
               AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
        FROM users u
        LEFT JOIN designer_profiles dp ON u.id = dp.user_id
        LEFT JOIN reviews r ON u.id = r.designer_id
        WHERE u.role = 'designer'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE ? OR dp.bio LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($location)) {
    $sql .= " AND dp.location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($category)) {
    $sql .= " AND u.id IN (SELECT user_id FROM services WHERE category = ?)";
    $params[] = $category;
}

$sql .= " GROUP BY u.id ORDER BY avg_rating DESC, u.full_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$designers = $stmt->fetchAll();

$cat_stmt = $pdo->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section" style="background: var(--navy); padding-bottom: 40px;">
    <div class="container">
        <h2 style="color: white; margin-bottom: 24px;">Browse Designers</h2>
        <form method="GET" action="browse.php" class="search-container" style="flex-direction: column; gap: 12px; background: var(--card-bg);">
            <div style="display: flex; gap: 12px; width: 100%; flex-wrap: wrap;">
                <input type="text" name="search" class="search-input" placeholder="Search by name or skill..." value="<?= htmlspecialchars($search) ?>" style="flex: 1;">
                <input type="text" name="location" class="search-input" placeholder="Location..." value="<?= htmlspecialchars($location) ?>" style="flex: 1;">
            </div>
            <div style="display: flex; gap: 12px; width: 100%; flex-wrap: wrap;">
                <select name="category" class="search-input" style="flex: 1;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="search-btn" style="width: auto; padding: 10px 30px;">Filter</button>
            </div>
        </form>
    </div>
</section>

<section class="section" style="background: var(--off-white);">
    <div class="container">
        <?php if (empty($designers)): ?>
            <div style="text-align: center; padding: 40px;">
                <h3 style="color: var(--text-primary);">No designers found</h3>
                <p style="color: var(--text-muted);">Try adjusting your search or filters.</p>
            </div>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach ($designers as $designer): ?>
                    <div class="designer-card">
                        <div class="designer-header">
                            <div class="designer-avatar">
                                <?= strtoupper(substr($designer['full_name'], 0, 1)) ?>
                            </div>
                            <div class="designer-info">
                                <h3 style="color: var(--text-primary);"><?= htmlspecialchars($designer['full_name']) ?> <span style="color:var(--accent);">✔</span></h3>
                                <p style="color: var(--text-muted);"><?= htmlspecialchars($designer['location'] ?? 'Remote') ?></p>
                            </div>
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 16px; height: 40px; overflow: hidden;">
                            <?= htmlspecialchars(substr($designer['bio'] ?? 'No bio available.', 0, 100)) ?>...
                        </p>
                        <div class="designer-footer">
                            <div class="rating">
                                <?php 
                                    $rating = round($designer['avg_rating'] ?? 0, 1);
                                    echo str_repeat('★', floor($rating)) . str_repeat('☆', 5 - floor($rating)); 
                                ?> 
                                <?= $rating ?> (<?= $designer['review_count'] ?>)
                            </div>
                            <div class="price">
                                <small>FROM</small>
                                $<?= number_format($designer['starting_price'] ?? 0) ?>
                            </div>
                        </div>
                        <div style="margin-top: 16px; display: flex; gap: 8px;">
                            <a href="designer_profile.php?id=<?= $designer['id'] ?>" class="search-btn" style="flex: 1; text-align: center; display: block; text-decoration: none;">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>