<?php 
session_start();
include_once 'src/core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
    exit;
}

if (!empty($_SESSION['team_id'])) {
    header('Location: index.php');
    exit;
}
$zapisy = false;
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE `type`='Zapisy'");
    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $zapisy = true;
        $ending_at = $res['ending_at'];
    }
} catch (PDOException $e) {
    echo $e->getMessage();
}

if (isset($_GET['error'])) {
    if ($_GET['error'] == 'empty_fields') {
        echo "<script>alert('Podaj nazwe druzyny i skrot.');</script>";
    } elseif ($_GET['error'] == 'invalid_name') {
        echo "<script>alert('Nazwa druzyny musi mieć od 3 do 20 znaków.');</script>";
    } elseif ($_GET['error'] == 'invalid_skrot') {
        echo "<script>alert('Skrót drużyny musi mieć od 1 do 4 znaków.');</script>";
    } else {
        echo "<script>alert('Wystąpił nieznany błąd.');</script>";
    }
}

$steamConnected = false;
try {
    $stmt = $pdo->prepare("SELECT steam_id FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $_SESSION['id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res && !empty($res['steam_id'])) {
        $steamConnected = true;
    }
} catch (PDOException $e) {
    echo $e->getMessage();
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
    <script src="assets/js/notifications.js?v=<?= time() ?>"></script>
    <script src="assets/js/chat.js"></script>
    <style>
        form input[type="submit"]:disabled {
            background-color: gray;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content <?= $zapisy? '' : 'ranking' ?>">
            <?php if ($zapisy): ?>
            <div class="team-form">
                <h1>Stwórz drużynę</h1>
                <div id="countdown-timer"></div>
                <form action="src/apis/create_team.php" method="post">
                    <label for="nazwa">Nazwa drużyny</label>
                    <input type="text" name="nazwa" id="nazwa" maxlength="20" minlength="3" required>
                    <label for="skrot">Tag drużyny (np. MD16 dla MANDARYNKI SZESNASTE)</label>
                    <input type="text" name="skrot" id="skrot" maxlength="4" minlength="1" required>
                    <input type="submit" value="Stwórz drużynę" <?= $steamConnected ? '' : 'disabled title="Aby stworzyć drużynę, musisz połączyć konto Steam w ustawieniach."' ?>>
                </form>
            </div>
            <script>
                // Pass PHP ending_at to JS
                const endingAt = "<?= $ending_at ?>";
                const timer = document.getElementById("countdown-timer");
                function updateCountdown() {
                    const endTime = new Date(endingAt.replace(' ', 'T')).getTime();
                    const now = new Date().getTime();
                    let distance = endTime - now;

                    if (distance < 0) {
                        timer.innerHTML = "Zapisy zakończone!";
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    timer.innerHTML =
                        `${days} dni ${hours} godz. ${minutes} min ${seconds} sek`;
                }
                updateCountdown();
                setInterval(updateCountdown, 1000);
            </script>
            <?php else: ?>
                <h1>No patrz, pusto tu..</h1>
                <h3>Zapisy jeszcze nie ruszyły.</h3>
            <?php endif;?>
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
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>

