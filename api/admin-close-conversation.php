<?php
/**
 * api/admin-close-conversation.php
 * ─────────────────────────────────────────────────────────────
 * Admin/CS menutup percakapan — status menjadi 'closed'.
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');

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

$pdo = db_connect();

$pdo->prepare("UPDATE conversations SET status = 'closed' WHERE id = ?")
    ->execute([$convId]);

$adminName = $_SESSION['auth']['name'];
$pdo->prepare(
    'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
)->execute([$convId, 'system', 'System', "Percakapan ini telah ditutup oleh {$adminName}. Terima kasih."]);

echo json_encode(['success' => true]);
