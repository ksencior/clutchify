<?php
require_once __DIR__ . '/Config.php';

// Load .env (if present)
$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $val] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $val = trim($val);
        $val = trim($val, "\"'");
        if ($key !== '') {
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
} else {
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (php_sapi_name() !== 'cli' && $script !== 'install.php') {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir = rtrim($dir, '/');
        $installUrl = ($dir ? $dir : '') . '/install.php';
        header('Location: ' . $installUrl);
        exit;
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $base = $_ENV['BASE_URL'] ?? Config::get('base_url', '/clutchify');
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

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'tournament_app_template';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
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




