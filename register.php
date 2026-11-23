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
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        set_flash('error', 'All fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Invalid email format.');
    } elseif ($password !== $confirm_password) {
        set_flash('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters long.');
    } elseif (!in_array($role, ['client', 'designer'])) {
        set_flash('error', 'Invalid role selected.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            set_flash('error', 'Email is already registered.');
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $password_hash, $role])) {
                set_flash('success', 'Registration successful! You can now login.');
                header("Location: login.php");
                exit;
            } else {
                set_flash('error', 'Something went wrong. Please try again.');
            }
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section style="display: flex; min-height: 80vh; align-items: center; justify-content: center; padding: 40px 24px; background: var(--bg-primary);">
    <div style="display: flex; max-width: 1100px; width: 100%; background: var(--card-bg); border-radius: 24px; overflow: hidden; box-shadow: var(--shadow-xl); border: 1px solid var(--border-light);">
        
        <!-- Left Side: Visuals/Branding -->
        <div style="flex: 1; background: linear-gradient(135deg, #0A192F 0%, #112240 100%); padding: 60px; display: flex; flex-direction: column; justify-content: center; position: relative;">
            <h2 style="color: #FFFFFF; font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 24px;">Join the creative marketplace.</h2>
            <p style="color: #CBD5E1; font-size: 1.1rem; line-height: 1.6;">Whether you are looking to hire top talent or showcase your portfolio, BrandBuilder connects you with the right people.</p>
            <ul style="margin-top: 32px; color: #FFFFFF; display: flex; flex-direction: column; gap: 16px; list-style: none; padding: 0;">
                <li style="display: flex; align-items: center; gap: 12px;"><span style="color: var(--accent);">✔</span> Verified Professionals</li>
                <li style="display: flex; align-items: center; gap: 12px;"><span style="color: var(--accent);">✔</span> Secure Payments</li>
                <li style="display: flex; align-items: center; gap: 12px;"><span style="color: var(--accent);">✔</span> Transparent Pricing</li>
            </ul>
        </div>

        <!-- Right Side: Form -->
        <div style="flex: 1; padding: 60px;">
            <h2 style="font-size: 2rem; color: var(--text-primary); margin-bottom: 8px; font-weight: 800;">Create an Account</h2>
            <p style="color: var(--text-muted); margin-bottom: 32px;">Get started in less than a minute.</p>

            <form method="POST" action="">
                <div style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Full Name</label>
                    <input type="text" name="full_name" class="search-input" placeholder="John Doe" style="width:100%; padding: 14px 16px; border-radius: 10px;" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Email Address</label>
                    <input type="email" name="email" class="search-input" placeholder="you@company.com" style="width:100%; padding: 14px 16px; border-radius: 10px;" required>
                </div>
                <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                    <div style="flex: 1;">
                        <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Password</label>
                        <input type="password" name="password" class="search-input" placeholder="••••••••" style="width:100%; padding: 14px 16px; border-radius: 10px;" required>
                    </div>
                    <div style="flex: 1;">
                        <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Confirm</label>
                        <input type="password" name="confirm_password" class="search-input" placeholder="••••••••" style="width:100%; padding: 14px 16px; border-radius: 10px;" required>
                    </div>
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">I want to:</label>
                    <select name="role" class="search-input" style="width:100%; padding: 14px 16px; border-radius: 10px; cursor: pointer;">
                        <option value="client">Hire Designers (Client)</option>
                        <option value="designer">Find Work (Designer)</option>
                    </select>
                </div>
                <button type="submit" class="search-btn" style="width:100%; padding: 16px; font-size: 1rem;">Sign Up</button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; font-size: 0.95rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--accent); font-weight: 700;">Log in</a>
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>