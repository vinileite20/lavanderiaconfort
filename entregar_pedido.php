<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$id_pedido = $_POST['id_pedido'];
$recebedor = $_POST['recebedor'];

// Verifica se a forma de pagamento foi selecionada no momento da entrega
if (isset($_POST['metodo_pagamento']) && !empty($_POST['metodo_pagamento'])) {
    $metodo_pagamento = $_POST['metodo_pagamento'];
    
    // Atualiza o status para entregue, registra quem buscou e troca 'Pendente' pelo método real escolhido
    $sql = "UPDATE pedidos SET status = 'Entregue', recebedor = '$recebedor', metodo_pagamento = '$metodo_pagamento' WHERE id = '$id_pedido'";
} else {
    // Se já estava pago desde o início, mantém a forma de pagamento antiga e só altera a entrega
    $sql = "UPDATE pedidos SET status = 'Entregue', recebedor = '$recebedor' WHERE id = '$id_pedido'";
}

if ($conexao->query($sql) === TRUE) {
    header("Location: painel.php");
} else {
    echo "Erro ao registrar a entrega do pedido: " . $conexao->error;
}
exit;
?>