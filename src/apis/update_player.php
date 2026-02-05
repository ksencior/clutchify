<?php
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
    exit();
}

$userId = $_SESSION['id'];
$type = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('login.php');
    exit();
}

// Pobierz aktualne hasło z bazy
$stmt = $pdo->prepare("SELECT password_hash, email FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Błąd użytkownika.");
}

function verifyPassword($inputPassword, $hashFromDb) {
    return password_verify($inputPassword, $hashFromDb);
}

switch ($type) {
    case 'email':
        $current = trim($_POST['obecnyEmail']);
        $new = trim($_POST['nowyEmail']);
        $password = $_POST['password'];

        if ($_SESSION['email'] != $user['email']) {
            redirect_to('login.php');
            exit;
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            redirect_to('login.php');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET email = :email, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':email' => $new, ':id' => $userId]);
        break;

    case 'password':
        $current = $_POST['obecneHaslo'];
        $new = $_POST['noweHaslo'];
        $confirm = $_POST['confirmpassword'];

        if ($new !== $confirm) {
            redirect_to('login.php');
            exit;
        }

        if (!verifyPassword($current, $user['password_hash'])) {
            redirect_to('login.php');
            exit;
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':hash' => $newHash, ':id' => $userId]);
        break;

    case 'imie':
        $name = trim($_POST['imie']);
        $password = $_POST['password'];

        if (!verifyPassword($password, $user['password_hash'])) {
            redirect_to('login.php');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET imie = :imie, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':imie' => $name, ':id' => $userId]);
        break;

    case 'plec':
        $plec = $_POST['plec'];
        $password = $_POST['password'];

        if (!in_array($plec, ['m', 'k'])) {
            redirect_to('login.php');
            exit;
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            redirect_to('login.php');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET plec = :plec, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':plec' => $plec, ':id' => $userId]);
        break;

    case 'klasa':
        $klasa = strtoupper(trim($_POST['klasa']));
        $password = $_POST['password'];

        if (!verifyPassword($password, $user['password_hash'])) {
            redirect_to('login.php');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET klasa = :klasa, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':klasa' => $klasa, ':id' => $userId]);
        break;

    default:
        die("Nieznany typ aktualizacji.");
}

// Po zakończeniu – przekieruj użytkownika z powrotem
redirect_to('login.php');
exit();
?>








