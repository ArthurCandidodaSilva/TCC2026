<?php

session_start();

require_once __DIR__ . '/config/database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Verifica se os campos foram preenchidos
    if ($email === '' || $senha === '') {

        $erro = 'Preencha o e-mail e a senha.';

    } else {

        // Busca o usuário pelo e-mail
        $sql = "SELECT *
                FROM usuarios
                WHERE email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário existe e se a senha está correta
        if (
            $usuario &&
            password_verify($senha, $usuario['senha'])
        ) {

            // Regenera o ID da sessão
            session_regenerate_id(true);

            // Guarda informações do usuário na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            // Redireciona conforme o tipo de usuário
            if ($usuario['tipo_usuario'] === 'admin') {

                header('Location: admin/index.php');
                exit;

            } else {

                header('Location: index.php');
                exit;
            }

        } else {

            $erro = 'E-mail ou senha incorretos.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Entrar - Natália Milanese Joias</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="pagina-autenticacao">


    <!-- ==============================
         HEADER
         ============================== -->

    <header class="site-header">

        <div class="header-container">

            <a href="index.php" class="logo">
                Natália Milanese Joias
            </a>

            <nav class="site-nav">

                <a href="index.php">
                    Início
                </a>

                <a href="produtos.php">
                    Produtos
                </a>

                <a href="cadastro.php">
                    Criar conta
                </a>

            </nav>

        </div>

    </header>


    <!-- ==============================
         LOGIN
         ============================== -->

    <main class="autenticacao">

        <div class="autenticacao-card">


            <div class="autenticacao-cabecalho">

                <p class="autenticacao-subtitulo">
                    Bem-vinda
                </p>

                <h1>
                    Entre na sua conta
                </h1>

                <p>
                    Acesse sua conta para continuar.
                </p>

            </div>


            <?php if ($erro): ?>

                <div class="mensagem-erro">

                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>


            <form method="POST" class="form-autenticacao">


                <div class="form-grupo-publico">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="Digite seu e-mail"
                        autocomplete="email"
                        required
                    >

                </div>


                <div class="form-grupo-publico">

                    <label for="senha">
                        Senha
                    </label>

                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="botao-autenticacao"
                >
                    Entrar
                </button>


            </form>


            <div class="autenticacao-rodape">

                <p>
                    Ainda não possui uma conta?
                </p>

                <a href="cadastro.php">
                    Criar uma conta
                </a>

            </div>


        </div>

    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>