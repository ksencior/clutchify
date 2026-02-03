<?php
session_start();
include_once 'connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    http_response_code(403);
    exit;
}

$teamId = $_SESSION['team_id'];
$userId = $_SESSION['id'];
$message = trim($_POST['message']);

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Wiadomość nie może być pusta']);
    exit;
}

$message = substr(trim($_POST['message']), 0, 250);

$stmt = $pdo->prepare("INSERT INTO team_chat_messages (team_id, user_id, message) VALUES (:tid, :uid, :msg)");
$stmt->execute([':tid'=>$teamId, ':uid'=>$userId, ':msg'=>$message]);

echo json_encode(['success' => true]);
?>