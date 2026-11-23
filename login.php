<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        set_flash('error', 'Please enter both email and password.');
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            set_flash('error', 'Invalid email or password.');
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section style="display: flex; min-height: 80vh; align-items: center; justify-content: center; padding: 40px 24px; background: var(--bg-primary);">
    <div style="display: flex; max-width: 1000px; width: 100%; background: var(--card-bg); border-radius: 24px; overflow: hidden; box-shadow: var(--shadow-xl); border: 1px solid var(--border-light);">
        
        <!-- Left Side: Visuals/Branding -->
        <div style="flex: 1; background: linear-gradient(135deg, #0A192F 0%, #112240 100%); padding: 60px; display: flex; flex-direction: column; justify-content: center; position: relative;">
            <h2 style="color: #FFFFFF; font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 24px;">Welcome back to BrandBuilder.</h2>
            <p style="color: #CBD5E1; font-size: 1.1rem; line-height: 1.6;">Log in to manage your projects, message designers, and find the perfect creative partner for your brand.</p>
            <div style="margin-top: 40px; display: flex; gap: 16px;">
                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent);">✔</div>
                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent);">★</div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div style="flex: 1; padding: 60px;">
            <h2 style="font-size: 2rem; color: var(--text-primary); margin-bottom: 8px; font-weight: 800;">Log In</h2>
            <p style="color: var(--text-muted); margin-bottom: 32px;">Enter your credentials to access your dashboard.</p>

            <form method="POST" action="">
                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Email Address</label>
                    <input type="email" name="email" class="search-input" placeholder="you@company.com" style="width:100%; padding: 14px 16px; border-radius: 10px;" required>
                </div>
                <div style="margin-bottom: 32px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Password</label>
                    <input type="password" name="password" class="search-input" placeholder="••••••••" style="width:100%; padding: 14px 16px; border-radius: 10px;" required>
                </div>
                <button type="submit" class="search-btn" style="width:100%; padding: 16px; font-size: 1rem;">Log In</button>
            </form>
            
            <p style="text-align: center; margin-top: 32px; font-size: 0.95rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="color: var(--accent); font-weight: 700;">Sign up for free</a>
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>