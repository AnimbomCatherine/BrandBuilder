<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Fetch Statistics
$total_designers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'designer'")->fetchColumn();
$total_projects = $pdo->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();
$avg_rating = $pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn();
$avg_rating = $avg_rating ? round($avg_rating, 1) : '0.0';

// 2. Fetch Featured Designers (Top 2 by rating)
$featured_sql = "SELECT u.id, u.full_name, dp.location, dp.starting_price, 
                        AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                 FROM users u
                 LEFT JOIN designer_profiles dp ON u.id = dp.user_id
                 LEFT JOIN reviews r ON u.id = r.designer_id
                 WHERE u.role = 'designer' AND u.is_verified = 1
                 GROUP BY u.id
                 ORDER BY avg_rating DESC, review_count DESC
                 LIMIT 2";
$featured_designers = $pdo->query($featured_sql)->fetchAll();

// 3. Fetch Popular Services (Count of services per category)
$services_sql = "SELECT category, COUNT(*) as project_count, MIN(starting_price) as min_price 
                 FROM services 
                 GROUP BY category 
                 ORDER BY project_count DESC 
                 LIMIT 3";
$popular_services = $pdo->query($services_sql)->fetchAll();

// 4. Fetch Top Rated Designers (Leaderboard)
$leaderboard_sql = "SELECT u.id, u.full_name, dp.location, dp.starting_price, 
                           AVG(r.rating) as avg_rating
                    FROM users u
                    LEFT JOIN designer_profiles dp ON u.id = dp.user_id
                    LEFT JOIN reviews r ON u.id = r.designer_id
                    WHERE u.role = 'designer' AND u.is_verified = 1
                    GROUP BY u.id
                    ORDER BY avg_rating DESC
                    LIMIT 4";
$top_designers = $pdo->query($leaderboard_sql)->fetchAll();

// 5. Fetch Latest Reviews
$reviews_sql = "SELECT r.rating, r.comment, c.full_name as client_name, c.role as client_role 
                FROM reviews r 
                JOIN users c ON r.client_id = c.id 
                ORDER BY r.created_at DESC 
                LIMIT 2";
$latest_reviews = $pdo->query($reviews_sql)->fetchAll();

// 6. Fetch Categories for Search Dropdown
$categories = $pdo->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Find your brand's<br>creative partner</h1>
        <div class="search-container">
    <form method="GET" action="browse.php" style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
        <div style="display: flex; gap: 12px; width: 100%; flex-wrap: wrap;">
            <!-- Increased flex ratio and added min-width -->
            <input type="text" name="search" class="search-input" 
                   placeholder="What do you need? e.g. logo" 
                   style="flex: 3; min-width: 280px; padding: 16px 20px;">
            
            <select name="location" class="filter-select" style="flex: 1; min-width: 140px; padding: 16px;">
                <option value="">Anywhere</option>
                <option value="Remote">Remote</option>
                <option value="New York">New York</option>
                <option value="London">London</option>
                <option value="Milan">Milan</option>
            </select>
            
            <select name="category" class="filter-select" style="flex: 1; min-width: 140px; padding: 16px;">
                <option value="">All services</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="search-btn" style="flex: 0 0 auto; padding: 16px 32px;">Search</button>
        </div>
    </form>
</div>
</section>

<!-- Statistics -->
<section class="stats-section">
    <div class="stat-item">
        <h3><?= $total_designers > 0 ? number_format($total_designers) . '+' : 'Join Us' ?></h3>
        <p>Verified professionals</p>
    </div>
    <div class="stat-item">
        <h3><?= $total_projects > 0 ? number_format($total_projects) . '+' : 'Be the first' ?></h3>
        <p>Projects delivered</p>
    </div>
    <div class="stat-item">
        <h3><?= ($avg_rating > 0) ? $avg_rating . '/5' : 'New Platform' ?></h3>
        <p>Average client rating</p>
    </div>
</section>

<!-- Featured Designers -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p style="color:var(--accent); font-weight:600; font-size:0.875rem; margin-bottom:4px;">FEATURED</p>
            <h2>Featured designers this month</h2>
            <p>Hand-picked studios and freelancers with consistently excellent client outcomes.</p>
        </div>
        <?php if (empty($featured_designers)): ?>
            <p style="color: var(--text-muted);">No featured designers available yet.</p>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach ($featured_designers as $designer): ?>
                    <div class="designer-card">
                        <div class="designer-header">
                            <div class="designer-avatar">
                                <?= strtoupper(substr($designer['full_name'], 0, 1)) ?>
                            </div>
                            <div class="designer-info">
                                <h3 style="color: var(--text-dark);"><?= htmlspecialchars($designer['full_name']) ?> <span style="color:var(--accent);">✔</span></h3>
                                <p style="color: var(--text-muted);"><?= htmlspecialchars($designer['location'] ?? 'Remote') ?> · Freelancer</p>
                            </div>
                        </div>
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
                        <div style="margin-top: 16px;">
                            <a href="designer_profile.php?id=<?= $designer['id'] ?>" class="search-btn" style="display: block; text-align: center; text-decoration: none;">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Popular Branding Services -->
<section class="section" style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <h2>Popular branding services</h2>
            <p>Explore our most requested service categories.</p>
        </div>
        <?php if (empty($popular_services)): ?>
            <p style="color: var(--text-muted);">No services listed yet.</p>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($popular_services as $service): ?>
                    <div class="service-card">
                        <div>
                            <h3><?= htmlspecialchars($service['category']) ?></h3>
                            <p>Professional <?= strtolower(htmlspecialchars($service['category'])) ?> services tailored to your brand.</p>
                            <div class="service-meta">
                                <span><?= $service['project_count'] ?> experts</span>
                                <span>from $<?= number_format($service['min_price']) ?></span>
                            </div>
                        </div>
                        <a href="browse.php?category=<?= urlencode($service['category']) ?>" class="service-link">Browse experts →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How BrandBuilder Works -->
<section class="section how-it-works" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>How BrandBuilder works</h2>
            <p>Your path to finding the perfect creative partner.</p>
        </div>
        <div class="grid-2">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Tell us what you need</h3>
                <p style="color:var(--text-muted); margin-top:8px;">Search by service, budget and location, or post a brief and let matching studios come to you.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Compare real work</h3>
                <p style="color:var(--text-muted); margin-top:8px;">Side-by-side portfolios, transparent starting prices, verified reviews and live availability.</p>
            </div>
        </div>
    </div>
</section>

<!-- Top Rated Designers (Leaderboard) -->
<section class="section">
    <div class="container">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Top rated designers</h2>
            <a href="browse.php" style="color:var(--accent); font-weight:600;">See all designers →</a>
        </div>
        <div>
            <?php if (empty($top_designers)): ?>
                <p style="color: var(--text-muted);">No designers ranked yet.</p>
            <?php else: ?>
                <?php foreach ($top_designers as $index => $designer): ?>
                    <div class="leaderboard-item">
                        <div class="rank"><?= $index + 1 ?></div>
                        <div class="leaderboard-avatar"><?= strtoupper(substr($designer['full_name'], 0, 1)) ?></div>
                        <div class="leaderboard-details">
                            <h4 style="color: var(--text-dark);"><?= htmlspecialchars($designer['full_name']) ?></h4>
                            <p style="color: var(--text-muted);"><?= htmlspecialchars($designer['location'] ?? 'Remote') ?> · from $<?= number_format($designer['starting_price'] ?? 0) ?></p>
                        </div>
                        <a href="designer_profile.php?id=<?= $designer['id'] ?>" class="leaderboard-price" style="text-decoration: none; color: var(--text-dark);">→</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Businesses Choose BrandBuilder -->
<section class="section why-us">
    <div class="container">
        <div class="section-header">
            <p style="color:var(--accent); font-weight:600; font-size:0.875rem; margin-bottom:4px;">WHY BRANDBUILDER</p>
            <h2>Why businesses choose BrandBuilder</h2>
        </div>
        <div class="grid-2">
            <div class="feature-item">
                <div class="feature-icon">✔</div>
                <div class="feature-text">
                    <h4>Every profile is reviewed</h4>
                    <p>Verified badges are only issued after we confirm identity, ownership of portfolio work and past client references.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">💰</div>
                <div class="feature-text">
                    <h4>Pricing you can see upfront</h4>
                    <p>Published starting prices mean you can compare options without needing a discovery call to learn a number.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>What our clients say</h2>
        </div>
        <?php if (empty($latest_reviews)): ?>
            <!-- Zero-state message -->
            <div style="text-align: center; padding: 40px; background: var(--white); border-radius: var(--radius-card); border: 1px dashed var(--border-light);">
                <p style="font-size: 1.1rem; color: var(--text-muted);">Be the first to leave a review! Hire a designer and share your experience.</p>
            </div>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach ($latest_reviews as $review): ?>
                    <div class="testimonial-card">
                        <div class="rating" style="margin-bottom: 8px; color: var(--accent);">
                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                        </div>
                        <p class="testimonial-quote">"<?= htmlspecialchars($review['comment']) ?>"</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar" style="background: var(--navy);">
                                <?= strtoupper(substr($review['client_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h4 style="font-size:1rem; color: var(--text-dark);"><?= htmlspecialchars($review['client_name']) ?></h4>
                                <p style="font-size:0.875rem; color: var(--text-muted);">Client</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Final CTA -->
<section class="container">
    <div class="cta-section">
        <h2>Ready to find your brand's creative partner?</h2>
        <p>Create a free account to save favorites, send service requests and manage every brand project in one dashboard.</p>
        <div class="cta-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Get started free</a>
            <?php endif; ?>
            <a href="browse.php" class="btn-secondary" style="text-decoration: none; display: inline-block;">Browse designers</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>