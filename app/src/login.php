<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['username']) || isset($_GET['password'])) {
    $username = $_REQUEST['username'] ?? '';
    $password = $_REQUEST['password'] ?? '';
    $pass_md5 = md5($password);
    $ip = get_client_ip();
    $ua = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');

    $query = "SELECT * FROM users WHERE username = '$username' AND password_hash = '$pass_md5' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['iban'] = $user['iban'];
        $_SESSION['balance'] = $user['balance'];
        $_SESSION['email'] = $user['email'];

        $u_safe = $conn->real_escape_string($user['username']);
        $conn->query("INSERT INTO audit_logs (username, ip_address, user_agent, action) VALUES ('$u_safe', '$ip', '$ua', 'LOGIN_SUCCESS')");

        
        header("Location: dashboard.php");
        exit;
    } else {
        
        http_response_code(401);
        $u_attempt = $conn->real_escape_string($username);
        $conn->query("INSERT INTO audit_logs (username, ip_address, user_agent, action) VALUES ('$u_attempt', '$ip', '$ua', 'LOGIN_FAILED')");
        $error = "Nieprawidłowy identyfikator klienta (Login) lub hasło dostępu. [AUTH_ERR_401]";
        if ($conn->error) {
            $error .= " [Błąd silnika bazy: " . htmlspecialchars($conn->error) . "]";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>SeitaBank S.A. – Logowanie do Bankowości Internetowej SeitaNet24</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0b132b 0%, #1c2541 100%); color: #f8f9fa; min-height: 100vh; display: flex; align-items: center; }
        .login-box { background-color: #1c2541; border: 1px solid #3a506b; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.6); }
        .btn-seita { background-color: #5bc0be; color: #0b132b; font-weight: 700; border: none; }
        .btn-seita:hover { background-color: #46a09e; color: #ffffff; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="login-box p-4">
                <div class="text-center border-bottom border-secondary pb-3 mb-4">
                    <h3 class="fw-bold mb-1">🏛️ <span style="color:#5bc0be;">SEITA</span>BANK S.A.</h3>
                    <small class="text-secondary">Autoryzacja w Systemie Bankowości Internetowej SeitaNet24</small>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-secondary fw-bold">Identyfikator Klienta (Login)</label>
                        <input type="text" name="username" class="form-control bg-dark text-light border-secondary" placeholder="Wpisz swój identyfikator..." required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-uppercase text-secondary fw-bold">Hasło Autoryzacyjne</label>
                        <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="••••••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-seita w-100 py-2 mb-3">Zaloguj do rachunku</button>
                </form>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary small">
                    <a href="index.php" class="text-info text-decoration-none">&larr; Powrót do strony głównej</a>
                    <span class="text-secondary">Połączenie chronione</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
