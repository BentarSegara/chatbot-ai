<?php
/**
 * seeder.php
 * ─────────────────────────────────────────────────────────────
 * Seeder untuk mendaftarkan user awal ke database chatbot_ai.
 * Jalankan SEKALI melalui browser: http://localhost/chatbot-ai/seeder.php
 * atau via CLI: php seeder.php
 *
 * PENTING: Hapus atau amankan file ini setelah dijalankan!
 * ─────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/includes/db.php';

// ── Konfigurasi: Daftar user yang akan di-seed ────────────────
$users = [
    [
        'username'  => 'cs_budi',
        'password'  => 'cs123456',
        'full_name' => 'Budi Santoso',
        'role'      => 'cs',
    ],
    [
        'username'  => 'cs_sari',
        'password'  => 'cs123456',
        'full_name' => 'Sari Dewi',
        'role'      => 'cs',
    ],
];

// ── Proses seeding ────────────────────────────────────────────
$results = [];

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password, full_name, role)
         VALUES (:username, :password, :full_name, :role)'
    );

    foreach ($users as $user) {
        $username  = $user['username'];
        $hashedPw  = password_hash($user['password'], PASSWORD_BCRYPT);
        $full_name = $user['full_name'];
        $role      = $user['role'];

        // Cek apakah username sudah ada
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $check->execute([$username]);

        if ($check->fetch()) {
            $results[] = [
                'status'   => 'SKIP',
                'username' => $username,
                'message'  => "Username sudah ada, dilewati.",
            ];
            continue;
        }

        $stmt->execute([
            ':username'  => $username,
            ':password'  => $hashedPw,
            ':full_name' => $full_name,
            ':role'      => $role,
        ]);

        $results[] = [
            'status'   => 'OK',
            'username' => $username,
            'role'     => $role,
            'full_name'=> $full_name,
            'message'  => "Berhasil ditambahkan.",
        ];
    }

} catch (PDOException $e) {
    die('❌ Koneksi / query gagal: ' . htmlspecialchars($e->getMessage()));
}

// ── Output hasil ──────────────────────────────────────────────
$isCli = PHP_SAPI === 'cli';

if ($isCli) {
    // ── Tampilan CLI ──────────────────────────────────────────
    echo "\n=== SEEDER — chatbot_ai.users ===\n\n";
    foreach ($results as $r) {
        $icon = $r['status'] === 'OK' ? '✅' : '⚠️ ';
        echo "{$icon}  [{$r['status']}] {$r['username']} — {$r['message']}\n";
    }
    echo "\nSeeder selesai. Hapus file ini jika sudah tidak diperlukan!\n\n";
} else {
    // ── Tampilan Browser ──────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seeder — chatbot_ai</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0f1117;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: #1a1d27;
            border: 1px solid #2d3148;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #2d3148;
        }

        .logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #f1f5f9;
        }

        h1 span {
            display: block;
            font-size: 0.8rem;
            font-weight: 400;
            color: #64748b;
            margin-top: 2px;
        }

        .result-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .result-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            border: 1px solid;
            animation: slideIn 0.3s ease forwards;
        }

        .result-item.ok {
            background: rgba(34,197,94,0.08);
            border-color: rgba(34,197,94,0.25);
        }

        .result-item.skip {
            background: rgba(234,179,8,0.08);
            border-color: rgba(234,179,8,0.25);
        }

        .result-icon { font-size: 1.3rem; flex-shrink: 0; }

        .result-body { flex: 1; }

        .result-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #f1f5f9;
        }

        .result-meta {
            font-size: 0.78rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        .badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-admin { background: rgba(99,102,241,0.2); color: #a5b4fc; }
        .badge-cs    { background: rgba(20,184,166,0.2); color: #5eead4; }

        .warning-box {
            margin-top: 2rem;
            padding: 1rem 1.25rem;
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            border-radius: 10px;
            font-size: 0.85rem;
            color: #fca5a5;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="card">

    <div class="card-header">
        <div class="logo">DT</div>
        <h1>
            Database Seeder
            <span>chatbot_ai &rsaquo; users</span>
        </h1>
    </div>

    <div class="result-list">
        <?php foreach ($results as $i => $r): ?>
            <?php $isOk = $r['status'] === 'OK'; ?>
            <div class="result-item <?= $isOk ? 'ok' : 'skip' ?>"
                 style="animation-delay: <?= $i * 0.08 ?>s; opacity:0">
                <div class="result-icon"><?= $isOk ? '✅' : '⚠️' ?></div>
                <div class="result-body">
                    <div class="result-name">
                        <?= htmlspecialchars($r['username']) ?>
                        <?php if ($isOk): ?>
                            <span class="badge badge-<?= $r['role'] ?>">
                                <?= $r['role'] ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="result-meta">
                        <?php if ($isOk): ?>
                            <?= htmlspecialchars($r['full_name']) ?> — <?= $r['message'] ?>
                        <?php else: ?>
                            <?= htmlspecialchars($r['message']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="warning-box">
        <span>🔒</span>
        <span>
            <strong>Perhatian:</strong> Seeder telah selesai dijalankan.
            Segera hapus atau amankan file <code>seeder.php</code>
            agar tidak dapat diakses publik.
        </span>
    </div>

</div>
</body>
</html>
<?php
}
