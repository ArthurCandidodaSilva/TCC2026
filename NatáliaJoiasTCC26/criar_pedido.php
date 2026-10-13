<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

try {

    /*
    |--------------------------------------------------------------------------
    | Inicia uma transação
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Busca o carrinho do usuário
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT
                carrinhos.id AS carrinho_id,
                carrinhos.produto_id,
                carrinhos.quantidade,

                produtos.nome,
                produtos.preco,
                produtos.estoque

            FROM carrinhos

            INNER JOIN produtos
                ON carrinhos.produto_id = produtos.id

            WHERE carrinhos.usuario_id = :usuario_id

            FOR UPDATE";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':usuario_id' => $usuario_id
    ]);

    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Verifica se o carrinho está vazio
    |--------------------------------------------------------------------------
    */

    if (empty($itens)) {

        $pdo->rollBack();

        header('Location: carrinho.php');

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Calcula o total e verifica estoque
    |--------------------------------------------------------------------------
    */

    $total = 0;

    foreach ($itens as $item) {

        if ($item['quantidade'] > $item['estoque']) {

            throw new Exception(
                'Estoque insuficiente para o produto: ' .
                $item['nome']
            );
        }

        $subtotal =
            $item['preco'] * $item['quantidade'];

        $total += $subtotal;
    }

    /*
    |--------------------------------------------------------------------------
    | Cria o pedido
    |--------------------------------------------------------------------------
    */

    $sql = "INSERT INTO pedidos
            (
                usuario_id,
                valor_total,
                status_pedido
            )
            VALUES
            (
                :usuario_id,
                :valor_total,
                'pendente'
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':valor_total' => $total
    ]);

    /*
    |--------------------------------------------------------------------------
    | Pega o ID do pedido criado
    |--------------------------------------------------------------------------
    */

    $pedido_id = $pdo->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | Cria os itens do pedido
    |--------------------------------------------------------------------------
    */

    $sql = "INSERT INTO itens_pedido
            (
                pedido_id,
                produto_id,
                quantidade,
                preco_unitario
            )
            VALUES
            (
                :pedido_id,
                :produto_id,
                :quantidade,
                :preco_unitario
            )";

    $stmtItem = $pdo->prepare($sql);

    /*
    |--------------------------------------------------------------------------
    | Atualiza o estoque
    |--------------------------------------------------------------------------
    */

    $sqlEstoque = "UPDATE produtos
                   SET estoque = estoque - :quantidade
                   WHERE id = :produto_id";

    $stmtEstoque = $pdo->prepare($sqlEstoque);

    foreach ($itens as $item) {

        $stmtItem->execute([
            ':pedido_id' => $pedido_id,
            ':produto_id' => $item['produto_id'],
            ':quantidade' => $item['quantidade'],
            ':preco_unitario' => $item['preco']
        ]);

        $stmtEstoque->execute([
            ':quantidade' => $item['quantidade'],
            ':produto_id' => $item['produto_id']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Limpa o carrinho
    |--------------------------------------------------------------------------
    */

    $sql = "DELETE FROM carrinhos
            WHERE usuario_id = :usuario_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':usuario_id' => $usuario_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | Confirma tudo
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Vai para a página do pedido
    |--------------------------------------------------------------------------
    */

header(
    'Location: pagamento.php?id=' .
    $pedido_id
);
    exit;

} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | Cancela a transação em caso de erro
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo '<h1>Erro ao finalizar o pedido</h1>';

    echo '<p>';

    echo htmlspecialchars(
        $e->getMessage()
    );

    echo '</p>';

    echo '<p>';

    echo '<a href="carrinho.php">';
    echo 'Voltar para o carrinho';
    echo '</a>';

    echo '</p>';
}