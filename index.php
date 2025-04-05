<?php include 'cabecalho.php'; ?>
<?php include 'conexao.php'; ?>

<?php
$busca = $_GET['busca'] ?? '';
$categoria_id = $_GET['categoria_id'] ?? '';
$preco_min = $_GET['preco_min'] ?? '';
$preco_max = $_GET['preco_max'] ?? '';
$estoque_min = $_GET['estoque_min'] ?? '';
$estoque_max = $_GET['estoque_max'] ?? '';
$filtro_status = $_GET['filtro_status'] ?? '';
$ordem = $_GET['ordem'] ?? 'nome';
$direcao = $_GET['direcao'] ?? 'asc';

// validação básica
$colunas_validas = ['nome', 'preco', 'estoque', 'categoria'];
if (!in_array($ordem, $colunas_validas)) $ordem = 'nome';
$direcao = strtolower($direcao) === 'desc' ? 'desc' : 'asc';

$sql = "SELECT d.*, c.nome AS categoria 
        FROM doces d 
        LEFT JOIN categorias c ON d.categoria_id = c.id 
        WHERE d.nome LIKE ?";
$params = ["%$busca%"];
$types = "s";

if ($categoria_id != '') {
    $sql .= " AND d.categoria_id = ?";
    $params[] = $categoria_id;
    $types .= "i";
}
if ($preco_min != '') {
    $sql .= " AND d.preco >= ?";
    $params[] = $preco_min;
    $types .= "d";
}
if ($preco_max != '') {
    $sql .= " AND d.preco <= ?";
    $params[] = $preco_max;
    $types .= "d";
}
if ($estoque_min != '') {
    $sql .= " AND d.estoque >= ?";
    $params[] = $estoque_min;
    $types .= "i";
}
if ($estoque_max != '') {
    $sql .= " AND d.estoque <= ?";
    $params[] = $estoque_max;
    $types .= "i";
}
if ($filtro_status === 'ok') {
    $sql .= " AND d.estoque > 20";
} elseif ($filtro_status === 'baixo') {
    $sql .= " AND d.estoque > 0 AND d.estoque <= 20";
} elseif ($filtro_status === 'zerado') {
    $sql .= " AND d.estoque = 0";
}

$sql .= " ORDER BY $ordem $direcao";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

function gerar_link_ordem($coluna, $texto, $ordem_atual, $direcao_atual) {
    $icone = '';
    $nova_direcao = 'asc';
    if ($ordem_atual === $coluna) {
        if ($direcao_atual === 'asc') {
            $icone = '↑';
            $nova_direcao = 'desc';
        } else {
            $icone = '↓';
        }
    }
    $query = $_GET;
    $query['ordem'] = $coluna;
    $query['direcao'] = $nova_direcao;
    $url = '?' . http_build_query($query);
    return "<a href=\"$url\">$texto $icone</a>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Consulta de Doces</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        .linha-horizontal {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .linha-horizontal input {
            flex: 1;
        }
        .status-ok { color: green; font-weight: bold; }
        .status-alerta { color: orange; font-weight: bold; }
        .status-critico { color: red; font-weight: bold; }
    </style>
</head>
<body>

<form method="GET" style="margin-bottom: 20px;">
    <label>Buscar por nome:</label><br>
    <input type="text" name="busca" value="<?php echo htmlspecialchars($busca); ?>">

    <br><br>
    <label>Categoria:</label><br>
    <select name="categoria_id">
        <option value="">Todas as categorias</option>
        <?php
        $categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
        while ($cat = $categorias->fetch_assoc()) {
            $selecionado = ($categoria_id == $cat['id']) ? 'selected' : '';
            echo "<option value='{$cat['id']}' $selecionado>{$cat['nome']}</option>";
        }
        ?>
    </select>

    <div class="linha-horizontal">
        <input type="number" name="preco_min" step="0.01" placeholder="Preço mín." value="<?php echo $preco_min; ?>">
        <input type="number" name="preco_max" step="0.01" placeholder="Preço máx." value="<?php echo $preco_max; ?>">
    </div>

    <div class="linha-horizontal">
        <input type="number" name="estoque_min" placeholder="Estoque mín." value="<?php echo $estoque_min; ?>">
        <input type="number" name="estoque_max" placeholder="Estoque máx." value="<?php echo $estoque_max; ?>">
    </div>

    <br>
    <label>Status do Estoque:</label><br>
    <select name="filtro_status">
        <option value="" <?php if ($filtro_status === '') echo 'selected'; ?>>Todos</option>
        <option value="ok" <?php if ($filtro_status === 'ok') echo 'selected'; ?>>✅ Estoque Ok</option>
        <option value="baixo" <?php if ($filtro_status === 'baixo') echo 'selected'; ?>>⚠️ Estoque Baixo</option>
        <option value="zerado" <?php if ($filtro_status === 'zerado') echo 'selected'; ?>>❌ Esgotado</option>
    </select>

    <br><br>
    <input type="submit" value="Filtrar">
    <a href="index.php" style="margin-left: 10px;">Limpar Filtros</a>
</form>

<a href="cadastrar.php">Cadastrar novo doce</a> | <a href="categorias.php">Gerenciar categorias</a><br><br>

<table>
    <tr>
        <th>ID</th>
        <th>Imagem</th>
        <th><?php echo gerar_link_ordem('nome', 'Nome', $ordem, $direcao); ?></th>
        <th><?php echo gerar_link_ordem('categoria', 'Categoria', $ordem, $direcao); ?></th>
        <th><?php echo gerar_link_ordem('preco', 'Preço', $ordem, $direcao); ?></th>
        <th><?php echo gerar_link_ordem('estoque', 'Estoque', $ordem, $direcao); ?></th>
        <th>Status</th>
        <th>Descrição</th>
        <th>Ações</th>
    </tr>

    <?php while ($doce = $resultado->fetch_assoc()) { 
        if ($doce['estoque'] == 0) {
            $status = "<span class='status-critico'>❌ Sem estoque</span>";
        } elseif ($doce['estoque'] <= 20) {
            $status = "<span class='status-alerta'>⚠️ Baixo</span>";
        } else {
            $status = "<span class='status-ok'>✅ Ok</span>";
        }
    ?>
    <tr>
        <td><?php echo $doce['id']; ?></td>
        <td>
            <?php if ($doce['imagem']) : ?>
                <img src="imagens/<?php echo $doce['imagem']; ?>" width="60">
            <?php else : ?>
                -
            <?php endif; ?>
        </td>
        <td><a href="produto.php?id=<?php echo $doce['id']; ?>"><?php echo $doce['nome']; ?></a></td>
        <td><?php echo $doce['categoria'] ?? 'Sem categoria'; ?></td>
        <td>R$ <?php echo number_format($doce['preco'], 2, ',', '.'); ?></td>
        <td><?php echo $doce['estoque']; ?></td>
        <td><?php echo $status; ?></td>
        <td><?php echo $doce['descricao']; ?></td>
        <td>
            <a href="editar.php?id=<?php echo $doce['id']; ?>">Editar</a> |
            <a href="excluir.php?id=<?php echo $doce['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este doce?')">Excluir</a>
        </td>
    </tr>
    <?php } ?>
</table>
</body>
</html>