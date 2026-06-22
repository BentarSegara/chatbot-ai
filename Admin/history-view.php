<?php
/**
 * Admin/history-view.php
 * ─────────────────────────────────────────────────────────────
 * Halaman Histori Percakapan — menampilkan semua percakapan
 * yang sudah selesai (status = closed).
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
    <meta name="description" content="Histori Percakapan – Disty Teknologi">
    <title>Histori Percakapan – Disty Teknologi</title>
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
            <a href="chat-view.php">💬 Chat <span class="nav-badge" id="waitingBadge" hidden>!</span></a>
            <a href="history-view.php" class="active">📋 Histori</a>
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
                <span class="page-label">Histori Percakapan</span>
            </div>
            <div class="topbar-right">
                <span class="admin-info">👑 <?= $adminName ?></span>
                <a href="../logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body" style="padding:0;">
            <div class="history-layout">

                <!-- List Panel -->
                <aside class="history-panel">
                    <div class="history-filter">
                        <h2>📋 Histori Percakapan</h2>
                        <div class="history-search">
                            <input type="text" id="searchInput" placeholder="Cari nama customer..." autocomplete="off">
                        </div>
                    </div>
                    <div class="history-list" id="historyList">
                        <div class="empty-history">
                            <div class="icon">🕐</div>
                            <p>Memuat histori...</p>
                        </div>
                    </div>
                    <div class="pagination-bar" id="paginationBar" style="display:none;">
                        <button class="page-btn" id="prevBtn" disabled>← Sebelumnya</button>
                        <span class="page-info" id="pageInfo">–</span>
                        <button class="page-btn" id="nextBtn" disabled>Berikutnya →</button>
                    </div>
                </aside>

                <!-- Detail Panel -->
                <section class="history-detail" id="historyDetail">
                    <div class="history-detail-empty" id="detailEmpty">
                        <div class="icon">💬</div>
                        <p>Pilih percakapan untuk melihat detail percakapan.</p>
                    </div>

                    <div id="detailContent" style="display:none; flex-direction:column; height:100%; overflow:hidden;">
                        <div class="history-detail-header">
                            <div>
                                <h2 id="detailCustomerName">—</h2>
                                <div class="meta-info" id="detailMeta">—</div>
                            </div>
                            <span class="history-badge-closed">✅ Selesai</span>
                        </div>
                        <div class="history-messages" id="detailMessages"></div>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>

<script src="../assets/js/history-chat.js"></script>

</body>
</html>
