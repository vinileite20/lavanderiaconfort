<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

if (!isset($_GET['id'])) { echo "Pedido não encontrado."; exit; }

$id = $_GET['id'];
$sql = "SELECT p.id, c.nome, c.telefone, p.data_criacao, p.valor, p.descricao 
        FROM pedidos p 
        JOIN clientes c ON p.id_cliente = c.id 
        WHERE p.id = '$id'";
$resultado = $conexao->query($sql);

if ($resultado->num_rows == 0) { echo "Pedido não encontrado."; exit; }
$pedido = $resultado->fetch_assoc();

$data = date("d/m/Y H:i", strtotime($pedido['data_criacao']));
$valor = number_format($pedido['valor'], 2, ',', '.');

// ============================================================================
// MUDE O TEXTO ABAIXO PARA O QUE VOCÊ QUISER (O TERMO DE RESPONSABILIDADE)
// ============================================================================
$texto_termo = "Declaro estar ciente e de acordo com todas as respostas acima. Autorizo os processos indicados e assumo total responsabilidade por possiveis ocorrencias, como manchas ou danos, decorrentes das escolhas feitas, incluindo a mistura de pecas ou nao separacao por cor.";
// ============================================================================
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo str_pad($pedido['id'], 4, "0", STR_PAD_LEFT); ?></title>
    <style>
        /* AJUSTES PARA IMPRESSORA PORTÁTIL 58mm (MPT-2) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 180px; /* Largura ideal para papel de 58mm */
            margin: 0; 
            padding: 5px;
            color: #000; 
            background: #fff; 
            font-size: 11px; /* Letra um pouco menor para caber na MPT-2 */
            line-height: 1.2;
        }
        
        .cabecalho { text-align: center; font-weight: bold; margin-bottom: 10px; font-size: 13px; text-transform: uppercase; }
        .termo { text-align: justify; margin-top: 8px; margin-bottom: 8px; font-size: 10px; }
        .linha-assinatura { margin-top: 15px; margin-bottom: 20px; text-align: center; }
        .divisor { border-bottom: 1px dashed #000; margin: 15px 0; width: 100%; }
        
        .negrito { font-weight: bold; }
        
        /* Força a quebra de página se necessário e limpa margens */
        @media print {
            @page { margin: 0; }
            .via { page-break-after: always; }
        }
    </style>
</head>
<body>

    <?php 
    // Criamos um loop para imprimir a via 2 vezes (Lavandaria e Cliente)
    for ($i = 1; $i <= 2; $i++) { 
        $identificacao = ($i == 1) ? "VIA: LAVANDERIA" : "VIA: CLIENTE";
    ?>
    
    <div class="via">
        <div class="cabecalho">Lavanderia Confort</div>
        <div style="text-align: center; font-size: 9px; margin-bottom: 10px;"><?php echo $identificacao; ?></div>
        
        <span class="negrito">ID:</span> #<?php echo str_pad($pedido['id'], 4, "0", STR_PAD_LEFT); ?><br>
        <span class="negrito">Nome:</span> <?php echo $pedido['nome']; ?><br>
        <span class="negrito">Fone:</span> <?php echo $pedido['telefone']; ?><br>
        <span class="negrito">Data:</span> <?php echo $data; ?><br>
        Entrega: ___/___/___<br>
        <br>
        Cor Separada? ( )Sim ( )Nao<br>
        Qtd Pecas: ___________<br>
        Pagou? ( )Sim ( )Nao<br>
        ( )Dinheiro ( )Pix ( )Cartao<br>
        <br>
        <span class="negrito">ITENS:</span><br>
        <?php echo nl2br($pedido['descricao']); ?><br>
        <br>
        <span class="negrito">VALOR TOTAL: R$ <?php echo $valor; ?></span><br>
        <br>
        OBS: ____________________<br>
        _________________________<br>
        
        <div class="termo">
            <?php echo $texto_termo; ?>
        </div>
        
        <div class="linha-assinatura">
            Ass: ____________________
        </div>
    </div>

    <?php if ($i == 1) echo '<div class="divisor">--- CORTE AQUI ---</div>'; ?>

    <?php } // Fim do loop das 2 vias ?>

    <script>
        window.onload = function() {
            window.print();
            // Opcional: fecha a aba após imprimir (descomente se quiser)
            // window.onafterprint = function() { window.close(); };
        };
    </script>
</body>
</html>