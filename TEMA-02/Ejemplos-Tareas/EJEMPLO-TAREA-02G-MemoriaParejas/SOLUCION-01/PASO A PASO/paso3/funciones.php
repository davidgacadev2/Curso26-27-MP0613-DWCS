<?php
/*
 * >>> PASO 3 de 6: tablero barajado con una semilla, cartas como botones y partida guardada en campos ocultos <<<
 *
 * TAREA-02 MEDIA: Memoria de parejas — funciones.php
 *  Funciones sin HTML (fáciles de depurar paso a paso): validar la configuración, crear el tablero, leer el
 *  estado de los campos ocultos, jugar una carta, saber si la partida ha terminado y validar el estado.
 */

// constantes del juego
const MIN_LADO = 2;                 // filas y columnas: de 2 a 6
const MAX_LADO = 6;
const MIN_FALLOS = 1;               // fallos permitidos: de 1 a 30
const MAX_FALLOS = 30;
const PUNTOS_PAREJA = 10;           // una pareja suma 10 puntos
const PUNTOS_FALLO = 2;             // un fallo resta 2 puntos

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// función validarEntrada(array $datos): array -> [configuración, errores] del formulario inicial
function validarEntrada(array $datos): array
{
    $errores = [];
    $rango = ['options' => ['min_range' => MIN_LADO, 'max_range' => MAX_LADO]];
    $filas = filter_var($datos['filas'] ?? '', FILTER_VALIDATE_INT, $rango);
    $columnas = filter_var($datos['columnas'] ?? '', FILTER_VALIDATE_INT, $rango);
    $letras = is_string($datos['letras'] ?? null) ? strtoupper(trim($datos['letras'])) : '';
    $maxFallos = filter_var($datos['maxfallos'] ?? '', FILTER_VALIDATE_INT,
        ['options' => ['min_range' => MIN_FALLOS, 'max_range' => MAX_FALLOS]]);

    if ($filas === false) {
        $errores[] = 'El número de filas debe ser un entero entre ' . MIN_LADO . ' y ' . MAX_LADO . '.';
    }
    if ($columnas === false) {
        $errores[] = 'El número de columnas debe ser un entero entre ' . MIN_LADO . ' y ' . MAX_LADO . '.';
    }
    $parejas = 0;
    if ($filas !== false && $columnas !== false) {
        if ($filas * $columnas % 2 !== 0) {
            $errores[] = "Con $filas filas y $columnas columnas hay " . $filas * $columnas
                . ' cartas: el número de cartas debe ser par.';
        } else {
            $parejas = intdiv($filas * $columnas, 2);
        }
    }
    if (!preg_match('/^[A-Z]+$/', $letras)) {
        $errores[] = 'Escribe las letras de las cartas: solo letras de la A a la Z, sin espacios, tildes ni ñ.';
    } else {
        $repetidas = array_keys(array_filter(count_chars($letras, 1), fn($veces) => $veces > 1));
        if ($repetidas !== []) {
            $errores[] = 'Cada letra solo puede aparecer una vez. Repetidas: ' . implode(', ', array_map('chr', $repetidas)) . '.';
        } elseif ($parejas > 0 && strlen($letras) < $parejas) {
            $errores[] = "Para $parejas parejas hacen falta al menos $parejas letras (has escrito " . strlen($letras) . ').';
        }
    }
    if ($maxFallos === false) {
        $errores[] = 'Los fallos permitidos deben ser un entero entre ' . MIN_FALLOS . ' y ' . MAX_FALLOS . '.';
    }

    $configuracion = [
        'filas' => $filas === false ? (string) ($datos['filas'] ?? '') : $filas,
        'columnas' => $columnas === false ? (string) ($datos['columnas'] ?? '') : $columnas,
        'letras' => $errores === [] ? substr($letras, 0, $parejas) : $letras,
        'maxFallos' => $maxFallos === false ? (string) ($datos['maxfallos'] ?? '') : $maxFallos,
    ];
    return [$configuracion, $errores];
}

// función crearTablero(string $letras, int $columnas, int $semilla): array -> cada letra dos veces, barajadas, en filas
function crearTablero(string $letras, int $columnas, int $semilla): array
{
    $cartas = str_split($letras . $letras);    // "ABC" -> A, B, C, A, B, C
    mt_srand($semilla);                         // la misma semilla da siempre el mismo reparto (para probar)
    shuffle($cartas);
    return array_chunk($cartas, $columnas);     // [[fila 0], [fila 1], ...]
}

// función nuevaPartida(array $configuracion, int $semilla): array -> la partida recién empezada
function nuevaPartida(array $configuracion, int $semilla): array
{
    return [
        'filas' => $configuracion['filas'],
        'columnas' => $configuracion['columnas'],
        'letras' => $configuracion['letras'],
        'maxFallos' => $configuracion['maxFallos'],
        'cartas' => crearTablero($configuracion['letras'], $configuracion['columnas'], $semilla),
        'encontradas' => [],                    // posiciones "fila-columna" de las parejas ya encontradas
        'pendiente' => '',                      // la primera carta girada del turno ("" si no hay)
        'puntos' => 0,
        'fallos' => 0,
        'giradas' => 0,                         // cartas giradas en total (el marcador sale a partir de la 1.ª)
    ];
}

// función leerEstado(array $datos): array -> la partida guardada en los campos ocultos
function leerEstado(array $datos): array
{
    $encontradas = is_string($datos['encontradas'] ?? null) ? $datos['encontradas'] : '';
    return [
        'filas' => (int) ($datos['filas'] ?? 0),
        'columnas' => (int) ($datos['columnas'] ?? 0),
        'letras' => is_string($datos['letras'] ?? null) ? $datos['letras'] : '',
        'maxFallos' => (int) ($datos['maxfallos'] ?? 0),
        'cartas' => is_array($datos['cartas'] ?? null) ? $datos['cartas'] : [],
        'encontradas' => $encontradas === '' ? [] : explode(',', $encontradas),
        'pendiente' => is_string($datos['pendiente'] ?? null) ? $datos['pendiente'] : '',
        'puntos' => (int) ($datos['puntos'] ?? 0),
        'fallos' => (int) ($datos['fallos'] ?? 0),
        'giradas' => (int) ($datos['giradas'] ?? 0),
    ];
}

// TODO: función letraEn(array $partida, string $posicion): ?string -> la letra de "fila-columna" (null si no existe)

// TODO: función jugar(array &$partida, string $posicion): array -> gira una carta: [mensaje, cartas que se ven solo ahora]
    // TODO: si la partida ya ha terminado, no se juega

// TODO: función cartaVisible(array $partida, string $posicion, array $temporales): bool -> ¿se ve la letra?
    // TODO: al perder, se enseñan todas las cartas
function cartaVisible(array $partida, string $posicion, array $temporales): bool
{
    return false;                               // provisional: todas boca abajo
}

// TODO: función estadoPartida(array $partida): string -> 'jugando', 'ganada' o 'perdida'
function estadoPartida(array $partida): string
{
    return 'jugando';                           // provisional: la partida no termina nunca
}

// TODO: función estadoValido(array $partida): bool -> ¿los campos ocultos son una partida posible?
