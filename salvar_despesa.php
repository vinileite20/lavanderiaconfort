<?php
session_start();

// Segurança: verifica se está logado
if (!isset($_SESSION['usuario_logado'])) { 
    header("Location: index.html"); 
    exit; 
}

require_once 'conexao.php';

// Recebe todos os dados enviados pela janela flutuante do financeiro
$descricao = $_POST['descricao'];
$categoria = $_POST['categoria'];
$data_despesa = $_POST['data_despesa'];
$usuario = $_POST['usuario']; // Pega o nome do funcionário/dono escondido no formulário

// TRATAMENTO DO VALOR (Mágica da Moeda)
// O formulário envia algo como "1.500,00" ou "50,00". O banco de dados só entende "1500.00" ou "50.00".
$valor_recebido = $_POST['valor'];
$valor_limpo = str_replace('.', '', $valor_recebido); // Remove pontos de milhar (se houver)
$valor_banco = str_replace(',', '.', $valor_limpo);   // Troca a vírgula dos centavos por ponto

// TRATAMENTO DE TEXTO (Segurança contra quebra de banco de dados)
$descricao = $conexao->real_escape_string($descricao);
$categoria = $conexao->real_escape_string($categoria);
$usuario = $conexao->real_escape_string($usuario);

// Prepara a ordem de inserção na tabela de despesas
$sql = "INSERT INTO despesas (descricao, categoria, valor, data_despesa, usuario) 
        VALUES ('$descricao', '$categoria', '$valor_banco', '$data_despesa', '$usuario')";

// Executa a ação
if ($conexao->query($sql) === TRUE) {
    // Se deu certo, manda a dona de volta para o financeiro.
    // O pulo do gato: já manda ela direto para o filtro mostrando a data em que ela acabou de lançar o gasto!
    header("Location: financeiro.php?data_inicio=" . $data_despesa . "&data_fim=" . $data_despesa);
    exit;
} else {
    // Se der erro (como faltar a coluna 'usuario' no banco), ele mostra na tela para você saber arrumar
    echo "Erro ao registrar a despesa: " . $conexao->error;
    echo "<br><br><a href='financeiro.php'>Voltar para o Financeiro</a>";
}
?>