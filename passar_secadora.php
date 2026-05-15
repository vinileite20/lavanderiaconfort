<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Atualiza o status para Secando
    $conexao->query("UPDATE pedidos SET status = 'Secando' WHERE id = '$id'");
}

// Volta para a Fila de Produção!
header("Location: fila.php");
exit;
?>