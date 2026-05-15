<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lançar Despesa - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-card" style="width: 450px; border-top: 5px solid #ef4444;">
        <div class="header">
            <i class="fa-solid fa-file-invoice-dollar logo-icon" style="color: #ef4444;"></i>
            <h2>Registrar Despesa</h2>
        </div>

        <form action="salvar_despesa.php" method="POST">
            <div class="input-group">
                <label>Descrição (Ex: Compra de Sabão, Aluguel):</label>
                <input type="text" name="descricao" required>
            </div>

            <div class="input-group">
                <label>Categoria:</label>
                <select name="categoria" required style="width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none;">
                    <option value="Insumos">Insumos (Sabão, Amaciante, etc)</option>
                    <option value="Água">Conta de Água</option>
                    <option value="Energia">Conta de Energia</option>
                    <option value="Aluguel">Aluguel</option>
                    <option value="Salário">Salário / Funcionários</option>
                    <option value="Manutenção">Manutenção de Máquinas</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>

            <div class="input-group">
                <label>Data da Despesa:</label>
                <!-- Já vem preenchido com o dia de hoje automaticamente -->
                <input type="date" name="data_despesa" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="input-group">
                <label>Valor (R$):</label>
                <input type="text" name="valor" placeholder="0,00" oninput="formatarMoeda(this)" required>
            </div>

            <button type="submit" class="btn-entrar" style="margin-top: 15px; background: #ef4444;">Salvar Despesa</button>
            <div style="text-align: center; margin-top: 15px;">
                <a href="financeiro.php" style="color: #555; text-decoration: none; font-size: 14px;">Cancelar e Voltar</a>
            </div>
        </form>
    </div>

    <!-- MÁSCARA DA CAIXA REGISTRADORA -->
    <script>
    function formatarMoeda(campo) {
        var valor = campo.value.replace(/\D/g, ''); 
        if (valor === '') { campo.value = ''; return; }
        valor = (parseInt(valor) / 100).toFixed(2) + '';
        valor = valor.replace('.', ',');
        campo.value = valor;
    }
    </script>
</body>
</html>