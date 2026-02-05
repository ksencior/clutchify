<?php
session_start();
include_once 'connect_db.php';

if(!isset($_SESSION['id']) || !isset($_POST['to_user']) || !isset($_POST['team_id'])) {
    http_response_code(400);
    echo 'Brak danych';
    exit;
}

try {
    $from = $_SESSION['id'];
    $to = (int)$_POST['to_user'];
    $team_id = $_POST['team_id'];

    // Pobierz nazwę drużyny
    $teamNameStmt = $pdo->prepare("SELECT nazwa FROM teams WHERE id = :teamid");
    $teamNameStmt->execute([':teamid' => $team_id]);
    $teamName = $teamNameStmt->fetchColumn();

    if(!$teamName) {
        http_response_code(400);
        echo 'Drużyna nie istnieje';
        exit;
    }


    // Treść powiadomienia
    $content = "Otrzymałeś zaproszenie do drużyny $teamName";
    $type = 'team-request';

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :to_user AND type = 'team-request' AND content = :content");
    $stmt->execute([
        ':to_user' => $to,
        ':content' => $content
    ]);
    $invited = $stmt->fetch();
    if($invited) {
        http_response_code(400);
        echo 'Już wysłałeś zaproszenie tej osobie';
        exit;
    }

    // Zapisz powiadomienie
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, content, type, created_at, seen) VALUES (:to_user, :content, :type, NOW(), 0)");
    $stmt->execute([
        ':to_user' => $to,
        ':content' => $content,
        ':type' => $type
    ]);

    echo 'success';

} catch (PDOException $e) {
    http_response_code(500);
    echo $e->getMessage();
}
?>

