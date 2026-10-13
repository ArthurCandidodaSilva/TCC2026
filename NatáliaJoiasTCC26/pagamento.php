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
| Busca o pedido do usuário
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            usuario_id,
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
| Verifica se o pedido já possui pagamento
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            metodo,
            status,
            valor,
            id_transacao,
            data_pagamento

        FROM pagamentos

        WHERE pedido_id = :pedido_id

        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pedido_id' => $pedido_id
]);

$pagamento = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pagamento - Natália Milanese Joias</title>

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
                <a href="carrinho.php">Carrinho</a>
                <a href="cliente/minha_conta.php">Minha conta</a>
                <a href="logout.php">Sair</a>
            </nav>

        </div>
    </header>

    <main class="pagina-pagamento">

        <div class="pagamento-cabecalho">
            <p class="pagamento-legenda">ÚLTIMA ETAPA</p>

            <h1>Pagamento</h1>

            <p>
                Confira o valor do seu pedido e escolha a forma de pagamento.
            </p>
        </div>

        <div class="pagamento-layout">

            <section class="pagamento-conteudo">

                <div class="pagamento-numero">
                    <span>Pedido</span>
                    <strong>#<?= (int) $pedido['id'] ?></strong>
                </div>

                <?php if ($pagamento): ?>

                    <div class="pagamento-status-card">

                        <h2>Pagamento registrado</h2>

                        <div class="pagamento-detalhe">
                            <span>Forma de pagamento</span>

                            <strong>
                                <?php
                                $metodos = [
                                    'pix' => 'PIX',
                                    'cartao' => 'Cartão',
                                    'boleto' => 'Boleto'
                                ];

                                echo htmlspecialchars(
                                    $metodos[$pagamento['metodo']]
                                        ?? $pagamento['metodo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </strong>
                        </div>

                        <div class="pagamento-detalhe">
                            <span>Status</span>

                            <strong class="pagamento-status-texto">
                                <?= htmlspecialchars(
                                    ucfirst($pagamento['status']),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>
                        </div>

                        <?php if ($pagamento['status'] === 'aprovado'): ?>

                            <div class="pagamento-mensagem pagamento-aprovado">
                                <strong>✓ Pagamento aprovado!</strong>
                                <p>Seu pagamento foi registrado com sucesso.</p>
                            </div>

                            <a
                                href="pedido_finalizado.php?id=<?= (int) $pedido['id'] ?>"
                                class="pagamento-botao"
                            >
                                Ver pedido
                            </a>

                        <?php elseif ($pagamento['status'] === 'pendente'): ?>

                            <div class="pagamento-mensagem pagamento-pendente">
                                <strong>Pagamento pendente</strong>
                                <p>
                                    Nesta versão de demonstração, você pode
                                    simular a aprovação do pagamento.
                                </p>
                            </div>

                            <form action="aprovar_pagamento.php" method="POST">
                                <input
                                    type="hidden"
                                    name="pedido_id"
                                    value="<?= (int) $pedido['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="pagamento-botao"
                                >
                                    Simular pagamento aprovado
                                </button>
                            </form>

                        <?php else: ?>

                            <div class="pagamento-mensagem pagamento-pendente">
                                <p>
                                    O pagamento não está pendente nem aprovado.
                                    Consulte os detalhes do pedido para verificar
                                    a situação atual.
                                </p>
                            </div>

                        <?php endif; ?>

                    </div>

                <?php else: ?>

                    <div class="pagamento-formulario">

                        <h2>Escolha a forma de pagamento</h2>

                        <form action="criar_pagamento.php" method="POST">

                            <input
                                type="hidden"
                                name="pedido_id"
                                value="<?= (int) $pedido['id'] ?>"
                            >

                            <div class="pagamento-opcoes">

                                <label class="pagamento-opcao">
                                    <input
                                        type="radio"
                                        name="metodo"
                                        value="pix"
                                        required
                                    >

                                    <span class="pagamento-icone">PIX</span>

                                    <span class="pagamento-opcao-texto">
                                        <strong>PIX</strong>
                                        <small>Pagamento instantâneo</small>
                                    </span>
                                </label>

                                <label class="pagamento-opcao">
                                    <input
                                        type="radio"
                                        name="metodo"
                                        value="cartao"
                                    >

                                    <span class="pagamento-icone">CC</span>

                                    <span class="pagamento-opcao-texto">
                                        <strong>Cartão</strong>
                                        <small>Cartão de crédito ou débito</small>
                                    </span>
                                </label>

                                <label class="pagamento-opcao">
                                    <input
                                        type="radio"
                                        name="metodo"
                                        value="boleto"
                                    >

                                    <span class="pagamento-icone">BOL</span>

                                    <span class="pagamento-opcao-texto">
                                        <strong>Boleto</strong>
                                        <small>Pagamento por boleto bancário</small>
                                    </span>
                                </label>

                            </div>

                            <p class="pagamento-aviso">
                                Ambiente de demonstração acadêmica.
                                Nenhuma cobrança real é realizada nesta etapa.
                            </p>

                            <button
                                type="submit"
                                class="pagamento-botao"
                            >
                                Continuar para pagamento
                            </button>

                        </form>

                    </div>

                <?php endif; ?>

            </section>

            <aside class="pagamento-resumo">

                <h2>Resumo do pedido</h2>

                <div class="pagamento-resumo-linha">
                    <span>Número do pedido</span>
                    <strong>#<?= (int) $pedido['id'] ?></strong>
                </div>

                <div class="pagamento-resumo-total">
                    <span>Total da compra</span>

                    <strong>
                        R$ <?= number_format(
                            $pedido['valor_total'],
                            2,
                            ',',
                            '.'
                        ) ?>
                    </strong>
                </div>

                <p>
                    Obrigado por escolher a Natália Milanese Joias.
                </p>

            </aside>

        </div>

        <a href="cliente/minha_conta.php" class="pagamento-voltar">
            ← Voltar para minha conta
        </a>

    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>


</html>