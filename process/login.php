<?php
session_start();
include 'conexao.php';

try {
    

if (!empty($_POST['email']) && !empty($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../Tela/cadastro.php?status=erro&msg=" . urlencode("Email inválido"));
    exit();
}

$stmt = $conexao->prepare("SELECT * FROM usuario WHERE email = ?");
$stmt->execute([$email]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($senha, $usuario['senha'])) {
$_SESSION['usuario'] = [
    'id' => $usuario['id'],
    'nome' => $usuario['nome'],
    'email' => $usuario['email'],
    'tipo_de_usuario' => $usuario['tipo_de_usuario']
];

if ($_SESSION['usuario']['tipo_de_usuario'] === 'administrador') {
    header("Location: ../inicio.php");
    exit();
} else {
    header("Location: ../Tela/Comercio.php");
    exit();
}
    } 
    else {
        header("Location: ../index.php?status=erro&msg=" . urlencode("Senha incorreta"));
        exit();
    }
} else {
    header("Location: ../Tela/cadastro.php?status=erro&msg=" . urlencode("Usuário inexistente, Cadastre-se"));
    exit();
}
} catch (PDOException $e) {
    // Log opcional: error_log($e->getMessage());
    header("Location: ../index.php?status=erro&msg=" . urlencode("Erro interno do servidor"));
    exit();
}
?>