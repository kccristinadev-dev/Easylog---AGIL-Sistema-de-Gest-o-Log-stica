<?php
session_start();
include "../process/conexao.php";

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}
// Recebe compra
if ($_SERVER["REQUEST_METHOD"] === "POST") {

$conexao->beginTransaction();

try {
    
    $dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !is_array($dados)) {
    http_response_code(400);
    echo "Dados inválidos.";
    exit;
}

    
    
    
    


    foreach ($dados as $produto) {


$sql = "SELECT estoque_fisico FROM produtos WHERE id = :id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $produto["id"]]);

$estoque = $stmt->fetchColumn();

if ($estoque < $produto["quantidade"]) {
    echo "Estoque insuficiente.";
    exit;
}




        $sql = "
        UPDATE produtos
        SET 
            total_pedidos = total_pedidos + :quantidade,
            estoque_fisico = estoque_fisico - :quantidade
        WHERE id = :id
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            ":quantidade" => $produto["quantidade"],
            ":id" => $produto["id"]
        ]);
    }
    $conexao->commit();

    echo "Atualizado";
    exit;
}
catch (Exception $e) {

    $conexao->rollBack();
    echo "Erro ao finalizar compra.";

}
}




// Carrega produtos 
$idUsuario = $_SESSION['usuario']['id'];


$tipoUsuario = $_SESSION['usuario']['tipo_de_usuario'] ?? 'cliente';
$sqlProdutos = $tipoUsuario === 'administrador'
  ? "SELECT p.* FROM produtos p WHERE p.usuario_id = :id"
  : "SELECT p.* FROM produtos p INNER JOIN usuario u ON u.id = p.usuario_id WHERE u.tipo_de_usuario = 'administrador' ORDER BY p.nome";
$stmt = $conexao->prepare($sqlProdutos);
$stmt->execute([":id" => $idUsuario]);

$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="theme-color" content="#4A0072">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Easylog </title>
  <link rel="stylesheet" href="../estilo/comerce.css">
</head>

<body>
<header>
</header>

<main>

  <!-- CATEGORIAS / VITRINE -->
  <section id="categorias" class="tela ativa" aria-label="Lista de categorias e produtos">

    <header class="categorias-topo">
      <input 
        type="search"
        id="busca-categorias" 
        placeholder="Buscar categorias..." 
        oninput="buscarCategorias()" 
        aria-label="Buscar categorias"
      />
    </header>


    <h2 class="titulo-secao">Produtos</h2>
    <div class="lista-produtos" id="lista-produtos"></div>

  </section>

  <section id="acompanhar" class="tela" aria-label="Acompanhamento de pedidos">
    <h2>Meus pedidos</h2>
    <div id="lista-pedidos"></div>
  </section>

  <!-- DETALHE DO PRODUTO -->
  <section id="detalhe-produto" class="produto" aria-label="Detalhe do produto">

    <button class="fechar" onclick="voltarParaCategoria()" aria-label="Voltar">&lt;</button>

    <article>
      <img id="img" src="" alt="Imagem do produto">

      <h2 id="detalhe"></h2>
      <p id="descricao"></p>

      <p id="preco"></p>

      <div class="alinar">
        <button type="button" class="calcular" onclick="calcular('soma')" aria-label="Aumentar quantidade">+</button>
        <p id="quantidade"></p>
        <button type="button" class="calcular" onclick="calcular('menos')" aria-label="Diminuir quantidade">-</button>
      </div>

      <p id="total-produt"></p>

      <button id="pedido"
        type="button" 
        class="Adicio"
        onclick="adicionarCarrinho()"
      >
        pedidos
      </button>
    </article>

  </section>

  <!-- CARRINHO -->
  <section id="carrinho" class="tela" aria-label="Carrinho de compras">

    <div class="organiza">

      <ol class="carrinho-de-produtos"></ol>

      <p id="totalgeral"></p>
      <div class="dados-entrega">
        <h3>Dados para entrega</h3>
        <input id="endereco-entrega" type="text" placeholder="Endereço completo" required>
        <input id="telefone-entrega" type="tel" placeholder="Telefone">
        <textarea id="observacoes-entrega" placeholder="Observações"></textarea>
      </div>
<br>
      <button id="comprar" onclick="atualizarCompra()"
      >
        Comprar
      </button>

    </div>

  </section>

</main>

<!-- NAVEGAÇÃO -->
<nav id="menu" aria-label="Menu principal">
  <button onclick="mostrarElemento('categorias')">Produtos</button>
  <button onclick="mostrarElemento('carrinho')">Pedidos</button>
  <button onclick="mostrarElemento('acompanhar'); carregarPedidos()">Acompanhar</button>
<button onclick="window.location.href='../inicio.php'">
    Administração
</button>
</nav>

<script>
const produtos = <?= json_encode($produtos) ?>;

const idUsuario = <?= $_SESSION['usuario']['id'] ?>;
</script>
<script src="../acoes/comerce.js" defer></script>
<script src="../acoes/temas.js" defer></script>
</body>
</html>