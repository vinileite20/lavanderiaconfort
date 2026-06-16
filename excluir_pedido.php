<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// CADEADO MÁXIMO: Só entra se for o dono (Administrador)
if ($_SESSION['cargo_usuario'] != 'Administrador') {
    // Se um funcionário comum tentar entrar aqui à força, é chutado de volta pro painel
    header("Location: painel.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 1. Apaga os serviços ligados ao pedido
    $conexao->query("DELETE FROM itens_pedido WHERE id_pedido = '$id'");
    
    // 2. Apaga o pedido principal
    $conexao->query("DELETE FROM pedidos WHERE id = '$id'");
}

// Volta para o painel após apagar
header("Location: painel.php");
exit;
?>