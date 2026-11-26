<?php
session_start();

$db_host = getenv('DB_HOST') ?: 'mariadb';
$db_user = getenv('DB_USER') ?: 'seitabank_user';
$db_pass = getenv('DB_PASS') ?: 'SeitaPass2026!';
$db_name = getenv('DB_NAME') ?: 'seitabank_db';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    http_response_code(500);
    die("Błąd połączenia z serwerem bazodanowym SeitaBank: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function get_client_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>
