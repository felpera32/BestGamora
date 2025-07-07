<?php
session_start();

include "connect.php";

if (!$conn) {
    die("Erro na conexão com o banco de dados");
}

$user_id = $_SESSION['user_id'] ?? 1; 

$query_meus_jogos = "SELECT p.idProduto, p.nome, p.preco, p.imagemPrincipal 
                    FROM produtos p 
                    INNER JOIN itens_venda iv ON p.idProduto = iv.idProduto 
                    INNER JOIN vendas v ON iv.idVenda = v.idVenda 
                    WHERE v.idCliente = ?";

$stmt = $conn->prepare($query_meus_jogos);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$meus_jogos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar TODOS os usuários que compraram jogos (não apenas os diferentes do usuário atual)
$query_usuarios = "SELECT DISTINCT c.idCliente, c.nome 
                  FROM clientes c 
                  INNER JOIN vendas v ON c.idCliente = v.idCliente 
                  WHERE c.idCliente != ?
                  ORDER BY c.nome";

$stmt_usuarios = $conn->prepare($query_usuarios);
$stmt_usuarios->bind_param("i", $user_id);
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug: verificar se há usuários
// echo "<pre>Usuários encontrados: " . print_r($usuarios, true) . "</pre>";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troca Simulada</title>
    <link rel="stylesheet" href="css/trocasimulada.css">
</head>
<body>
    <?php include "navbar/nav.php"; ?>

    <div class="container">
        <div class="price-validation-info">
            <h4>💡 Regra de Troca por Faixa de Preço</h4>
            <p>Jogos podem ser trocados apenas dentro da mesma faixa de preço (±20%). 
            Exemplo: Um jogo de R$ 299,00 pode ser trocado por outro entre R$ 239,20 e R$ 358,80.</p>
        </div>

        <div class="trade-container" id="trade-container">
            <!-- Seção do usuário atual -->
            <div class="user-section">
                <div class="user-header">
                    <div class="avatar">👤</div>
                    <div class="user-name">Meus Jogos</div>
                </div>

                <div id="meus-jogos">
                    <?php if (empty($meus_jogos)): ?>
                        <div class="no-games">Você não possui jogos para trocar</div>
                    <?php else: ?>
                        <?php foreach($meus_jogos as $jogo): 
                            $preco = (float)$jogo['preco']; // Garantir que é float
                            $min = number_format($preco * 0.8, 2, '.', '');
                            $max = number_format($preco * 1.2, 2, '.', '');
                            $preco_formatado = number_format($preco, 2, ',', '.');
                            $min_formatado = number_format($preco * 0.8, 2, ',', '.');
                            $max_formatado = number_format($preco * 1.2, 2, ',', '.');
                            
                            // Verificar se a imagem existe e criar caminho correto
                            $imagem_path = $jogo['imagemPrincipal'];
                            if (!empty($imagem_path)) {
                                // Se não começar com http, adicionar caminho relativo
                                if (!preg_match('/^https?:\/\//', $imagem_path)) {
                                    $imagem_path = 'uploads/produtos/' . basename($imagem_path);
                                }
                            }
                        ?>
                        <div class="game-item" onclick="selectGame(this, 'meu', <?= $jogo['idProduto'] ?>, <?= $preco ?>)" 
                             data-price="<?= $preco ?>" data-min="<?= $min ?>" data-max="<?= $max ?>">
                            <div class="game-image">
                                <?php if (!empty($imagem_path) && file_exists($imagem_path)): ?>
                                    <img src="<?= htmlspecialchars($imagem_path) ?>" alt="<?= htmlspecialchars($jogo['nome']) ?>" 
                                         onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'no-image\'>Sem Imagem</div>'">
                                <?php else: ?>
                                    <div class='no-image'>Sem Imagem</div>
                                <?php endif; ?>
                            </div>
                            <div class="game-info">
                                <h3><?= htmlspecialchars($jogo['nome']) ?></h3>
                                <div class="game-price">R$ <?= $preco_formatado ?></div>
                                <div class="price-range">Aceita: R$ <?= $min_formatado ?> - R$ <?= $max_formatado ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="value-section">
                    <div class="value-label">Valor dos jogos:</div>
                    <div class="value-amount" id="valor-meus-jogos">R$ 0,00</div>
                </div>
            </div>

            <!-- Seção das setas -->
            <div class="arrow-section">
                <div class="arrow">→</div>
                <div class="arrow">←</div>
                <div id="trade-status" class="trade-status" style="display: none;"></div>
            </div>

            <!-- Seção do outro usuário -->
            <div class="user-section">
                <div class="user-selector">
                    <select onchange="changeUser(this.value)">
                        <option value="">Selecione um usuário</option>
                        <?php if (empty($usuarios)): ?>
                            <option disabled>Nenhum usuário disponível</option>
                        <?php else: ?>
                            <?php foreach($usuarios as $usuario): ?>
                            <option value="<?= $usuario['idCliente'] ?>"><?= htmlspecialchars($usuario['nome']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="user-header" id="other-user-header" style="display: none;">
                    <div class="avatar">👤</div>
                    <div class="user-name" id="other-user-name">Usuário</div>
                </div>

                <div id="jogos-outros">
                    <div class="no-games">Selecione um usuário para ver seus jogos</div>
                </div>

                <div class="value-section">
                    <div class="value-label">Valor dos jogos:</div>
                    <div class="value-amount" id="valor-outros-jogos">R$ 0,00</div>
                </div>
            </div>
        </div>

        <button class="confirm-button" onclick="confirmarTroca()" id="confirm-btn" disabled>
            Confirmar Troca
        </button>
        
        <!-- Debug info (remover em produção) -->
        <div style="margin-top: 20px; padding: 10px; background: #f0f0f0; font-size: 10px;">
            <strong>Debug Info:</strong><br>
            User ID: <?= $user_id ?><br>
            Meus jogos: <?= count($meus_jogos) ?><br>
            Usuários disponíveis: <?= count($usuarios) ?><br>
            <?php if (!empty($usuarios)): ?>
                Usuários: <?= implode(', ', array_column($usuarios, 'nome')) ?>
            <?php endif; ?>
        </div>
    </div>  
    
    <script src="js/trocasimulada.js"></script>

    <!-- Script para passar dados PHP para JavaScript -->
    <script>
        const meusJogosData = <?= json_encode($meus_jogos) ?>;
        const currentUserId = <?= $user_id ?>;
        
        // Debug no console
        console.log('Meus jogos:', meusJogosData);
        console.log('Current user ID:', currentUserId);
    </script>
</body>
</html>