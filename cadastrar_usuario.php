<?php
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

$erro = ''; // Variável para armazenar mensagens de erro
$sucesso = ''; // Variável para armazenar mensagens de sucesso

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // O formulário foi submetido

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validação dos dados
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } elseif ($senha != $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) { // Exemplo: Mínimo de 6 caracteres para a senha
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        // Validação passou, vamos cadastrar o usuário

        // Hash da senha (usando password_hash - MAIS SEGURO)
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Verifica se o e-mail já existe
        $stmt_verificar = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_verificar->bind_param("s", $email);
        $stmt_verificar->execute();
        $stmt_verificar->store_result();

        if ($stmt_verificar->num_rows > 0) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            // Insere o usuário no banco de dados
            $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nome, $email, $senha_hash);

            if ($stmt->execute()) {
                $sucesso = "Usuário cadastrado com sucesso! <a href='login.php'>Faça login</a>";
            } else {
                $erro = "Erro ao cadastrar usuário: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Cadastrar Usuário</h1>

    <?php if ($erro): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p style="color: green;"><?php echo $sucesso; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <label>Confirmar Senha:</label><br>
        <input type="password" name="confirmar_senha" required><br><br>

        <input type="submit" value="Cadastrar">
    </form>

    <br>
    <a href="login.php">Voltar para o Login</a>
</body>
</html>