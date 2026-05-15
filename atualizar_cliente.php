<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// Pegamos tudo que veio do formulário (incluindo aquele ID escondido)
$id = $_POST['id'];
$novoNome = $_POST['nome_cliente'];
$novoTelefone = $_POST['telefone_cliente'];
$novoEndereco = $_POST['endereco_cliente'];

// A ORDEM DE ATUALIZAR: "Atualize a tabela clientes, configure o nome, telefone e endereço com 
// os novos valores, MAS SOMENTE onde o ID for igual ao do cliente que estamos mexendo!"
$sql = "UPDATE clientes 
        SET nome = '$novoNome', telefone = '$novoTelefone', endereco = '$novoEndereco' 
        WHERE id = '$id'";

if ($conexao->query($sql) === TRUE) {
    // Se der certo, volta direto para a lista de clientes para ele ver a mudança na hora!
    header("Location: clientes.php");
    exit;
} else {
    echo "Erro ao atualizar: " . $conexao->error;
}
?>