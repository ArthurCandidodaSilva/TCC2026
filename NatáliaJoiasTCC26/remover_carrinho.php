<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$carrinho_id = filter_input(
    INPUT_POST,
    'carrinho_id',
    FILTER_VALIDATE_INT
);

if (!$carrinho_id) {
    header('Location: carrinho.php');
    exit;
}

$sql = "DELETE FROM carrinhos
        WHERE id = :carrinho_id
          AND usuario_id = :usuario_id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':carrinho_id' => $carrinho_id,
    ':usuario_id' => $usuario_id
]);

header('Location: carrinho.php');
exit;