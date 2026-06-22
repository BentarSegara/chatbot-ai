<?php
/**
 * api/start-conversation.php
 * ─────────────────────────────────────────────────────────────
 * Dipanggil saat user pertama kali membuka Help Desk.
 * Membuat atau mengambil conversation yang sudah ada
 * berdasarkan PHP session.
 * Return: { "conversation_id": N }
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$pdo = db_connect();

// Jika session sudah punya conv_id, cek dulu apakah masih aktif
if (!empty($_SESSION['conv_id'])) {
    $stmtCheck = $pdo->prepare('SELECT id, status FROM conversations WHERE id = ? LIMIT 1');
    $stmtCheck->execute([(int) $_SESSION['conv_id']]);
    $existingConv = $stmtCheck->fetch();

    // Jika percakapan masih ada dan belum closed, langsung kembalikan
    if ($existingConv && $existingConv['status'] !== 'closed') {
        echo json_encode(['conversation_id' => (int) $existingConv['id']]);
        exit;
    }

    // Percakapan sudah closed atau tidak ditemukan — hapus dari session dan buat baru
    unset($_SESSION['conv_id']);
}

// Buat session_key unik: session_id + timestamp (ms) agar tidak konflik dengan UNIQUE constraint
// ketika user memulai percakapan baru setelah percakapan lama ditutup
$sessionKey = session_id() . '_' . round(microtime(true) * 1000);

// Buat conversation baru
$stmt = $pdo->prepare(
    'INSERT INTO conversations (session_key, customer_name, status) VALUES (?, ?, ?)'
);
$stmt->execute([$sessionKey, 'Tamu', 'ai_handling']);
$convId = (int) $pdo->lastInsertId();

$_SESSION['conv_id'] = $convId;

// Insert pesan sambutan awal dari AI
// $stmt = $pdo->prepare(
//     'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
// );
// $stmt->execute([$convId, 'ai', 'Bot Assistant', 'Halo! Ada yang bisa saya bantu?']);

echo json_encode(['conversation_id' => $convId]);
