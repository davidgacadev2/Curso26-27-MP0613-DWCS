<?php
/*
 * logica.php
 *  Reglas del juego. Ninguna función escribe HTML.
 *
 *  Una partida es un array asociativo:
 *      [
 *          'filas' => 9, 'columnas' => 9, 'minas' => 10,
 *          'colocadas' => false,          // las minas se colocan tras el PRIMER clic
 *          'tablero'   => [[0, 1, -1, ...], ...],   // MINA (-1) o nº de minas vecinas
 *          'estado'    => [['O', 'D', 'B', ...], ...], // OCULTA, DESCUBIERTA o BANDERA
 *          'jugadas'   => 0,
 *          'inicio'    => 0,              // marca de tiempo (time()) del primer clic
 *          'fin'       => 0,              // marca de tiempo del final de la partida
 *          'resultado' => EN_JUEGO,       // EN_JUEGO, GANADA o PERDIDA
 *          'explotada' => '',             // "fila-columna" de la mina que explotó
 *      ]
 */
require_once 'config.php';

/**
 * Crea una matriz filas x columnas rellena con $valor.
 */
function crearMatriz(int $filas, int $columnas, mixed $valor): array
{
    return array_fill(0, $filas, array_fill(0, $columnas, $valor));
}

/**
 * Devuelve una partida nueva, todavía sin minas.
 */
function nuevaPartida(int $filas, int $columnas, int $minas): array
{
    return [
        'filas'     => $filas,
        'columnas'  => $columnas,
        'minas'     => $minas,
        'colocadas' => false,
        'tablero'   => crearMatriz($filas, $columnas, 0),
        'estado'    => crearMatriz($filas, $columnas, OCULTA),
        'jugadas'   => 0,
        'inicio'    => 0,
        'fin'       => 0,
        'resultado' => EN_JUEGO,
        'explotada' => '',
    ];
}

/**
 * Máximo de minas permitido para un tablero: el 35 % de las casillas, y siempre dejando
 * libres al menos 9 (la casilla del primer clic y sus 8 vecinas).
 */
function maximoMinas(int $filas, int $columnas): int
{
    return min(intdiv($filas * $columnas * PORCENTAJE_MAX_MINAS, 100), $filas * $columnas - 9);
}

/**
 * Devuelve las coordenadas [fila, columna] de las casillas vecinas (2, 3, 5 u 8 según la posición).
 */
function vecinos(int $fila, int $columna, int $filas, int $columnas): array
{
    $resultado = [];
    for ($df = -1; $df <= 1; $df++) {
        for ($dc = -1; $dc <= 1; $dc++) {
            $f = $fila + $df;
            $c = $columna + $dc;
            if (($df != 0 || $dc != 0) && $f >= 0 && $f < $filas && $c >= 0 && $c < $columnas) {
                $resultado[] = [$f, $c];
            }
        }
    }
    return $resultado;
}

/**
 * Coloca las minas al azar, evitando la casilla del primer clic y sus vecinas
 * (así el primer clic nunca pierde y además siempre abre una zona), y calcula los números.
 */
function colocarMinas(array &$partida, int $filaSegura, int $columnaSegura): void
{
    $filas    = $partida['filas'];
    $columnas = $partida['columnas'];

    // Numeramos las casillas de 0 a (filas * columnas - 1): posicion = fila * columnas + columna
    $prohibidas = [$filaSegura * $columnas + $columnaSegura];
    foreach (vecinos($filaSegura, $columnaSegura, $filas, $columnas) as [$f, $c]) {
        $prohibidas[] = $f * $columnas + $c;
    }
    $candidatas = array_diff(range(0, $filas * $columnas - 1), $prohibidas);
    shuffle($candidatas);

    foreach (array_slice($candidatas, 0, $partida['minas']) as $posicion) {
        $partida['tablero'][intdiv($posicion, $columnas)][$posicion % $columnas] = MINA;
    }

    calcularNumeros($partida['tablero']);
    $partida['colocadas'] = true;
}

/**
 * Para cada casilla sin mina, cuenta las minas que tiene alrededor.
 */
function calcularNumeros(array &$tablero): void
{
    $filas    = count($tablero);
    $columnas = count($tablero[0]);
    for ($f = 0; $f < $filas; $f++) {
        for ($c = 0; $c < $columnas; $c++) {
            if ($tablero[$f][$c] == MINA) {
                continue;
            }
            $total = 0;
            foreach (vecinos($f, $c, $filas, $columnas) as [$vf, $vc]) {
                if ($tablero[$vf][$vc] == MINA) {
                    $total++;
                }
            }
            $tablero[$f][$c] = $total;
        }
    }
}

/**
 * Descubre la casilla. Si es un 0 (no tiene minas alrededor) descubre también sus vecinas
 * de forma RECURSIVA, igual que hace el juego original.
 *  - $tablero se pasa por valor (solo lo leemos); $estado por referencia (lo modificamos)
 *  - devuelve el número de casillas descubiertas
 */
function descubrir(array $tablero, array &$estado, int $fila, int $columna): int
{
    // Caso base: ya descubierta o con bandera -> no hacemos nada
    if ($estado[$fila][$columna] != OCULTA) {
        return 0;
    }

    $estado[$fila][$columna] = DESCUBIERTA;
    $descubiertas = 1;

    if ($tablero[$fila][$columna] == 0) {
        foreach (vecinos($fila, $columna, count($tablero), count($tablero[0])) as [$f, $c]) {
            $descubiertas += descubrir($tablero, $estado, $f, $c);
        }
    }
    return $descubiertas;
}

/**
 * Cuenta cuántas casillas hay en un estado concreto (OCULTA, DESCUBIERTA o BANDERA).
 */
function contarEstado(array $estado, string $tipo): int
{
    $total = 0;
    foreach ($estado as $fila) {
        // array_count_values(['O','D','O']) = ['O' => 2, 'D' => 1]
        $total += array_count_values($fila)[$tipo] ?? 0;
    }
    return $total;
}

/**
 * Se gana cuando todas las casillas que NO son mina están descubiertas.
 */
function haGanado(array $partida): bool
{
    $casillasLibres = $partida['filas'] * $partida['columnas'] - $partida['minas'];
    return contarEstado($partida['estado'], DESCUBIERTA) == $casillasLibres;
}

/**
 * Pone o quita una bandera en una casilla oculta.
 */
function alternarBandera(array &$partida, int $fila, int $columna): void
{
    $actual = $partida['estado'][$fila][$columna];
    if ($actual == DESCUBIERTA) {
        return;   // en una casilla descubierta no se puede poner bandera
    }
    $partida['estado'][$fila][$columna] = ($actual == OCULTA) ? BANDERA : OCULTA;
    $partida['jugadas']++;
}

/**
 * Jugada de "descubrir" en una casilla. Devuelve un mensaje para el jugador (o '' si no hay nada que decir).
 */
function jugarCasilla(array &$partida, int $fila, int $columna): string
{
    if ($partida['estado'][$fila][$columna] == BANDERA) {
        return 'Esa casilla tiene una bandera: quítala antes de descubrirla (modo «Bandera»).';
    }
    if ($partida['estado'][$fila][$columna] == DESCUBIERTA) {
        return '';
    }

    // Primer clic: ahora sí colocamos las minas y ponemos en marcha el reloj
    if (!$partida['colocadas']) {
        colocarMinas($partida, $fila, $columna);
        $partida['inicio'] = time();
    }
    $partida['jugadas']++;

    if ($partida['tablero'][$fila][$columna] == MINA) {
        $partida['estado'][$fila][$columna] = DESCUBIERTA;
        $partida['resultado'] = PERDIDA;
        $partida['explotada'] = "{$fila}-{$columna}";
        $partida['fin']       = time();
        return '';
    }

    descubrir($partida['tablero'], $partida['estado'], $fila, $columna);

    if (haGanado($partida)) {
        $partida['resultado'] = GANADA;
        $partida['fin']       = time();
        // Al ganar, marcamos con bandera todas las minas
        foreach ($partida['tablero'] as $f => $filaTablero) {
            foreach ($filaTablero as $c => $valor) {
                if ($valor == MINA) {
                    $partida['estado'][$f][$c] = BANDERA;
                }
            }
        }
    }
    return '';
}

/**
 * Segundos transcurridos de partida.
 */
function segundosJugados(array $partida): int
{
    if ($partida['inicio'] == 0) {
        return 0;
    }
    return ($partida['fin'] > 0 ? $partida['fin'] : time()) - $partida['inicio'];
}

/**
 * Convierte "fila-columna" en [fila, columna] comprobando que está dentro del tablero.
 * Devuelve null si el texto no es válido (¡el value de un botón también se puede manipular!).
 */
function leerCoordenadas(string $texto, int $filas, int $columnas): ?array
{
    if (preg_match('/^(\d{1,2})-(\d{1,2})$/', $texto, $partes) !== 1) {
        return null;
    }
    $fila    = (int) $partes[1];
    $columna = (int) $partes[2];
    if ($fila >= $filas || $columna >= $columnas) {
        return null;
    }
    return [$fila, $columna];
}
