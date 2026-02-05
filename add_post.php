<?php 
session_start();
include_once 'src/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    header('Location: index.php');
    exit;
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
    <style>
        textarea {
            resize: vertical;
            width: 70%;
            background-color: rgb(15,15,15);
            color: white;
            border-radius: 4px;
            padding: 8px;
            font-size: 16px;
            border: none;
            margin: auto;
        }
    </style>
</head>
<body>
    <div id="root">
        <?php include 'src/navbar.php'; ?>
        <div class="content">
            <div class="team-form">
                <h1>Dodaj post</h1>
                <form action="src/create_post.php" method="post">
                    <label for="title">Tytuł</label>
                    <input type="text" name="title" id="title" required>
                    <label for="content">Treść</label>
                    <textarea name="content" id="content" rows="5" required></textarea>
                    <input type="submit" value="Dodaj post">
                </form>
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
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>
