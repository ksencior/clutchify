<?php
require_once __DIR__ . '/../core/connect_db.php';

$apiKey = ''; // <<< ZMIEŃ TO
$steamid = $_SESSION['steam_id'] ?? NULL;
$url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$apiKey}&steamids={$steamid}";

if (!$steamid) {
    return;
}

// If API key is missing, set default avatar and exit early to avoid failures
if (empty($apiKey)) {
    $defaultAvatar = 'assets/img/avatar_default.png';
    try {
        $stmt = $pdo->prepare("UPDATE users SET avatar_url = :avatar WHERE id = :id");
        $stmt->execute([
            ':avatar' => $defaultAvatar,
            ':id' => $_SESSION['id']
        ]);
        $_SESSION['avatar_url'] = $defaultAvatar;
        header("Location: /clutchify/index.php");
    } catch (PDOException $e) {
        echo $e; exit;
    }
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
        header("Location: /clutchify/index.php");
    } catch (PDOException $e) {
        echo $e; exit;
    }
} else {
    return;
}


