<?php
// Inicia a sessão ANTES de qualquer output
session_start();

// Debug das variáveis de sessão (remova depois de testar)
error_log("=== DEBUG CARRINHO ===");
error_log("Session ID: " . session_id());
error_log("usuario_logado: " . (isset($_SESSION['usuario_logado']) ? var_export($_SESSION['usuario_logado'], true) : 'não definido'));
error_log("nome_usuario: " . (isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'não definido'));
error_log("id_usuario: " . (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 'não definido'));
error_log("Todas as sessões: " . print_r($_SESSION, true));
error_log("=====================");

include 'connect.php';

// Verificação de sessão mais simples e clara
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    error_log("Usuário não logado - redirecionando para login");
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Verificar se tem id_usuario (necessário para o formulário)
if (!isset($_SESSION['id_usuario'])) {
    error_log("ID do usuário não encontrado na sessão");
    // Se não tem id_usuario mas está logado, tentar pegar do array usuario
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

$carrinhoVazio = empty($_SESSION['carrinho']);
$valorTotal = 0;

if (!$carrinhoVazio) {
    foreach ($_SESSION['carrinho'] as $id => $item) {
        $subtotal = $item['preco'] * $item['quantidade'];
        $valorTotal += $subtotal;
    }
}

function criarNovaConexao() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gamoraloja";

    $novaConn = new mysqli($servername, $username, $password, $dbname);
    
    if ($novaConn->connect_error) {
        throw new Exception("Falha na conexão: " . $novaConn->connect_error);
    }
    
    $novaConn->set_charset("utf8");
    
    return $novaConn;
}

// Função para obter a imagem principal do produto
function getImagemPrincipal($idProduto)
{
    try {
        // Criar uma nova conexão independente para esta função
        $conn = criarNovaConexao();
        
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
            $conn->close();
            return $row['urlImagem'];
        }
        
        $stmt->close();
        
        // Fallback para a imagem do produto na tabela produtos
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
            $conn->close();
            return $row['imagemPrincipal'] ? $row['imagemPrincipal'] : 'imagens/placeholder.jpg';
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        error_log("Erro ao buscar imagem do produto $idProduto: " . $e->getMessage());
    }

    return 'imagens/placeholder.jpg'; // Imagem padrão caso nenhuma seja encontrada
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
            // Simula buscar saldo de moedas do usuário (substitua pela sua lógica de banco de dados)
            $userCoins = isset($_SESSION['user_coins']) ? $_SESSION['user_coins'] : 500;
            
            // Calcula total do carrinho em moedas (assumindo 1 dólar = 100 moedas)
            $cartTotalDollars = 0; // Você deve calcular isso baseado nos itens do carrinho
            $coinsPerDollar = 100;
            $requiredCoins = $cartTotalDollars * $coinsPerDollar;
            $hasSufficientCoins = $userCoins >= $requiredCoins;
            
            $paymentMethods = [
                'Cartão de crédito',
                'Pix', 
                'PicPay', 
                'Boleto', 
                'Cripto',
                'Moedas' 
            ];
            
            foreach ($paymentMethods as $index => $method) {
                $methodId = strtolower(str_replace(' ', '-', $method));
                $methodValue = strtolower(str_replace(' ', '-', $method));
                $isCoinsMethod = $method === 'Moedas';
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
                
                echo htmlspecialchars($method) . "</label>";
                
                // info para pagamento com moedas
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
            }
            
            // msg de fundos insuficientes
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
                <input type="hidden" name="payment_method" id="selected_payment">
                <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                <button type="submit" class="finalize-button">Finalizar Compra</button>
            </form>

            <script>
                // Script para garantir que um método de pagamento seja selecionado
                document.getElementById('checkout-form').addEventListener('submit', function (e) {
                    // Obter método de pagamento selecionado
                    const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
                    if (selectedMethod) {
                        document.getElementById('selected_payment').value = selectedMethod.value;
                    } else {
                        e.preventDefault();
                        alert('Por favor, selecione um método de pagamento.');
                        return false;
                    }
                    
                    // Debug - verificar se os dados estão sendo enviados
                    console.log('Dados do formulário:', {
                        idCliente: document.querySelector('input[name="idCliente"]').value,
                        payment_method: document.getElementById('selected_payment').value
                    });
                });
            </script>
        <?php endif; ?>

        <a href="index.php" class="back-to-store">Voltar para a loja</a>
    </div>
</body>

</html>