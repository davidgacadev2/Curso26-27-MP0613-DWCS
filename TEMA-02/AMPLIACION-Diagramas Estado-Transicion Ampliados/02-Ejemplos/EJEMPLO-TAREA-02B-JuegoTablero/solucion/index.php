<?php
/*
 * Tarea 02B: juego de tablero con letras ocultas
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-TAREA-02B.html)
 *
 * Tres eventos entrantes (cargar la página, Enviar la configuración y pulsar una casilla) y cinco
 * estados. Toda la partida (tablero, letras escondidas, palabra, puntos y fallos) viaja en campos
 * ocultos, así que T3 empieza comprobando que lo que vuelve del navegador es coherente (PL3).
 */
const LADO_MINIMO = 5;
const LADO_MAXIMO = 30;
const LETRAS_MINIMO = 5;
const PUNTOS_ACIERTO = 5;
const PUNTOS_FALLO = 2;
const FALLOS_MAXIMOS = 3;                   // se pierde al 4.º fallo seguido

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Configurando la partida
const ST2 = 2;          // Tablero nuevo (sin jugadas: no se ve el marcador)
const ST3 = 3;          // Jugando (con marcador)
const ST4 = 4;          // Fin de la partida
const EE0 = 0;          // Carga (la URL sin parámetros; también el enlace "Borrar")
const EE1 = 1;          // Enviar (lado y palabra)
const EE2 = 2;          // Jugada (clic en una casilla con asterisco)

// ----- Tabla de eventos-estados ampliada (null = el evento no puede ocurrir en ese estado) -------
$automata = [
    ST0 => [EE0 => 1, EE1 => 1, EE2 => 1],
    ST1 => [EE0 => 1, EE1 => 2, EE2 => null],
    ST2 => [EE0 => 1, EE1 => 2, EE2 => 3],
    ST3 => [EE0 => 1, EE1 => 2, EE2 => 3],
    ST4 => [EE0 => 1, EE1 => 2, EE2 => null],
];

function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** ¿La casilla (fila, columna) del tablero de lado $lado está dentro del rombo? (filas y columnas desde 0) */
function enRombo(int $fila, int $columna, int $lado): bool
{
    $medio = intdiv($lado, 2);
    return abs($columna - $medio) <= $medio - abs($fila - $medio);
}

// ----- Acciones (AC) -----------------------------------------------------------------------------
/** AC3: tablero con '*' en el rombo y 'o' fuera. */
function crearTablero(int $lado): array
{
    $tablero = [];
    for ($f = 0; $f < $lado; $f++) {
        for ($c = 0; $c < $lado; $c++) {
            $tablero[$f][$c] = enRombo($f, $c, $lado) ? '*' : 'o';
        }
    }
    return $tablero;
}

/** AC4: esconde las letras de la palabra bajo asteriscos elegidos al azar (y recorta la palabra si no caben). */
function esconderLetras(array $tablero, string &$palabra): array
{
    $huecos = [];
    foreach ($tablero as $f => $fila) {
        foreach ($fila as $c => $casilla) {
            if ($casilla === '*') {
                $huecos[] = [$f, $c];
            }
        }
    }
    shuffle($huecos);
    $palabra = substr($palabra, 0, count($huecos));
    $letras = [];
    foreach (str_split($palabra) as $i => $letra) {
        [$f, $c] = $huecos[$i];
        $letras[$f][$c] = $letra;
    }
    return $letras;
}

/** AC1: recupera la partida de los campos ocultos; null si no es coherente (página manipulada). */
function recuperarPartida(array $datos): ?array
{
    $lado = filter_var($datos['ladoPartida'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => LADO_MINIMO, 'max_range' => LADO_MAXIMO]]);
    $tablero = $datos['tablero'] ?? null;
    $letras = $datos['letras'] ?? [];
    $palabra = (string) ($datos['palabraPartida'] ?? '');
    if ($lado === false || $lado % 2 === 0 || !is_array($tablero) || !is_array($letras)) {
        return null;
    }
    $limpio = [];
    $escondidas = [];
    $pendientes = '';
    for ($f = 0; $f < $lado; $f++) {
        for ($c = 0; $c < $lado; $c++) {
            $casilla = $tablero[$f][$c] ?? null;
            $dentro = enRombo($f, $c, $lado);
            if (!is_string($casilla) || ($dentro ? !preg_match('/^(\*|[A-Za-z])$/', $casilla) : $casilla !== 'o')) {
                return null;
            }
            $limpio[$f][$c] = $casilla;
            $letra = $letras[$f][$c] ?? '';
            if ($letra !== '') {
                if (!$dentro || !is_string($letra) || !preg_match('/^[A-Za-z]$/', $letra)) {
                    return null;
                }
                $escondidas[$f][$c] = $letra;
                if ($casilla === '*') {
                    $pendientes .= $letra;
                }
            }
        }
    }
    // la palabra que queda debe tener exactamente las letras que siguen escondidas
    $ordenar = function (string $texto): string {
        $letrasTexto = str_split($texto);
        sort($letrasTexto);
        return implode('', $letrasTexto);
    };
    if ($palabra === '' || $ordenar($palabra) !== $ordenar($pendientes)) {
        return null;
    }
    return [
        'lado' => $lado,
        'tablero' => $limpio,
        'letras' => $escondidas,
        'palabra' => $palabra,
        'puntos' => (int) filter_var($datos['puntos'] ?? 0, FILTER_VALIDATE_INT),
        'fallos' => filter_var($datos['fallos'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => FALLOS_MAXIMOS]]),
    ];
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function formulario(string $lado, string $palabra, array $errores = []): void
{
    echo '<div id="entrada"><p><label for="lado">Longitud del lado del tablero (impar de 5 a 29): </label>';
    echo '<input type="text" id="lado" name="lado" size="3" value="' . e($lado) . '"></p>';
    echo '<p><label for="palabra">Palabra (5 letras o más, sin tildes ni ñ): </label>';
    echo '<input type="text" id="palabra" name="palabra" value="' . e($palabra) . '"></p>';
    foreach ($errores as $error) {
        echo '<p class="error">' . e($error) . '</p>';
    }
    echo '<button type="submit" name="enviar" value="1">Enviar</button> ';
    echo '<a href="' . e($_SERVER['PHP_SELF']) . '">Borrar</a></div>';
}

/** Dibuja el tablero; con $jugar, cada asterisco es un botón. Guarda la partida en campos ocultos (AC8). */
function tablero(array $p, bool $jugar): void
{
    echo '<table class="tablero">';
    foreach ($p['tablero'] as $f => $fila) {
        echo '<tr>';
        foreach ($fila as $c => $casilla) {
            if ($casilla === 'o') {
                echo '<td>o</td>';
            } elseif ($casilla === '*') {
                $desactivado = $jugar ? '' : ' disabled';
                echo "<td><button type=\"submit\" name=\"jugada\" value=\"{$f}x{$c}\"$desactivado>*</button></td>";
            } else {
                echo '<td><button type="button" class="letra" disabled>' . e($casilla) . '</button></td>';
            }
            echo "<input type=\"hidden\" name=\"tablero[$f][$c]\" value=\"" . e($casilla) . '">';
            if (isset($p['letras'][$f][$c])) {
                echo "<input type=\"hidden\" name=\"letras[$f][$c]\" value=\"" . e($p['letras'][$f][$c]) . '">';
            }
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '<input type="hidden" name="ladoPartida" value="' . $p['lado'] . '">';
    echo '<input type="hidden" name="palabraPartida" value="' . e($p['palabra']) . '">';
    echo '<input type="hidden" name="puntos" value="' . $p['puntos'] . '">';
    echo '<input type="hidden" name="fallos" value="' . $p['fallos'] . '">';
}

function marcador(array $p): void
{
    echo '<p class="marcador">Puntos: <strong>' . $p['puntos'] . '</strong> · Fallos seguidos: ' . $p['fallos'] . '</p>';
}

/** ES0: formulario de configuración vacío. */
function es0(): void
{
    formulario('', '');
}

/** ES1: formulario con los avisos. */
function es1(string $lado, string $palabra, array $errores): void
{
    formulario($lado, $palabra, $errores);
}

/** ES2: formulario y tablero nuevo, sin marcador. */
function es2(array $p): void
{
    formulario((string) $p['lado'], $p['palabra']);
    tablero($p, true);
}

/** ES3: formulario, marcador y tablero. */
function es3(array $p): void
{
    formulario((string) $p['lado'], $p['palabra']);
    marcador($p);
    tablero($p, true);
}

/** ES4: has ganado (tablero sin botones activos). */
function es4(array $p): void
{
    formulario((string) $p['lado'], '');
    marcador($p);
    echo '<p class="fin">FIN DEL JUEGO: ¡has descubierto todas las letras!</p>';
    tablero($p, false);
}

/** ES5: has perdido (tablero sin botones activos). */
function es5(array $p): void
{
    formulario((string) $p['lado'], $p['palabra']);
    marcador($p);
    echo '<p class="fin">FIN DEL JUEGO: ' . ($p['fallos']) . ' fallos seguidos.</p>';
    tablero($p, false);
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
$evento = isset($_GET['jugada']) ? EE2 : (isset($_GET['enviar']) ? EE1 : EE0);
$transicion = $automata[$estado][$evento] ?? 1;            // evento imposible (página manipulada): empezar de nuevo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarea 02B - Juego de tablero (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .error { color: #c0392b; }
        .tablero { border-collapse: collapse; margin-top: 1em; }
        .tablero td { width: 2em; height: 2em; text-align: center; }
        .tablero button { width: 2em; height: 2em; }
        .tablero button.letra { background: #c6efa0; font-weight: bold; color: #222; }
        .fin { font-weight: bold; color: #8e44ad; }
    </style>
</head>
<body>
<h1>Juego de tablero</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
switch ($transicion) {
    // ---- TRANSICIÓN T1 -----------------------------------------------------------------------
    case 1:
        $estado = ST1;
        es0();
        break;

    // ---- TRANSICIÓN T2 -----------------------------------------------------------------------
    case 2:
        // AEs PRE: AC0 limpiar y validar el lado y la palabra
        $ladoEscrito = trim((string) ($_GET['lado'] ?? ''));
        $palabra = trim((string) ($_GET['palabra'] ?? ''));
        $lado = filter_var($ladoEscrito, FILTER_VALIDATE_INT);
        $pl0 = $lado !== false && $lado >= LADO_MINIMO && $lado <= LADO_MAXIMO;   // PL0: lado entre 5 y 30
        $pl1 = $lado !== false && $lado % 2 === 1;                                 // PL1: lado impar
        $pl2 = preg_match('/^[A-Za-z]{' . LETRAS_MINIMO . ',}$/', $palabra) === 1; // PL2: 5 letras o más
        if (!$pl0 || !$pl1 || !$pl2) {                         // !PL0 || !PL1 || !PL2
            $estado = ST1;
            $errores = [];                                     // AC9: avisos de configuración
            if (!$pl0) {
                $errores[] = 'El lado debe ser un número entre 5 y 30.';
            }
            if (!$pl1) {
                $errores[] = 'El lado debe ser un número impar.';
            }
            if (!$pl2) {
                $errores[] = 'La palabra debe tener 5 letras como mínimo (sin tildes, ñ ni espacios).';
            }
            es1($ladoEscrito, $palabra, $errores);
        } else {                                               // PL0 && PL1 && PL2
            $estado = ST2;
            $tablero = crearTablero($lado);                    // AC3
            $letras = esconderLetras($tablero, $palabra);      // AC4 (puede recortar $palabra)
            $partida = ['lado' => $lado, 'tablero' => $tablero, 'letras' => $letras, 'palabra' => $palabra,
                'puntos' => 0, 'fallos' => 0];                 // AC5
            es2($partida);
        }
        break;

    // ---- TRANSICIÓN T3 -----------------------------------------------------------------------
    case 3:
        $partida = recuperarPartida($_GET);                    // AEs PRE: AC1
        $jugada = sscanf((string) $_GET['jugada'], '%dx%d');   // AEs PRE: AC2
        [$f, $c] = is_array($jugada) ? $jugada : [null, null];
        // PL3: partida coherente y la casilla es un asterisco sin descubrir
        $pl3 = $partida !== null && $partida['fallos'] !== false && is_int($f) && is_int($c)
            && ($partida['tablero'][$f][$c] ?? '') === '*';
        if (!$pl3) {                                           // !PL3
            $estado = ST1;
            es0();
            break;
        }
        $pl4 = isset($partida['letras'][$f][$c]);              // PL4: la casilla esconde una letra
        $pl5 = strlen($partida['palabra']) === 1;              // PL5: es la última letra que quedaba
        $pl6 = $partida['fallos'] === FALLOS_MAXIMOS;          // PL6: ya llevaba 3 fallos seguidos
        if ($pl4) {
            $letra = $partida['letras'][$f][$c];               // AC6: acierto
            $partida['tablero'][$f][$c] = $letra;
            $partida['palabra'] = substr_replace($partida['palabra'], '', strpos($partida['palabra'], $letra), 1);
            $partida['puntos'] += PUNTOS_ACIERTO;
            $partida['fallos'] = 0;
            if (!$pl5) {                                       // PL3 && PL4 && !PL5
                $estado = ST3;
                es3($partida);
            } else {                                           // PL3 && PL4 && PL5
                $estado = ST4;
                es4($partida);
            }
        } else {
            $partida['puntos'] -= PUNTOS_FALLO;                // AC7: fallo
            $partida['fallos']++;
            if (!$pl6) {                                       // PL3 && !PL4 && !PL6
                $estado = ST3;
                es3($partida);
            } else {                                           // PL3 && !PL4 && PL6
                $estado = ST4;
                es5($partida);
            }
        }
        break;
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
</form>
</body>
</html>
