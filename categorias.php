<?php
include 'cabecalho.php';
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $sql = "INSERT INTO categorias (nome) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();

    header("Location: categorias.php");
    exit;
}

// Modificação na consulta SQL para contar os doces por categoria
$sql = "SELECT c.*, COUNT(d.id) AS quantidade_doces 
        FROM categorias c
        LEFT JOIN doces d ON c.id = d.categoria_id
        GROUP BY c.id
        ORDER BY c.nome ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Categorias</title>
</head>

<body>
    <h1>Cadastro de Categorias</h1>

    <form method="POST">
        <label>Nome da categoria:</label>
        <input type="text" name="nome" required>
        <input type="submit" value="Cadastrar" class="form-row form-buttons-row">
    </form>

    <?php if (isset($_SESSION['erro_exclusao'])): ?>
        <p class="mensagem-sistema erro" style="color: red;"><?php echo $_SESSION['erro_exclusao']; ?></p>
        <?php unset($_SESSION['erro_exclusao']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso_exclusao'])): ?>
        <p class="mensagem-sistema sucesso"style="color: green;"><?php echo $_SESSION['sucesso_exclusao']; ?></p>
        <?php unset($_SESSION['sucesso_exclusao']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro_categoria'])): ?>
        <p class="mensagem-sistema erro"style="color: red;"><?php echo $_SESSION['erro_categoria']; ?></p>
        <?php unset($_SESSION['erro_categoria']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso_categoria'])): ?>
        <p class="mensagem-sistema sucesso"style="color: green;"><?php echo $_SESSION['sucesso_categoria']; ?></p>
        <?php unset($_SESSION['sucesso_categoria']); ?>
    <?php endif; ?>

    <h2>Categorias Cadastradas</h2>
    <table class="categorias-table"> <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Quantidade de Produtos</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php while ($cat = $resultado->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><?php echo htmlspecialchars($cat['nome']); ?></td>
                <td><?php echo $cat['quantidade_doces']; ?></td>
                <td>
                    <?php
                    if ($cat['ativo'] == 1) {
                        echo "<span class='ativo'>Ativo</span>";
                    } else {
                        echo "<span class='inativo'>Inativo</span>";
                    }
                    ?>
                </td>
                <td>
                    <a href="editar_categoria.php?id=<?php echo $cat['id']; ?>">Editar</a> |
                    <a href="excluir_categoria.php?id=<?php echo $cat['id']; ?>"
                       onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="index.php" class="btn-voltar">Voltar para a Home</a>
</body>

</html>