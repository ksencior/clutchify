<?php
include_once 'connect_db.php';

try {
    // sprawdzamy czy zapisy się skończyły
    $stmt = $pdo->prepare("SELECT * FROM events WHERE type = 'Zapisy' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event || strtotime($event['ending_at']) > time()) {
        die("Zapisy jeszcze trwają!");
    }

    // sprawdzamy czy pierwsza runda już nie istnieje
    $check = $pdo->query("SELECT COUNT(*) FROM mecze WHERE round = 1")->fetchColumn();
    if ($check > 0) {
        die("Pierwsza runda już została wygenerowana!");
    }

    // pobieramy drużyny
    $teams = $pdo->query("SELECT id FROM teams")->fetchAll(PDO::FETCH_COLUMN);

    if (count($teams) < 2) {
        die("Za mało drużyn!");
    }

    shuffle($teams);

    $round = 1;
    $matchNumber = 1;

    for ($i = 0; $i < count($teams); $i += 2) {
        $team1 = $teams[$i];
        $team2 = isset($teams[$i+1]) ? $teams[$i+1] : null;

        $stmt = $pdo->prepare("
            INSERT INTO mecze (team1, team2, round, match_number, termin) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$team1, $team2, $round, $matchNumber]);

        $matchNumber++;
    }

    //echo "Pierwsza runda wygenerowana!";
} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
