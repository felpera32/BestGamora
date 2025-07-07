<?php
session_start();
$mensagemErro = "";
$valores = [
    'nickname' => '',
    'email' => '',
    'phone' => '',
    'cpf' => '',
    'userType' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once "connect.php";

    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Armazenar valores para preencher o formulário em caso de erro
    $valores['email'] = $_POST['email'] ?? '';
    $valores['nickname'] = $_POST['nickname'] ?? '';
    $valores['phone'] = $_POST['phone'] ?? '';
    $valores['cpf'] = $_POST['cpf'] ?? '';
    $valores['userType'] = $_POST['userType'] ?? '';

    // Verificar se todos os campos foram enviados
    if (isset($_POST['email']) && isset($_POST['senha']) && isset($_POST['nickname']) && isset($_POST['phone']) && isset($_POST['cpf']) && isset($_POST['userType'])) {
        $email = $_POST['email'];
        $user = $_POST['nickname'];
        $password = $_POST['senha'];
        $phone = $_POST['phone'];
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $userType = $_POST['userType'];

        $dbUserType = ($userType == "anunciante") ? "vendedor" : "comprador";
        $cript = password_hash($password, PASSWORD_DEFAULT);

        // Verificar cada campo individualmente para mensagens de erro específicas
        $camposUtilizados = [];
        
        // Verifica o email
        $stmt = $conn->prepare("SELECT COUNT(*) FROM `clientes` WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $camposUtilizados[] = "E-mail";
        }
        
        // Verifica o CPF
        $stmt = $conn->prepare("SELECT COUNT(*) FROM `clientes` WHERE cpf = ?");
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $camposUtilizados[] = "CPF";
        }
        
        // Verifica o nome/nickname
        $stmt = $conn->prepare("SELECT COUNT(*) FROM `clientes` WHERE nome = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $camposUtilizados[] = "Nome";
        }
        
        // Verifica o telefone
        $stmt = $conn->prepare("SELECT COUNT(*) FROM `clientes` WHERE telefone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $camposUtilizados[] = "Telefone";
        }

        // Se houver campos já utilizados, cria a mensagem de erro
        if (!empty($camposUtilizados)) {
            $mensagemErro = implode(", ", $camposUtilizados) . " já utilizado(s)!";
        } else {
            // Nenhum campo duplicado, podemos inserir
            $stmt = $conn->prepare("INSERT INTO `clientes`(`nome`, `email`, `telefone`, `senha_hash`, `cpf`, `tipo_usuario`) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Erro no prepare: " . $conn->error);
            }

            $stmt->bind_param("ssssss", $user, $email, $phone, $cript, $cpf, $dbUserType);

            if ($stmt->execute()) {
                if ($dbUserType == "vendedor") {
                    header("location: vendedor.php");
                } else {
                    header("location: index.php");
                }
                exit();
            } else {
                $mensagemErro = "Erro ao cadastrar: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $mensagemErro = "Todos os campos são obrigatórios!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/registro.css">
    <script src="js/registro.js"></script>
    <title>Cadastro - GAMORA</title>
    <style>
        .hidden {
            display: none;
        }

        .container {
            margin-top: 7%;
        }
        
        .selected {
            background-color: #4CAF50;
            color: white;
        }
        
        .mensagem-erro {
            color: #ff0000;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>

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

    <div class="container" id="ct">
        <h1 class="tracking-in-expand">GAMORA</h1>
        <h1 class="tracking-in-expand">Registrar</h1>

        <?php if (!empty($mensagemErro)): ?>
        <div class="mensagem-erro"><?php echo $mensagemErro; ?></div>
        <?php endif; ?>

        <div id="buttons">
            <button onclick="showInputs('anunciante', event)">Sou Anunciante<br>Irei vender</button>
            <button onclick="showInputs('cliente', event)">Sou Cliente<br>Irei comprar</button>
        </div>

        <div class="form-container <?php echo empty($valores['userType']) ? 'hidden' : ''; ?>" id="inputFields">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- Campo oculto para o tipo de usuário -->
                <input type="hidden" name="userType" id="userType" value="<?php echo htmlspecialchars($valores['userType']); ?>">
                
                <input type="text" name="nickname" placeholder="Nome" required value="<?php echo htmlspecialchars($valores['nickname']); ?>">
                <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required value="<?php echo htmlspecialchars($valores['cpf']); ?>">
                <input type="tel" name="phone" id="phone" placeholder="(XX) XXXXX-XXXX" required value="<?php echo htmlspecialchars($valores['phone']); ?>">
                <input type="email" name="email" placeholder="E-Mail" required value="<?php echo htmlspecialchars($valores['email']); ?>">
                <input type="password" name="senha" placeholder="Senha" minlength="6" required 
                    title="A senha deve ter pelo menos 6 caracteres, incluindo letras e números.">
                <button type="submit">Entrar</button>
            </form>
        </div>
        
        <p>Já tem uma conta? <a href="login.php">Entrar</a></p>
    </div>
</body>
</html>