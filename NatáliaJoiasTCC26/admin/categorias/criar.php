<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificarAdmin();

$erro = '';
$nome = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {

        $erro = 'Informe o nome da categoria.';

    } else {

        $sql = "INSERT INTO categorias (nome) VALUES (:nome)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $nome
        ]);

        header('Location: index.php');
        exit;
    }
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
        Nova Categoria - Natália Milanese Joias
    </title>

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

                <h1>Nova Categoria</h1>

                <p>
                    Cadastre uma nova categoria para os produtos.
                </p>

            </div>

            <a
                href="index.php"
                class="btn"
            >
                ← Voltar
            </a>

        </div>

        <!-- =====================================================
             FORMULÁRIO
        ====================================================== -->

        <div class="card">

            <h2>
                Dados da categoria
            </h2>

            <?php if ($erro): ?>

                <div class="erro">

                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>

            <form method="POST">

                <label for="nome">
                    Nome da categoria:
                </label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    value="<?= htmlspecialchars($nome) ?>"
                    placeholder="Ex.: Anéis"
                    required
                >

                <button type="submit">
                    Cadastrar categoria
                </button>

            </form>

        </div>

    </main>

</div>

</body>

</html>