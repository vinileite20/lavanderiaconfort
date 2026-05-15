<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$mesAtual = date('m');
$anoAtual = date('Y');

$sql_entradas = "SELECT SUM(valor) as total FROM pedidos WHERE MONTH(data_criacao) = '$mesAtual' AND YEAR(data_criacao) = '$anoAtual'";
$entradas = $conexao->query($sql_entradas)->fetch_assoc()['total'] ?? 0;

$sql_saidas = "SELECT SUM(valor) as total FROM despesas WHERE MONTH(data_despesa) = '$mesAtual' AND YEAR(data_despesa) = '$anoAtual'";
$saidas = $conexao->query($sql_saidas)->fetch_assoc()['total'] ?? 0;

$saldo = $entradas - $saidas;

$sql_categorias = "SELECT categoria, SUM(valor) as total FROM despesas WHERE MONTH(data_despesa) = '$mesAtual' AND YEAR(data_despesa) = '$anoAtual' GROUP BY categoria";
$resultado_categorias = $conexao->query($sql_categorias);

$nomes_categorias = [];
$valores_categorias = [];
if ($resultado_categorias->num_rows > 0) {
    while($row = $resultado_categorias->fetch_assoc()) {
        $nomes_categorias[] = $row['categoria'];
        $valores_categorias[] = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Financeiro - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .modal-fundo {
            display: none; 
            position: fixed;
            z-index: 9999; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); 
        }
        .modal-caixa {
            background-color: #fff;
            margin: 5% auto; 
            padding: 25px;
            border-radius: 10px;
            width: 450px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
            border-top: 5px solid #ef4444; 
        }
        .modal-fechar {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            margin-top: -10px;
        }
        .modal-fechar:hover {
            color: #333;
        }
    </style>
</head>
<body style="display: block;">

    <div class="dashboard-container">
       <!-- INÍCIO DO MENU LATERAL PADRONIZADO -->
        <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="logo.png" alt="Lavanderia" style="max-width: 160px; height: auto;">
            </div>
            
            <a href="painel.php" class="menu-item <?php echo ($pagina_atual == 'painel.php') ? 'ativo' : ''; ?>">
                <i class="fa-solid fa-house"></i> Tela Inicial
            </a>
            
            <a href="fila.php" class="menu-item <?php echo ($pagina_atual == 'fila.php') ? 'ativo' : ''; ?>">
                <i class="fa-solid fa-list-ol"></i> Fila de Produção
            </a>
            
            <a href="novo_pedido.php" class="menu-item <?php echo ($pagina_atual == 'novo_pedido.php') ? 'ativo' : ''; ?>">
                <i class="fa-solid fa-file-lines"></i> Novo Pedido
            </a>
            
            <a href="clientes.php" class="menu-item <?php echo ($pagina_atual == 'clientes.php') ? 'ativo' : ''; ?>">
                <i class="fa-solid fa-users"></i> Clientes
            </a>
            
            <a href="servicos.php" class="menu-item <?php echo ($pagina_atual == 'servicos.php') ? 'ativo' : ''; ?>">
                <i class="fa-solid fa-tag"></i> Serviços
            </a>
            
            <a href="financeiro.php" class="menu-item <?php echo ($pagina_atual == 'financeiro.php') ? 'ativo' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Financeiro
            </a>
            <a href="funcionarios.php" class="menu-item"><i class="fa-solid fa-id-card"></i> Funcionários</a>
            <div style="flex-grow: 1;"></div>
            
            <a href="logout.php" class="menu-item" style="color: #d32f2f;">
                <i class="fa-solid fa-right-from-bracket"></i> Sair
            </a>
        </div>
        <!-- FIM DO MENU LATERAL -->

        <div class="main-content">
            <div class="top-bar">
                <h2>Dashboard Financeiro (Mês Atual)</h2>
                <button onclick="abrirModal()" class="btn-entrar" style="width: auto; padding: 8px 15px; font-size: 14px; background: #ef4444; border: none; cursor: pointer; color: white;">
                    <i class="fa-solid fa-plus"></i> Lançar Despesa
                </button>
            </div>

            <div class="cards-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="summary-card" style="border-left: 5px solid #16a34a;">
                    <div class="card-header"><span>Entradas (Receitas)</span><div class="card-icon" style="background: #dcfce7; color: #16a34a;"><i class="fa-solid fa-arrow-up"></i></div></div>
                    <div class="card-value" style="color: #16a34a;">R$ <?php echo number_format($entradas, 2, ',', '.'); ?></div>
                </div>

                <div class="summary-card" style="border-left: 5px solid #ef4444;">
                    <div class="card-header"><span>Saídas (Despesas)</span><div class="card-icon" style="background: #fee2e2; color: #ef4444;"><i class="fa-solid fa-arrow-down"></i></div></div>
                    <div class="card-value" style="color: #ef4444;">R$ <?php echo number_format($saidas, 2, ',', '.'); ?></div>
                </div>

                <?php 
                    $corSaldo = ($saldo >= 0) ? '#0ea5e9' : '#ef4444'; 
                    $textoSaldo = ($saldo >= 0) ? 'Lucro Líquido' : 'Prejuízo';
                    $iconeSaldo = ($saldo >= 0) ? 'fa-face-smile' : 'fa-face-sad-tear';
                ?>
                <div class="summary-card" style="border-left: 5px solid <?php echo $corSaldo; ?>;">
                    <div class="card-header"><span><?php echo $textoSaldo; ?></span><div class="card-icon" style="background: #f1f5f9; color: <?php echo $corSaldo; ?>;"><i class="fa-solid <?php echo $iconeSaldo; ?>"></i></div></div>
                    <div class="card-value" style="color: <?php echo $corSaldo; ?>;">R$ <?php echo number_format($saldo, 2, ',', '.'); ?></div>
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
                <div class="table-container" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h3 style="margin-bottom: 15px; color: #333;"><i class="fa-solid fa-scale-balanced"></i> Balanço do Mês</h3>
                    <div style="position: relative; height: 300px; width: 100%;"><canvas id="graficoBalanco"></canvas></div>
                </div>
                <div class="table-container" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h3 style="margin-bottom: 15px; color: #333;"><i class="fa-solid fa-chart-pie"></i> Gastos por Categoria</h3>
                    <div style="position: relative; height: 300px; width: 100%;"><canvas id="graficoCategorias"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div id="janelaDespesa" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            <h2 style="color: #ef4444; margin-bottom: 20px;"><i class="fa-solid fa-file-invoice-dollar"></i> Registrar Despesa</h2>
            
            <form action="salvar_despesa.php" method="POST">
                <div class="input-group">
                    <label>Descrição (Ex: Compra de Sabão):</label>
                    <input type="text" name="descricao" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div class="input-group" style="margin-top: 10px;">
                    <label>Categoria:</label>
                    <select name="categoria" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                        <option value="Insumos">Insumos (Sabão, Amaciante, etc)</option>
                        <option value="Água">Conta de Água</option>
                        <option value="Energia">Conta de Energia</option>
                        <option value="Aluguel">Aluguel</option>
                        <option value="Salário">Salário / Funcionários</option>
                        <option value="Manutenção">Manutenção de Máquinas</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>
                <div class="input-group" style="margin-top: 10px;">
                    <label>Data:</label>
                    <input type="date" name="data_despesa" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div class="input-group" style="margin-top: 10px;">
                    <label>Valor (R$):</label>
                    <input type="text" name="valor" placeholder="0,00" oninput="formatarMoeda(this)" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <button type="submit" style="width: 100%; margin-top: 20px; background: #ef4444; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Salvar Despesa</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() { 
            document.getElementById('janelaDespesa').style.display = 'block'; 
        }
        
        function fecharModal() { 
            document.getElementById('janelaDespesa').style.display = 'none'; 
        }

        function formatarMoeda(campo) {
            var valor = campo.value.replace(/\D/g, ''); 
            if (valor === '') { campo.value = ''; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace('.', ',');
            campo.value = valor;
        }

        const ctxBalanco = document.getElementById('graficoBalanco').getContext('2d');
        new Chart(ctxBalanco, {
            type: 'bar',
            data: {
                labels: ['Entradas', 'Saídas'],
                datasets: [{ label: 'Valor em R$', data: [<?php echo $entradas; ?>, <?php echo $saidas; ?>], backgroundColor: ['rgba(22, 163, 74, 0.7)', 'rgba(239, 68, 68, 0.7)'], borderColor: ['rgba(22, 163, 74, 1)', 'rgba(239, 68, 68, 1)'], borderWidth: 1 }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxCategorias = document.getElementById('graficoCategorias').getContext('2d');
        new Chart(ctxCategorias, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($nomes_categorias); ?>,
                datasets: [{ data: <?php echo json_encode($valores_categorias); ?>, backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#64748b'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>