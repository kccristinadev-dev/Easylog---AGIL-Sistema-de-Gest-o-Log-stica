
<!DOCTYPE html>
<html lang="pt-br">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta http-equiv="X-UA-Compatible" content="ie=edge">
 <!-- Importa ícones do Font Awesome -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 <title>Cadastro form- MYSQLI</title>
 <link rel="stylesheet" href="../estilo/cadastro.css?v=<?= time() ?>">

</head>
<body>

<header>

</header>

<?php
// Verifica se existe status e mensagem na URL para exibir alert
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $classe = $_GET['status'] === 'sucesso' ? 'sucesso' : 'erro';
    echo '<div class="mensagem '.$classe.'">'.htmlspecialchars($_GET['msg']).'</div>';
}
?>

<div class="container-form">

    <!-- Formulário para cadastro de aluno -->
    <form action="../process/cadastro.php" method="post" accept-charset="utf-8">
                <!-- Link para voltar à página inicial -->

        <h2>Cadastro</h2><br>
        
        <label for="nome">Nome: 
            <input type="text" name="nome" required>
        </label>
        <label for="email">Email:
            <input type="email" name="email" required>
        </label>
        <label for="senha">Senha:
            <input type="password" name="senha" required>
        </label>
        <label for="tipo_de_usuario">Tipo de conta:
            <select name="tipo_de_usuario" id="tipo_de_usuario" required>
                <option value="cliente">Cliente</option>
                <option value="administrador">Administrador</option>
            </select>
        </label>

        <button type="submit" name="botao-adicionar">    
            <i>Adicionar</i>
        </button>
    <a href="../index.php" class="voltar-Pinicio">
        <i class="seta">voltar</i>
    </a>   
    </form>
</div>

</body>

<script>
// Seleciona a mensagem de alerta
const msg = document.querySelector('.mensagem');
if(msg) {
    // Depois de 3 segundos, inicia fade out da mensagem
    setTimeout(() => {
      msg.style.transition = "opacity 0.5s";
      msg.style.opacity = 0;
      // Após o fade, remove do DOM
      setTimeout(() => msg.remove(), 500);
    }, 3000); // 3000ms = 3 segundos
}
</script>

</html>