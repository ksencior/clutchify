<?php 
echo '
<div class="sidebar-left">
    <img src="img/clutchify-w-o-text.png" alt="" class="logo" onclick="window.location.href=`index.php`">
    <div class="menu">
        <a href="play.php">
            <i class="fa-solid fa-gamepad"></i><span>Graj</span>
        </a>
        <a href="ranking.php">
            <i class="fa-solid fa-chart-simple"></i><span>Ranking</span>
        </a>
        <div class="line"></div>
        <a href="';
echo (!empty($_SESSION["team_id"]) && ($_SESSION['team_id']!=NULL))?'team.php?id='.$_SESSION['team_id']:'team_create.php';
echo '">
            <i class="fa-solid fa-users"></i><span>Drużyna</span>
        </a>
        <a href="mecze.php">
            <i class="fa-solid fa-table"></i><span>Mecze</span>
        </a>
        <a href="drabina.php">
            <i class="fa-solid fa-medal"></i><span>Drabina</span>
        </a>
    </div>
</div>
<div class="mobile-topbar">
    <div class="mobile-menu-btn">
        <i class="fa-solid fa-bars" style="font-size: 150%;" id="mobile-menu-btn"></i>
    </div>
    <div class="mobile-logo" onclick="window.location.href=`index.php`">
        <img src="img/logo.png" alt="" class="logo">
    </div>
</div>
<div class="mobile-navbar">
    <div class="menu">
        <a href="play.php">
            <i class="fa-solid fa-gamepad"></i><span>Graj</span>
        </a>
        <a href="ranking.php">
            <i class="fa-solid fa-chart-simple"></i><span>Ranking</span>
        </a>
        <div class="line"></div>
        <a href="';
echo (!empty($_SESSION["team_id"]) && ($_SESSION['team_id']!=NULL))?'team.php?id='.$_SESSION['team_id']:'team_create.php';
echo '">
            <i class="fa-solid fa-users"></i><span>Drużyna</span>
        </a>
        <a href="mecze.php">
            <i class="fa-solid fa-table"></i><span>Mecze</span>
        </a>
        <a href="drabina.php">
            <i class="fa-solid fa-medal"></i><span>Drabina</span>
        </a>
        <div class="line"></div>
        <a href="settings.php">
            <i class="fa-solid fa-gear"></i><span>Ustawienia</span>
        </a>
        <a href="profile.php">
            <i class="fa-solid fa-circle-user"></i><span>Profil</span>
        </a>
        ';
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
            echo '
            <div class="line"></div>
            <a href="admin.php">
                <i class="fa-solid fa-shield-halved"></i><span>Admin</span>
            </a>
            ';
        }
echo '
    </div>
    
</div>
';

?>