<?php
/*
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
// constante: emergencias que se permiten antes de dejar el ascensor fuera de servicio
const MAX_EMERGENCIAS = 3;

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

// PL4 masCercanaArriba(array $ascensor): bool -> la llamada más cercana está arriba (a igual distancia, también)
function masCercanaArriba(array $ascensor): bool
{
    $arriba = PHP_INT_MAX;
    $abajo = PHP_INT_MAX;
    foreach ($ascensor['llamadas'] as $planta) {
        if ($planta > $ascensor['piso']) {
            $arriba = min($arriba, $planta - $ascensor['piso']);
        } elseif ($planta < $ascensor['piso']) {
            $abajo = min($abajo, $ascensor['piso'] - $planta);
        }
    }
    return $arriba <= $abajo && $arriba !== PHP_INT_MAX;
}

// hayLlamadas(array $ascensor, string $donde): bool -> ¿hay llamadas "arriba" o "abajo" del piso actual?
function hayLlamadas(array $ascensor, string $donde): bool
{
    foreach ($ascensor['llamadas'] as $planta) {
        if ($donde === 'arriba' ? $planta > $ascensor['piso'] : $planta < $ascensor['piso']) {
            return true;
        }
    }
    return false;
}

// AC5 mover(array &$ascensor, int $paso): sube (+1) o baja (-1) una planta y la cuenta
function mover(array &$ascensor, int $paso): void
{
    $ascensor['piso'] += $paso;
    $ascensor['recorridas']++;
}

// AC6 parar(array &$ascensor): quita la llamada de la planta en la que está y cuenta la parada
function parar(array &$ascensor): void
{
    $ascensor['llamadas'] = array_values(array_diff($ascensor['llamadas'], [$ascensor['piso']]));
    $ascensor['paradas']++;
}

// PL0 coherente(int $estado, array $ascensor): bool -> ¿son posibles estos datos EN ESTE ESTADO? (invariantes)
function coherente(int $estado, array $ascensor): bool
{
    // datos que valen en cualquier estado
    if ($ascensor['piso'] < 0 || $ascensor['piso'] > ULTIMA || $ascensor['recorridas'] < 0 || $ascensor['paradas'] < 0
        || $ascensor['emergencias'] < 0 || $ascensor['emergencias'] > MAX_EMERGENCIAS
        || ($estado !== ST5 && $ascensor['emergencias'] === MAX_EMERGENCIAS)      // con 3 solo se puede estar en ST5
        || !preg_match('/^0?1?2?3?4?$/D', $ascensor['textoLlamadas'])          // cifras de 0 a 4, ordenadas y sin repetir
        || !in_array($ascensor['direccion'], ['', 'sube', 'baja'], true)) {
        return false;
    }
    $aqui = in_array($ascensor['piso'], $ascensor['llamadas'], true);
    // lo que tiene que cumplirse en cada estado
    return match ($estado) {
        ST1 => !$aqui && $ascensor['direccion'] === '',                                         // parado
        ST2 => hayLlamadas($ascensor, 'arriba') && $ascensor['direccion'] === 'sube',           // subiendo
        ST3 => hayLlamadas($ascensor, 'abajo') && $ascensor['direccion'] === 'baja',            // bajando
        ST4 => !$aqui,                                                                          // puertas abiertas
        ST5 => $ascensor['llamadas'] === [] && $ascensor['emergencias'] >= 1,                   // emergencia
        default => false,
    };
}
