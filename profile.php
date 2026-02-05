<?php 
include_once 'src/core/connect_db.php';
$userid = 0;
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
    exit;
}

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $userid = $_GET['id'];
} else {
    $userid = $_SESSION['id'];
}

if (isset($userid) && $userid!=NULL) {
    try {
        $sql = "SELECT * FROM users WHERE id=:uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid'=>$userid]);
        $user=$stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $username = $user['username'];
            $teamId = $user['team_id'];
            $teamName = NULL;
            $steamId = $user['steam_id'];
            $avatar = $user['avatar_url'];

            $imie = $user['imie'];
            $klasa = $user['klasa'];
            $plec = $user['plec'];
            
            if ($steamId != NULL) {
                $steamLink = "https://steamcommunity.com/profiles/" . $steamId;
                $csstatsLink = "https://csstats.gg/player/" . $steamId;
            }

            if ($teamId != NULL) {
                $sqlTeam = "SELECT skrot FROM teams WHERE id=:tid";
                $stmtT = $pdo->prepare($sqlTeam);
                $stmtT->execute([':tid'=>$teamId]);
                $team=$stmtT->fetch(PDO::FETCH_ASSOC);

                if ($team) {
                    $teamName = $team['skrot'];
                }
            }
        } else {
            redirect_to('profile.php?id='.$_SESSION['id']);
            exit;
        }

    } catch (PDOException $e) {
        die($e);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js?v=<?= time() ?>"></script>
    <script src="assets/js/chat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content">
            <div class="profile-info">
                <img src="<?php echo (isset($avatar) && !empty($avatar)) ? $avatar : 'assets/img/avatar_default.png'; ?>" alt="Avatar uzytkownika" class="avatar">
                <div class="name">
                    <h1><?php echo "$username" ?></h1>
                    <p class="team-name"><?php echo ($teamName!=NULL)?$teamName:"Brak drużyny"; ?></p>
                    <?php 
                    if ($steamId != NULL) {
                        echo '
                        <div class="links">
                            <a href="'.$steamLink.'" target="_blank"><i class="fa-brands fa-steam"></i></a>
                            <a href="'.$csstatsLink.'" target="_blank"><i class="fa-solid fa-chart-simple"></i></a>
                        </div>
                        ';
                    }
                    ?>
                </div>
            </div>
            <div class="stats">
                    <h2>Statystyki CS2</h2>
                    <?php 
                        if (!empty($steamId)) {
                            $steamApiKey = Config::get('steam_api_key', '');
                            if (empty($steamApiKey)) {
                                echo '<p class="brak-danych">Brak klucza Steam API.</p>';
                            } else {
                                $steamUrl = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v2/?appid=730&key={$steamApiKey}&steamid={$steamId}";

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $steamUrl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                                $resp = curl_exec($ch);
                                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $curlErr = curl_error($ch);
                                curl_close($ch);

                                if ($resp !== false && $httpCode === 200) {
                                    $data = json_decode($resp, true);
                                    if (isset($data['playerstats']['stats']) && is_array($data['playerstats']['stats'])) {
                                        $map = [];
                                        foreach ($data['playerstats']['stats'] as $s) {
                                            if (isset($s['name'])) $map[$s['name']] = isset($s['value']) ? $s['value'] : 0;
                                        }

                                        // typowe nazwy statystyk CS:GO - dopasuj w zależności od tego, co zwraca API dla Twoich graczy
                                        if (isset($map['total_kills'])) {
                                            $staty['kills'] = (int)$map['total_kills'];
                                        } elseif (isset($map['kills'])) {
                                            $staty['kills'] = (int)$map['kills'];
                                        }

                                        if (isset($map['total_deaths'])) {
                                            $staty['deaths'] = (int)$map['total_deaths'];
                                        } elseif (isset($map['deaths'])) {
                                            $staty['deaths'] = (int)$map['deaths'];
                                        }

                                        if (isset($map['total_kills_headshot'])) {
                                            $headshots = (int)$map['total_kills_headshot'];
                                            $staty['headshot_pct'] = ($staty['kills'] > 0) ? round(($headshots / $staty['kills']) * 100, 2) : 0;
                                        }
                                        $kd = ($staty['deaths'] > 0) ? round($staty['kills'] / $staty['deaths'], 2) : $staty['kills'];
                                        echo '    <p><i class="fa-solid fa-crosshairs"></i>  '.htmlspecialchars($staty['kills']). '</p>';
                                        echo '    <p><i class="fa-solid fa-skull-crossbones"></i> '.htmlspecialchars($staty['deaths']).'</p>';
                                        echo '    <p><i class="fa-solid fa-bullseye"></i>  '.htmlspecialchars($staty['headshot_pct']).'%</p>';
                                        echo '    <p><i class="fa-solid fa-arrow-trend-up"></i>  '.$kd.'</p>';
                                    }
                                } else {
                                    // opcjonalnie: loguj $curlErr lub $resp jeśli potrzebne
                                }
                            }
                        } else {
                            echo "<div class='brak-danych'><h3>No patrz, pusto tu..</h3><br><h4>Użytkownik nie połączył konta z kontem steam.</h4></div>";
                        }
                    ?>
            </div>
            <div class="dane">
                    <h2>Dane</h2>
                    <p>Imię: <?php echo ($imie!=NULL)?$imie:"Nie ustawiono" ?></p>
                    <p>Płeć: <?php echo ($plec!=NULL)?($plec=="m"?'Mężczyzna':"Kobieta"):"Nie ustawiono" ?></p>
                    <p>Klasa: <?php echo ($klasa!=NULL)?$klasa:"Nie ustawiono" ?></p>
            </div>
            <div class="performance">
                <h2>Statystyki turniejowe</h2>
                <?php 
                    $sqlPerf = "
                        SELECT match_id, kills, deaths, assists, headshots, mvps
                        FROM finished_stats
                        WHERE user_id = :uid 
                        ORDER BY match_id DESC 
                        LIMIT 10
                    ";
                    $stmtPerf = $pdo->prepare($sqlPerf);
                    $stmtPerf->execute([':uid' => $userid]);
                    $matches = $stmtPerf->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if (count($matches) == 0) : ?>
                <div class="brak-danych">
                    <h3>No patrz, pusto tu..</h3><br>
                    <h4>Użytkownik nie rozegrał jeszcze żadnego meczu w turnieju.</h4>
                </div>
                <?php else: ?>
                <div class="charts-container">
                    <?php

                    $labels = [];
                    $stats = [
                        "kills" => [],
                        "deaths" => [],
                        "assists" => [],
                        "hs" => [],
                        "kd" => [],
                        "mvps" => []
                    ];

                    foreach ($matches as $m) {
                        $labels[] = "Mecz_".$m['match_id'];

                        $kills = (int)$m['kills'];
                        $deaths = (int)$m['deaths'];
                        $assists = (int)$m['assists'];
                        $headshots = (int)$m['headshots'];
                        $mvps = (int)$m['mvps'];

                        $kd = ($deaths > 0) ? round($kills / $deaths, 2) : $kills;
                        $hsPct = ($kills > 0) ? round(($headshots / $kills) * 100, 2) : 0;

                        $stats['kills'][] = $kills;
                        $stats['deaths'][] = $deaths;
                        $stats['assists'][] = $assists;
                        $stats['mvps'][] = $mvps;
                        $stats['kd'][] = $kd;
                        $stats['hs'][] = $hsPct;
                    }
                    ?>

                    <script>
                    const labels = <?= json_encode(array_reverse($labels)); ?>;
                    const stats = <?= json_encode($stats); ?>;
                    </script>

                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <div class="chart-block">
                        <select class="stat-select" data-chart="<?= $i ?>">
                            <option value="kills" <?= $i==1? 'selected' : '' ?>>Zabójstwa</option>
                            <option value="deaths" <?= $i==2? 'selected' : '' ?>>Śmierci</option>
                            <option value="assists" <?= $i==3? 'selected' : '' ?>>Asysty</option>
                            <option value="hs">HS%</option>
                            <option value="kd">K/D</option>
                            <option value="mvps">MVP</option>
                        </select>
                        <canvas id="chart<?= $i ?>"></canvas>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
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
    <script>
        function createChart(canvasId, statKey) {
            let filler = stats[statKey].length > 0 ? Math.max(...stats[statKey]) * 0.1 : 1;
            return new Chart(document.getElementById(canvasId).getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: statKey.toUpperCase(),
                        data: stats[statKey],
                        borderWidth: 2.5,
                        borderColor: "rgba(145, 81, 255, 1)",
                        backgroundColor: "rgba(145, 81, 255, 0.18)",
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: "#9151ff",
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    animation: {
                        duration: 800,
                        easing: "easeOutExpo",
                        delay(ctx) {
                            return ctx.dataIndex * 80; // punkty po kolei 💥💥💥
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgba(0,0,0,0.8)",
                            titleColor: "#fff",
                            bodyColor: "#fff",
                            borderWidth: 1,
                            borderColor: "rgba(145, 81, 255, 0.7)"
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: "#cfcfcf" },
                            grid: { color: "rgba(255,255,255,0.05)" }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: "#cfcfcf" },
                            grid: { color: "rgba(255,255,255,0.05)" },
                            suggestedMin: 0,
                            suggestedMax: Math.max(...stats[statKey]) + filler
                        }
                    },
                    elements: {
                        point: {
                            radius: 5,
                            hoverRadius: 7,
                            backgroundColor: "rgba(145, 81, 255, 1)", // neon fiolet
                            borderColor: "rgba(255, 255, 255, 0.9)",
                            borderWidth: 2
                        },
                        line: {
                            borderColor: "rgba(145, 81, 255, 0.9)", // laser line
                            borderWidth: 3,
                            tension: 0.25
                        }
                    }
                }
            });
        }

        let charts = {};

        document.querySelectorAll('.stat-select').forEach(select => {
            const chartId = "chart" + select.dataset.chart;
            const defaultStat = select.value;

            charts[chartId] = createChart(chartId, defaultStat);

            select.addEventListener('change', function() {
                charts[chartId].destroy();
                charts[chartId] = createChart(chartId, this.value);
            });
        });

        const chartCanvases = document.querySelectorAll('.chart-block canvas');

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target); // 📌 nie obserwujemy ponownie
                }
            });
        }, {
            threshold: 0.55
        });

        chartCanvases.forEach(canvas => observer.observe(canvas));
    </script>
</body>
</html>









