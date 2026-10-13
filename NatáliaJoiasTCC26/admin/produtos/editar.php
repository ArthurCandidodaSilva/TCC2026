<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

verificarAdmin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Busca o produto pelo ID
$sql = "SELECT * FROM produtos WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o produto existe
if (!$produto) {
    die('Produto não encontrado.');
}

$erro = '';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $preco = $_POST['preco'] ?? '';
    $tipo_material = trim($_POST['tipo_material'] ?? '');
    $estoque = $_POST['estoque'] ?? '';

    // Validação dos campos obrigatórios
    if (
        $nome === '' ||
        $categoria_id === '' ||
        $preco === '' ||
        $estoque === ''
    ) {

        $erro = 'Preencha todos os campos obrigatórios.';

    } else {

        // Mantém a imagem atual
        $imagem = $produto['imagem'];

        // Verifica se uma nova imagem foi enviada
        if (
            isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            // Verifica se houve erro no upload
            if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {

                $erro = 'Erro ao enviar a nova imagem.';

            } else {

                // Limite de 5 MB
                if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {

                    $erro = 'A imagem deve ter no máximo 5 MB.';

                } else {

                    // Verifica se é realmente uma imagem
                    $infoImagem = getimagesize(
                        $_FILES['imagem']['tmp_name']
                    );

                    if ($infoImagem === false) {

                        $erro = 'O arquivo enviado não é uma imagem válida.';

                    } else {

                        // Extensão do arquivo
                        $extensao = strtolower(
                            pathinfo(
                                $_FILES['imagem']['name'],
                                PATHINFO_EXTENSION
                            )
                        );

                        // Extensões permitidas
                        $extensoesPermitidas = [
                            'jpg',
                            'jpeg',
                            'png',
                            'webp'
                        ];

                        if (!in_array($extensao, $extensoesPermitidas)) {

                            $erro = 'Formato de imagem não permitido. Use JPG, JPEG, PNG ou WEBP.';

                        } else {

                            // Gera um nome único para a nova imagem
                            $novoNome = uniqid('produto_', true) . '.' . $extensao;

                            // Caminho onde a imagem será salva
                            $pastaUpload = __DIR__ . '/../../uploads/produtos/';

                            // Cria a pasta caso não exista
                            if (!is_dir($pastaUpload)) {
                                mkdir($pastaUpload, 0777, true);
                            }

                            $caminhoImagem = $pastaUpload . $novoNome;

                            // Move a imagem para a pasta
                            if (!move_uploaded_file(
                                $_FILES['imagem']['tmp_name'],
                                $caminhoImagem
                            )) {

                                $erro = 'Não foi possível salvar a nova imagem.';

                            } else {

                                // Se existia uma imagem antiga, remove
                                if (
                                    !empty($produto['imagem']) &&
                                    file_exists(
                                        $pastaUpload . $produto['imagem']
                                    )
                                ) {
                                    unlink(
                                        $pastaUpload . $produto['imagem']
                                    );
                                }

                                // Atualiza o nome da imagem
                                $imagem = $novoNome;
                            }
                        }
                    }
                }
            }
        }

        // Só atualiza o banco se não houver erro
        if ($erro === '') {

            // Atualiza o produto
            $sql = "UPDATE produtos
                    SET
                        categoria_id = :categoria_id,
                        nome = :nome,
                        descricao = :descricao,
                        preco = :preco,
                        tipo_material = :tipo_material,
                        estoque = :estoque,
                        imagem = :imagem
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':categoria_id' => $categoria_id,
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':preco' => $preco,
                ':tipo_material' => $tipo_material,
                ':estoque' => $estoque,
                ':imagem' => $imagem,
                ':id' => $id
            ]);

            header('Location: index.php');
            exit;
        }
    }
}

// Busca as categorias
$sql = "SELECT * FROM categorias ORDER BY nome ASC";

$stmt = $pdo->query($sql);

$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Editar Produto - Natália Milanese Joias
    </title>

    <link rel="stylesheet" href="../../assets/css/admin.css">

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

            <h2>Editar Produto</h2>


            <!-- ERRO -->

            <?php if ($erro): ?>

                <div class="erro">

                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>


            <!-- FORMULÁRIO -->

            <form method="POST" enctype="multipart/form-data">


                <!-- NOME -->

                <label for="nome">
                    Nome do produto *
                </label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    value="<?= htmlspecialchars($_POST['nome'] ?? $produto['nome']) ?>"
                    required
                >


                <!-- DESCRIÇÃO -->

                <label for="descricao">
                    Descrição
                </label>

                <textarea
                    id="descricao"
                    name="descricao"
                    rows="5"
                ><?= htmlspecialchars($_POST['descricao'] ?? $produto['descricao']) ?></textarea>


                <!-- CATEGORIA -->

                <label for="categoria_id">
                    Categoria *
                </label>

                <select
                    id="categoria_id"
                    name="categoria_id"
                    required
                >

                    <option value="">
                        Selecione uma categoria
                    </option>

                    <?php
                    $categoriaSelecionada =
                        $_POST['categoria_id'] ??
                        $produto['categoria_id'];
                    ?>

                    <?php foreach ($categorias as $categoria): ?>

                        <option
                            value="<?= $categoria['id'] ?>"
                            <?= $categoria['id'] == $categoriaSelecionada ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($categoria['nome']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <!-- PREÇO -->

                <label for="preco">
                    Preço *
                </label>

                <input
                    type="number"
                    id="preco"
                    name="preco"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($_POST['preco'] ?? $produto['preco']) ?>"
                    required
                >


                <!-- MATERIAL -->

                <label for="tipo_material">
                    Material
                </label>

                <input
                    type="text"
                    id="tipo_material"
                    name="tipo_material"
                    value="<?= htmlspecialchars($_POST['tipo_material'] ?? $produto['tipo_material']) ?>"
                >


                <!-- ESTOQUE -->

                <label for="estoque">
                    Estoque *
                </label>

                <input
                    type="number"
                    id="estoque"
                    name="estoque"
                    min="0"
                    value="<?= htmlspecialchars($_POST['estoque'] ?? $produto['estoque']) ?>"
                    required
                >


          
<!-- IMAGENS DO PRODUTO -->
<div class="campo-imagens">

    <label>Imagem atual</label>

    <div class="produto-imagem">
        <?php if (!empty($produto['imagem'])): ?>
            <img
                src="../../uploads/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                alt="<?= htmlspecialchars($produto['nome']) ?>"
            >
        <?php else: ?>
            <p>Este produto não possui imagem.</p>
        <?php endif; ?>
    </div>

    <div class="campo-nova-imagem">
        <label for="imagem">Substituir imagem</label>

        <input
            type="file"
            id="imagem"
            name="imagem"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <small>
            Selecione um arquivo somente se quiser substituir a imagem atual.
            Formatos permitidos: JPG, JPEG, PNG e WEBP. Máximo: 5 MB.
        </small>
    </div>

</div>



                <br><br>


                <!-- BOTÕES -->

                <div class="acoes-container">

                    <button type="submit">
                        Salvar alterações
                    </button>

                    <a
                        href="index.php"
                        class="btn"
                    >
                        Cancelar
                    </a>

                </div>

            </form>

        </main>

    </div>

</body>

</html>