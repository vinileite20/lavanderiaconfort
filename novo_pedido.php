<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

// Carregar clientes
$clientes = $conexao->query("SELECT id, nome, telefone FROM clientes ORDER BY nome ASC");

// Carregar serviços para o JavaScript usar
$res_servicos = $conexao->query("SELECT id, nome_servico, preco FROM servicos ORDER BY nome_servico ASC");
$lista_servicos = [];
while($s = $res_servicos->fetch_assoc()) {
    $lista_servicos[] = $s;
}
$json_servicos = json_encode($lista_servicos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pedido - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .linha-servico { display: flex; gap: 15px; margin-bottom: 15px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; flex-wrap: wrap; }
        .linha-servico select { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 5px; outline: none; font-size: 15px; min-width: 200px; box-sizing: border-box; }
        .linha-servico input { width: 100px; padding: 12px; border: 1px solid #ccc; border-radius: 5px; text-align: center; outline: none; font-size: 15px; box-sizing: border-box; }
        .btn-remover { color: #ef4444; cursor: pointer; font-size: 20px; border: none; background: none; padding: 5px; }
        .resumo-totais { border-top: 2px solid #e2e8f0; padding-top: 20px; margin-top: 20px; font-weight: bold; font-size: 16px; color: #333; }
        .linha-total { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .input-group select, .input-group input, .input-group textarea { box-sizing: border-box; }
    </style>
</head>
<body style="display: block;">
    <div class="dashboard-container">
        
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>

        <?php 
            $pagina_atual = 'novo_pedido.php'; 
            $ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
        ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
             <img src="marca.jpg.png" alt="Lavanderia Confort" style="max-width: 140px; height: auto;">
                <button class="btn-fechar-menu" onclick="fecharMenuMobile()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <a href="painel.php" class="menu-item"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item ativo"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
            <a href="clientes.php" class="menu-item"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="servicos.php" class="menu-item"><i class="fa-solid fa-tag"></i> Serviços</a>
            
            <?php if ($ehAdmin): ?>
                <a href="funcionarios.php" class="menu-item"><i class="fa-solid fa-id-card"></i> Funcionários</a>
                <a href="financeiro.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Financeiro</a>
            <?php endif; ?>
            
            <div style="flex-grow: 1;"></div>
    
    <a href="perfil.php" class="menu-item" style="border-top: 1px solid #e2e8f0;"><i class="fa-solid fa-user-gear"></i> Meu Perfil</a>
    
    <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
</div>

        <div class="main-content">
            <div class="top-bar">
                <button class="btn-menu-mobile" onclick="abrirMenuMobile()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2>Novo Pedido</h2>
            </div>

            <div class="table-container" style="padding: 20px; margin-top: 20px;">
                <form action="salvar_pedido.php" method="POST">
                    
                    <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px;">
                        <div class="input-group">
                            <label>Funcionário Responsável:</label>
                            <input type="text" name="funcionario" value="<?php echo $_SESSION['usuario_logado']; ?>" readonly style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; background: #f1f5f9; color: #666; outline: none;">
                        </div>

                        <div class="input-group">
                            <div style="display: flex; justify-content: space-between;">
                                <label>Cliente:</label>
                                <a href="clientes.php" style="color: #0284c7; text-decoration: none; font-size: 14px; font-weight: bold;">+ Novo Cliente</a>
                            </div>
                            <select name="id_cliente" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; outline: none; font-size: 15px;">
                                <option value="">Selecione o cliente...</option>
                                <?php 
                                while($cli = $clientes->fetch_assoc()) { 
                                    $tel = preg_replace("/\D/", "", $cli['telefone']);
                                    if(strlen($tel) == 11) {
                                        $telFormatado = sprintf("(%s) %s-%s", substr($tel, 0, 2), substr($tel, 2, 5), substr($tel, 7));
                                    } elseif(strlen($tel) == 10) {
                                        $telFormatado = sprintf("(%s) %s-%s", substr($tel, 0, 2), substr($tel, 2, 4), substr($tel, 6));
                                    } else {
                                        $telFormatado = $cli['telefone'];
                                    }
                                    echo "<option value='".$cli['id']."'>".$cli['nome']." - ".$telFormatado."</option>"; 
                                } 
                                ?>
                            </select>
                        </div>
                    </div>

                    <label style="font-weight: bold; display: block; margin-bottom: 15px; font-size: 16px; border-bottom: 2px solid #0284c7; padding-bottom: 5px;">Serviços do Pedido</label>
                    <div id="lista-servicos">
                        </div>
                    
                    <button type="button" onclick="adicionarLinha()" style="width: 100%; padding: 10px 20px; background: #f1f5f9; color: #0284c7; border: 2px dashed #0284c7; border-radius: 5px; cursor: pointer; font-weight: bold; margin-bottom: 30px;">
                        <i class="fa-solid fa-plus"></i> Adicionar Outro Serviço
                    </button>

                    <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px;">
                        <div class="input-group">
                            <label>Método de Pagamento:</label>
                            <select name="metodo_pagamento" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; outline: none; font-size: 15px;">
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Pix">Pix</option>
                                <option value="Cartão">Cartão</option>
                                <option value="Pendente">Ainda não pagou (Pendente)</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Desconto (R$):</label>
                            <input type="text" name="desconto" id="inputDesconto" value="0,00" oninput="formatarMoeda(this); calcularTotal();" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; text-align: right; outline: none; font-size: 15px;">
                        </div>
                    </div>

                    <div class="input-group" style="margin-bottom: 20px;">
                        <label>Observação / Preferências (Opcional):</label>
                        <textarea name="obs" rows="3" placeholder="Ex: Lavar separado..." style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; outline: none; font-size: 15px;"></textarea>
                    </div>

                    <div class="resumo-totais">
                        <div class="linha-total"><span>Total dos Serviços:</span><span id="txtTotalServicos">R$ 0,00</span></div>
                        <div class="linha-total"><span>Desconto:</span><span id="txtDesconto">- R$ 0,00</span></div>
                        <div class="linha-total" style="font-size: 20px; margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 15px;">
                            <span>TOTAL FINAL:</span><span id="txtTotalFinal">R$ 0,00</span>
                        </div>
                    </div>

                    <input type="hidden" name="valor_total_escondido" id="valorTotalEscondido" value="0">

                    <button type="submit" class="btn-entrar" style="width: 100%; margin-top: 25px; padding: 15px; font-size: 18px;">
                        <i class="fa-solid fa-check"></i> Criar Pedido
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const servicosDB = <?php echo $json_servicos; ?>;
        
        function formatarMoeda(campo) {
            var valor = campo.value.replace(/\D/g, ''); 
            if (valor === '') { campo.value = '0,00'; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace('.', ',');
            campo.value = valor;
        }

        function adicionarLinha() {
            let container = document.getElementById('lista-servicos');
            let index = container.children.length;
            
            let opcoes = `<option value="">Selecione o serviço</option>`;
            servicosDB.forEach(s => { 
                let precoFmt = parseFloat(s.preco).toFixed(2).replace('.', ',');
                opcoes += `<option value="${s.nome_servico}" data-preco="${s.preco}">${s.nome_servico} (R$ ${precoFmt})</option>`; 
            });

            let html = `
                <div class="linha-servico" id="linha_${index}">
                    <select name="servicos[]" onchange="calcularTotal()" required>${opcoes}</select>
                    <input type="number" name="quantidades[]" value="1" min="1" onchange="calcularTotal()">
                    <input type="hidden" name="precos_unitarios[]" class="preco-escondido" value="0">
                    <button type="button" class="btn-remover" onclick="removerLinha(${index})"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removerLinha(id) {
            document.getElementById('linha_' + id).remove();
            calcularTotal();
        }

        function calcularTotal() {
            let linhas = document.querySelectorAll('.linha-servico');
            let totalServicos = 0;

            linhas.forEach(linha => {
                let select = linha.querySelector('select');
                let qtd = parseInt(linha.querySelector('input[type="number"]').value) || 0;
                let precoHidden = linha.querySelector('.preco-escondido');
                
                let precoItem = 0;
                if(select.selectedIndex > 0) {
                    precoItem = parseFloat(select.options[select.selectedIndex].getAttribute('data-preco'));
                }
                
                precoHidden.value = precoItem;
                totalServicos += (precoItem * qtd);
            });

            let descontoStr = document.getElementById('inputDesconto').value.replace('.', '').replace(',', '.');
            let descontoFormatado = parseFloat(descontoStr);
            if(isNaN(descontoFormatado)) descontoFormatado = 0;

            let totalFinal = totalServicos - descontoFormatado;
            if(totalFinal < 0) totalFinal = 0;

            document.getElementById('txtTotalServicos').innerText = "R$ " + totalServicos.toFixed(2).replace('.', ',');
            document.getElementById('txtDesconto').innerText = "- R$ " + descontoFormatado.toFixed(2).replace('.', ',');
            document.getElementById('txtTotalFinal').innerText = "R$ " + totalFinal.toFixed(2).replace('.', ',');
            
            document.getElementById('valorTotalEscondido').value = totalFinal;
        }

        window.onload = adicionarLinha;

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