<?php

$router->adicionar('GET', '/', 'controllerAuth@loginForm');
$router->adicionar('GET', '/login', 'controllerAuth@loginForm');
$router->adicionar('POST', '/login', 'controllerAuth@login');
$router->adicionar('POST', '/logout', 'controllerAuth@logout');

$router->adicionar("GET", "/cadastro/index", "controllerCadastro@index");