<?php
/**
 * auth.php
 * ─────────────────────────────────────────────────────────────
 * Logic autentikasi:
 *   - Query ke database (bukan hardcoded)
 *   - Verifikasi password dengan password_verify()
 *   - Pengecekan session & redirect
 *
 * Di-require oleh login.php sebelum output HTML apapun.
 * ─────────────────────────────────────────────────────────────
 */

session_start();
require_once __DIR__ . '/db.php';

// ── Sudah login → redirect langsung ─────────────────────────
if (!empty($_SESSION['auth'])) {
    $redirect = $_SESSION['auth']['role'] === 'admin'
        ? 'Admin/dashboard-view.php'
        : 'index.php';
    header('Location: ' . $redirect);
    exit;
}

// ── Proses form POST ─────────────────────────────────────────
$error        = '';
$lastUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = trim($_POST['username'] ?? '');
    $inputPassword = $_POST['password'] ?? '';
    $lastUsername  = $inputUsername;

    if ($inputUsername === '' || $inputPassword === '') {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        try {
            $pdo  = db_connect();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$inputUsername]);
            $user = $stmt->fetch();

            if ($user && password_verify($inputPassword, $user['password'])) {
                // Login berhasil — simpan data ke session
                $_SESSION['auth'] = [
                    'id'       => (int) $user['id'],
                    'username' => $user['username'],
                    'role'     => $user['role'],
                    'name'     => $user['full_name'],
                ];

                $redirect = $user['role'] === 'admin'
                    ? 'Admin/dashboard-view.php'
                    : 'index.php';

                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Username atau password salah. Silakan coba lagi.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
        }
    }
}
