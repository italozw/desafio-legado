<?php

namespace Core;

class validador
{
    public static function required(string $valor): bool
    {
        return trim((string) $valor) !== '';
    }

    public static function email(string $valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function cpf(string $valor): bool
    {

        $cpfLimpo = str_replace(".", "", $valor);
        [$novePrimeiros, $doisUltimos] = explode("-", $cpfLimpo);
        $numeroUnico = count_chars($novePrimeiros, 1);

        if (count($numeroUnico) === 1) {
            exit("CPF inválido");
        }

        (int) $multiplicador = 10;
        $total = 0;

        foreach (str_split($novePrimeiros) as $num) {
            (int) $total += $multiplicador * (int) $num;
            $multiplicador -= 1;
        }

        $total = $total % 11;
        $primeiroVerificador = $total < 2 ? 0 : 11 - $total;
        $total = 0;
        $multiplicador = 11;

        foreach (str_split($novePrimeiros) as $num) {
            (int) $total += $multiplicador * (int) $num;
            $multiplicador -= 1;
        }

        $total += $multiplicador * (int) $primeiroVerificador;
        $total = $total % 11;
        $segundoVerificador = $total < 2 ? 0 : 11 - $total;

        if ((int) $doisUltimos[0] !== $primeiroVerificador && (int) $doisUltimos[1] !== $segundoVerificador) {
            exit('CPF inválido');
        }
        return true;
    }
    public static function validarCnpj(string $cnpj): bool
    {
        $limpo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $cnpj));

        if (!preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $limpo)) {
            return false;
        }

        if (preg_match('/^(.)\1{13}$/', $limpo)) {
            return false;
        }

        return self::calcularDvCnpj(substr($limpo, 0, 12)) === substr($limpo, 12, 2);
    }

    private static function calcularDvCnpj(string $base12): string
    {
        $dvs = '';

        $tabelas = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        foreach ($tabelas as $pesos) {
            $soma  = 0;
            $chars = str_split($base12 . $dvs);

            foreach ($chars as $i => $c) {
                $soma += (ord($c) - 48) * $pesos[$i];
            }

            $resto = $soma % 11;
            $dvs  .= (string) ($resto < 2 ? 0 : 11 - $resto);
        }

        return $dvs;
    }
    public static function validarTel(string $tel): bool{
        if (strlen($tel) < 10){
            return false;
        }
        return true;
    }
}

