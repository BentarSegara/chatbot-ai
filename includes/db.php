<?php
/**
 * includes/db.php
 * ─────────────────────────────────────────────────────────────
 * Koneksi PDO ke MySQL (XAMPP).
 * Gunakan fungsi db_connect() di seluruh proyek.
 * ─────────────────────────────────────────────────────────────
 */

function db_connect(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host    = 'localhost';
        $dbname  = 'chatbot_ai';
        $user    = 'root';
        $pass    = 'Segarabuana212';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Jangan expose detail error di production
            http_response_code(500);
            echo json_encode(['error' => 'Koneksi database gagal.']);
            exit;
        }
    }

    return $pdo;
}
