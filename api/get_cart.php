<?php
session_start();
require_once 'conn.php';

if (!isset($_SESSION['cliente_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado']);
    exit;
}

$clienteId = $_SESSION['cliente_id'];

try {
    $sql = "SELECT 
                p.id as produto_id, 
                p.nome, 
                p.imagem, 
                iv.quantidade, 
                p.preco, 
                (iv.quantidade * p.preco) as total 
            FROM itensvenda iv
            JOIN produtos p ON iv.produto_id = p.id
            WHERE iv.cliente_id = ? AND iv.status = 'carrinho'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clienteId);
    $stmt->execute();
    $result = $stmt->get_result();

    $itens = [];
    $totalCarrinho = 0;
    while ($item = $result->fetch_assoc()) {
        $itens[] = $item;
        $totalCarrinho += $item['total'];
    }

    echo json_encode([
        'sucesso' => true,
        'itens' => $itens,
        'total' => number_format($totalCarrinho, 2, ',', '.')
    ]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
}
?>