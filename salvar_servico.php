<?php
session_start();
if ($_SESSION['cargo_usuario'] != 'Administrador') {
    // Se não for Administrador, joga para fora imediatamente
    header("Location: servicos.php");
    exit;
}
require_once 'conexao.php';

$nome = $_POST['nome_servico'];
$preco = $_POST['preco'];

// A MÁGICA DA SUA REGRA DE NEGÓCIO AQUI:
// Se a caixinha "pode_alterar" chegou marcada do formulário, o preço fixo é FALSE (0).
// Se ela não chegou, significa que ficou desmarcada, então o preço fixo é TRUE (1).
if (isset($_POST['pode_alterar'])) {
    $precoFixo = 0; 
} else {
    $precoFixo = 1;
}

$sql = "INSERT INTO servicos (nome_servico, preco, preco_fixo) VALUES ('$nome', '$preco', '$precoFixo')";

if ($conexao->query($sql) === TRUE) {
    header("Location: servicos.php");
    exit;
} else {
    echo "Erro ao salvar serviço: " . $conexao->error;
}
?>