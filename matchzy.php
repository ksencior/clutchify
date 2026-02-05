<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$lobbyID = 168056;
include 'src/core/connect_db.php';
$currentMapNum = 1;
$nextMapNum = $currentMapNum + 1;
$stmt = $pdo->prepare("SELECT map FROM games_maps WHERE lobby_id = ?");
$stmt->execute([$lobbyID]);
$maps = $stmt->fetchAll();
$mapsArray = [];
foreach ($maps as $map) { 
$mapsArray[] = $map['map']; // wrzucamy do tablicy
}

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
}, $mapsArray);
$currentMap = $maps[$currentMapNum-1];
$nextMap = $maps[$nextMapNum-1];
// $stmt = $pdo->prepare("INSERT INTO games (lobby_id, match_id, team1, team2, current_map, status) 
// VALUES (:lid, :mid, :t1, :t2, :map, 'playing')");
// $stmt->execute([':lid'=>$lobbyID, ':mid'=>$matchId, ':t1'=>$teamy['team1'], ':t2'=>$teamy['team2'], ':map' => $map]);

//ranking
$stmt = $pdo->prepare("SELECT id FROM games WHERE lobby_id = :lid AND current_map = :map");
$stmt->execute([':lid'=>$lobbyID, ':map'=>$currentMap]);
$gameId = $stmt->fetchColumn();

// pobierz top 3 graczy
$stmt = $pdo->prepare("
SELECT user_id, team_id, kills, deaths, assists, headshots, mvps,
    ROUND((kills + assists) / NULLIF(deaths,0), 2) AS kda
FROM game_players
WHERE game_id = :game_id
ORDER BY kda DESC, kills DESC
LIMIT 3
");
$stmt->execute([':game_id'=>$gameId]);
$bestPlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// wstaw do rankingu
$insert = $pdo->prepare("
INSERT INTO ranking (user_id, global_kills, global_deaths, global_assists, global_headshots, global_mvps, global_kd)
VALUES (:uid, :kills, :deaths, :assists, :hs, :mvps, :kd)
ON DUPLICATE KEY UPDATE 
    global_kills = global_kills + VALUES(global_kills),
    global_deaths = global_deaths + VALUES(global_deaths),
    global_assists = global_assists + VALUES(global_assists),
    global_headshots = global_headshots + VALUES(global_headshots),
    global_mvps = global_mvps + VALUES(global_mvps),
    global_kd = ROUND(global_kills / NULLIF(global_deaths, 0), 2)
");

foreach ($bestPlayers as $p) {
$insert->execute([
    ':uid'    => $p['user_id'],
    ':kills'  => $p['kills'],
    ':deaths' => $p['deaths'],
    ':assists'=> $p['assists'],
    ':hs'     => $p['headshots'],
    ':mvps'   => $p['mvps'],
    ':kd'     => $p['deaths'] > 0 ? round($p['kills'] / $p['deaths'], 2) : $p['kills'] // lokalny KD do wrzucenia
]);
}

