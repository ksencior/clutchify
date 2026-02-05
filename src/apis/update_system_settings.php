<?php
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged'] || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    redirect_to('index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('index.php');
    exit;
}

// Ensure settings table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$allowedKeys = [
    'app_name',
    'primary_color',
    'base_url',
    'twitch_channel',
    'server_ip',
    'rcon_host',
    'rcon_port',
    'rcon_password',
    'steam_api_key',
    'enable_veto'
];

$data = [];
foreach ($allowedKeys as $key) {
    if (!array_key_exists($key, $_POST)) {
        continue;
    }
    $value = is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key];
    if ($key === 'enable_veto') {
        $value = ($value === '1') ? '1' : '0';
    }
    $data[$key] = $value;
}

if (empty($data)) {
    redirect_to('index.php');
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO system_settings (setting_key, setting_value)
    VALUES (:k, :v)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
");

foreach ($data as $k => $v) {
    $stmt->execute([':k' => $k, ':v' => $v]);
}

redirect_to('index.php');
exit;






