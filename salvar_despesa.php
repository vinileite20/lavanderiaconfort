<?php
session_start();
if (!isset($_SESSION['usuario_logado']) || $_SESSION['cargo_usuario'] != 'Administrador') { 
    header("Location: index.html"); 
    exit; 
}
require_once 'conexao.php';

$descricao = $_POST['descricao'];
$categoria = $_POST['categoria'];
$data_despesa = $_POST['data_despesa'];

// Converte a máscara "50,00" para o padrão do banco de dados "50.00"
$valor = str_replace('.', '', $_POST['valor']); // Tira os pontos se houver milhar
$valor = str_replace(',', '.', $valor);         // Troca a vírgula por ponto

$sql = "INSERT INTO despesas (descricao, categoria, valor, data_despesa) VALUES ('$descricao', '$categoria', '$valor', '$data_despesa')";
$conexao->query($sql);

// Volta pro financeiro
header("Location: financeiro.php");
exit;
?>