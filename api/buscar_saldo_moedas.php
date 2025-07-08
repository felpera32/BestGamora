<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não encontrado']);
    exit;
}

include 'connect.php';

try {
    $idCliente = $_SESSION['id_usuario'];
    
    // Buscar saldo atual de moedas
    $sql = "SELECT moedas FROM clientes WHERE idCliente = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $idCliente);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $saldoAtual = (int)$row['moedas'];
        
        // Atualizar a sessão com o saldo atual
        $_SESSION['user_coins'] = $saldoAtual;
        
        echo json_encode([
            'success' => true,
            'saldo' => $saldoAtual,
            'message' => 'Saldo atualizado com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Cliente não encontrado'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Erro ao buscar saldo de moedas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>