<?php include 'cabecalho.php'; ?>
<?php include 'conexao.php'; ?>

<?php
if (!isset($_GET['id'])) {
    die("ID do doce não informado.");
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $descricao = $_POST['descricao'];
    $categoria_id = $_POST['categoria_id'];
    $estoque = $_POST['estoque'];
    $imagem_atual = $_POST['imagem_atual'];
    $imagem = $imagem_atual;

    if (!empty($_FILES['imagem']['name'])) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "imagens/" . $imagem);
    }

    $sql = "UPDATE doces SET nome = ?, preco = ?, descricao = ?, categoria_id = ?, imagem = ?, estoque = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsisii", $nome, $preco, $descricao, $categoria_id, $imagem, $estoque, $id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM doces WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$doce = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Doce</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Editar Doce</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imagem_atual" value="<?php echo $doce['imagem']; ?>">

        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($doce['nome']); ?>" required>

        <label>Preço:</label>
        <input type="number" name="preco" step="0.01" value="<?php echo $doce['preco']; ?>" required>

        <label>Categoria:</label>
        <select name="categoria_id" required>
            <option value="">Selecione</option>
            <?php
            $cats = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
            while ($c = $cats->fetch_assoc()) {
                $selected = ($c['id'] == $doce['categoria_id']) ? 'selected' : '';
                echo "<option value='{$c['id']}' $selected>{$c['nome']}</option>";
            }
            ?>
        </select>

        <label>Descrição:</label>
        <textarea name="descricao" required><?php echo htmlspecialchars($doce['descricao']); ?></textarea>

        <label>Imagem:</label><br>
        <?php if ($doce['imagem']) : ?>
            <img src="imagens/<?php echo $doce['imagem']; ?>" width="120"><br>
        <?php else : ?>
            Nenhuma imagem cadastrada<br>
        <?php endif; ?>
        <input type="file" name="imagem" accept="image/*"><br><br>

        <label>Estoque:</label>
        <input type="number" name="estoque" min="0" value="<?php echo $doce['estoque']; ?>" required>

        <input type="submit" value="Salvar Alterações">
    </form>
</body>
</html>
