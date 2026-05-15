<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$sql = "SELECT * FROM clientes ORDER BY nome ASC";
$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Clientes - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- ESTILOS DA JANELA FLUTUANTE -->
    <style>
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-caixa { background-color: #fff; margin: 5% auto; padding: 25px; border-radius: 10px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; border-top: 5px solid #0284c7; }
        .modal-fechar { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; margin-top: -10px; }
        .modal-fechar:hover { color: #333; }
    </style>
</head>
<body style="display: block;">
    <div class="dashboard-container">
        <!-- MENU LATERAL -->
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
                <h2>Gerenciar Clientes</h2>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Lista de Clientes</h3>
                    <!-- BOTÃO MÁGICO QUE ABRE O MODAL -->
                    <button onclick="abrirModal()" class="btn-entrar" style="width: auto; padding: 8px 15px; font-size: 14px; background: #0284c7; border: none; cursor: pointer; color: white;">
                        <i class="fa-solid fa-plus"></i> Novo Cliente
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr><th>NOME</th><th>TELEFONE</th><th style="text-align: center;">AÇÕES</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultado->num_rows > 0) {
                            while($cliente = $resultado->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $cliente['nome'] . "</td>";
                                echo "<td>" . $cliente['telefone'] . "</td>";
                                echo "<td style='text-align: center;'>";
                                echo "<a href='excluir_cliente.php?id=" . $cliente['id'] . "' style='display: inline-block; background-color: #ef4444; color: white; width: 30px; height: 30px; line-height: 30px; text-align: center; border-radius: 4px;' onclick='return confirm(\"Tem certeza que deseja apagar este cliente?\")'><i class='fa-solid fa-trash'></i></a>";
                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align: center; padding: 30px;'>Nenhum cliente cadastrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div> 
    </div> 

    <!-- JANELA FLUTUANTE DE CLIENTE -->
    <div id="janelaCadastro" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            <h2 style="color: #0284c7; margin-bottom: 20px;"><i class="fa-solid fa-user-plus"></i> Novo Cliente</h2>
            
            <form action="salvar_cliente.php" method="POST">
                <div class="input-group">
                    <label>Nome Completo:</label>
                    <input type="text" name="nome" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div class="input-group" style="margin-top: 10px;">
                    <label>Telefone (WhatsApp):</label>
                    <input type="text" name="telefone" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <button type="submit" style="width: 100%; margin-top: 20px; background: #0284c7; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Salvar Cliente</button>
            </form>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        function abrirModal() { document.getElementById('janelaCadastro').style.display = 'block'; }
        function fecharModal() { document.getElementById('janelaCadastro').style.display = 'none'; }
    </script>
</body>
</html>