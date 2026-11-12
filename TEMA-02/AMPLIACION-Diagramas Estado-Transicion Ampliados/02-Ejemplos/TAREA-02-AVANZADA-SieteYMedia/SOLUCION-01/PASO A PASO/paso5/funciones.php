<?php
/*
 * >>> PASO 5 de 6: T6 siguiente mano, T7 retirarse y el final de la partida sin fichas <<<
 *
 * TAREA-02 AVANZADA: Siete y media — funciones.php
 *  Las acciones (AC) y los predicados (PL) del autómata que necesitan más de una línea, sin HTML.
 *  La partida es un array:
 *    ['saldo' => 100, 'apuesta' => 10, 'semilla' => 1234, 'manos' => 3, 'cartas' => 2, 'ganadas' => 1, 'perdidas' => 1]
 *  Las cartas NO viajan en la página: el mazo de cada mano se baraja a partir de la semilla y del número de mano
 *  con una clave que solo conoce el servidor (hash_hmac). El jugador tiene las 'cartas' primeras del mazo y la
 *  banca, las siguientes.
 */

// constantes: la baraja española de 40 cartas, el dinero y el límite de 7 y media
const PALOS = ['oros', 'copas', 'espadas', 'bastos'];
const VALORES = [1, 2, 3, 4, 5, 6, 7, 10, 11, 12];
const FIGURAS = [1 => 'As', 10 => 'Sota', 11 => 'Caballo', 12 => 'Rey'];
const SALDO_INICIAL = 100;
const APUESTA_MINIMA = 5;
const APUESTA_MAXIMA = 50;
const LIMITE = 7.5;
const CLAVE = 'BK-Programacion-clave-de-la-baraja';

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int|float $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// AC0 partidaNueva(int $semilla): array -> 100 fichas y ninguna mano jugada
function partidaNueva(int $semilla): array
{
    return ['saldo' => SALDO_INICIAL, 'apuesta' => 0, 'semilla' => $semilla, 'manos' => 0, 'cartas' => 0,
        'ganadas' => 0, 'perdidas' => 0];
}

// AC1 leerPartida(array $datos): array -> la partida de los campos ocultos (sin comprobar: eso es PL0)
function leerPartida(array $datos): array
{
    $partida = [];
    foreach (['saldo', 'apuesta', 'semilla', 'manos', 'cartas', 'ganadas', 'perdidas'] as $campo) {
        $valor = filter_var($datos[$campo] ?? '', FILTER_VALIDATE_INT);
        $partida[$campo] = $valor === false ? -1 : $valor;
    }
    return $partida;
}

// AC2 leerApuesta(array $datos, int $saldo): ?int -> la apuesta (de 5 a 50 y no más que el saldo) o null; PL1
function leerApuesta(array $datos, int $saldo): ?int
{
    $apuesta = filter_var($datos['importe'] ?? '', FILTER_VALIDATE_INT,
        ['options' => ['min_range' => APUESTA_MINIMA, 'max_range' => min(APUESTA_MAXIMA, $saldo)]]);
    return $apuesta === false ? null : $apuesta;
}

// mazo(array $partida): array -> las 40 cartas [palo, valor] barajadas para esta mano (siempre igual para la misma mano)
function mazo(array $partida): array
{
    $cartas = [];
    foreach (array_keys(PALOS) as $palo) {
        foreach (VALORES as $valor) {
            $cartas[] = [$palo, $valor];
        }
    }
    mt_srand(crc32(hash_hmac('sha256', $partida['semilla'] . '-' . $partida['manos'], CLAVE)));
    shuffle($cartas);
    return $cartas;
}

// puntos(array $cartas): float -> del 1 al 7, lo que marcan; sota, caballo y rey, medio punto
function puntos(array $cartas): float
{
    $total = 0.0;
    foreach ($cartas as [$palo, $valor]) {
        $total += $valor <= 7 ? $valor : 0.5;
    }
    return $total;
}

// nombreCarta(array $carta): string -> "7 de oros", "Rey de copas"
function nombreCarta(array $carta): string
{
    [$palo, $valor] = $carta;
    return (FIGURAS[$valor] ?? (string) $valor) . ' de ' . PALOS[$palo];
}

// textoPuntos(float $puntos): string -> 7.5 se escribe "7 y media"
function textoPuntos(float $puntos): string
{
    $entero = (int) floor($puntos);
    return $puntos - $entero > 0 ? ($entero === 0 ? 'media' : "$entero y media") : (string) $entero;
}

// cartasJugador(array $partida): array -> las primeras 'cartas' cartas del mazo de la mano
function cartasJugador(array $partida): array
{
    return array_slice(mazo($partida), 0, $partida['cartas']);
}

// AC5 juegaBanca(array $partida): array -> la banca roba cartas (las siguientes) mientras tenga menos que el jugador
function juegaBanca(array $partida): array
{
    $mazo = mazo($partida);
    $objetivo = puntos(cartasJugador($partida));
    $banca = [];
    $siguiente = $partida['cartas'];
    while (puntos($banca) < $objetivo) {                // la banca gana los empates: le basta con igualar
        $banca[] = $mazo[$siguiente];
        $siguiente++;
    }
    return $banca;
}

// TODO: PL0 partidaValida(int $estado, array $partida): bool -> ¿son posibles estos datos en este estado?
