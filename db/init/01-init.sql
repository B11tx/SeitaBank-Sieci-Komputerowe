CREATE DATABASE IF NOT EXISTS seitabank_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE seitabank_db;


CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_code VARCHAR(16) NOT NULL UNIQUE,
    city VARCHAR(64) NOT NULL,
    address VARCHAR(150) NOT NULL,
    phone VARCHAR(32) NOT NULL,
    hours VARCHAR(64) NOT NULL,
    branch_type ENUM('Oddział Korporacyjny', 'Oddział Detaliczny', 'Centrum Private Banking') DEFAULT 'Oddział Detaliczny'
) ENGINE=InnoDB;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(128) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    role ENUM('client', 'corporate', 'employee', 'admin') DEFAULT 'client',
    iban VARCHAR(34) NOT NULL,
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    email VARCHAR(120) NOT NULL,
    pesel VARCHAR(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_iban VARCHAR(34) NOT NULL,
    receiver_iban VARCHAR(34) NOT NULL,
    receiver_name VARCHAR(120) NOT NULL,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status ENUM('ZREALIZOWANY', 'W_REALIZACJI', 'WERYFIKACJA_AML') DEFAULT 'ZREALIZOWANY',
    tx_date DATETIME NOT NULL,
    INDEX idx_sender (sender_iban),
    INDEX idx_title (title),
    INDEX idx_tx_date (tx_date)
) ENGINE=InnoDB;


CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64),
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    action VARCHAR(120),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


INSERT INTO branches (branch_code, city, address, phone, hours, branch_type) VALUES
('SB-WAW-01', 'Warszawa', 'ul. Rondo ONZ 1 (Centrala)', '+48 22 500 10 00', 'Pn-Pt: 08:00 - 18:00', 'Oddział Korporacyjny'),
('SB-KRK-01', 'Kraków', 'al. Pokoju 5', '+48 12 400 20 10', 'Pn-Pt: 09:00 - 17:00', 'Oddział Korporacyjny'),
('SB-KAT-01', 'Katowice', 'ul. Chorzowska 107', '+48 32 300 30 20', 'Pn-Pt: 08:30 - 16:30', 'Oddział Detaliczny'),
('SB-WRO-01', 'Wrocław', 'ul. Powstańców Śląskich 95', '+48 71 700 40 30', 'Pn-Pt: 09:00 - 17:00', 'Centrum Private Banking'),
('SB-POZ-01', 'Poznań', 'ul. Andersa 3', '+48 61 800 50 40', 'Pn-Pt: 09:00 - 17:00', 'Oddział Detaliczny'),
('SB-GDA-01', 'Gdańsk', 'al. Grunwaldzka 472', '+48 58 600 60 50', 'Pn-Pt: 08:00 - 16:00', 'Oddział Korporacyjny'),
('SB-LOD-01', 'Łódź', 'al. Piłsudskiego 22', '+48 42 200 70 60', 'Pn-Pt: 09:00 - 17:00', 'Oddział Detaliczny'),
('SB-SZC-01', 'Szczecin', 'Brama Portowa 1', '+48 91 900 80 70', 'Pn-Pt: 09:00 - 16:30', 'Oddział Detaliczny');



INSERT INTO users (username, password_hash, full_name, role, iban, balance, email, pesel) VALUES
('admin', '482c811da5d5b4bc6d497ffa98491e38', 'Główny Administrator SeitaBank', 'admin', 'PL61109010140000071219810001', 9450000.00, 'admin@seitabank.pl', '80010112345'),
('m.kowalski', 'd8578edf8458ce06fbc5bb76a58c5ca4', 'Marek Kowalski (Konto Osobiste)', 'client', 'PL61109010140000071219810002', 48520.50, 'm.kowalski@klient.pl', '85041209876'),
('a.nowak', 'e10adc3949ba59abbe56e057f20f883e', 'Anna Nowak (Doradca Klienta)', 'employee', 'PL61109010140000071219810003', 19400.00, 'a.nowak@seitabank.pl', '92072345612'),
('k.wisniewski', '0d107d09f5bbe40cade3de5c71e9e9b7', 'Krzysztof Wiśniewski Sp. z o.o.', 'corporate', 'PL61109010140000071219810004', 342800.75, 'biuro@wisniewski-transport.pl', '78110567890'),
('p.zielinski', '4c25b32a72699ed712dff00c709d2c59', 'Piotr Zieliński', 'client', 'PL61109010140000071219810005', 67300.20, 'p.zielinski@poczta.pl', '88031923456'),
('zarzad_vip', '5ebe2294ecd0e0f08eab7690d2a6ee69', 'Rachunek Powierniczy Zarządu SeitaBank', 'admin', 'PL61109010140000071219810006', 25800000.00, 'zarzad@seitabank.pl', '71092811223');

DELIMITER $$
CREATE PROCEDURE init_seitabank_data()
BEGIN
    DECLARE i INT DEFAULT 7;
    DECLARE j INT DEFAULT 1;
    
    
    WHILE i <= 100 DO
        INSERT INTO users (username, password_hash, full_name, role, iban, balance, email, pesel)
        VALUES (
            CONCAT('klient', i),
            MD5(CONCAT('haslo', i)),
            CONCAT('Klient Indywidualny #', i),
            IF(MOD(i, 5) = 0, 'corporate', 'client'),
            CONCAT('PL6110901014000007121981', LPAD(i, 4, '0')),
            ROUND(1500 + (RAND() * 180000), 2),
            CONCAT('klient', i, '@seitabank-online.pl'),
            CONCAT(LPAD(MOD(i + 65, 99), 2, '0'), '0618', LPAD(i, 5, '0'))
        );
        SET i = i + 1;
    END WHILE;

   
    WHILE j <= 15000 DO
        INSERT INTO transactions (sender_iban, receiver_iban, receiver_name, title, amount, status, tx_date)
        VALUES (
            CONCAT('PL6110901014000007121981', LPAD(MOD(j, 100) + 1, 4, '0')),
            CONCAT('PL2711402004000030020135', LPAD(MOD(j * 13, 800) + 1, 4, '0')),
            ELT(MOD(j, 8) + 1, 'Orlen S.A.', 'PGE Obrót S.A.', 'Urząd Skarbowy Warszawa', 'ZUS Oddział Centralny', 'Polkomtel Sp. z o.o.', 'Kancelaria Prawna Lex-Partner', 'Trans-Logistyka Polska S.A.', 'Dom Maklerski SeitaInvest'),
            CONCAT(
                ELT(MOD(j, 6) + 1, 'Faktura VAT nr FV/2026/', 'Przelew krajowy ELIXIR nr ', 'Wynagrodzenie zasadnicze okres #', 'Spłata raty leasingowej umowy #', 'Rozliczenie kontraktu B2B nr ', 'Opłata za licencje serwerowe #'),
                j
            ),
            ROUND(100 + (RAND() * 35000), 2),
            ELT(MOD(j, 10) + 1, 'ZREALIZOWANY', 'ZREALIZOWANY', 'ZREALIZOWANY', 'ZREALIZOWANY', 'ZREALIZOWANY', 'ZREALIZOWANY', 'ZREALIZOWANY', 'ZREALIZOWANY', 'W_REALIZACJI', 'WERYFIKACJA_AML'),
            DATE_SUB(NOW(), INTERVAL MOD(j, 43200) MINUTE)
        );
        SET j = j + 1;
    END WHILE;
END$$
DELIMITER ;

CALL init_seitabank_data();
DROP PROCEDURE init_seitabank_data;
