<?php
session_start();

if (isset($_GET['id']) && isset($_SESSION['carrinho'][$_GET['id']])) {
    unset($_SESSION['carrinho'][$_GET['id']]);
}

header('Location: cart.php');
exit;
?>