<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$id_cliente = $_POST['id_cliente'];
$funcionario = $_POST['funcionario'];
$obs = $_POST['obs'];
$metodo_pagamento = $_POST['metodo_pagamento'];

// Tratar valores
$desconto = str_replace(',', '.', $_POST['desconto']);
$valor_total = $_POST['valor_total_escondido'];

// Arrays que vêm do ecrã de Novo Pedido
$servicos = $_POST['servicos'];
$quantidades = $_POST['quantidades'];
$precos_unitarios = $_POST['precos_unitarios'];

// Montar um texto resumo (Ex: "1x Lavagem, 2x Passadoria") para não quebrar as outras páginas
$descricao_resumo = [];
for ($i = 0; $i < count($servicos); $i++) {
    if (!empty($servicos[$i])) {
        $descricao_resumo[] = $quantidades[$i] . "x " . $servicos[$i];
    }
}
$texto_descricao = implode(" + ", $descricao_resumo);

// 1. Guardar o pedido principal
$sql_pedido = "INSERT INTO pedidos (id_cliente, descricao, valor, status, data_criacao, desconto, metodo_pagamento, funcionario, obs) 
               VALUES ('$id_cliente', '$texto_descricao', '$valor_total', 'Esperando', NOW(), '$desconto', '$metodo_pagamento', '$funcionario', '$obs')";

if ($conexao->query($sql_pedido) === TRUE) {
    $id_pedido_gerado = $conexao->insert_id; // Apanha o ID que acabou de ser criado

    // 2. Guardar os itens separados na tabela nova
    for ($i = 0; $i < count($servicos); $i++) {
        if (!empty($servicos[$i])) {
            $nome_servico = $servicos[$i];
            $qtd = $quantidades[$i];
            $preco_un = $precos_unitarios[$i];
            $subtotal = $qtd * $preco_un;

            $sql_item = "INSERT INTO itens_pedido (id_pedido, servico, quantidade, valor_unitario, subtotal) 
                         VALUES ('$id_pedido_gerado', '$nome_servico', '$qtd', '$preco_un', '$subtotal')";
            $conexao->query($sql_item);
        }
    }

    header("Location: painel.php");
    exit;
} else {
    echo "Erro ao criar pedido: " . $conexao->error;
}
?>