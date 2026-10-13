<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$pedido_id = filter_input(
    INPUT_POST,
    'pedido_id',
    FILTER_VALIDATE_INT
);

if (!$pedido_id) {
    header('Location: index.php');
    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Inicia transação
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Verifica se o pedido pertence ao usuário
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT
                id,
                valor_total,
                status_pedido

            FROM pedidos

            WHERE id = :pedido_id
              AND usuario_id = :usuario_id

            LIMIT 1
            FOR UPDATE";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':pedido_id' => $pedido_id,
        ':usuario_id' => $usuario_id
    ]);

    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {

        throw new Exception(
            'Pedido não encontrado.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Busca o pagamento
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT
                id,
                status,
                valor

            FROM pagamentos

            WHERE pedido_id = :pedido_id

            LIMIT 1
            FOR UPDATE";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':pedido_id' => $pedido_id
    ]);

    $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pagamento) {

        throw new Exception(
            'Pagamento não encontrado.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verifica se já foi aprovado
    |--------------------------------------------------------------------------
    */

    if ($pagamento['status'] === 'aprovado') {

        throw new Exception(
            'Este pagamento já foi aprovado.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Gera um ID de transação para teste
    |--------------------------------------------------------------------------
    */

    $id_transacao =
        'TESTE-PAG-' .
        date('YmdHis') .
        '-' .
        $pagamento['id'];

    /*
    |--------------------------------------------------------------------------
    | Atualiza o pagamento
    |--------------------------------------------------------------------------
    */

    $sql = "UPDATE pagamentos

            SET
                status = 'aprovado',
                id_transacao = :id_transacao,
                data_pagamento = CURRENT_TIMESTAMP

            WHERE id = :pagamento_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_transacao' => $id_transacao,
        ':pagamento_id' => $pagamento['id']
    ]);

    /*
    |--------------------------------------------------------------------------
    | Atualiza o pedido
    |--------------------------------------------------------------------------
    */

    $sql = "UPDATE pedidos

            SET status_pedido = 'pago'

            WHERE id = :pedido_id
              AND usuario_id = :usuario_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':pedido_id' => $pedido_id,
        ':usuario_id' => $usuario_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | Confirma a transação
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Volta para a página de pagamento
    |--------------------------------------------------------------------------
    */

    header(
        'Location: pagamento.php?id=' .
        $pedido_id
    );

    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo '<h1>Erro ao processar pagamento</h1>';

    echo '<p>';

    echo htmlspecialchars(
        $e->getMessage()
    );

    echo '</p>';

    echo '<p>';

    echo '<a href="pagamento.php?id=' .
        $pedido_id .
        '">';

    echo 'Voltar para pagamento';

    echo '</a>';

    echo '</p>';
}