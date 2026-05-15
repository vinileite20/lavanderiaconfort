<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$sql_faturamento = "SELECT SUM(valor) AS total FROM pedidos";
$faturamento = $conexao->query($sql_faturamento)->fetch_assoc()['total'] ?? 0;
$faturamento_formatado = number_format($faturamento, 2, ',', '.');

$total_pedidos = $conexao->query("SELECT COUNT(id) AS qtd FROM pedidos")->fetch_assoc()['qtd'];
$total_clientes = $conexao->query("SELECT COUNT(id) AS qtd FROM clientes")->fetch_assoc()['qtd'];
$total_fila = $conexao->query("SELECT COUNT(id) AS qtd FROM pedidos WHERE status IN ('Esperando', 'Lavando')")->fetch_assoc()['qtd'];

$sql_pedidos = "SELECT p.id, c.nome AS cliente_nome, p.data_criacao, p.valor, p.status, p.recebedor 
                FROM pedidos p JOIN clientes c ON p.id_cliente = c.id ORDER BY p.data_criacao DESC LIMIT 10";
$resultado_tabela = $conexao->query($sql_pedidos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-caixa { background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 10px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; border-top: 5px solid #f59e0b; }
        .modal-fechar { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; margin-top: -10px; }
        .modal-fechar:hover { color: #333; }
    </style>
</head>
<body style="display: block;">

    <div class="dashboard-container">
        <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
        <div class="sidebar">
            <div class="sidebar-logo"><img src="logo.png" alt="Lavanderia" style="max-width: 160px; height: auto;"></div>
            <a href="painel.php" class="menu-item <?php echo ($pagina_atual == 'painel.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item <?php echo ($pagina_atual == 'fila.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item <?php echo ($pagina_atual == 'novo_pedido.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
            <a href="clientes.php" class="menu-item <?php echo ($pagina_atual == 'clientes.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="servicos.php" class="menu-item <?php echo ($pagina_atual == 'servicos.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-tag"></i> Serviços</a>
            <a href="financeiro.php" class="menu-item <?php echo ($pagina_atual == 'financeiro.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-chart-line"></i> Financeiro</a>
            <a href="funcionarios.php" class="menu-item"><i class="fa-solid fa-id-card"></i> Funcionários</a>
            <div style="flex-grow: 1;"></div>
            <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <h2>Visão Geral</h2>
                <div class="user-info"><i class="fa-solid fa-circle-user"></i> Olá, <?php echo $_SESSION['usuario_logado']; ?></div>
            </div>

            <div class="cards-grid">
                <div class="summary-card">
                    <div class="card-header"><span>Faturamento</span><div class="card-icon icon-purple"><i class="fa-solid fa-wallet"></i></div></div>
                    <div class="card-value">R$ <?php echo $faturamento_formatado; ?></div>
                    <div class="card-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Total acumulado</div>
                </div>
                <div class="summary-card">
                    <div class="card-header"><span>Total de Pedidos</span><div class="card-icon icon-blue"><i class="fa-solid fa-bag-shopping"></i></div></div>
                    <div class="card-value"><?php echo $total_pedidos; ?></div>
                    <div class="card-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Pedidos criados</div>
                </div>
                <div class="summary-card">
                    <div class="card-header"><span>Clientes</span><div class="card-icon icon-orange"><i class="fa-solid fa-users"></i></div></div>
                    <div class="card-value"><?php echo $total_clientes; ?></div>
                    <div class="card-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Na base de dados</div>
                </div>
                <div class="summary-card">
                    <div class="card-header"><span>Roupas na Fila</span><div class="card-icon icon-green"><i class="fa-solid fa-clock-rotate-left"></i></div></div>
                    <div class="card-value"><?php echo $total_fila; ?></div>
                    <div class="card-trend trend-down"><i class="fa-solid fa-arrows-spin"></i> Em processo</div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Últimos Pedidos</h3>
                    <a href="novo_pedido.php" class="btn-entrar" style="width: auto; padding: 8px 15px; font-size: 14px; text-decoration: none;">+ Novo Pedido</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CLIENTE</th>
                            <th>DATA</th>
                            <th>VALOR</th>
                            <th style="text-align: center;">STATUS</th>
                            <th style="text-align: center;">AÇÃO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultado_tabela->num_rows > 0) {
                            while($pedido = $resultado_tabela->fetch_assoc()) {
                                $valorFormatado = number_format($pedido['valor'], 2, ',', '.'); 
                                $dataFormatada = date("d/m/Y", strtotime($pedido['data_criacao'])); 
                                $status_banco = trim($pedido['status']);

                                $classeStatus = 'status-esperando';
                                $textoStatus = 'Na Fila'; 
                                
                                if ($status_banco == 'Lavando' || $status_banco == 'Secando') { 
                                    $classeStatus = 'status-lavando'; $textoStatus = 'Lavando';
                                } elseif ($status_banco == 'Lavado') { 
                                    $classeStatus = 'status-lavado'; $textoStatus = 'Aguardando Recolha'; 
                                } elseif ($status_banco == 'Entregue') {
                                    $classeStatus = 'status-lavado'; $textoStatus = 'Finalizado'; 
                                }

                                echo "<tr>";
                                
                                // COLUNA 1: ID CLICÁVEL + IMPRESSORA
                                echo "<td>";
                                echo "<a href='detalhes_pedido.php?id=" . $pedido['id'] . "' style='color: #0284c7; text-decoration: none; font-weight: bold; font-size: 15px;'>#" . str_pad($pedido['id'], 4, "0", STR_PAD_LEFT) . "</a>";
                                echo "<a href='imprimir_recibo.php?id=" . $pedido['id'] . "' target='_blank' style='color: #4b5563; margin-left: 15px; text-decoration: none;' title='Imprimir Recibo'><i class='fa-solid fa-print'></i></a>";
                                echo "</td>";

                                echo "<td><strong>" . $pedido['cliente_nome'] . "</strong></td>";
                                echo "<td>" . $dataFormatada . "</td>";
                                echo "<td>R$ " . $valorFormatado . "</td>";
                                echo "<td style='text-align: center;'><span class='status " . $classeStatus . "'>" . $textoStatus . "</span></td>";
                                
                                // COLUNA DE AÇÕES
                                echo "<td style='text-align: center;'>";
                                if ($status_banco == 'Esperando') {
                                    echo "<a href='mudar_status.php?id=" . $pedido['id'] . "' style='display: inline-block; background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold;'><i class='fa-solid fa-play'></i> Lavar</a>";
                                } elseif ($status_banco == 'Lavando' || $status_banco == 'Secando') {
                                    echo "<a href='concluir_pedido.php?id=" . $pedido['id'] . "' style='display: inline-block; background-color: #16a34a; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold;'><i class='fa-brands fa-whatsapp'></i> Avisar</a>";
                                } elseif ($status_banco == 'Lavado') {
                                    echo "<button onclick='abrirModalEntrega(" . $pedido['id'] . ", \"" . addslashes($pedido['cliente_nome']) . "\")' style='background-color: #f59e0b; color: white; padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: bold;'><i class='fa-solid fa-hand-holding-hand'></i> Entregar Roupa</button>";
                                } elseif ($status_banco == 'Entregue') {
                                    echo "<span style='color: #666; font-size: 13px; font-weight: bold;'><i class='fa-solid fa-check'></i> Recebido por: <span style='color: #333;'>" . $pedido['recebedor'] . "</span></span>";
                                }
                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center; padding: 30px;'>Nenhum pedido lançado ainda.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div> 
    </div> 

    <div id="janelaEntrega" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModalEntrega()">&times;</span>
            <h2 style="color: #f59e0b; margin-bottom: 20px;"><i class="fa-solid fa-hand-holding-hand"></i> Registrar Entrega</h2>
            <p style="margin-bottom: 15px; color: #555; font-size: 15px;">Dono das roupas: <strong id="nomeClienteModal" style="color: #333;"></strong></p>
            <form action="entregar_pedido.php" method="POST">
                <input type="hidden" name="id_pedido" id="idPedidoModal">
                <div class="input-group">
                    <label style="font-weight: bold; margin-bottom: 5px; display: block;">Quem está retirando as roupas?</label>
                    <input type="text" name="recebedor" placeholder="Ex: O próprio, Maria (Esposa)..." required style="width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none;">
                </div>
                <button type="submit" style="width: 100%; margin-top: 20px; background: #f59e0b; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; font-size: 15px; cursor: pointer;">Confirmar Entrega</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalEntrega(id, nome) {
            document.getElementById('idPedidoModal').value = id;
            document.getElementById('nomeClienteModal').innerText = nome;
            document.getElementById('janelaEntrega').style.display = 'block';
        }
        function fecharModalEntrega() {
            document.getElementById('janelaEntrega').style.display = 'none';
        }
    </script>
</body>
</html>