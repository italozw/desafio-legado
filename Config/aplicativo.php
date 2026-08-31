<?php
return[
    
    'app_name'          => 'legado',
    'base_folder'       => '/desafio-legado/',
    'base_js'           => '/desafio-legado/Public/Js/',
    'db' => [
        'host'          => 'localhost',
        'dbname'        => 'legado',
        'usuario'       => 'root',
        'senha'         => ''
    ],
    'jwt' => [
        'secret'        => 'nZK9TFFBEjSEllKj1U3Js7EE1IxJHDId',
        'ttl'           => 1800,
        'cookie_name'   => 'legado',
        'cookie_secure' => false
    ]
];