<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

verificarAdmin();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $preco = $_POST['preco'] ?? '';
    $tipo_material = trim($_POST['tipo_material'] ?? '');
    $estoque = $_POST['estoque'] ?? '';

    // Verifica se os campos obrigatórios foram preenchidos
    if (
        $nome === '' ||
        $categoria_id === '' ||
        $preco === '' ||
        $estoque === ''
    ) {

        $erro = 'Preencha todos os campos obrigatórios.';

    } elseif (
        !isset($_FILES['imagem']) ||
        $_FILES['imagem']['error'] !== UPLOAD_ERR_OK
    ) {

        $erro = 'Selecione uma imagem para o produto.';

    } else {

        $imagem = $_FILES['imagem'];

        // Extensões permitidas
        $extensoesPermitidas = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $extensao = strtolower(
            pathinfo($imagem['name'], PATHINFO_EXTENSION)
        );

        // Verifica a extensão
        if (!in_array($extensao, $extensoesPermitidas)) {

            $erro = 'Formato de imagem não permitido. Use JPG, JPEG, PNG ou WEBP.';

        } elseif ($imagem['size'] > 5 * 1024 * 1024) {

            $erro = 'A imagem deve ter no máximo 5 MB.';

        } else {

            // Verifica se realmente é uma imagem
            $tipoImagem = getimagesize($imagem['tmp_name']);

            if ($tipoImagem === false) {

                $erro = 'O arquivo enviado não é uma imagem válida.';

            } else {

                // Gera um nome único para a imagem
                $nomeImagem = uniqid('produto_', true) . '.' . $extensao;

                // Caminho da pasta de uploads
                $pastaUploads = __DIR__ . '/../../uploads/produtos/';

                // Cria a pasta caso ela não exista
                if (!is_dir($pastaUploads)) {
                    mkdir($pastaUploads, 0777, true);
                }

                $caminhoImagem = $pastaUploads . $nomeImagem;

                // Move a imagem para a pasta
                if (!move_uploaded_file($imagem['tmp_name'], $caminhoImagem)) {

                    $erro = 'Não foi possível salvar a imagem.';

                } else {

                    // Salva o produto no banco
                    $sql = "INSERT INTO produtos
                            (
                                categoria_id,
                                nome,
                                descricao,
                                preco,
                                tipo_material,
                                estoque,
                                imagem
                            )
                            VALUES
                            (
                                :categoria_id,
                                :nome,
                                :descricao,
                                :preco,
                                :tipo_material,
                                :estoque,
                                :imagem
                            )";

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        ':categoria_id' => $categoria_id,
                        ':nome' => $nome,
                        ':descricao' => $descricao,
                        ':preco' => $preco,
                        ':tipo_material' => $tipo_material,
                        ':estoque' => $estoque,
                        ':imagem' => $nomeImagem
                    ]);

                    header('Location: index.php');
                    exit;
                }
            }
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

    <title>Cadastrar Produto - Natália Milanese Joias</title>

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

            <h2>Cadastrar Produto</h2>


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
                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
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
                ><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>


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

                    <?php foreach ($categorias as $categoria): ?>

                        <option
                            value="<?= $categoria['id'] ?>"
                            <?= (($_POST['categoria_id'] ?? '') == $categoria['id']) ? 'selected' : '' ?>
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
                    value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>"
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
                    value="<?= htmlspecialchars($_POST['tipo_material'] ?? '') ?>"
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
                    value="<?= htmlspecialchars($_POST['estoque'] ?? '') ?>"
                    required
                >


                <!-- IMAGEM -->

                <label for="imagem">
                    Imagem do produto *
                </label>

                <input
                    type="file"
                    id="imagem"
                    name="imagem"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >

                <small>
                    Formatos permitidos: JPG, JPEG, PNG e WEBP.
                    Máximo: 5 MB.
                </small>

                <br><br>


                <!-- BOTÕES -->

                <div class="acoes-container">

                    <button type="submit">
                        Cadastrar produto
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