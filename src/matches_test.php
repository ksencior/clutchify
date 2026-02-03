<?php 
include_once 'connect_db.php';
date_default_timezone_set("Europe/Warsaw");
// pseudo-cron: sprawdź czy trzeba wygenerować mecze
//die('Unauthorized access');
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE type = 'Zapisy' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    $event['ending_at'] = '2025-12-10 13:51:00';
    if ($event) {
        echo 'Event ending at: ' . $event['ending_at'] . '<br>';
        echo strtotime($event['ending_at']);
        echo '<br>';
        $a = time();
        echo $a;
        echo '<br>';
        echo date('Y-m-d H:i:s', $a) . ' ' . date('Y-m-d H:i:s', strtotime($event['ending_at']));
    }
    if ($event && strtotime($event['ending_at']) <= time()) {
        echo '<br> TERAZ! <br>';
        $check = $pdo->query("SELECT COUNT(*) FROM mecze WHERE round = 1")->fetchColumn();
        if ($check == 0) {
            // pobierz drużyny
            $teams = $pdo->query("SELECT id FROM teams")->fetchAll(PDO::FETCH_COLUMN);
            if (count($teams) >= 2) {
                shuffle($teams);
                $matchNumber = 1;
                $currentDate = new DateTime('next week');
                $currentDate->modify('18:00');
                echo 'Current date start: ' . $currentDate->format('Y-m-d H:i:s') . '<br>';
                foreach (array_chunk($teams, 2) as $pair) {
                    echo 'Pair: ' . implode(', ', $pair) . '<br>';
                }
            }
        }
    }
        
    // }
} catch (PDOException $e) {
    // echo "Błąd CRONa: " . $e->getMessage();
}