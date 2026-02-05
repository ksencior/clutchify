<?php
session_start();
require_once __DIR__ . '/steam/openid.php';
require_once __DIR__ . '/../core/connect_db.php';

$openid = new LightOpenID('zsnturniej.0bg.pl');

if (!$openid->mode) {
    // Rozpocznij autoryzację przez Steam
    $openid->identity = 'http://specs.openid.net/auth/2.0/identifier_select';
    header('Location: ' . $openid->authUrl());
    exit;
} elseif ($openid->mode === 'cancel') {
    echo 'Logowanie przez Steam anulowane.';
} elseif ($openid->validate()) {

    // Pobierz steamID64 z URL zwróconego przez Steam
    $id = $_GET['openid_claimed_id'];
    if (preg_match("#^https://steamcommunity.com/openid/id/([0-9]+)$#", $id, $matches)) {
        $steamid64 = $matches[1];
        
        // zapis do bazy
        $stmt = $pdo->prepare("UPDATE users SET steam_id = :steamid WHERE id = :id");
        $stmt->execute([
            ':steamid' => $steamid64,
            ':id' => $_SESSION['id']
        ]);
    
        $_SESSION['steam_id'] = $steamid64;
        include_once __DIR__ . '/fetch_steam_data.php';
        header('Location: ../index.php');
        exit;
    } else {
        die("Nie udało się odczytać steamID.");
    }
} else {
    echo "Błąd logowania przez Steam.";
}


