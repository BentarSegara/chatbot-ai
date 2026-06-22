<?php
/**
 * Admin/chat-view.php
 * ─────────────────────────────────────────────────────────────
 * Halaman chat admin.
 * Semua data diambil secara real-time via JavaScript fetch
 * dengan short polling setiap 3-5 detik.
 * ─────────────────────────────────────────────────────────────
 */
session_start();
if (empty($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['auth']['name']);
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
            <a href="chat-view.php" class="active">💬 Chat <span class="nav-badge" id="waitingBadge" hidden>!</span></a>
            <a href="history-view.php">📋 Histori</a>
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
                        <span>Percakapan Aktif</span>
                        <select id="statusFilter">
                            <option value="all">Semua</option>
                            <option value="waiting_cs">Menunggu CS</option>
                            <option value="cs_handling">Ditangani CS</option>
                            <option value="ai_handling">Ditangani AI</option>
                        </select>
                    </div>
                    <div class="conversation-list" id="conversationList">
                        <p class="empty-small" id="convListEmpty">Memuat percakapan...</p>
                    </div>
                </aside>

                <!-- Chat Window -->
                <section class="chat-window" id="chatWindow">
                    <div class="chat-empty" id="chatEmptyState">
                        <h1>Pilih percakapan</h1>
                        <p>Pilih chat dari daftar di kiri untuk melihat pesan dan membalas pelanggan.</p>
                    </div>

                    <!-- Active Chat (hidden until conversation selected) -->
                    <div id="activeChatArea" hidden>
                        <div class="chat-header" id="chatHeader">
                            <div>
                                <h2 id="chatCustomerName">—</h2>
                                <p id="chatConvStatus">—</p>
                            </div>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <button type="button" class="btn-assign" id="btnAssign" hidden>
                                    🙋 Ambil Percakapan
                                </button>
                                <button type="button" class="btn-close-conv" id="btnCloseConv" hidden>
                                    ✅ Tutup
                                </button>
                            </div>
                        </div>

                        <div class="message-list" id="messageList"></div>

                        <form class="message-form" id="adminChatForm">
                            <input type="text" id="adminChatInput" placeholder="Ketik pesan..." autocomplete="off" required>
                            <button type="submit" id="adminChatSubmit">Kirim</button>
                        </form>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>

<script src="../assets/js/admin-chat.js"></script>

</body>
</html>