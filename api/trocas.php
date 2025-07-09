<?php
include 'connect.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['usuario']['id']; 
$current_user_name = $_SESSION['usuario']['nome']; 

$sql = "SELECT idCliente, nome, tipo_usuario, status FROM clientes WHERE status = 'Ativo'";
$result = $conn->query($sql);

$sql_produtos = "SELECT idProduto, nome FROM produtos WHERE categoria = 'Jogos' AND status = 'Disponível' ORDER BY nome ASC";
$produtos_result = $conn->query($sql_produtos);

$jogos = array();
if ($produtos_result && $produtos_result->num_rows > 0) {
    while ($produto = $produtos_result->fetch_assoc()) {
        $jogos[$produto['idProduto']] = $produto['nome'];
    }
}

$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    $message = 'Proposta enviada com sucesso!';
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    $message = 'Erro ao enviar proposta. Por favor, tente novamente.';
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Proposta de Troca</title>
    <link rel="stylesheet" href="css/trade.css">
    <style>
        /* Estilos para a notificação temporária */
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
        
        .simulated-trade-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .simulated-trade-section h2 {
            color: white;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .simulated-trade-section p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .simulated-trade-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .simulated-trade-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .section-divider {
            text-align: center;
            margin: 40px 0;
            position: relative;
        }
        
        .section-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #ddd, transparent);
        }
        
        .section-divider span {
            background: white;
            padding: 0 20px;
            color: #666;
            font-weight: 500;
        }
        
        .users-section h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
        <?php
        include "navbar/nav.php"
        ?>
    
    <div class="container">
        <h1>Sistema de Trocas</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Seção de Troca Simulada -->
        <div class="simulated-trade-section">
            <h2>🎮 Troca Simulada</h2>
            <p>Receba propostas automáticas de outros jogadores e pratique o sistema de trocas!</p>
            <a href="trocasimulada.php" class="simulated-trade-button">
                🎯 Começar Troca Simulada
            </a>
        </div>
        
        <div class="section-divider">
            <span>OU</span>
        </div>
        
        <!-- Seção de Usuários Reais -->
        <div class="users-section">
            <h2>👥 Enviar Proposta para Usuários</h2>
            
            <?php
            if ($result && $result->num_rows > 0) {
                $user_count = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($row['idCliente'] == $current_user_id) {
                        continue;
                    }
                    
                    $user_count++;
                    
                    $avatar_html = '';
                    if ($row['idCliente'] % 3 == 1) {  
                        $avatar_html = '<div class="default-avatar">👤</div>';
                    } elseif ($row['idCliente'] % 3 == 2) {
                        $avatar_html = '<div class="default-avatar">👨</div>';
                    } else {
                        $avatar_html = '<div class="default-avatar">👩</div>';
                    }
                    
                    echo '<div class="user-card" onclick="openModal(' . $row['idCliente'] . ', \'' . htmlspecialchars($row['nome']) . '\')">
                            <div class="user-avatar">' . $avatar_html . '</div>
                            <div class="user-name">' . htmlspecialchars($row['nome']) . '</div>
                          </div>';
                }
                
                if ($user_count == 0) {
                    echo '<div class="no-users">Nenhum outro usuário disponível para troca</div>';
                }
            } else {
                echo '<div class="no-users">Nenhum usuário disponível para troca</div>';
            }
            ?>
        </div>
    </div>
    
    <div id="proposalModal" class="modal">
        <div class="modal-content">
            <h2 class="modal-title">Enviar proposta para <span id="receiverName"></span></h2>
            
            <form id="proposalForm" method="post" action="process_proposal.php">
                <input type="hidden" id="receiverId" name="receiver_id">
                
                <div class="form-group">
                    <label for="proposalDescription">Descrição da proposta:</label>
                    <textarea id="proposalDescription" name="description" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="exchangeType">Selecione o jogo para troca:</label>
                    <select id="exchangeType" name="exchange_type" required onchange="toggleCoinsField()">
                        <option value="">Selecione um jogo</option>
                        <?php
                        if (!empty($jogos)) {
                            foreach ($jogos as $id => $nome) {
                                echo '<option value="jogo_' . $id . '">' . htmlspecialchars($nome) . '</option>';
                            }
                        } else {
                            echo '<option value="product">Produto por produto</option>';
                            echo '<option value="service">Serviço por serviço</option>';
                            echo '<option value="mixed">Produto por serviço</option>';
                        }
                        ?>
                        <option value="coins">Moedas</option>
                    </select>
                </div>
                
                <div id="coinsField" class="form-group coins-field">
                    <label for="coinsAmount">Quantidade de moedas:</label>
                    <input type="number" id="coinsAmount" name="coins_amount" min="1" value="1">
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Enviar proposta</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
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
        
        function openModal(userId, userName) {
            document.getElementById('receiverId').value = userId;
            document.getElementById('receiverName').textContent = userName;
            document.getElementById('proposalModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('proposalModal').style.display = 'none';
            document.getElementById('proposalForm').reset();
            document.getElementById('coinsField').style.display = 'none';
        }
        
        function toggleCoinsField() {
            const exchangeType = document.getElementById('exchangeType').value;
            const coinsField = document.getElementById('coinsField');
            
            if (exchangeType === 'coins') {
                coinsField.style.display = 'block';
            } else {
                coinsField.style.display = 'none';
            }
        }
        
        document.getElementById('proposalForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_proposal.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Proposta de troca enviada');
                    closeModal();
                } else {
                    showToast('Erro ao enviar proposta', 'error');
                }
            })
            .catch(error => {
                showToast('Proposta de troca enviada');
                closeModal();
            });
        });
        
        window.onclick = function(event) {
            const modal = document.getElementById('proposalModal');
            if (event.target === modal) {
                closeModal();
            }
        };
        
        <?php if (isset($_GET['success'])): ?>
            showToast('Proposta de troca enviada');
        <?php elseif (isset($_GET['error'])): ?>
            showToast('Erro ao enviar proposta', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
