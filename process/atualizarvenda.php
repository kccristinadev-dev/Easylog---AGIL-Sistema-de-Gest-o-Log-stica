<?php
session_start();
include "conexao.php";

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Método não permitido.";
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);

if (!is_array($dados)) {
    http_response_code(400);
    echo "Dados inválidos.";
    exit;
}

$endereco = trim((string) ($dados['endereco'] ?? ''));
$telefone = trim((string) ($dados['telefone'] ?? ''));
$observacoes = trim((string) ($dados['observacoes'] ?? ''));
$itens = $dados['itens'] ?? [];

if ($endereco === '' || !is_array($itens) || empty($itens)) {
    http_response_code(400);
    echo "Informe o endereço e adicione produtos ao pedido.";
    exit;
}

try {

    $codigo = 'PED-' . date('YmdHis') . '-' . $_SESSION['usuario']['id'];

    /*
     * Cadastra cada item do pedido.
     */
    $stmtPedido = $conexao->prepare("
        INSERT INTO pedidos
        (
            id_produto,
            id_usuario,
            quantidade,
            status,
            codigo_pedido,
            endereco_entrega,
            telefone,
            observacoes_entrega
        )
        VALUES (?, ?, ?, 'Pendente', ?, ?, ?, ?)
    ");

    /*
     * Atualiza o estoque e o total de pedidos.
     *
     * Os parâmetros q1, q2 e q3 são separados
     * para evitar o erro de "Invalid parameter number"
     * causado pela repetição do mesmo parâmetro nomeado.
     */
    $stmtEstoque = $conexao->prepare("
        UPDATE produtos p
        INNER JOIN usuario u ON u.id = p.usuario_id
        SET
            p.total_pedidos = p.total_pedidos + :q1,
            p.estoque_fisico = p.estoque_fisico - :q2
        WHERE
            p.id = :id
            AND u.tipo_de_usuario = 'administrador'
            AND p.estoque_fisico >= :q3
    ");

    foreach ($itens as $produto) {

        $idProduto = (int) ($produto['id'] ?? 0);
        $quantidade = (int) ($produto['quantidade'] ?? 0);

        if ($idProduto <= 0 || $quantidade <= 0) {
            throw new RuntimeException(
                "Produto inválido. ID: " .
                ($produto['id'] ?? 'não informado') .
                " | Quantidade: " .
                ($produto['quantidade'] ?? 'não informada')
            );
        }

        /*
         * Atualiza o estoque.
         */
        $stmtEstoque->execute([
            ":q1" => $quantidade,
            ":q2" => $quantidade,
            ":id" => $idProduto,
            ":q3" => $quantidade
        ]);

        if ($stmtEstoque->rowCount() !== 1) {
            throw new RuntimeException(
                "Estoque insuficiente ou produto não encontrado. " .
                "ID: " . $idProduto
            );
        }

        /*
         * Registra o pedido.
         */
        $stmtPedido->execute([
            $idProduto,
            $_SESSION['usuario']['id'],
            $quantidade,
            $codigo,
            $endereco,
            $telefone,
            $observacoes
        ]);
    }

    echo "Pedido realizado com sucesso.";
    exit;

} catch (Throwable $e) {

    http_response_code(500);

    echo "ERRO: " .
        get_class($e) .
        " | " .
        $e->getMessage();

    exit;
}