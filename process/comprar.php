<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    die("Usuário não autenticado.");
}

$id = (int) $_POST["id"];
$quantidade = (int) $_POST["quantidade"];

if ($quantidade <= 0) {
    die("Quantidade inválida.");
}

$sql = "
UPDATE produtos
SET
    estoque_virtual = estoque_virtual - ?,
    total_vendas = total_vendas + ?,
    total_pedidos = total_pedidos + 1
WHERE id = ? AND usuario_id = ?
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    $quantidade,
    $quantidade,
    $id,
    $_SESSION['usuario']['id']
]);

echo "Compra registrada com sucesso!";