
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$estaLogado = isset($_SESSION['usuario_id']);
$tipoUsuario = $_SESSION['tipo_usuario'] ?? null;

$paginaAtual = basename($_SERVER['PHP_SELF']);
?>

<header class="site-header">
    <div class="container header-container">

        <a href="/Arthur351/NatáliaJoiasTCC26/index.php" class="logo">
            Natália Milanese Joias
        </a>

        <nav class="site-nav">
            <a
                href="/Arthur351/NatáliaJoiasTCC26/index.php"
                class="<?= $paginaAtual === 'index.php' ? 'ativo' : '' ?>"
            >Início</a>

            <a
                href="/Arthur351/NatáliaJoiasTCC26/produtos.php"
                class="<?= $paginaAtual === 'produtos.php' ? 'ativo' : '' ?>"
            >Produtos</a>

            <?php if (!$estaLogado): ?>

                <a href="/Arthur351/NatáliaJoiasTCC26/login.php">
                    Entrar
                </a>

                <a href="/Arthur351/NatáliaJoiasTCC26/cadastro.php">
                    Criar conta
                </a>

            <?php elseif ($tipoUsuario === 'cliente'): ?>

                <a href="/Arthur351/NatáliaJoiasTCC26/cliente/minha_conta.php">
                    Minha conta
                </a>

                <a href="/Arthur351/NatáliaJoiasTCC26/cliente/meus_pedidos.php">
                    Meus pedidos
                </a>

                <a href="/Arthur351/NatáliaJoiasTCC26/carrinho.php">
                    Carrinho
                </a>

                <a href="/Arthur351/NatáliaJoiasTCC26/logout.php">
                    Sair
                </a>

            <?php elseif ($tipoUsuario === 'admin'): ?>

                <a href="/Arthur351/NatáliaJoiasTCC26/admin/index.php">
                    Administração
                </a>

                <a href="/Arthur351/NatáliaJoiasTCC26/logout.php">
                    Sair
                </a>

            <?php endif; ?>
        </nav>

    </div>
</header>
