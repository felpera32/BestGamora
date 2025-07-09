<?php
header('Content-Type: application/json');
include 'connect.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$current_user_id = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offered_game_id = $_POST['offered_game_id'] ?? '';
    $user_game_id = $_POST['user_game_id'] ?? '';
    $proposer_name = $_POST['proposer_name'] ?? '';
    
    if (empty($offered_game_id) || empty($user_game_id)) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
        exit;
    }
    
    $sql_check_games = "SELECT idProduto, nome, preco FROM produtos WHERE idProduto IN (?, ?) AND categoria = 'Jogos' AND status = 'Disponível'";
    $stmt = $conn->prepare($sql_check_games);
    $stmt->bind_param("ii", $offered_game_id, $user_game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows != 2) {
        echo json_encode(['success' => false, 'message' => 'Jogos não encontrados ou indisponíveis']);
        exit;
    }
    
    $games = [];
    while ($row = $result->fetch_assoc()) {
        $games[$row['idProduto']] = $row;
    }
    
    $offered_game = $games[$offered_game_id];
    $user_game = $games[$user_game_id];
    
    $min_price = $offered_game['preco'] * 0.8;
    $max_price = $offered_game['preco'] * 1.2;
    
    if ($user_game['preco'] < $min_price || $user_game['preco'] > $max_price) {
        echo json_encode([
            'success' => false, 
            'message' => 'Troca não permitida: valores incompatíveis',
            'details' => [
                'offered_price' => $offered_game['preco'],
                'user_price' => $user_game['preco'],
                'min_allowed' => $min_price,
                'max_allowed' => $max_price
            ]
        ]);
        exit;
    }
    
    try {
        $conn->begin_transaction();
        
        $sql_insert_trade = "INSERT INTO trocas (idCliente, idProdutoUsado, idProdutoNovo, motivoTroca, statusTroca) VALUES (?, ?, ?, ?, 'Concluída')";
        $motivo_troca = "Troca simulada com " . $proposer_name . " - " . $offered_game['nome'] . " por " . $user_game['nome'];
        
        $stmt_trade = $conn->prepare($sql_insert_trade);
        $stmt_trade->bind_param("iius", $current_user_id, $user_game_id, $offered_game_id, $motivo_troca);
        $stmt_trade->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Troca realizada com sucesso!',
            'trade_details' => [
                'offered_game' => $offered_game['nome'],
                'user_game' => $user_game['nome'],
                'proposer' => $proposer_name
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro ao processar troca: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
