<?php
session_start();

require_once 'connect.php';

if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}

function fazerLogin($conn, $email, $senha) {
    // Preparar consulta
    $query = "SELECT * FROM clientes WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        // bglh da mostrar o erro
        die("Erro na preparação da consulta: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    
    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        unset($usuario['senha_hash']);
        
        $_SESSION['usuario'] = [
            'id' => $usuario['idCliente'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo_usuario' => $usuario['tipo_usuario']
        ];
        
        // CORREÇÃO: Definir todas as variáveis que o carrinho espera
        $_SESSION['usuario_logado'] = true;
        $_SESSION['nome_usuario'] = $usuario['nome'];
        $_SESSION['id_usuario'] = $usuario['idCliente']; // ← ESTA LINHA ESTAVA FALTANDO!
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        
        // Debug - log das sessões criadas
        error_log("Login realizado com sucesso:");
        error_log("- usuario_logado: " . var_export($_SESSION['usuario_logado'], true));
        error_log("- id_usuario: " . $_SESSION['id_usuario']);
        error_log("- nome_usuario: " . $_SESSION['nome_usuario']);
        error_log("- tipo_usuario: " . $_SESSION['tipo_usuario']);
        
        return true;
    }
    
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['passwd']) ? $_POST['passwd'] : '';

    if (empty($email) || empty($senha)) {
        header("Location: login.php?erro=campos_vazios");
        exit();
    } else {
        if (fazerLogin($conn, $email, $senha)) {
            // Verificar o tipo de usuário e redirecionar adequadamente
            if ($_SESSION['tipo_usuario'] == 'vendedor') {
                header("Location: vendedor.php");
            } else {
                // Para clientes/compradores
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
}

$erro = isset($_GET['erro']) ? $_GET['erro'] : null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <title>Login</title>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li>
                    <a href="index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" class="linkvoltar" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18" />
                        </svg>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    
    <div class="container">
        <h1 class="tracking-in-expand">GAMORA</h1>
        <h1 class="tracking-in-expand">Entrar</h1>

        <?php
        // Mostrar mensagens de erro
        if ($erro === 'campos_vazios') {
            echo '<div style="color: red; text-align: center;">Por favor, preencha todos os campos.</div>';
        } elseif ($erro === 'credenciais_invalidas') {
            echo '<div style="color: red; text-align: center;">Email ou senha inválidos.</div>';
        }
        ?>

        <div class="form-container">
            <form action="login.php" method="post">
                <input type="email" name="email" placeholder="E-Mail" required>
                <input type="password" name="passwd" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
        </div>
        <p>Não tem uma conta? <a href="reg.php">Registrar</a></p>
        
        <!-- Debug info temporário - remova depois -->
        <div style="margin-top: 20px; padding: 10px; background: #f0f0f0; font-size: 12px;">
            <strong>Debug Info:</strong><br>
            Session ID: <?php echo session_id(); ?><br>
            Usuário Logado: <?php echo isset($_SESSION['usuario_logado']) ? ($_SESSION['usuario_logado'] ? 'SIM' : 'NÃO') : 'NÃO DEFINIDO'; ?><br>
            ID Usuário: <?php echo isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 'NÃO DEFINIDO'; ?><br>
            <a href="debug_session.php">Ver Debug Completo</a>
        </div>
    </div>
</body>
</html>

<?php
// Fechar conexão
mysqli_close($conn);
?>