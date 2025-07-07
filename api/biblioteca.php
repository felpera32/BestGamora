<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['biblioteca'])) {
    $_SESSION['biblioteca'] = [];
}

$bibliotecaVazia = empty($_SESSION['biblioteca']);

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gamoraloja');

// Função para obter a imagem principal do produto
function getImagemPrincipal($idProduto) {
    try {
        // Criar conexão específica para esta função
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            throw new Exception("Erro de conexão: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset("utf8");
        
        // Buscar na tabela imagensproduto primeiro
        $sql = "SELECT urlImagem FROM imagensproduto WHERE idProduto = ? AND ordemExibicao = 1 AND status = 'Ativa' LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $idProduto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $imagePath = $row['urlImagem'];
                $stmt->close();
                $mysqli->close();
                return $imagePath;
            }
            $stmt->close();
        }
        
        // Fallback para tabela produtos
        $sql = "SELECT imagemPrincipal FROM produtos WHERE idProduto = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $idProduto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $imagePath = $row['imagemPrincipal'] ? $row['imagemPrincipal'] : 'imagens/placeholder.jpg';
                $stmt->close();
                $mysqli->close();
                return $imagePath;
            }
            $stmt->close();
        }
        
        $mysqli->close();
        
    } catch (Exception $e) {
        error_log("Erro ao buscar imagem do produto $idProduto: " . $e->getMessage());
    }
    
    return 'imagens/placeholder.jpg';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minha Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/biblioteca.css">
    <?php include "navbar/nav.php"; ?>
</head>
<body>
    <div class="biblioteca-container">
        <div class="biblioteca-header">
            <h2>Minha Biblioteca</h2>
            <div class="nav-links">
                <a href="biblioteca.php" class="nav-link active">Biblioteca</a>
                <a href="lista_desejos.php" class="nav-link">Lista de desejos</a>
                <a href="trocas.php" class="nav-link">Trocas</a>
                <div class="user-info">
                    <span>Olá, <?php echo isset($_SESSION['nome_usuario']) ? htmlspecialchars($_SESSION['nome_usuario']) : 'Usuário'; ?></span>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['compra_finalizada']) && $_SESSION['compra_finalizada']): ?>
            <div class="success-message">
                <p>Sua compra foi finalizada com sucesso! Os jogos foram adicionados à sua biblioteca.</p>
            </div>
            <?php unset($_SESSION['compra_finalizada']); ?>
        <?php endif; ?>

        <?php if ($bibliotecaVazia): ?>
            <div class="empty-biblioteca-message">
                <p>Sua biblioteca está vazia.</p>
                <p>Visite a <a href="index.php" class="nav-link">loja</a> para adicionar jogos.</p>
            </div>
        <?php else: ?>
            <div class="jogos-grid">
                <?php foreach ($_SESSION['biblioteca'] as $id => $jogo): 
                    // Obter a imagem do banco de dados
                    $imagePath = getImagemPrincipal($id);
                ?>
                    <div class="jogo-card">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($jogo['nome']); ?>" 
                             class="jogo-imagem"
                             onerror="this.src='imagens/placeholder.jpg';">
                        <div class="jogo-info">
                            <h3><?php echo htmlspecialchars($jogo['nome']); ?></h3>
                            <p>Adquirido em: <?php echo date('d/m/Y', strtotime($jogo['data_compra'])); ?></p>
                            <button class="install-button">Instalar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>