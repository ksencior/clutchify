<?php
session_start();
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: /clutchify/login.php');
    exit();
}

$userId = $_SESSION['id'];
$type = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /clutchify/settings.php");
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
            header("Location: /clutchify/settings.php?success=false&err=wrong-email");
            exit;
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            header("Location: /clutchify/settings.php?success=false&err=wrong-password");
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
            header("Location: /clutchify/settings.php?success=false&err=passwords-dont-match");
            exit;
        }

        if (!verifyPassword($current, $user['password_hash'])) {
            header("Location: /clutchify/settings.php?success=false&err=wrong-password");
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
            header("Location: /clutchify/settings.php?success=false&err=wrong-password");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET imie = :imie, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':imie' => $name, ':id' => $userId]);
        break;

    case 'plec':
        $plec = $_POST['plec'];
        $password = $_POST['password'];

        if (!in_array($plec, ['m', 'k'])) {
            header("Location: /clutchify/settings.php?success=false&err=wrong-sex");
            exit;
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            header("Location: /clutchify/settings.php?success=false&err=wrong-password");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET plec = :plec, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':plec' => $plec, ':id' => $userId]);
        break;

    case 'klasa':
        $klasa = strtoupper(trim($_POST['klasa']));
        $password = $_POST['password'];

        if (!verifyPassword($password, $user['password_hash'])) {
            header("Location: /clutchify/settings.php?success=false&err=wrong-password");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET klasa = :klasa, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':klasa' => $klasa, ':id' => $userId]);
        break;

    default:
        die("Nieznany typ aktualizacji.");
}

// Po zakończeniu – przekieruj użytkownika z powrotem
header("Location: /clutchify/settings.php?success=true");
exit();
?>


