<?php

$dbHost = $_ENV['DB_HOST'] ?? $_ENV['TIDB_HOST'] ?? '127.0.0.1';
$dbPort = (int)($_ENV['DB_PORT'] ?? $_ENV['TIDB_PORT'] ?? 3306);
$dbName = $_ENV['DB_NAME'] ?? $_ENV['TIDB_DATABASE'] ?? 'spk_evaluasikinerjaguru';
$dbUser = $_ENV['DB_USER'] ?? $_ENV['TIDB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? $_ENV['TIDB_PASSWORD'] ?? '';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if (!empty($_ENV['DB_SSL_CA'])) {
        $sslCa = trim($_ENV['DB_SSL_CA']);
        if (str_contains($sslCa, '-----BEGIN CERTIFICATE-----')) {
            $sslCaPath = sys_get_temp_dir() . '/spk-mysql-ca-' . substr(hash('sha256', $sslCa), 0, 16) . '.pem';
            if (!is_file($sslCaPath) && file_put_contents($sslCaPath, $sslCa) === false) {
                throw new RuntimeException('Sertifikat CA database tidak dapat disimpan.');
            }
            $sslCa = $sslCaPath;
        }
        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
    }

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Throwable $e) {
    http_response_code(500);
    $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    echo $debug
        ? 'Koneksi database gagal: ' . htmlspecialchars($e->getMessage())
        : 'Layanan database belum tersedia.';
    exit;
}

// Expose globally (sederhana untuk native setup)
$GLOBALS['pdo'] = $pdo;
