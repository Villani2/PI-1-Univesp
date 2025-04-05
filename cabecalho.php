<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<div class="topo">
    <span class="titulo">Distribuidora de Doces</span>
    <span class="usuario">Olá, <?php echo $_SESSION['usuario']; ?> | <a href="logout.php">Sair</a></span>
</div>
