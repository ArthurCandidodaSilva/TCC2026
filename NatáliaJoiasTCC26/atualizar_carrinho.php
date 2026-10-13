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

$quantidade = filter_input(
    INPUT_POST,
    'quantidade',
    FILTER_VALIDATE_INT
);

if (!$carrinho_id || $quantidade === false || $quantidade < 1) {
    header('Location: carrinho.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Verifica se o item pertence ao usuário e busca o estoque
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            carrinhos.id,
            produtos.estoque
        FROM carrinhos

        INNER JOIN produtos
            ON carrinhos.produto_id = produtos.id

        WHERE carrinhos.id = :carrinho_id
          AND carrinhos.usuario_id = :usuario_id

        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':carrinho_id' => $carrinho_id,
    ':usuario_id' => $usuario_id
]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: carrinho.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Não permite quantidade maior que o estoque
|--------------------------------------------------------------------------
*/

if ($quantidade > $item['estoque']) {
    $quantidade = $item['estoque'];
}

/*
|--------------------------------------------------------------------------
| Atualiza a quantidade
|--------------------------------------------------------------------------
*/

$sql = "UPDATE carrinhos
        SET quantidade = :quantidade
        WHERE id = :carrinho_id
          AND usuario_id = :usuario_id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':quantidade' => $quantidade,
    ':carrinho_id' => $carrinho_id,
    ':usuario_id' => $usuario_id
]);

header('Location: carrinho.php');
exit;