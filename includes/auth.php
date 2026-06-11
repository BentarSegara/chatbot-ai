<?php
/**
 * auth.php
 * ─────────────────────────────────────────────────────────────
 * Menyimpan logic autentikasi:
 *   - Daftar pengguna (hardcoded untuk demo)
 *   - Pengecekan session (redirect jika sudah login)
 *   - Pemrosesan form POST (validasi & set session)
 *
 * Di-require oleh login.php sebelum output HTML apapun.
 * ─────────────────────────────────────────────────────────────
 */

session_start();

// ── Daftar pengguna (demo) ───────────────────────────────────
// Produksi: ganti dengan query database + password_hash/verify
$users = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'role'     => 'admin',
        'name'     => 'Administrator',
    ],
    [
        'username' => 'user',
        'password' => 'user123',
        'role'     => 'user',
        'name'     => 'Guest User',
    ],
];

// ── Sudah login → redirect langsung ─────────────────────────
if (!empty($_SESSION['auth'])) {
    $redirect = $_SESSION['auth']['role'] === 'admin'
        ? 'Admin/dashboard-view.php'
        : 'index.php';
    header('Location: ' . $redirect);
    exit;
}

// ── Proses form POST ─────────────────────────────────────────
$error         = '';
$lastUsername  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = trim($_POST['username'] ?? '');
    $inputPassword = $_POST['password'] ?? '';
    $lastUsername  = $inputUsername;

    $authenticated = false;
    foreach ($users as $user) {
        if ($user['username'] === $inputUsername && $user['password'] === $inputPassword) {
            $_SESSION['auth'] = [
                'username' => $user['username'],
                'role'     => $user['role'],
                'name'     => $user['name'],
            ];
            $authenticated = true;

            $redirect = $user['role'] === 'admin'
                ? 'Admin/dashboard-view.php'
                : 'index.php';
            header('Location: ' . $redirect);
            exit;
        }
    }

    if (!$authenticated) {
        $error = 'Username atau password salah. Silakan coba lagi.';
    }
}
