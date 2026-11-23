<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $message_text = trim($_POST['message']);

    if (!empty($message_text)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $receiver_id, $message_text]);
        
        // Notification
        $notif_msg = "New message from " . $_SESSION['full_name'];
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, 'messages.php?with=" . $receiver_id . "')");
        $notif_stmt->execute([$receiver_id, $notif_msg]);
        
        header("Location: messages.php?with=" . $receiver_id);
        exit;
    }
}

// Fetch conversations with the last message and unread count
$conv_sql = "
    SELECT 
        u.id as contact_id, 
        u.full_name, 
        u.role,
        (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = FALSE) as unread_count
    FROM users u
    WHERE u.id IN (
        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
    )
    ORDER BY last_time DESC
";
$conv_stmt = $pdo->prepare($conv_sql);
$conv_stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $conv_stmt->fetchAll();

// Get active chat partner
$active_partner_id = isset($_GET['with']) ? (int)$_GET['with'] : (count($conversations) > 0 ? $conversations[0]['contact_id'] : 0);

$partner_info = null;
$chat_messages = [];

if ($active_partner_id > 0) {
    $p_stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE id = ?");
    $p_stmt->execute([$active_partner_id]);
    $partner_info = $p_stmt->fetch();

    // Mark messages as read
    $read_stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ?");
    $read_stmt->execute([$active_partner_id, $user_id]);

    // Fetch chat history
    $msg_stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $msg_stmt->execute([$user_id, $active_partner_id, $active_partner_id, $user_id]);
    $chat_messages = $msg_stmt->fetchAll();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<style>
    /* WhatsApp-style CSS with Theme Support */
    .chat-container {
        display: flex;
        height: calc(100vh - 200px);
        min-height: 500px;
        background: var(--white);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
    }
    .chat-sidebar {
        width: 350px;
        border-right: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        background: var(--white);
    }
    .chat-sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-light);
        background: var(--off-white);
    }
    .chat-list {
        flex: 1;
        overflow-y: auto;
    }
    .chat-list-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid var(--border-light);
    }
    .chat-list-item:hover, .chat-list-item.active {
        background: var(--off-white);
    }
    .chat-list-item.active {
        border-left: 4px solid var(--accent);
    }
    .chat-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--navy);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }
    .chat-preview {
        flex: 1;
        min-width: 0;
    }
    .chat-preview h4 {
        font-size: 1rem;
        margin-bottom: 4px;
        color: var(--text-dark);
    }
    .chat-preview p {
        font-size: 0.85rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .unread-badge {
        background: var(--accent);
        color: #0A192F;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 700;
    }
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--off-white); /* Dynamic background */
    }
    .chat-main-header {
        padding: 16px 24px;
        background: var(--white);
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .chat-messages {
        flex: 1;
        padding: 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: var(--off-white);
        /* Subtle dot pattern that works in both modes */
        background-image: radial-gradient(var(--border-light) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .message-bubble {
        max-width: 70%;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 0.95rem;
        position: relative;
        box-shadow: var(--shadow-sm);
        line-height: 1.4;
    }
    .message-sent {
    align-self: flex-end;
    background: var(--success-bg);
    color: var(--success-text);
    border-bottom-right-radius: 0;
    }
    .message-received {
        align-self: flex-start;
        background: var(--card-bg);
        color: var(--text-primary);
        border-bottom-left-radius: 0;
        border: 1px solid var(--border-light);
    }
    .message-time {
    font-size: 0.65rem;
    color: var(--text-muted);
    ...
    }
    .chat-input-area {
        padding: 16px 24px;
        background: var(--white);
        border-top: 1px solid var(--border-light);
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .chat-input-area input {
        flex: 1;
        padding: 14px 20px;
        border-radius: 24px;
        border: 1px solid var(--border-light);
        background: var(--off-white);
        outline: none;
        color: var(--text-dark);
    }
    .chat-input-area button {
        background: var(--accent);
        color: #0A192F;
        border: none;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: transform 0.2s;
    }
    .chat-input-area button:hover {
        transform: scale(1.05);
    }

    /* DARK MODE OVERRIDES FOR CHAT */
    body.dark-mode .message-sent {
        background: #005c4b; /* WhatsApp Dark Green */
        color: #e9edef;
    }
    body.dark-mode .message-received {
        background: #202c33; /* Dark Gray */
        color: #e9edef;
        border-color: #2a3942;
    }
    body.dark-mode .message-time {
        color: #8696a0;
    }
    body.dark-mode .chat-main-header {
        background: #202c33;
    }
    body.dark-mode .chat-main-header h4 {
        color: #e9edef;
    }
    body.dark-mode .chat-input-area {
        background: #202c33;
        border-top: 1px solid #2a3942;
    }
    body.dark-mode .chat-input-area input {
        background: #2a3942;
        border-color: #2a3942;
        color: #e9edef;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .chat-container {
            flex-direction: column;
            height: auto;
        }
        .chat-sidebar {
            width: 100%;
            max-height: 300px;
        }
        .chat-main {
            height: 500px;
        }
        .message-bubble {
            max-width: 85%;
        }
    }
</style>

<section class="section" style="padding-top: 20px;">
    <div class="container">
        <div class="chat-container">
            
            <!-- Sidebar: Contact List -->
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h3 style="font-size: 1.25rem; color: var(--text-primary);">Messages</h3>
                </div>
                <div class="chat-list">
                    <?php if (empty($conversations)): ?>
                        <p style="padding: 20px; color: var(--text-muted); text-align: center;">No conversations yet.</p>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="messages.php?with=<?= $conv['contact_id'] ?>" class="chat-list-item <?= $active_partner_id == $conv['contact_id'] ? 'active' : '' ?>">
                                <div class="chat-avatar"><?= strtoupper(substr($conv['full_name'], 0, 1)) ?></div>
                                <div class="chat-preview">
                                    <h4><?= htmlspecialchars($conv['full_name']) ?></h4>
                                    <p><?= htmlspecialchars($conv['last_message'] ?? 'Start a conversation') ?></p>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="chat-main">
                <?php if ($partner_info): ?>
                    <div class="chat-main-header">
                        <div class="chat-avatar" style="width: 40px; height: 40px; font-size: 1rem;"><?= strtoupper(substr($partner_info['full_name'], 0, 1)) ?></div>
                        <div>
                            <h4 style="font-size: 1.1rem; color: var(--text-primary);"><?= htmlspecialchars($partner_info['full_name']) ?></h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); text-transform: capitalize;"><?= htmlspecialchars($partner_info['role']) ?></p>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <?php foreach ($chat_messages as $msg): ?>
                            <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                <div class="message-time"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form method="POST" action="messages.php" class="chat-input-area">
                        <input type="hidden" name="receiver_id" value="<?= $active_partner_id ?>">
                        <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" name="send_message">➤</button>
                    </form>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); background: var(--off-white);">
                        <h3 style="margin-bottom: 8px;">Select a conversation</h3>
                        <p>Choose a contact from the sidebar to start messaging.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Auto-scroll to bottom of chat
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>