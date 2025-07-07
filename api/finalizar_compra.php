<?php
session_start();
include 'connect.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    $_SESSION['erro_compra'] = "Você precisa estar logado para finalizar a compra.";
    header('Location: login.php');
    exit;
}

// Obter ID do cliente de forma segura
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

// **NOVA FUNCIONALIDADE: Processamento AJAX para pagamento com moedas**
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
        
        // Verificar se tem moedas suficientes
        if ($moedasAtuais < $coinsAmount) {
            echo json_encode([
                'success' => false, 
                'message' => 'Saldo insuficiente',
                'current_balance' => $moedasAtuais,
                'required' => $coinsAmount
            ]);
            exit;
        }
        
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Debitar moedas do cliente
            $novoSaldoMoedas = $moedasAtuais - $coinsAmount;
            $sqlUpdateMoedas = "UPDATE clientes SET moedas = ? WHERE idCliente = ?";
            $stmtUpdateMoedas = $conn->prepare($sqlUpdateMoedas);
            $stmtUpdateMoedas->bind_param("ii", $novoSaldoMoedas, $idCliente);
            
            if (!$stmtUpdateMoedas->execute()) {
                throw new Exception("Erro ao debitar moedas");
            }
            
            // Registrar transação de moedas
            $sqlTransacao = "INSERT INTO transacoes_moedas (idCliente, tipo, quantidade, descricao, data_transacao) VALUES (?, 'debito', ?, 'Compra de jogos', NOW())";
            $stmtTransacao = $conn->prepare($sqlTransacao);
            $stmtTransacao->bind_param("ii", $idCliente, $coinsAmount);
            $stmtTransacao->execute();
            
            // Processar itens do carrinho (adicionar à biblioteca)
            if (!empty($_SESSION['carrinho'])) {
                // Salvar cópia do carrinho
                $_SESSION['carrinho_finalizado'] = $_SESSION['carrinho'];
                
                // Adicionar jogos à biblioteca
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
                
                // Limpar carrinho
                $_SESSION['carrinho'] = [];
            }
            
            // Confirmar transação
            $conn->commit();
            
            // Atualizar sessão
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

// **PROCESSAMENTO NORMAL (métodos de pagamento tradicionais)**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['carrinho'])) {
    
    // Verificar método de pagamento
    $metodoPagamento = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cartao-de-credito';
    
    // Se for pagamento com moedas via POST normal (fallback)
    if ($metodoPagamento === 'moedas') {
        // Calcular valor total em moedas
        $valorTotal = 0;
        foreach ($_SESSION['carrinho'] as $idJogo => $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $valorTotal += $subtotal;
        }
        
        $coinsPerDollar = 100; // 1 dólar = 100 moedas
        $moedasNecessarias = ceil($valorTotal * $coinsPerDollar);
        
        // Verificar saldo
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
            
            // Processar pagamento com moedas
            $novoSaldoMoedas = $moedasAtuais - $moedasNecessarias;
            $sqlUpdateMoedas = "UPDATE clientes SET moedas = ? WHERE idCliente = ?";
            $stmtUpdateMoedas = $conn->prepare($sqlUpdateMoedas);
            $stmtUpdateMoedas->bind_param("ii", $novoSaldoMoedas, $idCliente);
            $stmtUpdateMoedas->execute();
            
            $_SESSION['moedas_gastas'] = $moedasNecessarias;
            $_SESSION['metodo_pagamento_usado'] = 'moedas';
        }
    } else {
        // Processamento normal para outros métodos de pagamento
        $_SESSION['metodo_pagamento_usado'] = $metodoPagamento;
        
        // Calcular o valor total da compra para o cálculo de moedas de fidelidade
        $valorTotal = 0;
        foreach ($_SESSION['carrinho'] as $idJogo => $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $valorTotal += $subtotal;
        }
        
        // Calcular moedas de fidelidade (5% do valor total) - apenas para outros métodos
        $novasMoedas = floor($valorTotal * 0.05);
        
        // Atualizar as moedas do cliente no banco de dados
        if ($novasMoedas > 0 && isset($conn) && $conn !== null) {
            $atualizouMoedas = atualizarMoedasFidelidade($conn, $idCliente, $novasMoedas);
            
            if ($atualizouMoedas) {
                $_SESSION['moedas_ganhas'] = $novasMoedas;
            } else {
                error_log("Falha ao atualizar moedas para o cliente ID: $idCliente");
            }
        }
    }
    
    // Salvar uma cópia do carrinho antes de limpá-lo
    $_SESSION['carrinho_finalizado'] = $_SESSION['carrinho'];
    
    // Inicializar biblioteca se não existir
    if (!isset($_SESSION['biblioteca'])) {
        $_SESSION['biblioteca'] = [];
    }
    
    // Adicionar jogos à biblioteca
    foreach ($_SESSION['carrinho'] as $idJogo => $item) {
        if (!isset($_SESSION['biblioteca'][$idJogo])) {
            $_SESSION['biblioteca'][$idJogo] = [
                'nome' => $item['nome'],
                'data_compra' => date('Y-m-d H:i:s'),
                'metodo_pagamento' => $metodoPagamento
            ];
        }
    }
    
    // Limpar o carrinho após finalizar a compra
    $_SESSION['carrinho'] = [];
    
    $_SESSION['compra_finalizada'] = true;
    
    // Redirecionar para a página de pedidos
    header('Location: pedidos.php');
    exit;
    
} else {
    // Se não for POST ou carrinho vazio, voltar para o carrinho
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
    // Verificar se a conexão é válida
    if (!$conn || $conn->connect_error) {
        error_log("Conexão inválida ao atualizar moedas");
        return false;
    }
    
    try {
        // Primeiro, vamos buscar a quantidade atual de moedas
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
            
            // Calcular o novo total de moedas
            $totalMoedas = $moedasAtuais + $novasMoedas;
            
            // Atualizar o registro do cliente
            $sqlUpdate = "UPDATE clientes SET moedas = ? WHERE idCliente = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            
            if (!$stmtUpdate) {
                error_log("Erro ao preparar atualização: " . $conn->error);
                return false;
            }
            
            $stmtUpdate->bind_param("ii", $totalMoedas, $idCliente);
            $success = $stmtUpdate->execute();
            
            if ($success) {
                // Registrar transação de crédito
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
