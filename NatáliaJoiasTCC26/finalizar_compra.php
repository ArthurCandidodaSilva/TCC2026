<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| Busca os produtos do carrinho
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Se o carrinho estiver vazio
|--------------------------------------------------------------------------
*/

if (empty($itens)) {
    header('Location: carrinho.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Calcula o total
|--------------------------------------------------------------------------
*/

$total = 0;

foreach ($itens as $item) {

    $subtotal =
        $item['preco'] * $item['quantidade'];

    $total += $subtotal;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Finalizar compra - Natália Milanese Joias</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>




    <header>
```php
<body>

    <header class="site-header">
        <div class="header-container">

            <a href="index.php" class="logo">
                Natália Milanese Joias
            </a>

            <nav class="site-nav">
                <a href="index.php">Início</a>
                <a href="produtos.php">Produtos</a>
                <a href="carrinho.php">Carrinho</a>
                <a href="cliente/minha_conta.php">Minha conta</a>
                <a href="logout.php">Sair</a>
            </nav>

        </div>
    </header>

    <main class="pagina-checkout">

        <div class="checkout-cabecalho">
            <p class="checkout-legenda">QUASE LÁ</p>

            <h1>Finalizar compra</h1>

            <p>
                Confira os itens e os valores antes de confirmar seu pedido.
            </p>
        </div>

        <div class="checkout-layout">

            <section class="checkout-produtos">

                <h2>Resumo dos produtos</h2>

                <?php foreach ($itens as $item): ?>

                    <?php
                    $subtotal = $item['preco'] * $item['quantidade'];
                    ?>

                    <article class="checkout-item">

                        <?php if (!empty($item['imagem'])): ?>

                            <img
                                src="uploads/produtos/<?= htmlspecialchars($item['imagem'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                class="checkout-item-imagem"
                            >

                        <?php else: ?>

                            <div class="checkout-item-sem-imagem">
                                Sem imagem
                            </div>

                        <?php endif; ?>

                        <div class="checkout-item-info">

                            <h3>
                                <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <p>
                                Quantidade: <?= (int) $item['quantidade'] ?>
                            </p>

                            <p class="checkout-preco-unitario">
                                Unitário:
                                R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                            </p>

                            <strong class="checkout-subtotal">
                                R$ <?= number_format($subtotal, 2, ',', '.') ?>
                            </strong>

                        </div>

                    </article>

                <?php endforeach; ?>

                <a href="carrinho.php" class="checkout-voltar">
                    ← Voltar para o carrinho
                </a>

            </section>

            <aside class="checkout-resumo">

                <h2>Resumo da compra</h2>

                <div class="checkout-resumo-linha">
                    <span>Quantidade de itens</span>
                    <strong>
                        <?php
                        $quantidade_total = 0;

                        foreach ($itens as $item) {
                            $quantidade_total += (int) $item['quantidade'];
                        }

                        echo $quantidade_total;
                        ?>
                    </strong>
                </div>

                <div class="checkout-resumo-linha checkout-total">
                    <span>Total da compra</span>

                    <strong>
                        R$ <?= number_format($total, 2, ',', '.') ?>
                    </strong>
                </div>

                <p class="checkout-aviso">
                    Confira seu pedido antes de continuar.
                </p>

                <form action="criar_pedido.php" method="POST">
                    <button type="submit" class="checkout-confirmar">
                        Confirmar pedido
                    </button>
                </form>

            </aside>

        </div>

    </main>
    
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>