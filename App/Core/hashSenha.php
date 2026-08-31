<?php

namespace Core;

class hashSenha {
    private const ITERACOES = 210000;

    public static function criar(string $senha) : string{ 
        $salt = bin2hex(random_bytes(16));

        $hash = hash_pbkdf2(
            'sha256',
            $senha,
            $salt,
            self::ITERACOES,
            64
        );

        return self::ITERACOES . '$' . $salt . '$' . $hash;
    }

    public static function verificar(string $senha, string $hashSalvo) : bool{
        $partes = explode('$', $hashSalvo);

        if(count($partes) !== 3){
            return false;
        }

        [
            $iteracoes,
            $salt,
            $hashEsperado
        ] = $partes;

        $hashCalculado = hash_pbkdf2(
            'sha256',
            $senha,
            $salt,
            (int) $iteracoes,
            64
        );

        return hash_equals($hashEsperado, $hashCalculado);
    }
}