<?php

require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/includes/header.php'; 


// Buscar categorias disponíveis para o filtro
$sql_categorias = "
    SELECT id, nome
    FROM categorias
    ORDER BY nome ASC
";

$stmt_categorias = $pdo->query($sql_categorias);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Identificar a categoria selecionada na URL
$categoria_id = filter_input(
    INPUT_GET,
    'categoria',
    FILTER_VALIDATE_INT
);

if (!$categoria_id || $categoria_id < 1) {
    $categoria_id = null;
}

// Buscar produtos disponíveis, aplicando o filtro se necessário
$sql = "
    SELECT
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
    WHERE produtos.estoque > 0
";

if ($categoria_id !== null) {
    $sql .= " AND produtos.categoria_id = :categoria_id";
}

$sql .= " ORDER BY produtos.id DESC";

$stmt = $pdo->prepare($sql);

if ($categoria_id !== null) {
    $stmt->bindValue(':categoria_id', $categoria_id, PDO::PARAM_INT);
}

$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos - Natália Milanese Joias</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>


    <!-- ==============================
         CONTEÚDO
         ============================== -->

    <main>

        <section class="pagina-produtos">

            <div class="container">

                <div class="titulo-pagina">

                    <p class="subtitulo">
                        Nossa coleção
                    </p>

                    <h1>
                        Todos os produtos
                    </h1>

                    <p class="descricao-pagina">
                        Encontre a joia perfeita para completar seu estilo.
                    </p>
                    <form action="produtos.php" method="GET" class="filtro-categorias">

    <label for="categoria">Filtrar por categoria:</label>

    <select name="categoria" id="categoria">
        <option value="">Todas as categorias</option>

        <?php foreach ($categorias as $categoria): ?>
            <option
                value="<?= (int) $categoria['id'] ?>"
                <?= $categoria_id === (int) $categoria['id']
                    ? 'selected'
                    : '' ?>
            >
                <?= htmlspecialchars($categoria['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="botao-produto">
        Filtrar
    </button>

    <?php if ($categoria_id !== null): ?>
        <a href="produtos.php" class="limpar-filtro">
            Limpar filtro
        </a>
    <?php endif; ?>

</form>

                </div>


                <?php if (empty($produtos)): ?>

                 <h2>
    <?= $categoria_id !== null
        ? 'Nenhum produto encontrado nesta categoria'
        : 'Nenhum produto disponível' ?>
</h2>

<p>
    <?= $categoria_id !== null
        ? 'Experimente escolher outra categoria ou visualizar todos os produtos.'
        : 'No momento não temos produtos disponíveis.' ?>
</p>

<a href="produtos.php" class="botao">
    Ver todos os produtos
</a>

                <?php else: ?>

                    <div class="produtos-grid">

                        <?php foreach ($produtos as $produto): ?>

                            <article class="produto-card">

                                <a
                                    href="produto.php?id=<?= $produto['id'] ?>"
                                    class="produto-imagem-link"
                                >

                                    <?php if (!empty($produto['imagem'])): ?>

                                        <img
                                            src="uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                                            alt="<?= htmlspecialchars($produto['nome']) ?>"
                                            class="produto-imagem"
                                        >

                                    <?php else: ?>

                                        <div class="produto-sem-imagem">
                                            Sem imagem
                                        </div>

                                    <?php endif; ?>

                                </a>


                                <div class="produto-info">

                                    <p class="produto-categoria">
                                        <?= htmlspecialchars($produto['categoria_nome']) ?>
                                    </p>

                                    <h2 class="produto-nome">

                                        <a href="produto.php?id=<?= $produto['id'] ?>">

                                            <?= htmlspecialchars($produto['nome']) ?>

                                        </a>

                                    </h2>


                                    <p class="produto-material">

                                        <?= htmlspecialchars($produto['tipo_material']) ?>

                                    </p>


                                    <p class="produto-descricao">

                                        <?= htmlspecialchars($produto['descricao']) ?>

                                    </p>


                                    <div class="produto-rodape">

                                        <span class="produto-preco">

                                            R$
                                            <?= number_format(
                                                $produto['preco'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </span>


                                        <a
                                            href="produto.php?id=<?= $produto['id'] ?>"
                                            class="botao-produto"
                                        >
                                            Ver detalhes
                                        </a>

                                    </div>


                                    <p class="produto-estoque">

                                        <?= $produto['estoque'] ?>
                                        <?= $produto['estoque'] == 1 ? 'unidade disponível' : 'unidades disponíveis' ?>

                                    </p>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>


    <!-- ==============================
         FOOTER
         ============================== -->

   
<footer class="site-footer">
    <div class="container footer-conteudo">

        <div class="footer-marca">
            <a
                href="/Arthur351/NatáliaJoiasTCC26/index.php"
                class="footer-logo"
            >
                Natália Milanese Joias
            </a>

            <p class="footer-frase">
                Elegância em cada detalhe.
            </p>

            <p class="footer-descricao">
                Joias escolhidas para valorizar sua beleza
                e acompanhar momentos especiais.
            </p>
        </div>

        <div class="footer-links">
            <h2>Explore</h2>

            <a href="/Arthur351/NatáliaJoiasTCC26/index.php">
                Início
            </a>

            <a href="/Arthur351/NatáliaJoiasTCC26/produtos.php">
                Produtos
            </a>

            <a href="/Arthur351/NatáliaJoiasTCC26/carrinho.php">
                Carrinho
            </a>
        </div>

        <div class="footer-social">
            <h2>Vamos nos conectar?</h2>

            <p>
                Acompanhe novidades, peças e inspirações
                nas nossas redes sociais.
            </p>

            <a
                href="https://www.instagram.com/natimilanesejoias/"
                target="_blank"
                rel="noopener noreferrer"
                class="footer-instagram"
                aria-label="Instagram da Natália Milanese Joias"
            >
                <span aria-hidden="true">◎</span>
                @natimilanesejoias
            </a>
        </div>

    </div>

    <div class="container footer-final">
        <p>
            © <?= date('Y') ?> Natália Milanese Joias.
            Todos os direitos reservados.
        </p>

        <a href="https://www.instagram.com/natimilanesejoias/"
           target="_blank"
           rel="noopener noreferrer">
            Feito com carinho ♡
        </a>
    </div>
</footer>


</body>

</html>