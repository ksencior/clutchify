<?php 
session_start();
include_once 'src/core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
}

$sql = $pdo->prepare("SELECT * FROM users WHERE id=:id;");
$sql->execute([':id'=>$_SESSION['id']]);
$result = $sql->fetch(PDO::FETCH_ASSOC);

if($result) {
    $imie = $result['imie'];
    $plec = $result['plec'];
    $klasa = $result['klasa'];
}

$alertMessage = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'true') {
        
    } elseif ($_GET['success'] === 'false') {
        switch ($_GET['err'] ?? '') {
            case 'wrong-password':
                $alertMessage = 'Niepoprawne hasło.';
                break;
            case 'wrong-email':
                $alertMessage = 'Nieprawidłowy adres e-mail.';
                break;
            case 'passwords-dont-match':
                $alertMessage = 'Nowe hasła się nie zgadzają.';
                break;
            case 'wrong-sex':
                $alertMessage = 'Nie wybrano płci.';
                break;
            default:
                $alertMessage = 'Wystąpił nieznany błąd.';
                break;
        }
    }
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const changeButtons = document.querySelectorAll('button[name="change"]');
            const cancelButtons = document.querySelectorAll('button#cancel');

            changeButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const settingDiv = btn.closest('.setting');
                    // Dezaktywuj wszystkie inne formularze
                    document.querySelectorAll('.setting.active').forEach(el => el.classList.remove('active'));
                    // Aktywuj aktualny
                    settingDiv.classList.add('active');
                });
            });

            cancelButtons.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault(); // żeby nie wysłało formularza
                    const settingDiv = btn.closest('.setting');
                    settingDiv.classList.remove('active');
                });
            });
        });
    </script>

</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content">
            <h1 class="w-300 f-upper ls-2">Ustawienia</h1>
            <div class="settings">
                <h3 class="w-300 f-upper ls-2">Informacje o koncie</h3>
                <div class="setting">
                    <p>Twój nick</p>
                    <span><?php echo $_SESSION['username'] ?></span>
                </div>
                <div class="setting">
                    <p>Adres e-mail</p>
                    <button name="change">Zmień</button>
                    <form action="src/apis/update_player.php?type=email" method="post">
                        <input type="email" name="obecnyEmail" id="obecnyEmail" placeholder="Obecny adres e-mail" required>
                        <input type="email" name="nowyEmail" id="nowyEmail" placeholder="Nowy adres e-mail" required>
                        <input type="password" name="password" id="password-email" placeholder="Potwierdź hasło" required>
                        <div class="actions">
                            <input type="submit" value="Potwierdź">
                            <button id="cancel">Anuluj</button>
                        </div>
                    </form>
                </div>
                <div class="setting">
                    <p>Zmiana hasła</p>
                    <button name="change">Zmień</button>
                    <form action="src/apis/update_player.php?type=password" method="post">
                        <input type="password" name="obecneHaslo" id="obecneHaslo" placeholder="Obecne hasło" required>
                        <input type="password" name="noweHaslo" id="noweHaslo" placeholder="Nowe hasło" required>
                        <input type="password" name="confirmpassword" id="confirmpassword" placeholder="Potwierdź nowe hasło" required>
                        <div class="actions">
                            <input type="submit" value="Potwierdź">
                            <button id="cancel">Anuluj</button>
                        </div>
                    </form>
                </div>
                <div class="setting">
                    <p <?php echo (empty($_SESSION['steam_id']) || $_SESSION['steam_id'] == NULL)?'style=color:red;':'' ?>>Połączenie konta z kontem Steam</p>
                    <?php 
                        if (empty($_SESSION['steam_id']) || $_SESSION['steam_id'] == NULL) {
                            echo '<button onclick="location.href=`src/auth/connect_steam.php`">Połącz</button>';
                        } else {
                            echo '<button disabled>Połączono</button>';
                        }
                    ?>
                </div>
                <h3 class="w-300 f-upper ls-2">Dane osobowe</h3>
                <div class="setting">
                    <p>Imię i nazwisko 
                        [<?php 
                            if ($imie) {
                                echo $imie;
                            } else {
                                echo 'Nie ustawiono';
                            }
                        ?>]
                        </p>
                    <button name="change">Zmień</button>
                    <form action="src/apis/update_player.php?type=imie" method="post">
                        <input type="text" name="imie" id="imie" placeholder="Imię i nazwisko" required>
                        <input type="password" name="password" id="password-imie" placeholder="Potwierdź hasło" required>
                        <div class="actions">
                            <input type="submit" value="Potwierdź">
                            <button id="cancel">Anuluj</button>
                        </div>
                    </form>
                </div>
                <div class="setting">
                    <p>Płeć [<?php 
                            if ($plec) {
                                if ($plec == "m") {
                                    echo 'Mężczyzna';
                                } else {
                                    echo 'Kobieta';
                                }
                            } else {
                                echo 'Nie ustawiono';
                            }
                        ?>]
                    </p>
                    <button name="change">Zmień</button>
                    <form action="src/apis/update_player.php?type=plec" method="post">
                        <select name="plec" id="plec">
                            <option value="">Wybierz płeć</option>
                            <option value="m">Mężczyzna</option>
                            <option value="k">Kobieta</option>
                        </select>
                        <input type="password" name="password" id="password-plec" placeholder="Potwierdź hasło" required>
                        <div class="actions">
                            <input type="submit" value="Potwierdź">
                            <button id="cancel">Anuluj</button>
                        </div>
                    </form>
                </div>
                <div class="setting">
                    <p>Klasa [<?php 
                            if ($klasa) {
                                echo $klasa;
                            } else {
                                echo 'Nie ustawiono';
                            }
                        ?>]
                        </p>
                    <button name="change">Zmień</button>
                    <form action="src/apis/update_player.php?type=klasa" method="post">
                        <input type="text" name="klasa" id="klasa" placeholder="Klasa np. 4TI, 2LO" maxlength="3" required>
                        <input type="password" name="password" id="password-klasa" placeholder="Potwierdź hasło" required>
                        <div class="actions">
                            <input type="submit" value="Potwierdź">
                            <button id="cancel">Anuluj</button>
                        </div>
                    </form>
                </div>
                <h3 class="w-300 f-upper ls-2">Informacje o turnieju</h3>
                <div class="setting">
                    <a href="policy.html">Polityka prywatności</a>
                </div>
                <div class="setting">
                    <a href="regulamin.html">Regulamin turnieju</a>
                </div>
                <h3 class="w-300 f-upper ls-2">Inne</h3>
                <div class="setting">
                    <a href="src/auth/logout.php">Wyloguj się</a>
                </div>
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
    <?php if ($alertMessage): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                alert("<?php echo addslashes($alertMessage); ?>");
            });
        </script>
    <?php endif; ?>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>

