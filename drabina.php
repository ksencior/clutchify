<?php 
session_start();
include_once 'src/connect_db.php';
$teamID = 0;
$isLeader = false;
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZSN Champions III</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="src/notifications.js"></script>
    <script src="src/chat.js"></script>
    <script>
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

    </script>
</head>
<body>
    <div id="root">
        <?php include 'src/navbar.php'; ?>
        <div class="content" style="flex-direction: column; align-items: center;">
                <h1>Drabinka turniejowa</h1>
                <div class="bracket">
                    <?php
                    // pobierz mecze
                    $stmt = $pdo->query("SELECT * FROM mecze ORDER BY round, match_number");
                    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($matches) {
                        // grupowanie po rundach
                        $rounds = [];
                        foreach ($matches as $m) {
                            $rounds[$m['round']][] = $m;
                        }

                        foreach ($rounds as $roundNum => $roundMatches) {
                            echo "<div class='round'>";
                            echo "<h2>Runda $roundNum</h2>";

                            foreach ($roundMatches as $match) {
                                // pobierz nazwy drużyn
                                $team1 = '?';
                                $team2 = "?";

                                if ($match['team1']) {
                                    $t = $pdo->prepare("SELECT nazwa FROM teams WHERE id = ?");
                                    $t->execute([$match['team1']]);
                                    $team1 = $t->fetchColumn();
                                }

                                if ($match['team2']) {
                                    $t = $pdo->prepare("SELECT nazwa FROM teams WHERE id = ?");
                                    $t->execute([$match['team2']]);
                                    $team2 = $t->fetchColumn();
                                }
                                $winner = $match['winner_id'];
                                if ($winner == $match['team1']) {
                                    $team1Class = "winner";
                                } else {
                                    $team1Class = "";
                                }
                                if ($winner == $match['team2']) {
                                    $team2Class = "winner";
                                } else {
                                    $team2Class = "";
                                }
                                if ($match['winner_id'] == null) {
                                    $team1Class = "";
                                    $team2Class = "";
                                }
                                echo "<div class='match'>";
                                echo "<div class='team ".$team1Class." '>" . htmlspecialchars($team1) . "</div>";
                                echo "<div class='team ".$team2Class." '>" . htmlspecialchars($team2) . "</div>";
                                echo "</div>";
                            }

                            echo "</div>";
                        }
                    } else {
                        echo '
                        <script>
                            document.querySelector(".content").className += " ranking";
                            document.querySelector(".content").innerHTML += "<h1>No patrz, pusto tu..</h1> <h3>Drabinka pojawi się po zapisach.</h3>";
                            document.querySelector(".content h1").style.display = "none";
                        </script>';
                    }
                    ?>
                </div>
        </div>
        <?php include 'src/sidebar_load.php';?>
        <div class="notifications-menu"></div>
        <div class="team-chat-window">
            <div class="chat-header">
                <h3>Czat drużynowy</h3>
            </div>
            <div class="chat-messages"></div>
            <div class="chat-input">
                <input type="text" placeholder="Napisz wiadomość...">
                <button class="send-chat"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    <script src="src/mobile-menu.js"></script>
</body>
</html>