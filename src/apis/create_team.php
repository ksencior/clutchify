<?php
require_once __DIR__ . '/../core/connect_db.php';
if (!isset($_SESSION['logged']) || $_SESSION['logged'] != true) {
    redirect_to('login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nazwa'], $_POST['skrot'])) {
    $nazwa = $_POST['nazwa'];
    $skrot = $_POST['skrot'];
    $leader = $_SESSION['id'];

    if (trim($nazwa) == '' && trim($skrot) == '') {
        redirect_to('login.php');
        exit();
    }

    if (strlen($nazwa) < 3 || strlen($nazwa) > 20) {
        redirect_to('login.php');
        exit();
    }

    if (strlen($skrot) < 1 || strlen($skrot) > 4) {
        redirect_to('login.php');
        exit();
    }

    try {
        $sql = "INSERT INTO teams (`nazwa`, `skrot`, `leader_id`) VALUES (:nazwa, :skrot, :leader)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nazwa' => $nazwa, ':skrot' => $skrot, ':leader' => $leader]);

        $newTeamId = $pdo->lastInsertId();
        $sql = "UPDATE users SET team_id = :teamId WHERE id= :lid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':teamId' => $newTeamId, ':lid' => $leader]);
        $_SESSION['team_id'] = $newTeamId;
        $_SESSION['team_name'] = $nazwa;
        $_SESSION['team_skrot'] = $skrot;
        redirect_to('team.php?id='.$newTeamId);
        exit();
    } catch (PDOException $e) {
        echo "Wystąpił błąd: " . $e->getMessage();
    }
}
?>







