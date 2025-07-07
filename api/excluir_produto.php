<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header("Location: login.php");
    exit();
}

// Verificar se o ID do produto foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['erro'] = "ID de produto inválido.";
    header("Location: meus_produtos.php");
    exit();
}

$idProduto = intval($_GET['id']);

// Conectar ao banco de dados
$servername = "localhost";
$username = "root"; // Substitua pelo seu usuário do banco
$password = ""; // Substitua pela sua senha do banco
$dbname = "gamora"; // Substitua pelo nome do seu banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o produto pertence ao usuário logado
$sql = "SELECT * FROM produtos WHERE idProduto = ? AND fabricante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $idProduto, $_SESSION['usuario']['nome']); // Assumindo que o nome do usuário está em $_SESSION['usuario']['nome']
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['erro'] = "Produto não encontrado ou você não tem permissão para excluí-lo.";
    header("Location: meus_produtos.php");
    exit();
}

// Obter dados do produto para excluir arquivos associados
$produto = $result->fetch_assoc();
$imagemPrincipal = $produto['imagemPrincipal'];

// Excluir o produto
$sql = "DELETE FROM produtos WHERE idProduto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idProduto);

if ($stmt->execute()) {
    // Excluir a imagem principal se não for a imagem padrão
    if ($imagemPrincipal !== "default_game.jpg" && file_exists($imagemPrincipal)) {
        unlink($imagemPrincipal);
    }
    
    // Aqui você também poderia excluir screenshots associados se estiverem em uma tabela separada
    
    $_SESSION['mensagem'] = "Jogo excluído com sucesso!";
} else {
    $_SESSION['erro'] = "Erro ao excluir jogo: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirecionar de volta para a página de produtos
header("Location: meus_produtos.php");
exit();
?>