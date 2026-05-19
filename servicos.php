<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// Trava inteligente: verifica se quem está logado é Administrador
$ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');

$sql = "SELECT * FROM servicos ORDER BY nome_servico ASC";
$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-caixa { background-color: #fff; margin: 5% auto; padding: 25px; border-radius: 10px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; border-top: 5px solid #16a34a; }
        .modal-fechar { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; margin-top: -10px; }
        .modal-fechar:hover { color: #333; }
    </style>
</head>
<body style="display: block;">
    <div class="dashboard-container">
        <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
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
                <h2>Catálogo de Serviços</h2>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Serviços e Preços</h3>
                    <?php if ($ehAdmin): ?>
                        <button onclick="abrirModal()" class="btn-entrar" style="width: auto; padding: 8px 15px; font-size: 14px; background: #16a34a; border: none; cursor: pointer; color: white;">
                            <i class="fa-solid fa-plus"></i> Novo Serviço
                        </button>
                    <?php endif; ?>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>NOME DO SERVIÇO</th>
                            <th>PREÇO PADRÃO</th>
                            <th>REGRA DE VALOR</th>
                            <?php if ($ehAdmin): ?><th style="text-align: center;">AÇÕES</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultado->num_rows > 0) {
                            while($servico = $resultado->fetch_assoc()) {
                                $valor = number_format($servico['preco'], 2, ',', '.');
                                $regra = ($servico['preco_fixo'] == 1) ? "<span style='color: #16a34a; font-weight: bold;'><i class='fa-solid fa-lock'></i> Valor Fixo</span>" : "<span style='color: #f59e0b; font-weight: bold;'><i class='fa-solid fa-pen-to-square'></i> Pode Alterar</span>";

                                echo "<tr>";
                                echo "<td>" . $servico['nome_servico'] . "</td>";
                                echo "<td>R$ " . $valor . "</td>";
                                echo "<td>" . $regra . "</td>";
                                
                                // SÓ EXIBE O BOTÃO DE EXCLUIR NA LINHA SE FOR ADM
                                if ($ehAdmin) {
                                    echo "<td style='text-align: center;'>";
                                    echo "<a href='excluir_servico.php?id=" . $servico['id'] . "' style='display: inline-block; background-color: #ef4444; color: white; width: 30px; height: 30px; line-height: 30px; text-align: center; border-radius: 4px;' onclick='return confirm(\"Tem certeza que deseja apagar o serviço " . $servico['nome_servico'] . "?\")'><i class='fa-solid fa-trash'></i></a>";
                                    echo "</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            $colunas_total = $ehAdmin ? 4 : 3;
                            echo "<tr><td colspan='{$colunas_total}' style='text-align: center; padding: 30px;'>Nenhum serviço cadastrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div> 
    </div> 

    <?php if ($ehAdmin): ?>
    <div id="janelaServico" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            <h2 style="color: #16a34a; margin-bottom: 20px;"><i class="fa-solid fa-tag"></i> Novo Serviço</h2>
            
            <form action="salvar_servico.php" method="POST">
                <div class="input-group">
                    <label>Nome do Serviço (Ex: Lavagem, Tapete):</label>
                    <input type="text" name="nome_servico" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div class="input-group" style="margin-top: 10px;">
                    <label>Preço Padrão (R$):</label>
                    <input type="number" step="0.01" name="preco" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                
                <div class="input-group" style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #e1e1e1; margin-top: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                        <input type="checkbox" name="pode_alterar" value="sim" style="width: 20px; height: 20px;">
                        <span style="font-size: 14px; color: #555;">Permitir alterar o valor na hora do pedido.</span>
                    </label>
                </div>

                <button type="submit" style="width: 100%; margin-top: 20px; background: #16a34a; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Salvar Serviço</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

  <script>
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