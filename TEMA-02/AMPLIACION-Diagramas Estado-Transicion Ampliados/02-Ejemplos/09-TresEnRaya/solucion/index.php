<?php
/*
 * Ejemplo 09: tres en raya contra el servidor
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-TresEnRaya.html)
 *
 * 7 estados, 7 eventos entrantes (uno de ellos INTERNO) y 7 transiciones. Después de la jugada del jugador el
 * autómata pasa a ST3 (turno de la máquina). ST3 no se envía nunca al navegador: en la misma petición se
 * atiende el evento interno "turno de la máquina" (T4), que hace la jugada del servidor. El bucle del final
 * del programa es el que "dispara" ese evento interno.
 */
const LINEAS = [[0, 1, 2], [3, 4, 5], [6, 7, 8], [0, 3, 6], [1, 4, 7], [2, 5, 8], [0, 4, 8], [2, 4, 6]];
const PREFERENCIA = [4, 0, 2, 6, 8, 1, 3, 5, 7];     // centro, esquinas y lados

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Configuración
const ST2 = 2;          // Turno del jugador
const ST3 = 3;          // Turno de la máquina (transitorio)
const ST4 = 4;          // Ganada
const ST5 = 5;          // Perdida
const ST6 = 6;          // Empate
const EE_INTERNO = 5;
const EVENTOS = ['carga' => 0, 'empezar' => 1, 'casilla' => 2, 'revancha' => 3, 'abandonar' => 4, 'configurar' => 6];

//                Carga Empezar Casilla Revancha Abandonar Interno Configurar
$automata = [
    ST0 => [1, 1, 1, 1, 1, null, 1],
    ST1 => [1, 2, null, null, null, null, null],
    ST2 => [1, null, 3, null, 6, null, null],
    ST3 => [null, null, null, null, null, 4, null],
    ST4 => [1, null, null, 5, null, null, 7],
    ST5 => [1, null, null, 5, null, null, 7],
    ST6 => [1, null, null, 5, null, null, 7],
];

function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function contraria(string $ficha): string
{
    return $ficha === 'X' ? 'O' : 'X';
}

/** ¿Hace tres en raya la ficha $ficha si se pone en $casilla? (PL5 y PL7) */
function haceTresEnRaya(string $tablero, int $casilla, string $ficha): bool
{
    $tablero[$casilla] = $ficha;
    foreach (LINEAS as [$a, $b, $c]) {
        if ($tablero[$a] === $ficha && $tablero[$b] === $ficha && $tablero[$c] === $ficha) {
            return true;
        }
    }
    return false;
}

/** ¿Se llena el tablero al poner una ficha más? (PL6 y PL8) */
function seLlena(string $tablero): bool
{
    return substr_count($tablero, '-') === 1;
}

/** AC9: la jugada de la máquina: ganar si puede, si no tapar la del jugador, si no centro, esquinas y lados. */
function jugadaMaquina(string $tablero, string $maquina): int
{
    foreach ([$maquina, contraria($maquina)] as $ficha) {
        foreach (PREFERENCIA as $casilla) {
            if ($tablero[$casilla] === '-' && haceTresEnRaya($tablero, $casilla, $ficha)) {
                return $casilla;
            }
        }
    }
    foreach (PREFERENCIA as $casilla) {
        if ($tablero[$casilla] === '-') {
            return $casilla;
        }
    }
    throw new LogicException('No quedan casillas');       // no ocurre: si estuviera lleno habría sido empate
}

/** AC1 y AC4: recupera la partida del campo oculto; null si no es coherente (PL3 falso). */
function recuperarPartida(array $datos, bool $enJuego): ?array
{
    $p = json_decode((string) ($datos['partida'] ?? ''), true);
    if (!is_array($p) || !in_array($p['ficha'] ?? '', ['X', 'O'], true) || !in_array($p['empieza'] ?? '', ['jugador', 'maquina'], true)
        || !is_string($p['tablero'] ?? null) || preg_match('/^[XO-]{9}$/', $p['tablero']) !== 1 || !is_array($p['marcador'] ?? null)) {
        return null;
    }
    foreach (['ganadas', 'perdidas', 'empates'] as $clave) {
        if (!is_int($p['marcador'][$clave] ?? null) || $p['marcador'][$clave] < 0) {
            return null;
        }
    }
    if ($enJuego) {                                       // en ST2 le toca al jugador: las fichas deben cuadrar
        $jugador = substr_count($p['tablero'], $p['ficha']);
        $maquina = substr_count($p['tablero'], contraria($p['ficha']));
        if ($maquina - $jugador !== ($p['empieza'] === 'maquina' ? 1 : 0)) {
            return null;
        }
    }
    return $p;
}

/** AC1: solo el marcador de las partidas anteriores (en ST1 y en T7 no hay partida); a cero si no es válido. */
function recuperarMarcador(array $datos): array
{
    $p = json_decode((string) ($datos['partida'] ?? ''), true);
    $marcador = ['ganadas' => 0, 'perdidas' => 0, 'empates' => 0];
    foreach (array_keys($marcador) as $clave) {
        $valor = $p['marcador'][$clave] ?? null;
        if (!is_int($valor) || $valor < 0) {
            return ['ganadas' => 0, 'perdidas' => 0, 'empates' => 0];
        }
        $marcador[$clave] = $valor;
    }
    return $marcador;
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function marcador(array $m): void
{
    echo "<p class=\"marcador\">Ganadas: {$m['ganadas']} · Perdidas: {$m['perdidas']} · Empates: {$m['empates']}</p>";
}

/** ES0 / ES1: configuración (con aviso opcional). */
function es0(array $marcador, string $aviso = ''): void
{
    echo '<h2>Nueva partida</h2><p>Su ficha: <label><input type="radio" name="ficha" value="X" checked> X</label> '
        . '<label><input type="radio" name="ficha" value="O"> O</label></p>';
    echo '<p>Empieza: <label><input type="radio" name="empieza" value="jugador" checked> usted</label> '
        . '<label><input type="radio" name="empieza" value="maquina"> la máquina</label></p>';
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    marcador($marcador);
    echo '<p><button type="submit" name="accion" value="empezar">Empezar</button></p>';
}

/** El tablero; con $jugar, las casillas libres son botones. */
function tablero(array $p, bool $jugar): void
{
    echo '<table class="tablero">';
    for ($fila = 0; $fila < 3; $fila++) {
        echo '<tr>';
        for ($col = 0; $col < 3; $col++) {
            $i = $fila * 3 + $col;
            $ficha = $p['tablero'][$i];
            if ($ficha === '-' && $jugar) {
                echo '<td><button type="submit" name="accion" value="casilla:' . $i . '">&nbsp;</button></td>';
            } else {
                echo '<td class="ficha">' . ($ficha === '-' ? '&nbsp;' : $ficha) . '</td>';
            }
        }
        echo '</tr>';
    }
    echo '</table>';
}

/** ES2: le toca al jugador (con aviso opcional). */
function es2(array $p, string $aviso = ''): void
{
    echo '<h2>Le toca: juega con ' . $p['ficha'] . '</h2>';
    tablero($p, true);
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    marcador($p['marcador']);
    echo '<p><button type="submit" name="accion" value="abandonar">Abandonar</button></p>';
}

/** ES3, ES4, ES5: fin de la partida. */
function fin(array $p, string $mensaje): void
{
    echo '<h2>' . e($mensaje) . '</h2>';
    tablero($p, false);
    marcador($p['marcador']);
    echo '<p><button type="submit" name="accion" value="revancha">Revancha</button> '
        . '<button type="submit" name="accion" value="configurar">Cambiar de ficha</button></p>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado]) || $estado === ST3) {
    $estado = ST0;                                        // ST3 nunca viaja al navegador
}
[$accion, $dato] = array_pad(explode(':', (string) ($_GET['accion'] ?? 'carga'), 2), 2, '');
$evento = EVENTOS[$accion] ?? EVENTOS['carga'];
$transicion = $automata[$estado][$evento] ?? 1;
$cero = ['ganadas' => 0, 'perdidas' => 0, 'empates' => 0];
$p = null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tres en raya (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .tablero td { width: 3em; height: 3em; border: 1px solid #555; text-align: center; font-size: 1.4em; }
        .tablero button { width: 100%; height: 100%; }
        .aviso { color: #c0392b; } .marcador { color: #555; }
    </style>
</head>
<body>
<h1>Tres en raya</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
do {
    $interno = false;
    switch ($transicion) {
        case 1:                                            // ---- T1 (Carga)
            $estado = ST1;
            $p = ['marcador' => $cero];                    // AC0
            es0($cero);
            break;

        case 2:                                            // ---- T2 (ST1, Empezar)
            $marcadorAnterior = recuperarMarcador($_GET);  // AC1: el marcador viene de partidas anteriores
            $ficha = (string) ($_GET['ficha'] ?? '');      // AC2: leer la configuración
            $empieza = (string) ($_GET['empieza'] ?? '');
            if (!in_array($ficha, ['X', 'O'], true) || !in_array($empieza, ['jugador', 'maquina'], true)) {   // !PL0 || !PL1
                $estado = ST1;
                $p = ['marcador' => $marcadorAnterior];
                es0($marcadorAnterior, 'Elija la ficha y quién empieza.');
            } else {
                $p = ['tablero' => '---------', 'ficha' => $ficha, 'empieza' => $empieza, 'marcador' => $marcadorAnterior];   // AC3
                if ($empieza === 'jugador') {              // PL0 && PL1 && !PL2
                    $estado = ST2;
                    es2($p);
                } else {                                   // PL0 && PL1 && PL2
                    $estado = ST3;                         // turno de la máquina: evento interno
                }
            }
            break;

        case 3:                                            // ---- T3 (ST2, Casilla)
            $p = recuperarPartida($_GET, true);            // AC4
            $casilla = filter_var($dato, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 8]]);   // AC5
            if ($p === null) {                             // !PL3
                $estado = ST1;
                $p = ['marcador' => $cero];
                es0($cero, 'La partida no era válida: empiece otra.');
            } elseif ($casilla === false || $p['tablero'][$casilla] !== '-') {   // PL3 && !PL4
                $estado = ST2;
                es2($p, 'Esa casilla no está libre.');
            } else {
                $pl5 = haceTresEnRaya($p['tablero'], $casilla, $p['ficha']);
                $pl6 = seLlena($p['tablero']);
                $p['tablero'][$casilla] = $p['ficha'];     // AC6: poner la ficha del jugador
                if ($pl5) {                                // PL3 && PL4 && PL5
                    $estado = ST4;
                    $p['marcador']['ganadas']++;           // AC7
                    fin($p, '¡Ha ganado!');
                } elseif ($pl6) {                          // PL3 && PL4 && !PL5 && PL6
                    $estado = ST6;
                    $p['marcador']['empates']++;           // AC8
                    fin($p, 'Empate');
                } else {                                   // PL3 && PL4 && !PL5 && !PL6
                    $estado = ST3;                         // turno de la máquina: evento interno
                }
            }
            break;

        case 4:                                            // ---- T4 (ST3, Interno: turno de la máquina)
            $maquina = contraria($p['ficha']);
            $jugada = jugadaMaquina($p['tablero'], $maquina);   // AEs PRE: AC9
            $pl7 = haceTresEnRaya($p['tablero'], $jugada, $maquina);
            $pl8 = seLlena($p['tablero']);
            $p['tablero'][$jugada] = $maquina;             // AC10: poner la ficha de la máquina
            if ($pl7) {                                    // PL7
                $estado = ST5;
                $p['marcador']['perdidas']++;              // AC11
                fin($p, 'Ha ganado la máquina');
            } elseif ($pl8) {                              // !PL7 && PL8
                $estado = ST6;
                $p['marcador']['empates']++;               // AC8
                fin($p, 'Empate');
            } else {                                       // !PL7 && !PL8
                $estado = ST2;
                es2($p);
            }
            break;

        case 5:                                            // ---- T5 (ST4, ST5 o ST6, Revancha)
            $p = recuperarPartida($_GET, false);           // AC1
            if ($p === null) {                             // !PL3
                $estado = ST1;
                $p = ['marcador' => $cero];
                es0($cero, 'La partida no era válida: empiece otra.');
            } else {
                $p['tablero'] = '---------';               // AC3 (con la misma configuración)
                if ($p['empieza'] === 'jugador') {         // PL3 && !PL2
                    $estado = ST2;
                    es2($p);
                } else {                                   // PL3 && PL2
                    $estado = ST3;
                }
            }
            break;

        case 6:                                            // ---- T6 (ST2, Abandonar)
            $p = recuperarPartida($_GET, true);            // AC4
            if ($p === null) {                             // !PL3
                $estado = ST1;
                $p = ['marcador' => $cero];
                es0($cero, 'La partida no era válida: empiece otra.');
            } else {                                       // PL3
                $estado = ST5;
                $p['marcador']['perdidas']++;              // AC11
                fin($p, 'Ha abandonado la partida');
            }
            break;

        case 7:                                            // ---- T7 (ST4, ST5 o ST6, Configurar)
            $estado = ST1;
            $p = ['marcador' => recuperarMarcador($_GET)];  // AC1
            es0($p['marcador']);
            break;
    }
    // Evento interno: si la máquina tiene el turno, se atiende ya, en esta misma petición
    if ($estado === ST3) {
        $transicion = $automata[ST3][EE_INTERNO];
        $interno = true;
    }
} while ($interno);

// AC12: la partida (o solo el marcador) viaja en un campo oculto
?>
    <input type="hidden" name="partida" value="<?= e(json_encode($p)) ?>">
    <input type="hidden" name="estado" value="<?= $estado ?>">
</form>
</body>
</html>
