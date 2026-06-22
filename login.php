<?php
/**
 * login.php
 * ─────────────────────────────────────────────────────────────
 * Tampilan halaman login.
 * Logic autentikasi dikelola oleh includes/auth.php.
 * Styles dikelola oleh assets/css/login.css.
 * ─────────────────────────────────────────────────────────────
 */
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke panel Disty Teknologi">
    <title>Login – Disty Teknologi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="login-card">

    <!-- Header -->
    <div class="login-header">
        <div class="login-logo">DT</div>
        <h1>Selamat Datang Kembali</h1>
        <p>Masuk ke panel Disty Teknologi</p>
    </div>

    <!-- Body -->
    <div class="login-body">

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
        <div class="alert-error" role="alert">
            <span class="alert-icon">⚠️</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" id="loginForm" novalidate>

            <!-- Username -->
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div class="form-input-wrap">
                    <span class="form-input-icon">👤</span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($lastUsername) ?>"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="form-input-wrap">
                    <span class="form-input-icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan password">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginSubmit">Masuk →</button>

        </form>

        <!-- Demo Credentials Hint -->
        <!-- <div class="demo-hint">
            <p>🔑 Akun Demo</p>
            <code>Admin &nbsp;&nbsp;→ admin / admin123</code>
            <code>User &nbsp;&nbsp;&nbsp;→ user / user123</code>
        </div> -->

        <a href="index.php" class="back-link">← Kembali ke Beranda</a>

    </div>
</div>

<script>
    // ── Toggle visibilitas password ──────────────────────────
    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');

    togglePw.addEventListener('click', function () {
        const isHidden    = pwInput.type === 'password';
        pwInput.type      = isHidden ? 'text' : 'password';
        togglePw.textContent = isHidden ? '🙈' : '👁️';
    });

    // ── Loading state saat submit ────────────────────────────
    const loginForm   = document.getElementById('loginForm');
    const loginSubmit = document.getElementById('loginSubmit');

    loginForm.addEventListener('submit', function () {
        loginSubmit.textContent = 'Memproses...';
        loginSubmit.disabled    = true;
    });
</script>

</body>
</html>
