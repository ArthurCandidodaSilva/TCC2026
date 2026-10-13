<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

verificarAdmin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

// Busca o produto
$sql = "SELECT id, nome, imagem FROM produtos WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o produto existe
if (!$produto) {
    header('Location: index.php');
    exit;
}


// Verifica se o produto já foi utilizado em algum pedido
$sql = "SELECT COUNT(*) 
        FROM itens_pedido 
        WHERE produto_id = :produto_id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':produto_id' => $id
]);

$quantidadeItens = $stmt->fetchColumn();


// Se o produto já foi vendido, não permite excluir
if ($quantidadeItens > 0) {

    $erro = 'Não é possível excluir este produto porque ele está associado a um ou mais pedidos. Para preservar o histórico das vendas, o produto não pode ser removido.';

} else {

    // Exclui o produto
    $sql = "DELETE FROM produtos WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);


    // Exclui a imagem do produto
    if (
        !empty($produto['imagem'])
    ) {

        $caminhoImagem =
            __DIR__ . '/../../uploads/produtos/' . $produto['imagem'];

        if (file_exists($caminhoImagem)) {
            unlink($caminhoImagem);
        }
    }


    $sucesso = 'Produto excluído com sucesso.';
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
        Excluir Produto - Natália Milanese Joias
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

            <h1>Natália Milanese Joias</h1>

            <p>Painel administrativo</p>

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

            <h2>Administração</h2>

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

            <h2>Excluir Produto</h2>


            <?php if ($erro): ?>

                <div class="erro">

                    <?= htmlspecialchars($erro) ?>

                </div>

                <br>

                <a
                    href="index.php"
                    class="btn"
                >
                    Voltar para produtos
                </a>

            <?php endif; ?>


            <?php if ($sucesso): ?>

                <div class="sucesso">

                    <?= htmlspecialchars($sucesso) ?>

                </div>

                <br>

                <a
                    href="index.php"
                    class="btn"
                >
                    Voltar para produtos
                </a>

            <?php endif; ?>


        </main>

    </div>

</body>

</html>