<?php include 'cabecalho.php'; ?>
<?php include 'conexao.php'; ?>

<?php
if (!isset($_GET['id'])) {
    die("ID do doce não informado.");
}

$id = $_GET['id'];

$sql = "DELETE FROM doces WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
exit;
?>
