<?php
/*
 * >>> PASO 2 de 6: formulario de configuración, elegir la palabra con una semilla y guardar la partida en campos ocultos <<<
 *
 * TAREA-02 MEDIA: El ahorcado — funciones.php
 *  Funciones sin HTML (fáciles de depurar paso a paso). La partida es un array:
 *    ['categoria' => 'Animales', 'indice' => 2, 'maxFallos' => 6, 'usadas' => 'AEK', 'palabrasFalladas' => 0]
 *  La palabra secreta NO se guarda: se busca en palabras.php con la categoría y el índice. Los fallos tampoco
 *  se guardan: se cuentan a partir de las letras usadas.
 */

// constantes del juego
const DIFICULTADES = ['facil' => 8, 'normal' => 6, 'dificil' => 4];   // fallos permitidos
// TODO: constantes del teclado: las 27 letras y las vocales con tilde o diéresis
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

// TODO: función leerEstado(array $datos): array -> la partida de los campos ocultos (sin comprobar aún)

// TODO: función quitarTildes(string $texto): string -> "CIGÜEÑA" pasa a "CIGUEÑA" (la Ñ no cambia)

// TODO: función letrasUsadas(array $partida): array -> "AEÑ" pasa a ['A', 'E', 'Ñ']

// TODO: función palabraOculta(string $palabra, array $usadas, bool $descubrir): array -> casillas con su letra o un hueco

// TODO: función probarLetra(array &$partida, mixed $letra, string $palabra): string -> añade la letra y devuelve el mensaje

// TODO: función contarFallos(array $partida, string $palabra): int -> letras que no están + palabras falladas
    // TODO: decir una palabra equivocada cuesta FALLOS_PALABRA fallos

// TODO: función estadoPartida(array $partida, string $palabra): string -> "jugando", "ganada" o "perdida"

// TODO: función dibujo(int $fallos, int $maxFallos): string -> el ahorcado en 7 etapas (0 a 6)

// TODO: función resolver(array &$partida, mixed $intento, string $palabra): string -> decir la palabra entera

// TODO: función leerMarcador(array $datos): array -> victorias y derrotas de los campos ocultos (0 si no valen)

// TODO: función estadoValido(array $partida, array $palabras): bool -> ¿es una partida posible?
