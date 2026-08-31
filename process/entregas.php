<?php
session_start();

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

require __DIR__ . '/conexao.php';

if (!$conexao) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Banco de dados indisponível.']);
    exit;
}

$usuarioId = (int) $_SESSION['usuario']['id'];
$method = $_SERVER['REQUEST_METHOD'];

function lerPayload(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $dados = json_decode($raw, true);
    if (is_array($dados)) {
        return $dados;
    }

    parse_str($raw, $dados);
    return is_array($dados) ? $dados : [];
}

if ($method === 'GET') {
    $acao = $_GET['acao'] ?? 'listar';

    if ($acao === 'listar') {
        $status = $_GET['status'] ?? null;
        $where = ['usuario_id = :usuario'];
        $parametros = [':usuario' => $usuarioId];

        if ($status !== null && $status !== '' && $status !== 'todos') {
            $where[] = 'status = :status';
            $parametros[':status'] = $status;
        }

        $sql = 'SELECT * FROM entregas WHERE ' . implode(' AND ', $where) . ' ORDER BY data_prevista ASC, id DESC';
        $stmt = $conexao->prepare($sql);
        $stmt->execute($parametros);
        $entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($entregas as &$entrega) {
            $entrega['produtos_relacionados'] = json_decode($entrega['produtos_relacionados'] ?? '[]', true) ?: [];
        }

        echo json_encode(['success' => true, 'dados' => $entregas]);
        exit;
    }

    if ($acao === 'rota') {
        $sql = "SELECT * FROM entregas WHERE usuario_id = :usuario AND status NOT IN ('Entregue', 'Cancelada') ORDER BY data_prevista ASC, id ASC";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([':usuario' => $usuarioId]);
        $entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rota = $entregas;
        usort($rota, function ($a, $b) {
            $aCoordenada = !empty($a['latitude']) && !empty($a['longitude']);
            $bCoordenada = !empty($b['latitude']) && !empty($b['longitude']);

            if ($aCoordenada && $bCoordenada) {
                $diferenca = floatval($a['latitude']) - floatval($b['latitude']);
                if (abs($diferenca) < 0.000001) {
                    return floatval($a['longitude']) - floatval($b['longitude']);
                }
                return $diferenca;
            }

            if ($aCoordenada !== $bCoordenada) {
                return $aCoordenada ? -1 : 1;
            }

            $enderecoA = strtolower(trim((string) ($a['endereco'] ?? '')));
            $enderecoB = strtolower(trim((string) ($b['endereco'] ?? '')));
            return strcmp($enderecoA, $enderecoB);
        });

        echo json_encode(['success' => true, 'dados' => $rota]);
        exit;
    }
}

if ($method === 'POST') {
    $dados = lerPayload();

    $pedidoId = $dados['pedido_id'] ?? null;
    $cliente = trim((string) ($dados['cliente'] ?? ''));
    $endereco = trim((string) ($dados['endereco'] ?? ''));
    $dataPrevista = trim((string) ($dados['data_prevista'] ?? ''));
    $status = trim((string) ($dados['status'] ?? 'Pendente'));
    $produtosRelacionados = $dados['produtos_relacionados'] ?? [];
    $quantidade = isset($dados['quantidade']) ? (int) $dados['quantidade'] : 0;
    $observacoes = trim((string) ($dados['observacoes'] ?? ''));
    $latitude = isset($dados['latitude']) && $dados['latitude'] !== '' ? (float) $dados['latitude'] : null;
    $longitude = isset($dados['longitude']) && $dados['longitude'] !== '' ? (float) $dados['longitude'] : null;

    $statusValidos = ['Pendente', 'Em separação', 'Em transporte', 'Entregue', 'Cancelada'];
    if (!in_array($status, $statusValidos, true)) {
        $status = 'Pendente';
    }

    if ($cliente === '' || $endereco === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cliente e endereço são obrigatórios.']);
        exit;
    }

    $produtosJson = json_encode(is_array($produtosRelacionados) ? $produtosRelacionados : []);

    $sql = 'INSERT INTO entregas (usuario_id, pedido_id, cliente, endereco, data_prevista, status, produtos_relacionados, quantidade, observacoes, latitude, longitude)
            VALUES (:usuario_id, :pedido_id, :cliente, :endereco, :data_prevista, :status, :produtos_relacionados, :quantidade, :observacoes, :latitude, :longitude)';

    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuarioId,
        ':pedido_id' => $pedidoId !== '' && $pedidoId !== null ? $pedidoId : null,
        ':cliente' => $cliente,
        ':endereco' => $endereco,
        ':data_prevista' => $dataPrevista !== '' ? $dataPrevista : null,
        ':status' => $status,
        ':produtos_relacionados' => $produtosJson,
        ':quantidade' => $quantidade,
        ':observacoes' => $observacoes !== '' ? $observacoes : null,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
    ]);

    echo json_encode(['success' => true, 'message' => 'Entrega cadastrada com sucesso.']);
    exit;
}

if ($method === 'PUT' || $method === 'PATCH') {
    $dados = lerPayload();
    $id = isset($dados['id']) ? (int) $dados['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Identificador da entrega inválido.']);
        exit;
    }

    $status = trim((string) ($dados['status'] ?? 'Pendente'));
    $observacoes = trim((string) ($dados['observacoes'] ?? ''));

    $sql = 'UPDATE entregas SET status = :status, observacoes = :observacoes WHERE id = :id AND usuario_id = :usuario';
    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':observacoes' => $observacoes !== '' ? $observacoes : null,
        ':id' => $id,
        ':usuario' => $usuarioId,
    ]);

    echo json_encode(['success' => true, 'message' => 'Entrega atualizada.']);
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
        $raw = lerPayload();
        $id = isset($raw['id']) ? (int) $raw['id'] : 0;
    }

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Identificador da entrega inválido.']);
        exit;
    }

    $sql = 'DELETE FROM entregas WHERE id = :id AND usuario_id = :usuario';
    $stmt = $conexao->prepare($sql);
    $stmt->execute([':id' => $id, ':usuario' => $usuarioId]);

    echo json_encode(['success' => true, 'message' => 'Entrega removida.']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
