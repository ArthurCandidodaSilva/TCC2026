<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';



verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| Busca os pedidos do usuário
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            data_pedido,
            valor_total,
            status_pedido

        FROM pedidos

        WHERE usuario_id = :usuario_id

        ORDER BY data_pedido DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':usuario_id' => $usuario_id
]);

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meus pedidos - Natália Milanese Joias</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="site-header">
        <div class="header-container">

            <a href="../index.php" class="logo">
                Natália Milanese Joias
            </a>

            <nav class="site-nav">
                <a href="../index.php">Início</a>
                <a href="../produtos.php">Produtos</a>
                <a href="../carrinho.php">Carrinho</a>
                <a href="minha_conta.php">Minha conta</a>
                <a href="../logout.php">Sair</a>
            </nav>

        </div>
    </header>

    <main class="pagina-meus-pedidos">

        <div class="pedidos-cabecalho">
            <p class="pedidos-legenda">SUA CONTA</p>
            <h1>Meus pedidos</h1>
            <p>Acompanhe suas compras e consulte os detalhes de cada pedido.</p>
        </div>

        <?php if (empty($pedidos)): ?>

            <section class="pedidos-vazio">
                <h2>Você ainda não fez nenhum pedido</h2>

                <p>
                    Quando você realizar uma compra, as informações
                    aparecerão nesta página.
                </p>

                <a href="../produtos.php" class="pedidos-botao">
                    Conhecer as joias
                </a>
            </section>

        <?php else: ?>

            <div class="pedidos-lista">

                <?php foreach ($pedidos as $pedido): ?>

                    <article class="pedido-card">

                        <div class="pedido-card-topo">
                            <div>
                                <span class="pedido-identificacao">Pedido</span>

                                <h2>
                                    #<?= (int) $pedido['id'] ?>
                                </h2>
                            </div>

                            <span class="pedido-status status-<?= htmlspecialchars(
                                strtolower($pedido['status_pedido']),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                <?= htmlspecialchars(
                                    ucfirst($pedido['status_pedido']),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </div>

                        <div class="pedido-card-info">

                            <div>
                                <span class="pedido-info-label">Data do pedido</span>

                                <p>
                                    <?= date(
                                        'd/m/Y \à\s H:i',
                                        strtotime($pedido['data_pedido'])
                                    ) ?>
                                </p>
                            </div>

                            <div>
                                <span class="pedido-info-label">Valor total</span>

                                <p class="pedido-valor">
                                    R$ <?= number_format(
                                        $pedido['valor_total'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </p>
                            </div>

                        </div>

                        <div class="pedido-card-acoes">
                            <a
                                href="pedido.php?id=<?= (int) $pedido['id'] ?>"
                                class="pedido-link-detalhes"
                            >
                                Ver detalhes
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

   <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>