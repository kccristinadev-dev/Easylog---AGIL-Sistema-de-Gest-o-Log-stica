<?php
session_start();
require __DIR__ . '/conexao.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

$usuario = $_SESSION['usuario'];
$tipo = $usuario['tipo_de_usuario'] ?? 'cliente';

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    if ($tipo !== 'administrador') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Apenas administradores podem atualizar pedidos.']);
        exit;
    }

    $dados = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int) ($dados['id'] ?? 0);
    $codigo = trim((string) ($dados['codigo_pedido'] ?? ''));
    $status = (string) ($dados['status'] ?? '');
    $statusValidos = ['Pendente', 'Em preparação', 'Em transporte', 'Entregue', 'Cancelada'];

    if ($id <= 0 || !in_array($status, $statusValidos, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pedido ou status inválido.']);
        exit;
    }

    if ($codigo !== '') {
        $stmt = $conexao->prepare('UPDATE pedidos p INNER JOIN produtos pr ON pr.id = p.id_produto SET p.status = :status WHERE p.codigo_pedido = :codigo AND pr.usuario_id = :admin');
        $stmt->execute([':status' => $status, ':codigo' => $codigo, ':admin' => $usuario['id']]);
    } else {
        $stmt = $conexao->prepare('UPDATE pedidos p INNER JOIN produtos pr ON pr.id = p.id_produto SET p.status = :status WHERE p.id = :id AND pr.usuario_id = :admin');
        $stmt->execute([':status' => $status, ':id' => $id, ':admin' => $usuario['id']]);
    }
    echo json_encode(['success' => true, 'message' => 'Status atualizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

if ($tipo === 'administrador') {
    $sql = 'SELECT p.*, pr.nome AS produto, u.nome AS cliente_nome, u.email AS cliente_email FROM pedidos p INNER JOIN produtos pr ON pr.id = p.id_produto INNER JOIN usuario u ON u.id = p.id_usuario WHERE pr.usuario_id = :admin';
    $parametros = [':admin' => $usuario['id']];
} else {
    $sql = 'SELECT p.*, pr.nome AS produto FROM pedidos p INNER JOIN produtos pr ON pr.id = p.id_produto WHERE p.id_usuario = :cliente';
    $parametros = [':cliente' => $usuario['id']];
}

$historico = filter_var($_GET['historico'] ?? false, FILTER_VALIDATE_BOOLEAN);
if (!$historico) {
    $sql .= " AND p.status NOT IN ('Entregue', 'Cancelada')";
}
if (!empty($_GET['data_inicio'])) {
    $sql .= ' AND p.data >= :data_inicio';
    $parametros[':data_inicio'] = $_GET['data_inicio'] . ' 00:00:00';
}
if (!empty($_GET['data_fim'])) {
    $sql .= ' AND p.data <= :data_fim';
    $parametros[':data_fim'] = $_GET['data_fim'] . ' 23:59:59';
}
$sql .= ' ORDER BY p.data DESC, p.id DESC';
$stmt = $conexao->prepare($sql);
$stmt->execute($parametros);

echo json_encode(['success' => true, 'dados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
