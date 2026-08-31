<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Essenciais -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Compatibilidade -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO básico -->
    <title>Login</title>
    <meta name="description" content="Descrição curta da página para Google e compartilhamento.">
    <meta name="keywords" content="html, css, javascript, site">
    <meta name="author" content="Seu Nome">

    <!-- Indexação -->
    <meta name="robots" content="index, follow">

    <!-- Tema navegador mobile -->
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph (redes sociais) -->
    <meta property="og:title" content="Título da Página">
    <meta property="og:description" content="Descrição para redes sociais.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://seusite.com">
    <meta property="og:image" content="https://seusite.com/imagem.jpg">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Título da Página">
    <meta name="twitter:description" content="Descrição para Twitter/X">
    <meta name="twitter:image" content="https://seusite.com/imagem.jpg">

    <!-- Ícone -->
    <link rel="icon" href="favicon.ico" type="image/x-icon">
<title>Projeto</title>
<link rel="stylesheet" href="estilo/login.css?v=123">
</head>


<!-- Classe inicial do tema -->
<body>
<?php
// Verifica se existe status e mensagem na URL para exibir alert
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $classe = $_GET['status'] === 'sucesso' ? 'sucesso' : 'erro';
 echo '
<div class="mensagem '.$classe.'">'.htmlspecialchars($_GET['msg']).'</div>';
}
?>


<!-- Botão que alterna tema -->
 <button id="theme-toggle">☀️</button>   

<div class="login-container">
    <!-- Título -->
    <h2>Bem-vindo(a)!</h2>
    <p>Faça login para acessar o sistema.</p>

    <!-- Área dos cards -->

<form action="process/login.php" method="POST">
    
  <input type="email" name="email" placeholder="E-mail" required>
  <input type="password" name="senha" placeholder="Senha" required>

  <button type="submit">Entrar</button>
</form>

<p>
  Não tem conta?
  <a href="Tela/cadastro.php">Cadastre-se</a>
</p>

</div>

<script src="acoes/temas.js"></script>

</body>


</html>