<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$resultado = $conexao->query("SELECT * FROM despesas ORDER BY data_despesa DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Despesas</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: block;">
    <div class="dashboard-container">
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