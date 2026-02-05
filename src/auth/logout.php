<?php
session_start();
session_unset(); // Usunięcie wszystkich zmiennych sesji
session_destroy(); // Zniszczenie sesji
header('Location: /clutchify/index.php'); // Przekierowanie do strony logowania
exit;
?>

