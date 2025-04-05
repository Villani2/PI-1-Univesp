<?php include 'cabecalho.php'; ?>
<?php include 'conexao.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $sql = "INSERT INTO categorias (nome) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
}

$resultado = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Categorias</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Cadastro de Categorias</h1>

    <form method="POST">
        <label>Nome da categoria:</label>
        <input type="text" name="nome" required>
        <input type="submit" value="Cadastrar">
    </form>

    <br><a href="index.php">Voltar</a>

    <h2>Categorias Cadastradas</h2>
    <ul>
        <?php while ($cat = $resultado->fetch_assoc()) { ?>
            <li><?php echo $cat['nome']; ?></li>
        <?php } ?>
    </ul>
</body>
</html>
