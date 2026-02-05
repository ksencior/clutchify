<?php include_once 'src/core/connect_db.php'; ?>\n<!DOCTYPE html>\n<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polityka Prywatno�ci | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <style>
        #root, body, html {
            overflow-y: auto;
            overflow-x: hidden;
            height: auto;
        }
        .policy {
            width: 60%;
            padding: 4vh 4vw;
            margin: 2vh auto;
            background-color: rgb(15, 15, 15);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .policy p.aktualizacja {
            font-style: italic;
            margin-bottom: 15px;
        }

        .policy .label {
            margin-bottom: 20px;
            background-color: rgb(18, 18, 18);
            border: 1px solid rgb(20, 20 ,20);
            padding: 10px;
            border-radius: 4px;
        }
        .policy .label h3 {
            font-size: 2em;
        }

        .policy .label p, .policy .label ul {
            font-size: 1.1em;
            line-height: 1.6em;
            color: #ddd;
        }
        .policy .label ul {
            padding-left: 20px;
        }
    </style>
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root" class="root-login">
        <div class="policy">
            <h1>Polityka prywatności - Szkolny Turniej CS2</h1>
            <p class="aktualizacja">Data ostatniej aktualizacji: 10 listopada 2025 r.</p>
            <div class="label">
                <h3>1. Wprowadzenie</h3>
                <p>Ta strona internetowa została stworzona w celu organizacji szkolnego turnieju Counter-Strike 2 (CS2). 
                    Dbamy o prywatność wszystkich uczestników i chcemy jasno wyjaśnić, jakie dane zbieramy, po co to robimy i jak je chronimy.</p>
            </div>
            <div class="label">
                <h3>2. Kto jest administratorem danych?</h3>
                <p>Administratorem danych jest organizator szkolnego turnieju CS2 - czyli nauczyciel/opiekun projektu lub osoba wyznaczona przez szkołę.
                W sprawach związanych z prywatnością można się skontaktować przez e-mail: szymonmazur@zsngasawa.pl.</p>
                <p>Organizatorzy turnieju:</p>
                <ul>
                    <li>Karol Kaczmarek - Nauczyciel</li>
                    <li>Szymon Mazur - Klasa 5 Technikum Informatyczne</li>
                    <li>Bartosz Szymaniak - Klasa 4 Technikum Informatyczne</li>
                </ul>
            </div>
            <div class="label">
                <h3>3. Jakie dane zbieramy</h3>
                <p>Podczas korzystania ze strony mogą być zbierane następujące dane:</p>
                <ul>
                    <li><b>Nazwa użytkownika / nick gracza -</b> potrzebna do utworzenia drużyny i udziału w turnieju.</li>
                    <li><b>Hasło -</b> służy wyłącznie do logowania się na konto. Jest przechowywane w <b>zaszyfrowanej formie (hashowane)</b> i nikt, nawet administrator, nie ma do niego dostępu.</li>
                    <li><b>Adres e-mail -</b> jest wykorzystywany do weryfikacji uczestnika, oraz służy w potrzebie kontaktu.</li>
                    <li><b>SteamID -</b> jest niezbędny w celu rozgrywania meczów. Plugin, którego używamy <i>(MatchZy)</i> wymaga SteamID od każdego gracza.</li>
                </ul>
                <p>Nie zbieramy żadnych dodatkowych informacji, takich jak dane osobowe, lokalizacja czy historia przeglądania.</p>
            </div>
            <div class="label">
                <h3>4. W jakim celu przetwarzamy dane</h3>
                <p>Dane są używane wyłącznie w celach organizacyjnych turnieju, czyli:</p>
                <ul>
                    <li>do tworzenia drużyn,</li>
                    <li>do losowania meczów,</li>
                    <li>do przydzielania serwerów i wyników spotkań,</li>
                    <li>do komunikacji między organizatorem a uczestnikami (jeśli to konieczne).</li>
                </ul>
            </div>
            <div class="label">
                <h3>5. Jak chronimy Twoje dane</h3>
                <ul>
                    <li>Hasła są <b>szyfrowane</b> i nie są widoczne dla nikogo.</li>
                    <li>Dane nie są udostępniane osobom trzecim.</li>
                    <li>Dostęp do systemu mają wyłącznie organizatorzy turnieju.</li>
                    <li>Po zakończeniu turnieju dane mogą zostać <b>usunięte.</b></li>
                </ul>
            </div>
            <div class="label">
                <h3>6. Pliki cookies</h3>
                <p>Strona może wykorzystywać pliki cookies w celu poprawnego działania (np. utrzymanie sesji logowania). Nie są one wykorzystywane do śledzenia użytkowników ani celów reklamowych.</p>
            </div>
            <div class="label">
                <h3>7. Twoje prawa</h3>
                <ul>
                    <p>Każdy użytkownik ma prawo do:</p>
                    <li>wglądu w swoje dane,</li>
                    <li>ich poprawienia lub usunięcia,</li>
                    <li>żądania usunięcia konta,</li>
                    <li>kontaktu z administratorem w przypadku pytań.</li>
                </ul>
            </div>
            <div class="label">
                <h3>8. Zmiany w polityce prywatności</h3>
                <p>W przypadku wprowadzenia zmian w zasadach ochrony prywatności, zaktualizowana wersja zostanie opublikowana na tej stronie.</p>
            </div>
        </div>
    </div>
</body>
</html>







