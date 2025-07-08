<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    $_SESSION['erro_compra'] = "Você precisa estar logado para finalizar a compra.";
    header('Location: login.php');
    exit;
}

$idCliente = 0;
if (isset($_POST['idCliente']) && is_numeric($_POST['idCliente']) && $_POST['idCliente'] > 0) {
    $idCliente = (int)$_POST['idCliente'];
} elseif (isset($_SESSION['id_usuario']) && is_numeric($_SESSION['id_usuario']) && $_SESSION['id_usuario'] > 0) {
    $idCliente = (int)$_SESSION['id_usuario'];
}

if ($idCliente <= 0) {
    error_log("ID do cliente inválido - POST: " . (isset($_POST['idCliente']) ? $_POST['idCliente'] : 'não definido') . 
              " SESSION: " . (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 'não definido'));
    $_SESSION['erro_compra'] = "Erro: usuário não identificado. Faça login novamente.";
    header('Location: login.php');
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'process_coins_payment') {
    header('Content-Type: application/json');
    
    $coinsAmount = intval($_POST['coins_amount']);
    $cartTotal = floatval($_POST['cart_total']);
    
    try {
        // Buscar saldo atual de moedas do cliente
        $sqlMoedas = "SELECT moedas FROM clientes WHERE idCliente = ?";
        $stmtMoedas = $conn->prepare($sqlMoedas);
        $stmtMoedas->bind_param("i", $idCliente);
        $stmtMoedas->execute();
        $resultMoedas = $stmtMoedas->get_result();
        
        if ($resultMoedas->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
            exit;
        }
        
        $clienteData = $resultMoedas->fetch_assoc();
        $moedasAtuais = $clienteData['moedas'];
        
        if ($moedasAtuais < $coinsAmount) {
            echo json_encode([
                'success' => false, 
                'message' => 'Saldo insuficiente',
                'current_balance' => $moedasAtuais,
                'required' => $coinsAmount
            ]);
            exit;
        }
        
        $conn->begin_transaction();
        
        try {
            $novoSaldoMoedas = $moedasAtuais - $coinsAmount;
            $sqlUpdateMoedas = "UPDATE clientes SET moedas = ? WHERE idCliente = ?";
            $stmtUpdateMoedas = $conn->prepare($sqlUpdateMoedas);
            $stmtUpdateMoedas->bind_param("ii", $novoSaldoMoedas, $idCliente);
            
            if (!$stmtUpdateMoedas->execute()) {
                throw new Exception("Erro ao debitar moedas");
            }
            
            $sqlTransacao = "INSERT INTO transacoes_moedas (idCliente, tipo, quantidade, descricao, data_transacao) VALUES (?, 'debito', ?, 'Compra de jogos', NOW())";
            $stmtTransacao = $conn->prepare($sqlTransacao);
            $stmtTransacao->bind_param("ii", $idCliente, $coinsAmount);
            $stmtTransacao->execute();
            
            if (!empty($_SESSION['carrinho'])) {
                $_SESSION['carrinho_finalizado'] = $_SESSION['carrinho'];
                
                if (!isset($_SESSION['biblioteca'])) {
                    $_SESSION['biblioteca'] = [];
                }
                
                foreach ($_SESSION['carrinho'] as $idJogo => $item) {
                    if (!isset($_SESSION['biblioteca'][$idJogo])) {
                        $_SESSION['biblioteca'][$idJogo] = [
                            'nome' => $item['nome'],
                            'data_compra' => date('Y-m-d H:i:s'),
                            'metodo_pagamento' => 'moedas'
                        ];
                    }
                }
                
                $_SESSION['carrinho'] = [];
            }
            
            $conn->commit();
            
            $_SESSION['compra_finalizada'] = true;
            $_SESSION['metodo_pagamento_usado'] = 'moedas';
            $_SESSION['moedas_gastas'] = $coinsAmount;
            
            echo json_encode([
                'success' => true,
                'new_balance' => $novoSaldoMoedas,
                'coins_spent' => $coinsAmount,
                'message' => 'Compra realizada com sucesso!'
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Erro no pagamento com moedas: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
    }
    
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['carrinho'])) {
    
    $metodoPagamento = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cartao-de-credito';
    
    if ($metodoPagamento === 'moedas') {
        $valorTotal = 0;
        foreach ($_SESSION['carrinho'] as $idJogo => $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $valorTotal += $subtotal;
        }
        
        $coinsPerDollar = 1;
        $moedasNecessarias = ceil($valorTotal * $coinsPerDollar);
        
        $sqlMoedas = "SELECT moedas FROM clientes WHERE idCliente = ?";
        $stmtMoedas = $conn->prepare($sqlMoedas);
        $stmtMoedas->bind_param("i", $idCliente);
        $stmtMoedas->execute();
        $resultMoedas = $stmtMoedas->get_result();
        
        if ($resultMoedas->num_rows > 0) {
            $clienteData = $resultMoedas->fetch_assoc();
            $moedasAtuais = $clienteData['moedas'];
            
            if ($moedasAtuais < $moedasNecessarias) {
                $_SESSION['erro_compra'] = "Saldo insuficiente! Você tem {$moedasAtuais} moedas, mas precisa de {$moedasNecessarias}.";
                header('Location: cart.php');
                exit;
            }
            
            $novoSaldoMoedas = $moedasAtuais - $moedasNecessarias;
            $sqlUpdateMoedas = "UPDATE clientes SET moedas = ? WHERE idCliente = ?";
            $stmtUpdateMoedas = $conn->prepare($sqlUpdateMoedas);
            $stmtUpdateMoedas->bind_param("ii", $novoSaldoMoedas, $idCliente);
            $stmtUpdateMoedas->execute();
            
            $_SESSION['moedas_gastas'] = $moedasNecessarias;
            $_SESSION['metodo_pagamento_usado'] = 'moedas';
        }
    } else {
        $_SESSION['metodo_pagamento_usado'] = $metodoPagamento;
        
        $valorTotal = 0;
        foreach ($_SESSION['carrinho'] as $idJogo => $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $valorTotal += $subtotal;
        }
        
        $novasMoedas = floor($valorTotal * 0.05);
        
        if ($novasMoedas > 0 && isset($conn) && $conn !== null) {
            $atualizouMoedas = atualizarMoedasFidelidade($conn, $idCliente, $novasMoedas);
            
            if ($atualizouMoedas) {
                $_SESSION['moedas_ganhas'] = $novasMoedas;
            } else {
                error_log("Falha ao atualizar moedas para o cliente ID: $idCliente");
            }
        }
    }
    
    $_SESSION['carrinho_finalizado'] = $_SESSION['carrinho'];
    
    if (!isset($_SESSION['biblioteca'])) {
        $_SESSION['biblioteca'] = [];
    }
    
    foreach ($_SESSION['carrinho'] as $idJogo => $item) {
        if (!isset($_SESSION['biblioteca'][$idJogo])) {
            $_SESSION['biblioteca'][$idJogo] = [
                'nome' => $item['nome'],
                'data_compra' => date('Y-m-d H:i:s'),
                'metodo_pagamento' => $metodoPagamento
            ];
        }
    }
    
    $_SESSION['carrinho'] = [];
    
    $_SESSION['compra_finalizada'] = true;
    
    header('Location: pedidos.php');
    exit;
    
} else {
    header('Location: cart.php');
    exit;
}

/**
 * Função para atualizar as moedas de fidelidade do cliente
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $idCliente ID do cliente
 * @param int $novasMoedas Quantidade de moedas a adicionar
 * @return bool True se atualizado com sucesso, False caso contrário
 */
function atualizarMoedasFidelidade($conn, $idCliente, $novasMoedas) {
    if (!$conn || $conn->connect_error) {
        error_log("Conexão inválida ao atualizar moedas");
        return false;
    }
    
    try {
        $sqlSelect = "SELECT moedas FROM clientes WHERE idCliente = ?";
        $stmtSelect = $conn->prepare($sqlSelect);
        
        if (!$stmtSelect) {
            error_log("Erro ao preparar consulta: " . $conn->error);
            return false;
        }
        
        $stmtSelect->bind_param("i", $idCliente);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $moedasAtuais = $row['moedas'];
            
            $totalMoedas = $moedasAtuais + $novasMoedas;
            
            $sqlUpdate = "UPDATE clientes SET moedas = ? WHERE idCliente = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            
            if (!$stmtUpdate) {
                error_log("Erro ao preparar atualização: " . $conn->error);
                return false;
            }
            
            $stmtUpdate->bind_param("ii", $totalMoedas, $idCliente);
            $success = $stmtUpdate->execute();
            
            if ($success) {
                $sqlTransacao = "INSERT INTO transacoes_moedas (idCliente, tipo, quantidade, descricao, data_transacao) VALUES (?, 'credito', ?, 'Fidelidade por compra', NOW())";
                $stmtTransacao = $conn->prepare($sqlTransacao);
                if ($stmtTransacao) {
                    $stmtTransacao->bind_param("ii", $idCliente, $novasMoedas);
                    $stmtTransacao->execute();
                }
                
                return true;
            } else {
                error_log("Erro ao atualizar moedas: " . $stmtUpdate->error);
                return false;
            }
        } else {
            error_log("Cliente não encontrado: ID " . $idCliente);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exceção ao atualizar moedas: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para buscar saldo de moedas do cliente
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $idCliente ID do cliente
 * @return int Saldo de moedas do cliente
 */
function buscarSaldoMoedas($conn, $idCliente) {
    try {
        $sql = "SELECT moedas FROM clientes WHERE idCliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['moedas'];
        }
        
        return 0;
    } catch (Exception $e) {
        error_log("Erro ao buscar saldo de moedas: " . $e->getMessage());
        return 0;
    }
}
?>
