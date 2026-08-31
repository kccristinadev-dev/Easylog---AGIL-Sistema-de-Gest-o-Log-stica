<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuração do servidor e credenciais do banco de dados
include 'conexao.php';



if (
   !empty($_POST['nome']) &&
    !empty($_POST['email']) &&
    !empty($_POST['senha']) 
)
{
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senhavalida = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../tela/cadastro.php?status=erro&msg=" . urlencode("Email inválido"));
    exit();
}  
 try {
     

$check = $conexao->prepare("SELECT * FROM usuario WHERE email = ?");
$check->execute([$_POST['email']]);

if ($check->rowCount() > 0) {
    header("Location: ../tela/cadastro.php?status=erro&msg=" . urlencode("E-mail já cadastrado"));
    exit();
}



$stmt = $conexao->prepare("INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)");

if ($stmt->execute([$nome, $email, $senhavalida])) {
    header("Location: ../tela/cadastro.php?status=sucesso&msg=" . urlencode("Cadastro feito com sucesso"));
} else {
    header("Location: ../tela/cadastro.php?status=erro&msg=" . urlencode("Erro ao cadastrar"));
}
}

  catch (PDOException $e) {
    header("Location: ../tela/cadastro.php?status=erro&msg=" . urlencode("Erro interno do servidor"));
    exit();
}
}
else {
    header("Location: ../tela/cadastro.php?status=erro&msg=" . urlencode("Preencha todos os campos"));
}
exit();
?>