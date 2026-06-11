<?php
/**
 * api/admin-send-message.php
 * ─────────────────────────────────────────────────────────────
 * Admin/CS mengirim balasan pesan ke user.
 * Otomatis mengubah status ke cs_handling jika belum.
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

$body    = json_decode(file_get_contents('php://input'), true);
$convId  = (int) ($body['conv_id'] ?? 0);
$text    = trim($body['message'] ?? '');

if (!$convId || $text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit;
}

$pdo       = db_connect();
$adminId   = (int) $_SESSION['auth']['id'];
$adminName = $_SESSION['auth']['name'];

// Simpan pesan CS
$stmt = $pdo->prepare(
    'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$convId, 'cs', $adminName, $text]);
$msgId = (int) $pdo->lastInsertId();

// Update status ke cs_handling jika masih waiting_cs atau ai_handling
$pdo->prepare(
    "UPDATE conversations
     SET status = 'cs_handling', assigned_cs_id = ?
     WHERE id = ? AND status IN ('waiting_cs', 'ai_handling')"
)->execute([$adminId, $convId]);

echo json_encode(['success' => true, 'message_id' => $msgId]);
