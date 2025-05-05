<?php
// session_start(); // Remova esta linha se cabecalho.php já inicia a sessão
include 'cabecalho.php'; // Inclui cabecalho.php, que inicia a sessão e faz a verificação de login
include 'conexao.php';

// --- Início: Lógica para processar POST ou carregar dados ---

// Inicializa as variáveis de mensagem da sessão
$erro_edicao = $_SESSION['erro_edicao'] ?? null;
$sucesso_edicao = $_SESSION['sucesso_edicao'] ?? null;

// Limpa as variáveis de sessão após copiá-las
unset($_SESSION['erro_edicao']);
unset($_SESSION['sucesso_edicao']);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Processar os dados do formulário enviado (POST)
    $id = $_POST['id']; // Pega o ID do input hidden
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $descricao = $_POST['descricao'];
    $categoria_id = $_POST['categoria_id'];
    $estoque = $_POST['estoque'];
    $imagem_atual = $_POST['imagem_atual'];
    $imagem = $imagem_atual; // Mantém a imagem atual por padrão

    // Lógica para upload de nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem = uniqid() . '.' . $ext; // Gera nome único para a nova imagem
        $upload_path = "imagens/" . $imagem;

        // Tenta mover o arquivo temporário para a pasta de imagens
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
             // Se houver erro no upload, define mensagem de erro e redireciona
            $_SESSION['erro_edicao'] = "Erro ao fazer upload da imagem.";
            header("Location: editar.php?id=" . $id); // Redireciona de volta para a página de edição
            exit;
        }
        // Se o upload for bem sucedido, opcionalmente remove a imagem antiga
        if ($imagem_atual && file_exists("imagens/" . $imagem_atual)) {
             unlink("imagens/" . $imagem_atual); // Remove o arquivo antigo
        }
    }

    // Prepara e executa a consulta de atualização no banco de dados
    $sql = "UPDATE doces SET nome = ?, preco = ?, descricao = ?, categoria_id = ?, imagem = ?, estoque = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    // "sdsisii" - Tipos dos parâmetros: string, double, string, integer, string, integer, integer
    $stmt->bind_param("sdsisii", $nome, $preco, $descricao, $categoria_id, $imagem, $estoque, $id);
    $stmt->execute();

    // Verifica se houve erro na execução da consulta SQL
    if ($stmt->error) {
         // Ocorreu um erro SQL
        $_SESSION['erro_edicao'] = "Erro ao atualizar produto: " . $stmt->error;
         header("Location: editar.php?id=" . $id); // Redireciona de volta com erro
         exit;
    } elseif ($stmt->affected_rows > 0) {
         // Atualização bem-sucedida (pelo menos uma linha foi modificada)
         $_SESSION['sucesso_edicao'] = "Produto atualizado com sucesso!";
         header("Location: index.php"); // Redireciona para a lista de doces
         exit;
    } else {
         // Nenhuma linha afetada (pode ser que os dados enviados sejam os mesmos já no BD)
         $_SESSION['sucesso_edicao'] = "Nenhuma alteração detectada."; // Mensagem informativa
         header("Location: index.php"); // Redireciona para a lista de doces
         exit;
    }

} else {
    // Lógica para carregar os dados do doce para exibição no formulário (método GET)
    if (!isset($_GET['id'])) {
        // Se o ID não for fornecido na URL, redireciona para a lista
        // die("ID do doce não informado."); // Ou redirecionar
        header("Location: index.php");
        exit;
    }
    $id = $_GET['id'];
    $sql = "SELECT * FROM doces WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doce = $result->fetch_assoc(); // Busca os dados do doce

    if (!$doce) {
         // Se o doce não for encontrado com o ID fornecido, redireciona
         // die("Doce não encontrado."); // Ou redirecionar
         header("Location: index.php");
         exit;
    }
}

// --- Fim: Lógica PHP ---
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="estilo.css">
    </head>
<body class="pagina-editar-doce">

<div class="conteudo"> <h1>Editar Produto</h1>

    <?php if ($erro_edicao): ?>
        <p class="mensagem-sistema erro"><?php echo htmlspecialchars($erro_edicao); ?></p> <?php endif; ?>

    <?php if ($sucesso_edicao): ?>
        <p class="mensagem-sistema sucesso"><?php echo htmlspecialchars($sucesso_edicao); ?></p> <?php endif; ?>


    <form method="POST" enctype="multipart/form-data" class="aligned-left-form"> <input type="hidden" name="id" value="<?php echo htmlspecialchars($doce['id']); ?>">
        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($doce['imagem']); ?>">

        <div class="form-row"> <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($doce['nome']); ?>" required>
        </div>

        <div class="form-row"> <label for="preco">Preço:</label>
            <input type="number" name="preco" id="preco" step="0.01" value="<?php echo htmlspecialchars($doce['preco']); ?>" required>
        </div>

        <div class="form-row"> <label for="categoria_id">Categoria:</label>
            <select name="categoria_id" id="categoria_id" required>
                <option value="">Selecione</option>
                <?php
                $cats = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
                if ($cats) { // Verifica se a consulta foi bem sucedida
                    while ($c = $cats->fetch_assoc()) {
                        $selected = ($c['id'] == $doce['categoria_id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $selected>{$c['nome']}</option>";
                    }
                } else {
                     // Opcional: exibir mensagem de erro se as categorias não puderem ser carregadas
                     // echo "<option value=''>Erro ao carregar categorias</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-row"> <label for="descricao">Descrição:</label>
            <textarea name="descricao" id="descricao" required><?php echo htmlspecialchars($doce['descricao']); ?></textarea>
        </div>

        <div class="form-row"> <label for="imagem">Imagem:</label><br>
            <?php if ($doce['imagem']) : ?>
                <img src="imagens/<?php echo htmlspecialchars($doce['imagem']); ?>" width="120"><br>
            <?php else : ?>
                Nenhuma imagem cadastrada<br>
            <?php endif; ?>
            <input type="file" name="imagem" id="imagem" accept="image/*">
        </div>

        <div class="form-row"> <label for="estoque">Estoque:</label>
            <input type="number" name="estoque" id="estoque" min="0" value="<?php echo htmlspecialchars($doce['estoque']); ?>" required>
        </div>

        <div class="form-row form-buttons-row"> <input type="submit" value="Salvar Alterações">
        </div>
    </form>
    <a href="index.php">← Voltar para a lista de produtos</a>
    

</div> </body>
</html>