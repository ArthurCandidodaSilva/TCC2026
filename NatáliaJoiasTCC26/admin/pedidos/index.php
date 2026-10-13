<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificarAdmin();

$sql = "
    SELECT
        pedidos.id,
        pedidos.data_pedido,
        pedidos.valor_total,
        pedidos.status_pedido,
        usuarios.nome AS cliente_nome,
        usuarios.email AS cliente_email
    FROM pedidos
    INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id
    ORDER BY pedidos.data_pedido DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pedidos - Administração</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>

<body>

<header class="topo">

    <div>

        <h1>Natália Milanese Joias</h1>

        <p>Painel administrativo</p>

    </div>

    <div>

        Olá,
        <?= htmlspecialchars($_SESSION['usuario_nome']) ?>

    </div>

</header>

<div class="layout">

    <aside class="menu">

        <h2>Administração</h2>

        <a href="../index.php">
            Dashboard
        </a>

        <a href="../produtos/index.php">
            Produtos
        </a>

        <a href="../categorias/index.php">
            Categorias
        </a>

        <a href="index.php">
            Pedidos
        </a>

        <a href="../../cliente/minha_conta.php">
            Minha conta
        </a>

        <a href="../../index.php">
            Voltar para a loja
        </a>

        <a href="../../logout.php">
            Sair
        </a>

    </aside>

    <main class="conteudo">

        <!-- =====================================================
             CABEÇALHO
        ====================================================== -->

        <div class="acoes-container">

            <div>

                <h1>Pedidos</h1>

                <p>
                    Gerencie e acompanhe os pedidos realizados
                    pelos clientes.
                </p>

            </div>

        </div>

        <!-- =====================================================
             LISTAGEM
        ====================================================== -->

        <?php if (empty($pedidos)): ?>

            <div class="card">

                <div class="erro">

                    Nenhum pedido encontrado.

                </div>

            </div>

        <?php else: ?>

            <div class="card">

                <h2>
                    Lista de pedidos
                </h2>

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
                                    E-mail
                                </th>

                                <th>
                                    Data
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Ações
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($pedidos as $pedido): ?>

                                <tr>

                                    <!-- PEDIDO -->

                                    <td>

                                        <strong>
                                            #<?= (int) $pedido['id'] ?>
                                        </strong>

                                    </td>

                                    <!-- CLIENTE -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $pedido['cliente_nome']
                                        ) ?>

                                    </td>

                                    <!-- E-MAIL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $pedido['cliente_email']
                                        ) ?>

                                    </td>

                                    <!-- DATA -->

                                    <td>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $pedido['data_pedido']
                                            )
                                        ) ?>

                                    </td>

                                    <!-- TOTAL -->

                                    <td>

                                        <strong>

                                            R$

                                            <?= number_format(
                                                $pedido['valor_total'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </strong>

                                    </td>

                                    <!-- STATUS -->

                                    <td>

                                        <span class="status">

                                            <?= htmlspecialchars(
                                                ucfirst(
                                                    $pedido['status_pedido']
                                                )
                                            ) ?>

                                        </span>

                                    </td>

                                    <!-- AÇÕES -->

                                    <td>

                                        <a
                                            href="visualizar.php?id=<?= (int) $pedido['id'] ?>"
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

            </div>

        <?php endif; ?>

    </main>

</div>

</body>

</html>