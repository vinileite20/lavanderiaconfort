<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (isset($_GET['id'])) {
    $id_pedido = $_GET['id'];
    
    // Atualiza o status para Lavando
    $sql_atualizar = "UPDATE pedidos SET status = 'Lavando' WHERE id = '$id_pedido'";
    $conexao->query($sql_atualizar);
}

// Volta pro painel na mesma hora (o usuário nem vê essa tela)
header("Location: painel.php");
exit;
?>