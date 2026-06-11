<?php
session_start();
if (empty($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['auth']['name']);

// ── Dummy data ──────────────────────────────────────────────
$chartData = [
    ['label' => 'Jan', 'total' => 120, 'is_current' => false],
    ['label' => 'Feb', 'total' => 150, 'is_current' => false],
    ['label' => 'Mar', 'total' => 180, 'is_current' => false],
    ['label' => 'Apr', 'total' => 200, 'is_current' => false],
    ['label' => 'May', 'total' => 220, 'is_current' => false],
    ['label' => 'Jun', 'total' => 250, 'is_current' => true],
];
$maxValue            = max(array_column($chartData, 'total')) ?: 1;
$monthlyCustomers    = 250;
$growth              = 13.5;
$unansweredCustomers = 12;
$totalConversations  = 1450;
$csEscalations       = 8;
$averageResponseTime = '2m 15s';
$recentActivities    = [
    ['color' => 'success', 'message' => 'New conversation from',   'actor_name' => 'Budi Santoso',   'time' => '5 min ago'],
    ['color' => 'warning', 'message' => 'Escalated to CS:',        'actor_name' => 'Siti Nurhaliza', 'time' => '12 min ago'],
    ['color' => 'success', 'message' => 'Conversation closed by',  'actor_name' => 'Ahmad Wijaya',   'time' => '28 min ago'],
    ['color' => 'danger',  'message' => 'High priority ticket for', 'actor_name' => 'Rini',           'time' => '1 hour ago'],
];
$currentMonthLabel = 'June 2026';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Admin – Disty Teknologi">
    <title>Dashboard – Disty Teknologi</title>
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
            <a href="dashboard-view.php" class="active">📊 Dashboard</a>
            <a href="chat-view.php">💬 Chat</a>
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
                <span class="page-label">Dashboard</span>
            </div>
            <div class="topbar-right">
                <span class="admin-info">👑 <?= $adminName ?></span>
                <a href="../logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body">
            <div class="dashboard-page">

                <!-- Row 1: Monthly + Unanswered -->
                <div class="dashboard-top-grid">

                    <section class="dashboard-card">
                        <p class="card-title">Monthly Customers</p>
                        <div class="monthly-chart">
                            <?php foreach ($chartData as $month):
                                $height = max(30, ($month['total'] / $maxValue) * 95);
                            ?>
                                <div class="chart-column">
                                    <div class="chart-bar <?= $month['is_current'] ? 'is-active' : '' ?>"
                                         style="--bar-height:<?= $height ?>px"></div>
                                    <span><?= htmlspecialchars($month['label']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="monthly-label-row">
                            <strong><?= htmlspecialchars($currentMonthLabel) ?></strong>
                            <span class="growth-badge <?= $growth >= 0 ? 'positive' : 'negative' ?>">
                                <?= $growth >= 0 ? '▲' : '▼' ?> <?= abs($growth) ?>%
                            </span>
                        </div>
                        <div class="monthly-number"><?= number_format($monthlyCustomers, 0, ',', '.') ?></div>
                    </section>

                    <section class="dashboard-card">
                        <p class="card-title">Unanswered Customers</p>
                        <div class="unanswered-number"><?= $unansweredCustomers ?></div>
                        <p>Menunggu respons CS</p>
                    </section>

                </div>

                <!-- Row 2: Metrics -->
                <div class="dashboard-middle-grid">
                    <section class="dashboard-card">
                        <p class="card-title">Total Percakapan</p>
                        <div class="metric-number"><?= number_format($totalConversations, 0, ',', '.') ?></div>
                        <p>Bulan ini</p>
                    </section>
                    <section class="dashboard-card">
                        <p class="card-title">Eskalasi ke CS</p>
                        <div class="metric-number danger"><?= $csEscalations ?></div>
                        <p>Perlu penanganan</p>
                    </section>
                    <section class="dashboard-card">
                        <p class="card-title">Avg. Response Time</p>
                        <div class="metric-number response-time"><?= htmlspecialchars($averageResponseTime) ?></div>
                        <p>Rata-rata bot / CS</p>
                    </section>
                </div>

                <!-- Row 3: Activity -->
                <section class="dashboard-card">
                    <p class="card-title">Aktivitas Terbaru</p>
                    <div class="activity-list">
                        <?php if (!empty($recentActivities)): foreach ($recentActivities as $activity): ?>
                            <div class="activity-row">
                                <span class="activity-dot <?= htmlspecialchars($activity['color'] ?? 'neutral') ?>"></span>
                                <div class="activity-text">
                                    <?= htmlspecialchars($activity['message'] ?? '-') ?>
                                    <?php if (!empty($activity['actor_name'])): ?>
                                        <strong><?= htmlspecialchars($activity['actor_name']) ?></strong>
                                    <?php endif; ?>
                                </div>
                                <time><?= htmlspecialchars($activity['time'] ?? '-') ?></time>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="activity-row">
                                <span class="activity-dot neutral"></span>
                                <div class="activity-text">Belum ada aktivitas terbaru.</div>
                                <time>–</time>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>

</body>
</html>