<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// TRAVA DE SEGURANÇA: Se não for Administrador, é expulso de volta para o Painel!
if ($_SESSION['cargo_usuario'] != 'Administrador') {
    header("Location: painel.php");
    exit;
}

$resultado = $conexao->query("SELECT * FROM despesas ORDER BY data_despesa DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Despesas - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: block;">
    <div class="dashboard-container">
        
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
              <img src="marca.jpg.png" alt="Lavanderia Confort" style="max-width: 140px; height: auto;">
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
                <h2><a href="financeiro.php" style="color: #333; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Histórico de Despesas</a></h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>DATA</th><th>DESCRIÇÃO</th><th>CATEGORIA</th><th>VALOR</th><th style="text-align: center;">AÇÃO</th></tr>
                    </thead>
                    <tbody>
                        <?php while($d = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($d['data_despesa'])); ?></td>
                                <td><?php echo $d['descricao']; ?></td>
                                <td><span style="background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-size: 12px;"><?php echo $d['categoria']; ?></span></td>
                                <td style="color: #ef4444; font-weight: bold;">R$ <?php echo number_format($d['valor'], 2, ',', '.'); ?></td>
                                <td style="text-align: center;">
                                    <a href="excluir_despesa.php?id=<?php echo $d['id']; ?>" style="color: #aaa;" onclick="return confirm('Excluir este lançamento?')"><i class="fa-solid fa-trash-can"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>