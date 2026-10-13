<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

verificarAdmin();

/*
|--------------------------------------------------------------------------
| Consultas do Dashboard
|--------------------------------------------------------------------------
*/

// Total de produtos
$stmt = $pdo->query("SELECT COUNT(*) FROM produtos");
$totalProdutos = (int) $stmt->fetchColumn();

// Total de categorias
$stmt = $pdo->query("SELECT COUNT(*) FROM categorias");
$totalCategorias = (int) $stmt->fetchColumn();

// Total de pedidos
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos");
$totalPedidos = (int) $stmt->fetchColumn();

// Pedidos pendentes
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM pedidos
    WHERE status_pedido = 'pendente'
");

$totalPendentes = (int) $stmt->fetchColumn();

// Valor total dos pedidos
$stmt = $pdo->query("
    SELECT COALESCE(SUM(valor_total), 0)
    FROM pedidos
    WHERE status_pedido <> 'cancelado'
");

$valorTotalPedidos = (float) $stmt->fetchColumn();

// Pedidos recentes
$stmt = $pdo->query("
    SELECT
        pedidos.id,
        pedidos.data_pedido,
        pedidos.valor_total,
        pedidos.status_pedido,
        usuarios.nome AS cliente_nome
    FROM pedidos
    INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id
    ORDER BY pedidos.data_pedido DESC
    LIMIT 5
");

$pedidosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Dashboard - Natália Milanese Joias
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

    <!-- CABEÇALHO -->

    <header class="topo">

        <div>

            <h1>
                Natália Milanese Joias
            </h1>

            <p>
                Painel administrativo
            </p>

        </div>

        <div>

            Olá,
            <?= htmlspecialchars($_SESSION['usuario_nome']) ?>

        </div>

    </header>


    <!-- LAYOUT -->

    <div class="layout">


        <!-- MENU LATERAL -->

        <aside class="menu">

            <h2>
                Administração
            </h2>

            <a href="index.php">
                Dashboard
            </a>

            <a href="produtos/index.php">
                Produtos
            </a>

            <a href="categorias/index.php">
                Categorias
            </a>

            <a href="pedidos/index.php">
                Pedidos
            </a>

            <a href="../cliente/minha_conta.php">
                Minha conta
            </a>

            <a href="../index.php">
                Voltar para a loja
            </a>

            <a href="../logout.php">
                Sair
            </a>

        </aside>


        <!-- CONTEÚDO -->

        <main class="conteudo">

            <div class="acoes-container">

                <div>

                    <h2>
                        Dashboard
                    </h2>

                    <p>
                        Visão geral da loja
                    </p>

                </div>

            </div>


            <!-- CARDS -->

            <section class="cards">


                <div class="card">

                    <div class="titulo">
                        Produtos
                    </div>

                    <div class="numero">
                        <?= $totalProdutos ?>
                    </div>

                </div>


                <div class="card">

                    <div class="titulo">
                        Categorias
                    </div>

                    <div class="numero">
                        <?= $totalCategorias ?>
                    </div>

                </div>


                <div class="card">

                    <div class="titulo">
                        Pedidos
                    </div>

                    <div class="numero">
                        <?= $totalPedidos ?>
                    </div>

                </div>


                <div class="card">

                    <div class="titulo">
                        Pedidos pendentes
                    </div>

                    <div class="numero">
                        <?= $totalPendentes ?>
                    </div>

                </div>


            </section>


            <!-- VALOR TOTAL -->

            <section class="card">

                <div class="titulo">
                    Valor total dos pedidos
                </div>

                <div class="numero">

                    R$
                    <?= number_format(
                        $valorTotalPedidos,
                        2,
                        ',',
                        '.'
                    ) ?>

                </div>

            </section>


            <!-- PEDIDOS RECENTES -->

            <section class="card">

                <div class="acoes-container">

                    <div>

                        <h3>
                            Pedidos recentes
                        </h3>

                        <p>
                            Últimos pedidos realizados na loja.
                        </p>

                    </div>

                    <a
                        href="pedidos/index.php"
                        class="btn"
                    >
                        Ver todos
                    </a>

                </div>


                <?php if (count($pedidosRecentes) > 0): ?>

                    <div class="tabela-container">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Pedido
                                    </th>

                                    <th>
                                        Cliente
                                    </th>

                                    <th>
                                        Data
                                    </th>

                                    <th>
                                        Valor
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Ação
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($pedidosRecentes as $pedido): ?>

                                    <tr>

                                        <td>
                                            #<?= $pedido['id'] ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $pedido['cliente_nome']
                                            ) ?>
                                        </td>

                                        <td>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $pedido['data_pedido']
                                                )
                                            ) ?>

                                        </td>

                                        <td>

                                            R$
                                            <?= number_format(
                                                $pedido['valor_total'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </td>

                                        <td>

                                            <span class="status">

                                                <?= htmlspecialchars(
                                                    $pedido['status_pedido']
                                                ) ?>

                                            </span>

                                        </td>

                                        <td>

                                            <a
                                                href="pedidos/visualizar.php?id=<?= $pedido['id'] ?>"
                                                class="btn"
                                            >
                                                Visualizar
                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <p class="vazio">
                        Nenhum pedido cadastrado.
                    </p>

                <?php endif; ?>

            </section>

        </main>

    </div>

</body>

</html>