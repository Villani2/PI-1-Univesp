<?php include 'cabecalho.php'; ?>
<?php include 'conexao.php'; ?>

<?php
if (!isset($_GET['id'])) {
    die("Produto não informado.");
}

$id = $_GET['id'];

$sql = "SELECT d.*, c.nome AS categoria 
        FROM doces d 
        LEFT JOIN categorias c ON d.categoria_id = c.id 
        WHERE d.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$doce = $result->fetch_assoc();

if (!$doce) {
    die("Produto não encontrado.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($doce['nome']); ?></title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1><?php echo htmlspecialchars($doce['nome']); ?></h1>

    <?php if ($doce['imagem']) : ?>
        <img src="imagens/<?php echo $doce['imagem']; ?>" width="250" style="margin-bottom: 20px;"><br>
    <?php endif; ?>

    <p><strong>Categoria:</strong> <?php echo $doce['categoria'] ?? 'Sem categoria'; ?></p>
    <p><strong>Preço:</strong> R$ <?php echo number_format($doce['preco'], 2, ',', '.'); ?></p>
    <p><strong>Estoque:</strong> <?php echo $doce['estoque']; ?></p>
    <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($doce['descricao'])); ?></p>

    <br>
    <a href="index.php">← Voltar para a lista</a>
</body>
</html>