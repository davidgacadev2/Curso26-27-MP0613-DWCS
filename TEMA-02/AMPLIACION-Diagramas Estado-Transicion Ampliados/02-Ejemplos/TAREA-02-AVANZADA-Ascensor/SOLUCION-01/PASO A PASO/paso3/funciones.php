<?php
/*
 * >>> PASO 3 de 6: T2 Llamar con el ascensor parado: la planta del botón, añadir la llamada o abrir las puertas <<<
 *
 * TAREA-02 AVANZADA: El ascensor del instituto — funciones.php
 *  Las acciones (AC) y los predicados (PL) del autómata que necesitan más de una línea, sin HTML.
 *  El ascensor es un array:
 *    ['piso' => 2, 'direccion' => 'sube', 'llamadas' => [0, 4], 'emergencias' => 0, 'recorridas' => 7, 'paradas' => 3]
 *  'direccion' es '' (parado), 'sube' o 'baja'; 'llamadas' es la lista ordenada de plantas que esperan.
 */

// constantes: las plantas y sus nombres
const PLANTAS = ['Planta baja', '1.ª planta', '2.ª planta', '3.ª planta', '4.ª planta'];
const ULTIMA = 4;
// TODO: constante: emergencias que se permiten antes de dejar el ascensor fuera de servicio

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// AC0 ascensorInicial(): array -> en la planta baja, parado, sin llamadas y con los contadores a cero
function ascensorInicial(): array
{
    return ['piso' => 0, 'direccion' => '', 'llamadas' => [], 'emergencias' => 0, 'recorridas' => 0, 'paradas' => 0];
}

// AC1 leerAscensor(array $datos): array -> el ascensor de los campos ocultos (sin comprobar: eso es PL0)
function leerAscensor(array $datos): array
{
    $entero = fn(string $campo): int =>
        filter_var($datos[$campo] ?? '', FILTER_VALIDATE_INT) === false ? -1 : (int) $datos[$campo];
    $llamadas = is_string($datos['llamadas'] ?? null) ? $datos['llamadas'] : '';
    $direccion = is_string($datos['direccion'] ?? null) ? $datos['direccion'] : '';
    return [
        'piso' => $entero('piso'),
        'direccion' => $direccion,
        'llamadas' => $llamadas === '' ? [] : array_map('intval', str_split($llamadas)),
        'textoLlamadas' => $llamadas,
        'emergencias' => $entero('emergencias'),
        'recorridas' => $entero('recorridas'),
        'paradas' => $entero('paradas'),
    ];
}

// AC2 leerPlanta(array $datos): ?int -> la planta del botón "llamar:3" (null si no es válida); PL1 es "no es null"
function leerPlanta(array $datos): ?int
{
    $evento = is_string($datos['evento'] ?? null) ? $datos['evento'] : '';
    $planta = filter_var(substr($evento, strlen('llamar:')), FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => ULTIMA]]);
    return $planta === false ? null : $planta;
}

// AC3 anadirLlamada(array &$ascensor, int $planta): la añade a la lista (sin repetir y ordenada)
function anadirLlamada(array &$ascensor, int $planta): void
{
    if (!in_array($planta, $ascensor['llamadas'], true)) {
        $ascensor['llamadas'][] = $planta;
        sort($ascensor['llamadas']);
    }
}

// TODO: PL4 masCercanaArriba(array $ascensor): bool -> la llamada más cercana está arriba (a igual distancia, también)

// TODO: hayLlamadas(array $ascensor, string $donde): bool -> ¿hay llamadas "arriba" o "abajo" del piso actual?

// TODO: AC5 mover(array &$ascensor, int $paso): sube (+1) o baja (-1) una planta y la cuenta

// TODO: AC6 parar(array &$ascensor): quita la llamada de la planta en la que está y cuenta la parada

// TODO: PL0 coherente(int $estado, array $ascensor): bool -> ¿son posibles estos datos EN ESTE ESTADO? (invariantes)
