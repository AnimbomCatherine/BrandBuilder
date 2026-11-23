<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$designer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($designer_id === 0) {
    header("Location: browse.php");
    exit;
}

$stmt = $pdo->prepare("SELECT u.id, u.full_name, u.email, dp.bio, dp.location, dp.hourly_rate, dp.starting_price 
                       FROM users u 
                       LEFT JOIN designer_profiles dp ON u.id = dp.user_id 
                       WHERE u.id = ? AND u.role = 'designer'");
$stmt->execute([$designer_id]);
$designer = $stmt->fetch();

if (!$designer) {
    header("Location: browse.php");
    exit;
}

$srv_stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ?");
$srv_stmt->execute([$designer_id]);
$services = $srv_stmt->fetchAll();

$port_stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ?");
$port_stmt->execute([$designer_id]);
$portfolio = $port_stmt->fetchAll();

$rev_stmt = $pdo->prepare("SELECT r.*, u.full_name as client_name FROM reviews r JOIN users u ON r.client_id = u.id WHERE r.designer_id = ? ORDER BY r.created_at DESC");
$rev_stmt->execute([$designer_id]);
$reviews = $rev_stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <!-- Designer Header -->
        <div class="designer-card" style="padding: 40px; display: flex; gap: 32px; align-items: flex-start; flex-wrap: wrap;">
            <div class="designer-avatar" style="width: 100px; height: 100px; font-size: 2.5rem;">
                <?= strtoupper(substr($designer['full_name'], 0, 1)) ?>
            </div>
            <div style="flex: 1;">
                <h1 style="margin-bottom: 8px; color: var(--text-primary);"><?= htmlspecialchars($designer['full_name']) ?> <span style="color:var(--accent);">✔</span></h1>
                <p style="color: var(--text-muted); margin-bottom: 16px;"><?= htmlspecialchars($designer['location'] ?? 'Remote') ?> · Freelancer</p>
                <p style="margin-bottom: 24px; max-width: 600px; color: var(--text-secondary);"><?= nl2br(htmlspecialchars($designer['bio'] ?? 'No bio provided.')) ?></p>
                <div style="display: flex; gap: 24px; color: var(--text-primary);">
                    <?php if ($designer['starting_price']): ?>
                        <div><strong>Starting Price:</strong> $<?= number_format($designer['starting_price']) ?></div>
                    <?php endif; ?>
                    <?php if ($designer['hourly_rate']): ?>
                        <div><strong>Hourly Rate:</strong> $<?= number_format($designer['hourly_rate']) ?>/hr</div>
                    <?php endif; ?>
                </div>
            </div>
            <div style="width: 100%; max-width: 200px;">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'client'): ?>
                    <a href="request_service.php?designer_id=<?= $designer['id'] ?>" class="search-btn" style="display: block; text-align: center; text-decoration: none; width: 100%; margin-bottom: 8px;">Request Service</a>
                    <a href="messages.php?with=<?= $designer['id'] ?>" class="search-btn" style="display: block; text-align: center; text-decoration: none; width: 100%; margin-bottom: 8px; background: var(--navy); color: var(--bg-primary);">Send Message</a>
                    <form method="POST" action="toggle_favorite.php" style="width: 100%;">
                        <input type="hidden" name="designer_id" value="<?= $designer['id'] ?>">
                        <button type="submit" class="search-btn" style="width: 100%; background: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-light);">
                            <?php 
                                $fav_check = $pdo->prepare("SELECT id FROM favorites WHERE client_id = ? AND designer_id = ?");
                                $fav_check->execute([$_SESSION['user_id'], $designer['id']]);
                                echo $fav_check->fetch() ? '★ Saved to Favorites' : '☆ Save to Favorites';
                            ?>
                        </button>
                    </form>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="search-btn" style="display: block; text-align: center; text-decoration: none; width: 100%; margin-bottom: 8px;">Login to Contact</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Services Section -->
        <div style="margin-top: 48px;">
            <h2 style="margin-bottom: 24px;">Services Offered</h2>
            <?php if (empty($services)): ?>
                <p style="color: var(--text-muted);">No services listed yet.</p>
            <?php else: ?>
                <div class="grid-3">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <h3><?= htmlspecialchars($service['title']) ?></h3>
                            <p><?= htmlspecialchars($service['description']) ?></p>
                            <div class="service-meta">
                                <span><?= htmlspecialchars($service['category']) ?></span>
                                <span>from $<?= number_format($service['starting_price']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Portfolio Section -->
        <div style="margin-top: 48px;">
            <h2 style="margin-bottom: 24px;">Portfolio</h2>
            <?php if (empty($portfolio)): ?>
                <p style="color: var(--text-muted);">No portfolio items uploaded yet.</p>
            <?php else: ?>
                <div class="grid-3">
                    <?php foreach ($portfolio as $item): ?>
                        <div style="background: var(--card-bg); border-radius: 16px; overflow: hidden; border: 1px solid var(--border-light);">
                            <?php $img_url = !empty($item['image_url']) ? e($item['image_url']) : 'https://via.placeholder.com/400x300?text=No+Image'; ?>
                            <img src="<?= $img_url ?>" alt="<?= e($item['title']) ?>" style="width: 100%; height: 200px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/400x300?text=Image+Not+Found'">
                            <div style="padding: 16px;">
                                <h4 style="font-size: 1rem; color: var(--text-primary);"><?= e($item['title']) ?></h4>
                                <?php if ($item['project_url']): ?>
                                    <a href="<?= e($item['project_url']) ?>" target="_blank" style="color: var(--accent); font-size: 0.875rem; font-weight: 600; margin-top: 8px; display: inline-block;">View Project →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Section -->
        <div style="margin-top: 48px;">
            <h2 style="margin-bottom: 24px;">Client Reviews</h2>
            <?php if (empty($reviews)): ?>
                <p style="color: var(--text-muted);">No reviews yet.</p>
            <?php else: ?>
                <div class="grid-2">
                    <?php foreach ($reviews as $review): ?>
                        <div class="testimonial-card">
                            <div class="rating" style="margin-bottom: 8px;">
                                <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                            </div>
                            <p class="testimonial-quote" style="font-size: 1rem;">"<?= htmlspecialchars($review['comment']) ?>"</p>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar" style="background: var(--navy);">
                                    <?= strtoupper(substr($review['client_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 style="font-size: 0.875rem; color: var(--text-primary);"><?= htmlspecialchars($review['client_name']) ?></h4>
                                    <p style="font-size: 0.75rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($review['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>