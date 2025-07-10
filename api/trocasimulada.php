<?php
session_start();
include 'connect.php';

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
            transition: all 0.3s ease;
        }
        
        .proposal-card.accepted {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
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
        
        .game-selection {
            margin-top: 15px;
            padding: 15px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }
        
        .game-selection label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .game-selection select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            idth: 100%;
            color: black;
        }
        
        .game-selection select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .game-selection select option {
            padding: 10px;
        }
        
        .price-range-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
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
        
        .btn-accept:disabled {
            background: #6c757d;
            cursor: not-allowed;
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
        
        .accepted-status {
            text-align: center;
            padding: 25px;
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            color: #155724;
            font-weight: bold;
            font-size: 18px;
        }
        
        .accepted-status::before {
            content: "✓ ";
            font-size: 24px;
            color: #28a745;
        }
        
        .trade-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        
        .trade-summary h4 {
            margin: 0 0 10px 0;
            color: #28a745;
            font-size: 16px;
        }
        
        .trade-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .trade-details span {
            font-size: 14px;
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
        
        .fade-out {
            opacity: 0;
            transform: translateY(-20px);
            pointer-events: none;
        }
        
        .fade-in {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <?php include "navbar/nav.php"; ?>
    
    <div class="container">
        <a href="trocas.php" class="back-button">← Voltar para Trocas</a>
        
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
                    
                    $proposal_id = 'proposal_' . $i;
                    
                    echo '<div class="proposal-card" id="' . $proposal_id . '">
                            <div class="proposal-header">
                                <div class="proposer-avatar">' . $avatar . '</div>
                                <div class="proposer-info">
                                    <h3>' . htmlspecialchars($cliente['nome']) . '</h3>
                                    <p>Quer trocar com você</p>
                                </div>
                            </div>
                            
                            <div class="proposal-content">
                                <div class="proposal-details">
                                    <div class="game-info">
                                        <span class="game-name">' . htmlspecialchars($cliente['nome']) . ' oferece:</span>
                                        <span class="game-price">R$ ' . number_format($jogo_desejado['preco'], 2, ',', '.') . '</span>
                                    </div>
                                    <div style="font-weight: bold; color: #007bff; margin-bottom: 10px;">' . htmlspecialchars($jogo_desejado['nome']) . '</div>
                                    
                                    <div class="exchange-arrow">⬇️ Em troca de ⬇️</div>
                                    
                                    <div class="game-selection">
                                        <label for="game_select_' . $i . '">Escolha um jogo na faixa de preço permitida (±20%):</label>
                                        <select id="game_select_' . $i . '" onchange="updateAcceptButton(' . $i . ')">
                                            <option value="">Selecione um jogo...</option>';
                    
                    foreach ($jogos_compativeis as $jogo_compativel) {
                        echo '<option value="' . $jogo_compativel['idProduto'] . '" data-price="' . $jogo_compativel['preco'] . '" data-name="' . htmlspecialchars($jogo_compativel['nome']) . '">' 
                             . htmlspecialchars($jogo_compativel['nome']) . ' - R$ ' 
                             . number_format($jogo_compativel['preco'], 2, ',', '.') . '</option>';
                    }
                    
                    echo '          </select>
                                        <div class="price-range-info">
                                            Faixa de preço: R$ ' . number_format($preco_min, 2, ',', '.') . ' - R$ ' . number_format($preco_max, 2, ',', '.') . '
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="proposal-actions">
                                    <button class="btn-decline" onclick="declineProposal(this)">Recusar</button>
                                    <button class="btn-accept" id="accept_btn_' . $i . '" disabled onclick="acceptTrade(' . $i . ', ' . $jogo_desejado['idProduto'] . ', \'' . htmlspecialchars($jogo_desejado['nome']) . '\', ' . $jogo_desejado['preco'] . ', \'' . htmlspecialchars($cliente['nome']) . '\')">Aceitar</button>
                                </div>
                            </div>
                        </div>';
                }
            } else {
                echo '<div class="no-proposals">Nenhuma proposta de troca disponível no momento.</div>';
            }
            ?>
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
        
        function updateAcceptButton(proposalIndex) {
            const select = document.getElementById('game_select_' + proposalIndex);
            const acceptBtn = document.getElementById('accept_btn_' + proposalIndex);
            
            if (select.value) {
                acceptBtn.disabled = false;
                acceptBtn.style.background = '#28a745';
            } else {
                acceptBtn.disabled = true;
                acceptBtn.style.background = '#6c757d';
            }
        }
        
        function acceptTrade(proposalIndex, offeredGameId, offeredGameName, offeredGamePrice, proposerName) {
            const select = document.getElementById('game_select_' + proposalIndex);
            const selectedOption = select.options[select.selectedIndex];
            
            if (!select.value) {
                showToast('Por favor, selecione um jogo para trocar.', 'error');
                return;
            }
            
            const userGameName = selectedOption.getAttribute('data-name');
            const userGamePrice = selectedOption.getAttribute('data-price');
            
            const proposalCard = document.getElementById('proposal_' + proposalIndex);
            const proposalContent = proposalCard.querySelector('.proposal-content');
            
            proposalContent.classList.add('fade-out');
            
            setTimeout(() => {
                proposalCard.classList.add('accepted');
                
                const acceptedContent = document.createElement('div');
                acceptedContent.innerHTML = `
                    <div class="accepted-status">
                        Proposta Aceita
                    </div>
                    
                    <div class="trade-summary">
                        <h4>Resumo da Troca</h4>
                        <div class="trade-details">
                            <span><strong>Você recebeu:</strong></span>
                            <span>${offeredGameName} - R$ ${parseFloat(offeredGamePrice).toFixed(2).replace('.', ',')}</span>
                        </div>
                        <div class="trade-details">
                            <span><strong>Você ofereceu:</strong></span>
                            <span>${userGameName} - R$ ${parseFloat(userGamePrice).toFixed(2).replace('.', ',')}</span>
                        </div>
                        <div class="trade-details">
                            <span><strong>Trocado com:</strong></span>
                            <span>${proposerName}</span>
                        </div>
                    </div>
                `;
                
                proposalCard.removeChild(proposalContent);
                proposalCard.appendChild(acceptedContent);
                
                acceptedContent.classList.add('fade-in');
                
                showToast('Troca realizada com sucesso!');
                
            }, 300);
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
        
        <?php if (isset($_GET['success'])): ?>
            showToast('Troca simulada realizada com sucesso!');
        <?php elseif (isset($_GET['error'])): ?>
            showToast('Erro ao processar troca', 'error');
        <?php endif; ?>
    </script>
</body>
</html>

