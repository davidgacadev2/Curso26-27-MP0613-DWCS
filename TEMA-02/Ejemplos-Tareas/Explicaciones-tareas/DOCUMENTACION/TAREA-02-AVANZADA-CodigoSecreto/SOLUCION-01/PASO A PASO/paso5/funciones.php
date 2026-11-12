<?php
/*
 * >>> PASO 5 de 6: T4 Pista (cuesta un intento) y T5 Rendirse <<<
 *
 * TAREA-02 AVANZADA: Código secreto — funciones.php
 *  Las acciones (AC) y los predicados (PL) del autómata que necesitan más de una línea, sin HTML.
 *  La partida es un array:
 *    ['posiciones' => 4, 'colores' => 6, 'maxIntentos' => 10, 'repetir' => false, 'semilla' => 1234,
 *     'intentos' => [[0, 1, 2, 3], ...], 'pista' => -1, 'ganadas' => 0, 'perdidas' => 0]
 *  El código secreto NO se guarda en la página: se calcula a partir de la semilla con una clave que solo
 *  conoce el servidor (hash_hmac), así que ver el código fuente de la página no sirve para hacer trampas.
 */

// constantes: los colores (nombre y color CSS), los límites de la configuración y la clave del servidor
const COLORES = [
    ['rojo', '#d32f2f'], ['verde', '#388e3c'], ['azul', '#1976d2'], ['amarillo', '#fbc02d'],
    ['naranja', '#f57c00'], ['morado', '#7b1fa2'], ['blanco', '#f5f5f5'], ['negro', '#212121'],
];
const POSICIONES = [4, 5];
const NUM_COLORES = [6, 7, 8];
const MIN_INTENTOS = 6;
const MAX_INTENTOS = 12;
const CLAVE = 'BK-Programacion-clave-del-servidor';

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// AC2 validarConfiguracion(array $datos): array -> [configuración, errores]; PL0 es "no hay errores"
function validarConfiguracion(array $datos): array
{
    $errores = [];
    $posiciones = filter_var($datos['posiciones'] ?? '', FILTER_VALIDATE_INT);
    $colores = filter_var($datos['colores'] ?? '', FILTER_VALIDATE_INT);
    $intentos = filter_var($datos['maxintentos'] ?? '', FILTER_VALIDATE_INT,
        ['options' => ['min_range' => MIN_INTENTOS, 'max_range' => MAX_INTENTOS]]);
    if (!in_array($posiciones, POSICIONES, true)) {
        $errores[] = 'El código debe tener 4 o 5 posiciones.';
    }
    if (!in_array($colores, NUM_COLORES, true)) {
        $errores[] = 'Se juega con 6, 7 u 8 colores.';
    }
    if ($intentos === false) {
        $errores[] = 'Los intentos deben ser un entero entre ' . MIN_INTENTOS . ' y ' . MAX_INTENTOS . '.';
    }
    $configuracion = [
        'posiciones' => $posiciones === false ? 4 : $posiciones,
        'colores' => $colores === false ? 6 : $colores,
        'maxIntentos' => $intentos === false ? 10 : $intentos,
        'repetir' => isset($datos['repetir']),
    ];
    return [$configuracion, $errores];
}

// AC3 nuevaPartida(array $configuracion, int $semilla, int $ganadas, int $perdidas): array -> sin intentos ni pista
function nuevaPartida(array $configuracion, int $semilla, int $ganadas, int $perdidas): array
{
    return [
        'posiciones' => $configuracion['posiciones'],
        'colores' => $configuracion['colores'],
        'maxIntentos' => $configuracion['maxIntentos'],
        'repetir' => $configuracion['repetir'],
        'semilla' => $semilla,
        'intentos' => [],
        'pista' => -1,
        'ganadas' => $ganadas,
        'perdidas' => $perdidas,
    ];
}

// codigoSecreto(array $partida): array -> el código (índices de color), siempre el mismo para la misma semilla
function codigoSecreto(array $partida): array
{
    // la semilla del generador sale de la semilla de la partida y de la clave: sin la clave no se puede calcular
    mt_srand(crc32(hash_hmac('sha256', (string) $partida['semilla'], CLAVE)));
    if ($partida['repetir']) {
        $codigo = [];
        for ($i = 0; $i < $partida['posiciones']; $i++) {
            $codigo[] = mt_rand(0, $partida['colores'] - 1);
        }
        return $codigo;
    }
    $todos = range(0, $partida['colores'] - 1);
    shuffle($todos);                                    // shuffle usa el mismo generador que mt_rand
    return array_slice($todos, 0, $partida['posiciones']);
}

// AC1 leerPartida(array $datos): array -> la partida de los campos ocultos (sin comprobar: eso es PL1)
function leerPartida(array $datos): array
{
    $entero = fn(string $campo, int $defecto): int =>
        filter_var($datos[$campo] ?? '', FILTER_VALIDATE_INT) === false ? $defecto : (int) $datos[$campo];
    $texto = is_string($datos['intentos'] ?? null) ? $datos['intentos'] : '';
    $intentos = [];
    foreach ($texto === '' ? [] : explode('-', $texto) as $grupo) {
        $intentos[] = array_map('intval', str_split($grupo));
    }
    return [
        'posiciones' => $entero('posiciones', 0),
        'colores' => $entero('colores', 0),
        'maxIntentos' => $entero('maxintentos', 0),
        'repetir' => ($datos['repite'] ?? '') === '1',
        'semilla' => $entero('semilla', 0),
        'intentos' => $intentos,
        'textoIntentos' => $texto,
        'pista' => $entero('pista', -1),
        'ganadas' => max(0, $entero('ganadas', 0)),
        'perdidas' => max(0, $entero('perdidas', 0)),
    ];
}

// AC5 leerIntento(array $datos, array $partida): array -> [intento o null, motivo]; PL2 es "intento no null"
function leerIntento(array $datos, array $partida): array
{
    $recibido = is_array($datos['intento'] ?? null) ? $datos['intento'] : [];
    $intento = [];
    for ($i = 0; $i < $partida['posiciones']; $i++) {
        $color = filter_var($recibido[$i] ?? '', FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => $partida['colores'] - 1]]);
        if ($color === false) {
            return [null, 'Elige un color para cada una de las ' . $partida['posiciones'] . ' posiciones.'];
        }
        $intento[] = $color;
    }
    if (!$partida['repetir'] && count(array_unique($intento)) < count($intento)) {
        return [null, 'En esta partida los colores no se repiten: no pongas dos veces el mismo.'];
    }
    return [$intento, ''];
}

// puntuar(array $intento, array $secreto): array -> [negras, blancas]: color y posición bien / solo color bien
function puntuar(array $intento, array $secreto): array
{
    $negras = 0;
    foreach ($intento as $i => $color) {
        if ($secreto[$i] === $color) {
            $negras++;
        }
    }
    // coincidencias de color sin mirar la posición: para cada color, el mínimo de veces que sale en cada código
    $coinciden = 0;
    $enIntento = array_count_values($intento);
    $enSecreto = array_count_values($secreto);
    foreach ($enIntento as $color => $veces) {
        $coinciden += min($veces, $enSecreto[$color] ?? 0);
    }
    return [$negras, $coinciden - $negras];
}

// intentosRestantes(array $partida): int -> los permitidos menos los hechos (y menos uno si se pidió la pista)
function intentosRestantes(array $partida): int
{
    $gastados = count($partida['intentos']);
    // la pista cuesta un intento
    $gastados += $partida['pista'] >= 0 ? 1 : 0;
    return $partida['maxIntentos'] - $gastados;
}

// textoIntentos(array $intentos): string -> [[0,1,2,3],[4,5,1,2]] pasa a "0123-4512" para el campo oculto
function textoIntentos(array $intentos): string
{
    return implode('-', array_map(fn(array $intento): string => implode('', $intento), $intentos));
}

// AC8 elegirPista(array $partida, array $secreto): int -> la primera posición que el jugador no ha acertado nunca
function elegirPista(array $partida, array $secreto): int
{
    foreach ($secreto as $i => $color) {
        $acertada = false;
        foreach ($partida['intentos'] as $intento) {
            if ($intento[$i] === $color) {
                $acertada = true;
            }
        }
        if (!$acertada) {
            return $i;
        }
    }
    return 0;
}

// TODO: PL1 partidaValida(array $partida): bool -> ¿es una partida posible en ST2 (jugando)?
