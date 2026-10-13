<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

verificarAdmin();

$sql = "SELECT 
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

        ORDER BY produtos.id ASC";

$stmt = $pdo->query($sql);

$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Produtos - Natália Milanese Joias
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
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


    <!-- ESTRUTURA -->

    <div class="layout">


        <!-- MENU -->

        <aside class="menu">

            <h2>
                Administração
            </h2>

            <a href="../index.php">
                Dashboard
            </a>

            <a href="index.php">
                Produtos
            </a>

            <a href="../categorias/index.php">
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


        <!-- CONTEÚDO -->

        <main class="conteudo">


            <!-- TÍTULO E AÇÃO -->

            <div class="acoes-container">

                <div>

                    <h2>
                        Produtos
                    </h2>

                    <p>
                        Gerencie os produtos cadastrados na loja.
                    </p>

                </div>

                <a
                    class="btn"
                    href="criar.php"
                >
                    + Novo produto
                </a>

            </div>


            <!-- TABELA -->

            <div class="tabela-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Imagem
                            </th>

                            <th>
                                Nome
                            </th>

                            <th>
                                Categoria
                            </th>

                            <th>
                                Preço
                            </th>

                            <th>
                                Material
                            </th>

                            <th>
                                Estoque
                            </th>

                            <th>
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (count($produtos) > 0): ?>

                            <?php foreach ($produtos as $produto): ?>

                                <tr>


                                    <!-- ID -->

                                    <td>
                                        <?= $produto['id'] ?>
                                    </td>


                                    <!-- IMAGEM -->

                                    <td>

                                        <?php if (!empty($produto['imagem'])): ?>

                                            <img
                                                class="produto-imagem"
                                                src="../../uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                                                alt="<?= htmlspecialchars($produto['nome']) ?>"
                                            >

                                        <?php else: ?>

                                            <span>
                                                Sem imagem
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- NOME -->

                                    <td>
                                        <?= htmlspecialchars($produto['nome']) ?>
                                    </td>


                                    <!-- CATEGORIA -->

                                    <td>
                                        <?= htmlspecialchars($produto['categoria_nome']) ?>
                                    </td>


                                    <!-- PREÇO -->

                                    <td>

                                        R$
                                        <?= number_format(
                                            $produto['preco'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>


                                    <!-- MATERIAL -->

                                    <td>
                                        <?= htmlspecialchars($produto['tipo_material']) ?>
                                    </td>


                                    <!-- ESTOQUE -->

                                    <td>

                                        <span class="status">
                                            <?= $produto['estoque'] ?>
                                        </span>

                                    </td>


                                    <!-- AÇÕES -->

                                    <td>

                                        <div class="acoes">

                                            <a
                                                class="btn btn-editar"
                                                href="editar.php?id=<?= $produto['id'] ?>"
                                            >
                                                Editar
                                            </a>

                                            <a
                                                class="btn btn-excluir"
                                                href="excluir.php?id=<?= $produto['id'] ?>"
                                                onclick="return confirm('Tem certeza que deseja excluir este produto?');"
                                            >
                                                Excluir
                                            </a>

                                        </div>

                                    </td>


                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td colspan="8">

                                    Nenhum produto cadastrado.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </main>

    </div>

</body>

</html>