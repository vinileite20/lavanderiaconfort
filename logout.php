<?php
session_start();

// Destrói todas as sessões ativas (Rasga a pulseira)
session_destroy();

// Manda o usuário de volta para a tela de login
header("Location: index.html");
exit;
?>