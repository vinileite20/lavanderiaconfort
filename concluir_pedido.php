<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. Pegar dados do cliente e do pedido antes de finalizar
    $sql = "SELECT p.id, c.nome, c.telefone, p.descricao 
            FROM pedidos p 
            JOIN clientes c ON p.id_cliente = c.id 
            WHERE p.id = '$id'";
    $pedido = $conexao->query($sql)->fetch_assoc();

    if ($pedido) {
        $nomeCliente = $pedido['nome'];
        $telefone = preg_replace('/\D/', '', $pedido['telefone']); // Tira parênteses e espaços
        $servico = $pedido['descricao'];

        // 2. Atualizar status para 'Lavado' (Finalizado)
        $conexao->query("UPDATE pedidos SET status = 'Lavado' WHERE id = '$id'");

        // 3. Montar a mensagem do WhatsApp
        $mensagem = "Olá, " . $nomeCliente . "! Suas roupas (" . $servico . ") já estão prontas aqui na Lavanderia. Pode vir buscar! 🧼✨";
        $urlZap = "https://api.whatsapp.com/send?phone=55" . $telefone . "&text=" . urlencode($mensagem);

        // 4. Redirecionar para o WhatsApp
        header("Location: " . $urlZap);
        exit;
    }
}

header("Location: fila.php");
exit;
?>