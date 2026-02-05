<?php 
session_start();
include_once 'src/connect_db.php';

if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
    header('Location: index.php');
    exit;
}
$error = NULL;
if (isset($_GET['err']) && $_GET['err'] != '') {
    $error = $_GET['err'];
}
$errorMsg = '';
switch($error) {
    case 'no-inputs':
        $errorMsg = "Wszystkie pola są wymagane!";
        break;
    case 'bad-password':
        $errorMsg = "Hasło musi mieć co najmniej 3 znaki.";
        break;
    case 'bad-username':
        $errorMsg = "Nick musi mieć co najmniej 3 znaki.";
        break;
    case 'email-not-valid':
        $errorMsg = "E-mail musi być zarejestrowany w domenie szkolnej. (@zsngasawa.pl)";
        break;
    case 'user-exists':
        $errorMsg = "Użytkownik z tym adresem e-mail / nickiem już istnieje.";
        break;
    case 'passwords-no-match':
        $errorMsg = "Podane hasła nie są identyczne.";
        break;
    case 'server':
        $errorMsg = "Wystąpił błąd serwera.";
        break;
    default:
        $errorMsg = '';
        break;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarejestruj się</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon">
</head>
<body>
    <div id="root" class="root-login">
        <div class="lines">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
        
        <div class="login-box">
            <h1>Rejestracja</h1>
            <form action="src/zarejestruj.php" method="post">
                <label for="mc_nickname">Nick</label>
                <input type="text" name="mc_nickname" id="mc_nickname" required>
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Hasło</label>
                <p style="font-size: 0.85em; color: #555; width: 70%; margin: auto;">
                    <i class="fa-solid fa-circle-info"></i>
                    Twoje hasło jest zaszyfrowane i bezpieczne. Nikt, nawet administratorzy serwisu, nie mają do niego dostępu. 
                    Więcej informacji znajdziesz <a href="policy.html" target="_blank">w polityce prywatności</a>.
                </p>
                <input type="password" name="password" id="password" required>
                <label for="password">Powtórz hasło</label>
                <input type="password" name="password2" id="password2" required>
                <input type="submit" value="Zarejestruj się">
                <span style="color: red;">
                    <?php 
                        if (isset($error)) {
                            echo $errorMsg;
                        }
                    ?>
                </span>
                <a href="login.php">Masz już konto?</a>
            </form>
        </div>
    </div>
</body>
</html>

