<?php
session_start();
$auth     = $_SESSION['auth'] ?? null;
$isAdmin  = $auth && $auth['role'] === 'admin';
$userName = $auth ? htmlspecialchars($auth['name']) : null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Disty Teknologi – Solusi digital terpercaya untuk bisnis Anda. Web Development, Digital Marketing, dan layanan IT profesional.">
    <title>Disty Teknologi – Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/help-desk-chat.js" defer></script>
</head>

<body>
    <main class="main-content">

        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="logo-badge">DT</div>
                <div class="topbar-breadcrumb">
                    <a href="#" class="breadcrumb-link">Disty Teknologi</a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="page-label">Home</span>
                </div>
            </div>
            <div class="topbar-right">
                <span class="status-pill">
                    <span class="status-dot"></span>
                    Semua sistem aktif
                </span>

                <?php if ($auth): ?>
                    <!-- User is logged in -->
                    <div class="user-menu">
                        <div class="user-avatar" id="userMenuTrigger" title="<?= $userName ?>">
                            <?= mb_strtoupper(mb_substr($auth['name'], 0, 1)) ?>
                        </div>
                        <div class="user-dropdown" id="userDropdown" hidden>
                            <div class="user-dropdown-info">
                                <strong><?= $userName ?></strong>
                                <span><?= htmlspecialchars($auth['role']) === 'admin' ? '👑 Administrator' : '👤 User' ?></span>
                            </div>
                            <?php if ($isAdmin): ?>
                                <a href="Admin/dashboard-view.php" class="user-dropdown-item">
                                    📊 Admin Dashboard
                                </a>
                            <?php endif; ?>
                            <a href="logout.php" class="user-dropdown-item user-dropdown-item--danger">
                                🚪 Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Not logged in -->
                    <a href="login.php" class="btn-topbar-login" id="topbarLoginBtn">
                        🔑 Login
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Page Body -->
        <section class="page-body">

            <!-- Hero Banner -->
            <div class="hero-banner">
                <div class="hero-glow hero-glow-1"></div>
                <div class="hero-glow hero-glow-2"></div>
                <div class="hero-content">
                    <?php if ($auth): ?>
                        <span class="eyebrow">👋 Selamat datang kembali, <?= $userName ?>!</span>
                    <?php else: ?>
                        <span class="eyebrow">🚀 Platform Digital Terpercaya</span>
                    <?php endif; ?>
                    <h1>Selamat Datang di<br><span class="hero-highlight">Disty Teknologi</span></h1>
                    <p class="hero-desc">Kami hadir sebagai mitra digital Anda — dari pengembangan web modern, pemasaran digital, hingga solusi IT yang scalable dan handal.</p>
                    <div class="hero-actions">
                        <a href="#layanan" class="btn-primary">Jelajahi Layanan</a>
                        <?php if ($isAdmin): ?>
                            <a href="Admin/dashboard-view.php" class="btn-ghost">📊 Admin Dashboard</a>
                        <?php elseif ($auth): ?>
                            <a href="logout.php" class="btn-ghost">🚪 Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-ghost">🔑 Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hero-stats">
                    <div class="stat-card">
                        <strong class="stat-num">120+</strong>
                        <span class="stat-label">Proyek Selesai</span>
                    </div>
                    <div class="stat-card">
                        <strong class="stat-num">98%</strong>
                        <span class="stat-label">Kepuasan Klien</span>
                    </div>
                    <div class="stat-card">
                        <strong class="stat-num">5★</strong>
                        <span class="stat-label">Rating Layanan</span>
                    </div>
                    <div class="stat-card">
                        <strong class="stat-num">24/7</strong>
                        <span class="stat-label">Support Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Layanan Section -->
            <div class="section-header" id="layanan">
                <h2 class="section-title">Layanan Unggulan</h2>
                <p class="section-sub">Solusi digital end-to-end untuk mendorong pertumbuhan bisnis Anda</p>
            </div>

            <div class="services-grid">
                <div class="service-card service-card--featured">
                    <div class="service-icon">🌐</div>
                    <h3>Web Development</h3>
                    <p>Membangun website dan aplikasi web modern yang cepat, responsif, dan SEO-friendly menggunakan teknologi terkini.</p>
                    <ul class="service-tags">
                        <li>React & Next.js</li>
                        <li>Laravel & PHP</li>
                        <li>REST API</li>
                    </ul>
                    <a href="#" class="service-link">Pelajari lebih lanjut →</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">📈</div>
                    <h3>Digital Marketing</h3>
                    <p>Strategi pemasaran digital terukur yang meningkatkan visibilitas merek dan konversi penjualan Anda secara signifikan.</p>
                    <ul class="service-tags">
                        <li>SEO/SEM</li>
                        <li>Social Media</li>
                        <li>Content Strategy</li>
                    </ul>
                    <a href="#" class="service-link">Pelajari lebih lanjut →</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">🤖</div>
                    <h3>AI & Chatbot</h3>
                    <p>Integrasikan kecerdasan buatan ke dalam bisnis Anda dengan chatbot pintar yang siap melayani pelanggan 24/7.</p>
                    <ul class="service-tags">
                        <li>NLP & AI</li>
                        <li>Chatbot Custom</li>
                        <li>Otomatisasi</li>
                    </ul>
                    <a href="#" class="service-link">Pelajari lebih lanjut →</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">☁️</div>
                    <h3>Cloud & Infrastruktur</h3>
                    <p>Solusi hosting dan infrastruktur cloud yang andal, aman, dan scalable untuk kebutuhan bisnis skala apapun.</p>
                    <ul class="service-tags">
                        <li>AWS / GCP</li>
                        <li>DevOps & CI/CD</li>
                        <li>Monitoring</li>
                    </ul>
                    <a href="#" class="service-link">Pelajari lebih lanjut →</a>
                </div>
            </div>

            <!-- Why Us -->
            <div class="why-us-section">
                <div class="why-us-text">
                    <span class="eyebrow">✨ Mengapa Memilih Kami?</span>
                    <h2>Tim Profesional, Hasil Nyata</h2>
                    <p>Kami bukan sekadar vendor teknologi. Kami adalah mitra pertumbuhan yang berkomitmen untuk memahami visi bisnis Anda dan mewujudkannya melalui solusi digital yang tepat sasaran.</p>
                    <div class="why-us-points">
                        <div class="why-point">
                            <span class="why-icon">⚡</span>
                            <div>
                                <strong>Pengerjaan Cepat & Tepat</strong>
                                <p>Deadline adalah prioritas kami. Setiap proyek dikerjakan dengan standar kualitas tinggi dan tepat waktu.</p>
                            </div>
                        </div>
                        <div class="why-point">
                            <span class="why-icon">🔒</span>
                            <div>
                                <strong>Keamanan Terjamin</strong>
                                <p>Setiap solusi yang kami bangun menempatkan keamanan data dan privasi pengguna sebagai prioritas utama.</p>
                            </div>
                        </div>
                        <div class="why-point">
                            <span class="why-icon">💬</span>
                            <div>
                                <strong>Komunikasi Transparan</strong>
                                <p>Update proyek secara berkala, laporan yang jelas, dan tim yang selalu siap dihubungi kapanpun.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="why-us-visual">
                    <div class="visual-badge visual-badge--1">
                        <span class="vb-num">120+</span>
                        <span class="vb-label">Klien Puas</span>
                    </div>
                    <div class="visual-badge visual-badge--2">
                        <span class="vb-num">5 Thn</span>
                        <span class="vb-label">Pengalaman</span>
                    </div>
                    <div class="visual-card">
                        <div class="vc-icon">🏆</div>
                        <strong>Award Winning Agency</strong>
                        <p>Digital Excellence 2024</p>
                    </div>
                </div>
            </div>

        </section>
    </main>

    <!-- Help Desk Trigger -->
    <button type="button" class="help-desk" id="helpDeskTrigger" aria-label="Help desk" aria-controls="helpDeskPopup">
        <span class="dot"></span>
        Help Desk
    </button>

    <!-- Pop Up Chat -->
    <div class="help-desk-chat-backdrop" id="helpDeskBackdrop" hidden>
        <section class="help-desk-chat-window" id="helpDeskPopup" aria-label="Help desk chat">
            <div class="help-desk-chat-header">
                <div class="chat-title">
                    <h4>Help Desk</h4>
                    <p>Tim support online</p>
                </div>
                <button type="button" class="chat-close" id="closeHelpDesk" aria-label="Tutup chat">✕</button>
            </div>

            <div class="help-desk-chat-body" id="helpDeskMessages">
                <div class="chat-message assistant">
                    Halo! Ada yang bisa saya bantu?
                    <div class="chat-timestamp">Sekarang</div>
                </div>
                <div class="chat-suggestions-wrapper">
                    <div class="chat-suggestions-heading">Coba salah satu pertanyaan berikut</div>
                    <div class="chat-suggestions" id="chatSuggestions">
                        <button type="button" class="suggestion-box">Apa itu layanan web development Disty?</button>
                        <button type="button" class="suggestion-box">Apa keunggulan web development Disty?</button>
                        <button type="button" class="suggestion-box">Apa itu layanan Digital Marketing pada PT Disty?</button>
                    </div>
                </div>
            </div>

            <form class="help-desk-chat-form" id="helpDeskForm">
                <input type="text" class="help-desk-chat-input" id="helpDeskInput"
                    placeholder="Ketik pertanyaan Anda..." autocomplete="off" required>
                <button type="submit" class="help-desk-chat-send">Kirim</button>
            </form>
        </section>
    </div>

    <script>
        // User dropdown toggle
        const userMenuTrigger = document.getElementById('userMenuTrigger');
        const userDropdown    = document.getElementById('userDropdown');

        if (userMenuTrigger && userDropdown) {
            userMenuTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                userDropdown.hidden = !userDropdown.hidden;
            });

            document.addEventListener('click', function () {
                if (userDropdown) userDropdown.hidden = true;
            });

            userDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }
    </script>
</body>

</html>
