<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (isset($_POST['id_pedido']) && isset($_POST['recebedor'])) {
    $id = $_POST['id_pedido'];
    $recebedor = $_POST['recebedor'];
    
    // Atualiza para Entregue e guarda quem retirou a roupa
    $conexao->query("UPDATE pedidos SET status = 'Entregue', recebedor = '$recebedor' WHERE id = '$id'");
}

// Devolve o usuário para o painel na mesma hora
header("Location: painel.php");
exit;
?>