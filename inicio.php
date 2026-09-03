<?php
session_start();

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: index.php");
    exit;
}

require __DIR__ . '/process/conexao.php';
$indicadoresInicio = [];
if ($conexao) {
    include __DIR__ . '/process/indicadores.php';
    $indicadoresInicio = $resultado;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/inicio.css">
    <title>Tela inicial</title>
</head>

<body>

<?php include __DIR__ . '/Tela/menu.php'; ?>

<header>
    <h2>Olá <?= htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Usuário'); ?></h2>
    <p>Bem vindo ao sistema de gestão de desempenho de sua empresa</p>
</header>

<main>
<main>

<div class="opcoes">

    <div class="card-opcao" onclick="abrirFormulario('produto')">
        <div class="icone">+</div>
        <h3>Adicionar Produto</h3>
    </div>

    <div class="card-opcao" onclick="gerarAcessoClientes()">
        <div class="icone">+</div>
        <h3>Acesso de clientes</h3>
    </div>

</div>

<section class="painel-desempenho">
    <div>
        <p class="rotulo-painel">Visão geral</p>
        <h3>Médias por nível de desempenho</h3>
    </div>
    <div class="grafico-inicial-area">
        <canvas id="grafico-desempenho" height="260"></canvas>
        <p id="sem-indicadores" hidden>Nenhum indicador disponível.</p>
    </div>
</section>

<div id="Calculadora">
<div id="overlay" onclick="fecharFormulario()"></div>
<form id="informacoes" style="display:none;">
   
    <button type="button" class="fechar" onclick="fecharFormulario()">
    ✕
</button>
        <h3>Adicionar Produto</h3>

    <div class="campo">
        <label for="nomeDoProdutoPrincipal">Nome do Produto</label>
        <input
            type="text"
            id="nomeDoProdutoPrincipal"
            name="nomeDoProdutoPrincipal"
            placeholder="Ex: Produto A"
            required>
    </div>

    <div class="campo">
        <label for="estoqueFisico">Estoque Físico</label>
        <input
            type="number"
            id="estoqueFisico"
            name="estoqueFisico"
            min="0"
            placeholder="Ex: 200"
            required>
    </div>

    <div class="campo">
        <label for="estoqueVirtual">Estoque Virtual</label>
        <input
            type="number"
            id="estoqueVirtual"
            name="estoqueVirtual"
            min="0"
            placeholder="Ex: 150"
            required>
    </div>


    <div class="campo">
        <label for="tempoRepo">Tempo de Reposição (dias)</label>
        <input
            type="number"
            id="tempoRepo"
            name="tempoRepo"
            min="0"
            placeholder="Ex: 5"
            required>
    </div>
<div class="campo">
    <label for="preco">Preço (unidade)</label>

    <input
        type="number"
        id="preco"
        name="preco"
        min="0"
        step="0.01"
        placeholder="Ex: 2.00"
        required>
</div>

    <div class="alinhar">


        <button type="button" onclick="armazenarValor()" id="calcular">
    Adicionar
</button>
    </div>

</form>

</div>

</main>
<script>
const indicadoresInicio = <?= json_encode($indicadoresInicio) ?>;

function desenharGraficoDesempenho() {
    const canvas = document.getElementById('grafico-desempenho');
    if (!canvas || indicadoresInicio.length === 0) {
        document.getElementById('sem-indicadores').hidden = false;
        return;
    }

    const grupos = {
        Bom: { soma: 0, total: 0 },
        Medio: { soma: 0, total: 0 },
        Ruim: { soma: 0, total: 0 }
    };
    indicadoresInicio.forEach((item) => {
        const giro = Number(item.giro) || 0;
        const desempenho = giro >= 3 ? 'Bom' : (giro >= 1.6 ? 'Medio' : 'Ruim');
        grupos[desempenho].soma += giro;
        grupos[desempenho].total += 1;
    });

    const ctx = canvas.getContext('2d');
    const largura = canvas.clientWidth || 600;
    canvas.width = largura;
    const medias = Object.fromEntries(Object.entries(grupos).map(([nivel, dados]) => [
        nivel,
        dados.total > 0 ? dados.soma / dados.total : 0
    ]));
    const maior = Math.max(...Object.values(medias), 1);
    const cores = { Bom: '#16a34a', Medio: '#d97706', Ruim: '#dc2626' };
    const nomes = { Bom: 'Bom', Medio: 'Médio', Ruim: 'Ruim' };
    const larguraBarra = Math.min(130, (largura - 100) / 3);

    Object.keys(grupos).forEach((nivel, indice) => {
        const altura = (medias[nivel] / maior) * 180;
        const x = 50 + indice * (largura - 100) / 3 + ((largura - 100) / 3 - larguraBarra) / 2;
        const y = 220 - altura;
        ctx.fillStyle = cores[nivel];
        ctx.fillRect(x, y, larguraBarra, altura);
        ctx.fillStyle = '#1f2937';
        ctx.textAlign = 'center';
        ctx.font = 'bold 16px Arial';
        ctx.fillText(medias[nivel].toFixed(1), x + larguraBarra / 2, y - 8);
        ctx.font = '14px Arial';
        ctx.fillText(nomes[nivel], x + larguraBarra / 2, 245);
    });
}

window.addEventListener('load', desenharGraficoDesempenho);

function gerarAcessoClientes(){
    const link = new URL('Tela/Comercio.php', window.location.href).href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(() => alert(`Link copiado: ${link}`));
    } else {
        window.prompt('Copie o link da área de clientes:', link);
    }
}

   function abrirFormulario(tipo){

    const produto = document.getElementById("informacoes");

    if(tipo === "produto"){
        produto.style.display = "grid";

        produto.scrollIntoView({
            behavior:"smooth",
            block:"start"
        });
    }

    if(tipo !== "produto") return;

    document.getElementById("informacoes")
        .classList.add("abrir");

    document.getElementById("overlay")
        .classList.add("abrir");
}

function fecharFormulario(){

    document.getElementById("informacoes")
        .classList.remove("abrir");

    document.getElementById("overlay")
        .classList.remove("abrir");
}


setTimeout(function(){
    document.querySelector(".mensagem").remove();
}, 3000);
</script>
<script src="acoes/calculos.js"></script>

<script src="acoes/temas.js"></script>
</body>
</html>