<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// SEGURANÇA: Só Administrador entra aqui!
if ($_SESSION['cargo_usuario'] != 'Administrador') { header("Location: painel.php"); exit; }

// ======================================================
// 1. SISTEMA DE FILTRO POR PERÍODO
// ======================================================
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim    = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

$data_inicio_formatada = date('d/m/Y', strtotime($data_inicio));
$data_fim_formatada = date('d/m/Y', strtotime($data_fim));

// ======================================================
// 2. CÁLCULOS DO PERÍODO
// ======================================================
$sql_entradas = "SELECT metodo_pagamento, SUM(valor) as total FROM pedidos WHERE DATE(data_criacao) BETWEEN '$data_inicio' AND '$data_fim' AND metodo_pagamento != 'Pendente' GROUP BY metodo_pagamento";
$res_entradas = $conexao->query($sql_entradas);
$entradas_periodo = 0; $dinheiro = 0; $pix = 0; $cartao = 0;

if ($res_entradas) {
    while($row = $res_entradas->fetch_assoc()) {
        $entradas_periodo += $row['total'];
        if ($row['metodo_pagamento'] == 'Dinheiro') $dinheiro += $row['total'];
        if ($row['metodo_pagamento'] == 'Pix') $pix += $row['total'];
        if ($row['metodo_pagamento'] == 'Cartão') $cartao += $row['total'];
    }
}

$saidas_periodo = $conexao->query("SELECT SUM(valor) as total FROM despesas WHERE data_despesa BETWEEN '$data_inicio' AND '$data_fim'")->fetch_assoc()['total'] ?? 0;
$lucro_periodo = $entradas_periodo - $saidas_periodo;

// ======================================================
// 3. EXTRATO UNIFICADO (ENTRADAS + SAÍDAS)
// ======================================================
$transacoes = [];

// A. Pega as Entradas (Pedidos Pagos)
$sql_lista_entradas = "SELECT p.id, p.data_criacao AS data_registro, p.valor, c.nome AS cliente, p.metodo_pagamento 
                       FROM pedidos p JOIN clientes c ON p.id_cliente = c.id 
                       WHERE DATE(p.data_criacao) BETWEEN '$data_inicio' AND '$data_fim' AND p.metodo_pagamento != 'Pendente'";
$res_lista_ent = $conexao->query($sql_lista_entradas);
if ($res_lista_ent) {
    while($row = $res_lista_ent->fetch_assoc()) {
        $transacoes[] = [
            'tipo' => 'Entrada',
            'data' => $row['data_registro'],
            'descricao' => 'Pedido #' . str_pad($row['id'], 4, "0", STR_PAD_LEFT) . ' - ' . $row['cliente'],
            'categoria' => $row['metodo_pagamento'],
            'usuario' => 'Sistema', // O pedido em si vem do sistema
            'valor' => $row['valor']
        ];
    }
}

// B. Pega as Saídas (Despesas)
$sql_lista_saidas = "SELECT id, data_despesa AS data_registro, valor, descricao, categoria, usuario 
                     FROM despesas 
                     WHERE data_despesa BETWEEN '$data_inicio' AND '$data_fim'";
$res_lista_sai = $conexao->query($sql_lista_saidas);
if ($res_lista_sai) {
    while($row = $res_lista_sai->fetch_assoc()) {
        $transacoes[] = [
            'tipo' => 'Saída',
            // Adiciona um horário falso (23:59) se a despesa só tiver data, para alinhar com as entradas que tem data e hora
            'data' => (strlen($row['data_registro']) <= 10) ? $row['data_registro'] . ' 23:59:59' : $row['data_registro'],
            'descricao' => $row['descricao'],
            'categoria' => $row['categoria'],
            'usuario' => !empty($row['usuario']) ? $row['usuario'] : 'Administrador',
            'valor' => $row['valor']
        ];
    }
}

// C. Organiza a lista misturada da mais nova para a mais velha (Ordem Decrescente)
usort($transacoes, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro Avançado - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .barra-filtro { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-left: 5px solid #3b82f6; }
        .form-filtro { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .input-data { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; }
        .btn-buscar { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        .btn-buscar:hover { background: #2563eb; }

        .secao-titulo { font-size: 18px; color: #1e293b; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; }
        
        .painel-lucro { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 30px; }
        .grid-3-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        
        .card-fin { padding: 20px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center; }
        .card-fin h4 { margin: 0 0 10px 0; color: #64748b; font-size: 14px; text-transform: uppercase; }
        .card-fin .valor { font-size: 26px; font-weight: 900; }
        
        .valor-positivo { color: #10b981; }
        .valor-negativo { color: #ef4444; }

        .card-destaque { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
        .card-destaque.prejuizo { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
        .card-destaque h4, .card-destaque .valor { color: white !important; }

        .separador-pagamentos { display: flex; justify-content: space-between; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; }
        .item-pagamento { text-align: center; flex: 1; border-right: 1px solid #cbd5e1; }
        .item-pagamento:last-child { border-right: none; }
        .item-pagamento span { font-size: 20px; font-weight: bold; color: #334155; display: block; margin-top: 5px; }

        /* EXTRATO (TABELA) */
        .tabela-historico { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .tabela-historico th, .tabela-historico td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .tabela-historico th { background: #f8fafc; color: #475569; font-size: 14px; font-weight: bold; }
        .tabela-historico td { font-size: 14px; color: #334155; }
        .tabela-historico tr:hover { background: #f1f5f9; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-entrada { background: #d1fae5; color: #047857; }
        .badge-saida { background: #fee2e2; color: #b91c1c; }
        .badge-categoria { background: #e2e8f0; color: #475569; }

        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-caixa { background-color: #fff; margin: 5% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 450px; position: relative; border-top: 5px solid #ef4444; }
        .modal-fechar { float: right; font-size: 24px; cursor: pointer; color: #aaa; margin-top: -10px; }
        .input-grupo { margin-bottom: 15px; }
        .input-grupo label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: bold; }
        .input-grupo input, .input-grupo select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; outline: none; box-sizing: border-box; }

        @media (max-width: 768px) {
            .separador-pagamentos { flex-direction: column; gap: 15px; }
            .item-pagamento { border-right: none; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px; }
            .item-pagamento:last-child { border-bottom: none; }
            .barra-filtro, .form-filtro { flex-direction: column; align-items: stretch; width: 100%; box-sizing: border-box; }
            .tabela-wrapper { overflow-x: auto; }
        }
    </style>
</head>
<body style="display: block;">
    <div class="dashboard-container">
        
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>
        <?php $pagina_atual = 'financeiro.php'; $ehAdmin = true; ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
             <img src="marca.jpg.png" alt="Lavanderia Confort" style="max-width: 140px; height: auto;">
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
            <a href="perfil.php" class="menu-item" style="border-top: 1px solid #e2e8f0;"><i class="fa-solid fa-user-gear"></i> Meu Perfil</a>
            <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <button class="btn-menu-mobile" onclick="abrirMenuMobile()"><i class="fa-solid fa-bars"></i></button>
                <h2>Gestão Financeira</h2>
                <button onclick="abrirModal()" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; box-shadow: 0 2px 4px rgba(239,68,68,0.3);"><i class="fa-solid fa-minus-circle"></i> Registrar Gasto</button>
            </div>

            <div class="barra-filtro">
                <div>
                    <h3 style="margin: 0; font-size: 16px; color: #1e293b;"><i class="fa-solid fa-calendar-week"></i> Período Analisado</h3>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #64748b;">De <strong><?php echo $data_inicio_formatada; ?></strong> até <strong><?php echo $data_fim_formatada; ?></strong></p>
                </div>
                <form method="GET" action="financeiro.php" class="form-filtro">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="date" name="data_inicio" class="input-data" value="<?php echo $data_inicio; ?>" required>
                        <span style="color: #64748b; font-weight: bold;">Até</span>
                        <input type="date" name="data_fim" class="input-data" value="<?php echo $data_fim; ?>" required>
                    </div>
                    <button type="submit" class="btn-buscar"><i class="fa-solid fa-filter"></i> Filtrar</button>
                </form>
            </div>

            <div class="painel-lucro">
                <div class="grid-3-col">
                    <div class="card-fin" style="border-top: 4px solid #10b981;">
                        <h4><i class="fa-solid fa-arrow-trend-up"></i> Faturamento (Entradas)</h4>
                        <div class="valor valor-positivo">R$ <?php echo number_format($entradas_periodo, 2, ',', '.'); ?></div>
                    </div>
                    <div class="card-fin" style="border-top: 4px solid #ef4444;">
                        <h4><i class="fa-solid fa-arrow-trend-down"></i> Gastos (Saídas)</h4>
                        <div class="valor valor-negativo">R$ <?php echo number_format($saidas_periodo, 2, ',', '.'); ?></div>
                    </div>
                    
                    <?php $classe_lucro = ($lucro_periodo >= 0) ? 'card-destaque' : 'card-destaque prejuizo'; ?>
                    <div class="card-fin <?php echo $classe_lucro; ?>">
                        <h4><i class="fa-solid fa-sack-dollar"></i> Lucro do Período</h4>
                        <div class="valor">R$ <?php echo number_format($lucro_periodo, 2, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="separador-pagamentos">
                <div class="item-pagamento"><h4><i class="fa-solid fa-money-bill-wave" style="color: #16a34a;"></i> Dinheiro</h4><span>R$ <?php echo number_format($dinheiro, 2, ',', '.'); ?></span></div>
                <div class="item-pagamento"><h4><i class="fa-brands fa-pix" style="color: #0ea5e9;"></i> Pix</h4><span>R$ <?php echo number_format($pix, 2, ',', '.'); ?></span></div>
                <div class="item-pagamento"><h4><i class="fa-solid fa-credit-card" style="color: #f59e0b;"></i> Cartão</h4><span>R$ <?php echo number_format($cartao, 2, ',', '.'); ?></span></div>
            </div>

            <div class="secao-titulo" style="margin-top: 40px;">
                <span><i class="fa-solid fa-money-bill-transfer"></i> Extrato de Movimentações (Entradas e Saídas)</span>
            </div>
            
            <div class="tabela-wrapper">
                <table class="tabela-historico">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Descrição / Cliente</th>
                            <th>Categoria</th>
                            <th>Usuário</th>
                            <th style="text-align: right;">Valor (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transacoes) > 0): ?>
                            <?php foreach($transacoes as $t): 
                                $data_fmt = date("d/m/Y", strtotime($t['data']));
                                $valor_fmt = number_format($t['valor'], 2, ',', '.');
                                
                                if ($t['tipo'] == 'Entrada') {
                                    $classe_badge = 'badge-entrada';
                                    $icone = '<i class="fa-solid fa-arrow-up"></i> Entrada';
                                    $cor_valor = '#16a34a'; // Verde
                                    $sinal = '+';
                                } else {
                                    $classe_badge = 'badge-saida';
                                    $icone = '<i class="fa-solid fa-arrow-down"></i> Saída';
                                    $cor_valor = '#ef4444'; // Vermelho
                                    $sinal = '-';
                                }
                            ?>
                                <tr>
                                    <td><i class="fa-regular fa-calendar" style="color: #94a3b8; margin-right: 5px;"></i> <?php echo $data_fmt; ?></td>
                                    <td><span class="badge <?php echo $classe_badge; ?>"><?php echo $icone; ?></span></td>
                                    <td><strong><?php echo $t['descricao']; ?></strong></td>
                                    <td><span class="badge badge-categoria"><?php echo $t['categoria']; ?></span></td>
                                    <td><i class="fa-solid fa-user-pen" style="color: #cbd5e1; margin-right: 5px;"></i> <?php echo $t['usuario']; ?></td>
                                    <td style="text-align: right; color: <?php echo $cor_valor; ?>; font-weight: bold;"><?php echo $sinal; ?> R$ <?php echo $valor_fmt; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">Nenhuma movimentação neste período selecionado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div id="modalDespesa" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            <h2 style="color: #ef4444; margin-bottom: 20px;"><i class="fa-solid fa-minus-circle"></i> Registrar Gasto</h2>
            
            <form action="salvar_despesa.php" method="POST">
                <input type="hidden" name="usuario" value="<?php echo $_SESSION['usuario_logado']; ?>">

                <div class="input-grupo">
                    <label>Descrição (O que foi pago?):</label>
                    <input type="text" name="descricao" required placeholder="Ex: Sabão OMO, Conta de Luz, Aluguel">
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
                    <label>Valor Gasto (R$):</label>
                    <input type="text" name="valor" required id="inputValor" value="0,00" oninput="formatarMoeda(this)" style="text-align: right; font-weight: bold; color: #ef4444; font-size: 18px;">
                </div>

                <div class="input-grupo">
                    <label>Data do Gasto:</label>
                    <input type="date" name="data_despesa" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <button type="submit" style="width: 100%; margin-top: 10px; background: #ef4444; color: white; padding: 15px; border: none; border-radius: 5px; font-weight: bold; font-size: 16px; cursor: pointer;">Salvar Despesa</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() { document.getElementById('modalDespesa').style.display = 'block'; }
        function fecharModal() { document.getElementById('modalDespesa').style.display = 'none'; }
        
        function formatarMoeda(campo) {
            var valor = campo.value.replace(/\D/g, ''); 
            if (valor === '') { campo.value = '0,00'; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace('.', ',');
            campo.value = valor;
        }

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