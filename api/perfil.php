<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="css/profile2.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">

</head>

<body>

    <body>
        <?php
        // Iniciar sessão antes de qualquer output
        session_start();

        // Incluir arquivo de conexão
        require_once 'connect.php';
        // Verificar se o usuário está logado
        if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
            // Redirecionar para a página de login
            header("Location: login.php");
            exit();
        }

        $idCliente = $_SESSION['usuario']['id'];
        $mensagem = "";
        $alertClass = "";

        // Buscar dados do cliente
        $stmt = $conn->prepare("SELECT * FROM Clientes WHERE idCliente = ?");
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
        } else {
            echo "Cliente não encontrado!";
            exit();
        }

        // Processar o formulário na hr de enviar
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validar os inputs
            $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensagem = "Formato de e-mail inválido!";
                $alertClass = "alert-danger";
            } else {
                // Verificar se o email já existe (exceto para o usuário atual)
                $stmt = $conn->prepare("SELECT idCliente FROM Clientes WHERE email = ? AND idCliente != ?");
                $stmt->bind_param("si", $email, $idCliente);
                $stmt->execute();
                $emailResult = $stmt->get_result();

                if ($emailResult->num_rows > 0) {
                    $mensagem = "Este e-mail já está sendo usado por outra conta!";
                    $alertClass = "alert-danger";
                } else {
                    // Atualizar senha se fornecida
                    $senhaAtual = $_POST['senha_atual'] ?? '';
                    $novaSenha = $_POST['nova_senha'] ?? '';
                    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

                    $senhaAlterada = false;

                    if (!empty($senhaAtual) && !empty($novaSenha) && !empty($confirmarSenha)) {
                        // Verificar se a senha atual está correta
                        if (password_verify($senhaAtual, $cliente['senha_hash'])) {
                            // Verificar se as novas senhas coincidem
                            if ($novaSenha === $confirmarSenha) {
                                // Hash da nova senha
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

                    // Se não houve erro de senha ou se a senha não está sendo alterada
                    if (empty($mensagem)) {
                        // Preparar a consulta SQL de atualização
                        if ($senhaAlterada) {
                            $stmt = $conn->prepare("UPDATE Clientes SET nome = ?, email = ?, telefone = ?, cpf = ?, senha_hash = ?, ultimoAcesso = NOW() WHERE idCliente = ?");
                            $stmt->bind_param("sssssi", $nome, $email, $telefone, $cpf, $novaSenhaHash, $idCliente);
                        } else {
                            $stmt = $conn->prepare("UPDATE Clientes SET nome = ?, email = ?, telefone = ?, cpf = ?, ultimoAcesso = NOW() WHERE idCliente = ?");
                            $stmt->bind_param("ssssi", $nome, $email, $telefone, $cpf, $idCliente);
                        }

                        if ($stmt->execute()) {
                            $mensagem = "Perfil atualizado com sucesso!";
                            $alertClass = "alert-success";

                            // Atualizar os dados exibidos na página
                            $cliente['nome'] = $nome;
                            $cliente['email'] = $email;
                            $cliente['telefone'] = $telefone;
                            $cliente['cpf'] = $cpf;
                            $cliente['ultimoAcesso'] = date('Y-m-d H:i:s');

                            // Atualizar os dados na sessão
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
        include "navbar/nav.php"
            ?>



        <div class="user-profile">
            <div class="user-info">
                <div class="row">
                    <div class="profile-photo-container">
                        <?php
                        $fotoPerfil = !empty($_SESSION['usuario']['foto']) ? $_SESSION['usuario']['foto'] : 'uploads/default.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" class="profile2-photo"
                            alt="Foto de perfil">
                    </div>
                    <div class="user-details">
                        <h2><?php echo htmlspecialchars($cliente['nome']); ?></h2>
                        <p class="text-muted">
                            <span
                                class="user-status-dot user-status-<?php echo strtolower($cliente['status']); ?>"></span>
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
                            <input type="text" class="input-field" id="nome" name="nome"
                                value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="email" class="field-label">E-mail</label>
                            <input type="email" class="input-field" id="email" name="email"
                                value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="telefone" class="field-label">Telefone</label>
                            <input type="tel" class="input-field" id="telefone" name="telefone"
                                value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>">
                        </div>
                        <div class="form-column">
                            <label for="cpf" class="field-label">CPF</label>
                            <input type="text" class="input-field" id="cpf" name="cpf"
                                value="<?php echo htmlspecialchars($cliente['cpf'] ?? ''); ?>">
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
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Toggle menu para dispositivos móveis
                const mobileToggle = document.getElementById('mobile-toggle');
                const navMenu = document.querySelector('.nav-menu');

                if (mobileToggle) {
                    mobileToggle.addEventListener('click', function () {
                        mobileToggle.classList.toggle('active');
                        navMenu.classList.toggle('active');
                    });
                }

                // Máscara para CPF
                const cpfInput = document.getElementById('cpf');
                if (cpfInput) {
                    cpfInput.addEventListener('input', function (e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);

                        if (value.length > 9) {
                            value = value.replace(/^(\d{3})(\d{3})(\d{3})/, '$1.$2.$3-');
                        } else if (value.length > 6) {
                            value = value.replace(/^(\d{3})(\d{3})/, '$1.$2.');
                        } else if (value.length > 3) {
                            value = value.replace(/^(\d{3})/, '$1.');
                        }

                        e.target.value = value;
                    });
                }

                // bglh pra telefone bonito
                const telefoneInput = document.getElementById('telefone');
                if (telefoneInput) {
                    telefoneInput.addEventListener('input', function (e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);

                        if (value.length > 10) {
                            value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                        } else if (value.length > 6) {
                            value = value.replace(/^(\d{2})(\d{4})/, '($1) $2-');
                        } else if (value.length > 2) {
                            value = value.replace(/^(\d{2})/, '($1) ');
                        }

                        e.target.value = value;
                    });
                }
            });
        </script>
    </body>
    <style>
        body {
            background-color: #081f4d;
        }
    </style>

</html>