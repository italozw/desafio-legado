<?php

namespace Core;

use Throwable;

class auth {
    public static function configJWT() : array {
        $config = require __DIR__ . '/../../Config/aplicativo.php';

        return $config['jwt'];
    }

    public static function removerCookie() : void {
        $config = self::configJWT();

        setcookie($config['cookie_name'], '', [
            'expires'   => time() - 3600,
            'path'      => BASE_URL,
            'secure'    => (bool) $config['cookie_secure'],
            'httponly'  => true,
            'samesite'  => 'Lax'
        ]);
        unset($_COOKIE[$config['cookie_name']]);
    }
    public static function login(array $usuario, bool $lembrarAcesso = false) : void {
        $config  = self::configJWT();
        $agora   = time();
        
        $ttl = $lembrarAcesso ? 60 * 60 * 24 * 30 : (int) $config['ttl'];

        $payload = [
            'sub'       => (int) $usuario['id'],
            'email'     => $usuario['email'],
            'perfil'     => $usuario['perfil'],
            'permissao' => $usuario['permissao'] ?? [],
            'iat'       => $agora,
            'exp'       => $agora + $ttl
            ];

        $token = jwtToken::encode($payload, $config['secret']);
            
        setcookie($config['cookie_name'], $token, [
            'expires'   => $payload['exp'],
            'path'      => BASE_URL,
            'secure'    => (bool) $config['cookie_secure'],
            'httponly'  => true,
            'samesite'  => 'Lax'
        ]);
                
        session_regenerate_id(true);
    }

    public static function usuario() : ?array {
        $config = self::configJWT();
        $token  = $_COOKIE[$config['cookie_name']] ?? null;

        if(!$token) {
            return null;
        }

        try {
            $payload = jwtToken::decode($token, $config['secret']);
            
            return[
                'id'        => (int) $payload['sub'],
                'email'     => $payload['email'] ?? '',
                'perfil'     => $payload['perfil'] ?? '',
                'permissao' => $payload['permissao'] ?? ''
            ];
        } catch(Throwable $erro) {
            self::removerCookie();
            return null;
        }
    }
    
    public static function id(): ?int {
        return self::usuario()['id'] ?? null;
    }

    public static function checar() : bool {
        return self::usuario() !== null;
    }
    
    public static function convidado() : bool {
        return !self::checar();
    }
    
    public static function pode(string $permissao) : bool {
        $usuario = self::usuario();

        return $usuario !== null && in_array(
            $permissao,
            $usuario['permissao'],
            true
        );
    }

    public static function temCargo(string $cargo) : bool {
        $usuario = self::usuario();

        return $usuario !== null && in_array(
            $cargo,
            $usuario['cargo'],
            true
        );
    }
    
    public static function requerLogin() : void {
        if(!self::checar()) {
            self::flash('erro', 'Faz login ae');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }
    
    public static function requerPermissao(string $permissao) : void {
        self::requerLogin();

        if(!self::pode($permissao)) {
            header('Location: ' . BASE_URL . '403');
            exit;
        }
    }
    public static function logout() : void {
        self::removerCookie();
        session_regenerate_id(true);
        }
    public static function flash(string $tipo, string $mensagem) : void {
        $_SESSION['flash'][$tipo] = $mensagem;
    }

    public static function pegarFlash(string $tipo) : ?string {
        $mensagem = $_SESSION['flash'][$tipo] ?? [];
        unset($_SESSION['flash'][$tipo]);
        return is_string($mensagem) ? $mensagem : null;
    }

    public static function csrfToken() : string {
        if(empty($_SESSION['csrfToken'])){
            $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrfToken'];
    }
    public static function validarCsrf() : string {
        if(empty($_SESSION['csrfToken'])) {
            $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrfToken'];
    }
}
