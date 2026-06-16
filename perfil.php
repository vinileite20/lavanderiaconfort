<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$ehAdmin = ($_SESSION['cargo_usuario'] == 'Administrador');
$nome_atual = $_SESSION['usuario_logado'];

// Se houver uma mensagem de sucesso ou erro vinda do arquivo que salva
$mensagem = isset($_GET['msg']) ? $_GET['msg'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ESTILOS DA TELA DE PERFIL BASEADOS NA SUA IMAGEM */
        .titulo-pagina { font-size: 24px; color: #111827; margin-bottom: 25px; }
        
        .card-config { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px; max-width: 800px; }
        .card-config h3 { font-size: 18px; color: #111827; margin-bottom: 5px; }
        .card-config p.subtitulo { color: #6b7280; font-size: 14px; margin-bottom: 25px; }
        
        .input-linha { margin-bottom: 20px; max-width: 500px; }
        .input-linha label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151; }
        .input-wrapper { position: relative; }
        .input-wrapper input { width: 100%; padding: 12px 40px 12px 15px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; outline: none; box-sizing: border-box; }
        .input-wrapper input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .input-wrapper i { position: absolute; right: 15px; top: 14px; color: #9ca3af; cursor: pointer; }
        .input-wrapper i:hover { color: #4b5563; }
        
        .btn-preto { background-color: #111827; color: white; padding: 10px 25px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .btn-preto:hover { background-color: #374151; }

        /* ÁREA DE PERIGO */
        .card-perigo { background: #fef2f2; border: 1px solid #fecaca; padding: 30px; border-radius: 8px; max-width: 800px; }
        .card-perigo h3 { color: #dc2626; font-size: 18px; margin-bottom: 10px; }
        .card-perigo p { color: #b91c1c; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
        .btn-vermelho { background-color: #e47575; color: white; padding: 10px 25px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; }
        .btn-vermelho:hover { background-color: #dc2626; }

        /* ALERTA DE SUCESSO/ERRO */
        .alerta { padding: 15px; border-radius: 6px; margin-bottom: 20px; max-width: 800px; font-weight: bold; }
        .alerta-sucesso { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alerta-erro { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body style="display: block;">

    <div class="dashboard-container">
        
        <div class="fundo-escuro-menu" id="fundoMenu" onclick="fecharMenuMobile()"></div>

        <?php $pagina_atual = 'perfil.php'; ?>
        <div class="sidebar" id="menuSidebar">
            <div class="sidebar-logo">
               <img src="marca.jpg.png" alt="Lavanderia Confort" style="max-width: 140px; height: auto;">
                <button class="btn-fechar-menu" onclick="fecharMenuMobile()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <a href="painel.php" class="menu-item"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
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
                <button class="btn-menu-mobile" onclick="abrirMenuMobile()"><i class="fa-solid fa-bars"></i></button>
                <h2>Configurações</h2>
            </div>

            <?php if($mensagem == 'sucesso'): ?>
                <div class="alerta alerta-sucesso"><i class="fa-solid fa-circle-check"></i> Perfil atualizado com sucesso!</div>
            <?php elseif($mensagem == 'erro_senha'): ?>
                <div class="alerta alerta-erro"><i class="fa-solid fa-triangle-exclamation"></i> As novas senhas não coincidem!</div>
            <?php elseif($mensagem == 'erro_atual'): ?>
                <div class="alerta alerta-erro"><i class="fa-solid fa-triangle-exclamation"></i> A senha atual digitada está incorreta!</div>
            <?php endif; ?>

            <div class="card-config">
                <h3>Informações do perfil</h3>
                <p class="subtitulo">Atualize seu nome ou senha aqui:</p>

                <form action="atualizar_perfil.php" method="POST">
                    <div class="input-linha">
                        <label>Nome:</label>
                        <div class="input-wrapper">
                            <input type="text" name="nome_usuario" value="<?php echo $nome_atual; ?>" required>
                        </div>
                    </div>

                    <div class="input-linha">
                        <label>Senha Atual:</label>
                        <div class="input-wrapper">
                            <input type="password" name="senha_atual" id="senhaAtual" placeholder="Digite sua senha atual">
                            <i class="fa-solid fa-eye-slash" onclick="mostrarSenha('senhaAtual', this)"></i>
                        </div>
                    </div>

                    <div class="input-linha">
                        <label>Nova Senha:</label>
                        <div class="input-wrapper">
                            <input type="password" name="nova_senha" id="novaSenha" placeholder="Digite a nova senha">
                            <i class="fa-solid fa-eye-slash" onclick="mostrarSenha('novaSenha', this)"></i>
                        </div>
                    </div>

                    <div class="input-linha">
                        <label>Confirme a Nova Senha:</label>
                        <div class="input-wrapper">
                            <input type="password" name="confirma_senha" id="confirmaSenha" placeholder="Confirme a nova senha">
                            <i class="fa-solid fa-eye-slash" onclick="mostrarSenha('confirmaSenha', this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-preto">Salvar</button>
                </form>
            </div>

            <div style="margin-bottom: 10px;">
                <h3 style="font-size: 18px; color: #111827;">Excluir sua conta</h3>
            </div>
            
            <div class="card-perigo">
                <h3>Perigo!</h3>
                <p>Excluir sua conta e todos os dados dela permanentemente. Por favor, prossiga com cautela, pois essa ação não pode ser desfeita.</p>
                <button type="button" class="btn-vermelho" onclick="confirmarExclusao()">Excluir sua conta</button>
            </div>

        </div>
    </div>

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

        // JS PARA OS OLHINHOS DA SENHA FUNCIONAREM
        function mostrarSenha(idCampo, icone) {
            var campo = document.getElementById(idCampo);
            if (campo.type === "password") {
                campo.type = "text";
                icone.classList.remove("fa-eye-slash");
                icone.classList.add("fa-eye");
            } else {
                campo.type = "password";
                icone.classList.remove("fa-eye");
                icone.classList.add("fa-eye-slash");
            }
        }

        // JS PARA O BOTÃO DE EXCLUIR CONTA
        function confirmarExclusao() {
            alert("Atenção: Por motivos de segurança, a exclusão de contas deve ser solicitada ao Administrador do sistema para não perder o histórico de pedidos.");
        }
    </script>
</body>
</html>