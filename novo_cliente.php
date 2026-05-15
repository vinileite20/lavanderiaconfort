<?php
// 1. A PROTEÇÃO DA PÁGINA
// Iniciamos a sessão e verificamos se a pessoa está logada (tem a pulseira)
session_start();
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: index.html"); // Se não estiver, chuta de volta pro login
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Cliente - Lavanderia</title>
    <!-- Usamos o mesmo visual que já criamos! -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="login-card" style="width: 450px;"> <!-- Deixei o cartão um pouco mais largo -->
        
        <div class="header">
            <i class="fa-solid fa-users logo-icon"></i>
            <h2>Novo Cliente</h2>
        </div>

        <!-- O FORMULÁRIO -->
        <!-- O action aponta para o arquivo que vai processar os dados -->
        <!-- O method="POST" envia os dados de forma escondida e segura -->
        <form action="salvar_cliente.php" method="POST">
            
            <div class="input-group">
                <label for="nome">Nome Completo:</label>
                <!-- A propriedade 'name' é a mais importante. É com ela que o PHP vai capturar o texto -->
                <input type="text" id="nome" name="nome_cliente" placeholder="Ex: João da Silva" required>
            </div>

            <div class="input-group">
                <label for="telefone">WhatsApp:</label>
                <input type="text" id="telefone" name="telefone_cliente" placeholder="Ex: 88 99999-9999" required>
            </div>

            <div class="input-group">
                <label for="endereco">Endereço (Opcional):</label>
                <!-- Não coloquei o 'required' aqui, pois decidimos que endereço é opcional -->
                <input type="text" id="endereco" name="endereco_cliente" placeholder="Ex: Rua das Flores, 123">
            </div>

            <button type="submit" class="btn-entrar">Salvar Cliente</button>
            
            <!-- Um link simples para voltar ao painel principal -->
            <div style="text-align: center; margin-top: 15px;">
                <a href="painel.php" style="color: #555; text-decoration: none; font-size: 14px;">Voltar ao Painel</a>
            </div>

        </form>

    </div>

</body>
</html>