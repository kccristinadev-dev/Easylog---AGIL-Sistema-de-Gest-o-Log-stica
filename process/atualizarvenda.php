<?php
session_start();
include "../process/conexao.php";

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}


// Recebe compra
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dados = json_decode(file_get_contents("php://input"), true);

    foreach ($dados as $produto) {

$sql = "
UPDATE produtos
SET
    total_vendas = total_vendas + :quantidade,
    estoque_fisico = estoque_fisico - :quantidade
WHERE id = :id
AND estoque_fisico >= :quantidade
";
        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            ":quantidade" => $produto["quantidade"],
            ":id" => $produto["id"]
        ]);
    }

    echo "Atualizado";
    exit;
}
