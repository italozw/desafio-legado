<?php

namespace Core;

use PDO;

abstract class model{
    public static function pegarBanco(): PDO {
        return bancoDados::conectar();
    }
}