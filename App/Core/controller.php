<?php

namespace Core;

class controller{

    protected function mostrarTela(string $name, array $data = []) : void {
        extract($data);

        $viewFile = __DIR__ . '/../View/' . $name . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);
            exit('Página não existe');
        }

        require $viewFile;
    }
    protected function redirecionar(string $url){
        $urlRedirecionamento = (strpos($url, "http") === 0) ? $url : BASE_URL . ltrim($url, "/");
        header("Location: " . $urlRedirecionamento);
        exit;
    }
}