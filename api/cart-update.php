<?php
session_start();
include 'connect.php';

if (empty($_SESSION['carrinho'])) {
    echo "Seu carrinho está vazio.";
} else {
    echo "<h2>Seu Carrinho</h2>";
    $valorTotal = 0;

    foreach ($_SESSION['carrinho'] as $id => $quantidade) {
        $stmt = $pdo->prepare("SELECT nome, preco FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        $subtotal = $produto['preco'] * $quantidade;
        $valorTotal += $subtotal;

        echo "<p>{$produto['nome']} - R$ {$produto['preco']} x $quantidade = R$ $subtotal</p>";
    }

    echo "<h3>Valor Total: R$ $valorTotal</h3>";
    echo '<form method="POST" action="carrinho.php">
            <input type="hidden" name="idCliente" value="1"> <!-- Substitua pelo ID do cliente -->
            <button type="submit" name="finalizar">Finalizar Compra</button>
          </form>';
}
?>
