<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$id = $_POST['id'];
$nome = $_POST['nome'];
$senha = $_POST['senha'];
$cargo = $_POST['cargo'];
$pode_editar = $_POST['pode_editar'];

if (!empty($id)) {
    // É uma EDIÇÃO
    $sql = "UPDATE funcionarios SET nome='$nome', senha='$senha', cargo='$cargo', pode_editar_pedidos='$pode_editar' WHERE id='$id'";
} else {
    // É um NOVO CADASTRO
    $sql = "INSERT INTO funcionarios (nome, senha, cargo, pode_editar_pedidos) VALUES ('$nome', '$senha', '$cargo', '$pode_editar')";
}

$conexao->query($sql);
header("Location: funcionarios.php");
exit;
?>