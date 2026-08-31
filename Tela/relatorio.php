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
<link rel="stylesheet" href="../estilo/relatorio.css">
<title>Relatório</title>
</head>
<body>
    <?php include 'menu.php'; 
    include "../process/indicadores.php";
    ?>
    
    <header>
            <h2>Relatório</h2>

    </header>
<main>
<button onclick= "gerarPDF()">Gerar PDF</button>

   <!---Gerar Relatorio---->
<div id="relatorio">

</div> 
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
const produtos = <?= json_encode($resultado) ?>;
</script>

<script src="../acoes/relatorio.js">
</script>
<script src="../acoes/temas.js"></script>


</body>
</html>