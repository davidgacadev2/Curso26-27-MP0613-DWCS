<?php
/*
 * >>> PASO 4 de 6: fallos calculados, dibujo del ahorcado y fin de la partida (ganada o perdida) <<<
 *
 * TAREA-02 MEDIA: El ahorcado — funciones.php
 *  Funciones sin HTML (fáciles de depurar paso a paso). La partida es un array:
 *    ['categoria' => 'Animales', 'indice' => 2, 'maxFallos' => 6, 'usadas' => 'AEK', 'palabrasFalladas' => 0]
 *  La palabra secreta NO se guarda: se busca en palabras.php con la categoría y el índice. Los fallos tampoco
 *  se guardan: se cuentan a partir de las letras usadas.
 */

// constantes del juego
const DIFICULTADES = ['facil' => 8, 'normal' => 6, 'dificil' => 4];   // fallos permitidos
// constantes del teclado: las 27 letras y las vocales con tilde o diéresis
const ABECEDARIO = 'ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';
const SIN_TILDE = ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U'];
// TODO: constantes de resolver: fallos que cuesta decir una palabra equivocada

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// función validarConfiguracion(array $datos, array $palabras): array -> [configuración, errores]
function validarConfiguracion(array $datos, array $palabras): array
{
    $errores = [];
    $categoria = is_string($datos['categoria'] ?? null) ? $datos['categoria'] : '';
    $dificultad = is_string($datos['dificultad'] ?? null) ? $datos['dificultad'] : '';
    if (!array_key_exists($categoria, $palabras)) {
        $errores[] = 'Elige una de las categorías de la lista.';
    }
    if (!array_key_exists($dificultad, DIFICULTADES)) {
        $errores[] = 'Elige una dificultad: fácil, normal o difícil.';
    }
    return [['categoria' => $categoria, 'dificultad' => $dificultad], $errores];
}

// función nuevaPartida(array $configuracion, array $palabras, int $semilla): array -> palabra al azar, ninguna letra usada
function nuevaPartida(array $configuracion, array $palabras, int $semilla): array
{
    $lista = $palabras[$configuracion['categoria']];
    mt_srand($semilla);                         // la misma semilla elige siempre la misma palabra (para probar)
    $indice = mt_rand(0, count($lista) - 1);
    return [
        'categoria' => $configuracion['categoria'],
        'indice' => $indice,
        'maxFallos' => DIFICULTADES[$configuracion['dificultad']],
        'usadas' => '',
        'palabrasFalladas' => 0,
    ];
}

// función palabraSecreta(array $partida, array $palabras): string -> la palabra de la partida
function palabraSecreta(array $partida, array $palabras): string
{
    return $palabras[$partida['categoria']][$partida['indice']];
}

// función leerEstado(array $datos): array -> la partida de los campos ocultos (sin comprobar aún)
function leerEstado(array $datos): array
{
    $entero = fn(string $campo): int => filter_var($datos[$campo] ?? '', FILTER_VALIDATE_INT) === false
        ? -1 : (int) $datos[$campo];
    return [
        'categoria' => is_string($datos['categoria'] ?? null) ? $datos['categoria'] : '',
        'indice' => $entero('indice'),
        'maxFallos' => $entero('maxfallos'),
        'usadas' => is_string($datos['usadas'] ?? null) ? $datos['usadas'] : '',
        'palabrasFalladas' => $entero('palabrasfalladas'),
    ];
}

// función quitarTildes(string $texto): string -> "CIGÜEÑA" pasa a "CIGUEÑA" (la Ñ no cambia)
function quitarTildes(string $texto): string
{
    return strtr($texto, SIN_TILDE);
}

// función letrasUsadas(array $partida): array -> "AEÑ" pasa a ['A', 'E', 'Ñ']
function letrasUsadas(array $partida): array
{
    return mb_str_split($partida['usadas']);   // str_split partiría la Ñ en dos bytes
}

// función palabraOculta(string $palabra, array $usadas, bool $descubrir): array -> casillas con su letra o un hueco
function palabraOculta(string $palabra, array $usadas, bool $descubrir = false): array
{
    $casillas = [];
    foreach (mb_str_split($palabra) as $letra) {
        if ($letra === ' ') {
            $casillas[] = ['letra' => ' ', 'clase' => 'espacio'];
        } elseif (in_array(quitarTildes($letra), $usadas, true)) {
            $casillas[] = ['letra' => $letra, 'clase' => 'acierto'];
        } elseif ($descubrir) {
            $casillas[] = ['letra' => $letra, 'clase' => 'faltaba'];     // al perder se enseñan las que faltaban
        } else {
            $casillas[] = ['letra' => '_', 'clase' => 'hueco'];
        }
    }
    return $casillas;
}

// función probarLetra(array &$partida, mixed $letra, string $palabra): string -> añade la letra y devuelve el mensaje
function probarLetra(array &$partida, mixed $letra, string $palabra): string
{
    if (!is_string($letra) || mb_strlen($letra) !== 1 || mb_strpos(ABECEDARIO, $letra) === false) {
        return 'Esa letra no es válida.';
    }
    if (in_array($letra, letrasUsadas($partida), true)) {
        return "Ya habías probado la $letra.";
    }
    $partida['usadas'] .= $letra;
    $veces = mb_substr_count(quitarTildes($palabra), $letra);
    if ($veces === 0) {
        return "La $letra no está en la palabra.";
    }
    return $veces === 1 ? "¡Bien! La $letra aparece una vez." : "¡Bien! La $letra aparece $veces veces.";
}

// función contarFallos(array $partida, string $palabra): int -> letras que no están + palabras falladas
function contarFallos(array $partida, string $palabra): int
{
    $letrasPalabra = mb_str_split(quitarTildes($palabra));
    $fallos = 0;
    foreach (letrasUsadas($partida) as $letra) {
        if (!in_array($letra, $letrasPalabra, true)) {
            $fallos++;
        }
    }
    // TODO: decir una palabra equivocada cuesta FALLOS_PALABRA fallos
    return $fallos;
}

// función estadoPartida(array $partida, string $palabra): string -> "jugando", "ganada" o "perdida"
function estadoPartida(array $partida, string $palabra): string
{
    if (contarFallos($partida, $palabra) >= $partida['maxFallos']) {
        return 'perdida';
    }
    foreach (palabraOculta($palabra, letrasUsadas($partida)) as $casilla) {
        if ($casilla['clase'] === 'hueco') {
            return 'jugando';
        }
    }
    return 'ganada';
}

// función dibujo(int $fallos, int $maxFallos): string -> el ahorcado en 7 etapas (0 a 6)
function dibujo(int $fallos, int $maxFallos): string
{
    $etapa = min(6, (int) round($fallos * 6 / $maxFallos));     // con 4 fallos permitidos se avanza más deprisa
    $cabeza = $etapa >= 1 ? 'O' : ' ';
    $cuerpo = ($etapa >= 3 ? '/' : ' ') . ($etapa >= 2 ? '|' : ' ') . ($etapa >= 4 ? '\\' : ' ');
    $piernas = ($etapa >= 5 ? '/' : ' ') . ' ' . ($etapa >= 6 ? '\\' : ' ');
    return implode("\n", ['  +---+', '  |   |', "  $cabeza   |", " $cuerpo  |", " $piernas  |", '      |', '=========']);
}

// TODO: función resolver(array &$partida, mixed $intento, string $palabra): string -> decir la palabra entera

// TODO: función leerMarcador(array $datos): array -> victorias y derrotas de los campos ocultos (0 si no valen)

// TODO: función estadoValido(array $partida, array $palabras): bool -> ¿es una partida posible?
