<?php
/**
 * api/request-human.php
 * ─────────────────────────────────────────────────────────────
 * Dipanggil saat user menekan tombol "Hubungi Admin".
 * Mengubah status conversation ke waiting_cs dan
 * menambahkan pesan sistem.
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$body   = json_decode(file_get_contents('php://input'), true);
$convId = (int) ($body['conv_id'] ?? $_SESSION['conv_id'] ?? 0);

if (!$convId) {
    http_response_code(400);
    echo json_encode(['error' => 'conv_id diperlukan.']);
    exit;
}

$pdo = db_connect();

// Hanya ubah jika masih di tahap ai_handling
$stmt = $pdo->prepare(
    "UPDATE conversations SET status = 'waiting_cs' WHERE id = ? AND status = 'ai_handling'"
);
$stmt->execute([$convId]);

// Tambahkan pesan sistem ke conversation
$stmtMsg = $pdo->prepare(
    'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
);
$stmtMsg->execute([
    $convId,
    'system',
    'System',
    'Pengguna meminta bantuan agen manusia. Mohon tunggu, agen kami akan segera bergabung...',
]);

echo json_encode(['success' => true, 'status' => 'waiting_cs']);
