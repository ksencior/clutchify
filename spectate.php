<?php 
session_start();
include_once 'src/core/connect_db.php';
$teamID = 0;
$isLeader = false;
if ((!isset($_SESSION['logged']) || !$_SESSION['logged']) || (!isset($_SESSION['isSpectator']) || $_SESSION['isSpectator'] == false)) {
    header('Location: login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZSN Champions III</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/chat.js"></script>
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
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content" style="flex-direction: column; align-items: center; flex-wrap: nowrap;">
                <h1>Mecze</h1>
                <?php
                //team-score-win - dla wygranej
                //team-score-loose - dla przegranej
                // pobierz mecze
                $stmt = $pdo->prepare("SELECT 
                                m.*, 
                                t1.nazwa AS team1_name, 
                                t2.nazwa AS team2_name 
                            FROM mecze m
                            JOIN teams t1 ON m.team1 = t1.id
                            JOIN teams t2 ON m.team2 = t2.id
                            WHERE m.winner_id IS NOT NULL
                            ORDER BY m.termin ASC");
                $stmt->execute();
                $matches = $stmt->fetchAll();

                if (!empty($matches)) {
                    echo '<div class="mecze-container">';
                     foreach($matches as $match){
                        $termin = new DateTime($match['termin']);
                        $now = new DateTime();
                        if($now > $termin){
                            $status = "W trakcie";
                        } else {
                            $status = "Nadchodzący";
                        }

                        if ($match['winner_id'] != NULL) {
                            $status = "Zakończony";
                        }

                        $dni = ["ND", "PN", "WT", "ŚR", "CZ", "PT", "SB"];
                        $miesiace = ["STY", "LUT", "MAR", "KWI", "MAJ", "CZE", "LIP", "SIE", "WRZ", "PAŹ", "LIS", "GRU"];

                        $dzienTyg = $dni[(int)$termin->format('w')]; // numer dnia tygodnia (0=ND)
                        $dzien = $termin->format('d');
                        $miesiac = $miesiace[(int)$termin->format('n') - 1]; // numer miesiąca (1-12)
                        $godzina = $termin->format('H:i');

                        $terminFormatted = "$dzienTyg. $dzien $miesiac, $godzina";

                        echo '<div class="mecz" data-matchid="'.htmlspecialchars($match['id']).'" data-gameip="37.221.94.158:27020">
                                <div class="team-display left">
                                    <p>'.htmlspecialchars($match['team1_name']).'</p>
                                </div>
                                <div class="mecz-info">
                                    <p class="status">'.$status.'</p>
                                    <p class="termin">'.$terminFormatted.'</p>
                                    <div class="score">
                                        <span class="">'.htmlspecialchars($match['team1_wins']).'</span><span> - </span><span class="">'.htmlspecialchars($match['team2_wins']).'</span>
                                    </div>
                                </div>
                                <div class="team-display right">
                                    <p>'.htmlspecialchars($match['team2_name']).'</p>
                                </div>
                            </div>';
                     }
                    echo '</div>';
                    
                } else {
                    echo '
                    <script>
                        document.querySelector(".content").className += " ranking";
                        document.querySelector(".content").innerHTML += "<h1>No patrz, pusto tu..</h1> <h3>Mecze wyświetlą się po zapisach.</h3>";
                        document.querySelector(".content h1").style.display = "none";
                    </script>';
                }
                ?>
        </div>
        <?php include 'src/views/partials/sidebar_load.php';?>
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
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const matchElements = document.querySelectorAll('.mecz');
        matchElements.forEach(el => {
            el.addEventListener('click', () => {
                const ip = el.getAttribute('data-gameip');
                navigator.clipboard.writeText(ip).then(() => {
                    alert('Adres IP serwera skopiowany do schowka: ' + ip);
                }).catch(err => {
                    console.error('Błąd przy kopiowaniu do schowka: ', err);
                });
            });
        });
    });
    </script>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>

