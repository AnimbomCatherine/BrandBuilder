<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$designer_id = isset($_GET['designer_id']) ? (int)$_GET['designer_id'] : 0;
if ($designer_id === 0) {
    header("Location: browse.php");
    exit;
}

$stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ? AND role = 'designer'");
$stmt->execute([$designer_id]);
$designer = $stmt->fetch();

if (!$designer) {
    header("Location: browse.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_title = trim($_POST['service_title']);
    $budget = (float)$_POST['budget'];
    $description = trim($_POST['description']);
    $payment_method = $_POST['payment_method'];

    if (empty($service_title) || empty($description)) {
        set_flash('error', 'Please fill in all required fields.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO service_requests (client_id, designer_id, service_title, budget, description, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $designer_id, $service_title, $budget, $description, $payment_method])) {
            set_flash('success', 'Request sent successfully! The designer will contact you soon.');
            header("Location: dashboard.php");
            exit;
        } else {
            set_flash('error', 'Failed to send request. Please try again.');
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container" style="max-width: 600px;">
        <div class="designer-card" style="padding: 40px;">
            <h2 style="margin-bottom: 8px;">Request a Service</h2>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Sending a request to <strong style="color: var(--text-primary);"><?= htmlspecialchars($designer['full_name']) ?></strong></p>

            <form method="POST" action="">
                <div style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--text-primary);">Service Title</label>
                    <input type="text" name="service_title" class="search-input" style="width:100%; padding: 10px 14px;" placeholder="e.g. Logo Design" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--text-primary);">Budget ($)</label>
                    <input type="number" name="budget" class="search-input" style="width:100%; padding: 10px 14px;" placeholder="e.g. 500" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--text-primary);">Payment Method</label>
                    <select name="payment_method" class="search-input" style="width:100%; padding: 10px 14px;">
                        <option value="Credit Card">Credit Card</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--text-primary);">Project Description</label>
                    <textarea name="description" class="search-input" style="width:100%; padding: 10px 14px; min-height: 120px;" placeholder="Describe your project requirements..." required></textarea>
                </div>
                <button type="submit" class="search-btn" style="width:100%;">Send Request</button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>