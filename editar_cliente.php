<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// Pegamos o ID que veio no link e buscamos a ficha desse cliente no banco
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $resultado = $conexao->query("SELECT * FROM clientes WHERE id = '$id'");
    
    // Se encontrou o cliente, guarda os dados na variável $cliente
    if ($resultado->num_rows > 0) {
        $cliente = $resultado->fetch_assoc();
    } else {
        header("Location: clientes.php"); // Se não achar (cliente não existe), volta pra lista
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="login-card" style="width: 450px;">
        
        <div class="header">
            <i class="fa-solid fa-user-pen logo-icon" style="color: #3b82f6;"></i>
            <h2>Editar Cliente</h2>
        </div>

        <form action="atualizar_cliente.php" method="POST">
            
            <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
            
            <div class="input-group">
                <label>Nome Completo:</label>
                <input type="text" name="nome_cliente" value="<?php echo $cliente['nome']; ?>" required>
            </div>

            <div class="input-group">
                <label>WhatsApp:</label>
                <input type="text" name="telefone_cliente" value="<?php echo $cliente['telefone']; ?>" required>
            </div>

            <div class="input-group">
                <label>Endereço:</label>
                <input type="text" name="endereco_cliente" value="<?php echo $cliente['endereco']; ?>">
            </div>

            <button type="submit" class="btn-entrar" style="background-color: #3b82f6;">Salvar Alterações</button>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="clientes.php" style="color: #555; text-decoration: none; font-size: 14px;">Cancelar e Voltar</a>
            </div>
        </form>

    </div>

</body>
</html>