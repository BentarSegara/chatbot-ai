<?php
/**
 * api/admin-assign.php
 * ─────────────────────────────────────────────────────────────
 * Admin/CS mengambil alih percakapan.
 * Mengubah status dari waiting_cs ke cs_handling dan
 * menetapkan assigned_cs_id.
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');

// ── Proteksi autentikasi ─────────────────────────────────────
if (empty($_SESSION['auth']) || !in_array($_SESSION['auth']['role'], ['admin', 'cs'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$body   = json_decode(file_get_contents('php://input'), true);
$convId = (int) ($body['conv_id'] ?? 0);

if (!$convId) {
    http_response_code(400);
    echo json_encode(['error' => 'conv_id diperlukan.']);
    exit;
}

$pdo       = db_connect();
$adminId   = (int) $_SESSION['auth']['id'];
$adminName = $_SESSION['auth']['name'];

// Update conversation
$pdo->prepare(
    "UPDATE conversations SET status = 'cs_handling', assigned_cs_id = ? WHERE id = ?"
)->execute([$adminId, $convId]);

// Pesan sistem notifikasi
$pdo->prepare(
    'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
)->execute([$convId, 'system', 'System', "Agen {$adminName} telah bergabung. Anda sekarang terhubung ke agen manusia."]);

echo json_encode(['success' => true]);
