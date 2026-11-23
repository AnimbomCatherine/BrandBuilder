<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrandBuilder - Find Your Creative Partner</title>

        <!-- ANTI-FLASH: apply theme before CSS/body render -->
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && prefersDark)) {
                document.documentElement.classList.add('dark-mode');
                document.documentElement.style.backgroundColor = '#0F172A';
            } else {
                document.documentElement.classList.remove('dark-mode');
                document.documentElement.style.backgroundColor = '#F8FAFC'; /* Reset to light bg */
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=6">
    <link rel="stylesheet" href="assets/css/dark-mode.css?v=6">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="index.php" class="logo">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                Brand<span>Builder</span>
            </a>
            
            <button class="menu-toggle" id="menuToggle" aria-label="Open menu">☰</button>
            
            <nav class="nav-links" id="navLinks">
                <a href="index.php">Find Designers</a>
                <a href="browse.php">Browse</a>
                <a href="index.php#how-it-works">How it Works</a>

                <div style="flex: 1;"></div>

                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- PROFESSIONAL THEME TOGGLE -->
                    <button id="theme-toggle" class="theme-toggle-btn" type="button" aria-pressed="false" title="Toggle dark mode">
                        <span class="theme-toggle-icon">🌙</span>
                        <span class="theme-toggle-track">
                            <span class="theme-toggle-thumb"></span>
                        </span>
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="admin_dashboard.php" style="color: var(--accent); font-weight: 700;">Admin</a>
                        <?php endif; ?>
                        <a href="messages.php" style="font-weight: 600;">Messages</a>
                        <a href="notifications.php" style="font-weight: 600;">Alerts</a>
                        <a href="dashboard.php" class="btn-primary" style="padding: 10px 24px; font-size: 0.9rem; border-radius: 8px;">Dashboard</a>
                        <a href="logout.php" style="color: var(--text-muted); font-size: 0.9rem;">Logout</a>
                    <?php else: ?>
                        <a href="login.php" style="font-weight: 600;">Login</a>
                        <a href="register.php" class="btn-primary" style="padding: 10px 24px; font-size: 0.9rem; border-radius: 8px;">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main>
        <div class="container" style="padding-top: 20px;">
            <?php display_flash(); ?>
        </div>