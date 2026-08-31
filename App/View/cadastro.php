<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/desafio-legado/Public/Css/style.css">
</head>

<body>
    <div class="fundoTelaCRUD">
        <form class="containerCRUD">
            <h1 class="tituloModalCRUD">Cadastro de Usuario</h1>
            <div class="containerInput">
                <label class="labelCRUD" for="nome">Nome:</label>
                <input class="inputLoginCRUD" type="nome" placeholder="Nome">
                <label class="labelCRUD" for="email">Email:</label>
                <input class="inputLoginCRUD" type="email" placeholder="Email">
                <label class="labelCRUD" for="senha">Senha:</label>
                <input class="inputLoginCRUD" type="senha" placeholder="Senha">
                <label class="labelCRUDCPF" for="cpf">Cpf:</label>
                <input class="inputLoginCRUD" type="cpf" placeholder="cpf">
                <button class="botaoLoginCRUD" type="submit">Cadastrar</button>

            </div>
        </form>
        <form action="<?= BASE_URL ?>logout" method="POST">
            <button class="botaoLoginCRUD" type="submit">logout</button>
        </form>
    </div>
</body>

</html>

<!-- nome, email, senha, cpf, -->