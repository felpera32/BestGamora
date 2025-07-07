<?php
session_start();

include 'connect.php';


if (!isset($conn) || $conn === null) {
    try {
        include_once 'connect.php';

        // se n conectar, esse codigo tenta manualmente 
        if (!isset($conn) || $conn === null) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "gamoraloja";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }
        }
    } catch (Exception $e) {
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit;
}

// Verificar se há biblioteca na sessão
if (!isset($_SESSION['biblioteca']) || empty($_SESSION['biblioteca'])) {
    header('Location: index.php');
    exit;
}
// dados do cliente

$dadosCliente = [
    'nome' => $_SESSION['nome_usuario'] ?? 'Cliente',
    'email' => 'Não informado',
    'cpf' => '',
    'telefone' => ''
];

// ID do user
$idCliente = $_SESSION['id_usuario'] ?? 0;

// Tentando fazer um metodo bom de busca
if (isset($_SESSION['nome_usuario']) && !empty($_SESSION['nome_usuario'])) {
    $nomeUsuario = $_SESSION['nome_usuario'];

    $sql = "SELECT idCliente, nome, email, cpf, telefone FROM clientes WHERE nome = ?";

    try {
        $stmt = $conn->prepare($sql);
        if ($stmt !== false) {
            $stmt->bind_param("s", $nomeUsuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $clienteData = $result->fetch_assoc();

                // Atualizar o ID do cliente na sessão
                $_SESSION['id_usuario'] = $clienteData['idCliente'];
                $idCliente = $clienteData['idCliente'];

                // Atualizar dados do cliente
                if (!empty($clienteData['nome']))
                    $dadosCliente['nome'] = $clienteData['nome'];
                if (!empty($clienteData['email']))
                    $dadosCliente['email'] = $clienteData['email'];
                if (!empty($clienteData['cpf']))
                    $dadosCliente['cpf'] = $clienteData['cpf'];
                if (!empty($clienteData['telefone']))
                    $dadosCliente['telefone'] = $clienteData['telefone'];
            } else {
                // Segundo método: busca usando LIKE para ser mais flexível
                $sql = "SELECT idCliente, nome, email, cpf, telefone FROM clientes WHERE nome LIKE ?";
                $nomeParam = "%$nomeUsuario%";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $nomeParam);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $clienteData = $result->fetch_assoc();

                    // Atualizar o ID do cliente na sessão
                    $_SESSION['id_usuario'] = $clienteData['idCliente'];
                    $idCliente = $clienteData['idCliente'];

                    // Atualizar dados do cliente
                    if (!empty($clienteData['nome']))
                        $dadosCliente['nome'] = $clienteData['nome'];
                    if (!empty($clienteData['email']))
                        $dadosCliente['email'] = $clienteData['email'];
                    if (!empty($clienteData['cpf']))
                        $dadosCliente['cpf'] = $clienteData['cpf'];
                    if (!empty($clienteData['telefone']))
                        $dadosCliente['telefone'] = $clienteData['telefone'];
                } else {
                    // OPCIONAL: Inserir o usuário no banco de dados
                    $sqlInsert = "INSERT INTO clientes (nome, email) VALUES (?, 'cliente@exemplo.com')";
                    $stmtInsert = $conn->prepare($sqlInsert);
                    $stmtInsert->bind_param("s", $nomeUsuario);

                    if ($stmtInsert->execute()) {
                        $novoId = $conn->insert_id;

                        $_SESSION['id_usuario'] = $novoId;
                        $idCliente = $novoId;
                        $dadosCliente['email'] = 'cliente@exemplo.com';
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Tratamento silencioso de erro
    }
}

// Se ainda não temos dados suficientes e um ID válido, verificamos novamente
if ($idCliente > 0) {
    $sql = "SELECT nome, email, cpf, telefone FROM clientes WHERE idCliente = ?";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $clienteData = $result->fetch_assoc();

            // Atualizar dados com qualquer informação disponível
            if (!empty($clienteData['nome']))
                $dadosCliente['nome'] = $clienteData['nome'];
            if (!empty($clienteData['email']))
                $dadosCliente['email'] = $clienteData['email'];
            if (!empty($clienteData['cpf']))
                $dadosCliente['cpf'] = $clienteData['cpf'];
            if (!empty($clienteData['telefone']))
                $dadosCliente['telefone'] = $clienteData['telefone'];
        }
    } catch (Exception $e) {
        // Tratamento silencioso de erro
    }
}

$metodoPagamento = $_SESSION['metodo_pagamento'] ?? 'Não especificado';
$dataPedido = date('d/m/Y H:i:s');
$numeroPedido = date('YmdHis') . rand(100, 999);
$valorTotal = 0;

// Recuperar o carrinho finalizado (antes de limpar na finalização da compra)
$carrinhoFinalizado = $_SESSION['carrinho_finalizado'] ?? [];

// Se não temos um carrinho finalizado, mas temos uma biblioteca, vamos usar a biblioteca
if (empty($carrinhoFinalizado) && !empty($_SESSION['biblioteca'])) {
    $carrinhoFinalizado = [];
    foreach ($_SESSION['biblioteca'] as $idJogo => $item) {
        // Buscar dados do produto no banco de dados
        $sql = "SELECT nome, preco FROM produtos WHERE idProduto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idJogo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            $carrinhoFinalizado[$idJogo] = [
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => 1
            ];

            // Adicionar ao valor total
            $valorTotal += $produto['preco'];
        } else {
            // Se não encontrar no banco, usar o nome da biblioteca
            $carrinhoFinalizado[$idJogo] = [
                'nome' => $item['nome'],
                'preco' => 0, // Não temos o preço
                'quantidade' => 1
            ];
        }
    }
}

// Recalcular o valor total com base nos subtotais
$valorTotal = 0;
if (!empty($carrinhoFinalizado)) {
    foreach ($carrinhoFinalizado as $item) {
        $valorTotal += $item['preco'] * $item['quantidade'];
    }
}

// Função para formatar CPF
function formatarCPF($cpf)
{
    if (!$cpf || strlen($cpf) != 11) {
        return 'Não informado';
    }
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' .
        substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

// Função para formatar telefone
function formatarTelefone($telefone)
{
    if (!$telefone) {
        return 'Não informado';
    }

    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    $tamanho = strlen($telefone);

    if ($tamanho == 11) {
        return '(' . substr($telefone, 0, 2) . ') ' .
            substr($telefone, 2, 5) . '-' .
            substr($telefone, 7);
    } elseif ($tamanho == 10) {
        return '(' . substr($telefone, 0, 2) . ') ' .
            substr($telefone, 2, 4) . '-' .
            substr($telefone, 6);
    }

    return $telefone;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Nota Fiscal - Gamora Loja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <?php include "navbar/nav.php"; ?>
    <link rel="stylesheet" href="css/pedidos.css">
</head>

<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="logo-container">
                <img src="imagens/logo.png" alt="Gamora Loja">
            </div>
            <div class="invoice-title">
                <h1>NOTA FISCAL</h1>
                <p>Nº <?php echo $numeroPedido; ?></p>
            </div>
        </div>

        <div class="invoice-details">
            <div class="company-info">
                <h3>DADOS DA EMPRESA</h3>
                <p><strong>Gamora Loja Virtual</strong></p>
                <p>CNPJ: 12.345.678/0001-90</p>
                <p>Av. Das Américas, 5000 - Rio de Janeiro, RJ</p>
                <p>CEP: 22640-102</p>
                <p>Tel: (21) 3333-4444</p>
                <p>Email: contato@gamoraloja.com.br</p>
            </div>

            <div class="client-info">
                <h3>DADOS DO CLIENTE</h3>
                <p><strong>Nome:</strong> <?php echo $dadosCliente['nome']; ?></p>
                <p><strong>CPF:</strong> <?php echo formatarCPF($dadosCliente['cpf']); ?></p>
                <p><strong>E-mail:</strong> <?php echo $dadosCliente['email']; ?></p>
                <p><strong>Telefone:</strong> <?php echo formatarTelefone($dadosCliente['telefone']); ?></p>
                <p><strong>Data da compra:</strong> <?php echo $dataPedido; ?></p>
                <p><strong>Forma de pagamento:</strong> <?php echo $metodoPagamento; ?></p>
            </div>
        </div>

        <table class="invoice-items">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($carrinhoFinalizado)): ?>
                    <?php foreach ($carrinhoFinalizado as $item): ?>
                        <?php
                        $subtotal = $item['preco'] * $item['quantidade'];
                        ?>
                        <tr>
                            <td><?php echo $item['nome']; ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Nenhum item encontrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="invoice-total">
            TOTAL: R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?>
        </div>

        <div class="invoice-footer">
            <p>Obrigado por comprar na Gamora Loja!</p>
            <p>Este documento serve como comprovante fiscal de sua compra.</p>
            <p>Em caso de dúvidas, entre em contato com nosso suporte: suporte@gamoraloja.com.br</p>
        </div>

        <div class="actions">
            <button class="print-btn" onclick="window.print()">Imprimir</button>
            <button class="download-btn" onclick="generatePDF()">Baixar PDF</button>
            <a href="index.php"><button class="return-btn">Voltar para a Loja</button></a>
        </div>
    </div>

    <script>
        function generatePDF() {
            window.print();

        }
    </script>
</body>

</html>