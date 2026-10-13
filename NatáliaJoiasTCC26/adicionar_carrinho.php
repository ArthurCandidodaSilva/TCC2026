<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';


verificarLogin();

$produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);

if (!$produto_id) {
    header('Location: produtos.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/*
 * Verifica se o produto existe
 * e se possui estoque.
 */
$sql = "SELECT id, estoque
        FROM produtos
        WHERE id = :id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $produto_id
]);

$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: produtos.php');
    exit;
}

if ($produto['estoque'] <= 0) {
    header('Location: produto.php?id=' . $produto_id);
    exit;
}

/*
 * Verifica se o produto
 * já está no carrinho do usuário.
 */
$sql = "SELECT id, quantidade
        FROM carrinhos
        WHERE usuario_id = :usuario_id
          AND produto_id = :produto_id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':usuario_id' => $usuario_id,
    ':produto_id' => $produto_id
]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {

    $nova_quantidade = $item['quantidade'] + 1;

    /*
     * Não permite colocar no carrinho
     * uma quantidade maior que o estoque.
     */
    if ($nova_quantidade > $produto['estoque']) {
        $nova_quantidade = $produto['estoque'];
    }

    $sql = "UPDATE carrinhos
            SET quantidade = :quantidade
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':quantidade' => $nova_quantidade,
        ':id' => $item['id']
    ]);

} else {

    $sql = "INSERT INTO carrinhos
            (
                usuario_id,
                produto_id,
                quantidade
            )
            VALUES
            (
                :usuario_id,
                :produto_id,
                1
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':produto_id' => $produto_id
    ]);
}

header('Location: carrinho.php');
exit;