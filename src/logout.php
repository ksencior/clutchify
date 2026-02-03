<?php
session_start();
session_unset(); // Usunięcie wszystkich zmiennych sesji
session_destroy(); // Zniszczenie sesji
header('Location: ../index.php'); // Przekierowanie do strony logowania
exit;
?>