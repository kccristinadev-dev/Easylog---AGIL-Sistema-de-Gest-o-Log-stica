<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "sql103.infinityfree.com";
$username = "if0_42271504";
$password = "4YYjx9BwEzQrG8Z";
$dbname = "if0_42271504_sistema";

try {$conexao = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch (PDOException $e) {
    echo "Erro na conexão";
}


?>