<?php
require_once 'src/core/connect_db.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'ok',
    'app_name' => Config::get('app_name', 'Clutchify.gg'),
    'base_url' => Config::get('base_url', '/clutchify'),
    'time' => date('c')
]);
