<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// Se o ficheiro recebeu um ID válido pelo link...
if (isset($_GET['id'])) {
    $id_servico = $_GET['id'];
    
    // Elimina o serviço específico do banco de dados
    $sql = "DELETE FROM servicos WHERE id = '$id_servico'";
    $conexao->query($sql);
}

// Devolve o utilizador para a página de Serviços instantaneamente
header("Location: servicos.php");
exit;
?>