<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

// Configuração do banco de dados
define('DB_HOST', 'mysql-gamora.alwaysdata.net');
define('DB_USER', 'gamora');
define('DB_PASS', 'trabalho6969*');
define('DB_NAME', 'gamora_loja');

try {
    // Conectar ao banco de dados
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }

    // Log para debug
    error_log('Dados recebidos: ' . print_r($input, true));

    $produtoId = $input['id'] ?? null;
    $clienteId = $_SESSION['idCliente'] ?? null;
    
    if (!$produtoId) {
        throw new Exception('ID do produto não fornecido');
    }
    
    if (!$clienteId) {
        throw new Exception('ID do cliente não encontrado na sessão');
    }

    // Verificar se o produto existe
    $checkProduct = $pdo->prepare("SELECT idProduto FROM produtos WHERE idProduto = ?");
    $checkProduct->execute([$produtoId]);
    
    if (!$checkProduct->fetch()) {
        throw new Exception('Produto não encontrado');
    }

    // Verificar se o jogo já está na lista de desejos
    $checkStmt = $pdo->prepare("SELECT id FROM wishlist WHERE idCliente = ? AND idProduto = ?");
    $checkStmt->execute([$clienteId, $produtoId]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Jogo já está na lista de desejos']);
        exit;
    }

    // Inserir na lista de desejos
    $stmt = $pdo->prepare("INSERT INTO wishlist (idCliente, idProduto, created_at) VALUES (?, ?, NOW())");
    $result = $stmt->execute([$clienteId, $produtoId]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Jogo adicionado à lista de desejos com sucesso']);
    } else {
        throw new Exception('Erro ao inserir na lista de desejos');
    }

} catch (Exception $e) {
    error_log('Erro em add_wishlist.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>