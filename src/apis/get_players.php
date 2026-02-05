<?php
session_start();
require_once __DIR__ . '/../core/connect_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE team_id IS NULL AND steam_id IS NOT NULL ORDER BY RAND() ASC LIMIT 7");
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($players);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]);
}


