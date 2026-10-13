<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pedido_id = filter_input(
    INPUT_POST,
    'pedido_id',
    FILTER_VALIDATE_INT
);

$status_pedido = $_POST['status_pedido'] ?? '';

$status_permitidos = [
    'pendente',
    'pago',
    'enviado',
    'entregue',
    'cancelado'
];

if (!$pedido_id || !in_array($status_pedido, $status_permitidos, true)) {
    header('Location: index.php');
    exit;
}

$sql = "
    UPDATE pedidos
    SET status_pedido = :status_pedido
    WHERE id = :pedido_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':status_pedido' => $status_pedido,
    ':pedido_id' => $pedido_id
]);

header(
    'Location: visualizar.php?id=' . $pedido_id
);

exit;