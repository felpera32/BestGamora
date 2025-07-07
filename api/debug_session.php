<?php
session_start();
header('Content-Type: application/json');

// Script para debug - verificar se a sessão está funcionando
echo json_encode([
    'session_data' => $_SESSION,
    'usuario_logado' => $_SESSION['usuario_logado'] ?? false,
    'idCliente' => $_SESSION['idCliente'] ?? null,
    'session_id' => session_id()
]);
?>