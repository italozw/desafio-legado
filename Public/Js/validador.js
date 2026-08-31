/*!
 * Validações de documento no navegador, espelhando App/Core/validador.php.
 * O servidor continua sendo quem decide — isto é só o aviso antecipado na tela.
 *
 * USO:
 *   Validador.cnpj('12.345.678/0001-95')   -> true | false
 *
 * Aceita o valor com ou sem máscara: separadores são descartados antes da conta.
 */
(function () {
    'use strict';

    if (window.Validador) return; // evita registrar duas vezes

    // Pesos do módulo 11: a segunda passada inclui o primeiro dígito verificador.
    var PESOS_CNPJ = [
        [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
    ];

    function limpar(valor) {
        return String(valor === null || valor === undefined ? '' : valor)
            .replace(/[^A-Za-z0-9]/g, '')
            .toUpperCase();
    }

    /**
     * Os dois dígitos verificadores da base de 12 caracteres.
     *
     * O CNPJ alfanumérico entra na conta pelo código ASCII menos 48, então
     * '0'..'9' viram 0..9 e 'A'..'Z' viram 17..42 — é a regra da Receita.
     */
    function calcularDvCnpj(base12) {
        var dvs = '';

        for (var t = 0; t < PESOS_CNPJ.length; t++) {
            var pesos = PESOS_CNPJ[t];
            var chars = base12 + dvs;
            var soma = 0;

            for (var i = 0; i < chars.length; i++) {
                soma += (chars.charCodeAt(i) - 48) * pesos[i];
            }

            var resto = soma % 11;

            dvs += String(resto < 2 ? 0 : 11 - resto);
        }

        return dvs;
    }

    function cnpj(valor) {
        var limpo = limpar(valor);

        // 12 caracteres de base (alfanuméricos) + 2 dígitos verificadores
        if (!/^[A-Z0-9]{12}[0-9]{2}$/.test(limpo)) {
            return false;
        }

        // sequência de um caractere só passa na conta, mas não é CNPJ
        if (/^(.)\1{13}$/.test(limpo)) {
            return false;
        }

        return calcularDvCnpj(limpo.slice(0, 12)) === limpo.slice(12);
    }

    window.Validador = {
        cnpj: cnpj
    };
})();
