<?php
session_start();
require_once 'conexao.php';

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

// Busca o funcionário no banco de dados
$sql = "SELECT * FROM funcionarios WHERE nome = '$usuario' AND senha = '$senha' LIMIT 1";
$resultado = $conexao->query($sql);

if ($resultado->num_rows > 0) {
    $dados = $resultado->fetch_assoc();
    
    // Sucesso! Guarda os dados na sessão
    $_SESSION['usuario_logado'] = $dados['nome'];
    $_SESSION['pode_editar'] = $dados['pode_editar_pedidos'];
    $_SESSION['cargo_usuario'] = $dados['cargo'];
    
    header("Location: painel.php");
} else {
    // Erro! Nome ou senha errados
    header("Location: index.html?erro=1");
}
exit;
?>