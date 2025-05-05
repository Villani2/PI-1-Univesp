<?php
session_start();
include 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario['nome'];
            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos!";
        }
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Distribuidora de Doces</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body class="login">

<div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="submit" value="Entrar">
    </form>
</div>

</body>
</html>
