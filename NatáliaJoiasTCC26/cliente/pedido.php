<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';



verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pedido_id || $pedido_id < 1) {
    header('Location: meus_pedidos.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Busca o pedido do usuário logado
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, data_pedido, valor_total, status_pedido
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
    header('Location: meus_pedidos.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Busca os produtos do pedido
|--------------------------------------------------------------------------
*/

$sql_itens = "SELECT
                  itens_pedido.quantidade,
                  itens_pedido.preco_unitario,
                  produtos.nome,
                  produtos.imagem
              FROM itens_pedido
              INNER JOIN produtos
                  ON itens_pedido.produto_id = produtos.id
              WHERE itens_pedido.pedido_id = :pedido_id";

$stmt_itens = $pdo->prepare($sql_itens);
$stmt_itens->execute([':pedido_id' => $pedido_id]);

$itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Busca o pagamento
|--------------------------------------------------------------------------
*/

$sql_pagamento = "SELECT
                      metodo,
                      status,
                      valor,
                      id_transacao,
                      data_pagamento
                  FROM pagamentos
                  WHERE pedido_id = :pedido_id
                  LIMIT 1";

$stmt_pagamento = $pdo->prepare($sql_pagamento);
$stmt_pagamento->execute([':pedido_id' => $pedido_id]);

$pagamento = $stmt_pagamento->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Formatação dos dados
|--------------------------------------------------------------------------
*/

$formatarValor = static function ($valor): string {
    return number_format((float) $valor, 2, ',', '.');
};

$formatarTexto = static function ($texto): string {
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES,
        'UTF-8'
    );
};

$statusPedido = (string) $pedido['status_pedido'];

$statusPagamento = $pagamento
    ? (string) $pagamento['status']
    : '';

$metodosPagamento = [
    'pix' => 'Pix',
    'cartao' => 'Cartão',
    'boleto' => 'Boleto'
];

$metodoPagamento = $pagamento
    ? ($metodosPagamento[strtolower($pagamento['metodo'])]
        ?? ucfirst((string) $pagamento['metodo']))
    : '';

$statusClasses = [
    'pago' => 'detalhe-status-pago',
    'pendente' => 'detalhe-status-pendente',
    'cancelado' => 'detalhe-status-cancelado',
    'enviado' => 'detalhe-status-enviado',
    'entregue' => 'detalhe-status-entregue'
];

$statusClasse = $statusClasses[strtolower($statusPedido)]
    ?? 'detalhe-status-padrao';

$dataPedido = date(
    'd/m/Y \à\s H:i',
    strtotime($pedido['data_pedido'])
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pedido #<?= (int) $pedido['id'] ?> - Natália Milanese Joias</title>

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

    <main class="pagina-detalhes-pedido">

        <div class="detalhe-pedido-topo">

            <a href="meus_pedidos.php" class="detalhe-voltar">
                ← Voltar para meus pedidos
            </a>

            <p class="detalhe-legenda">MINHA CONTA</p>

            <div class="detalhe-titulo-linha">
                <div>
                    <h1>Pedido #<?= (int) $pedido['id'] ?></h1>
                    <p class="detalhe-data">
                        Realizado em <?= $formatarTexto($dataPedido) ?>
                    </p>
                </div>

                <span class="detalhe-status <?= $statusClasse ?>">
                    <?= $formatarTexto($statusPedido) ?>
                </span>
            </div>

        </div>

        <div class="detalhe-pedido-layout">

            <section class="detalhe-pedido-produtos">

                <div class="detalhe-card">

                    <div class="detalhe-card-cabecalho">
                        <h2>Produtos do pedido</h2>
                        <span><?= count($itens) ?> item(ns)</span>
                    </div>

                    <?php if (empty($itens)): ?>

                        <p class="detalhe-vazio">
                            Nenhum produto foi encontrado neste pedido.
                        </p>

                    <?php else: ?>

                        <div class="detalhe-lista-produtos">

                            <?php foreach ($itens as $item): ?>

                                <?php
                                $quantidade = (int) $item['quantidade'];
                                $precoUnitario = (float) $item['preco_unitario'];
                                $subtotal = $precoUnitario * $quantidade;
                                ?>

                                <article class="detalhe-produto">

                                    <div class="detalhe-produto-imagem">

                                        <?php if (!empty($item['imagem'])): ?>

                                            <img
                                                src="../uploads/produtos/<?= rawurlencode(basename($item['imagem'])) ?>"
                                                alt="<?= $formatarTexto($item['nome']) ?>"
                                                loading="lazy"
                                            >

                                        <?php else: ?>

                                            <div class="detalhe-sem-imagem">
                                                Sem imagem
                                            </div>

                                        <?php endif; ?>

                                    </div>

                                    <div class="detalhe-produto-info">

                                        <h3>
                                            <?= $formatarTexto($item['nome']) ?>
                                        </h3>

                                        <p>
                                            Quantidade: <?= $quantidade ?>
                                        </p>

                                        <p>
                                            Preço unitário:
                                            R$ <?= $formatarValor($precoUnitario) ?>
                                        </p>

                                    </div>

                                    <div class="detalhe-produto-subtotal">
                                        <span>Subtotal</span>
                                        <strong>
                                            R$ <?= $formatarValor($subtotal) ?>
                                        </strong>
                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                    <div class="detalhe-total">

                        <span>Total do pedido</span>

                        <strong>
                            R$ <?= $formatarValor($pedido['valor_total']) ?>
                        </strong>

                    </div>

                </div>

                <div class="detalhe-card detalhe-card-pagamento">

                    <div class="detalhe-card-cabecalho">
                        <h2>Pagamento</h2>
                    </div>

                    <?php if ($pagamento): ?>

                        <div class="detalhe-pagamento-linha">
                            <span>Método de pagamento</span>
                            <strong><?= $formatarTexto($metodoPagamento) ?></strong>
                        </div>

                        <div class="detalhe-pagamento-linha">
                            <span>Status do pagamento</span>

                            <strong class="detalhe-status-pagamento">
                                <?= $formatarTexto($statusPagamento) ?>
                            </strong>
                        </div>

                        <div class="detalhe-pagamento-linha">
                            <span>Valor registrado</span>
                            <strong>
                                R$ <?= $formatarValor($pagamento['valor']) ?>
                            </strong>
                        </div>

                        <?php if (!empty($pagamento['id_transacao'])): ?>

                            <div class="detalhe-pagamento-linha">
                                <span>Identificação da transação</span>
                                <strong class="detalhe-transacao">
                                    <?= $formatarTexto($pagamento['id_transacao']) ?>
                                </strong>
                            </div>

                        <?php endif; ?>

                        <?php if (!empty($pagamento['data_pagamento'])): ?>

                            <div class="detalhe-pagamento-linha">
                                <span>Data do pagamento</span>
                                <strong>
                                    <?= $formatarTexto(date(
                                        'd/m/Y \à\s H:i',
                                        strtotime($pagamento['data_pagamento'])
                                    )) ?>
                                </strong>
                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <p class="detalhe-vazio">
                            Ainda não há pagamento registrado para este pedido.
                        </p>

                    <?php endif; ?>

                </div>

            </section>

            <aside class="detalhe-pedido-resumo">

                <div class="detalhe-card detalhe-resumo-card">

                    <p class="detalhe-resumo-legenda">RESUMO DA COMPRA</p>

                    <h2>Pedido #<?= (int) $pedido['id'] ?></h2>

                    <div class="detalhe-resumo-linha">
                        <span>Quantidade de itens</span>
                        <strong><?= array_sum(array_map(
                            static fn($item) => (int) $item['quantidade'],
                            $itens
                        )) ?></strong>
                    </div>

                    <div class="detalhe-resumo-linha">
                        <span>Data do pedido</span>
                        <strong><?= $formatarTexto(date(
                            'd/m/Y',
                            strtotime($pedido['data_pedido'])
                        )) ?></strong>
                    </div>

                    <div class="detalhe-resumo-total">
                        <span>Total pago/registrado</span>
                        <strong>
                            R$ <?= $formatarValor($pedido['valor_total']) ?>
                        </strong>
                    </div>

                    <a href="meus_pedidos.php" class="detalhe-botao">
                        Meus pedidos
                    </a>

                    <a href="../produtos.php" class="detalhe-botao-secundario">
                        Continuar comprando
                    </a>

                </div>

            </aside>

        </div>

    </main>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>


</body>
</html>