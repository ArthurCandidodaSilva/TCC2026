<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';


verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$sql = "
    SELECT
        id,
        nome,
        cpf,
        sexo,
        email,
        telefone,
        estado,
        cidade,
        tipo_usuario
    FROM usuarios
    WHERE id = :usuario_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':usuario_id' => $usuario_id
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: ../logout.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Formatação de dados
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


/*
|--------------------------------------------------------------------------
| Verifica o tipo de usuário
|--------------------------------------------------------------------------
*/

$isAdmin = $usuario['tipo_usuario'] === 'admin';

?>

<!DOCTYPE html>

<html lang="pt-BR">


<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Minha Conta - Natália Milanese Joias</title>

    <!-- Estilos gerais da loja e do rodapé -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <!-- Estilos específicos da área do cliente -->
    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >
</head>


<body>

    <?php if ($isAdmin): ?>

        <!-- =====================================================
             ÁREA DO ADMINISTRADOR
             ===================================================== -->

        <header class="topo">

            <div>

                <h1>
                    Natália Milanese Joias
                </h1>

                <p>
                    Painel administrativo
                </p>

            </div>

            <div>

                Olá,
                <?= htmlspecialchars($usuario['nome']) ?>

            </div>

        </header>


        <div class="layout">

            <aside class="menu">

                <h2>
                    Administração
                </h2>

                <a href="../admin/index.php">
                    Dashboard
                </a>

                <a href="../admin/produtos/index.php">
                    Produtos
                </a>

                <a href="../admin/categorias/index.php">
                    Categorias
                </a>

                <a href="../admin/pedidos/index.php">
                    Pedidos
                </a>

                <a href="minha_conta.php">
                    Minha conta
                </a>

                <a href="../index.php">
                    Voltar para a loja
                </a>

                <a href="../logout.php">
                    Sair
                </a>

            </aside>


            <main class="conteudo">

                <div class="acoes-container">

                    <div>

                        <h2>
                            Minha Conta
                        </h2>

                        <p>
                            Consulte seus dados cadastrados.
                        </p>

                    </div>

                </div>


                <!-- DADOS DA CONTA -->

                <section class="card">

                    <h3>
                        Meus dados
                    </h3>


                    <div class="dados-conta">

                        <div class="dado">

                            <span>
                                Nome
                            </span>

                            <strong>
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                CPF
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    formatarCpf($usuario['cpf'])
                                ) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Sexo
                            </span>

                            <strong>
                                <?= $usuario['sexo']
                                    ? htmlspecialchars($usuario['sexo'])
                                    : 'Não informado'
                                ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                E-mail
                            </span>

                            <strong>
                                <?= htmlspecialchars($usuario['email']) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Telefone
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    formatarTelefone($usuario['telefone'])
                                ) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Estado
                            </span>

                            <strong>
                                <?= $usuario['estado']
                                    ? htmlspecialchars($usuario['estado'])
                                    : 'Não informado'
                                ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Cidade
                            </span>

                            <strong>
                                <?= $usuario['cidade']
                                    ? htmlspecialchars($usuario['cidade'])
                                    : 'Não informado'
                                ?>
                            </strong>

                        </div>

                    </div>

                </section>


                <!-- ÁREA DO ADMINISTRADOR -->

                <section class="card">

                    <h3>
                        Área administrativa
                    </h3>

                    <p>
                        Acesse o painel de gerenciamento da loja.
                    </p>

                    <div class="form-acoes">

                        <a
                            href="../admin/index.php"
                            class="btn"
                        >
                            Painel administrativo
                        </a>

                        <a
                            href="../index.php"
                            class="btn btn-secundario"
                        >
                            Voltar para a loja
                        </a>

                        <a
                            href="../logout.php"
                            class="btn btn-secundario"
                        >
                            Sair
                        </a>

                    </div>

                </section>

            </main>

        </div>


    <?php else: ?>

        <!-- =====================================================
             ÁREA DO CLIENTE
             ===================================================== -->

        <header class="topo">

            <div>

                <h1>
                    Natália Milanese Joias
                </h1>

                <p>
                    Área do cliente
                </p>

            </div>

            <div>

                Olá,
                <?= htmlspecialchars($usuario['nome']) ?>

            </div>

        </header>


        <div class="layout">

            <aside class="menu">

                <h2>
                    Minha conta
                </h2>

                <a href="../index.php">
                    Início
                </a>

                <a href="../produtos.php">
                    Produtos
                </a>

                <a href="minha_conta.php">
                    Minha conta
                </a>

                <a href="meus_pedidos.php">
                    Meus pedidos
                </a>

                <a href="../carrinho.php">
                    Carrinho
                </a>

                <a href="../logout.php">
                    Sair
                </a>

            </aside>


            <main class="conteudo">

                <div class="acoes-container">

                    <div>

                        <h2>
                            Minha Conta
                        </h2>

                        <p>
                            Consulte seus dados cadastrados.
                        </p>

                    </div>

                </div>


                <!-- DADOS DO CLIENTE -->

                <section class="card">

                    <h3>
                        Meus dados
                    </h3>


                    <div class="dados-conta">

                        <div class="dado">

                            <span>
                                Nome
                            </span>

                            <strong>
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                CPF
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    formatarCpf($usuario['cpf'])
                                ) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Sexo
                            </span>

                            <strong>
                                <?= $usuario['sexo']
                                    ? htmlspecialchars($usuario['sexo'])
                                    : 'Não informado'
                                ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                E-mail
                            </span>

                            <strong>
                                <?= htmlspecialchars($usuario['email']) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Telefone
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    formatarTelefone($usuario['telefone'])
                                ) ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Estado
                            </span>

                            <strong>
                                <?= $usuario['estado']
                                    ? htmlspecialchars($usuario['estado'])
                                    : 'Não informado'
                                ?>
                            </strong>

                        </div>


                        <div class="dado">

                            <span>
                                Cidade
                            </span>

                            <strong>
                                <?= $usuario['cidade']
                                    ? htmlspecialchars($usuario['cidade'])
                                    : 'Não informado'
                                ?>
                            </strong>

                        </div>

                    </div>

                </section>


                <!-- ÁREA DO CLIENTE -->

                <section class="card">

                    <h3>
                        Minha área
                    </h3>

                    <p>
                        Acesse suas informações e acompanhe seus pedidos.
                    </p>

                    <div class="form-acoes">

                        <a
                            href="meus_pedidos.php"
                            class="btn"
                        >
                            Meus pedidos
                        </a>

                        <a
                            href="../index.php"
                            class="btn btn-secundario"
                        >
                            Voltar para a loja
                        </a>

                        <a
                            href="../logout.php"
                            class="btn btn-secundario"
                        >
                            Sair
                        </a>

                    </div>

                </section>

            </main>

        </div>

    <?php endif; ?>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>


</body>

</html>