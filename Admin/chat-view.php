<?php
session_start();
if (empty($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['auth']['name']);

// ── Dummy data ──────────────────────────────────────────────
$conversations = [
    (object)[
        'conversation_id' => 1,
        'customer'        => (object)['phone' => '+62812345678', 'full_name' => 'Budi Santoso'],
        'current_status'  => 'waiting_cs',
        'messages_count'  => 5,
        'assignedCs'      => (object)['full_name' => 'Joni'],
    ],
    (object)[
        'conversation_id' => 2,
        'customer'        => (object)['phone' => '+62898765432', 'full_name' => 'Siti Nurhaliza'],
        'current_status'  => 'ai_handling',
        'messages_count'  => 3,
        'assignedCs'      => (object)['full_name' => 'Unassigned'],
    ],
    (object)[
        'conversation_id' => 3,
        'customer'        => (object)['phone' => '+62811223344', 'full_name' => 'Ahmad Wijaya'],
        'current_status'  => 'closed',
        'messages_count'  => 8,
        'assignedCs'      => (object)['full_name' => 'Rini'],
    ],
];

$selectedConversation = $conversations[0] ?? null;
if ($selectedConversation) {
    $selectedConversation->messages = [
        (object)[
            'content'    => 'Halo, saya ingin membuat akun baru',
            'sender'     => (object)['role' => 'customer', 'full_name' => 'Budi Santoso'],
            'created_at' => '2026-06-10 10:00:00',
        ],
        (object)[
            'content'    => 'Baik pak Budi, saya siap membantu. Silakan berikan email Anda.',
            'sender'     => (object)['role' => 'ai', 'full_name' => 'Bot Assistant'],
            'created_at' => '2026-06-10 10:05:00',
        ],
        (object)[
            'content'    => 'Email saya adalah budi@email.com',
            'sender'     => (object)['role' => 'customer', 'full_name' => 'Budi Santoso'],
            'created_at' => '2026-06-10 11:30:00',
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Chat Admin – Disty Teknologi">
    <title>Chat – Disty Teknologi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="app-shell">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">DT</div>
            <span class="sidebar-brand">Disty Teknologi</span>
        </div>
        <nav class="side-nav">
            <a href="dashboard-view.php">📊 Dashboard</a>
            <a href="chat-view.php" class="active">💬 Chat</a>
            <a href="staff-view.php">👥 Staff</a>
        </nav>
        <div class="side-bottom">
            <a href="../logout.php">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main -->
    <main class="main-content">

        <!-- Top Bar -->
        <header class="topbar">
            <div class="top-left">
                <a href="../index.php" class="breadcrumb-link">Beranda</a>
                <span class="breadcrumb-sep">/</span>
                <span class="page-label">Chat</span>
            </div>
            <div class="topbar-right">
                <span class="admin-info">👑 <?= $adminName ?></span>
                <a href="../logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body">
            <div class="chat-layout">

                <!-- Conversation List -->
                <aside class="conversation-panel">
                    <div class="conversation-filter">
                        <span>Sort by</span>
                        <select>
                            <option>New</option>
                            <option>Waiting CS</option>
                            <option>Active</option>
                        </select>
                    </div>
                    <div class="conversation-list">
                        <?php if (!empty($conversations)): foreach ($conversations as $conv): ?>
                            <a href="#" class="conversation-card <?= ($selectedConversation && $selectedConversation->conversation_id === $conv->conversation_id) ? 'selected' : '' ?>">
                                <div class="conversation-phone"><?= htmlspecialchars($conv->customer->phone ?? 'No phone') ?></div>
                                <div class="conversation-meta">
                                    <span class="status-pill <?= htmlspecialchars($conv->current_status) ?>">
                                        <?= str_replace('_', ' ', $conv->current_status) ?>
                                    </span>
                                    <?php if ($conv->current_status === 'waiting_cs'): ?>
                                        <span class="red-dot"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="conversation-footer">
                                    <span><?= (int)$conv->messages_count ?> messages</span>
                                    <span><?= htmlspecialchars($conv->assignedCs->full_name ?? 'Unassigned') ?></span>
                                </div>
                            </a>
                        <?php endforeach; else: ?>
                            <p class="empty-small">No conversations found.</p>
                        <?php endif; ?>
                    </div>
                </aside>

                <!-- Chat Window -->
                <section class="chat-window">
                    <?php if ($selectedConversation): ?>
                        <div class="chat-header">
                            <div>
                                <h2><?= htmlspecialchars($selectedConversation->customer->full_name ?? 'Unknown') ?></h2>
                                <p><?= htmlspecialchars($selectedConversation->customer->phone ?? '-') ?></p>
                            </div>
                            <span class="status-pill <?= htmlspecialchars($selectedConversation->current_status) ?>">
                                <?= str_replace('_', ' ', $selectedConversation->current_status) ?>
                            </span>
                        </div>

                        <div class="message-list">
                            <?php foreach ($selectedConversation->messages as $msg):
                                $isStaff = in_array($msg->sender->role, ['cs', 'admin', 'ai']);
                            ?>
                                <div class="message-row <?= $isStaff ? 'outgoing' : 'incoming' ?>">
                                    <div class="message-bubble">
                                        <p><?= htmlspecialchars($msg->content) ?></p>
                                        <small>
                                            <?= htmlspecialchars($msg->sender->full_name ?? 'System') ?>
                                            &middot;
                                            <?= date('H:i', strtotime($msg->created_at)) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" action="#" class="message-form">
                            <input type="text" name="content" placeholder="Ketik pesan..." required>
                            <button type="submit">Kirim</button>
                        </form>
                    <?php else: ?>
                        <div class="chat-empty">
                            <h1>Pilih percakapan untuk memulai</h1>
                            <p>Pilih chat dari daftar di kiri untuk melihat pesan dan membalas pelanggan.</p>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </section>

    </main>
</div>

</body>
</html>