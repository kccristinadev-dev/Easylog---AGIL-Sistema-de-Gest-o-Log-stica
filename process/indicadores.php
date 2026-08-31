<?php

session_start();

include 'conexao.php';

$usuario = $_SESSION['usuario']['id'];
if (!isset($_SESSION['usuario']['id'])) {
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM produtos WHERE usuario_id = ?");
$stmt->execute([$usuario]);

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$resultado = [];

foreach ($dados as $p) {

    $estoqueTotal = $p['estoque_fisico'] + $p['estoque_virtual'];
    
$estoqueInicial = $p['estoque_fisico'] + $p['total_vendas'];

$estoqueMedio = ($estoqueInicial + $p['estoque_fisico']) / 2;

$giro = ($estoqueMedio > 0)
    ? $p['total_vendas'] / $estoqueMedio
    : 0;


    $dias = match($p['periodo']) {
        "Dia" => 1,
        "Mes" => 30,
        default => 365
    };

    $consumo = ($dias > 0)
        ? $p['total_vendas'] / $dias
        : 0;

    $cobertura = ($consumo > 0)
        ? $estoqueTotal / $consumo
        : 0;

    $faltou = max(0, $p['total_pedidos'] - $p['total_vendas']);

    $ruptura = ($p['total_pedidos'] > 0)
        ? ($faltou / $p['total_pedidos']) * 100
        : 0;

    $resultado[] = [
        "id" => $p["id"],
        "produto" => $p['nome'],
        "estoqueFisico" => $p['estoque_fisico'],
        "estoqueVirtual" => $p['estoque_virtual'],
        "vendaTotal" => $p['total_vendas'],
        "tempoRepo" => $p['tempo_reposicao'],
        "totalPedidos" => $p['total_pedidos'],
        "periodo" => $p['periodo'],

        "giro" => $giro,
        "consumo" => $consumo,
        "cobertura" => $cobertura,
        "ruptura" => $ruptura
    ];
}


$stmt = $conexao->prepare(" SELECT COUNT(*) AS total FROM produtos WHERE usuario_id = ?");
$stmt->execute([$usuario]);
$totalGeral = $stmt->fetchColumn();
