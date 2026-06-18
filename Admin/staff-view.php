<?php
session_start();
if (empty($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['auth']['name']);

// ── Dummy data ──────────────────────────────────────────────
$staffStats = [
    (object)['full_name' => 'Joni Wijaya',      'total_cust' => 45, 'finished_cust' => 35, 'ongoing_cust' => 10],
    (object)['full_name' => 'Rini Hermawan',    'total_cust' => 52, 'finished_cust' => 48, 'ongoing_cust' => 4],
    (object)['full_name' => 'Bambang Sutrisno', 'total_cust' => 38, 'finished_cust' => 30, 'ongoing_cust' => 8],
    (object)['full_name' => 'Siti Rahayu',      'total_cust' => 41, 'finished_cust' => 36, 'ongoing_cust' => 5],
    (object)['full_name' => 'Ahmad Rizki',      'total_cust' => 56, 'finished_cust' => 50, 'ongoing_cust' => 6],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manajemen Staff – Disty Teknologi">
    <title>Staff – Disty Teknologi</title>
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
            <a href="staff-view.php" class="active">👥 Staff</a>
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
                <span class="page-label">Staff</span>
            </div>
            <div class="topbar-right">
                <span class="admin-info">👑 <?= $adminName ?></span>
                <a href="../logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h1>Staff Customer Service</h1>
                    <p>Ringkasan jumlah customer yang ditangani oleh masing-masing CS.</p>
                </div>
                <div class="staff-table-wrapper">
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Total Cust</th>
                                <th>Finished Cust</th>
                                <th>On-going Cust</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffStats as $index => $staff): ?>
                                <tr>
                                    <td class="num-cell"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($staff->full_name) ?></td>
                                    <td><span class="badge badge-total"><?= (int)$staff->total_cust ?></span></td>
                                    <td><span class="badge badge-finished"><?= (int)$staff->finished_cust ?></span></td>
                                    <td><span class="badge badge-ongoing"><?= (int)$staff->ongoing_cust ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>
</div>

</body>
</html>