<?php

require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: produtos.php');
    exit;
}

$sql = "SELECT
            produtos.id,
            produtos.nome,
            produtos.descricao,
            produtos.preco,
            produtos.tipo_material,
            produtos.estoque,
            produtos.imagem,
            categorias.nome AS categoria_nome
        FROM produtos
        INNER JOIN categorias
            ON produtos.categoria_id = categorias.id
        WHERE produtos.id = :id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: produtos.php');
    exit;
}

$logado = isset($_SESSION['usuario_id']);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($produto['nome']) ?>
        - Natália Milanese Joias
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <header class="site-header">

        <div class="header-container">

            <a href="index.php" class="logo">
                Natália Milanese Joias
            </a>

            <nav class="site-nav">

                <a href="index.php">
                    Início
                </a>

                <a href="produtos.php">
                    Produtos
                </a>

                <?php if ($logado): ?>

                    <a href="cliente/minha_conta.php">
                        Minha conta
                    </a>

                    <a href="carrinho.php">
                        Carrinho
                    </a>

                    <a href="logout.php">
                        Sair
                    </a>

                <?php else: ?>

                    <a href="login.php">
                        Entrar
                    </a>

                <?php endif; ?>

            </nav>

        </div>

    </header>


    <!-- =====================================================
         PRODUTO
         ===================================================== -->

    <main class="produto-detalhe">

        <div class="produto-detalhe-container">

            <!-- VOLTAR -->

            <div class="produto-voltar">

                <a href="produtos.php">
                    ← Voltar para produtos
                </a>

            </div>


            <!-- PRODUTO -->

            <section class="produto-detalhe-grid">


                <!-- IMAGEM -->

                <div class="produto-detalhe-imagem-area">

                    <?php if (!empty($produto['imagem'])): ?>

                        <img
                            class="produto-detalhe-imagem"
                            src="uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                            alt="<?= htmlspecialchars($produto['nome']) ?>"
                        >

                    <?php else: ?>

                        <div class="produto-sem-imagem">

                            <span>
                                Imagem não disponível
                            </span>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- INFORMAÇÕES -->

                <div class="produto-detalhe-info">

                    <p class="produto-detalhe-categoria">

                        <?= htmlspecialchars($produto['categoria_nome']) ?>

                    </p>


                    <h1 class="produto-detalhe-titulo">

                        <?= htmlspecialchars($produto['nome']) ?>

                    </h1>


                    <?php if (!empty($produto['descricao'])): ?>

                        <p class="produto-detalhe-descricao">

                            <?= htmlspecialchars($produto['descricao']) ?>

                        </p>

                    <?php endif; ?>


                    <div class="produto-detalhe-material">

                        <span>
                            Material
                        </span>

                        <strong>
                            <?= htmlspecialchars($produto['tipo_material']) ?>
                        </strong>

                    </div>


                    <div class="produto-detalhe-preco">

                        R$
                        <?= number_format(
                            $produto['preco'],
                            2,
                            ',',
                            '.'
                        ) ?>

                    </div>


                    <?php if ($produto['estoque'] > 0): ?>

                        <p class="produto-detalhe-estoque">

                            Disponível em estoque

                        </p>


                        <?php if ($logado): ?>

                            <form
                                action="adicionar_carrinho.php"
                                method="POST"
                                class="produto-form-carrinho"
                            >

                                <input
                                    type="hidden"
                                    name="produto_id"
                                    value="<?= $produto['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="produto-btn-carrinho"
                                >
                                    Adicionar ao carrinho
                                </button>

                            </form>

                        <?php else: ?>

                            <a
                                href="login.php"
                                class="produto-btn-carrinho"
                            >
                                Entrar para comprar
                            </a>

                            <p class="produto-login-aviso">

                                Faça login ou crie uma conta para
                                adicionar produtos ao carrinho.

                            </p>

                        <?php endif; ?>


                    <?php else: ?>

                        <p class="produto-detalhe-esgotado">

                            Produto esgotado.

                        </p>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>