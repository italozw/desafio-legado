/*!
 * USO (só o atributo, nada de JS):
 *   <input type="text" inputmode="numeric" data-mascara="cpf">
 *   <input type="text" inputmode="numeric" data-mascara="telefone">
 *   <input type="text" inputmode="numeric" data-mascara="cep">
 *   <input type="text" data-mascara="cnpj">
 *   <input type="text" inputmode="numeric" data-mascara="data">
 *   <input type="text" data-mascara="##/##/####">   <!-- modelo avulso -->
 *
 * ATENÇÃO: type="text" é obrigatório. Em type="number" ou type="email"
 * o método setSelectionRange lança InvalidStateError e a máscara não funciona.
 * Use inputmode="numeric" para trazer o teclado numérico no celular.
 *
 * API GLOBAL (opcional, para pegar o valor cru antes de enviar):
 *   Mascara.remover('#cpf')            -> "12345678901"
 *   Mascara.aplicarEm(valor, 'cnpj')   -> "12.ABC.345/0001-99"
 *   Mascara.formatar(document)         -> reformata um trecho manualmente
 *
 * MODELOS PRONTOS: cpf, cnpj, cep, telefone, data, hora
 */
(function () {
  'use strict';

  if (window.Mascara) return; // evita registrar duas vezes

  var MARCADORES = {
    '#': /[0-9]/,      // dígito
    '@': /[0-9A-Z]/,   // alfanumérico
    'A': /[A-Z]/       // letra
  };

  var MODELOS = {
    cpf:    '###.###.###-##',    // CNPJ alfanumérico
    cnpj:   '@@.@@@.@@@/@@@@-##',
    cep:    '#####-###',
    data:   '##/##/####',
    hora:   '##:##'
  };

  var ATRIBUTO = 'data-mascara';
  var SELETOR = '[' + ATRIBUTO + ']';


  /** Resolve o modelo. `telefone` muda conforme a quantidade de dígitos. */
  function resolverModelo(nome, quantidade) {
    if (nome === 'telefone') {
      return quantidade > 10 ? '(##) #####-####' : '(##) ####-####';
    }
    return MODELOS[nome] || nome; // valor desconhecido = modelo literal
  }

  /** Descobre, a partir dos marcadores do modelo, o que conta como conteúdo. */
  function regraSignificativa(modelo) {
    var aceitaDigito = false;
    var aceitaLetra = false;

    for (var k = 0; k < modelo.length; k++) {
      var marcador = MARCADORES[modelo[k]];
      if (!marcador) continue;                    // separador literal
      if (marcador.test('0')) aceitaDigito = true;
      if (marcador.test('A')) aceitaLetra = true;
    }

    if (aceitaLetra && aceitaDigito) return /[0-9A-Za-z]/;
    if (aceitaLetra) return /[A-Za-z]/;
    return /[0-9]/;
  }

  function encaixar(caracteres, modelo) {
    var saida = '';
    var i = 0;
    var paradas = [];
    var indiceOrigem = [];

    for (var k = 0; k < modelo.length; k++) {
      var simbolo = modelo[k];
      var marcador = MARCADORES[simbolo];

      if (!marcador) {                          // separador
        if (i >= caracteres.length) break;      // não deixa separador órfão
        saida += simbolo;
        continue;                               // i não avança
      }

      // pula o que não serve nesta posição (ex.: letra no DV do CNPJ)
      while (i < caracteres.length && !marcador.test(caracteres[i])) i++;
      if (i >= caracteres.length) break;

      saida += caracteres[i];
      indiceOrigem.push(i);
      paradas.push(saida.length);
      i++;
    }

    return { saida: saida, paradas: paradas, indiceOrigem: indiceOrigem };
  }

  /** Conta caracteres significativos à esquerda da posição `ate`. */
  function contarSignificativos(texto, ate, regra) {
    var total = 0;
    for (var k = 0; k < ate && k < texto.length; k++) {
      if (regra.test(texto[k])) total++;
    }
    return total;
  }

  /** Quebra a string em array de caracteres válidos, já normalizados. */
  function extrair(texto, regra, maiusculas) {
    var lista = [];
    for (var k = 0; k < texto.length; k++) {
      var c = texto[k];
      if (!regra.test(c)) continue;
      lista.push(maiusculas ? c.toUpperCase() : c);
    }
    return lista;
  }


  function aplicarPosicional(campo, tipoEntrada) {
    var nome = campo.getAttribute(ATRIBUTO);
    var valorBruto = campo.value;
    var cursorAntes = campo.selectionStart;
    if (cursorAntes === null || cursorAntes === undefined) {
      cursorAntes = valorBruto.length;
    }

    // Modelo provisório, só para descobrir a regra de caracteres
    var amostra = valorBruto.replace(/[^0-9A-Za-z]/g, '');
    var modelo = resolverModelo(nome, amostra.length);
    var regra = regraSignificativa(modelo);
    var maiusculas = /[@A]/.test(modelo);

    var caracteres = extrair(valorBruto, regra, maiusculas);
    var significativosAntes = contarSignificativos(valorBruto, cursorAntes, regra);

    // Backspace que apagou só um SEPARADOR: o browser removeu o literal e a
    // máscara o reinseriria, travando o campo. Remove o caractere anterior.
    if (tipoEntrada === 'deleteContentBackward') {
      var anterior = campo._mascaraAnterior || '';
      var anteriores = extrair(anterior, regra, maiusculas);

      if (anteriores.length > 0 && caracteres.length === anteriores.length) {
        var indice = significativosAntes - 1;
        if (indice >= 0) {
          caracteres.splice(indice, 1);
          significativosAntes = indice;
        }
      }
    }

    // Modelo definitivo, agora com a contagem final (telefone 10 -> 11)
    var modeloFinal = resolverModelo(nome, caracteres.length);
    var resultado = encaixar(caracteres, modeloFinal);

    var colocadosAntes = 0;
    for (var j = 0; j < resultado.indiceOrigem.length; j++) {
      if (resultado.indiceOrigem[j] < significativosAntes) colocadosAntes++;
    }
    var cursorDepois =
      colocadosAntes === 0 ? 0 : resultado.paradas[colocadosAntes - 1];

    campo.value = resultado.saida;
    campo._mascaraAnterior = resultado.saida;

    posicionarCursor(campo, cursorDepois);
  }

  function posicionarCursor(campo, posicao) {
    if (document.activeElement !== campo) return; // não rouba foco na carga
    try {
      campo.setSelectionRange(posicao, posicao);
    } catch (erro) {
      /* type do input não suporta seleção — ignora */
    }
  }
  /** Valor do campo sem máscara — é o que vai para o servidor. */
  function valorCru(campo) {
    var nome = campo.getAttribute(ATRIBUTO);

    var significativos = campo.value.replace(/[^0-9A-Za-z]/g, '');
    var modelo = resolverModelo(nome, significativos.length);

    // modelos com letra (@ ou A) guardam maiúsculo, igual ao que a máscara digita
    return /[@A]/.test(modelo) ? significativos.toUpperCase() : significativos;
  }

  function formatarTrecho(raiz) {
    if (!raiz || !raiz.querySelectorAll) return;

    // o próprio nó, se ele mesmo for um campo mascarado
    if (raiz.matches && raiz.matches(SELETOR) && raiz.value) aplicarPosicional(raiz, null);

    var campos = raiz.querySelectorAll(SELETOR);
    for (var k = 0; k < campos.length; k++) {
      if (campos[k].value) aplicarPosicional(campos[k], null);
    }
  }

  function tirarMascarasDoEnvio(formulario) {
    var campos = formulario.querySelectorAll(SELETOR);
    if (campos.length === 0) return;

    var formatados = [];

    for (var k = 0; k < campos.length; k++) {
      formatados.push(campos[k].value);
      campos[k].value = valorCru(campos[k]);
    }

    setTimeout(function () {
      for (var j = 0; j < campos.length; j++) {
        campos[j].value = formatados[j];
        campos[j]._mascaraAnterior = formatados[j];
      }
    }, 0);
  }

  /* ---------------------------------------------------------------- */
  /* Inicialização automática                                          */
  /* ---------------------------------------------------------------- */

  function alvoMascarado(alvo) {
    if (!alvo || !alvo.closest) return null;
    return alvo.closest(SELETOR);
  }

  /**
   * Formata campos que chegam depois — HTML injetado por fetch, linhas de
   * tabela renderizadas no client, conteúdo de <dialog> montado sob demanda.
   */
  function observarDom() {
    if (typeof MutationObserver === 'undefined') return;

    var observador = new MutationObserver(function (mutacoes) {
      for (var m = 0; m < mutacoes.length; m++) {
        var adicionados = mutacoes[m].addedNodes;
        for (var n = 0; n < adicionados.length; n++) {
          if (adicionados[n].nodeType === 1) formatarTrecho(adicionados[n]);
        }
      }
    });

    observador.observe(document.documentElement, {
      childList: true,
      subtree: true
    });
  }

  function ligar() {
    // Delegação: cobre qualquer campo, inclusive os criados depois.
    // Os eventos 'input' e 'change' sobem até document.
    document.addEventListener('input', function (evento) {
      var campo = alvoMascarado(evento.target);
      if (campo) aplicarPosicional(campo, evento.inputType);
    });

    // Autofill do browser e atribuição programática nem sempre disparam
    // 'input'; 'change' garante a formatação ao sair do campo.
    document.addEventListener('change', function (evento) {
      var campo = alvoMascarado(evento.target);
      if (campo) aplicarPosicional(campo, null);
    });

    // Captura: roda antes dos handlers presos ao próprio <form>, então um
    // FormData montado lá dentro já enxerga o valor sem máscara.
    document.addEventListener('submit', function (evento) {
      var formulario = evento.target;
      if (formulario && formulario.tagName === 'FORM') tirarMascarasDoEnvio(formulario);
    }, true);

    formatarTrecho(document);
    observarDom();
  }

  /* ---------------------------------------------------------------- */
  /* API pública                                                       */
  /* ---------------------------------------------------------------- */

  window.Mascara = {
    /** Valor sem máscara. Aceita elemento ou seletor CSS. */
    remover: function (alvo) {
      var campo = typeof alvo === 'string' ? document.querySelector(alvo) : alvo;
      if (!campo) return '';
      return valorCru(campo);
    },

    /** Formata uma string solta — útil ao montar tabelas no client. */
    aplicarEm: function (valor, nome) {
      var texto = String(valor === null || valor === undefined ? '' : valor);
      var amostra = texto.replace(/[^0-9A-Za-z]/g, '');
      var modelo = resolverModelo(nome, amostra.length);
      var regra = regraSignificativa(modelo);
      var maiusculas = /[@A]/.test(modelo);
      return encaixar(extrair(texto, regra, maiusculas), modelo).saida;
    },

    /** Reformata um trecho manualmente (raramente necessário). */
    formatar: formatarTrecho,

    /** Registra um modelo novo em tempo de execução. */
    registrar: function (nome, modelo) {
      MODELOS[nome] = modelo;
    },

    MODELOS: MODELOS
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ligar);
  } else {
    ligar();
  }
})();
