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

function verificarJogoNaBiblioteca($idCliente, $idProduto) {
    global $conn;
    
    try {
        $sql = "SELECT COUNT(*) as count FROM biblioteca_usuario WHERE idCliente = ? AND idProduto = ? AND statusJogo = 'Ativo'";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar verificação de jogo na biblioteca: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $idCliente, $idProduto);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
        
    } catch (Exception $e) {
        error_log("Erro ao verificar jogo na biblioteca: " . $e->getMessage());
        return false;
    }
}

function adicionarJogoBiblioteca($idCliente, $idProduto) {
    global $conn;
    
    try {
        if (verificarJogoNaBiblioteca($idCliente, $idProduto)) {
            error_log("Jogo $idProduto já existe na biblioteca do usuário $idCliente");
            return true;
        }
        
        $sql = "INSERT INTO biblioteca_usuario (idCliente, idProduto, dataAquisicao, statusJogo) VALUES (?, ?, NOW(), 'Ativo')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar inserção na biblioteca: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $idCliente, $idProduto);
        $resultado = $stmt->execute();
        
        if ($resultado) {
            error_log("Jogo $idProduto adicionado à biblioteca do usuário $idCliente");
        } else {
            error_log("Erro ao adicionar jogo à biblioteca: " . $stmt->error);
        }
        
        $stmt->close();
        return $resultado;
        
    } catch (Exception $e) {
        error_log("Erro ao adicionar jogo à biblioteca: " . $e->getMessage());
        return false;
    }
}

function registrarCompra($idCliente, $totalValor, $paymentMethod, $itensCarrinho) {
    global $conn;
    
    try {
        $sql = "INSERT INTO compras (idCliente, valorTotal, metodoPagamento, dataCompra, status) VALUES (?, ?, ?, NOW(), 'Concluida')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar inserção de compra: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ids", $idCliente, $totalValor, $paymentMethod);
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Erro ao inserir compra: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $idCompra = $conn->insert_id;
        $stmt->close();
        
        foreach ($itensCarrinho as $idProduto => $item) {
            $sql = "INSERT INTO itens_compra (idCompra, idProduto, quantidade, precoUV, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Erro ao preparar inserção de item: " . $conn->error);
                continue;
            }
            
            $quantidade = $item['quantidade'];
            $precoUnitario = $item['preco'];
            $subtotal = $precoUnitario * $quantidade;
            
            $stmt->bind_param("iiidd", $idCompra, $idProduto, $quantidade, $precoUnitario, $subtotal);
            $stmt->execute();
            $stmt->close();
        }
        
        return $idCompra;
        
    } catch (Exception $e) {
        error_log("Erro ao registrar compra: " . $e->getMessage());
        return false;
    }
}

function buscarSaldoMoedas($idCliente) {
    global $conn;
    
    try {
        $sql = "SELECT moedas FROM clientes WHERE idCliente = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar consulta de moedas: " . $conn->error);
            return 0;
        }
        
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return (int)$row['moedas'];
        }
        
        $stmt->close();
        return 0;
        
    } catch (Exception $e) {
        error_log("Erro ao buscar saldo de moedas: " . $e->getMessage());
        return 0;
    }
}

function debitarMoedas($idCliente, $quantidadeMoedas) {
    global $conn;
    
    try {
        $sql = "UPDATE clientes SET moedas = moedas - ? WHERE idCliente = ? AND moedas >= ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar débito de moedas: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("iii", $quantidadeMoedas, $idCliente, $quantidadeMoedas);
        $resultado = $stmt->execute();
        
        if ($resultado && $stmt->affected_rows > 0) {
            error_log("Debitadas $quantidadeMoedas moedas do usuário $idCliente");
            
            $sqlTransacao = "INSERT INTO transacoes_moedas (idCliente, tipo, quantidade, descricao, data_transacao) VALUES (?, 'debito', ?, 'Compra de jogos', NOW())";
            $stmtTransacao = $conn->prepare($sqlTransacao);
            if ($stmtTransacao) {
                $stmtTransacao->bind_param("ii", $idCliente, $quantidadeMoedas);
                $stmtTransacao->execute();
                $stmtTransacao->close();
            }
            
            $stmt->close();
            return true;
        } else {
            error_log("Erro ao debitar moedas ou saldo insuficiente - Affected rows: " . $stmt->affected_rows);
            $stmt->close();
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Erro ao debitar moedas: " . $e->getMessage());
        return false;
    }
}

function adicionarMoedasFidelidade($idCliente, $novasMoedas) {
    global $conn;
    
    if (!$conn || $conn->connect_error) {
        error_log("Conexão inválida ao adicionar moedas de fidelidade");
        return false;
    }
    
    try {
        $sql = "UPDATE clientes SET moedas = moedas + ? WHERE idCliente = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar adição de moedas de fidelidade: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $novasMoedas, $idCliente);
        $resultado = $stmt->execute();
        
        if ($resultado) {
            $sqlTransacao = "INSERT INTO transacoes_moedas (idCliente, tipo, quantidade, descricao, data_transacao) VALUES (?, 'credito', ?, 'Fidelidade por compra', NOW())";
            $stmtTransacao = $conn->prepare($sqlTransacao);
            if ($stmtTransacao) {
                $stmtTransacao->bind_param("ii", $idCliente, $novasMoedas);
                $stmtTransacao->execute();
                $stmtTransacao->close();
            }
            
            $stmt->close();
            return true;
        } else {
            error_log("Erro ao adicionar moedas de fidelidade: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Erro ao adicionar moedas de fidelidade: " . $e->getMessage());
        return false;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'process_coins_payment') {
    header('Content-Type: application/json');
    
    $coinsAmount = intval($_POST['coins_amount']);
    $cartTotal = floatval($_POST['cart_total']);
    
    if (empty($_SESSION['carrinho'])) {
        echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
        exit;
    }
    
    $saldoAtual = buscarSaldoMoedas($idCliente);
    
    if ($saldoAtual < $coinsAmount) {
        echo json_encode([
            'success' => false, 
            'message' => 'Saldo insuficiente',
            'current_balance' => $saldoAtual,
            'required' => $coinsAmount
        ]);
        exit;
    }
    
    $conn->begin_transaction();
    
    try {
        $jogosAdicionados = [];
        $errosAdicao = [];
        
        foreach ($_SESSION['carrinho'] as $idProduto => $item) {
            if (adicionarJogoBiblioteca($idCliente, $idProduto)) {
                $jogosAdicionados[] = $item['nome'];
            } else {
                $errosAdicao[] = $item['nome'];
            }
        }
        
        if (!empty($errosAdicao)) {
            $conn->rollback();
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao adicionar jogos à biblioteca: ' . implode(', ', $errosAdicao)
            ]);
            exit;
        }
        
        if (!debitarMoedas($idCliente, $coinsAmount)) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Erro ao debitar moedas']);
            exit;
        }
        
        $idCompra = registrarCompra($idCliente, $cartTotal, 'moedas', $_SESSION['carrinho']);
        
        if (!$idCompra) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar compra']);
            exit;
        }
        
        $conn->commit();
        
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
        
        $_SESSION['compra_finalizada'] = true;
        $_SESSION['metodo_pagamento_usado'] = 'moedas';
        $_SESSION['moedas_gastas'] = $coinsAmount;
        $_SESSION['compra_sucesso'] = [
            'jogos' => $jogosAdicionados,
            'total' => $cartTotal,
            'metodo' => 'moedas',
            'id_compra' => $idCompra
        ];
        
        $novoSaldo = buscarSaldoMoedas($idCliente);
        
        echo json_encode([
            'success' => true,
            'new_balance' => $novoSaldo,
            'coins_spent' => $coinsAmount,
            'message' => 'Compra realizada com sucesso!'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro na transação de pagamento com moedas: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
    }
    
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
    
    $metodoPagamento = isset($_POST['payment_method']) && !empty($_POST['payment_method']) 
        ? $_POST['payment_method'] 
        : 'cartao-de-credito';
    
    $valorTotal = 0;
    $itensValidos = [];
    
    foreach ($_SESSION['carrinho'] as $idJogo => $item) {
        if (!isset($item['preco']) || !isset($item['quantidade']) || !is_numeric($item['preco']) || !is_numeric($item['quantidade'])) {
            error_log("Item inválido no carrinho - ID: $idJogo");
            continue;
        }
        
        $subtotal = floatval($item['preco']) * intval($item['quantidade']);
        $valorTotal += $subtotal;
        $itensValidos[$idJogo] = $item;
    }
    
    if (empty($itensValidos) || $valorTotal <= 0) {
        $_SESSION['erro_compra'] = "Carrinho inválido ou vazio. Adicione itens válidos.";
        header('Location: cart.php');
        exit;
    }
    
    $_SESSION['carrinho'] = $itensValidos;
    
    if ($metodoPagamento === 'moedas') {
        // Processamento com moedas (código existente)
        $coinsPerDollar = 100;
        $moedasNecessarias = ceil($valorTotal * $coinsPerDollar);
        
        $saldoAtual = buscarSaldoMoedas($idCliente);
        
        if ($saldoAtual < $moedasNecessarias) {
            $_SESSION['erro_compra'] = "Saldo insuficiente! Você tem {$saldoAtual} moedas, mas precisa de {$moedasNecessarias}.";
            header('Location: cart.php');
            exit;
        }
        
        if (!$conn->begin_transaction()) {
            $_SESSION['erro_compra'] = 'Erro ao iniciar transação. Tente novamente.';
            header('Location: cart.php');
            exit;
        }
        
        try {
            $jogosAdicionados = [];
            $errosAdicao = [];
            
            foreach ($_SESSION['carrinho'] as $idProduto => $item) {
                if (adicionarJogoBiblioteca($idCliente, $idProduto)) {
                    $jogosAdicionados[] = $item['nome'];
                } else {
                    $errosAdicao[] = $item['nome'];
                }
            }
            
            if (!empty($errosAdicao)) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao adicionar jogos à biblioteca: ' . implode(', ', $errosAdicao);
                header('Location: cart.php');
                exit;
            }
            
            if (!debitarMoedas($idCliente, $moedasNecessarias)) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao debitar moedas. Tente novamente.';
                header('Location: cart.php');
                exit;
            }
            
            $idCompra = registrarCompra($idCliente, $valorTotal, 'moedas', $_SESSION['carrinho']);
            
            if (!$idCompra) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao registrar compra no histórico.';
                header('Location: cart.php');
                exit;
            }
            
            if (!$conn->commit()) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao finalizar transação.';
                header('Location: cart.php');
                exit;
            }
            
            $_SESSION['moedas_gastas'] = $moedasNecessarias;
            $_SESSION['metodo_pagamento_usado'] = 'moedas';
            $_SESSION['compra_sucesso'] = [
                'jogos' => $jogosAdicionados,
                'total' => $valorTotal,
                'metodo' => 'moedas',
                'id_compra' => $idCompra
            ];
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Erro na transação de compra com moedas: " . $e->getMessage());
            $_SESSION['erro_compra'] = 'Erro interno do servidor. Tente novamente.';
            header('Location: cart.php');
            exit;
        }
        
    } else {
        if (!$conn->begin_transaction()) {
            $_SESSION['erro_compra'] = 'Erro ao iniciar transação. Tente novamente.';
            header('Location: cart.php');
            exit;
        }
        
        try {
            $jogosAdicionados = [];
            $errosAdicao = [];
            
            foreach ($_SESSION['carrinho'] as $idProduto => $item) {
                if (adicionarJogoBiblioteca($idCliente, $idProduto)) {
                    $jogosAdicionados[] = $item['nome'];
                } else {
                    $errosAdicao[] = $item['nome'];
                }
            }
            
            if (!empty($errosAdicao)) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao adicionar jogos à biblioteca: ' . implode(', ', $errosAdicao);
                header('Location: cart.php');
                exit;
            }
            
            $idCompra = registrarCompra($idCliente, $valorTotal, $metodoPagamento, $_SESSION['carrinho']);
            
            if (!$idCompra) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao registrar compra no histórico.';
                header('Location: cart.php');
                exit;
            }
            
            // Adicionar moedas de fidelidade
            $novasMoedas = ceil($valorTotal * 0.05);
            if ($novasMoedas > 0) {
                if (adicionarMoedasFidelidade($idCliente, $novasMoedas)) {
                    $_SESSION['moedas_ganhas'] = $novasMoedas;
                }
            }
            
            // 9. CORREÇÃO: Commit da transação
            if (!$conn->commit()) {
                $conn->rollback();
                $_SESSION['erro_compra'] = 'Erro ao finalizar transação.';
                header('Location: cart.php');
                exit;
            }
            
            $_SESSION['metodo_pagamento_usado'] = $metodoPagamento;
            $_SESSION['compra_sucesso'] = [
                'jogos' => $jogosAdicionados,
                'total' => $valorTotal,
                'metodo' => $metodoPagamento,
                'id_compra' => $idCompra
            ];
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Erro na transação de compra: " . $e->getMessage());
            $_SESSION['erro_compra'] = 'Erro interno do servidor. Tente novamente.';
            header('Location: cart.php');
            exit;
        }
    }
    
    // 10. CORREÇÃO: Atualizar biblioteca e limpar carrinho APENAS após sucesso
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
    
    // Limpar carrinho
    $_SESSION['carrinho'] = [];
    $_SESSION['compra_finalizada'] = true;
    
    // 11. CORREÇÃO: Adicionar log de sucesso
    error_log("Compra finalizada com sucesso - Cliente: $idCliente, Compra: " . $idCompra);
    
    header('Location: pedidos.php');
    exit;
    
} else {
    // 12. CORREÇÃO: Log para debugging
    error_log("Redirecionamento para cart.php - REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . 
              ", Carrinho vazio: " . (empty($_SESSION['carrinho']) ? 'sim' : 'não'));
    header('Location: cart.php');
    exit;
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
?>
