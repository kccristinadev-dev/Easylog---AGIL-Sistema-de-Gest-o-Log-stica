<?php
session_start();
include "conexao.php";

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {


$dados = json_decode(file_get_contents("php://input"), true);

var_dump($dados);

$sql = "DELETE FROM produtos WHERE id = :id";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    ":id" => $dados["id"]
]);

echo "Linhas apagadas: " . $stmt->rowCount();

exit();
}