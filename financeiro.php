<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// SEGURANÇA: Só Administrador entra aqui!
if ($_SESSION['cargo_usuario'] != 'Administrador') { header("Location: painel.php"); exit; }

$hoje = date('Y-m-d');

// 1. CALCULA ENTRADAS DE HOJE
$sql_entradas = "SELECT metodo_pagamento, SUM(valor) as total FROM pedidos WHERE DATE(data_criacao) = '$hoje' AND metodo_pagamento != 'Pendente' GROUP BY metodo_pagamento";
$res_entradas = $conexao->query($sql_entradas);
$entradas_hoje = 0; $dinheiro = 0; $pix = 0; $cartao = 0;

if ($res_entradas) {
    while($row = $res_entradas->fetch_assoc()) {
        $entradas_hoje += $row['total'];
        if ($row['metodo_pagamento'] == 'Dinheiro') $dinheiro += $row['total'];
        if ($row['metodo_pagamento'] == 'Pix') $pix += $row['total'];
        if ($row['metodo_pagamento'] == 'Cartão') $cartao += $row['total'];
    }
}

// 2. CALCULA DESPESAS DE HOJE
$sql_saidas = "SELECT SUM(valor) as total FROM despesas WHERE data_despesa = '$hoje'";
$saidas_hoje = $conexao->query($sql_saidas)->fetch_assoc()['total'] ?? 0;

// 3. SALDO GERAL DO DIA
$saldo_dia = $entradas_hoje - $saidas_hoje;

// CÁLCULOS DO MÊS
$mes_atual = date('m'); $ano_atual = date('Y');
$entradas_mes = $conexao->query("SELECT SUM(valor) as total FROM pedidos WHERE MONTH(data_criacao) = '$mes_atual' AND YEAR(data_criacao) = '$ano_atual' AND metodo_pagamento != 'Pendente'")->fetch_assoc()['total'] ?? 0;
$saidas_mes = $conexao->query("SELECT SUM(valor) as total FROM despesas WHERE MONTH(data_despesa) = '$mes_atual' AND YEAR(data_despesa) = '$ano_atual'")->fetch_assoc()['total'] ?? 0;
$lucro_mes = $entradas_mes - $saidas_mes;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .painel-caixa { background: #fff; padding: 25px; border-radius: 8px; border-top: 5px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .caixa-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center; }
        .valor-caixa { font-size: 28px; font-weight: bold; margin-top: 10px; }
        
        .separador-pagamentos { display: flex; justify-content: space-between; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; }
        .item-pagamento { text-align: center; flex: 1; border-right: 1px solid #cbd5e1; }
        .item-pagamento:last-child { border-right: none; }
        .item-pagamento h4 { color: #64748b; font-size: 14px; margin-bottom: 5px; }
        .item-pagamento span { font-size: 20px; font-weight: bold; color: #334155; }
        
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); }
        .modal-caixa { background-color: #fff; margin: 5% auto; padding: 30px; border-radius: 8px; width: 400px; position: relative; border-top: 5px solid #ef4444; }
        .modal-fechar { float: right; font-size: 24px; cursor: pointer; color: #aaa; }
        .input-grupo { margin-bottom: 15px; }
        .input-grupo label { display: block; margin-bottom: 5px; font-size: 14px; }
        .input-grupo input, .input-grupo select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; outline: none; font-size: 15px; box-sizing: border-box; }
    </style>
</head>
<body style="display: block;">
    <div class="dashboard-container">
        
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>

        <?php $pagina_atual = 'financeiro.php'; $ehAdmin = true; ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
                <img src="logo.png" alt="Lavanderia" style="max-width: 140px; height: auto;">
                <button class="btn-fechar-menu" onclick="fecharMenuMobile()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <a href="painel.php" class="menu-item"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
            <a href="clientes.php" class="menu-item"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="servicos.php" class="menu-item"><i class="fa-solid fa-tag"></i> Serviços</a>
            <a href="funcionarios.php" class="menu-item"><i class="fa-solid fa-id-card"></i> Funcionários</a>
            <a href="financeiro.php" class="menu-item ativo"><i class="fa-solid fa-chart-line"></i> Financeiro</a>
            
            <div style="flex-grow: 1;"></div>
            <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <button class="btn-menu-mobile" onclick="abrirMenuMobile()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2>Fechamento de Caixa</h2>
                <div style="display: flex; gap: 10px; width: 100%;">
                    <a href="despesas.php" style="flex: 1; background: #f1f5f9; color: #475569; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold;"><i class="fa-solid fa-list"></i> Histórico</a>
                    <button onclick="abrirModal()" style="flex: 1; background: #ef4444; color: white; padding: 10px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;"><i class="fa-solid fa-minus"></i> Despesa</button>
                </div>
            </div>

            <h3 style="margin-bottom: 15px; color: #334155;">Resumo de Hoje (<?php echo date('d/m/Y'); ?>)</h3>
            <div class="painel-caixa">
                <div class="caixa-grid">
                    <div>
                        <div style="color: #64748b; font-weight: bold;"><i class="fa-solid fa-arrow-down" style="color: #10b981;"></i> Entradas Hoje</div>
                        <div class="valor-caixa" style="color: #10b981;">R$ <?php echo number_format($entradas_hoje, 2, ',', '.'); ?></div>
                    </div>
                    <div>
                        <div style="color: #64748b; font-weight: bold;"><i class="fa-solid fa-arrow-up" style="color: #ef4444;"></i> Saídas Hoje</div>
                        <div class="valor-caixa" style="color: #ef4444;">R$ <?php echo number_format($saidas_hoje, 2, ',', '.'); ?></div>
                    </div>
                    <div style="border-left: 2px dashed #e2e8f0; padding-left: 15px;">
                        <div style="color: #334155; font-weight: bold;"><i class="fa-solid fa-cash-register"></i> Saldo do Dia</div>
                        <div class="valor-caixa" style="color: <?php echo ($saldo_dia >= 0) ? '#0284c7' : '#ef4444'; ?>;">R$ <?php echo number_format($saldo_dia, 2, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="separador-pagamentos">
                <div class="item-pagamento">
                    <h4><i class="fa-solid fa-money-bill-wave" style="color: #16a34a;"></i> Dinheiro (Gaveta)</h4>
                    <span>R$ <?php echo number_format($dinheiro, 2, ',', '.'); ?></span>
                </div>
                <div class="item-pagamento">
                    <h4><i class="fa-brands fa-pix" style="color: #0ea5e9;"></i> Pix (Conta)</h4>
                    <span>R$ <?php echo number_format($pix, 2, ',', '.'); ?></span>
                </div>
                <div class="item-pagamento">
                    <h4><i class="fa-solid fa-credit-card" style="color: #f59e0b;"></i> Cartão (Maquininha)</h4>
                    <span>R$ <?php echo number_format($cartao, 2, ',', '.'); ?></span>
                </div>
            </div>

            <h3 style="margin-bottom: 15px; color: #334155; margin-top: 40px;">Balanço do Mês Atual</h3>
            <div class="cards-grid">
                <div class="summary-card">
                    <div class="card-header"><span>Faturamento Bruto</span><div class="card-icon icon-green"><i class="fa-solid fa-arrow-trend-up"></i></div></div>
                    <div class="card-value">R$ <?php echo number_format($entradas_mes, 2, ',', '.'); ?></div>
                </div>
                <div class="summary-card">
                    <div class="card-header"><span>Total de Despesas</span><div class="card-icon" style="background: #fee2e2; color: #ef4444;"><i class="fa-solid fa-arrow-trend-down"></i></div></div>
                    <div class="card-value">R$ <?php echo number_format($saidas_mes, 2, ',', '.'); ?></div>
                </div>
                <div class="summary-card">
                    <div class="card-header"><span>Lucro Líquido</span><div class="card-icon icon-blue"><i class="fa-solid fa-sack-dollar"></i></div></div>
                    <div class="card-value">R$ <?php echo number_format($lucro_mes, 2, ',', '.'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalDespesa" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            <h2 style="color: #ef4444; margin-bottom: 20px;"><i class="fa-solid fa-minus-circle"></i> Registrar Despesa</h2>
            
            <form action="salvar_despesa.php" method="POST">
                <div class="input-grupo">
                    <label>Descrição (Ex: Sabão, Conta de Luz):</label>
                    <input type="text" name="descricao" required placeholder="O que foi pago?">
                </div>
                
                <div class="input-grupo">
                    <label>Categoria:</label>
                    <select name="categoria">
                        <option value="Produtos de Limpeza">Produtos de Limpeza</option>
                        <option value="Contas (Água/Luz/Net)">Contas (Água/Luz/Net)</option>
                        <option value="Manutenção">Manutenção de Máquinas</option>
                        <option value="Funcionários">Pagamento de Funcionários</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="input-grupo">
                    <label>Valor (R$):</label>
                    <input type="text" name="valor" required id="inputValor" value="0,00" oninput="formatarMoeda(this)" style="text-align: right; font-weight: bold; color: #ef4444;">
                </div>

                <div class="input-grupo">
                    <label>Data:</label>
                    <input type="date" name="data_despesa" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <button type="submit" style="width: 100%; margin-top: 10px; background: #ef4444; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; font-size: 16px; cursor: pointer;">Salvar Despesa</button>
            </form>
        </div>
    </div>

    <script>
        // JS DA DESPESA
        function abrirModal() { document.getElementById('modalDespesa').style.display = 'block'; }
        function fecharModal() { document.getElementById('modalDespesa').style.display = 'none'; }
        
        function formatarMoeda(campo) {
            var valor = campo.value.replace(/\D/g, ''); 
            if (valor === '') { campo.value = '0,00'; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace('.', ',');
            campo.value = valor;
        }

        // JS DO MENU MOBILE
        function abrirMenuMobile() {
            document.getElementById('menuSidebar').classList.add('aberto');
            document.getElementById('fundoMenu').classList.add('ativo');
        }

        function fecharMenuMobile() {
            document.getElementById('menuSidebar').classList.remove('aberto');
            document.getElementById('fundoMenu').classList.remove('ativo');
        }
    </script>
</body>
</html>