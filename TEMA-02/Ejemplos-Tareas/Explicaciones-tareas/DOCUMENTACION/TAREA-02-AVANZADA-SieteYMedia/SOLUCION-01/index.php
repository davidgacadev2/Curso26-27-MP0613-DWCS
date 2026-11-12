<?php
/*
 *
 * TAREA-02 AVANZADA: Siete y media
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "DIAGRAMA ESTADO-TRANSICION/Automata-SieteYMedia.pdf")
 *  - 7 estados, 7 eventos entrantes (uno INTERNO) y 7 transiciones; una única página que se envía a sí misma
 *    por POST; la partida viaja en campos ocultos (sin $_SESSION), las cartas no: se calculan;
 *  - ST3, el turno de la banca, es un estado TRANSITORIO: cuando una transición termina en ST3, el programa
 *    atiende enseguida el evento interno (T5) y la banca juega en la misma petición. ST3 nunca llega al navegador.
 */
// cargar las acciones y los predicados
require __DIR__ . '/funciones.php';

// estados (ST) y eventos entrantes (EE) del autómata
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Apuesta (esperando la apuesta de la mano)
const ST2 = 2;          // Turno del jugador
const ST3 = 3;          // Turno de la banca (transitorio)
const ST4 = 4;          // Mano terminada
const ST5 = 5;          // Sin fichas
const ST6 = 6;          // Retirado
const EE0 = 0;          // Carga (también "Nueva partida")
const EE1 = 1;          // Apostar
const EE2 = 2;          // Pedir carta
const EE3 = 3;          // Plantarse
const EE4 = 4;          // Interno: turno de la banca (lo genera el programa)
const EE5 = 5;          // Siguiente mano
const EE6 = 6;          // Retirarse
const EVENTOS = ['apostar' => EE1, 'pedir' => EE2, 'plantarse' => EE3, 'siguiente' => EE5, 'retirarse' => EE6];

// tabla de eventos-estados ampliada: $automata[estado][evento] = transición (null = "---")
//                Carga Apostar Pedir Plantarse Interno Siguiente Retirarse
$automata = [
    ST0 => [1, 1, 1, 1, null, 1, 1],
    ST1 => [1, 2, null, null, null, null, 7],
    ST2 => [1, null, 3, 4, null, null, null],
    ST3 => [null, null, null, null, 5, null, null],
    ST4 => [1, null, null, null, null, 6, 7],
    ST5 => [1, null, null, null, null, null, null],
    ST6 => [1, null, null, null, null, null, null],
];

// ----- Eventos salientes (ES) --------------------------------------------------------------------------------

// mano(string $titulo, array $cartas): las cartas de un jugador y sus puntos
function mano(string $titulo, array $cartas): void
{
    echo '<div class="mano"><h3>' . e($titulo) . ': ' . e(textoPuntos(puntos($cartas))) . '</h3>';
    foreach ($cartas as $carta) {
        echo '<span class="carta palo-' . $carta[0] . '">' . e(nombreCarta($carta)) . '</span>';
    }
    echo '</div>';
}

// resumen(array $partida): el saldo y las manos ganadas y perdidas
function resumen(array $partida): void
{
    echo '<p class="saldo">Fichas: <strong>' . $partida['saldo'] . '</strong> · Manos: ' . $partida['manos']
        . ' (ganadas ' . $partida['ganadas'] . ', perdidas ' . $partida['perdidas'] . ')</p>';
}

// ES0 / ES1: pedir la apuesta de la mano (ES1, con un aviso)
function es0(array $partida, string $aviso = ''): void
{
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    resumen($partida);
    echo '<p><label>Apuesta (' . APUESTA_MINIMA . '-' . min(APUESTA_MAXIMA, $partida['saldo']) . ' fichas): '
        . '<input name="importe" size="4" value="10"></label> <button name="evento" value="apostar">Repartir</button></p>';
    // retirarse con las fichas que se tienen
    echo '<p><button name="evento" value="retirarse">Retirarse</button></p>';
}

// ES2: le toca al jugador: sus cartas y los botones Pedir carta y Plantarse
function es2(array $partida, string $aviso = ''): void
{
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    echo '<p>Mano ' . $partida['manos'] . ' · apuesta: <strong>' . $partida['apuesta'] . '</strong> fichas</p>';
    mano('Tus cartas', cartasJugador($partida));
    echo '<p><button name="evento" value="pedir">Pedir carta</button> <button name="evento" value="plantarse">Plantarse</button></p>';
    resumen($partida);
}

// ES3 / ES4: la mano ha terminado (perdida o ganada): las cartas de los dos, el resultado y el saldo
function esFinMano(array $partida, array $banca, string $mensaje, string $clase): void
{
    mano('Tus cartas', cartasJugador($partida));
    if ($banca !== []) {
        mano('La banca', $banca);
    }
    echo '<p class="resultado ' . $clase . '">' . e($mensaje) . '</p>';
    resumen($partida);
    // los botones para seguir o retirarse
    echo '<p><button name="evento" value="siguiente">Siguiente mano</button> <button name="evento" value="retirarse">Retirarse</button></p>';
}

// ES5 / ES6: sin fichas o retirado: el resumen de la partida y el botón para empezar otra
function esFinPartida(array $partida, string $mensaje): void
{
    echo '<p class="resultado final">' . e($mensaje) . '</p>';
    resumen($partida);
    echo '<p><button name="evento" value="nueva">Nueva partida</button></p>';
}

// AC9 guardar(int $estado, array $partida): el estado y la partida en campos ocultos
function guardar(int $estado, array $partida): void
{
    echo '<input type="hidden" name="estado" value="' . $estado . '">';
    foreach ($partida as $campo => $valor) {
        echo '<input type="hidden" name="' . $campo . '" value="' . e($valor) . '">';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siete y media</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Siete y media</h1>
<form method="post">
<?php
// 1) el estado (campo oculto, validado; ST3 no puede llegar del navegador), el evento y la transición
$estado = filter_var($_POST['estado'] ?? '', FILTER_VALIDATE_INT);
if ($estado === false || !isset($automata[$estado]) || $estado === ST3) {
    $estado = ST0;
}
$evento = EVENTOS[is_string($_POST['evento'] ?? null) ? $_POST['evento'] : ''] ?? EE0;
$transicion = $automata[$estado][$evento] ?? 1;                 // un evento imposible ("---") se trata como T1
$aviso = '';

// 2) antes de cualquier transición salvo T1: AC1 recuperar la partida y comprobarla (PL0)
$partida = partidaNueva(1);
if ($transicion !== 1) {
    $partida = leerPartida($_POST);
    // PL0: ¿son posibles estos datos en el estado en el que dicen estar?
    $pl0 = partidaValida($estado, $partida);
    if (!$pl0) {                                                // !PL0 -> como T1, pero con ES1 (aviso)
        $transicion = 1;
        $aviso = 'La partida recibida no es válida (¿se han modificado los campos ocultos?). Empieza otra.';
    }
}

// 3) la transición; si deja el autómata en ST3, se atiende enseguida el evento interno (T5)
do {
    switch ($transicion) {
        case 1:     // ---- T1: cualquier estado + Carga (o evento imposible, o !PL0) -> ST1, AC0, ES0 (ES1 con aviso)
        default:    //      (default: una transición que todavía no esté programada se trata igual)
            $estado = ST1;
            // para poder probar, la primera petición (sin estado) puede mandar la semilla; si no, una al azar
            $semilla = isset($_POST['estado']) ? false
                : filter_var($_POST['semilla'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $partida = partidaNueva($semilla !== false ? $semilla : random_int(1, 999999));
            es0($partida, $aviso);
            break;

        // T2: ST1 + Apostar: leer la apuesta y repartir la primera carta
        case 2:     // ---- T2: ST1 + Apostar. Antes: AC2 leer la apuesta
            $apuesta = leerApuesta($_POST, $partida['saldo']);
            $pl1 = $apuesta !== null;                               // PL1: apuesta válida
            if (!$pl1) {                                            // !PL1 -> ST1, ES1
                es0($partida, 'La apuesta debe ser un número entero de fichas entre ' . APUESTA_MINIMA . ' y '
                    . min(APUESTA_MAXIMA, $partida['saldo']) . '.');
            } else {                                                // PL1 -> ST2, AC3 nueva mano con una carta, ES2
                $estado = ST2;
                $partida['apuesta'] = $apuesta;
                $partida['manos']++;
                $partida['cartas'] = 1;
                es2($partida);
            }
            break;

        // T3: ST2 + Pedir carta; T4: ST2 + Plantarse; T5: ST3 + evento interno (juega la banca)
        case 3:     // ---- T3: ST2 + Pedir carta. Antes: AC4 dar una carta más
            $partida['cartas']++;
            $puntos = puntos(cartasJugador($partida));
            $pl2 = $puntos > LIMITE;                                // PL2: se pasa
            $pl3 = $puntos === LIMITE;                              // PL3: tiene 7 y media
            if ($pl2) {                                             // PL2 -> AC6 pierde la apuesta
                $partida['saldo'] -= $partida['apuesta'];
                $partida['perdidas']++;
                // PL4: sin fichas -> ST5, ES5
                if ($partida['saldo'] === 0) {
                    $estado = ST5;
                    esFinPartida($partida, 'Te has pasado (' . textoPuntos($puntos) . ') y te has quedado sin fichas.');
                    break;
                }
                $estado = ST4;                                      // PL2 && !PL4 -> ST4, ES3
                esFinMano($partida, [], 'Te has pasado (' . textoPuntos($puntos) . '): pierdes ' . $partida['apuesta'] . ' fichas.', 'perdida');
            } elseif ($pl3) {                                       // !PL2 && PL3 -> ST3 (se planta solo)
                $estado = ST3;
            } else {                                                // !PL2 && !PL3 -> ST2, ES2
                es2($partida);
            }
            break;

        case 4:     // ---- T4: ST2 + Plantarse -> ST3
            $estado = ST3;
            break;

        case 5:     // ---- T5: ST3 + Interno. Antes: AC5 juega la banca
            $banca = juegaBanca($partida);
            $jugador = cartasJugador($partida);
            $pl5 = puntos($banca) > LIMITE;                         // PL5: la banca se pasa
            $pl6 = puntos($jugador) === LIMITE && count($jugador) === 2;    // PL6: siete y media "de mano"
            if ($pl5) {                                             // PL5 -> ST4, AC7 gana (doble si PL6), ES4
                $premio = $pl6 ? 2 * $partida['apuesta'] : $partida['apuesta'];
                $partida['saldo'] += $premio;
                $partida['ganadas']++;
                $estado = ST4;
                esFinMano($partida, $banca, 'La banca se pasa: ganas ' . $premio . ' fichas'
                    . ($pl6 ? ' (¡siete y media con dos cartas: paga doble!)' : '') . '.', 'ganada');
            } else {                                                // !PL5 -> AC6 pierde la apuesta
                $partida['saldo'] -= $partida['apuesta'];
                $partida['perdidas']++;
                // PL4: sin fichas -> ST5, ES5
                if ($partida['saldo'] === 0) {
                    $estado = ST5;
                    esFinPartida($partida, 'La banca gana con ' . textoPuntos(puntos($banca)) . ' y te has quedado sin fichas.');
                    break;
                }
                $estado = ST4;                                      // !PL5 && !PL4 -> ST4, ES3
                esFinMano($partida, $banca, 'La banca gana con ' . textoPuntos(puntos($banca)) . ': pierdes '
                    . $partida['apuesta'] . ' fichas.', 'perdida');
            }
            break;

        // T6: ST4 + Siguiente mano; T7: ST1/ST4 + Retirarse
        case 6:     // ---- T6: ST4 + Siguiente mano -> ST1, AC8 sin apuesta ni cartas, ES0
            $estado = ST1;
            $partida['apuesta'] = 0;
            $partida['cartas'] = 0;
            es0($partida);
            break;

        case 7:     // ---- T7: ST1/ST4 + Retirarse -> ST6, ES6
            $estado = ST6;
            $diferencia = $partida['saldo'] - SALDO_INICIAL;
            esFinPartida($partida, 'Te retiras con ' . $partida['saldo'] . ' fichas: '
                . ($diferencia >= 0 ? "ganas $diferencia." : 'pierdes ' . -$diferencia . '.'));
            break;
    }
    // evento interno: si la transición ha dejado el autómata en ST3, la tabla dice qué transición lo atiende (T5)
    $transicion = $estado === ST3 ? $automata[ST3][EE4] : null;
} while ($transicion !== null);

// 4) AC9: el nuevo estado y la partida viajan con la página
guardar($estado, $partida);
?>
</form>
</body>
</html>
