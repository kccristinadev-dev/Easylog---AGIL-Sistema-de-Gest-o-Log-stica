<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    die("Usuário não autenticado.");
}

$usuario = $_SESSION['usuario']['id'];

$nome = trim($_POST['nomeDoProdutoPrincipal']);
$estoqueFisico = (int) $_POST['estoqueFisico'];
$estoqueVirtual = (int) $_POST['estoqueVirtual'];
$tempoReposicao = (int) $_POST['tempoRepo'];
$preco = (float) $_POST['preco'];

$sql = "
INSERT INTO produtos
(
    usuario_id,
    nome,
    estoque_fisico,
    estoque_virtual,
    total_vendas,
    tempo_reposicao,
    total_pedidos,
    periodo,
    preco
)
VALUES (?, ?, ?, ?, 0, ?, 0, 'Mes', ?)"
;

$stmt = $conexao->prepare($sql);

$stmt->execute([
    $usuario,
    $nome,
    $estoqueFisico,
    $estoqueVirtual,
    $tempoReposicao,
    $preco
]);

echo "Produto cadastrado com sucesso!";
?>