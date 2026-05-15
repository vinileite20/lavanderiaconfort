<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$descricao = $_POST['descricao'];
$categoria = $_POST['categoria'];
$data = $_POST['data_despesa'];

// Transforma o 150,00 da tela em 150.00 para o banco de dados
$valor = str_replace(',', '.', $_POST['valor']);

$sql = "INSERT INTO despesas (descricao, categoria, valor, data_despesa) VALUES ('$descricao', '$categoria', '$valor', '$data')";

if ($conexao->query($sql) === TRUE) {
    header("Location: financeiro.php");
    exit;
} else {
    echo "Erro ao salvar despesa: " . $conexao->error;
}
?>