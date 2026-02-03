<?php
session_start();
include_once 'connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$searchTerm = $_GET['q'] ?? '';

if (strlen($searchTerm) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, avatar_url FROM users WHERE username LIKE :term AND team_id IS NULL AND steam_id IS NOT NULL LIMIT 7");
    $stmt->execute([':term' => "%$searchTerm%"]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($players);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]);
}
