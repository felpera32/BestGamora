<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gamoraloja";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    $nome = mysqli_real_escape_string($conn, $_POST['game_name']);
    $categoria = mysqli_real_escape_string($conn, $_POST['category']);
    $preco = floatval($_POST['price']);
    $descricao = mysqli_real_escape_string($conn, $_POST['description']);
    $fabricante = $_SESSION['usuario']['nome']; // <- Alterado aqui
    $estoque = 1;
    $status = "Disponível";

    $tags = isset($_POST['tags']) ? $_POST['tags'] : '[]';
    $tagsArray = json_decode($tags, true);
    $tagsString = !empty($tagsArray) ? ' Tags: ' . implode(', ', $tagsArray) : '';

    $descricaoCompleta = $descricao;

    if (!empty($_POST['system_requirements'])) {
        $descricaoCompleta .= "\n\n--- REQUISITOS DE SISTEMA ---\n" . $_POST['system_requirements'];
    }

    if (!empty($_POST['gameplay_features'])) {
        $descricaoCompleta .= "\n\n--- CARACTERÍSTICAS DE GAMEPLAY ---\n" . $_POST['gameplay_features'];
    }

    if (!empty($tagsString)) {
        $descricaoCompleta .= "\n\n" . $tagsString;
    }

    $imagemPrincipal = "default_game.jpg";

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/jogos/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extensao = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid('game_') . '.' . $extensao;
        $uploadFile = $uploadDir . $nomeArquivo;

        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
            $imagemPrincipal = $uploadFile;
        } else {
            $erro = "Falha ao fazer upload da imagem principal.";
        }
    }

    $sql = "INSERT INTO produtos (nome, categoria, preco, estoque, imagemPrincipal, descricao, fabricante, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdissss", $nome, $categoria, $preco, $estoque, $imagemPrincipal, $descricaoCompleta, $fabricante, $status);

    if ($stmt->execute()) {
        $idProduto = $conn->insert_id;

        if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
            $screenshotDir = "uploads/jogos/screenshots/";

            if (!file_exists($screenshotDir)) {
                mkdir($screenshotDir, 0777, true);
            }

            for ($i = 0; $i < count($_FILES['screenshots']['name']); $i++) {
                if ($_FILES['screenshots']['error'][$i] === UPLOAD_ERR_OK) {
                    $extensao = pathinfo($_FILES['screenshots']['name'][$i], PATHINFO_EXTENSION);
                    $nomeArquivo = uniqid('screenshot_') . '.' . $extensao;
                    $uploadFile = $screenshotDir . $nomeArquivo;

                    if (move_uploaded_file($_FILES['screenshots']['tmp_name'][$i], $uploadFile)) {
                        // Aqui você pode inserir na tabela de screenshots se quiser
                    }
                }
            }
        }

        $_SESSION['mensagem'] = "Jogo publicado com sucesso!";
        header("Location: meus_produtos.php");
        exit();
    } else {
        $_SESSION['erro'] = "Erro ao publicar jogo: " . $stmt->error;
        header("Location: vendedor.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: vendedor.php");
    exit();
}
?>
