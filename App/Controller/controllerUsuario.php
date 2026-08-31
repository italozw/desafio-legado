<?php

namespace Controller;

use Models\usuario;

class controllerUsuario{
    public function deletar(){
        $id = $_GET['id'];
        $nomeTabela = $_GET['nomeTabela'];
        usuario::deletar($nomeTabela, $id); 
    }

    public function salvar(){
        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'senha' => $_POST['senha'],
            'cpf' => $_POST['cpf']
        ];
        usuario::cadastro($dados);
    }
}
