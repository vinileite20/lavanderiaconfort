<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$sql = "SELECT * FROM servicos ORDER BY nome_servico ASC";
$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Serviços - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <h2>Catálogo de Serviços</h2>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Serviços e Preços</h3>
                    <a href="novo_servico.php" class="btn-entrar" style="width: auto; padding: 8px 15px; font-size: 14px; text-decoration: none;">+ Novo Serviço</a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>NOME DO SERVIÇO</th>
                            <th>PREÇO PADRÃO</th>
                            <th>REGRA DE VALOR</th>
                            <th style="text-align: center;">AÇÕES</th> </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultado->num_rows > 0) {
                            while($servico = $resultado->fetch_assoc()) {
                                $valor = number_format($servico['preco'], 2, ',', '.');
                                
                                if ($servico['preco_fixo'] == 1) {
                                    $regra = "<span style='color: #16a34a; font-weight: bold;'><i class='fa-solid fa-lock'></i> Valor Fixo</span>";
                                } else {
                                    $regra = "<span style='color: #f59e0b; font-weight: bold;'><i class='fa-solid fa-pen-to-square'></i> Pode Alterar</span>";
                                }

                                echo "<tr>";
                                echo "<td>" . $servico['nome_servico'] . "</td>";
                                echo "<td>R$ " . $valor . "</td>";
                                echo "<td>" . $regra . "</td>";
                                
                                // O BOTÃO DE EXCLUIR AQUI:
                                echo "<td style='text-align: center;'>";
                                echo "<a href='excluir_servico.php?id=" . $servico['id'] . "' style='display: inline-block; background-color: #ef4444; color: white; width: 30px; height: 30px; line-height: 30px; text-align: center; border-radius: 4px;' onclick='return confirm(\"Tem certeza que deseja apagar o serviço " . $servico['nome_servico'] . "?\")'><i class='fa-solid fa-trash'></i></a>";
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; padding: 30px;'>Nenhum serviço cadastrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div> 
    </div> 
</body>
</html>