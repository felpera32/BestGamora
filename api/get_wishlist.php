<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gamoraloja');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $clienteId = $_SESSION['idCliente'] ?? null;
    
    if (!$clienteId) {
        throw new Exception('ID do cliente não encontrado na sessão');
    }

    $stmt = $pdo->prepare("
        SELECT 
            p.idProduto,
            p.nome,
            p.preco,
            p.fabricante,
            p.imagemPrincipal,
            p.descricao,
            w.created_at as adicionado_em
        FROM wishlist w
        INNER JOIN produtos p ON w.idProduto = p.idProduto
        WHERE w.idCliente = ?
        ORDER BY w.created_at DESC
    ");
    
    $stmt->execute([$clienteId]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'games' => $games,
        'total' => count($games)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'games' => []
    ]);
}
?>