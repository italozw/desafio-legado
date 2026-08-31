<?php
use Core\auth;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/desafio-legado/Public/Css/style.css">
</head>

<body>
    <section class="fundoLoginCRUD">
        <div class="caixaEsquerdaCRUD">
            <h1 class="tituloLoginCRUD">Boas Vindas</h1>
            <span class="tituloLoginCRUD">Faça login pra entrar!</span>
        </div>
        <form action="<?= BASE_URL ?>login" method="POST" class="caixaDireitaCRUD">
            <input type="hidden" name="csrfToken"
                value="<?= htmlspecialchars(auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <h1 tituloLoginCRUD>Login</h1>
            <div class="caixaLoginCRUD">
                <input class="inputLoginCRUD" name="email" type="email" id="emailLogin" placeholder="Digite seu email">
                <input class="inputLoginCRUD" name="senha" type="password" id="senhaLogin"
                    placeholder="Digite sua senha">
                <button class="botaoLoginCRUD" type="submit">Enviar</button>
            </div>
        </form>
    </section>
</body>

</html>