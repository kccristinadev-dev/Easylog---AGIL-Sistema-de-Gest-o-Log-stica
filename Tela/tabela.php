<?php
session_start();

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabela</title>

    <link rel="stylesheet" href="../estilo/tabela.css">
</head>

<body>
    <?php include 'menu.php';
            include "../process/conexao.php";

    include "../process/indicadores.php";
    ?>

<header>
<h2>Tabela de Produtos</h2>
</header>

<main>

    <section id="tabela" class="tela">

        <div id="header-tabela">

            <input
                type="text"
                name="pesquisa"
                id="pesquisa-produto"
                placeholder="Procurar produto"
                oninput="filtrarTabela()"
            />

            <select id="filtro-periodo" aria-label="Filtrar por período" onchange="filtrarTabela()">
                <option value="todos">Todos os períodos</option>
                <option value="Dia">Dia</option>
                <option value="Mes">Mês</option>
                <option value="Ano">Ano</option>
            </select>

            <div class="botoes-filtro">

                <button class="botao-filtro" type="button" onclick="mostrarIndicador('todos')">
                    Todos
                </button>

                <button class="botao-filtro" type="button" onclick="mostrarIndicador(1)">
                    Giro
                </button>

                <button class="botao-filtro" type="button" onclick="mostrarIndicador(2)">
                    Consumo
                </button>

                <button class="botao-filtro" type="button" onclick="mostrarIndicador(3)">
                    Cobertura
                </button>

                <button class="botao-filtro" type="button" onclick="mostrarIndicador(4)">
                    Ruptura
                </button>

                <button class="botao-filtro" type="button" onclick="gerarPDF()">
                    Gerar PDF
                </button>
        <button id="apagarTudo" class="botao-filtro" type="button">
            Excluir Tudo
        </button>

            </div>

        </div>

        <section id="tabela-C">
            <div id="header-graficos" class="header">
           
                <div class="preenchimento linha">

                    <table id="tabela-produtos">

                        <thead>
                            <tr>
                                <th scope="col">Produto</th>
                                <th scope="col">Giro</th>
                                <th scope="col">Consumo</th>
                                <th scope="col">Cobertura</th>
                                <th scope="col">Ruptura</th>
                                <th scope="col">Editar</th>
                               
                                <th scope="col">Excluir</th>
                            </tr>

                        </thead>


                        <tbody id="graficos">
                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </section>
    <!---Editar---->

<form id="editarFor" style="display: none;" accept-charset="utf-8">
<button type="button" id="sair" onclick="voltar()">   &#8656; voltar </button>
<h3>Informaçoes do Produto</h3>
<div class="campo">
<label for="nomeproduto"> Produto</label> <input type="text" id="noproduto" placeholder="produto-A">
</div>
<div class="campo">
<label for="esFisico"> Estoque Físico</label> <input type="number" id="esFisico" placeholder="Ex: 200">
</div>
<div class="campo">
<label for="esVirtual"> Estoque Virtual</label> <input type="number" id="esvirtual" placeholder="Ex: 150">
</div>
<div class="campo">
<label for="esVenda"> Total de vendas</label> <input type="number" id="TotalDevendas" placeholder="Ex: 200">
</div>
<div class="campo">
<label for="tempo"> Tempo de Reposição (dias)</label> <input type="number" id="tempo" placeholder="Ex: 10">
</div>
<div class="campo">
<label for="totalPedido"> Total de Pedidos</label> <input type="number" id="totalPedido" placeholder="Ex: 300">
</div>
<button type="button" class="editar" onclick="salvarEdicao()"> Editar</button>
</form>
<div id="form-excluir" style="display: none;">
<p>
Tem certeza que deseja excluir tudo?
</p>
<div class="alinhar">

<button type="button"  class="cancelar">Cancelar</button>
<button type="button" class="confirmar">Excluir</button>
</div>
</div>


</main>
<script>
const produtos = <?= json_encode($resultado) ?>;
</script>

<script src="../acoes/temas.js"></script>

<script src="../acoes/tabela.js"></script>

</body>
</html>