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

        $endereco = trim((string) ($dados['endereco'] ?? ''));
        $itens = is_array($dados) ? ($dados['itens'] ?? []) : [];
        if (!$itens || $endereco === '') {
            http_response_code(400);
            echo "Informe o endereço e adicione produtos ao pedido.";
            exit;
        }

        $conexao->beginTransaction();
        try {
            $codigo = 'PED-' . date('YmdHis') . '-' . $_SESSION['usuario']['id'];
            $stmtPedido = $conexao->prepare("INSERT INTO pedidos (id_produto, id_usuario, quantidade, status, codigo_pedido, endereco_entrega, telefone, observacoes_entrega) VALUES (?, ?, ?, 'Pendente', ?, ?, ?, ?)");

        foreach ($itens as $produto) {

        $sql = "UPDATE produtos p INNER JOIN usuario u ON u.id = p.usuario_id SET p.total_pedidos = p.total_pedidos + :quantidade, p.estoque_fisico = p.estoque_fisico - :quantidade WHERE p.id = :id AND u.tipo_de_usuario = 'administrador' AND p.estoque_fisico >= :quantidade";
        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            ":quantidade" => $produto["quantidade"],
            ":id" => $produto["id"]
        ]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Estoque insuficiente.');
        }
        $stmtPedido->execute([$produto["id"], $_SESSION['usuario']['id'], $produto["quantidade"], $codigo, $endereco, trim((string) ($dados['telefone'] ?? '')), trim((string) ($dados['observacoes'] ?? ''))]);
    }

    $conexao->commit();
    echo "Atualizado";
    exit;
    } catch (Throwable $e) {
        if ($conexao->inTransaction()) $conexao->rollBack();
        http_response_code(500);
        echo "Não foi possível finalizar o pedido.";
        exit;
    }
}
