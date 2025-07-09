<?php
ob_start(); // Inicia o buffer de saída para evitar problemas de headers
session_start();

require_once 'connect.php';

if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header("Location: login.php");
    exit();
}

$idCliente = $_SESSION['usuario']['id'];
$mensagem = "";
$alertClass = "";

$stmt = $conn->prepare("SELECT * FROM clientes WHERE idCliente = ?");
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
} else {
    echo "Cliente não encontrado!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido!";
        $alertClass = "alert-danger";
    } else {
        $stmt = $conn->prepare("SELECT idCliente FROM clientes WHERE email = ? AND idCliente != ?");
        $stmt->bind_param("si", $email, $idCliente);
        $stmt->execute();
        $emailResult = $stmt->get_result();

        if ($emailResult->num_rows > 0) {
            $mensagem = "Este e-mail já está sendo usado por outra conta!";
            $alertClass = "alert-danger";
        } else {
            $senhaAtual = $_POST['senha_atual'] ?? '';
            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            $senhaAlterada = false;

            if (!empty($senhaAtual) && !empty($novaSenha) && !empty($confirmarSenha)) {
                if (password_verify($senhaAtual, $cliente['senha_hash'])) {
                    if ($novaSenha === $confirmarSenha) {
                        $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                        $senhaAlterada = true;
                    } else {
                        $mensagem = "A nova senha e a confirmação não coincidem!";
                        $alertClass = "alert-danger";
                    }
                } else {
                    $mensagem = "Senha atual incorreta!";
                    $alertClass = "alert-danger";
                }
            }

            if (empty($mensagem)) {
                if ($senhaAlterada) {
                    $stmt = $conn->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, cpf = ?, senha_hash = ?, ultimoAcesso = NOW() WHERE idCliente = ?");
                    $stmt->bind_param("sssssi", $nome, $email, $telefone, $cpf, $novaSenhaHash, $idCliente);
                } else {
                    $stmt = $conn->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, cpf = ?, ultimoAcesso = NOW() WHERE idCliente = ?");
                    $stmt->bind_param("ssssi", $nome, $email, $telefone, $cpf, $idCliente);
                }

                if ($stmt->execute()) {
                    $mensagem = "Perfil atualizado com sucesso!";
                    $alertClass = "alert-success";

                    $cliente['nome'] = $nome;
                    $cliente['email'] = $email;
                    $cliente['telefone'] = $telefone;
                    $cliente['cpf'] = $cpf;
                    $cliente['ultimoAcesso'] = date('Y-m-d H:i:s');

                    $_SESSION['usuario']['nome'] = $nome;
                    $_SESSION['usuario']['email'] = $email;
                } else {
                    $mensagem = "Erro ao atualizar o perfil: " . $conn->error;
                    $alertClass = "alert-danger";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="css/profile2.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #081f4d;
        }
    </style>
</head>
<body>

<?php include "navbar/nav.php"; ?>

<div class="user-profile">
    <div class="user-info">
        <div class="row">
            <div class="profile-photo-container">
                <?php
                $fotoPerfil = !empty($_SESSION['usuario']['foto']) ? $_SESSION['usuario']['foto'] : 'uploads/default.png';
                ?>
                <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" class="profile2-photo" alt="Foto de perfil">
            </div>
            <div class="user-details">
                <h2><?php echo htmlspecialchars($cliente['nome']); ?></h2>
                <p class="text-muted">
                    <span class="user-status-dot user-status-<?php echo strtolower($cliente['status']); ?>"></span>
                    Status: <?php echo $cliente['status']; ?>
                </p>
                <p>Cliente desde: <?php echo date('d/m/Y', strtotime($cliente['dataCadastro'])); ?></p>
                <p>Último acesso:
                    <?php echo $cliente['ultimoAcesso'] ? date('d/m/Y H:i', strtotime($cliente['ultimoAcesso'])) : 'Nunca'; ?>
                </p>
            </div>
            <div class="currency-container">
                <div class="user-currency">
                    <i class="bi bi-coin"></i> <?php echo $cliente['moedas']; ?> moedas
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert <?php echo $alertClass; ?>" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-block">
            <h3 class="section-title">Informações Pessoais</h3>
            <div class="form-row">
                <div class="form-column">
                    <label for="nome" class="field-label">Nome Completo</label>
                    <input type="text" class="input-field" id="nome" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                </div>
                <div class="form-column">
                    <label for="email" class="field-label">E-mail</label>
                    <input type="email" class="input-field" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column">
                    <label for="telefone" class="field-label">Telefone</label>
                    <input type="tel" class="input-field" id="telefone" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>">
                </div>
                <div class="form-column">
                    <label for="cpf" class="field-label">CPF</label>
                    <input type="text" class="input-field" id="cpf" name="cpf" value="<?php echo htmlspecialchars($cliente['cpf'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-block">
            <h3 class="section-title">Alterar Senha</h3>
            <div class="form-row">
                <div class="form-column-third">
                    <label for="senha_atual" class="field-label">Senha Atual</label>
                    <input type="password" class="input-field" id="senha_atual" name="senha_atual">
                </div>
                <div class="form-column-third">
                    <label for="nova_senha" class="field-label">Nova Senha</label>
                    <input type="password" class="input-field" id="nova_senha" name="nova_senha">
                </div>
                <div class="form-column-third">
                    <label for="confirmar_senha" class="field-label">Confirmar Nova Senha</label>
                    <input type="password" class="input-field" id="confirmar_senha" name="confirmar_senha">
                </div>
            </div>
            <div class="help-text">Deixe os campos em branco para manter a senha atual.</div>
        </div>

        <div class="button-container">
            <button type="submit" class="primary-button">Salvar Alterações</button>
            <a href="index.php" class="secondary-button">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
ob_end_flush(); // Envia o buffer de saída
?>
