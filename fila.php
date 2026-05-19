<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// 1. ESPERANDO (Mais antigos primeiro)
$sql_esperando = "SELECT p.id, c.nome, p.descricao, p.data_criacao FROM pedidos p JOIN clientes c ON p.id_cliente = c.id WHERE p.status = 'Esperando' ORDER BY p.data_criacao ASC";
$resultado_esperando = $conexao->query($sql_esperando);

// 2. NAS MÁQUINAS (Lavando/Processando)
$sql_lavando = "SELECT p.id, c.nome, p.descricao, c.telefone FROM pedidos p JOIN clientes c ON p.id_cliente = c.id WHERE p.status = 'Lavando'";
$resultado_lavando = $conexao->query($sql_lavando);
$maquinas_ocupadas = $resultado_lavando->num_rows; 
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fila de Produção - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: block;">

    <div class="dashboard-container">
        <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
       <?php 
            $pagina_atual = basename($_SERVER['PHP_SELF']); 
            $ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
        ?>
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>

        <?php 
            $pagina_atual = basename($_SERVER['PHP_SELF']); 
            $ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
        ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
                <img src="logo.png" alt="Lavanderia" style="max-width: 140px; height: auto;">
              <button class="btn-menu-mobile" onclick="abrirMenuMobile()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <a href="painel.php" class="menu-item <?php echo ($pagina_atual == 'painel.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item <?php echo ($pagina_atual == 'fila.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item <?php echo ($pagina_atual == 'novo_pedido.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
            <a href="clientes.php" class="menu-item <?php echo ($pagina_atual == 'clientes.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="servicos.php" class="menu-item <?php echo ($pagina_atual == 'servicos.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-tag"></i> Serviços</a>
            
            <?php if ($ehAdmin): ?>
                <a href="funcionarios.php" class="menu-item <?php echo ($pagina_atual == 'funcionarios.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-id-card"></i> Funcionários</a>
                <a href="financeiro.php" class="menu-item <?php echo ($pagina_atual == 'financeiro.php' || $pagina_atual == 'despesas.php') ? 'ativo' : ''; ?>"><i class="fa-solid fa-chart-line"></i> Financeiro</a>
            <?php endif; ?>
            
            <div style="flex-grow: 1;"></div>
            <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="main-content">
            <div class="top-bar">
                <h2>Linha de Produção Simplificada</h2>
            </div>

            <div class="table-container" style="border-top: 5px solid #0284c7; margin-bottom: 30px;">
                <div class="table-header">
                    <h3><i class="fa-solid fa-water"></i> Atualmente nas Máquinas (<?php echo $maquinas_ocupadas; ?>/8)</h3>
                </div>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Cliente</th><th>Serviço</th><th style="text-align: right;">Ação</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($maquinas_ocupadas > 0) {
                            while($lavando = $resultado_lavando->fetch_assoc()) {
                                echo "<tr style='background-color: #f0f9ff;'>";
                                echo "<td>#" . str_pad($lavando['id'], 4, "0", STR_PAD_LEFT) . "</td>";
                                echo "<td><strong>" . $lavando['nome'] . "</strong></td>";
                                echo "<td>" . $lavando['descricao'] . "</td>";
                                echo "<td style='text-align: right;'><a href='concluir_pedido.php?id=" . $lavando['id'] . "' style='background-color: #16a34a; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold;'><i class='fa-brands fa-whatsapp'></i> Finalizar e Avisar</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; color: #666; padding: 20px;'>Nenhuma máquina ocupada.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="table-container" style="border-top: 5px solid #f59e0b;">
                <div class="table-header">
                    <h3><i class="fa-solid fa-clock"></i> Roupas em Espera (Prioridade)</h3>
                </div>
                <table>
                    <thead>
                        <tr><th style="width: 80px; text-align: center;">ORDEM</th><th>ID</th><th>Cliente</th><th>Serviço</th><th style="text-align: right;">Ação</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultado_esperando->num_rows > 0) {
                            $posicao = 1;
                            while($espera = $resultado_esperando->fetch_assoc()) {
                                $corOrdem = ($posicao == 1) ? "background: #ef4444; color: white;" : "background: #f1f5f9; color: #333;";
                                echo "<tr>";
                                echo "<td style='text-align: center;'><span style='padding: 3px 10px; border-radius: 12px; font-weight: bold; ". $corOrdem ."'>". $posicao ."º</span></td>";
                                echo "<td>#" . str_pad($espera['id'], 4, "0", STR_PAD_LEFT) . "</td>";
                                echo "<td>" . $espera['nome'] . "</td>";
                                echo "<td>" . $espera['descricao'] . "</td>";
                                echo "<td style='text-align: right;'><a href='mudar_status.php?id=" . $espera['id'] . "' style='background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold;'><i class='fa-solid fa-play'></i> Iniciar Lavagem</a></td>";
                                echo "</tr>";
                                $posicao++; 
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center; color: #666; padding: 20px;'>Nenhum pedido aguardando.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>