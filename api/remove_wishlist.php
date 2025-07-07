<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

// Configuração do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gamoraloja');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('ID do produto não fornecido');
    }

    $produtoId = $input['id'];
    $clienteId = $_SESSION['idCliente'] ?? null;
    
    if (!$clienteId) {
        throw new Exception('ID do cliente não encontrado na sessão');
    }

    $checkStmt = $pdo->prepare("SELECT id FROM wishlist WHERE idCliente = ? AND idProduto = ?");
    $checkStmt->execute([$clienteId, $produtoId]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Item não encontrado na sua lista de desejos');
    }

    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE idCliente = ? AND idProduto = ?");
    $stmt->execute([$clienteId, $produtoId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Jogo removido da lista de desejos']);
    } else {
        throw new Exception('Erro ao remover o jogo da lista');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>