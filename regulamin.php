<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regulamin | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
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
            <h1>Regulamin Turnieju CS2 – Zespół Szkół Niepublicznych w Gąsawie</h1>
            <p class="aktualizacja">Poniższy regulamin określa zasady udziału w szkolnym turnieju Counter-Strike 2 organizowanym przez Zespół Szkół Niepublicznych w Gąsawie..</p>
            <div class="label">
                <h3>1. Uczestnicy</h3>
                <ul>
                    <li>W turnieju mogą brać udział wyłącznie uczniowie Zespołu Szkół Niepublicznych w Gąsawie.</li>
                    <li>Drużyny mogą być mieszane, niezależnie od klasy oraz wieku uczestników.</li>
                </ul>
            </div>

            <div class="label">
                <h3>2. Format turnieju</h3>
                <ul>
                    <li>Turniej odbywa się w formule 5 vs 5.</li>
                    <li>Mecze rozgrywane są w systemie BO3 (best of 3) w formie drabinki.</li>
                    <li>Liczba drużyn jest nieograniczona.</li>
                </ul>
            </div>

            <div class="label">
                <h3>3. Składy drużyn</h3>
                <ul>
                    <li>Drużyna musi składać się z 5 zawodników.</li>
                    <li>Dopuszczalny jest jeden zawodnik rezerwowy (opcjonalnie).</li>
                    <li>Składy drużyn są ostateczne po zamknięciu zapisów — późniejsze zmiany nie są możliwe.</li>
                </ul>
            </div>

            <div class="label">
                <h3>4. Zasady meczowe</h3>
                <ul>
                    <li>Map pool jest zgodny z aktualnym trybem Premier w CS2.</li>
                    <li>Obowiązuje całkowity zakaz używania cheatów, exploitów oraz bugów gry.</li>
                    <li>Mecze rozgrywane są na serwerach hostowanych przez NestHost.pl dzięki uprzejmości Bartosza Szymaniaka.</li>
                    <li>Po zakończeniu meczu BO3 możliwe jest pobranie demek oraz sprawdzenie wyników.</li>
                </ul>
            </div>

            <div class="label">
                <h3>5. Zachowanie uczestników</h3>
                <ul>
                    <li>Wymagana jest pełna kultura osobista na czacie głosowym i tekstowym.</li>
                    <li>Zakazane jest wyzywanie, trollowanie oraz inne zachowania utrudniające grę.</li>
                    <li>Organizatorzy mogą nałożyć kary za nieodpowiednie zachowanie.</li>
                </ul>
            </div>

            <div class="label">
                <h3>6. Przygotowanie do meczu</h3>
                <ul>
                    <li>Drużyna musi zgłosić gotowość do meczu najpóźniej na 15 minut przed upływem wyznaczonego czasu rozpoczęcia spotkania. Po przekroczeniu wyznaczonego czasu mecz zostaje rozstrzygnięty walkowerem.</li>
                    <li>Brak gotowości lub nieobecność jest równoznaczna z przegraniem meczu walkowerem.</li>
                    <li>Mecze są rozgrywane z domu; finały mogą zostać rozegrane w formie LAN w szkole.</li>
                </ul>
            </div>

            <div class="label">
                <h3>7. Kary i sankcje</h3>
                <ul>
                    <li>Naruszenie regulaminu może skutkować karą, w tym dyskwalifikacją drużyny.</li>
                    <li>Organizatorzy podejmują ostateczne decyzje dotyczące sporów, problemów technicznych oraz kar.</li>
                </ul>
            </div>

            <div class="label">
                <h3>8. Nagrody</h3>
                <ul>
                    <li>Informacje o nagrodach zostaną ujawnione w późniejszym terminie.</li>
                    <li>W poprzednich edycjach nagrodami były: karty podarunkowe (100 zł i 50 zł) oraz bilety zwalniające z odpowiedzi dla zwycięzców podium.</li>
                </ul>
            </div>

            <div class="label">
                <h3>9. Postanowienia końcowe</h3>
                <ul>
                    <li>Organizatorzy zastrzegają sobie prawo do zmiany regulaminu w trakcie trwania turnieju.</li>
                    <li>Udział w turnieju oznacza akceptację niniejszego regulaminu oraz polityki prywatności.</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>






