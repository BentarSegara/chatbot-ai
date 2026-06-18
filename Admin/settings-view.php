<?php
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
    <meta name="description" content="Pengaturan Admin – Disty Teknologi">
    <title>Settings – Disty Teknologi</title>
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
            <a href="chat-view.php">💬 Chat</a>
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
                <span class="page-label">Settings</span>
            </div>
            <div class="topbar-right">
                <span class="admin-info">👑 <?= $adminName ?></span>
                <a href="../logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body">
            <div class="admin-card settings-section">
                <div class="admin-card-header">
                    <h1>Pengaturan Panel</h1>
                    <p>Konfigurasi tampilan dan preferensi admin panel.</p>
                </div>
                <div style="padding:20px">
                    <div class="settings-group">
                        <p class="settings-group-title">Tampilan</p>
                        <form method="POST" action="#">
                            <input type="hidden" name="dark_mode" value="0">
                            <label class="toggle-row">
                                <input type="checkbox" name="dark_mode" value="1" onchange="this.form.submit()">
                                <span>Dark Mode</span>
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>

</body>
</html>