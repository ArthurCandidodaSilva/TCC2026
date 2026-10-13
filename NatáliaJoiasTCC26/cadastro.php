<?php

require_once __DIR__ . '/config/database.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $sexo = $_POST['sexo'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
    $estado = strtoupper(trim($_POST['estado'] ?? ''));
    $cidade = trim($_POST['cidade'] ?? '');

    // Verifica campos obrigatórios
    if (
        $nome === '' ||
        $cpf === '' ||
        $email === '' ||
        $senha === '' ||
        $confirmar_senha === '' ||
        $estado === '' ||
        $cidade === ''
    ) {

        $erro = 'Preencha todos os campos obrigatórios.';

    // Verifica CPF
    } elseif (strlen($cpf) !== 11) {

        $erro = 'Digite um CPF válido.';

    // Verifica estado
    } elseif (!preg_match('/^[A-Z]{2}$/', $estado)) {

        $erro = 'Selecione um estado válido.';

    // Verifica cidade
    } elseif (strlen($cidade) < 2) {

        $erro = 'Selecione uma cidade válida.';

    // Verifica e-mail
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = 'Digite um e-mail válido.';

    // Verifica senha
    } elseif (strlen($senha) < 6) {

        $erro = 'A senha deve possuir pelo menos 6 caracteres.';

    // Confirmação da senha
    } elseif ($senha !== $confirmar_senha) {

        $erro = 'As senhas não coincidem.';

    } else {

        // Verifica se CPF ou e-mail já estão cadastrados
        $sql = "SELECT id
                FROM usuarios
                WHERE cpf = :cpf
                   OR email = :email";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':cpf' => $cpf,
            ':email' => $email
        ]);

        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {

            $erro = 'CPF ou e-mail já cadastrado.';

        } else {

            // Cria o hash da senha
            $senhaHash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            // Cadastra como cliente
            $sql = "INSERT INTO usuarios
                    (
                        nome,
                        cpf,
                        sexo,
                        email,
                        senha,
                        telefone,
                        estado,
                        cidade,
                        tipo_usuario
                    )
                    VALUES
                    (
                        :nome,
                        :cpf,
                        :sexo,
                        :email,
                        :senha,
                        :telefone,
                        :estado,
                        :cidade,
                        'cliente'
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nome' => $nome,
                ':cpf' => $cpf,
                ':sexo' => $sexo !== '' ? $sexo : null,
                ':email' => $email,
                ':senha' => $senhaHash,
                ':telefone' => $telefone !== '' ? $telefone : null,
                ':estado' => $estado,
                ':cidade' => $cidade
            ]);

            $sucesso = 'Cadastro realizado com sucesso! Agora você pode fazer login.';
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

    <title>
        Cadastro - Natália Milanese Joias
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body class="pagina-autenticacao">

    <header class="site-header">

        <div class="header-container">

            <a
                href="index.php"
                class="logo"
            >
                Natália Milanese Joias
            </a>

            <nav class="site-nav">

                <a href="index.php">
                    Início
                </a>

                <a href="produtos.php">
                    Produtos
                </a>

                <a href="login.php">
                    Entrar
                </a>

                <a href="carrinho.php">
                    Carrinho
                </a>

            </nav>

        </div>

    </header>


    <main>

        <section class="autenticacao cadastro">

            <div class="autenticacao-card cadastro-card">

                <div class="autenticacao-cabecalho">

                    <p class="autenticacao-subtitulo">
                        Crie sua conta
                    </p>

                    <h1>
                        Cadastro
                    </h1>

                    <p>
                        Crie sua conta para comprar na
                        Natália Milanese Joias.
                    </p>

                </div>


                <?php if ($erro): ?>

                    <div class="mensagem-erro">
                        <?= htmlspecialchars($erro) ?>
                    </div>

                <?php endif; ?>


                <?php if ($sucesso): ?>

                    <div class="mensagem-sucesso">
                        <?= htmlspecialchars($sucesso) ?>
                    </div>

                    <div class="cadastro-sucesso">

                        <a
                            href="login.php"
                            class="botao-autenticacao"
                        >
                            Ir para o login
                        </a>

                    </div>

                <?php else: ?>


                    <form
                        method="POST"
                        class="form-autenticacao"
                    >

                        <div class="cadastro-secao">

                            <div class="cadastro-secao-titulo">

                                <span>
                                    01
                                </span>

                                <h2>
                                    Dados pessoais
                                </h2>

                            </div>


                            <div class="cadastro-grid">

                                <div class="form-grupo-publico cadastro-grupo-grande">

                                    <label for="nome">
                                        Nome completo
                                    </label>

                                    <input
                                        type="text"
                                        id="nome"
                                        name="nome"
                                        value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                        placeholder="Digite seu nome completo"
                                        required
                                    >

                                </div>


                                <div class="form-grupo-publico">

                                    <label for="cpf">
                                        CPF
                                    </label>

                                    <input
                                        type="text"
                                        id="cpf"
                                        name="cpf"
                                        maxlength="14"
                                        placeholder="000.000.000-00"
                                        value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>"
                                        required
                                    >

                                </div>


                                <div class="form-grupo-publico">

                                    <label for="sexo">
                                        Sexo
                                    </label>

                                    <select
                                        id="sexo"
                                        name="sexo"
                                    >

                                        <option value="">
                                            Selecione
                                        </option>

                                        <option
                                            value="Masculino"
                                            <?= ($_POST['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>
                                        >
                                            Masculino
                                        </option>

                                        <option
                                            value="Feminino"
                                            <?= ($_POST['sexo'] ?? '') === 'Feminino' ? 'selected' : '' ?>
                                        >
                                            Feminino
                                        </option>

                                        <option
                                            value="Outro"
                                            <?= ($_POST['sexo'] ?? '') === 'Outro' ? 'selected' : '' ?>
                                        >
                                            Outro
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <div class="cadastro-secao">

                            <div class="cadastro-secao-titulo">

                                <span>
                                    02
                                </span>

                                <h2>
                                    Contato e localização
                                </h2>

                            </div>


                            <div class="cadastro-grid">

                                <div class="form-grupo-publico cadastro-grupo-grande">

                                    <label for="email">
                                        E-mail
                                    </label>

                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                        placeholder="seuemail@exemplo.com"
                                        required
                                    >

                                </div>


                                <div class="form-grupo-publico">

                                    <label for="telefone">
                                        Telefone
                                    </label>

                                    <input
                                        type="text"
                                        id="telefone"
                                        name="telefone"
                                        maxlength="15"
                                        placeholder="(00) 00000-0000"
                                        value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"
                                    >

                                </div>


                                <div class="form-grupo-publico">

                                    <label for="estado">
                                        Estado
                                    </label>

                                    <select
                                        id="estado"
                                        name="estado"
                                        required
                                    >

                                        <option value="">
                                            Carregando estados...
                                        </option>

                                    </select>

                                </div>


                                <div class="form-grupo-publico">

                                    <label for="cidade">
                                        Cidade
                                    </label>

                                    <select
                                        id="cidade"
                                        name="cidade"
                                        required
                                        disabled
                                    >

                                        <option value="">
                                            Selecione primeiro o estado
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <div class="cadastro-secao">

                            <div class="cadastro-secao-titulo">

                                <span>
                                    03
                                </span>

                                <h2>
                                    Acesso à conta
                                </h2>

                            </div>


                            <div class="cadastro-grid">

                                <div class="form-grupo-publico">

                                    <label for="senha">
                                        Senha
                                    </label>

                                    <input
                                        type="password"
                                        id="senha"
                                        name="senha"
                                        placeholder="Crie uma senha"
                                        required
                                    >

                                    <small>
                                        A senha deve possuir pelo menos 6 caracteres.
                                    </small>

                                </div>


                                <div class="form-grupo-publico">

                                    <label for="confirmar_senha">
                                        Confirmar senha
                                    </label>

                                    <input
                                        type="password"
                                        id="confirmar_senha"
                                        name="confirmar_senha"
                                        placeholder="Digite a senha novamente"
                                        required
                                    >

                                </div>

                            </div>

                        </div>


                        <button
                            type="submit"
                            class="botao-autenticacao"
                        >
                            Criar minha conta
                        </button>

                    </form>


                    <div class="autenticacao-rodape">

                        <p>
                            Já possui uma conta?
                        </p>

                        <a href="login.php">
                            Faça login
                        </a>

                    </div>


                <?php endif; ?>

            </div>

        </section>

    </main>


    <script>

        const estadoSelect =
            document.getElementById('estado');

        const cidadeSelect =
            document.getElementById('cidade');

        const estadoSelecionado =
            <?= json_encode($_POST['estado'] ?? '') ?>;

        const cidadeSelecionada =
            <?= json_encode($_POST['cidade'] ?? '') ?>;


        /*
        |--------------------------------------------------------------------------
        | Carrega os estados
        |--------------------------------------------------------------------------
        */

        fetch(
            'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome'
        )

        .then(response => {

            if (!response.ok) {

                throw new Error(
                    'Não foi possível carregar os estados.'
                );

            }

            return response.json();

        })

        .then(estados => {

            estadoSelect.innerHTML = '';

            const primeiraOpcao =
                document.createElement('option');

            primeiraOpcao.value = '';

            primeiraOpcao.textContent =
                'Selecione o estado';

            estadoSelect.appendChild(
                primeiraOpcao
            );


            estados.forEach(estado => {

                const option =
                    document.createElement('option');

                option.value =
                    estado.sigla;

                option.textContent =
                    `${estado.nome} - ${estado.sigla}`;

                if (
                    estado.sigla === estadoSelecionado
                ) {

                    option.selected = true;

                }

                estadoSelect.appendChild(
                    option
                );

            });


            if (estadoSelecionado !== '') {

                carregarCidades(
                    estadoSelecionado,
                    cidadeSelecionada
                );

            }

        })

        .catch(error => {

            console.error(error);

            estadoSelect.innerHTML = '';

            const option =
                document.createElement('option');

            option.value = '';

            option.textContent =
                'Erro ao carregar estados';

            estadoSelect.appendChild(
                option
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Carrega as cidades do estado selecionado
        |--------------------------------------------------------------------------
        */

        function carregarCidades(
            uf,
            cidadeSelecionada = ''
        ) {

            cidadeSelect.disabled = true;

            cidadeSelect.innerHTML = '';

            const carregando =
                document.createElement('option');

            carregando.value = '';

            carregando.textContent =
                'Carregando cidades...';

            cidadeSelect.appendChild(
                carregando
            );


            fetch(
                `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios?orderBy=nome`
            )

            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        'Não foi possível carregar as cidades.'
                    );

                }

                return response.json();

            })

            .then(cidades => {

                cidadeSelect.innerHTML = '';

                const primeiraOpcao =
                    document.createElement('option');

                primeiraOpcao.value = '';

                primeiraOpcao.textContent =
                    'Selecione a cidade';

                cidadeSelect.appendChild(
                    primeiraOpcao
                );


                cidades.forEach(cidade => {

                    const option =
                        document.createElement('option');

                    option.value =
                        cidade.nome;

                    option.textContent =
                        cidade.nome;

                    if (
                        cidade.nome === cidadeSelecionada
                    ) {

                        option.selected = true;

                    }

                    cidadeSelect.appendChild(
                        option
                    );

                });


                cidadeSelect.disabled = false;

            })

            .catch(error => {

                console.error(error);

                cidadeSelect.innerHTML = '';

                const option =
                    document.createElement('option');

                option.value = '';

                option.textContent =
                    'Erro ao carregar cidades';

                cidadeSelect.appendChild(
                    option
                );

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Quando o estado mudar
        |--------------------------------------------------------------------------
        */

        estadoSelect.addEventListener(
            'change',
            function () {

                const uf = this.value;

                if (uf === '') {

                    cidadeSelect.innerHTML = '';

                    const option =
                        document.createElement('option');

                    option.value = '';

                    option.textContent =
                        'Selecione primeiro o estado';

                    cidadeSelect.appendChild(
                        option
                    );

                    cidadeSelect.disabled = true;

                    return;

                }

                carregarCidades(uf);

            }
        );

    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>