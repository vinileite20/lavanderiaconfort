<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Apaga o funcionário do banco de dados
    $conexao->query("DELETE FROM funcionarios WHERE id = '$id'");
}

// Volta para a tela de funcionários
header("Location: funcionarios.php");
exit;
?>