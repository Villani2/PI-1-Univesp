<?php
session_start(); // Inicie a sessão no início do arquivo
include 'cabecalho.php';
include 'conexao.php';

if (!isset($_GET['id'])) {
    header("Location: categorias.php"); // Redireciona se o ID não for fornecido
    exit;
}

$id = $_GET['id'];

// Verifica se a categoria está associada a algum doce
$stmt_verificar = $conn->prepare("SELECT COUNT(*) FROM doces WHERE categoria_id = ?");
$stmt_verificar->bind_param("i", $id);
$stmt_verificar->execute();
$stmt_verificar->bind_result($count);
$stmt_verificar->fetch();
$stmt_verificar->close();

if ($count > 0) {
    $_SESSION['erro_exclusao'] = "Não é possível excluir esta categoria, pois está associada a " . $count . " produto(s).";
} else {
    $sql = "DELETE FROM categorias WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['sucesso_exclusao'] = "Categoria excluída com sucesso!";
}

header("Location: categorias.php");
exit;
?>