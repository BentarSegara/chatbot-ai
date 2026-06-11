<?php
/**
 * api/get-messages.php
 * ─────────────────────────────────────────────────────────────
 * Dipanggil oleh JS secara berkala (polling) untuk mengambil
 * pesan-pesan baru setelah ID tertentu.
 * GET params: conv_id, after (ID pesan terakhir yang sudah ada)
 * Return: { messages: [...], conv_status: "..." }
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$convId  = (int) ($_GET['conv_id'] ?? 0);
$afterId = (int) ($_GET['after']   ?? 0);

if (!$convId) {
    http_response_code(400);
    echo json_encode(['error' => 'conv_id diperlukan.']);
    exit;
}

$pdo = db_connect();

// Ambil pesan baru setelah afterId
$stmt = $pdo->prepare(
    'SELECT id, sender_role, sender_name, content, created_at
     FROM messages
     WHERE conversation_id = ? AND id > ?
     ORDER BY id ASC'
);
$stmt->execute([$convId, $afterId]);
$messages = $stmt->fetchAll();

// Ambil status conversation terkini
$stmtConv = $pdo->prepare('SELECT status FROM conversations WHERE id = ? LIMIT 1');
$stmtConv->execute([$convId]);
$conv = $stmtConv->fetch();

echo json_encode([
    'messages'    => $messages,
    'conv_status' => $conv['status'] ?? 'unknown',
]);
