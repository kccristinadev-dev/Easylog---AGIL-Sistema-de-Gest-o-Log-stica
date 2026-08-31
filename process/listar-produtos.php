<?php
include "conexao.php";

$sql = "SELECT * FROM produtos";
$resultado = mysqli_query($conexao, $sql);

$produtos = [];

while ($linha = mysqli_fetch_assoc($resultado)) {
    $produtos[] = $linha;
}

header("Content-Type: application/json");
echo json_encode($produtos);