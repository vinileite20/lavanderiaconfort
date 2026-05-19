<?php
session_start();
if ($_SESSION['cargo_usuario'] != 'Administrador') {
    // Se não for Administrador, joga para fora imediatamente
    header("Location: servicos.php");
    exit;
}
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