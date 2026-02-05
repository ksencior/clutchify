<?php
$host = '127.0.0.1:3306';
$dbname = 'tournament_app_template';
$user = 'root';
$password = '';

require_once __DIR__ . '/Config.php';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
    Config::init($pdo);

    // echo 'Połączono!';
} catch (PDOException $e) {
    die('Błąd połączenia: ' . $e->getMessage());
}
