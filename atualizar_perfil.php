<?php
session_start();
if (!isset($_SESSION['usuario_logado'])) { header("Location: index.html"); exit; }
require_once 'conexao.php';

$nome_antigo = $_SESSION['usuario_logado'];
$novo_nome = trim($_POST['nome_usuario']);
$senha_atual_digitada = $_POST['senha_atual'];
$nova_senha = $_POST['nova_senha'];
$confirma_senha = $_POST['confirma_senha'];

// 1. O usuário quer tentar trocar a senha?
if (!empty($senha_atual_digitada) || !empty($nova_senha)) {
    
    // Verifica se a nova senha foi digitada igual nas duas caixas
    if ($nova_senha !== $confirma_senha) {
        header("Location: perfil.php?msg=erro_senha");
        exit;
    }

    // Procura no banco se a senha atual que ele digitou está correta
    $sql_busca = "SELECT id, senha FROM usuarios WHERE usuario = '$nome_antigo'";
    $resultado = $conexao->query($sql_busca);
    
    if ($resultado->num_rows > 0) {
        $user = $resultado->fetch_assoc();
        
        // Compara a senha (supondo que esteja gravada como texto normal no seu banco)
        if ($senha_atual_digitada == $user['senha']) {
            // A senha atual está certa, então atualiza o nome e a senha nova
            $id = $user['id'];
            $sql_update = "UPDATE usuarios SET usuario = '$novo_nome', senha = '$nova_senha' WHERE id = '$id'";
            $conexao->query($sql_update);
            
            // Atualiza a memória do sistema com o novo nome
            $_SESSION['usuario_logado'] = $novo_nome;
            header("Location: perfil.php?msg=sucesso");
            exit;
        } else {
            // Senha atual incorreta
            header("Location: perfil.php?msg=erro_atual");
            exit;
        }
    }
} else {
    // 2. Se as caixas de senha estiverem vazias, o usuário só quer mudar o Nome
    $sql_update_nome = "UPDATE usuarios SET usuario = '$novo_nome' WHERE usuario = '$nome_antigo'";
    if ($conexao->query($sql_update_nome) === TRUE) {
        $_SESSION['usuario_logado'] = $novo_nome;
        header("Location: perfil.php?msg=sucesso");
        exit;
    }
}
?>