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
    <title>Gráficos de Indicadores</title>

    <link rel="stylesheet" href="../estilo/grafico.css">
</head>
<body>
    <?php include 'menu.php';
    include "../process/indicadores.php";

    ?>
    <header>
        <h2>Indicadores Logísticos</h2>
        <p>
            Resultados
        </p>
    </header>

    <main id="graficos" class="graficos-coluna">
        <div class="resumo">
            <p>   Total de produtos: 
               <span>
               <?= $totalGeral ?> </span>

            </p>
            
        </div>
        <div class="grafico-box">
            <h4>Giro de Estoque</h4>
            <div class="grafico-scroll">
                <canvas id="graficoGiro"></canvas>
            </div>
        </div>

        <div class="grafico-box">
            <h4>Consumo Médio</h4>
            <div class="grafico-scroll">
                <canvas id="graficoConsumo"></canvas>
            </div>
        </div>

        <div class="grafico-box">
            <h4>Cobertura de Estoque</h4>
            <div class="grafico-scroll">
                <canvas id="graficoCobertura"></canvas>
            </div>
        </div>

        <div class="grafico-box">
            <h4>Índice de Ruptura</h4>
            <div class="grafico-scroll">
                <canvas id="graficoRuptura"></canvas>
            </div>
        </div>

    </main>
    <script>
        const produtos = <?= json_encode($resultado) ?>;
    </script>

    <script src="../acoes/temas.js"></script>
    <script src="../acoes/graficos.js"></script>

</body>
</html>