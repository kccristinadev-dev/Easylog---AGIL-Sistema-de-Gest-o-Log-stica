<?php
session_start();
require __DIR__ . '/conexao.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']['id']) || ($_SESSION['usuario']['tipo_de_usuario'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?: [];
$id = (int) ($dados['id'] ?? 0);
$campos = ['nome', 'estoque_fisico', 'estoque_virtual', 'total_vendas', 'tempo_reposicao', 'total_pedidos'];
if ($id <= 0 || empty($dados['nome'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$stmt = $conexao->prepare('UPDATE produtos SET nome = :nome, estoque_fisico = :estoque_fisico, estoque_virtual = :estoque_virtual, total_vendas = :total_vendas, tempo_reposicao = :tempo_reposicao, total_pedidos = :total_pedidos WHERE id = :id AND usuario_id = :usuario');
$stmt->execute([
    ':nome' => trim($dados['nome']),
    ':estoque_fisico' => max(0, (int) ($dados['estoque_fisico'] ?? 0)),
    ':estoque_virtual' => max(0, (int) ($dados['estoque_virtual'] ?? 0)),
    ':total_vendas' => max(0, (int) ($dados['total_vendas'] ?? 0)),
    ':tempo_reposicao' => max(0, (int) ($dados['tempo_reposicao'] ?? 0)),
    ':total_pedidos' => max(0, (int) ($dados['total_pedidos'] ?? 0)),
    ':id' => $id,
    ':usuario' => $_SESSION['usuario']['id']
]);

echo json_encode(['success' => $stmt->rowCount() >= 0, 'message' => 'Produto atualizado.']);
