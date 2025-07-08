<?php
session_start();

error_log("=== DEBUG CARRINHO ===");
error_log("Session ID: " . session_id());
error_log("usuario_logado: " . (isset($_SESSION['usuario_logado']) ? var_export($_SESSION['usuario_logado'], true) : 'não definido'));
error_log("nome_usuario: " . (isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'não definido'));
error_log("id_usuario: " . (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 'não definido'));
error_log("Todas as sessões: " . print_r($_SESSION, true));
error_log("=====================");

include 'connect.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    error_log("Usuário não logado - redirecionando para login");
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    error_log("ID do usuário não encontrado na sessão");
    if (isset($_SESSION['usuario']['id'])) {
        $_SESSION['id_usuario'] = $_SESSION['usuario']['id'];
        error_log("ID do usuário recuperado do array usuario: " . $_SESSION['id_usuario']);
    } else {
        error_log("ID do usuário não encontrado - forçando novo login");
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Gerar token CSRF se não existir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$carrinhoVazio = empty($_SESSION['carrinho']);
$valorTotal = 0;

if (!$carrinhoVazio) {
    foreach ($_SESSION['carrinho'] as $id => $item) {
        $subtotal = $item['preco'] * $item['quantidade'];
        $valorTotal += $subtotal;
    }
}

function getImagemPrincipal($idProduto)
{
    global $conn; 
    
    try {
        $sql = "SELECT urlImagem FROM imagensproduto WHERE idProduto = ? AND ordemExibicao = 1 AND status = 'Ativa' LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . $conn->error);
        }
        
        $stmt->bind_param("i", $idProduto);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            
            $imagePath = $row['urlImagem'];
            if (!empty($imagePath) && file_exists($imagePath)) {
                return $imagePath;
            }
        }
        
        if (isset($stmt)) {
            $stmt->close();
        }
        
        $sql = "SELECT imagemPrincipal FROM produtos WHERE idProduto = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro na preparação da query fallback: " . $conn->error);
        }
        
        $stmt->bind_param("i", $idProduto);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            
            $imagePath = $row['imagemPrincipal'];
            if (!empty($imagePath) && file_exists($imagePath)) {
                return $imagePath;
            }
        }
        
        if (isset($stmt)) {
            $stmt->close();
        }
        
    } catch (Exception $e) {
        error_log("Erro ao buscar imagem do produto $idProduto: " . $e->getMessage());
        if (isset($stmt)) {
            $stmt->close();
        }
    }

    return 'imagens/placeholder.jpg';
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

// Buscar saldo real de moedas do banco - CORREÇÃO AQUI
$userCoins = 0;
try {
    $userCoins = buscarSaldoMoedas($_SESSION['id_usuario']); // Removido $conn
    error_log("Saldo de moedas do usuário ID " . $_SESSION['id_usuario'] . ": " . $userCoins);
} catch (Exception $e) {
    error_log("Erro ao buscar saldo de moedas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Carrinho de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <?php include "navbar/nav.php"; ?>
    <link rel="stylesheet" href="css/cart.css">
</head>

<body>
    <div class="cart-container">
        <div class="cart-header">
            <h2>Carrinho</h2>
            <div class="nav-links">
                <a href="biblioteca.php" class="nav-link">Minha Biblioteca</a>
                <a href="index.php" class="nav-link">Loja</a>
            </div>
        </div>

        <div class="user-info">
            <span>Olá, <?php echo isset($_SESSION['nome_usuario']) ? htmlspecialchars($_SESSION['nome_usuario']) : 'Usuário'; ?></span>
            <div class="coins-display">
                <span class="coins-icon">🪙</span>
                <span id="user-coins-display"><?php echo number_format($userCoins); ?></span> moedas
            </div>
        </div>

        <?php if ($carrinhoVazio): ?>
            <div class='empty-cart-message'>Seu carrinho está vazio.</div>
            <button class='finalize-button' disabled>Finalizar Compra</button>
        <?php else: ?>
            <?php
            foreach ($_SESSION['carrinho'] as $id => $item) {
                // Obter a imagem principal do banco de dados
                $imagePath = getImagemPrincipal($id);
                ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" class="cart-item-image">
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
                        <p>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></p>
                    </div>
                    <a href="remover_item.php?id=<?php echo $id; ?>" class="remove-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </a>
                </div>
            <?php } ?>

            <div class="total-price">
                Total: R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?>
            </div>

        <div class="payment-methods">
            <h3>Método de Pagamento</h3>
            <?php
            $coinsPerReal = 1; 
            $requiredCoins = ceil($valorTotal * $coinsPerReal);
            $hasSufficientCoins = $userCoins >= $requiredCoins;
            
            $paymentMethods = [
                'cartao-credito' => 'Cartão de crédito',
                'pix' => 'Pix', 
                'picpay' => 'PicPay', 
                'boleto' => 'Boleto', 
                'cripto' => 'Cripto',
                'moedas' => 'Moedas' 
            ];
            
            $index = 0;
            foreach ($paymentMethods as $methodValue => $methodLabel) {
                $methodId = $methodValue;
                $isCoinsMethod = $methodValue === 'moedas';
                $isDisabled = $isCoinsMethod && !$hasSufficientCoins;
                $isChecked = $index === 0 && !$isDisabled;
                
                echo "<div class='payment-method" . ($isCoinsMethod ? ' coins' : '') . 
                    ($isCoinsMethod ? ($hasSufficientCoins ? ' sufficient' : ' insufficient') : '') . "'>";
                
                echo "<input type='radio' id='" . htmlspecialchars($methodId) . "' 
                            name='payment_method' value='" . htmlspecialchars($methodValue) . "'"
                            . ($isChecked ? ' checked' : '')
                            . ($isDisabled ? ' disabled' : '') . ">";
                
                echo "<label for='" . htmlspecialchars($methodId) . "'" . 
                    ($isDisabled ? " class='disabled'" : "") . ">";
                
                if ($isCoinsMethod) {
                    echo "<span class='coins-icon'>🪙</span>";
                }
                
                echo htmlspecialchars($methodLabel) . "</label>";
                
                // Info para pagamento com moedas
                if ($isCoinsMethod) {
                    echo "<div class='coins-info'>
                            <div class='coins-balance " . ($hasSufficientCoins ? 'sufficient' : 'insufficient') . "'>
                                Saldo: <span id='user-coins'>" . number_format($userCoins) . "</span> moedas
                            </div>
                            <div class='coins-required'>
                                Necessário: <span id='total-coins-needed'>" . number_format($requiredCoins) . "</span> moedas
                            </div>
                        </div>";
                }
                
                echo "</div>";
                $index++;
            }
            
            // Mensagem de fundos insuficientes
            if (!$hasSufficientCoins && $requiredCoins > 0) {
                $missingCoins = $requiredCoins - $userCoins;
                echo "<div id='insufficient-funds' class='insufficient-funds-message'>
                        Saldo insuficiente! Você precisa de mais " . number_format($missingCoins) . " moedas.
                    </div>";
            }
            ?>
        </div>

            <form method="POST" action="finalizar_compra.php" id="checkout-form">
                <input type="hidden" name="idCliente" value="<?php echo isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 0; ?>">
                <input type="hidden" name="payment_method" id="selected_payment" value="">
                <input type="hidden" name="total_valor" value="<?php echo $valorTotal; ?>">
                <input type="hidden" name="total_moedas" value="<?php echo $requiredCoins; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" class="finalize-button" id="finalize-btn">Finalizar Compra</button>
            </form>

            <script>
                // Definir o método de pagamento inicial
                document.addEventListener('DOMContentLoaded', function() {
                    const firstEnabledMethod = document.querySelector('input[name="payment_method"]:not([disabled])');
                    if (firstEnabledMethod) {
                        firstEnabledMethod.checked = true;
                        document.getElementById('selected_payment').value = firstEnabledMethod.value;
                    }
                });

                function atualizarSaldoMoedas() {
                    fetch('buscar_saldo_moedas.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('user-coins').textContent = data.saldo.toLocaleString();
                                document.getElementById('user-coins-display').textContent = data.saldo.toLocaleString();
                                
                                // Atualizar estado do botão de moedas
                                const totalMoedas = <?php echo $requiredCoins; ?>;
                                const coinsMethod = document.getElementById('moedas');
                                const coinsBalance = document.querySelector('.coins-balance');
                                const insufficientFunds = document.getElementById('insufficient-funds');
                                
                                if (data.saldo >= totalMoedas) {
                                    coinsMethod.disabled = false;
                                    coinsBalance.className = 'coins-balance sufficient';
                                    if (insufficientFunds) {
                                        insufficientFunds.style.display = 'none';
                                    }
                                } else {
                                    coinsMethod.disabled = true;
                                    coinsBalance.className = 'coins-balance insufficient';
                                    if (insufficientFunds) {
                                        insufficientFunds.style.display = 'block';
                                        const faltam = totalMoedas - data.saldo;
                                        insufficientFunds.innerHTML = `Saldo insuficiente! Você precisa de mais ${faltam.toLocaleString()} moedas.`;
                                    }
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao atualizar saldo:', error);
                        });
                }
                
                setInterval(atualizarSaldoMoedas, 30000);
                
                document.getElementById('checkout-form').addEventListener('submit', function (e) {
                    const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
                    if (selectedMethod) {
                        if (selectedMethod.value === 'moedas') {
                            const userCoins = parseInt(document.getElementById('user-coins').textContent.replace(/[.,]/g, ''));
                            const requiredCoins = <?php echo $requiredCoins; ?>;
                            
                            if (userCoins < requiredCoins) {
                                e.preventDefault();
                                alert('Saldo insuficiente para pagamento com moedas!');
                                return false;
                            }
                        }
                        
                        document.getElementById('selected_payment').value = selectedMethod.value;
                        
                        // Debug - verificar se os dados estão sendo enviados
                        console.log('Dados do formulário:', {
                            idCliente: document.querySelector('input[name="idCliente"]').value,
                            payment_method: document.getElementById('selected_payment').value,
                            total_valor: document.querySelector('input[name="total_valor"]').value,
                            total_moedas: document.querySelector('input[name="total_moedas"]').value,
                            csrf_token: document.querySelector('input[name="csrf_token"]').value
                        });
                    } else {
                        e.preventDefault();
                        alert('Por favor, selecione um método de pagamento.');
                        return false;
                    }
                });
                
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        const finalizeBtn = document.getElementById('finalize-btn');
                        document.getElementById('selected_payment').value = this.value;
                        
                        if (this.value === 'moedas') {
                            const userCoins = parseInt(document.getElementById('user-coins').textContent.replace(/[.,]/g, ''));
                            const requiredCoins = <?php echo $requiredCoins; ?>;
                            
                            if (userCoins < requiredCoins) {
                                finalizeBtn.disabled = true;
                                finalizeBtn.textContent = 'Saldo Insuficiente';
                            } else {
                                finalizeBtn.disabled = false;
                                finalizeBtn.textContent = 'Finalizar Compra';
                            }
                        } else {
                            finalizeBtn.disabled = false;
                            finalizeBtn.textContent = 'Finalizar Compra';
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <a href="index.php" class="back-to-store">Voltar para a loja</a>
    </div>
</body>

</html>
