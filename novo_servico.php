<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Serviço - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-card" style="width: 450px;">
        <div class="header">
            <i class="fa-solid fa-tag logo-icon" style="color: #1b3a57;"></i>
            <h2>Novo Serviço</h2>
        </div>

        <form action="salvar_servico.php" method="POST">
            <div class="input-group">
                <label>Nome do Serviço (Ex: Lavagem na Máquina, Tapete):</label>
                <input type="text" name="nome_servico" required>
            </div>

            <div class="input-group">
                <label>Preço Padrão (R$):</label>
                <input type="number" step="0.01" name="preco" required>
            </div>

            <!-- A SUA CAIXINHA DE REGRA DE NEGÓCIO -->
            <div class="input-group" style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #e1e1e1;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                    <input type="checkbox" name="pode_alterar" value="sim" style="width: 20px; height: 20px;">
                    <span style="font-size: 14px; color: #555;">Permitir alterar o valor deste serviço na hora de lançar o pedido.</span>
                </label>
            </div>

            <button type="submit" class="btn-entrar" style="margin-top: 15px;">Salvar Serviço</button>
            <div style="text-align: center; margin-top: 15px;">
                <a href="servicos.php" style="color: #555; text-decoration: none; font-size: 14px;">Cancelar e Voltar</a>
            </div>
        </form>
    </div>
</body>
</html>