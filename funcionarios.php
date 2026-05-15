<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$resultado = $conexao->query("SELECT * FROM funcionarios ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Funcionários - Lavanderia</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-fundo { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); }
        .modal-caixa { background-color: #fff; margin: 5% auto; padding: 30px; border-radius: 8px; width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative; }
        .modal-fechar { color: #94a3b8; float: right; font-size: 24px; cursor: pointer; text-decoration: none; }
        
        .input-grupo { margin-bottom: 15px; }
        .input-grupo label { display: block; margin-bottom: 5px; color: #333; font-size: 14px; }
        .input-grupo input, .input-grupo select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; font-size: 14px; }
        
        .senha-container { position: relative; display: flex; align-items: center; }
        .senha-container input { padding-right: 40px; }
        .senha-container i { position: absolute; right: 12px; color: #94a3b8; cursor: pointer; }
        
        .botoes-acao { display: flex; gap: 10px; margin-top: 25px; }
        .btn-cancelar { flex: 1; padding: 12px; background: #e2e8f0; color: #0f172a; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-criar { flex: 1; padding: 12px; background: #475569; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body style="display: block;">
    <div class="dashboard-container">
        <?php $pagina_atual = 'funcionarios.php'; ?>
        <div class="sidebar">
            <div class="sidebar-logo"><img src="logo.png" alt="Lavanderia" style="max-width: 160px;"></div>
            <a href="painel.php" class="menu-item"><i class="fa-solid fa-house"></i> Tela Inicial</a>
            <a href="fila.php" class="menu-item"><i class="fa-solid fa-list-ol"></i> Fila de Produção</a>
            <a href="novo_pedido.php" class="menu-item"><i class="fa-solid fa-file-lines"></i> Novo Pedido</a>
            <a href="clientes.php" class="menu-item"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="servicos.php" class="menu-item"><i class="fa-solid fa-tag"></i> Serviços</a>
            <a href="funcionarios.php" class="menu-item ativo"><i class="fa-solid fa-id-card"></i> Funcionários</a>
            <a href="financeiro.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Financeiro</a>
            <div style="flex-grow: 1;"></div>
            <a href="logout.php" class="menu-item" style="color: #d32f2f;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <div class="main-content">
            <div class="top-bar"><h2>Gestão de Equipe</h2></div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Funcionários</h3>
                    <button onclick="abrirModal()" style="width: auto; padding: 8px 15px; background: #475569; border: none; cursor: pointer; color: white; border-radius: 4px; font-weight: bold;">
                        + Novo Funcionário
                    </button>
                </div>
                <table>
                    <thead><tr><th>NOME</th><th>CARGO</th><th>EDITAR PEDIDOS?</th><th style="text-align: center;">AÇÕES</th></tr></thead>
                    <tbody>
                        <?php while($f = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $f['nome']; ?></strong></td>
                                <td><?php echo $f['cargo']; ?></td>
                                <td><?php echo $f['pode_editar_pedidos']; ?></td>
                                <td style="text-align: center;">
                                    <button onclick='editarFuncionario(<?php echo json_encode($f); ?>)' style="background: none; border: none; color: #0284c7; cursor: pointer; margin-right: 10px;"><i class="fa-solid fa-pen"></i></button>
                                    
                                    <a href="excluir_funcionario.php?id=<?php echo $f['id']; ?>" style="color: #ef4444;" onclick="return confirm('Excluir funcionário?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalFun" class="modal-fundo">
        <div class="modal-caixa">
            <span class="modal-fechar" onclick="fecharModal()">&times;</span>
            <h2 id="tituloModal" style="color: #0f172a; margin-bottom: 5px; font-size: 20px;">Novo Funcionário</h2>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Preencha os dados abaixo.</p>
            
            <form action="salvar_funcionario.php" method="POST">
                <input type="hidden" name="id" id="funId"> <div class="input-grupo">
                    <label>Nome do Funcionário:</label>
                    <input type="text" name="nome" id="funNome" placeholder="Nome do funcionário" required>
                </div>
                
                <div class="input-grupo">
                    <label>Senha:</label>
                    <div class="senha-container">
                        <input type="password" name="senha" id="funSenha" placeholder="Senha (mínimo 4 caracteres)" minlength="4" required>
                        <i class="fa-solid fa-eye-slash" id="iconeSenha" onclick="alternarSenha()"></i>
                    </div>
                </div>

                <div class="input-grupo">
                    <label>Cargo:</label>
                    <select name="cargo" id="funCargo" required>
                        <option value="">Selecione um cargo</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Atendente">Atendente</option>
                        <option value="Lavador(a)">Lavador(a)</option>
                    </select>
                </div>

                <div class="input-grupo">
                    <label>Pode editar pedidos:</label>
                    <select name="pode_editar" id="funEditar">
                        <option value="Não">Não</option>
                        <option value="Sim">Sim</option>
                    </select>
                </div>

                <div class="botoes-acao">
                    <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-criar" id="btnSalvar">Criar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('funId').value = "";
            document.getElementById('tituloModal').innerText = "Novo Funcionário";
            document.getElementById('btnSalvar').innerText = "Criar";
            document.getElementById('modalFun').style.display = 'block';
        }

        function editarFuncionario(f) {
            document.getElementById('funId').value = f.id;
            document.getElementById('funNome').value = f.nome;
            document.getElementById('funSenha').value = f.senha;
            document.getElementById('funCargo').value = f.cargo;
            document.getElementById('funEditar').value = f.pode_editar_pedidos;
            
            document.getElementById('tituloModal').innerText = "Editar Funcionário";
            document.getElementById('btnSalvar').innerText = "Salvar Alterações";
            document.getElementById('modalFun').style.display = 'block';
        }

        function fecharModal() { document.getElementById('modalFun').style.display = 'none'; }
        
        function alternarSenha() {
            var campo = document.getElementById("funSenha");
            var icone = document.getElementById("iconeSenha");
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
    </script>
</body>
</html>