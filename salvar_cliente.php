<?php
// 1. A PROTEÇÃO DA PÁGINA (A "Pulseira VIP")
// session_start() liga o verificador de memória.
// O 'if' pergunta: "NÃO existe (!) a variável 'usuario_logado' na sessão?". 
// Se for verdade (ou seja, algum cliente ou hacker tentou acessar o arquivo direto pelo link), 
// o comando 'header' chuta a pessoa de volta para a tela de login.
session_start();
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: index.html");
    exit;
}

// 2. LIGANDO O CABO DE ENERGIA DO BANCO DE DADOS
// Chamamos o nosso arquivo de conexão para abrir a porta do MySQL. Sem ele, o PHP não tem acesso ao banco.
require_once 'conexao.php';


// 3. RECEBENDO OS DADOS QUE O FUNCIONÁRIO DIGITOU NA TELA
// A variável $_POST é a caixa de correio do PHP. 
// Ela pega os dados pelos "names" exatos que colocamos no formulário HTML (nome_cliente, telefone_cliente, etc).
// O sinal de igual (=) guarda essas informações dentro das nossas variáveis do PHP (que começam com $).
$nomeDigitado = $_POST['nome_cliente'];
$telefoneDigitado = $_POST['telefone_cliente'];
$enderecoDigitado = $_POST['endereco_cliente'];


// 4. A ORDEM PARA O BANCO DE DADOS (O Comando SQL)
// Aqui nós montamos a instrução. 
// INSERT INTO clientes: "Insira dentro da tabela chamada clientes"
// (nome, telefone, endereco): "Nas gavetas de nome, telefone e endereço..."
// VALUES (...): "...os valores que estão guardados nas variáveis que acabei de criar acima."
$sql = "INSERT INTO clientes (nome, telefone, endereco) VALUES ('$nomeDigitado', '$telefoneDigitado', '$enderecoDigitado')";


// 5. EXECUTANDO A ORDEM E VERIFICANDO SE DEU CERTO
// O comando ->query($sql) pega a instrução de cima e aperta "Enter" lá dentro do MySQL.
// O 'if' avalia se a resposta do MySQL foi TRUE (Verdadeiro/Deu certo).
if ($conexao->query($sql) === TRUE) {
    
    // Se deu certo, mostramos uma mensagem de sucesso na tela e um botão para o funcionário 
    // voltar para a tela principal (Painel) e continuar trabalhando.
    echo "<div style='font-family: Arial; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #4a90e2;'>Cliente salvo com sucesso no sistema!</h1>";
    echo "<br>";
    echo "<a href='painel.php' style='padding: 10px 20px; background-color: #333; color: white; text-decoration: none; border-radius: 5px;'>Voltar ao Painel</a>";
    echo "</div>";

} else {
    // Se o banco de dados der algum erro (exemplo: a tabela clientes não existe, ou o servidor caiu),
    // ele mostra a mensagem de erro do próprio banco ($conexao->error) para nos ajudar a consertar.
    echo "Erro ao cadastrar: " . $conexao->error;
}
?>