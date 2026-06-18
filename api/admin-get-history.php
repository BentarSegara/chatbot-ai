<?php
/**
 * api/admin-get-history.php
 * ─────────────────────────────────────────────────────────────
 * Mengambil daftar percakapan yang sudah selesai (status=closed)
 * untuk halaman Histori Percakapan admin.
 * GET params (opsional):
 *   - conv_id  : jika ada, kembalikan detail pesan percakapan ini
 *   - page     : halaman (default 1)
 *   - per_page : jumlah per halaman (default 20)
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

// ── Mode detail: ambil pesan dari satu percakapan ─────────────
$convId = (int) ($_GET['conv_id'] ?? 0);
if ($convId > 0) {
    // Verifikasi bahwa percakapan ini closed
    $stmtConv = $pdo->prepare('SELECT * FROM conversations WHERE id = ? AND status = ? LIMIT 1');
    $stmtConv->execute([$convId, 'closed']);
    $conv = $stmtConv->fetch();

    if (!$conv) {
        http_response_code(404);
        echo json_encode(['error' => 'Percakapan tidak ditemukan atau belum selesai.']);
        exit;
    }

    $stmtMsgs = $pdo->prepare(
        'SELECT id, sender_role, sender_name, content, created_at
         FROM messages
         WHERE conversation_id = ?
         ORDER BY id ASC'
    );
    $stmtMsgs->execute([$convId]);
    $messages = $stmtMsgs->fetchAll();

    echo json_encode([
        'conversation' => $conv,
        'messages'     => $messages,
    ]);
    exit;
}

// ── Mode list: ambil semua percakapan closed ─────────────────
$page    = max(1, (int) ($_GET['page']     ?? 1));
$perPage = max(5, min(100, (int) ($_GET['per_page'] ?? 20)));
$offset  = ($page - 1) * $perPage;

// Total count
$stmtCount = $pdo->query("SELECT COUNT(*) FROM conversations WHERE status = 'closed'");
$totalRows  = (int) $stmtCount->fetchColumn();
$totalPages = (int) ceil($totalRows / $perPage);

$stmt = $pdo->prepare("
    SELECT
        c.id,
        c.customer_name,
        c.status,
        c.created_at,
        c.updated_at,
        u.full_name  AS assigned_cs_name,
        (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id) AS message_count,
        (SELECT content FROM messages m2 WHERE m2.conversation_id = c.id ORDER BY m2.id DESC LIMIT 1) AS last_message
    FROM conversations c
    LEFT JOIN users u ON c.assigned_cs_id = u.id
    WHERE c.status = 'closed'
    ORDER BY c.updated_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$conversations = $stmt->fetchAll();

echo json_encode([
    'conversations' => $conversations,
    'pagination'    => [
        'total_rows'  => $totalRows,
        'total_pages' => $totalPages,
        'current_page'=> $page,
        'per_page'    => $perPage,
    ],
]);
