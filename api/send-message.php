<?php
/**
 * api/send-message.php
 * ─────────────────────────────────────────────────────────────
 * Dipanggil setiap kali user mengirim pesan.
 * - Simpan pesan user ke DB
 * - Jika status ai_handling → panggil Python /detect-handoff
 * - Jika AI tidak tahu → ubah status ke waiting_cs
 * - Jika status waiting_cs / cs_handling → beri tahu user
 * Mengembalikan last_message_id (ID pesan terakhir yang diinsert)
 * agar JS dapat meng-update lastMsgId dan mencegah render ganda.
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// ── Parse input ──────────────────────────────────────────────
$body   = json_decode(file_get_contents('php://input'), true);
$convId = (int) ($body['conv_id'] ?? 0);
$text   = trim($body['message'] ?? '');

if (!$convId || $text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit;
}

$pdo = db_connect();

// ── Ambil conversation ───────────────────────────────────────
$stmt = $pdo->prepare('SELECT * FROM conversations WHERE id = ? LIMIT 1');
$stmt->execute([$convId]);
$conv = $stmt->fetch();

if (!$conv) {
    http_response_code(404);
    echo json_encode(['error' => 'Percakapan tidak ditemukan.']);
    exit;
}

// ── Simpan pesan user ────────────────────────────────────────
$stmtMsg = $pdo->prepare(
    'INSERT INTO messages (conversation_id, sender_role, sender_name, content) VALUES (?, ?, ?, ?)'
);
$stmtMsg->execute([$convId, 'user', 'Tamu', $text]);
// last_message_id akan terus diperbarui setiap kali ada insert baru
$lastMessageId = (int) $pdo->lastInsertId();

$status = $conv['status'];

// ── Logic berdasarkan status ─────────────────────────────────
switch ($status) {

    case 'ai_handling':
        // Panggil Python FastAPI /detect-handoff
        $aiAnswer   = null;
        $needsHuman = false;

        $ch = curl_init('http://127.0.0.1:8000/detect-handoff');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['question' => $text]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        if ($rawResponse && $httpCode === 200) {
            $aiData     = json_decode($rawResponse, true);
            $aiAnswer   = $aiData['answer']      ?? 'Maaf, terjadi kesalahan pada AI.';
            $needsHuman = (bool) ($aiData['needs_human'] ?? false);
        } else {
            $aiAnswer = 'Maaf, layanan AI sedang tidak tersedia. Silakan coba lagi nanti.';
        }

        // Simpan jawaban AI ke DB
        $stmtMsg->execute([$convId, 'ai', 'Bot Assistant', $aiAnswer]);
        $lastMessageId = (int) $pdo->lastInsertId(); // update ke ID pesan AI

        if ($needsHuman) {
            // Handoff → ubah status ke waiting_cs
            $pdo->prepare('UPDATE conversations SET status = ? WHERE id = ?')
                ->execute(['waiting_cs', $convId]);

            $systemMsg = 'Pertanyaan Anda akan diteruskan ke agen manusia. Mohon tunggu sebentar...';
            $stmtMsg->execute([$convId, 'system', 'System', $systemMsg]);
            $lastMessageId = (int) $pdo->lastInsertId(); // update ke ID pesan system

            echo json_encode([
                'answer'          => $aiAnswer,
                'status'          => 'waiting_cs',
                'system_msg'      => $systemMsg,
                'last_message_id' => $lastMessageId,
            ]);
            exit;
        }

        echo json_encode([
            'answer'          => $aiAnswer,
            'status'          => 'ai_handling',
            'last_message_id' => $lastMessageId,
        ]);
        break;

    case 'waiting_cs':
        echo json_encode([
            'answer'          => null,
            'status'          => 'waiting_cs',
            'system_msg'      => 'Pesan Anda sudah tercatat. Agen kami akan segera merespons...',
            'last_message_id' => $lastMessageId,
        ]);
        break;

    case 'cs_handling':
        echo json_encode([
            'answer'          => null,
            'status'          => 'cs_handling',
            'last_message_id' => $lastMessageId,
        ]);
        break;

    default:
        echo json_encode([
            'answer'          => null,
            'status'          => $status,
            'last_message_id' => $lastMessageId,
        ]);
}
