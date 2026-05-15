<?php
// Trocamos localhost por 127.0.0.1 (é a mesma coisa, mas evita erros no Windows)
$servidor = "127.0.0.1"; 
$usuario_banco = "root"; 
$senha_banco = ""; 
$nome_banco = "lavanderia"; 

// Aqui nós declaramos a porta que o XAMPP está usando agora
$porta = 3307; 

// A conexão agora envia 5 informações, incluindo a porta no final!
$conexao = new mysqli($servidor, $usuario_banco, $senha_banco, $nome_banco, $porta);

// Verifica se deu erro
if ($conexao->connect_error) {
    die("Falha ao conectar: " . $conexao->connect_error);
} 
?>