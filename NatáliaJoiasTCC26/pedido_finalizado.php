<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$pedido_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$pedido_id) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Busca o pedido do usuário logado
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            data_pedido,
            valor_total,
            status_pedido
        FROM pedidos
        WHERE id = :pedido_id
          AND usuario_id = :usuario_id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pedido_id' => $pedido_id,
    ':usuario_id' => $usuario_id
]);

$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Formatação dos dados
|--------------------------------------------------------------------------
*/

$data_pedido = date(
    'd/m/Y \à\s H:i',
    strtotime($pedido['data_pedido'])
);

$valor_total = number_format(
    (float) $pedido['valor_total'],
    2,
    ',',
    '.'
);

$status = htmlspecialchars(
    $pedido['status_pedido'],
    ENT_QUOTES,
    'UTF-8'
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pedido realizado - Natália Milanese Joias</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header class="site-header">
        <div class="header-container">

            <a href="index.php" class="logo">
                Natália Milanese Joias
            </a>

            <nav class="site-nav">
                <a href="index.php">Início</a>
                <a href="produtos.php">Produtos</a>
                <a href="cliente/minha_conta.php">Minha conta</a>
            </nav>

        </div>
    </header>

    <main class="pagina-pedido-finalizado">

        <section class="pedido-confirmacao">

            <div class="pedido-icone-sucesso" aria-hidden="true">
                ✓
            </div>

            <p class="pedido-legenda">
                NATÁLIA MILANESE JOIAS
            </p>

            <h1>Pedido realizado com sucesso!</h1>

            <p class="pedido-mensagem">
                Obrigado pela sua compra! Estamos felizes em fazer parte
                de um momento especial. Você pode consultar os detalhes
                deste pedido na sua conta.
            </p>

            <div class="pedido-numero">
                <span>Número do pedido</span>
                <strong>#<?= (int) $pedido['id'] ?></strong>
            </div>

            <div class="pedido-detalhes">

                <div class="pedido-detalhe-linha">
                    <span>Data do pedido</span>
                    <strong>
                        <?= htmlspecialchars(
                            $data_pedido,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>
                </div>

                <div class="pedido-detalhe-linha">
                    <span>Valor total</span>
                    <strong class="pedido-valor">
                        R$ <?= $valor_total ?>
                    </strong>
                </div>

                <div class="pedido-detalhe-linha">
                    <span>Status do pedido</span>
                    <strong class="pedido-status">
                        <?= $status ?>
                    </strong>
                </div>

            </div>

            <div class="pedido-acoes">
                <a href="cliente/pedido.php?id=<?= (int) $pedido['id'] ?>"
                   class="pedido-botao-principal">
                    Ver detalhes do pedido
                </a>

                <a href="produtos.php" class="pedido-botao-secundario">
                    Continuar comprando
                </a>
            </div>

            <p class="pedido-observacao">
                Guarde o número do pedido para consultar sua compra
                quando precisar.
            </p>

        </section>

    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>