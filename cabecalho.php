<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Distribuidora Estrela Real</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<div class="topo">
    <div class="topo-conteudo">
        <div class="topo-esquerda">
            <span class="titulo">Distribuidora Estrela Real</span>
            <div class="menu">
                <a href="index.php">Início</a>
                <a href="cadastrar.php">Cadastrar Produto</a>
                <a href="categorias.php">Gerenciar Categorias</a>
                <a href="catalogo.php">Catálogo de Produtos</a>
            </div>
        </div>
        <div class="usuario">
            <span>Olá, <?php echo $_SESSION['usuario']; ?></span>
            <a class="sair" href="logout.php">Sair</a>
        </div>
    </div>
</div>
