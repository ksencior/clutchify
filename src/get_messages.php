<?php
session_start();
include_once 'connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    http_response_code(403);
    exit;
}

$teamId = $_SESSION['team_id'];

$result = $pdo->query("SELECT m.*, u.username, u.avatar_url 
                        FROM team_chat_messages m
                        JOIN users u ON m.user_id = u.id
                        WHERE m.team_id = $teamId 
                        ORDER BY m.created_at DESC
                        LIMIT 50");

$messages = $result->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(array_reverse($messages));


?>

