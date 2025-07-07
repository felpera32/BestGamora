<?php
session_start();
require_once 'connect.php';

if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}

function fazerLogin($conn, $email, $senha) {
    $query = "SELECT * FROM clientes WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Erro na preparação da consulta: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        unset($usuario['senha_hash']);

        $_SESSION['usuario'] = [
            'id' => $usuario['idCliente'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo_usuario' => $usuario['tipo_usuario']
        ];

        $_SESSION['usuario_logado'] = true;
        $_SESSION['nome_usuario'] = $usuario['nome'];
        $_SESSION['id_usuario'] = $usuario['idCliente'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

        error_log("Login realizado com sucesso:");
        error_log("- usuario_logado: " . var_export($_SESSION['usuario_logado'], true));
        error_log("- id_usuario: " . $_SESSION['id_usuario']);
        error_log("- nome_usuario: " . $_SESSION['nome_usuario']);
        error_log("- tipo_usuario: " . $_SESSION['tipo_usuario']);

        return true;
    }

    return false;
}

$email = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['passwd'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: login.php?erro=campos_vazios");
        exit();
    }

    if (fazerLogin($conn, $email, $senha)) {
        if ($_SESSION['tipo_usuario'] == 'vendedor') {
            header("Location: vendedor.php");
        } else {
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
            } else {
                header("Location: index.php");
            }
        }
        exit();
    } else {
        header("Location: login.php?erro=credenciais_invalidas");
        exit();
    }
}

$erro = htmlspecialchars($_GET['erro'] ?? '');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gamora</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li>
                    <a href="index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" class="linkvoltar" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="tracking-in-expand">GAMORA</h1>
        <h1 class="tracking-in-expand">Entrar</h1>

        <?php if ($erro === 'campos_vazios'): ?>
            <div style="color: red; text-align: center;">Por favor, preencha todos os campos.</div>
        <?php elseif ($erro === 'credenciais_invalidas'): ?>
            <div style="color: red; text-align: center;">Email ou senha inválidos.</div>
        <?php endif; ?>

        <div class="form-container">
            <form action="login.php" method="post">
                <input type="email" name="email" placeholder="E-Mail" required value="<?= htmlspecialchars($email) ?>">
                <input type="password" name="passwd" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
        </div>
        <p>Não tem uma conta? <a href="reg.php">Registrar</a></p>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
