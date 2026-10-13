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

/*
|--------------------------------------------------------------------------
| Buscar categoria
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM categorias WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    header('Location: index.php');
    exit;
}

$erro = '';

$nome = $categoria['nome'];

/*
|--------------------------------------------------------------------------
| Atualizar categoria
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {

        $erro = 'Informe o nome da categoria.';

    } else {

        $sql = "
            UPDATE categorias
            SET nome = :nome
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $nome,
            ':id' => $id
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
        Editar Categoria - Natália Milanese Joias
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

                <h1>Editar Categoria</h1>

                <p>
                    Altere as informações da categoria.
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
                    required
                >

                <button type="submit">
                    Salvar alterações
                </button>

            </form>

        </div>

    </main>

</div>

</body>

</html>