<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
$podeEditar = ($_SESSION['pode_editar'] == 'Sim' || $ehAdmin);

// Consultas para os cards (Faturamento e Totais)
$sql_faturamento = "SELECT SUM(valor) AS total FROM pedidos WHERE metodo_pagamento != 'Pendente'";
$faturamento = $conexao->query($sql_faturamento)->fetch_assoc()['total'] ?? 0;
$faturamento_formatado = number_format($faturamento, 2, ',', '.');

$total_pedidos = $conexao->query("SELECT COUNT(id) AS qtd FROM pedidos")->fetch_assoc()['qtd'];
$total_clientes = $conexao->query("SELECT COUNT(id) AS qtd FROM clientes")->fetch_assoc()['qtd'];
$total_fila = $conexao->query("SELECT COUNT(id) AS qtd FROM pedidos WHERE status IN ('Esperando', 'Lavando', 'Secando')")->fetch_assoc()['qtd'];

// Consulta da tabela de últimos pedidos
$sql_pedidos = "SELECT p.id, c.nome AS cliente_nome, p.data_criacao, p.valor, p.status, p.recebedor, p.metodo_pagamento 
                FROM pedidos p JOIN clientes c ON p.id_cliente = c.id ORDER BY p.data_criacao DESC LIMIT 10";
$resultado_tabela = $conexao->query($sql_pedidos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-caixa { background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 10px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; border-top: 5px solid #f59e0b; }
        .modal-fechar { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; margin-top: -10px; }
        .modal-fechar:hover { color: #333; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .input-group select, .input-group input { width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none; box-sizing: border-box; }
    </style>
</head>
<body style="display: block;">

    <div class="dashboard-container">
        
        <!-- FUNDO ESCURO DO MENU MOBILE -->
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>

        <!-- MENU LATERAL -->
        <?php $pagina_atual = 'painel.php'; ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
             <img src="marca.jpg.png" alt="Lavanderia Confort" style="max-width: 140px; height: auto;">
                <!-- Botão X que só aparece no celular/tablet -->
                <button class="btn-fechar-menu" onclick="fecharMenuMobile()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <a href="painel.php" class="menu-item ativo"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
            <a href="clientes.php" class="menu-item"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="servicos.php" class="menu-item"><i class="fa-solid fa-tag"></i> Serviços</a>
            
            <?php if ($ehAdmin): ?>
                <a href="funcionarios.php" class="menu-item"><i class="fa-solid fa-id-card"></i> Funcionários</a>
                <a href="financeiro.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Financeiro</a>
            <?php endif; ?>
            
            <div style="flex-grow: 1;"></div>
            <a href="perfil.php" class="menu-item" style="border-top: 1px solid #e2e8f0;"><i class="fa-solid fa-user-gear"></i> Meu Perfil</a>
            <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="main-content">
            <div class="top-bar">
                <!-- Botão Hamburguer (Só aparece no celular/tablet) -->
                <button class="btn-menu-mobile" onclick="abrirMenuMobile()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                
                <h2>Visão Geral</h2>
                
                <div class="user-info">
                    <i class="fa-solid fa-circle-user"></i> Olá, <?php echo $_SESSION['usuario_logado']; ?> 
                </div>
            </div>

            <!-- CARDS INTELIGENTES -->
            <div class="cards-grid">
                <?php if ($ehAdmin): ?>
                <div class="summary-card">
                    <div class="card-header"><span>Faturamento Total</span><div class="card-icon icon-purple"><i class="fa-solid fa-wallet"></i></div></div>
                    <div class="card-value">R$ <?php echo $faturamento_formatado; ?></div>
                </div>
                <?php endif; ?>

                <div class="summary-card">
                    <div class="card-header"><span>Roupas na Fila</span><div class="card-icon icon-green"><i class="fa-solid fa-clock-rotate-left"></i></div></div>
                    <div class="card-value"><?php echo $total_fila; ?></div>
                </div>
                <div class="summary-card">
                    <div class="card-header"><span>Pedidos Criados</span><div class="card-icon icon-blue"><i class="fa-solid fa-bag-shopping"></i></div></div>
                    <div class="card-value"><?php echo $total_pedidos; ?></div>
                </div>
                
                <?php if ($ehAdmin): ?>
                <div class="summary-card">
                    <div class="card-header"><span>Base de Clientes</span><div class="card-icon icon-orange"><i class="fa-solid fa-users"></i></div></div>
                    <div class="card-value"><?php echo $total_clientes; ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- TABELA DE ÚLTIMOS PEDIDOS -->
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
                                $metodo_pag = trim($pedido['metodo_pagamento']);

                                $classeStatus = 'status-esperando';
                                $textoStatus = 'Na Fila'; 
                                if ($status_banco == 'Lavando' || $status_banco == 'Secando') { $classeStatus = 'status-lavando'; $textoStatus = 'Lavando'; } 
                                elseif ($status_banco == 'Lavado') { $classeStatus = 'status-lavado'; $textoStatus = 'Aguardando Recolha'; } 
                                elseif ($status_banco == 'Entregue') { $classeStatus = 'status-lavado'; $textoStatus = 'Finalizado'; }

                                echo "<tr>";
                                echo "<td><a href='detalhes_pedido.php?id=" . $pedido['id'] . "' style='color: #0284c7; text-decoration: none; font-weight: bold;'>#" . str_pad($pedido['id'], 4, "0", STR_PAD_LEFT) . "</a>";
                                echo "<a href='imprimir_recibo.php?id=" . $pedido['id'] . "' target='_blank' style='color: #4b5563; margin-left: 15px;'><i class='fa-solid fa-print'></i></a>";
                                
                                // SOMENTE ADMINISTRADOR PODE APAGAR PEDIDOS
                                if ($ehAdmin) { 
                                    echo "<a href='excluir_pedido.php?id=" . $pedido['id'] . "' style='color: #ef4444; margin-left: 15px;' onclick='return confirm(\"Apagar pedido?\")'><i class='fa-solid fa-trash-can'></i></a>"; 
                                }
                                
                                echo "</td>";
                                echo "<td><strong>" . $pedido['cliente_nome'] . "</strong></td>";
                                echo "<td>" . $dataFormatada . "</td>";
                                
                                if ($metodo_pag == 'Pendente' && $status_banco != 'Entregue') {
                                    echo "<td style='color: #ef4444; font-weight: bold;'>R$ " . $valorFormatado . " <span style='font-size:10px; display:block;'>[A RECEBER]</span></td>";
                                } else {
                                    echo "<td style='color: #16a34a; font-weight: bold;'>R$ " . $valorFormatado . "</td>";
                                }
                                
                                echo "<td style='text-align: center;'><span class='status " . $classeStatus . "'>" . $textoStatus . "</span></td>";
                                echo "<td style='text-align: center;'>";
                                if ($status_banco == 'Esperando') {
                                    echo "<a href='mudar_status.php?id=" . $pedido['id'] . "' style='background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px;'><i class='fa-solid fa-play'></i> Lavar</a>";
                                } elseif ($status_banco == 'Lavando' || $status_banco == 'Secando') {
                                    echo "<a href='concluir_pedido.php?id=" . $pedido['id'] . "' style='background-color: #16a34a; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px;'><i class='fa-brands fa-whatsapp'></i> Avisar</a>";
                                } elseif ($status_banco == 'Lavado') {
                                    echo "<button onclick='abrirModalEntrega(" . $pedido['id'] . ", \"" . addslashes($pedido['cliente_nome']) . "\", \"" . $metodo_pag . "\")' style='background-color: #f59e0b; color: white; padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 13px;'><i class='fa-solid fa-hand-holding-hand'></i> Entregar</button>";
                                } elseif ($status_banco == 'Entregue') {
                                    echo "<span style='color: #666; font-size: 13px;'><i class='fa-solid fa-check'></i> " . $pedido['recebedor'] . "</span>";
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

    <!-- JANELA FLUTUANTE PARA REGISTRAR ENTREGA -->
    <div id="janelaEntrega" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModalEntrega()">&times;</span>
            <h2 style="color: #f59e0b; margin-bottom: 20px;"><i class="fa-solid fa-hand-holding-hand"></i> Registrar Entrega</h2>
            <p style="margin-bottom: 15px; color: #555; font-size: 15px;">Dono das roupas: <strong id="nomeClienteModal" style="color: #333;"></strong></p>
            
            <form action="entregar_pedido.php" method="POST">
                <input type="hidden" name="id_pedido" id="idPedidoModal">
                
                <div class="input-group" id="grupoPagamentoModal" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: bold; margin-bottom: 5px; display: block; color: #dc2626;">Forma de Pagamento (Recebendo Agora):</label>
                    <select name="metodo_pagamento" id="metodoPagamentoModal">
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Pix">Pix</option>
                        <option value="Cartão">Cartão</option>
                    </select>
                </div>

                <div class="input-group">
                    <label style="font-weight: bold; margin-bottom: 5px; display: block;">Quem está retirando as roupas?</label>
                    <input type="text" name="recebedor" placeholder="Ex: O próprio, Maria (Esposa)..." required>
                </div>
                
                <button type="submit" style="width: 100%; margin-top: 20px; background: #f59e0b; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; font-size: 15px; cursor: pointer;">Confirmar Entrega e Fechar</button>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT: MOTOR DA PÁGINA E DO MENU -->
    <script>
        // MOTOR DA ENTREGA
        function abrirModalEntrega(id, nome, metodoPagamento) {
            document.getElementById('idPedidoModal').value = id;
            document.getElementById('nomeClienteModal').innerText = nome;
            
            let grupoPagamento = document.getElementById('grupoPagamentoModal');
            let selectPagamento = document.getElementById('metodoPagamentoModal');
            
            if (metodoPagamento === 'Pendente') {
                grupoPagamento.style.display = 'block';
                selectPagamento.setAttribute('required', 'required');
            } else {
                grupoPagamento.style.display = 'none';
                selectPagamento.removeAttribute('required');
            }
            
            document.getElementById('janelaEntrega').style.display = 'block';
        }
        
        function fecharModalEntrega() {
            document.getElementById('janelaEntrega').style.display = 'none';
        }

        // MOTOR DO MENU MOBILE
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