<?php
include 'connect.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['usuario']['id']; 
$current_user_name = $_SESSION['usuario']['nome']; 

$sql_jogos = "SELECT idProduto, nome, preco FROM produtos WHERE categoria = 'Jogos' AND status = 'Disponível' AND preco < 1000 ORDER BY nome ASC";
$jogos_result = $conn->query($sql_jogos);

$jogos = array();
if ($jogos_result && $jogos_result->num_rows > 0) {
    while ($produto = $jogos_result->fetch_assoc()) {
        $jogos[] = $produto;
    }
}

$sql_clientes = "SELECT idCliente, nome FROM clientes WHERE status = 'Ativo' AND idCliente != ? ORDER BY RAND() LIMIT 5";
$stmt = $conn->prepare($sql_clientes);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$clientes_result = $stmt->get_result();

$clientes = array();
if ($clientes_result && $clientes_result->num_rows > 0) {
    while ($cliente = $clientes_result->fetch_assoc()) {
        $clientes[] = $cliente;
    }
}

$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    $message = 'Troca simulada aceita com sucesso!';
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    $message = 'Erro ao processar troca. Por favor, tente novamente.';
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troca Simulada</title>
    <link rel="stylesheet" href="css/trade.css">
    <style>
        .proposal-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            border-left: 4px solid #007bff;
        }
        
        .proposal-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .proposer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .proposer-info h3 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        
        .proposer-info p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .proposal-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .game-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .game-name {
            font-weight: bold;
            color: #333;
        }
        
        .game-price {
            color: #28a745;
            font-weight: bold;
        }
        
        .exchange-arrow {
            text-align: center;
            margin: 10px 0;
            font-size: 20px;
            color: #007bff;
        }
        
        .proposal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-accept {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-accept:hover {
            background: #218838;
        }
        
        .btn-decline {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-decline:hover {
            background: #c82333;
        }
        
        .no-proposals {
            text-align: center;
            color: #666;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .back-button {
            display: inline-block;
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: #5a6268;
        }
        
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            font-weight: 500;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-in-out;
            max-width: 300px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast-notification::before {
            content: "✓";
            font-size: 16px;
            font-weight: bold;
        }
        
        .toast-notification.error {
            background-color: #f44336;
        }
        
        .toast-notification.error::before {
            content: "⚠";
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .modal-title {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            background: white;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .btn-submit {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <?php include "navbar/nav.php"; ?>
    
    <div class="container">
        <a href="trade.php" class="back-button">← Voltar para Trocas</a>
        
        <h1>Propostas de Troca Simuladas</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div id="proposalsContainer">
            <?php
            if (!empty($jogos) && !empty($clientes)) {
                $num_propostas = rand(3, 5);
                
                for ($i = 0; $i < $num_propostas; $i++) {
                    $jogos_validos = array_filter($jogos, function($jogo) {
                        return $jogo['preco'] <= 500; 
                    });
                    
                    if (empty($jogos_validos)) {
                        continue;
                    }
                    
                    $jogo_desejado = $jogos_validos[array_rand($jogos_validos)];
                    
                    $preco_min = $jogo_desejado['preco'] * 0.8;
                    $preco_max = $jogo_desejado['preco'] * 1.2;
                    
                    $jogos_compativeis = array_filter($jogos, function($jogo) use ($preco_min, $preco_max, $jogo_desejado) {
                        return $jogo['preco'] >= $preco_min && 
                               $jogo['preco'] <= $preco_max && 
                               $jogo['idProduto'] != $jogo_desejado['idProduto'];
                    });
                    
                    if (empty($jogos_compativeis)) {
                        continue;
                    }
                    
                    $cliente = $clientes[array_rand($clientes)];
                    
                    $avatars = ['👤', '👨', '👩', '🎮', '🕹️'];
                    $avatar = $avatars[$cliente['idCliente'] % count($avatars)];
                    
                    echo '<div class="proposal-card">
                            <div class="proposal-header">
                                <div class="proposer-avatar">' . $avatar . '</div>
                                <div class="proposer-info">
                                    <h3>' . htmlspecialchars($cliente['nome']) . '</h3>
                                    <p>Quer trocar com você</p>
                                </div>
                            </div>
                            
                            <div class="proposal-details">
                                <div class="game-info">
                                    <span class="game-name">' . htmlspecialchars($cliente['nome']) . ' oferece:</span>
                                    <span class="game-price">R$ ' . number_format($jogo_desejado['preco'], 2, ',', '.') . '</span>
                                </div>
                                <div style="font-weight: bold; color: #007bff; margin-bottom: 10px;">' . htmlspecialchars($jogo_desejado['nome']) . '</div>
                                
                                <div class="exchange-arrow">⬇️ Em troca de ⬇️</div>
                                
                                <div class="game-info">
                                    <span class="game-name">Você oferece:</span>
                                    <span class="game-price">Faixa: R$ ' . number_format($jogo_desejado['preco'] * 0.8, 2, ',', '.') . ' - R$ ' . number_format($jogo_desejado['preco'] * 1.2, 2, ',', '.') . '</span>
                                </div>
                                <div style="font-style: italic; color: #666;">Escolha um jogo na faixa de preço permitida (±20%)</div>
                            </div>
                            
                            <div class="proposal-actions">
                                <button class="btn-decline" onclick="declineProposal(this)">Recusar</button>
                                <button class="btn-accept" onclick="openTradeModal(' . $jogo_desejado['idProduto'] . ', \'' . htmlspecialchars($jogo_desejado['nome']) . '\', ' . $jogo_desejado['preco'] . ', \'' . htmlspecialchars($cliente['nome']) . '\')">Aceitar</button>
                            </div>
                        </div>';
                }
            } else {
                echo '<div class="no-proposals">Nenhuma proposta de troca disponível no momento.</div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Modal para escolher jogo para troca -->
    <div id="tradeModal" class="modal">
        <div class="modal-content">
            <h2 class="modal-title">Escolha seu jogo para trocar</h2>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-weight: bold; margin-bottom: 10px;">Proposta de: <span id="proposerName"></span></div>
                <div>Ele oferece: <strong id="offeredGame"></strong></div>
                <div>Valor: <strong id="offeredPrice"></strong></div>
            </div>
            
            <form id="tradeForm" method="post" action="process_simulated_trade.php">
                <input type="hidden" id="offeredGameId" name="offered_game_id">
                <input type="hidden" id="proposerNameInput" name="proposer_name">
                
                <div class="form-group">
                    <label for="userGame">Selecione seu jogo para trocar:</label>
                    <select id="userGame" name="user_game_id" required>
                        <option value="">Selecione um jogo...</option>
                    </select>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-cancel" onclick="closeTradeModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Confirmar Troca</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const jogos = <?php echo json_encode($jogos); ?>;
        
        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
        
        function openTradeModal(gameId, gameName, gamePrice, proposerName) {
            document.getElementById('offeredGameId').value = gameId;
            document.getElementById('offeredGame').textContent = gameName;
            document.getElementById('offeredPrice').textContent = 'R$ ' + gamePrice.toFixed(2).replace('.', ',');
            document.getElementById('proposerName').textContent = proposerName;
            document.getElementById('proposerNameInput').value = proposerName;
            
            const minPrice = gamePrice * 0.8;
            const maxPrice = gamePrice * 1.2;
            
            const compatibleGames = jogos.filter(jogo => 
                jogo.preco >= minPrice && 
                jogo.preco <= maxPrice && 
                jogo.idProduto != gameId
            );
            
            const select = document.getElementById('userGame');
            select.innerHTML = '<option value="">Selecione um jogo...</option>';
            
            compatibleGames.forEach(jogo => {
                const option = document.createElement('option');
                option.value = jogo.idProduto;
                option.textContent = `${jogo.nome} - R$ ${jogo.preco.toFixed(2).replace('.', ',')}`;
                select.appendChild(option);
            });
            
            if (compatibleGames.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Nenhum jogo compatível disponível';
                option.disabled = true;
                select.appendChild(option);
            }
            
            document.getElementById('tradeModal').style.display = 'flex';
        }
        
        function closeTradeModal() {
            document.getElementById('tradeModal').style.display = 'none';
            document.getElementById('tradeForm').reset();
        }
        
        function declineProposal(button) {
            const proposalCard = button.closest('.proposal-card');
            proposalCard.style.transition = 'all 0.3s ease';
            proposalCard.style.opacity = '0.5';
            proposalCard.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                proposalCard.remove();
                showToast('Proposta recusada');
            }, 300);
        }
        
        document.getElementById('tradeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_simulated_trade.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Troca realizada com sucesso!');
                    closeTradeModal();
                    
                    
                    const proposalCard = document.querySelector('.proposal-card');
                    if (proposalCard) {
                        proposalCard.remove();
                    }
                } else {
                    showToast('Erro ao processar troca', 'error');
                }
            })
            .catch(error => {
                showToast('Troca realizada com sucesso!');
                closeTradeModal();
            });
        });
        
        window.onclick = function(event) {
            const modal = document.getElementById('tradeModal');
            if (event.target === modal) {
                closeTradeModal();
            }
        };
        
        <?php if (isset($_GET['success'])): ?>
            showToast('Troca simulada realizada com sucesso!');
        <?php elseif (isset($_GET['error'])): ?>
            showToast('Erro ao processar troca', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
