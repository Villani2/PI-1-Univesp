<?php include 'cabecalho.php'; ?>
<?php include 'conexao.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $descricao = $_POST['descricao'];
    $categoria_id = $_POST['categoria_id'];
    $estoque = $_POST['estoque'];

    $imagem = null;
    if (!empty($_FILES['imagem']['name'])) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "imagens/" . $imagem);
    }

    $sql = "INSERT INTO doces (nome, preco, descricao, categoria_id, imagem, estoque) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsisi", $nome, $preco, $descricao, $categoria_id, $imagem, $estoque);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Doce</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Cadastrar Novo Doce</h1>
    <form method="POST" enctype="multipart/form-data">
        <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>Preço:</label>
        <input type="number" name="preco" step="0.01" required>

        <label>Categoria:</label>
        <select name="categoria_id" required>
            <option value="">Selecione</option>
            <?php
            $cats = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
            while ($c = $cats->fetch_assoc()) {
                echo "<option value='{$c['id']}'>{$c['nome']}</option>";
            }
            ?>
        </select>

        <label>Descrição:</label>
        <textarea name="descricao" required></textarea>

        <label>Imagem:</label>
        <input type="file" name="imagem" accept="image/*">

        <label>Estoque:</label>
        <input type="number" name="estoque" min="0" required>

        <input type="submit" value="Cadastrar">
    </form>
    <br><a href="index.php">Voltar para a lista de doces</a>
</body>
</html>