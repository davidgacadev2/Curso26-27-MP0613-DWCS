<?php
/*
 * >>> PASO 2 de 6: el autómata en PHP: constantes, tabla de eventos-estados, estado y evento recibidos, T1 (formulario de configuración) y el estado en campos ocultos <<<
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

// TODO: AC2 validarConfiguracion(array $datos): array -> [configuración, errores]; PL0 es "no hay errores"

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

// TODO: codigoSecreto(array $partida): array -> el código (índices de color), siempre el mismo para la misma semilla

// TODO: AC1 leerPartida(array $datos): array -> la partida de los campos ocultos (sin comprobar: eso es PL1)

// TODO: AC5 leerIntento(array $datos, array $partida): array -> [intento o null, motivo]; PL2 es "intento no null"

// TODO: puntuar(array $intento, array $secreto): array -> [negras, blancas]: color y posición bien / solo color bien

// TODO: intentosRestantes(array $partida): int -> los permitidos menos los hechos (y menos uno si se pidió la pista)
    // TODO: la pista cuesta un intento

// textoIntentos(array $intentos): string -> [[0,1,2,3],[4,5,1,2]] pasa a "0123-4512" para el campo oculto
function textoIntentos(array $intentos): string
{
    return implode('-', array_map(fn(array $intento): string => implode('', $intento), $intentos));
}

// TODO: AC8 elegirPista(array $partida, array $secreto): int -> la primera posición que el jugador no ha acertado nunca

// TODO: PL1 partidaValida(array $partida): bool -> ¿es una partida posible en ST2 (jugando)?
