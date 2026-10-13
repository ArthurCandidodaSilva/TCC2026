<?php

require_once __DIR__ . '/config/database.php';



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   BUSCA OS PRODUTOS
   ========================================================= */

$sql = "SELECT
            produtos.id,
            produtos.nome,
            produtos.preco,
            produtos.imagem,
            categorias.nome AS categoria_nome
        FROM produtos

        INNER JOIN categorias
            ON produtos.categoria_id = categorias.id

        WHERE produtos.estoque > 0

        ORDER BY produtos.id DESC";

$stmt = $pdo->query($sql);

$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   VERIFICA LOGIN
   ========================================================= */

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
        Natália Milanese Joias
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>


<!-- =========================================================
     HEADER
     ========================================================= -->

<header class="site-header">

    <div class="header-container">

        <a
            href="index.php"
            class="logo"
        >
            Natália Milanese Joias
        </a>


        <nav class="site-nav">

            <a
                href="index.php"
                class="ativo"
            >
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


<!-- =========================================================
     HERO
     ========================================================= -->

<main>

  <section class="hero">

    <div class="hero-container">

        <div class="hero-content">

            <p class="hero-subtitulo">
                NATÁLIA MILANESE JOIAS
            </p>

            <h1>
                Elegância que
                <br>
                acompanha você.
            </h1>

            <p class="hero-texto">
                Joias pensadas para valorizar
                cada momento e tornar suas
                histórias ainda mais especiais.
            </p>

            <a
                href="produtos.php"
                class="hero-btn"
            >
                Conheça nossas joias
            </a>

        </div>

        <div class="hero-imagem">
        <img
                src="assets/imagens/hero-joias.jpg"
                alt="Coleção de joias Natália Milanese"
                
            >

        </div>

    </div>

</section>


    <!-- =====================================================
         INTRODUÇÃO
         ===================================================== -->

    <section class="home-introducao">

        <div class="home-introducao-container">

            <p class="secao-legenda">
                NOSSA COLEÇÃO
            </p>

            <h2>
                Peças para todos os momentos
            </h2>

            <p>
                Descubra nossa seleção de joias,
                escolhidas para combinar delicadeza,
                personalidade e elegância.
            </p>

        </div>

    </section>


    <!-- =====================================================
         PRODUTOS EM DESTAQUE / CARROSSEL
         ===================================================== -->

    <section class="destaques">

        <div class="destaques-container">

            <div class="secao-cabecalho">

                <div>

                    <p class="secao-legenda">
                        DESTAQUES
                    </p>

                    <h2>
                        Nossas joias
                    </h2>

                </div>

                <a
                    href="produtos.php"
                    class="link-ver-todos"
                >
                    Ver todos os produtos →
                </a>

            </div>


            <?php if (empty($produtos)): ?>

                <div class="produtos-vazio">

                    <p>
                        Nenhum produto disponível no momento.
                    </p>

                </div>

            <?php else: ?>


                <div class="carrossel">

                    <button
                        type="button"
                        class="carrossel-btn carrossel-anterior"
                        aria-label="Produto anterior"
                    >
                        ‹
                    </button>


                    <div class="carrossel-janela">

                        <div class="carrossel-lista">

                            <?php foreach ($produtos as $produto): ?>

                                <article class="produto-card">


                                    <a
                                        href="produto.php?id=<?= $produto['id'] ?>"
                                        class="produto-card-imagem"
                                    >

                                        <?php if (!empty($produto['imagem'])): ?>

                                            <img
                                                src="uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                                                alt="<?= htmlspecialchars($produto['nome']) ?>"
                                            >

                                        <?php else: ?>

                                            <div class="produto-card-sem-imagem">

                                                Sem imagem

                                            </div>

                                        <?php endif; ?>

                                    </a>


                                    <div class="produto-card-info">

                                        <p class="produto-card-categoria">

                                            <?= htmlspecialchars($produto['categoria_nome']) ?>

                                        </p>


                                        <h3>

                                            <?= htmlspecialchars($produto['nome']) ?>

                                        </h3>


                                        <p class="produto-card-preco">

                                            R$
                                            <?= number_format(
                                                $produto['preco'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </p>


                                        <a
                                            href="produto.php?id=<?= $produto['id'] ?>"
                                            class="produto-card-link"
                                        >
                                            Ver produto
                                        </a>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="carrossel-btn carrossel-proximo"
                        aria-label="Próximo produto"
                    >
                        ›
                    </button>

                </div>


                <div class="carrossel-indicadores"></div>

            <?php endif; ?>

        </div>

    </section>


    <!-- =====================================================
         SOBRE A MARCA
         ===================================================== -->

    <section class="sobre-home">

        <div class="sobre-home-container">

            <div class="sobre-home-texto">

                <p class="secao-legenda">
                    SOBRE A MARCA
                </p>

                <h2>
                    Mais do que uma joia,
                    <br>
                    uma história.
                </h2>

                <p>
                    A Natália Milanese Joias nasceu com o
                    propósito de oferecer peças delicadas,
                    elegantes e especiais para diferentes
                    momentos da vida.
                </p>

                <p>
                    Cada detalhe é pensado para que nossas
                    joias façam parte das histórias de quem
                    escolhe usá-las.
                </p>

            </div>


            <div class="sobre-home-destaque">

                <span>
                    NATÁLIA
                </span>

                <strong>
                    MILANESE
                </strong>

                <small>
                    JOIAS
                </small>

            </div>

        </div>

    </section>


    <!-- =====================================================
         CTA
         ===================================================== -->

    <section class="home-cta">

        <div class="home-cta-container">

            <p class="secao-legenda">
                ENCONTRE SUA JOIA
            </p>

            <h2>
                Encontre uma peça
                <br>
                para chamar de sua.
            </h2>

            <a
                href="produtos.php"
                class="home-cta-btn"
            >
                Ver coleção
            </a>

        </div>

    </section>

</main>


<!-- =========================================================
     JAVASCRIPT DO CARROSSEL
     ========================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const lista = document.querySelector('.carrossel-lista');

    const anterior = document.querySelector('.carrossel-anterior');

    const proximo = document.querySelector('.carrossel-proximo');

    const indicadores =
        document.querySelector('.carrossel-indicadores');


    if (!lista || !anterior || !proximo) {
        return;
    }


    const cards =
        lista.querySelectorAll('.produto-card');


    if (cards.length === 0) {
        return;
    }


    let posicao = 0;


    function quantidadeVisivel() {

        if (window.innerWidth <= 600) {
            return 1;
        }

        if (window.innerWidth <= 900) {
            return 2;
        }

        return 3;
    }


    function atualizar() {

        const visiveis = quantidadeVisivel();

        const maximo =
            Math.max(0, cards.length - visiveis);

        if (posicao > maximo) {
            posicao = maximo;
        }

        if (posicao < 0) {
            posicao = 0;
        }


        const largura =
            cards[0].offsetWidth;


        const gap = 24;


        lista.style.transform =
            `translateX(-${posicao * (largura + gap)}px)`;


        atualizarIndicadores();

    }


    function atualizarIndicadores() {

        if (!indicadores) {
            return;
        }


        indicadores.innerHTML = '';


        const visiveis = quantidadeVisivel();

        const quantidade =
            Math.max(1, cards.length - visiveis + 1);


        for (let i = 0; i < quantidade; i++) {

            const indicador =
                document.createElement('button');

            indicador.type = 'button';

            indicador.className =
                'carrossel-indicador';


            if (i === posicao) {
                indicador.classList.add('ativo');
            }


            indicador.addEventListener(
                'click',
                function () {

                    posicao = i;

                    atualizar();

                }
            );


            indicadores.appendChild(indicador);

        }

    }


    anterior.addEventListener(
        'click',
        function () {

            posicao--;

            atualizar();

        }
    );


    proximo.addEventListener(
        'click',
        function () {

            posicao++;

            atualizar();

        }
    );


    window.addEventListener(
        'resize',
        atualizar
    );


    atualizar();


    /*
     * Passa automaticamente pelos produtos.
     */

    setInterval(function () {

        const visiveis = quantidadeVisivel();

        const maximo =
            Math.max(0, cards.length - visiveis);


        if (maximo === 0) {
            return;
        }


        posicao++;


        if (posicao > maximo) {
            posicao = 0;
        }


        atualizar();

    }, 5000);

});

</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>