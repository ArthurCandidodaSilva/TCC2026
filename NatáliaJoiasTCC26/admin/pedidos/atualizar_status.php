
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

$status_novo = $_POST['status_pedido'] ?? '';
$token = $_POST['csrf_token'] ?? '';

if (
    !$pedido_id ||
    !is_string($status_novo) ||
    !isset($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(400);
    exit('Solicitação inválida. Volte e tente novamente.');
}

try {
    $pdo->beginTransaction();

    // Bloqueia o pedido durante a alteração.
    $stmt = $pdo->prepare(
        'SELECT id, status_pedido
         FROM pedidos
         WHERE id = :id
         FOR UPDATE'
    );
    $stmt->execute([':id' => $pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new RuntimeException('Pedido não encontrado.');
    }

    $status_atual = $pedido['status_pedido'];

    // O pagamento deve ser aprovado pelo fluxo de pagamento,
    // não por uma alteração manual nesta tela.
    $transicoes = [
        'pendente' => ['cancelado'],
        'pago'     => ['enviado'],
        'enviado'  => ['entregue'],
    ];

    if (
        !isset($transicoes[$status_atual]) ||
        !in_array($status_novo, $transicoes[$status_atual], true)
    ) {
        throw new RuntimeException(
            'Essa mudança de status não é permitida.'
        );
    }

    // Só permite enviar um pedido com pagamento aprovado.
    if ($status_novo === 'enviado') {
        $stmt = $pdo->prepare(
            "SELECT status
             FROM pagamentos
             WHERE pedido_id = :pedido_id
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([':pedido_id' => $pedido_id]);
        $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pagamento || $pagamento['status'] !== 'aprovado') {
            throw new RuntimeException(
                'Não é possível enviar um pedido sem pagamento aprovado.'
            );
        }
    }

    // Devolve o estoque somente no cancelamento de um pedido
    // que ainda estava pendente. A transição é bloqueada após isso.
    if ($status_novo === 'cancelado') {
        $stmt = $pdo->prepare(
            'SELECT produto_id, quantidade
             FROM itens_pedido
             WHERE pedido_id = :pedido_id'
        );
        $stmt->execute([':pedido_id' => $pedido_id]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($itens as $item) {
            $produto_id = (int) $item['produto_id'];
            $quantidade = (int) $item['quantidade'];

            if ($produto_id > 0 && $quantidade > 0) {
                $estoque = $pdo->prepare(
                    'UPDATE produtos
                     SET estoque = estoque + :quantidade
                     WHERE id = :produto_id'
                );

                $estoque->execute([
                    ':quantidade' => $quantidade,
                    ':produto_id' => $produto_id,
                ]);
            }
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE pedidos
         SET status_pedido = :status
         WHERE id = :id'
    );
    $stmt->execute([
        ':status' => $status_novo,
        ':id' => $pedido_id,
    ]);

    $pdo->commit();

    // Gera um novo token após a operação.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: visualizar.php?id=' . $pedido_id);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Erro ao atualizar pedido ' . $pedido_id . ': ' . $e->getMessage()
    );

    http_response_code(400);
    echo 'Não foi possível atualizar o pedido. '
       . 'Confira o status atual e tente novamente.';
    exit;
}
