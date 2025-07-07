<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gamoraloja";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$nomeUsuario = $_SESSION['usuario']['nome'];

$sql = "SELECT * FROM produtos WHERE fabricante = ? ORDER BY dataCadastro DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nomeUsuario);
$stmt->execute();
$result = $stmt->get_result();
include "navbar/sellernav.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Jogos Publicados</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/products.css">
    
    <style>
        .produto-card {
            margin-bottom: 20px;
        }
        .produto-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Meus Jogos Publicados</h2>

    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['mensagem'] ?>
        </div>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($produto = $result->fetch_assoc()): ?>
                <div class="produto-card">
                    <div class="card">
                        <img src="<?= htmlspecialchars($produto['imagemPrincipal']) ?>" class="card-img-top produto-img" alt="<?= htmlspecialchars($produto['nome']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars(substr($produto['descricao'], 0, 100))) ?>...</p>
                            <p class="card-text"><strong>Preço:</strong> R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                            <p class="card-text"><strong>Status:</strong> <?= htmlspecialchars($produto['status']) ?></p>
                            <!-- Aqui você pode adicionar botões para editar/excluir -->
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Você ainda não publicou nenhum jogo.</div>
    <?php endif; ?>

</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
