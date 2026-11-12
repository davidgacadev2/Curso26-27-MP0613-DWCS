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
// TODO: incluir config.php (require_once)

// Función auxiliar TODO: crearMatriz(int $filas, int $columnas, mixed $valor): array      (array_fill)

// Función auxiliar TODO: nuevaPartida(int $filas, int $columnas, int $minas): array      (todavía sin minas)

// Función auxiliar TODO: maximoMinas(int $filas, int $columnas): int
//      35 % de las casillas, dejando siempre libres al menos 9

// Función auxiliar TODO: vecinos(int $fila, int $columna, int $filas, int $columnas): array
//      coordenadas [fila, columna] de las vecinas que existen

// Función auxiliar TODO: colocarMinas(array &$partida, int $filaSegura, int $columnaSegura): void
//      al azar, evitando la casilla del primer clic y sus vecinas (range, array_diff, shuffle, array_slice)

// Función auxiliar TODO: calcularNumeros(array &$tablero): void

// Función auxiliar TODO: descubrir(array $tablero, array &$estado, int $fila, int $columna): int
//      RECURSIVA: si la casilla es un 0, descubre también sus vecinas

// Función auxiliar TODO: contarEstado(array $estado, string $tipo): int

// Función auxiliar TODO: haGanado(array $partida): bool

// Función auxiliar TODO: alternarBandera(array &$partida, int $fila, int $columna): void

// Función auxiliar TODO: jugarCasilla(array &$partida, int $fila, int $columna): string
//      primer clic -> colocar minas; mina -> perder; si no -> descubrir y comprobar victoria

// Función auxiliar TODO: segundosJugados(array $partida): int

// Función auxiliar TODO: leerCoordenadas(string $texto, int $filas, int $columnas): ?array
//      "fila-columna" -> [fila, columna], o null si no es válido (¡el value se puede manipular!)
