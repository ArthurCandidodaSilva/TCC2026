<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificarAdmin();

$id = $_GET['id'] ?? null;

if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    header('Location: index.php');
    exit;
}

$id = (int) $id;

// Variáveis inicializadas para evitar avisos do VS Code
$categoria = null;
$quantidadeProdutos = 0;
$erro = '';

try {

    // Busca a categoria
    $sql = "SELECT * FROM categorias WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);

    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se a categoria existe
    if (!$categoria) {
        header('Location: index.php');
        exit;
    }


    // Verifica se existem produtos usando esta categoria
    $sql = "
        SELECT COUNT(*)
        FROM produtos
        WHERE categoria_id = :categoria_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':categoria_id' => $id
    ]);

    $quantidadeProdutos = (int) $stmt->fetchColumn();


    // Se houver produtos vinculados, não permite excluir
    if ($quantidadeProdutos > 0) {

        $erro = 'Esta categoria não pode ser excluída porque está sendo utilizada por '
              . $quantidadeProdutos
              . ' produto(s).';

    } else {

        // Exclui a categoria
        $sql = "
            DELETE FROM categorias
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {

    $erro = 'Ocorreu um erro ao tentar excluir a categoria.';
}

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
        Excluir Categoria - Natália Milanese Joias
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
                Painel Administrativo
            </p>

        </div>

        <a
            href="../../logout.php"
            class="btn"
        >
            Sair
        </a>

    </header>


    <!-- LAYOUT -->

    <div class="layout">


        <!-- MENU -->

        <aside class="menu">

            <h2>
                Administração
            </h2>

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


        <!-- CONTEÚDO -->

        <main class="conteudo">


            <div class="acoes-container">

                <div>

                    <h2>
                        Excluir Categoria
                    </h2>

                    <p>
                        Categoria:
                        <strong>
                            <?= htmlspecialchars($categoria['nome']) ?>
                        </strong>
                    </p>

                </div>

                <a
                    href="index.php"
                    class="btn btn-secundario"
                >
                    Voltar
                </a>

            </div>


            <!-- MENSAGEM DE ERRO -->

            <div class="card">

                <div class="erro">

                    <strong>
                        Não foi possível excluir esta categoria.
                    </strong>

                    <p>
                        <?= htmlspecialchars($erro) ?>
                    </p>


                    <?php if ($quantidadeProdutos > 0): ?>

                        <p>
                            Para excluir esta categoria, primeiro altere a
                            categoria dos produtos que estão utilizando-a.
                        </p>

                    <?php endif; ?>

                </div>


                <a
                    href="index.php"
                    class="btn"
                >
                    Voltar para categorias
                </a>

            </div>


        </main>

    </div>

</body>

</html>