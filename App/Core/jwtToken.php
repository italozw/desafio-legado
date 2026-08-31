<?php

namespace Core;

use RuntimeException;

class jwtToken{
    public static function encode(array $payload, string $segredo) : string{
        if(strlen($segredo) <32){
            throw new RuntimeException('Chave menor que 32');
        }

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $header64   = self::base64UrlEncode(json_encode($header));
        $payload64  = self::base64UrlEncode(json_encode($payload));
        $conteudo   = $header64 . '.' . $payload64;
        $assinatura = hash_hmac('sha256', $conteudo, $segredo, true);

        return $conteudo . '.' . self::base64UrlEncode($assinatura);
    }

    public static function decode(string $token, string $segredo) : array{
        if(strlen($segredo) < 32){
            throw new RuntimeException('Chave menor que 32');
        }

        $partes = explode('.', $token);

        if(count($partes) !== 3){
            throw new RuntimeException('Token inválido');
        }

        [
            $header64,
            $payload64,
            $assinatura64
        ] = $partes;

        $header     = json_decode(self::base64UrlDecode($header64), true);
        $payload    = json_decode(self::base64UrlDecode($payload64), true);

        if(!is_array($header) || !is_array($payload)){
            throw new RuntimeException('Token inválido');
        }

        if(($header['alg'] ?? '') !== 'HS256'){
            throw new RuntimeException('Algoritmo inválido');    
        }

        $conteudo           = $header64 . '.' . $payload64;
        $assinaturaEsperada = hash_hmac('sha256', $conteudo, $segredo, true);
        $assinaturaRecebida = self::base64UrlDecode($assinatura64);

        if(!hash_equals($assinaturaEsperada, $assinaturaRecebida)){
            throw new RuntimeException('Assinatura inválida');
        }
        
        if(!isset($payload['exp']) || time() >= (int) $payload['exp']){
            throw new RuntimeException('Usuario inválido');
        }

        return $payload;
    }

    public static function base64UrlEncode(string $valor) : string{
        return rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $valor) : string{
        $valor = strtr($valor, '-_', '+/');
        $resto = strlen($valor) % 4;

        if($resto !== 0){
            $valor .= str_repeat('=', 4 - $resto);
        }

        $resultado = base64_decode($valor, true);

        if($resultado === false){
            throw new RuntimeException('Token inválido');
        }

        return $resultado;
    }
}