<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificarAdmin();

$sql = "SELECT * FROM categorias ORDER BY nome ASC";

$stmt = $pdo->query($sql);

$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Categorias - Natália Milanese Joias</title>

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

        <a href="index.php">
            Categorias
        </a>

        <a href="../pedidos/index.php">
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

                <h1>Categorias</h1>

                <p>
                    Gerencie as categorias dos produtos da loja.
                </p>

            </div>

            <a
                href="criar.php"
                class="btn"
            >
                + Nova categoria
            </a>

        </div>

        <!-- =====================================================
             LISTAGEM
        ====================================================== -->

        <?php if (empty($categorias)): ?>

            <div class="card">

                <div class="erro">

                    Nenhuma categoria cadastrada.

                </div>

            </div>

        <?php else: ?>

            <div class="card">

                <h2>
                    Categorias cadastradas
                </h2>

                <div class="tabela-container">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Nome
                                </th>

                                <th>
                                    Ações
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($categorias as $categoria): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?= (int) $categoria['id'] ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars(
                                            $categoria['nome']
                                        ) ?>

                                    </td>

                                    <td>

                                        <div
                                            style="
                                                display:flex;
                                                gap:10px;
                                                flex-wrap:wrap;
                                            "
                                        >

                                            <a
                                                href="editar.php?id=<?= (int) $categoria['id'] ?>"
                                                class="btn"
                                            >
                                                Editar
                                            </a>

                                            <a
                                                href="excluir.php?id=<?= (int) $categoria['id'] ?>"
                                                class="btn"
                                                onclick="return confirm('Tem certeza que deseja excluir esta categoria?');"
                                            >
                                                Excluir
                                            </a>

                                        </div>

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