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
// TODO (paso 3): function vecinos(int $fila, int $columna, int $filas, int $columnas): array

/**
 * Coloca las minas al azar, evitando la casilla del primer clic y sus vecinas
 * (así el primer clic nunca pierde y además siempre abre una zona), y calcula los números.
 */
// TODO (paso 3): function colocarMinas(array &$partida, int $filaSegura, int $columnaSegura): void

/**
 * Para cada casilla sin mina, cuenta las minas que tiene alrededor.
 */
// TODO (paso 3): function calcularNumeros(array &$tablero): void

/**
 * Descubre la casilla. Si es un 0 (no tiene minas alrededor) descubre también sus vecinas
 * de forma RECURSIVA, igual que hace el juego original.
 *  - $tablero se pasa por valor (solo lo leemos); $estado por referencia (lo modificamos)
 *  - devuelve el número de casillas descubiertas
 */
// TODO (paso 3): function descubrir(array $tablero, array &$estado, int $fila, int $columna): int

/**
 * Cuenta cuántas casillas hay en un estado concreto (OCULTA, DESCUBIERTA o BANDERA).
 */
// TODO (paso 4): function contarEstado(array $estado, string $tipo): int

/**
 * Se gana cuando todas las casillas que NO son mina están descubiertas.
 */
// TODO (paso 4): function haGanado(array $partida): bool

/**
 * Pone o quita una bandera en una casilla oculta.
 */
// TODO (paso 4): function alternarBandera(array &$partida, int $fila, int $columna): void

/**
 * Jugada de "descubrir" en una casilla. Devuelve un mensaje para el jugador (o '' si no hay nada que decir).
 */
// TODO (paso 3): function jugarCasilla(array &$partida, int $fila, int $columna): string

/**
 * Segundos transcurridos de partida.
 */
// TODO (paso 4): function segundosJugados(array $partida): int

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
