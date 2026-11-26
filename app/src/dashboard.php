<?php
require_once 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$u_res = $conn->query("SELECT * FROM users WHERE id = $user_id LIMIT 1");
if ($u_res && $u_res->num_rows > 0) {
    $current_user = $u_res->fetch_assoc();
    $_SESSION['balance'] = $current_user['balance'];
    $_SESSION['iban'] = $current_user['iban'];
    $_SESSION['full_name'] = $current_user['full_name'];
}

$transfer_msg = '';
$transfer_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_transfer') {
    $rec_name = $conn->real_escape_string(trim($_POST['receiver_name'] ?? ''));
    $rec_iban = $conn->real_escape_string(trim($_POST['receiver_iban'] ?? ''));
    $tx_title = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $amount   = (float)($_POST['amount'] ?? 0);

    if ($amount <= 0) {
        $transfer_err = "Kwota przelewu musi być większa od zera.";
    } elseif ($amount > $_SESSION['balance']) {
        $transfer_err = "Niewystarczające środki na rachunku do realizacji zlecenia.";
    } elseif (empty($rec_name) || empty($rec_iban) || empty($tx_title)) {
        $transfer_err = "Wszystkie pola formularza przelewu są wymagane.";
    } else {
        $my_iban = $conn->real_escape_string($_SESSION['iban']);
        $ip = get_client_ip();
        $ua = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT'] ?? '');

        $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $user_id");
        $conn->query("INSERT INTO transactions (sender_iban, receiver_iban, receiver_name, title, amount, status, tx_date) 
                      VALUES ('$my_iban', '$rec_iban', '$rec_name', '$tx_title', $amount, 'ZREALIZOWANY', NOW())");
        $conn->query("INSERT INTO audit_logs (username, ip_address, user_agent, action) 
                      VALUES ('$username', '$ip', '$ua', 'TRANSFER_EXECUTED: $amount PLN')");

        $_SESSION['balance'] -= $amount;
        $transfer_msg = "Przelew na kwotę " . number_format($amount, 2, ',', ' ') . " PLN dla " . htmlspecialchars($rec_name) . " został przyjęty do realizacji.";
    }
}

$search = $_GET['search'] ?? '';
$tx_list = [];
$sql_debug = '';
$my_iban = $_SESSION['iban'];

if ($search !== '') {
    $sql = "SELECT id, sender_iban, receiver_iban, receiver_name, title, amount, status, tx_date 
            FROM transactions 
            WHERE (title LIKE '%$search%' OR receiver_name LIKE '%$search%') 
            ORDER BY tx_date DESC LIMIT 30";
} else {
    if ($role === 'admin' || $role === 'employee') {
        $sql = "SELECT id, sender_iban, receiver_iban, receiver_name, title, amount, status, tx_date 
                FROM transactions ORDER BY tx_date DESC LIMIT 30";
    } else {
        $sql = "SELECT id, sender_iban, receiver_iban, receiver_name, title, amount, status, tx_date 
                FROM transactions WHERE sender_iban = '$my_iban' ORDER BY tx_date DESC LIMIT 30";
    }
}

$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $tx_list[] = $row;
    }
} else {
    $sql_debug = $conn->error;
}

$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$total_tx = $conn->query("SELECT COUNT(*) AS c FROM transactions")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>SeitaNet24 – Panel Bankowości Internetowej SeitaBank S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b132b; color: #f8f9fa; }
        .panel-card { background-color: #1c2541; border: 1px solid #3a506b; border-radius: 10px; }
        .accent-gold { color: #5bc0be; }
        .btn-seita { background-color: #5bc0be; color: #0b132b; font-weight: 700; border: none; }
        .btn-seita:hover { background-color: #46a09e; color: #ffffff; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary px-4 py-3">
    <a class="navbar-brand fw-bold" href="dashboard.php">
        🏛️ <span class="accent-gold">SEITA</span>NET24 <span class="badge bg-secondary ms-2 fw-normal">Strefa Autoryzowana</span>
    </a>
    <div class="ms-auto d-flex align-items-center">
        <span class="me-3 text-light small">
            Klient: <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong> 
            (<span class="text-info"><?= htmlspecialchars($_SESSION['role']) ?></span>)
        </span>
        <a href="index.php" class="btn btn-outline-light btn-sm me-2">Strona Główna</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Wyloguj z systemu</a>
    </div>
</nav>

<div class="container-fluid py-4 px-4">
    <!-- Kafelki informacyjne rachunku -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="panel-card p-3 h-100">
                <small class="text-secondary text-uppercase">Twój Numer Rachunku (IBAN)</small>
                <h5 class="fw-bold text-info mt-2 mb-1"><code><?= htmlspecialchars($_SESSION['iban']) ?></code></h5>
                <small class="text-secondary">Waluta rachunku: PLN (Konto Rozliczeniowe)</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel-card p-3 h-100">
                <small class="text-secondary text-uppercase">Dostępne Środki (Saldo)</small>
                <h3 class="fw-bold text-success mt-1 mb-0"><?= number_format($_SESSION['balance'], 2, ',', ' ') ?> PLN</h3>
                <small class="text-secondary">Brak blokad komorniczych / AML</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel-card p-3 h-100">
                <?php if ($role === 'admin' || $role === 'employee'): ?>
                    <small class="text-warning text-uppercase fw-bold">Statystyki Centrali SeitaBank (Widok Admin)</small>
                    <div class="d-flex justify-content-between mt-2">
                        <span>Aktywne konta: <strong class="text-light"><?= number_format($total_users, 0, ',', ' ') ?></strong></span>
                        <span>Operacje ELIXIR: <strong class="text-info"><?= number_format($total_tx, 0, ',', ' ') ?></strong></span>
                    </div>
                    <small class="text-secondary d-block mt-1">Uprawnienia pełnego audytu rejestru międzybankowego</small>
                <?php else: ?>
                    <small class="text-secondary text-uppercase">Status Bezpieczeństwa Sesji</small>
                    <h5 class="fw-bold text-light mt-2 mb-1">Połączenie Autoryzowane</h5>
                    <small class="text-secondary">Adres IP sesji: <?= htmlspecialchars(get_client_ip()) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolumna lewa: Formularz nowego przelewu -->
        <div class="col-lg-4">
            <div class="panel-card p-4">
                <h5 class="accent-gold fw-bold mb-3">💸 Wykonaj Przelew Krajowy ELIXIR</h5>
                <?php if ($transfer_msg): ?>
                    <div class="alert alert-success small py-2"><?= $transfer_msg ?></div>
                <?php endif; ?>
                <?php if ($transfer_err): ?>
                    <div class="alert alert-danger small py-2"><?= $transfer_err ?></div>
                <?php endif; ?>
                <form method="POST" action="dashboard.php">
                    <input type="hidden" name="action" value="new_transfer">
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Nazwa Odbiorcy</label>
                        <input type="text" name="receiver_name" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="np. PGE Obrót S.A." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Rachunek Odbiorcy (IBAN)</label>
                        <input type="text" name="receiver_iban" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="PL00 0000 0000 0000 0000 0000 0000" value="PL27114020040000300201350099" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Tytuł Przelewu</label>
                        <input type="text" name="title" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="np. Opłata za fakturę FV/2026/10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Kwota (PLN)</label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="0.00" required>
                    </div>
                    <button type="submit" class="btn btn-seita w-100">Zatwierdź i wyślij przelew</button>
                </form>
            </div>
        </div>

        <!-- Kolumna prawa: Historia operacji i wyszukiwarka -->
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <h5 class="accent-gold fw-bold mb-3">📋 Historia Operacji i Wyszukiwarka Rozliczeń</h5>
                <form method="GET" action="dashboard.php" class="row g-2 mb-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Filtruj operacje po tytule przelewu lub nazwie kontrahenta..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-seita btn-sm w-100">Szukaj</button>
                        <?php if ($search !== ''): ?>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Wyczyść</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($sql_debug): ?>
                    <div class="alert alert-danger small py-2">
                        <strong>Błąd zapytania SQL:</strong> <?= htmlspecialchars($sql_debug) ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-dark table-hover table-sm align-middle mb-0">
                        <thead>
                            <tr class="text-secondary small">
                                <th>ID</th>
                                <th>Data</th>
                                <th>Nadawca IBAN</th>
                                <th>Odbiorca</th>
                                <th>Tytuł operacji</th>
                                <th class="text-end">Kwota</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php foreach ($tx_list as $row): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td><?= $row['tx_date'] ?></td>
                                <td><code><?= $row['sender_iban'] ?></code></td>
                                <td class="fw-semibold"><?= $row['receiver_name'] ?></td>
                                <td><?= $row['title'] ?></td>
                                <td class="text-end fw-bold text-info"><?= number_format($row['amount'], 2, ',', ' ') ?> PLN</td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'ZREALIZOWANY'): ?>
                                        <span class="badge bg-success">ZREALIZOWANY</span>
                                    <?php elseif ($row['status'] === 'W_REALIZACJI'): ?>
                                        <span class="badge bg-warning text-dark">W REALIZACJI</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">AML</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
