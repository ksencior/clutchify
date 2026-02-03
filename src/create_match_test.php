<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$lobbyId = 978045;
$team1 = 1;
$team2 = 2;
    include_once './connect_db.php';
    $stmt = $pdo->prepare("SELECT u.id, u.team_id, u.steam_id FROM users u JOIN teams t1 ON u.team_id = t1.id WHERE t1.id = :t1 
    UNION SELECT u.id, u.team_id, u.steam_id FROM users u JOIN teams t2 ON u.team_id = t2.id WHERE t2.id = :t2");
    $stmt->execute([':t1' => $team1, ':t2' => $team2]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id FROM games WHERE lobby_id = :lid ORDER BY id DESC LIMIT 1");
    $stmt->execute([':lid' => $lobbyId]);
    $gameId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT t1.nazwa AS team1_name, t2.nazwa AS team2_name FROM games g JOIN teams t1 ON g.team1 = t1.id JOIN teams t2 ON g.team2 = t2.id WHERE g.lobby_id = :lid");
    $stmt->execute([':lid'=> $lobbyId]);
    $druzyny = $stmt->fetch();

    $team1name = $druzyny['team1_name'];
    $team2name = $druzyny['team2_name'];

    // pobierz mapy
    $stmt = $pdo->prepare("SELECT map FROM games_maps WHERE lobby_id = :lid ORDER BY order_num ASC");
    $stmt->execute([':lid' => $lobbyId]);
    $maps = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // mapowanie nazw na cs'owe
    $mapTranslation = [
        "mirage"   => "de_mirage",
        "inferno"  => "de_inferno",
        "nuke"     => "de_nuke",
        "overpass" => "de_overpass",
        "ancient"  => "de_ancient",
        "dust ii"  => "de_dust2",
        "dust2"    => "de_dust2",
        "train"    => "de_train",
        "vertigo"  => "de_vertigo",
    ];

    // zamień mapy na cs'owe ID
    $maps = array_map(function($m) use ($mapTranslation) {
        $key = strtolower(trim($m));
        return $mapTranslation[$key] ?? ('de_' . $key);
    }, $maps);

    // pobierz graczy z team1 i team2
    $stmt = $pdo->prepare("SELECT u.id, u.team_id, u.steam_id, u.username 
        FROM users u 
        WHERE u.team_id IN (:t1, :t2)");
    $stmt->execute([':t1' => $team1, ':t2' => $team2]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // rozdziel na teamy
    $team1players = [];
    $team2players = [];
    foreach ($players as $p) {
    if ($p['team_id'] == $team1) {
        $team2players[$p['steam_id']] = $p['username'];
    } else {
        $team1players[$p['steam_id']] = $p['username'];
    }
}

    // zbuduj strukturę dla MatchZy
    $MatchZyMatch = [
        "matchid"   => 1,
        "team1"     => [
            "name"    => $team1name,
            "players" => $team1players
        ],
        "team2"     => [
            "name"    => $team2name,
            "players" => $team2players
        ],
        "num_maps"  => count($maps),
        "maplist"   => $maps,
        "map_sides" => [ "knife", "team1_ct", "team2_ct" ] // to możesz ustawiać dynamicznie
    ];

    // zapakuj do JSON
    $json = json_encode($MatchZyMatch, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(__DIR__. "/tmp/matchzy_config.json", $json);