<?php
session_start();
include_once 'connect_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

// Pobierz powiadomienia
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 20");
$stmt->execute([':uid' => $_SESSION['id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Oznacz jako przeczytane (seen = 1)
$update = $pdo->prepare("UPDATE notifications SET seen = 1 WHERE user_id = :uid AND seen = 0");
$update->execute([':uid' => $_SESSION['id']]);

echo json_encode($notifications);
