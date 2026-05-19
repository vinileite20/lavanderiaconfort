<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (!isset($_GET['id'])) { exit; }
$id = $_GET['id'];

// Carregar o Pedido
$sql = "SELECT p.*, c.nome, c.telefone FROM pedidos p JOIN clientes c ON p.id_cliente = c.id WHERE p.id = '$id'";
$pedido = $conexao->query($sql)->fetch_assoc();

// Carregar os Itens
$itens = $conexao->query("SELECT * FROM itens_pedido WHERE id_pedido = '$id'");

// Calcular Subtotal antes do desconto
$subtotal_geral = $pedido['valor'] + $pedido['desconto'];

// Cores para o Estado
$cor_estado = ($pedido['status'] == 'Entregue' || $pedido['metodo_pagamento'] != 'Pendente') ? '#dcfce7' : '#fef08a';
$texto_cor = ($pedido['status'] == 'Entregue' || $pedido['metodo_pagamento'] != 'Pendente') ? '#16a34a' : '#ca8a04';
$texto_estado = ($pedido['metodo_pagamento'] != 'Pendente') ? 'Pago' : 'Pendente';
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: Arial, sans-serif; }
        .cartao-detalhe { background: #fff; width: 450px; border-radius: 8px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative; }
        .fechar { position: absolute; top: 20px; right: 20px; font-size: 24px; color: #999; text-decoration: none; font-weight: bold; }
        .cabecalho-detalhe { border-bottom: 1px solid #e1e1e1; padding-bottom: 15px; margin-bottom: 20px; }
        .cabecalho-detalhe h2 { display: inline-block; margin: 0; font-size: 22px; color: #333; }
        .badge-pago { background: <?php echo $cor_estado; ?>; color: <?php echo $texto_cor; ?>; padding: 5px 12px; border-radius: 4px; font-size: 14px; font-weight: bold; margin-left: 10px; vertical-align: middle; }
        .info-cliente { color: #555; font-size: 14px; line-height: 1.8; margin-bottom: 25px; }
        
        .tabela-itens { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .tabela-itens th { background: #64748b; color: white; padding: 10px; text-align: left; font-size: 13px; }
        .tabela-itens td { padding: 10px; border-bottom: 1px solid #e1e1e1; font-size: 14px; color: #444; }
        
        .resumo-financeiro { background: #f8fafc; padding: 15px; border-radius: 5px; border: 1px solid #e1e1e1; }
        .linha-fin { display: flex; justify-content: space-between; margin-bottom: 8px; color: #555; }
        .linha-fin.total { font-weight: bold; font-size: 18px; color: #111; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="cartao-detalhe">
        <a href="painel.php" class="fechar">&times;</a>
        
        <div class="cabecalho-detalhe">
            <h2>Pedido #<?php echo str_pad($pedido['id'], 4, "0", STR_PAD_LEFT); ?></h2>
            <span class="badge-pago"><?php echo $texto_estado; ?></span>
        </div>

        <div class="info-cliente">
            <strong>Cliente:</strong> <?php echo $pedido['nome']; ?><br>
            <strong>Contacto:</strong> <?php echo $pedido['telefone']; ?><br>
            <strong>Método de Pagamento:</strong> <?php echo $pedido['metodo_pagamento']; ?><br>
            <strong>Criado em:</strong> <?php echo date("d/m/Y, H:i", strtotime($pedido['data_criacao'])); ?><br>
            <?php if(!empty($pedido['obs'])) { echo "<strong>OBS:</strong> " . $pedido['obs']; } ?>
        </div>

        <table class="tabela-itens">
            <thead>
                <tr><th>Serviço</th><th>Qtd</th><th>Valor Un.</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php
                if ($itens->num_rows > 0) {
                    while($item = $itens->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $item['servico'] . "</td>";
                        echo "<td style='text-align: center;'>" . $item['quantidade'] . "</td>";
                        echo "<td>R$ " . number_format($item['valor_unitario'], 2, ',', '.') . "</td>";
                        echo "<td>R$ " . number_format($item['subtotal'], 2, ',', '.') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>Serviço único (Modo antigo)</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="resumo-financeiro">
            <div class="linha-fin"><span>Subtotal:</span> <span>R$ <?php echo number_format($subtotal_geral, 2, ',', '.'); ?></span></div>
            <div class="linha-fin"><span>Desconto:</span> <span style="color: #ef4444;">- R$ <?php echo number_format($pedido['desconto'], 2, ',', '.'); ?></span></div>
            <div class="linha-fin total"><span>Total Final:</span> <span>R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></span></div>
        </div>
    </div>

</body>
</html>