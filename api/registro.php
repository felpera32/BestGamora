<?php
if (isset($_POST['email']) && isset($_POST['senha']) && isset($_POST['nickname']) && isset($_POST['phone']) && isset($_POST['cpf']) && isset($_POST['userType'])) {
    include_once "connect.php";

    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $user = $_POST['nickname'];
    $password = $_POST['senha'];
    $phone = $_POST['phone'];
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $userType = $_POST['userType'];

    // Corrigido: Removido espaço no nome da variável
    $dbUserType = ($userType == "anunciante") ? "vendedor" : "comprador";
    $cript = password_hash($password, PASSWORD_DEFAULT);

    // Verificação de duplicidade (exemplo)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM `clientes` WHERE email = ? OR cpf = ?");
    $stmt->bind_param("ss", $email, $cpf);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "E-mail ou CPF já cadastrados!";
    } else {
        $stmt = $conn->prepare("INSERT INTO `clientes`(`nome`, `email`, `telefone`, `senha_hash`, `cpf`, `tipo_usuario`) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Erro no prepare: " . $conn->error);
        }

        $stmt->bind_param("ssssss", $user, $email, $phone, $cript, $cpf, $dbUserType);

        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
            if ($dbUserType == "vendedor") {
                header("location: vendedor.php");
            } else {
                header("location: index.php");
            }
            exit();
        } else {
            echo "Erro ao cadastrar: " . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
} else {
    echo "Todos os campos são obrigatórios!";
}
?>