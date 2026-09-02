<?php

namespace Controller;

use Core\controller;
use Core\auth;
use Core\validador;
use Core\hashSenha;
use Models\usuario;

class controllerAuth extends controller{
    public function loginForm() : void {
        if (auth::checar()) {
            $this->redirecionar('/cadastro/index');
        }

        $this->mostrarTela('/login', [
            'erro'      => auth::pegarFlash('erro'),
            'sucesso'   => auth::pegarFlash('sucesso')
        ]);
    }

    public function login() : void {
        if(!auth::validarCsrf($_POST['csrfToken'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if(!validador::required($email) || !validador::required($senha)) {
            auth::flash('erro', 'Manda email e senha');
            $this->redirecionar('/login');
        }

        if(!validador::email($email)) {
            auth::flash('erro', 'Email inválido');
            $this->redirecionar('/login');
        }

        $usuario = usuario::buscarEmail($email);

        if(!$usuario || !hashSenha::verificar($senha, $usuario['senha'])) {
            auth::flash('erro', 'Email ou senha inválidos');
            $this->redirecionar('/login');
        }

        $lembrarAcesso = isset($_POST['lembrarAcesso']);
        auth::login($usuario, $lembrarAcesso);
        auth::flash('sucesso', 'login com sucesso');
        $this->redirecionar('/cadastro/index');
    }

    public function logout() : void{
        auth::requerLogin();

        if(!auth::validarCsrf($_POST['csrfToken'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        auth::logout();
        auth::flash('sucesso', 'logout realizado');
        $this->redirecionar('/login');
    }
}