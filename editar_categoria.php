<?php
include 'cabecalho.php'; // Inclui cabecalho.php, que inicia a sessão e faz a verificação de login
include 'conexao.php';

// --- Início: Lógica para carregar/salvar categoria---

// Inicializa as variáveis de mensagem para evitar Notices se não existirem
$erro_categoria = $_SESSION['erro_categoria'] ?? null;
$sucesso_categoria = $_SESSION['sucesso_categoria'] ?? null;

// Limpa as variáveis de sessão após copiá-las
unset($_SESSION['erro_categoria']);
unset($_SESSION['sucesso_categoria']);


if (!isset($_GET['id'])) {
    // Redireciona se o ID não for fornecido (melhor fazer antes de exibir qualquer HTML)
    header("Location: categorias.php");
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $ativo = isset($_POST['ativo']) ? 1 : 0; // Obtém o valor do checkbox

    // Verifica se a categoria está associada a algum doce antes de inativar
    if ($ativo == 0) { // Se estiver inativando
        $stmt_verificar = $conn->prepare("SELECT COUNT(*) FROM doces WHERE categoria_id = ?");
        $stmt_verificar->bind_param("i", $id);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($count);
        $stmt_verificar->fetch();
        $stmt_verificar->close();

        if ($count > 0) {
            // Define a mensagem de erro na sessão e redireciona para a própria página de edição
            $_SESSION['erro_categoria'] = "Não é possível inativar esta categoria, pois está associada a " . $count . " produto(s).";
            header("Location: editar_categoria.php?id=" . $id); // Redireciona de volta para recarregar a página com a mensagem
            exit;
        }
    }

    // Se chegou aqui, pode atualizar a categoria
    $sql = "UPDATE categorias SET nome = ?, ativo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nome, $ativo, $id);
    $stmt->execute();

    // Verifica se houve erro na execução ou se alguma linha foi afetada
    if ($stmt->error) {
         // Ocorreu um erro SQL
        $_SESSION['erro_categoria'] = "Erro ao atualizar categoria: " . $stmt->error;
         header("Location: editar_categoria.php?id=" . $id); // Redireciona de volta com erro
         exit;
    } elseif ($stmt->affected_rows > 0) {
         // Atualização bem-sucedida
         $_SESSION['sucesso_categoria'] = "Categoria atualizada com sucesso!";
         header("Location: categorias.php"); // Redireciona para a lista de categorias
         exit;
    } else {
         // Nenhuma linha afetada - pode ser que os dados enviados sejam os mesmos que já estavam no BD
         $_SESSION['sucesso_categoria'] = "Nenhuma alteração detectada."; // Mensagem informativa
         header("Location: categorias.php"); // Redireciona para a lista de categorias
         exit;
    }


}

// Se não for um POST (método GET), busca os dados da categoria para exibir no formulário
$sql = "SELECT * FROM categorias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$categoria = $result->fetch_assoc();

if (!$categoria) {
    // Redireciona se a categoria não for encontrada (ID inválido na URL, por exemplo)
     header("Location: categorias.php");
     exit;
}

// --- Fim: Lógica para carregar/salvar categoria e mensagens ---
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Categoria</title>
    <link rel="stylesheet" href="estilo.css">
    </head>

<body class="pagina-editar-categoria">

<div class="conteudo"> <h1>Editar Categoria</h1>

    <?php if ($erro_categoria): ?>
        <p class="mensagem-sistema erro"><?php echo htmlspecialchars($erro_categoria); ?></p> <?php endif; ?>

    <?php if ($sucesso_categoria): ?>
        <p class="mensagem-sistema sucesso"><?php echo htmlspecialchars($sucesso_categoria); ?></p> <?php endif; ?>
    <form method="POST" class="aligned-left-form"> <div class="form-row"> <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($categoria['nome']); ?>" required>
        </div>

        <div class="form-row"> <label for="ativo">Ativo:</label>
            <input type="checkbox" name="ativo" id="ativo" <?php if ($categoria['ativo'] == 1) echo 'checked'; ?>>
             </div>

        <div class="form-row form-buttons-row"> <input type="submit" value="Salvar Alterações">
        </div>
    </form>
    <br><a href="categorias.php" class="btn-voltar">Voltar para edição de categorias</a>

</div> </body>
</html>