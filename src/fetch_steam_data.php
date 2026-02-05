<?php
session_start();
require_once 'connect_db.php';

$apiKey = ''; // <<< ZMIEŃ TO
$steamid = $_SESSION['steam_id'] ?? NULL;
$url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$apiKey}&steamids={$steamid}";

if (!$steamid) {
    return;
}

$response = file_get_contents($url);
$data = json_decode($response, true);

if (!empty($data['response']['players'][0])) {
    $player = $data['response']['players'][0];
    $avatar = $player['avatarfull']; // pełny avatar 184x184
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET avatar_url = :avatar WHERE id = :id");
        $stmt->execute([
            ':avatar' => $avatar,
            ':id' => $_SESSION['id']
        ]);

        $_SESSION['avatar_url'] = $avatar;
        header("Location: ../index.php");
    } catch (PDOException $e) {
        echo $e; exit;
    }
} else {
    return;
}

