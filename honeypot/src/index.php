<?php
$alert_triggered = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $operator_id = substr(preg_replace('/[^a-zA-Z0-9_.-]/', '', $_POST['admin_user'] ?? 'none'), 0, 32);
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $telemetry_record = json_encode([
        'timestamp' => date('c'),
        'sensor' => 'SEITABANK_DMZ_HONEYPOT_8080',
        'event_type' => 'UNAUTHORIZED_ADMIN_GATEWAY_PROBE',
        'src_ip' => $ip,
        'probed_operator_id' => $operator_id,
        'http_user_agent' => $ua
    ]) . PHP_EOL;

    file_put_contents('/var/log/honeypot/honeypot_alerts.log', $telemetry_record, FILE_APPEND);
    http_response_code(403);
    $alert_triggered = true;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>SeitaBank SWIFT HSM Gateway – Restricted Node</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-black text-light d-flex align-items-center" style="min-height: 100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4 border border-danger p-4 rounded bg-dark shadow-lg">
            <h5 class="text-danger fw-bold text-center mb-1">⚠️ SEITABANK SWIFT GATEWAY</h5>
            <p class="small text-secondary text-center mb-3">Węzeł autoryzacji klucza sprzętowego HSM [DMZ-NODE-02]</p>
            <?php if ($alert_triggered): ?>
                <div class="alert alert-danger small py-2">
                    Odmowa dostępu (#HSM-403). Brak ważnego certyfikatu klucza sprzętowego. Zdarzenie przekazano do SOC.
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small">Identyfikator Operatora SWIFT</label>
                    <input type="text" name="admin_user" class="form-control form-control-sm bg-black text-light border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Kod Autoryzacyjny Tokena HSM</label>
                    <input type="password" name="admin_pass" class="form-control form-control-sm bg-black text-light border-secondary" required>
                </div>
                <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">Autoryzuj Kanał Międzybankowy</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
