<?php
include "connect.php";

header('Content-Type: application/json');

// Check if connection was successful
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Erro na conexão com o banco de dados']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['userId'] ?? null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'ID do usuário não fornecido']);
        exit;
    }
    
    try {
        // Buscar nome do usuário
        $query_user = "SELECT nome FROM clientes WHERE idCliente = ?";
        $stmt_user = $conn->prepare($query_user);
        $stmt_user->bind_param("i", $userId);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        
        if ($user_result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Usuário não encontrado']);
            exit;
        }
        
        $user_data = $user_result->fetch_assoc();
        
        // Buscar jogos do usuário
        $query_games = "SELECT p.idProduto, p.nome, p.preco, p.imagemPrincipal 
                       FROM produtos p 
                       INNER JOIN itens_venda iv ON p.idProduto = iv.idProduto 
                       INNER JOIN vendas v ON iv.idVenda = v.idVenda 
                       WHERE v.idCliente = ?";
        
        $stmt_games = $conn->prepare($query_games);
        $stmt_games->bind_param("i", $userId);
        $stmt_games->execute();
        $games_result = $stmt_games->get_result();
        
        $games = [];
        while ($row = $games_result->fetch_assoc()) {
            // Converter preço para float
            $row['preco'] = (float) $row['preco'];
            
            // Processar caminho da imagem
            $imagem_path = $row['imagemPrincipal'];
            if (!empty($imagem_path)) {
                // Se não começar com http, adicionar caminho relativo
                if (!preg_match('/^https?:\/\//', $imagem_path)) {
                    $imagem_path = 'uploads/produtos/' . basename($imagem_path);
                }
                $row['imagemPrincipal'] = $imagem_path;
            }
            
            $games[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'userName' => $user_data['nome'],
            'games' => $games
        ]);
        
        // Close statements
        $stmt_user->close();
        $stmt_games->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erro no servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}
?>