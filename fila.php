<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
$podeEditar = ($_SESSION['pode_editar'] == 'Sim' || $ehAdmin);

// Busca os pedidos que NÃO estão entregues e organiza pela data mais antiga primeiro
$sql_fila = "SELECT p.id, c.nome AS cliente_nome, p.data_criacao, p.status, p.metodo_pagamento 
             FROM pedidos p 
             JOIN clientes c ON p.id_cliente = c.id 
             WHERE p.status != 'Entregue' 
             ORDER BY p.data_criacao ASC";
             
$resultado = $conexao->query($sql_fila);

// Organizando os pedidos nas 3 colunas exatas
$espera = [];
$lavando = [];
$prontas = [];

$hoje = new DateTime();

if ($resultado->num_rows > 0) {
    while($pedido = $resultado->fetch_assoc()) {
        $status_banco = trim($pedido['status']);
        
        // Cálculo de dias na fila (para o alerta visual)
        $data_pedido = new DateTime($pedido['data_criacao']);
        $dias_diferenca = $hoje->diff($data_pedido)->days;
        $pedido['dias_espera'] = $dias_diferenca;
        
        if ($status_banco == 'Esperando') {
            $espera[] = $pedido;
        } elseif ($status_banco == 'Lavando' || $status_banco == 'Secando') {
            $lavando[] = $pedido;
        } elseif ($status_banco == 'Lavado') {
            $prontas[] = $pedido;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fila de Produção - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ESTILOS DO QUADRO KANBAN */
        .kanban-container { display: flex; gap: 20px; margin-top: 20px; overflow-x: auto; padding-bottom: 20px; align-items: flex-start; }
        .kanban-coluna { flex: 1; min-width: 280px; background: #f1f5f9; border-radius: 8px; padding: 15px; border: 1px solid #e2e8f0; }
        
        .kanban-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #cbd5e1; }
        .kanban-header h3 { font-size: 16px; color: #334155; margin: 0; }
        .badge-qtd { background: #cbd5e1; color: #334155; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        
        /* CORES DAS COLUNAS */
        .col-espera .kanban-header { border-bottom-color: #f59e0b; }
        .col-espera .badge-qtd { background: #fef3c7; color: #b45309; }
        
        .col-lavando .kanban-header { border-bottom-color: #0ea5e9; }
        .col-lavando .badge-qtd { background: #e0f2fe; color: #0369a1; }
        
        .col-prontas .kanban-header { border-bottom-color: #10b981; }
        .col-prontas .badge-qtd { background: #d1fae5; color: #047857; }

        /* ESTILOS DOS CARTÕES */
        .card-roupa { background: #fff; padding: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 12px; border-left: 4px solid #ccc; position: relative; }
        .card-roupa:hover { box-shadow: 0 4px 6px rgba(0,0,0,0.1); transform: translateY(-2px); transition: 0.2s; }
        
        .card-espera { border-left-color: #f59e0b; }
        .card-lavando { border-left-color: #0ea5e9; }
        .card-pronta { border-left-color: #10b981; }
        
        /* ALERTA DE ATRASO */
        .card-atrasado { border: 1px solid #fecaca; background: #fef2f2; border-left: 4px solid #ef4444; }
        .badge-atraso { display: inline-block; background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-bottom: 5px; font-weight: bold; }
        
        .card-id { font-weight: bold; color: #64748b; font-size: 12px; margin-bottom: 5px; }
        .card-cliente { font-size: 15px; font-weight: bold; color: #1e293b; margin-bottom: 10px; }
        .card-data { font-size: 12px; color: #64748b; margin-bottom: 15px; display: flex; align-items: center; gap: 5px; }
        
        .card-acoes { display: flex; gap: 5px; border-top: 1px dashed #e2e8f0; padding-top: 10px; }
        .btn-acao { flex: 1; text-align: center; padding: 8px 5px; border-radius: 4px; font-size: 12px; font-weight: bold; text-decoration: none; display: block; border: none; cursor: pointer; }
        .btn-lavar { background: #0ea5e9; color: white; }
        .btn-avisar { background: #10b981; color: white; }
        .btn-entregar { background: #f59e0b; color: white; }
        .btn-imprimir { background: #f1f5f9; color: #475569; max-width: 40px; }

        /* MODAL (O MESMO DO PAINEL) */
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-caixa { background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 10px; width: 90%; max-width: 450px; position: relative; border-top: 5px solid #f59e0b; }
        .modal-fechar { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; margin-top: -10px; }
        .input-group select, .input-group input { width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none; box-sizing: border-box; }
        
        @media (max-width: 768px) {
            .kanban-container { flex-direction: column; overflow-x: visible; }
            .kanban-coluna { width: 100%; }
        }
    </style>
</head>
<body style="display: block;">

    <div class="dashboard-container">
        
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>

        <?php $pagina_atual = 'fila.php'; ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
                <img src="marca.jpg.png" alt="Lavanderia Confort" style="max-width: 140px; height: auto;">
                <button class="btn-fechar-menu" onclick="fecharMenuMobile()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <a href="painel.php" class="menu-item"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item ativo"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
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

        <div class="main-content">
            <div class="top-bar">
                <button class="btn-menu-mobile" onclick="abrirMenuMobile()"><i class="fa-solid fa-bars"></i></button>
                <h2>Quadro de Produção</h2>
            </div>

            <div class="kanban-container">
                
                <div class="kanban-coluna col-espera">
                    <div class="kanban-header">
                        <h3><i class="fa-solid fa-basket-shopping" style="color: #f59e0b;"></i> Roupas em Espera</h3>
                        <span class="badge-qtd"><?php echo count($espera); ?></span>
                    </div>
                    
                    <?php if(count($espera) == 0): ?>
                        <p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;">Nenhuma roupa esperando.</p>
                    <?php endif; ?>

                    <?php foreach($espera as $ped): 
                        $atrasado = ($ped['dias_espera'] >= 2) ? 'card-atrasado' : 'card-espera';
                    ?>
                        <div class="card-roupa <?php echo $atrasado; ?>">
                            <?php if($ped['dias_espera'] >= 2): ?>
                                <div class="badge-atraso"><i class="fa-solid fa-triangle-exclamation"></i> Parado há <?php echo $ped['dias_espera']; ?> dias</div>
                            <?php endif; ?>
                            
                            <div class="card-id">#<?php echo str_pad($ped['id'], 4, "0", STR_PAD_LEFT); ?></div>
                            <div class="card-cliente"><?php echo $ped['cliente_nome']; ?></div>
                            <div class="card-data"><i class="fa-regular fa-calendar"></i> Entrada: <?php echo date("d/m", strtotime($ped['data_criacao'])); ?></div>
                            
                            <div class="card-acoes">
                                <a href="mudar_status.php?id=<?php echo $ped['id']; ?>" class="btn-acao btn-lavar"><i class="fa-solid fa-play"></i> Iniciar Lavagem</a>
                                <a href="imprimir_recibo.php?id=<?php echo $ped['id']; ?>" target="_blank" class="btn-acao btn-imprimir" title="Imprimir Recibo"><i class="fa-solid fa-print"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="kanban-coluna col-lavando">
                    <div class="kanban-header">
                        <h3><i class="fa-solid fa-water" style="color: #0ea5e9;"></i> Roupas Lavando</h3>
                        <span class="badge-qtd"><?php echo count($lavando); ?></span>
                    </div>

                    <?php if(count($lavando) == 0): ?>
                        <p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;">Nenhuma máquina em uso.</p>
                    <?php endif; ?>

                    <?php foreach($lavando as $ped): 
                        $atrasado = ($ped['dias_espera'] >= 3) ? 'card-atrasado' : 'card-lavando';
                    ?>
                        <div class="card-roupa <?php echo $atrasado; ?>">
                            <div class="card-id">#<?php echo str_pad($ped['id'], 4, "0", STR_PAD_LEFT); ?></div>
                            <div class="card-cliente"><?php echo $ped['cliente_nome']; ?></div>
                            <div class="card-data"><i class="fa-regular fa-clock"></i> No processo há <?php echo $ped['dias_espera']; ?> dias</div>
                            
                            <div class="card-acoes">
                                <a href="concluir_pedido.php?id=<?php echo $ped['id']; ?>" class="btn-acao btn-avisar"><i class="fa-solid fa-check"></i> Marcar como Pronta</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="kanban-coluna col-prontas">
                    <div class="kanban-header">
                        <h3><i class="fa-solid fa-check-double" style="color: #10b981;"></i> Roupas Prontas</h3>
                        <span class="badge-qtd"><?php echo count($prontas); ?></span>
                    </div>

                    <?php if(count($prontas) == 0): ?>
                        <p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;">Nenhuma roupa aguardando recolha.</p>
                    <?php endif; ?>

                    <?php foreach($prontas as $ped): ?>
                        <div class="card-roupa card-pronta">
                            <div class="card-id">#<?php echo str_pad($ped['id'], 4, "0", STR_PAD_LEFT); ?></div>
                            <div class="card-cliente"><?php echo $ped['cliente_nome']; ?></div>
                            
                            <?php if ($ped['metodo_pagamento'] == 'Pendente'): ?>
                                <div style="color: #ef4444; font-size: 12px; font-weight: bold; margin-bottom: 10px;"><i class="fa-solid fa-circle-exclamation"></i> Falta pagar</div>
                            <?php else: ?>
                                <div style="color: #10b981; font-size: 12px; font-weight: bold; margin-bottom: 10px;"><i class="fa-solid fa-circle-check"></i> Já pago</div>
                            <?php endif; ?>

                            <div class="card-acoes">
                                <button onclick="abrirModalEntrega(<?php echo $ped['id']; ?>, '<?php echo addslashes($ped['cliente_nome']); ?>', '<?php echo $ped['metodo_pagamento']; ?>')" class="btn-acao btn-entregar"><i class="fa-solid fa-hand-holding-hand"></i> Entregar Roupa</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

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

    <script>
        // JS DA ENTREGA
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