<?php
/**
 * api/admin-get-conversations.php
 * ─────────────────────────────────────────────────────────────
 * Mengambil daftar semua percakapan aktif untuk sidebar admin.
 * Diurutkan: waiting_cs → cs_handling → ai_handling.
 * Return: { conversations: [...] }
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
$pdo = db_connect();

$stmt = $pdo->query("
    SELECT
        c.id,
        c.customer_name,
        c.status,
        c.created_at,
        c.updated_at,
        u.full_name  AS assigned_cs_name,
        (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id) AS message_count,
        (SELECT content    FROM messages m2 WHERE m2.conversation_id = c.id ORDER BY m2.id DESC LIMIT 1) AS last_message
    FROM conversations c
    LEFT JOIN users u ON c.assigned_cs_id = u.id
    WHERE c.status != 'closed'
    ORDER BY
        CASE c.status
            WHEN 'waiting_cs'  THEN 0
            WHEN 'cs_handling' THEN 1
            ELSE 2
        END,
        c.updated_at DESC
");

echo json_encode(['conversations' => $stmt->fetchAll()]);
