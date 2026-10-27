<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificarAdmin();

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: index.php');
    exit;
}

$pedido_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Buscar pedido + cliente
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        pedidos.id,
        pedidos.data_pedido,
        pedidos.valor_total,
        pedidos.status_pedido,

        usuarios.nome AS cliente_nome,
        usuarios.cpf AS cliente_cpf,
        usuarios.email AS cliente_email,
        usuarios.telefone AS cliente_telefone,
        usuarios.estado AS cliente_estado,
        usuarios.cidade AS cliente_cidade

    FROM pedidos

    INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id

    WHERE pedidos.id = :pedido_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pedido_id' => $pedido_id
]);

$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar produtos do pedido
|--------------------------------------------------------------------------
*/

$sql_itens = "
    SELECT
        itens_pedido.id,
        itens_pedido.quantidade,
        itens_pedido.preco_unitario,

        produtos.nome AS produto_nome,
        produtos.imagem AS produto_imagem

    FROM itens_pedido

    INNER JOIN produtos
        ON itens_pedido.produto_id = produtos.id

    WHERE itens_pedido.pedido_id = :pedido_id

    ORDER BY itens_pedido.id ASC
";

$stmt_itens = $pdo->prepare($sql_itens);

$stmt_itens->execute([
    ':pedido_id' => $pedido_id
]);

$itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Buscar pagamento
|--------------------------------------------------------------------------
*/

$sql_pagamento = "
    SELECT
        id,
        metodo,
        status,
        valor,
        id_transacao,
        data_pagamento

    FROM pagamentos

    WHERE pedido_id = :pedido_id

    ORDER BY id DESC

    LIMIT 1
";

$stmt_pagamento = $pdo->prepare($sql_pagamento);

$stmt_pagamento->execute([
    ':pedido_id' => $pedido_id
]);

$pagamento = $stmt_pagamento->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Funções auxiliares
|--------------------------------------------------------------------------
*/

function formatarCpf(string $cpf): string
{
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    return $cpf;
}

function formatarTelefone(?string $telefone): string
{
    if (!$telefone) {
        return 'Não informado';
    }

    $telefone = preg_replace('/\D/', '', $telefone);

    if (strlen($telefone) === 11) {
        return '(' . substr($telefone, 0, 2) . ') ' .
               substr($telefone, 2, 5) . '-' .
               substr($telefone, 7, 4);
    }

    if (strlen($telefone) === 10) {
        return '(' . substr($telefone, 0, 2) . ') ' .
               substr($telefone, 2, 4) . '-' .
               substr($telefone, 6, 4);
    }

    return $telefone;
}

function nomeMetodoPagamento(?string $metodo): string
{
    switch ($metodo) {
        case 'pix':
            return 'PIX';

        case 'cartao':
            return 'Cartão';

        case 'boleto':
            return 'Boleto';

        default:
            return 'Não informado';
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Pedido #<?= (int) $pedido['id'] ?> - Administração
    </title>

    <link rel="stylesheet" href="../../assets/css/admin.css">

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

        <!-- CABEÇALHO -->

        <div class="acoes-container">

            <div>

                <h1>
                    Pedido #<?= (int) $pedido['id'] ?>
                </h1>

                <p>
                    Visualização completa do pedido
                </p>

            </div>

            <a
                href="index.php"
                class="btn"
            >
                ← Voltar para pedidos
            </a>

        </div>

        <!-- =====================================================
             INFORMAÇÕES DO PEDIDO
        ====================================================== -->

        <div class="card">

            <h2>Informações do pedido</h2>

            <div class="cards">

                <div class="card">

                    <strong>Número do pedido</strong>

                    <p>
                        #<?= (int) $pedido['id'] ?>
                    </p>

                </div>

                <div class="card">

                    <strong>Data do pedido</strong>

                    <p>
                        <?= date(
                            'd/m/Y H:i',
                            strtotime($pedido['data_pedido'])
                        ) ?>
                    </p>

                </div>

                <div class="card">

                    <strong>Status atual</strong>

                    <p>

                        <span class="status">
                            <?= htmlspecialchars(
                                ucfirst($pedido['status_pedido'])
                            ) ?>
                        </span>

                    </p>

                </div>

                <div class="card">

                    <strong>Valor total</strong>

                    <p>
                        R$
                        <?= number_format(
                            $pedido['valor_total'],
                            2,
                            ',',
                            '.'
                        ) ?>
                    </p>

                </div>

            </div>

        </div>

        <!-- =====================================================
             CLIENTE
        ====================================================== -->

        <div class="card">

            <h2>Dados do cliente</h2>

            <div class="cards">

                <div class="card">

                    <strong>Nome</strong>

                    <p>
                        <?= htmlspecialchars(
                            $pedido['cliente_nome']
                        ) ?>
                    </p>

                </div>

                <div class="card">

                    <strong>CPF</strong>

                    <p>
                        <?= htmlspecialchars(
                            formatarCpf($pedido['cliente_cpf'])
                        ) ?>
                    </p>

                </div>

                <div class="card">

                    <strong>E-mail</strong>

                    <p>
                        <?= htmlspecialchars(
                            $pedido['cliente_email']
                        ) ?>
                    </p>

                </div>

                <div class="card">

                    <strong>Telefone</strong>

                    <p>
                        <?= htmlspecialchars(
                            formatarTelefone(
                                $pedido['cliente_telefone']
                            )
                        ) ?>
                    </p>

                </div>

                <div class="card">

                    <strong>Estado</strong>

                    <p>
                        <?= htmlspecialchars(
                            $pedido['cliente_estado']
                            ?: 'Não informado'
                        ) ?>
                    </p>

                </div>

                <div class="card">

                    <strong>Cidade</strong>

                    <p>
                        <?= htmlspecialchars(
                            $pedido['cliente_cidade']
                            ?: 'Não informado'
                        ) ?>
                    </p>

                </div>

            </div>

        </div>

        <!-- =====================================================
             PRODUTOS
        ====================================================== -->

        <div class="card">

            <h2>Produtos do pedido</h2>

            <?php if (empty($itens)): ?>

                <div class="erro">

                    Nenhum produto encontrado neste pedido.

                </div>

            <?php else: ?>

                <div class="tabela-container">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Produto
                                </th>

                                <th>
                                    Quantidade
                                </th>

                                <th>
                                    Preço unitário
                                </th>

                                <th>
                                    Subtotal
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($itens as $item): ?>

                                <?php

                                $subtotal =
                                    $item['quantidade'] *
                                    $item['preco_unitario'];

                                ?>

                                <tr>

                                    <td>

                                        <div
                                            style="
                                                display:flex;
                                                align-items:center;
                                                gap:15px;
                                            "
                                        >

                                            <?php if (!empty($item['produto_imagem'])): ?>

                                                <img
                                                    src="../../uploads/produtos/<?= htmlspecialchars($item['produto_imagem']) ?>"
                                                    alt="<?= htmlspecialchars($item['produto_nome']) ?>"
                                                    class="produto-imagem"
                                                >

                                            <?php endif; ?>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $item['produto_nome']
                                                ) ?>
                                            </strong>

                                        </div>

                                    </td>

                                    <td>

                                        <?= (int) $item['quantidade'] ?>

                                    </td>

                                    <td>

                                        R$
                                        <?= number_format(
                                            $item['preco_unitario'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>

                                    <td>

                                        <strong>

                                            R$
                                            <?= number_format(
                                                $subtotal,
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </strong>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div
                    style="
                        margin-top:20px;
                        text-align:right;
                        font-size:20px;
                    "
                >

                    <strong>

                        Total do pedido:

                        R$

                        <?= number_format(
                            $pedido['valor_total'],
                            2,
                            ',',
                            '.'
                        ) ?>

                    </strong>

                </div>

            <?php endif; ?>

        </div>

        <!-- =====================================================
             PAGAMENTO
        ====================================================== -->

        <div class="card">

            <h2>Informações do pagamento</h2>

            <?php if ($pagamento): ?>

                <div class="cards">

                    <div class="card">

                        <strong>Método</strong>

                        <p>

                            <?= htmlspecialchars(
                                nomeMetodoPagamento(
                                    $pagamento['metodo']
                                )
                            ) ?>

                        </p>

                    </div>

                    <div class="card">

                        <strong>Status</strong>

                        <p>

                            <span class="status">

                                <?= htmlspecialchars(
                                    ucfirst(
                                        $pagamento['status']
                                    )
                                ) ?>

                            </span>

                        </p>

                    </div>

                    <div class="card">

                        <strong>Valor</strong>

                        <p>

                            R$

                            <?= number_format(
                                $pagamento['valor'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </p>

                    </div>

                    <div class="card">

                        <strong>ID da transação</strong>

                        <p>

                            <?= $pagamento['id_transacao']
                                ? htmlspecialchars(
                                    $pagamento['id_transacao']
                                )
                                : 'Não informado'
                            ?>

                        </p>

                    </div>

                    <div class="card">

                        <strong>Data do pagamento</strong>

                        <p>

                            <?= $pagamento['data_pagamento']
                                ? date(
                                    'd/m/Y H:i',
                                    strtotime(
                                        $pagamento['data_pagamento']
                                    )
                                )
                                : 'Não informado'
                            ?>

                        </p>

                    </div>

                </div>

            <?php else: ?>

                <div class="erro">

                    Nenhum pagamento registrado para este pedido.

                </div>

            <?php endif; ?>

        </div>

        <!-- =====================================================
             ALTERAÇÃO DE STATUS
        ====================================================== -->
        
<div class="card status-pedido-card">

    <h2>Atualizar status do pedido</h2>

    <p>
        Selecione a situação atual do pedido e confirme a alteração.
    </p>

    <form action="atualizar_status.php" method="POST" class="form-status">

        <input
            type="hidden"
            name="pedido_id"
            value="<?= (int) $pedido['id'] ?>"
        >

        <div class="campo-status">
            <label for="status_pedido">Status do pedido</label>

            <select name="status_pedido" id="status_pedido" required>
                <option value="pendente"
                    <?= $pedido['status_pedido'] === 'pendente' ? 'selected' : '' ?>>
                    Pendente
                </option>

                <option value="pago"
                    <?= $pedido['status_pedido'] === 'pago' ? 'selected' : '' ?>>
                    Pago
                </option>

                <option value="enviado"
                    <?= $pedido['status_pedido'] === 'enviado' ? 'selected' : '' ?>>
                    Enviado
                </option>

                <option value="entregue"
                    <?= $pedido['status_pedido'] === 'entregue' ? 'selected' : '' ?>>
                    Entregue
                </option>

                <option value="cancelado"
                    <?= $pedido['status_pedido'] === 'cancelado' ? 'selected' : '' ?>>
                    Cancelado
                </option>
            </select>
        </div>

        <button type="submit" class="btn-salvar-status">
            Salvar novo status
        </button>
<input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars(
        $_SESSION['csrf_token'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    </form>

 </div>

</main>

</div>

</body>

</html>