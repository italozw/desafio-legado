<?php

namespace Models;

use Core\bancoDados;
use Core\model;
use PDO;

class usuario extends model
{
    public static function cadastro(array $dados)
    {
        $db = bancoDados::conectar();
        $stmt = $db->prepare('INSERT INTO usuario (nome, email, senha, cpf) VALUES (:nome, :email:, :senha, :cpf)');
        $stmt->execute([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => $dados['senha'],
            'cpf' => $dados['cpf']
        ]);
        return $db->lastInsertId();
    }
    public static function listar(string $nomeTabela)
    {
        $db = bancoDados::conectar();
        $sql = "SELECT * FROM `$nomeTabela`";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
    public static function atualizar(int $id, string $nomeTabela,array $dados)
    {
        $db = bancoDados::conectar();
        $sql = "UPDATE :nomeTabela SET nome = :nome, email = :email, cpf = :cpf WHERE id = :id";
        $parametros = [
            'nomeTabela' => $nomeTabela,
            'id' => $id,
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'cpf' => $dados['cpf']
        ];
        $stmt = $db->prepare($sql);
        return $stmt->execute($parametros);
    }
    public static function deletar(string $nomeTabela,int $id)
    {
        $db = bancoDados::conectar();
        $stmt = $db->prepare('DELETE FROM :nomeTabela WHERE id = :id');
        $stmt->bindValue(':nomeTabela', $nomeTabela);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public static function buscarEmail(string $email) : ?array{
        $stmt = parent::pegarBanco()->prepare('SELECT id, email, senha FROM usuario WHERE email = :e LIMIT 1');
        $stmt->execute(['e' => $email]);
        $usuario = $stmt->fetch();

        if(!$usuario){
            return null;
        }

        $usuario['perfil']      = self::buscarPerfil((int) $usuario['id']);
        $usuario['permissao']   = self::buscarPermissao((int) $usuario['id']);

        return $usuario;
    }

    public static function buscarPerfil(int $id) : array{
        $stmt = parent::pegarBanco()->prepare('SELECT p.nome FROM perfil p INNER JOIN usuario u ON u.id_perfil = p.id WHERE u.id = :i');
        $stmt->execute(['i' => $id]);

        return $stmt->fetch();
    }

    public static function buscarpermissao(int $id) : array {
        $stmt = parent::pegarBanco()->prepare('SELECT p.nome FROM usuario u INNER JOIN perfil_permissao pp ON u.id_perfil = pp.id_perfil INNER JOIN permissao p ON pp.id_permissao = p.id WHERE u.id = :i');
        $stmt->execute(['i' => $id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}