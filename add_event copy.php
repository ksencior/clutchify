<?php 
include_once 'src/core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    redirect_to('login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Wydarzenie | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/chat.js"></script>
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content">
            <div class="team-form">
                <h1>Dodaj wydarzenie</h1>
                <form action="src/apis/create_event.php" method="post">
                    <label for="nazwa">Nazwa</label>
                    <input type="text" name="nazwa" id="nazwa">
                    <label for="type">Typ</label>
                    <select name="typ" id="type">
                        <option value="Zapisy">Zapisy</option>
                    </select>
                    <label for="koniec">Data zakończenia</label>
                    <input type="datetime-local" name="koniec" id="koniec">
                    <input type="submit" value="Dodaj wydarzenie">
                </form>
            </div>
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









