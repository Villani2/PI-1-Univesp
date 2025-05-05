<?php
include('conexao.php'); // Conexão padrão

// Inicia a sessão se necessário (pode vir do cabeçalho se for incluído)
// session_start(); // Remova se cabecalho.php já inicia

// Preparar filtros dinâmicos
$condicoes = ["d.estoque > 0"]; // Exibir apenas produtos com estoque > 0 por padrão

if (!empty($_GET['busca'])) {
    $busca = mysqli_real_escape_string($conn, $_GET['busca']);
    $condicoes[] = "d.nome LIKE '%$busca%'";
}

if (!empty($_GET['categoria'])) {
    $categoria = (int) $_GET['categoria'];
    if ($categoria > 0) { // Apenas adicionar filtro se uma categoria válida for selecionada
        $condicoes[] = "d.categoria_id = $categoria";
    }
}

if (!empty($_GET['preco_min'])) {
    // Limpa e valida a entrada
    $preco_min_str = str_replace(',', '.', $_GET['preco_min']);
    if (is_numeric($preco_min_str)) {
       $preco_min = (float) $preco_min_str;
       $condicoes[] = "d.preco >= $preco_min";
    }
}

if (!empty($_GET['preco_max'])) {
    // Limpa e valida a entrada
     $preco_max_str = str_replace(',', '.', $_GET['preco_max']);
    if (is_numeric($preco_max_str)) {
       $preco_max = (float) $preco_max_str;
       $condicoes[] = "d.preco <= $preco_max";
    }
}


$condicaoFinal = implode(' AND ', $condicoes);
if (empty($condicaoFinal)) {
    $condicaoFinal = "1"; // Cláusula WHERE sempre verdadeira se não houver filtros
}


// Definir ordenação
$ordem = "d.nome ASC"; // padrão
if (!empty($_GET['ordenar'])) {
    switch ($_GET['ordenar']) {
        case 'nome_az':
            $ordem = "d.nome ASC";
            break;
        case 'nome_za':
            $ordem = "d.nome DESC";
            break;
        case 'preco_menor':
            $ordem = "d.preco ASC";
            break;
        case 'preco_maior':
            $ordem = "d.preco DESC";
            break;
        default:
            $ordem = "d.nome ASC"; // Padrão se valor inválido
            break;
    }
}

// Buscar produtos com filtros e ordenação - INCLUINDO ESTOQUE
// Removido o comentário PHP // dentro da string SQL
$sql = "SELECT d.id, d.nome, c.nome AS categoria, d.preco, d.descricao, d.imagem, d.estoque
        FROM doces d
        LEFT JOIN categorias c ON d.categoria_id = c.id
        WHERE $condicaoFinal
        ORDER BY $ordem";

$resultado = mysqli_query($conn, $sql);

// Verifica se houve erro na consulta
if (!$resultado) {
    die("Erro na consulta: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Produtos</title>
    <link rel="stylesheet" href="estilo.css">
    </head>
<body class="pagina-catalogo"> <?php // include 'cabecalho.php'; ?>
<div class="conteudo"> <div class="topo-catalogo">
        <h1>Catálogo de Produtos - Distribuidora Estrela Real</h1>
        <a href="index.php" class="btn-geral">Voltar para Home</a> </div>

    <form method="GET" action="catalogo.php" class="filtros">
        <input type="text" name="busca" placeholder="Buscar produto" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">

        <select name="categoria">
            <option value="">Todas as Categorias</option>
            <?php
            $queryCategorias = mysqli_query($conn, "SELECT id, nome FROM categorias ORDER BY nome ASC");
            if ($queryCategorias) { // Verifica se a consulta de categorias foi bem sucedida
                 while($cat = mysqli_fetch_assoc($queryCategorias)):
                    $selected = (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($cat['nome']); ?>
                    </option>
                <?php endwhile;
            } else {
                 echo "<option value=''>Erro ao carregar categorias</option>";
            }
            ?>
        </select>

        <input type="text" name="preco_min" placeholder="Preço mínimo" value="<?php echo isset($_GET['preco_min']) ? htmlspecialchars($_GET['preco_min']) : ''; ?>">
        <input type="text" name="preco_max" placeholder="Preço máximo" value="<?php echo isset($_GET['preco_max']) ? htmlspecialchars($_GET['preco_max']) : ''; ?>">

        <select name="ordenar">
            <option value="">Ordenar</option>
            <option value="nome_az" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'nome_az') ? 'selected' : ''; ?>>Nome A-Z</option>
            <option value="nome_za" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'nome_za') ? 'selected' : ''; ?>>Nome Z-A</option>
            <option value="preco_menor" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'preco_menor') ? 'selected' : ''; ?>>Preço Menor → Maior</option>
            <option value="preco_maior" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] == 'preco_maior') ? 'selected' : ''; ?>>Preço Maior → Menor</option>
        </select>

        <button type="submit">Buscar</button>
    </form>

    <div class="produtos">
    <?php if (mysqli_num_rows($resultado) > 0): ?>
        <?php while($produto = mysqli_fetch_assoc($resultado)): ?>
            <div class="produto">
                <?php
                    // Lógica para exibir a imagem (produto ou categoria padrão)
                    $caminho_imagem = "imagens/categorias/default.png"; // Imagem padrão

                    if (!empty($produto['imagem'])) {
                        $caminho_imagem_produto = "imagens/" . htmlspecialchars($produto['imagem']);
                         if (file_exists($caminho_imagem_produto)) {
                             $caminho_imagem = $caminho_imagem_produto;
                         }
                    } elseif (!empty($produto['categoria'])) {
                        // Lógica para imagem de categoria (semelhante ao que você tinha)
                        $categoria_slug = strtolower(trim($produto['categoria']));
                        $categoria_slug = str_replace(
                            ['á','à','â','ã','é','è','ê','í','ì','î','ó','ò','ô','õ','ú','ù','û','ç',' '],
                            ['a','a','a','a','e','e','e','i','i','i','o','o','o','o','u','u','u','c','_'],
                            $categoria_slug
                        );
                        $caminho_imagem_categoria = "imagens/categorias/{$categoria_slug}.png";
                        if (file_exists($caminho_imagem_categoria)) {
                            $caminho_imagem = $caminho_imagem_categoria;
                        }
                    }
                ?>
                <img src="<?php echo $caminho_imagem; ?>" alt="Imagem do Produto">

                <h2><?php echo htmlspecialchars($produto['nome']); ?></h2>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($produto['categoria'] ?? 'Sem categoria'); ?></p>
                <p class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                 <p><?php echo nl2br(htmlspecialchars(substr($produto['descricao'], 0, 100)) . (strlen($produto['descricao']) > 100 ? '...' : '')); ?></p>

                 <p class="estoque-catalogo"><strong>Estoque:</strong> <?php echo htmlspecialchars($produto['estoque']); ?></p>
                 </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhum produto encontrado com os filtros aplicados.</p>
    <?php endif; ?>
    </div>

</div> </body>
</html>