<?php
$host = 'mysql-gamora.alwaysdata.net';     
$usuario = 'gamora';      
$senha = 'trabalho6969*';             
$banco = 'gamora_loja';      

$conn = new mysqli($host, $usuario, $senha, $banco);      


if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>