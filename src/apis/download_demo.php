<?php
require_once __DIR__ . '/../core/connect_db.php';
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
    exit;
}

if (empty($_GET['id'])) {
    http_response_code(400);
    echo "Brak ID meczu.";
    exit;
}

$matchId = (int)$_GET['id'];
$filePath = __DIR__ . "/../../demos/match_{$matchId}_demos.tar";

if (!file_exists($filePath)) {
    http_response_code(404);
    echo "Plik nie istnieje.";
    exit;
}

// Ustawienie nagłówków do pobrania
header('Content-Description: File Transfer');
header('Content-Type: application/x-tar');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;








