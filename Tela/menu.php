<?php
session_start();

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}

$pagina = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
<link rel="stylesheet" href="../estilo/menu.css">
</head>

<body>

    <nav id="menu">

<a href="../inicio.php" class="menu-btn <?= $pagina == 'inicio.php' ? 'ativo' : '' ?>">
    Início 
</a>

<a href="../Tela/tabela.php" class="menu-btn <?= $pagina == 'tabela.php' ? 'ativo' : '' ?>">
    Tabela
</a>

<a href="../Tela/graficos.php" class="menu-btn <?= $pagina == 'graficos.php' ? 'ativo' : '' ?>">
    Gráficos
</a>

<a href="../Tela/relatorio.php" class="menu-btn <?= $pagina == 'relatorio.php' ? 'ativo' : '' ?>">
    Relatório
</a>

<a href="../Tela/Comercio.php" class="menu-btn <?= $pagina == 'Comercio.php' ? 'ativo' : '' ?>">
    Produtos
</a>

<a href="../Tela/entregas.php" class="menu-btn <?= $pagina == 'entregas.php' ? 'ativo' : '' ?>">
    Entregas
</a>


    </nav>
<script src="../acoes/temas.js"></script>
</body>

</html>