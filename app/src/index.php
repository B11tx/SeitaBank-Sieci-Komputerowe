<?php
require_once 'db.php';

$city_search = $_GET['city'] ?? '';
$branches = [];
$sql_error = '';

if ($city_search !== '') {
    $sql = "SELECT branch_code, city, address, phone, hours, branch_type FROM branches WHERE city LIKE '%$city_search%' OR address LIKE '%$city_search%' ORDER BY city ASC";
} else {
    $sql = "SELECT branch_code, city, address, phone, hours, branch_type FROM branches ORDER BY id ASC";
}

$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $branches[] = $row;
    }
} else {
    $sql_error = $conn->error;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>SeitaBank Polska S.A. – Portal Bankowości Nowoczesnej</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b132b; color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-section { background: linear-gradient(135deg, #1c2541 0%, #0b132b 100%); border-bottom: 2px solid #3a506b; padding: 60px 0; }
        .feature-card { background-color: #1c2541; border: 1px solid #3a506b; border-radius: 10px; transition: transform 0.2s; }
        .feature-card:hover { transform: translateY(-4px); }
        .fx-bar { background-color: #111a30; border-bottom: 1px solid #2b3a55; font-size: 0.9rem; }
        .accent-gold { color: #5bc0be; }
        .btn-seita { background-color: #5bc0be; color: #0b132b; font-weight: 700; border: none; }
        .btn-seita:hover { background-color: #46a09e; color: #ffffff; }
    </style>
</head>
<body>

<!-- Pasek kursów walut -->
<div class="fx-bar py-2 px-4 d-flex justify-content-between align-items-center text-secondary">
    <div>
        <strong class="text-light me-3">📈 Kursy Walut SeitaBank (Tabela A):</strong>
        <span class="me-3">EUR/PLN: <strong class="text-success">4.2840</strong></span>
        <span class="me-3">USD/PLN: <strong class="text-success">3.9410</strong></span>
        <span class="me-3">CHF/PLN: <strong class="text-info">4.5620</strong></span>
        <span>GBP/PLN: <strong class="text-warning">5.1180</strong></span>
    </div>
    <div>
        <span>Infolinia 24/7: <strong class="text-light">+48 22 500 10 00</strong> | Nadzór KNF</span>
    </div>
</div>

<!-- Nawigacja główna -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary px-4 py-3">
    <a class="navbar-brand fw-bold fs-4" href="index.php">
        🏛️ <span class="accent-gold">SEITA</span>BANK <span class="fs-6 fw-normal text-secondary">Polska S.A.</span>
    </a>
    <div class="ms-auto d-flex align-items-center">
        <a href="#oferta" class="text-light text-decoration-none me-4">Klienci Indywidualni</a>
        <a href="#korporacje" class="text-light text-decoration-none me-4">Bankowość Korporacyjna</a>
        <a href="#placowki" class="text-light text-decoration-none me-4">Placówki i Bankomaty</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn btn-seita me-2">Mój Panel Bankowy (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-seita px-4">🔒 Zaloguj do SeitaNet24</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Sekcja powitalna Hero -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <span class="badge bg-info text-dark mb-2 px-3 py-2 fw-bold">BANKOWOŚĆ ELEKTRONICZNA SEITANET24</span>
                <h1 class="display-5 fw-bold mb-3">Bezpieczne finanse osobiste i korporacyjne w jednym miejscu.</h1>
                <p class="lead text-secondary mb-4">
                    Zarządzaj rachunkami IBAN, realizuj natychmiastowe rozliczenia międzybankowe ELIXIR i SORBNET oraz korzystaj z wielopoziomowej ochrony depozytów.
                </p>
                <div class="d-flex gap-3">
                    <a href="login.php" class="btn btn-seita btn-lg px-4">Logowanie do Rachunku</a>
                    <a href="#placowki" class="btn btn-outline-light btn-lg px-4">Znajdź Oddział</a>
                </div>
            </div>
            <div class="col-md-5">
                <div class="feature-card p-4 shadow-lg">
                    <h5 class="accent-gold fw-bold mb-3">🛡️ Komunikat Bezpieczeństwa SOC SeitaBank</h5>
                    <p class="small text-light mb-2">
                        Przypominamy, że dostęp do salda rachunku, historii operacji oraz zlecania przelewów wymaga autoryzacji w systemie <strong>SeitaNet24</strong>.
                    </p>
                    <ul class="small text-secondary mb-3">
                        <li>Zawsze sprawdzaj poprawność adresu strony przed wpisaniem hasła.</li>
                        <li>Pracownicy banku nigdy nie proszą o podanie hasła przez telefon.</li>
                        <li>Wszystkie próby nieautoryzowanego dostępu są monitorowane przez system SIEM.</li>
                    </ul>
                    <a href="login.php" class="btn btn-outline-info btn-sm w-100">Przejdź do autoryzacji klienta &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sekcja oferty -->
<div class="container py-5" id="oferta">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <h5 class="accent-gold fw-bold">Konto Seita Prestige</h5>
                <p class="text-secondary small mb-0">0 zł za prowadzenie rachunku, darmowe przelewy krajowe ELIXIR oraz wielowalutowa karta debetowa bez prowizji za przewalutowanie.</p>
            </div>
        </div>
        <div class="col-md-4" id="korporacje">
            <div class="feature-card p-4 h-100">
                <h5 class="accent-gold fw-bold">SeitaCorp Faktoring & Kredyt</h5>
                <p class="text-secondary small mb-0">Finansowanie bieżącej działalności przedsiębiorstw, linie kredytowe w rachunku bieżącym do 10 mln PLN oraz automatyczne księgowanie masowych płatności B2B.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <h5 class="accent-gold fw-bold">Ochrona Kapitału BFG</h5>
                <p class="text-secondary small mb-0">Depozyty zgromadzone w SeitaBank S.A. są objęte ustawowym systemem gwarantowania środków oraz całodobowym nadzorem Centrum Operacji Bezpieczeństwa.</p>
            </div>
        </div>
    </div>

    <!-- Publiczna wyszukiwarka placówek banku -->
    <div class="feature-card p-4" id="placowki">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1">📍 Wyszukiwarka Oddziałów i Punktów Obsługi SeitaBank S.A.</h4>
                <small class="text-secondary">Sprawdź adres, telefon oraz godziny otwarcia najbliższej placówki bankowej</small>
            </div>
        </div>

        <form method="GET" action="index.php#placowki" class="row g-2 mb-4">
            <div class="col-md-9">
                <input type="text" name="city" class="form-control bg-dark text-light border-secondary" placeholder="Wpisz miasto lub ulicę (np. Warszawa, Kraków, Katowice, Wrocław)..." value="<?= htmlspecialchars($city_search) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-seita w-100">Szukaj placówki</button>
                <?php if ($city_search !== ''): ?>
                    <a href="index.php#placowki" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($sql_error): ?>
            <div class="alert alert-danger">
                <strong>Błąd zapytania katalogowego:</strong> <?= htmlspecialchars($sql_error) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-secondary">
                        <th>Kod Placówki</th>
                        <th>Miasto</th>
                        <th>Adres Oddziału</th>
                        <th>Typ Jednostki</th>
                        <th>Godziny Otwarcia</th>
                        <th>Telefon Kontaktowy</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $b): ?>
                    <tr>
                        <td><code class="text-info"><?= $b['branch_code'] ?></code></td>
                        <td class="fw-bold"><?= $b['city'] ?></td>
                        <td><?= $b['address'] ?></td>
                        <td><span class="badge bg-secondary"><?= $b['branch_type'] ?></span></td>
                        <td><?= $b['hours'] ?></td>
                        <td><?= $b['phone'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="border-top border-secondary py-4 text-center text-secondary small mt-5">
    <div class="container">
        &copy; 2026 SeitaBank Polska S.A. Wszelkie prawa zastrzeżone. Infrastruktura: Rocky Linux 9 / Docker Enterprise Cluster.
    </div>
</footer>
</body>
</html>
