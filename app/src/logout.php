<?php
require_once 'db.php';

if (isset($_SESSION['username'])) {
    $u = $conn->real_escape_string($_SESSION['username']);
    $ip = get_client_ip();
    $ua = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT'] ?? '');
    $conn->query("INSERT INTO audit_logs (username, ip_address, user_agent, action) VALUES ('$u', '$ip', '$ua', 'USER_LOGOUT')");
}

session_unset();
session_destroy();
header("Location: index.php");
exit;
?>
