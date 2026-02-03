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

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'], $_POST['content'])) {
    $tytul = $_POST['title'];
    $content = $_POST['content'];

    try {
        $sql = "INSERT INTO posts (`title`, `content`) VALUES (:tytul, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':tytul' => $tytul, ':content' => $content]);
        header('Location: ../index.php');
        exit();
    } catch (PDOException $e) {
        echo "Wystąpił błąd: " . $e->getMessage();
    }
} else {header('Location: ../add_post.php');}
?>