<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// Segurança Dupla: Verifica se a pessoa tem permissão para apagar
$ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
$podeEditar = ($_SESSION['pode_editar'] == 'Sim' || $ehAdmin);

if (!$podeEditar) {
    // Se um funcionário sem permissão tentar aceder a esta página à força, é bloqueado
    header("Location: painel.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 1. Primeiro apaga os serviços ligados a este pedido
    $conexao->query("DELETE FROM itens_pedido WHERE id_pedido = '$id'");
    
    // 2. Depois apaga o pedido principal
    $conexao->query("DELETE FROM pedidos WHERE id = '$id'");
}

// Volta rapidamente para o painel
header("Location: painel.php");
exit;
?>