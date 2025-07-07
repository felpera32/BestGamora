<?php
session_start();
include 'connect.php'; 

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'], $data['name'], $data['price'], $data['quantity'])) {
    $idProduto = $data['id'];
    $nomeProduto = $data['name'];
    $precoProduto = $data['price'];
    $quantidadeProduto = $data['quantity'];

    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    if (isset($_SESSION['carrinho'][$idProduto])) {
        $_SESSION['carrinho'][$idProduto]['quantidade'] += $quantidadeProduto;
    } else {
        $_SESSION['carrinho'][$idProduto] = [
            'nome' => $nomeProduto,
            'preco' => $precoProduto,
            'quantidade' => $quantidadeProduto
        ];
    }

    echo json_encode(['success' => true, 'message' => 'Produto adicionado ao carrinho!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados do produto inválidos.']);
}
?>