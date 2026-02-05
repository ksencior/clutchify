<?php
$host = '127.0.0.1:3306';
$dbname = 'tournament_app_template';
$user = 'root';
$password = '';

require_once __DIR__ . '/Config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $base = Config::get('base_url', '/clutchify');
        $base = trim($base);
        if ($base === '' || $base === '/') {
            $base = '';
        } else {
            $base = '/' . trim($base, '/');
        }

        $path = ltrim($path, '/');
        if ($path === '') {
            return $base !== '' ? $base : '/';
        }
        return $base . '/' . $path;
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to(string $path): void {
        header('Location: ' . base_url($path));
        exit;
    }
}

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




