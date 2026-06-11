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

// Jika session sudah punya conv_id, langsung return
if (!empty($_SESSION['conv_id'])) {
    echo json_encode(['conversation_id' => (int) $_SESSION['conv_id']]);
    exit;
}

$sessionKey = session_id();
$pdo        = db_connect();

// Cek apakah session ini sudah punya conversation di DB
$stmt = $pdo->prepare('SELECT id FROM conversations WHERE session_key = ? LIMIT 1');
$stmt->execute([$sessionKey]);
$conv = $stmt->fetch();

if ($conv) {
    $_SESSION['conv_id'] = (int) $conv['id'];
    echo json_encode(['conversation_id' => (int) $conv['id']]);
    exit;
}

// Buat conversation baru
$stmt = $pdo->prepare(
    'INSERT INTO conversations (session_key, customer_name, status) VALUES (?, ?, ?)'
);
$stmt->execute([$sessionKey, 'Tamu', 'ai_handling']);
$convId = (int) $pdo->lastInsertId();

$_SESSION['conv_id'] = $convId;

// Insert pesan sambutan awal dari AI
$stmt = $pdo->prepare(
    'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$convId, 'ai', 'Bot Assistant', 'Halo! Ada yang bisa saya bantu?']);

echo json_encode(['conversation_id' => $convId]);
