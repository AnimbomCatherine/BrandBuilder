<?php
// Escape output to prevent XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Set a flash message
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success' or 'error'
        'message' => $message
    ];
}

// Display and clear flash message
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        
        $bg_color = $type === 'success' ? '#D1FAE5' : '#FEE2E2';
        $text_color = $type === 'success' ? '#065F46' : '#991B1B';
        
        echo "<div style='background: {$bg_color}; color: {$text_color}; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;'>{$message}</div>";
        
        unset($_SESSION['flash']);
    }
}
?>