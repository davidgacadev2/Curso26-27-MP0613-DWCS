<?php
/*
 * estado.php  (SOLUCIÓN 01)
 *  Guardar y recuperar la partida en campos ocultos del formulario.
 *
 *  Para no generar cientos de campos hidden (un tablero 16 x 30 tiene 480 casillas), cada FILA
 *  del tablero se guarda como una cadena de texto con un carácter por casilla:
 *      tablero: '*' = mina, '0'..'8' = nº de minas vecinas       p.ej. "01*1000"
 *      estado : 'O' = oculta, 'D' = descubierta, 'B' = bandera     p.ej. "DDOOBOO"
 *  y se convierte de nuevo en array con str_split().
 *
 *  INCONVENIENTE: el jugador puede ver dónde están las minas mirando el código fuente de la página
 *  (o cambiar el estado de la partida). La SOLUCIÓN 02 resuelve este problema.
 */
require_once 'logica.php';

/**
 * Genera los campos hidden con toda la información de la partida.
 */
function partidaACamposOcultos(array $partida): string
{
    $html = '';
    foreach (['filas', 'columnas', 'minas', 'jugadas', 'inicio', 'fin', 'resultado', 'explotada'] as $campo) {
        $html .= "<input type='hidden' name='partida[{$campo}]' value='{$partida[$campo]}'>\n";
    }
    $html .= "<input type='hidden' name='partida[colocadas]' value='" . ($partida['colocadas'] ? 1 : 0) . "'>\n";

    for ($f = 0; $f < $partida['filas']; $f++) {
        // array_map con una función anónima: convierte MINA (-1) en '*' y deja los números igual
        $textoTablero = implode('', array_map(fn($valor) => $valor == MINA ? '*' : $valor, $partida['tablero'][$f]));
        $textoEstado  = implode('', $partida['estado'][$f]);
        $html .= "<input type='hidden' name='partida[tablero][{$f}]' value='{$textoTablero}'>\n";
        $html .= "<input type='hidden' name='partida[estado][{$f}]' value='{$textoEstado}'>\n";
    }
    return $html;
}

/**
 * Reconstruye la partida a partir de $_POST['partida'].
 *  Como los datos vienen del cliente, lo comprobamos todo; si algo no cuadra devolvemos null.
 */
function recuperarPartida(mixed $datos): ?array
{
    if (!is_array($datos) || !isset($datos['tablero'], $datos['estado'])
        || !is_array($datos['tablero']) || !is_array($datos['estado'])) {
        return null;
    }

    // Campos numéricos
    $numeros = [];
    foreach (['filas', 'columnas', 'minas', 'jugadas', 'inicio', 'fin'] as $campo) {
        $valor = filter_var($datos[$campo] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($valor === false) {
            return null;
        }
        $numeros[$campo] = $valor;
    }
    [$filas, $columnas, $minas] = [$numeros['filas'], $numeros['columnas'], $numeros['minas']];
    if ($filas < FILAS_MIN || $filas > FILAS_MAX || $columnas < COLUMNAS_MIN || $columnas > COLUMNAS_MAX
        || $minas < 1 || $minas > maximoMinas($filas, $columnas)) {
        return null;
    }

    // Campos de texto: solo valores conocidos (lista blanca) o con el formato esperado
    $resultado = $datos['resultado'] ?? null;
    $explotada = $datos['explotada'] ?? null;
    if (!in_array($resultado, [EN_JUEGO, GANADA, PERDIDA], true)
        || !is_string($explotada) || preg_match('/^(\d{1,2}-\d{1,2})?$/', $explotada) !== 1) {
        return null;
    }

    $partida = nuevaPartida($filas, $columnas, $minas);
    $partida['colocadas'] = ($datos['colocadas'] ?? '0') === '1';
    $partida['resultado'] = $resultado;
    $partida['explotada'] = $explotada;
    foreach (['jugadas', 'inicio', 'fin'] as $campo) {
        $partida[$campo] = $numeros[$campo];
    }

    // Filas del tablero y del estado
    $minasContadas = 0;
    for ($f = 0; $f < $filas; $f++) {
        $textoTablero = (string) ($datos['tablero'][$f] ?? '');
        $textoEstado  = (string) ($datos['estado'][$f] ?? '');
        if (preg_match('/^[*0-8]{' . $columnas . '}$/', $textoTablero) !== 1
            || preg_match('/^[ODB]{' . $columnas . '}$/', $textoEstado) !== 1) {
            return null;
        }
        $partida['tablero'][$f] = array_map(fn($caracter) => $caracter == '*' ? MINA : (int) $caracter, str_split($textoTablero));
        $partida['estado'][$f]  = str_split($textoEstado);
        $minasContadas += substr_count($textoTablero, '*');
    }

    // Si las minas ya se colocaron, tiene que haber exactamente las indicadas; si no, ninguna
    if ($minasContadas != ($partida['colocadas'] ? $minas : 0)) {
        return null;
    }
    return $partida;
}

/**
 * Valida el formulario de configuración. Devuelve [filas, columnas, minas] o null (con los errores en $errores).
 */
function validarConfiguracion(array $datos, array &$errores): ?array
{
    $nivel = $datos['nivel'] ?? '';
    if (array_key_exists($nivel, NIVELES)) {
        return [NIVELES[$nivel]['filas'], NIVELES[$nivel]['columnas'], NIVELES[$nivel]['minas']];
    }
    if ($nivel !== 'personalizado') {
        $errores[] = 'Elige un nivel de dificultad.';
        return null;
    }

    $filas    = filter_var($datos['filas'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => FILAS_MIN, 'max_range' => FILAS_MAX]]);
    $columnas = filter_var($datos['columnas'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => COLUMNAS_MIN, 'max_range' => COLUMNAS_MAX]]);
    $minas    = filter_var($datos['minas'] ?? '', FILTER_VALIDATE_INT);

    if ($filas === false) {
        $errores[] = 'Las filas deben ser un número entero entre ' . FILAS_MIN . ' y ' . FILAS_MAX . '.';
    }
    if ($columnas === false) {
        $errores[] = 'Las columnas deben ser un número entero entre ' . COLUMNAS_MIN . ' y ' . COLUMNAS_MAX . '.';
    }
    if ($minas === false) {
        $errores[] = 'El número de minas debe ser un número entero.';
    } elseif ($filas !== false && $columnas !== false) {
        // Solo podemos comprobar el máximo de minas si filas y columnas son correctas
        $maximo = maximoMinas($filas, $columnas);
        if ($minas < 1 || $minas > $maximo) {
            $errores[] = "Para un tablero de {$filas} x {$columnas} el número de minas debe estar entre 1 y {$maximo}.";
        }
    }
    return count($errores) > 0 ? null : [$filas, $columnas, $minas];
}
