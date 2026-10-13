<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT
            carrinhos.id AS carrinho_id,
            carrinhos.quantidade,

            produtos.id AS produto_id,
            produtos.nome,
            produtos.preco,
            produtos.imagem,
            produtos.estoque

        FROM carrinhos

        INNER JOIN produtos
            ON carrinhos.produto_id = produtos.id

        WHERE carrinhos.usuario_id = :usuario_id

        ORDER BY carrinhos.id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':usuario_id' => $usuario_id
]);

$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

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
        Carrinho - Natália Milanese Joias
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=20261006-1"
    >

</head>

<body class="pagina-carrinho">

    <!-- =====================================================
         HEADER
         ===================================================== -->

    <header class="site-header">

        <div class="header-container">

            <a
                href="index.php"
                class="logo"
            >
                Natália Milanese Joias
            </a>

            <nav class="site-nav">

                <a href="index.php">
                    Início
                </a>

                <a href="produtos.php">
                    Produtos
                </a>

                <a
                    href="carrinho.php"
                    class="ativo"
                >
                    Carrinho
                </a>

                <a href="logout.php">
                    Sair
                </a>

            </nav>

        </div>

    </header>


    <!-- =====================================================
         CONTEÚDO
         ===================================================== -->

    <main class="carrinho">

        <div class="carrinho-container">

            <header class="carrinho-cabecalho">

                <p class="carrinho-subtitulo">
                    SUA SELEÇÃO
                </p>

                <h1>
                    Meu carrinho
                </h1>

                <p>
                    Confira os produtos selecionados antes de finalizar sua compra.
                </p>

            </header>


            <?php if (empty($itens)): ?>

                <!-- =================================================
                     CARRINHO VAZIO
                     ================================================= -->

                <section class="carrinho-vazio">

                    <div class="carrinho-vazio-icone">
                        ♡
                    </div>

                    <h2>
                        Seu carrinho está vazio
                    </h2>

                    <p>
                        Você ainda não adicionou nenhum produto ao seu carrinho.
                    </p>

                    <a
                        href="produtos.php"
                        class="btn-publico"
                    >
                        Ver produtos
                    </a>

                </section>


            <?php else: ?>

                <!-- =================================================
                     CARRINHO COM PRODUTOS
                     ================================================= -->

                <div class="carrinho-layout">


                    <!-- =================================================
                         LISTA DE PRODUTOS
                         ================================================= -->

                    <section class="carrinho-itens">

                        <?php foreach ($itens as $item): ?>

                            <?php

                            $subtotal =
                                $item['preco'] * $item['quantidade'];

                            $total += $subtotal;

                            ?>

                            <article class="carrinho-item">


                                <!-- IMAGEM -->

                                <div class="carrinho-item-imagem">

                                    <?php if (!empty($item['imagem'])): ?>

                                        <img
                                            src="uploads/produtos/<?= htmlspecialchars($item['imagem']) ?>"
                                            alt="<?= htmlspecialchars($item['nome']) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="carrinho-sem-imagem">
                                            Sem imagem
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- INFORMAÇÕES -->

                                <div class="carrinho-item-conteudo">

                                    <div class="carrinho-item-info">

                                        <p class="carrinho-item-label">
                                            JOIA
                                        </p>

                                        <h2>
                                            <?= htmlspecialchars($item['nome']) ?>
                                        </h2>

                                        <p class="carrinho-preco-unitario">

                                            R$

                                            <?= number_format(
                                                $item['preco'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                            <span>
                                                por unidade
                                            </span>

                                        </p>

                                    </div>


                                    <!-- AÇÕES -->

                                    <div class="carrinho-item-acoes">


                                        <!-- QUANTIDADE -->

                                        <div class="carrinho-quantidade">

                                            <span>
                                                Quantidade
                                            </span>

                                            <form
                                                action="atualizar_carrinho.php"
                                                method="POST"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="carrinho_id"
                                                    value="<?= $item['carrinho_id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="quantidade"
                                                    value="<?= $item['quantidade'] - 1 ?>"
                                                    <?= $item['quantidade'] <= 1 ? 'disabled' : '' ?>
                                                    aria-label="Diminuir quantidade"
                                                >
                                                    −
                                                </button>

                                                <strong>
                                                    <?= $item['quantidade'] ?>
                                                </strong>

                                                <button
                                                    type="submit"
                                                    name="quantidade"
                                                    value="<?= $item['quantidade'] + 1 ?>"
                                                    <?= $item['quantidade'] >= $item['estoque'] ? 'disabled' : '' ?>
                                                    aria-label="Aumentar quantidade"
                                                >
                                                    +
                                                </button>

                                            </form>

                                        </div>


                                        <!-- SUBTOTAL -->

                                        <div class="carrinho-item-total">

                                            <span>
                                                Subtotal
                                            </span>

                                            <strong>

                                                R$

                                                <?= number_format(
                                                    $subtotal,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) ?>

                                            </strong>

                                        </div>


                                        <!-- REMOVER -->

                                        <form
                                            action="remover_carrinho.php"
                                            method="POST"
                                            class="carrinho-remover"
                                        >

                                            <input
                                                type="hidden"
                                                name="carrinho_id"
                                                value="<?= $item['carrinho_id'] ?>"
                                            >

                                            <button type="submit">
                                                Remover
                                            </button>

                                        </form>

                                    </div>


                                    <p class="carrinho-estoque">

                                        <?= $item['estoque'] ?>

                                        unidades disponíveis

                                    </p>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </section>


                    <!-- =================================================
                         RESUMO DA COMPRA
                         ================================================= -->

                    <aside class="carrinho-resumo">

                        <p class="carrinho-resumo-subtitulo">
                            RESUMO DO PEDIDO
                        </p>

                        <h2>
                            Sua compra
                        </h2>


                        <div class="carrinho-resumo-linha">

                            <span>
                                Produtos
                            </span>

                            <span>
                                <?= count($itens) ?>
                            </span>

                        </div>


                        <div class="carrinho-resumo-linha">

                            <span>
                                Subtotal
                            </span>

                            <span>

                                R$

                                <?= number_format(
                                    $total,
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </span>

                        </div>


                        <div class="carrinho-resumo-total">

                            <span>
                                Total
                            </span>

                            <strong>

                                R$

                                <?= number_format(
                                    $total,
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </strong>

                        </div>


                        <a
                            href="finalizar_compra.php"
                            class="carrinho-finalizar"
                        >
                            Finalizar compra
                        </a>


                        <a
                            href="produtos.php"
                            class="carrinho-continuar"
                        >
                            Continuar comprando
                        </a>

                    </aside>

                </div>

            <?php endif; ?>

        </div>

    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>