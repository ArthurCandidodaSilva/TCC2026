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

$metodo = $_POST['metodo'] ?? '';

/*
|--------------------------------------------------------------------------
| Validação básica
|--------------------------------------------------------------------------
*/

$metodos_validos = [
    'pix',
    'cartao',
    'boleto'
];

if (
    !$pedido_id ||
    !in_array($metodo, $metodos_validos, true)
) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Busca o pedido
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            valor_total,
            status_pedido

        FROM pedidos

        WHERE id = :pedido_id
          AND usuario_id = :usuario_id

        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pedido_id' => $pedido_id,
    ':usuario_id' => $usuario_id
]);

$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Não permite pagamento de pedido cancelado
|--------------------------------------------------------------------------
*/

if ($pedido['status_pedido'] === 'cancelado') {

    echo '<h1>Pedido cancelado</h1>';

    echo '<p>';
    echo 'Não é possível realizar o pagamento deste pedido.';
    echo '</p>';

    echo '<p>';
    echo '<a href="index.php">Voltar para a loja</a>';
    echo '</p>';

    exit;
}

/*
|--------------------------------------------------------------------------
| Verifica se já existe pagamento
|--------------------------------------------------------------------------
*/

$sql = "SELECT id
        FROM pagamentos
        WHERE pedido_id = :pedido_id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pedido_id' => $pedido_id
]);

$pagamento_existente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pagamento_existente) {

    header(
        'Location: pagamento.php?id=' .
        $pedido_id
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Cria o pagamento
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO pagamentos
        (
            pedido_id,
            metodo,
            status,
            valor
        )
        VALUES
        (
            :pedido_id,
            :metodo,
            'pendente',
            :valor
        )";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pedido_id' => $pedido_id,
    ':metodo' => $metodo,
    ':valor' => $pedido['valor_total']
]);

/*
|--------------------------------------------------------------------------
| Redireciona para a página de pagamento
|--------------------------------------------------------------------------
*/

header(
    'Location: pagamento.php?id=' .
    $pedido_id
);

exit;