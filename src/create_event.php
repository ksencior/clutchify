<?php
session_start();
include_once 'connect_db.php';
if (!isset($_SESSION['logged']) || $_SESSION['logged'] != true) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    header('Location: index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nazwa'], $_POST['typ'])) {
    $nazwa = $_POST['nazwa'];
    $typ = $_POST['typ'];
    $koniec = $_POST['koniec'];

    try {
        $sql = "INSERT INTO events (`nazwa`, `type`, `started_at`, `ending_at`) VALUES (:nazwa, :type, NOW(), :koniec)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nazwa' => $nazwa, ':type' => $typ, ':koniec' => $koniec]);
        header('Location: ../admin.php');
        exit();
    } catch (PDOException $e) {
        echo "Wystąpił błąd: " . $e->getMessage();
    }
} else {header('Location: ../add_event.php');}
?>