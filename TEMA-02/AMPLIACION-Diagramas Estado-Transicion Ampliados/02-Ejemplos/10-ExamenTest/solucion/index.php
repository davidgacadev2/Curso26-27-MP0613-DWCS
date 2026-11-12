<?php
/*
 * Ejemplo 10: examen tipo test
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-ExamenTest.html)
 *
 * 6 estados, 8 eventos entrantes y 8 transiciones (T3 tiene 7 caminos). Las preguntas se pueden saltar; al
 * llegar al final, las que quedan sin responder aparecen en una lista de revisión desde la que se puede volver a
 * cualquiera. En el modo "corrección inmediata" cada respuesta se corrige en el momento (estado ST3).
 */
const PREGUNTAS = [                     // [enunciado, opciones, índice de la correcta]  (comprobadas con PHP 8.2)
    ['¿Qué muestra echo 7 % 3;?', ['1', '2', '2.33', '0'], 0],
    ['¿Qué devuelve intdiv(7, 2)?', ['3.5', '3', '4', '2'], 1],
    ['¿Qué muestra var_dump("10" == "1e1");?', ['bool(true)', 'bool(false)', 'Un error', 'NULL'], 0],
    ['¿Qué devuelve strlen("ñ") con el fichero en UTF-8?', ['1', '3', '0', '2'], 3],
    ['¿Qué función convierte < > & y " en entidades HTML?', ['strip_tags', 'htmlspecialchars', 'addslashes', 'urlencode'], 1],
    ['¿Qué devuelve count([1, [2, 3], 4])?', ['4', '2', '3', '5'], 2],
    ['¿Qué devuelve array_sum([1, 2, "3"])?', ['6', '"123"', '3', 'Un error'], 0],
    ['¿Qué devuelve 1 <=> 2?', ['1', '0', '-1', 'true'], 2],
    ['¿Dónde están los datos de un formulario con method="get"?', ['$_POST', '$_SERVER', '$_GET', '$_COOKIE'], 2],
    ['En PHP 8, ¿qué muestra var_dump(0 == "a");?', ['bool(true)', 'Un error', 'Un aviso', 'bool(false)'], 3],
];
const LETRAS = ['a', 'b', 'c', 'd'];

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Configuración
const ST2 = 2;          // Respondiendo una pregunta
const ST3 = 3;          // Corrección de la pregunta (modo inmediato)
const ST4 = 4;          // Revisión de las pendientes
const ST5 = 5;          // Resultados
const EVENTOS = ['carga' => 0, 'comenzar' => 1, 'responder' => 2, 'saltar' => 3, 'continuar' => 4, 'ir' => 5,
    'entregar' => 6, 'repetir' => 7];

//                Carga Comenzar Responder Saltar Continuar IrA Entregar Repetir
$automata = [
    ST0 => [1, 1, 1, 1, 1, 1, 1, 1],
    ST1 => [1, 2, null, null, null, null, null, null],
    ST2 => [1, null, 3, 4, null, null, 7, null],
    ST3 => [1, null, null, null, 5, null, null, null],
    ST4 => [1, null, null, null, null, 6, 7, null],
    ST5 => [1, null, null, null, null, null, null, 8],
];

function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** AC1: recupera el examen del campo oculto; null si no es coherente (PL3 falso). */
function recuperarExamen(array $datos): ?array
{
    $x = json_decode((string) ($datos['examen'] ?? ''), true);
    if (!is_array($x) || !is_string($x['nombre'] ?? null) || !in_array($x['modo'] ?? '', ['inmediata', 'final'], true)
        || !in_array($x['total'] ?? null, [5, 10], true) || !is_array($x['respuestas'] ?? null)
        || count($x['respuestas']) !== $x['total'] || !is_int($x['actual'] ?? null) || $x['actual'] < 0 || $x['actual'] >= $x['total']) {
        return null;
    }
    foreach ($x['respuestas'] as $respuesta) {
        if ($respuesta !== null && !in_array($respuesta, [0, 1, 2, 3], true)) {
            return null;
        }
    }
    return $x;
}

/** La siguiente pregunta sin responder DESPUÉS de $desde (PL7), o null. */
function siguientePendiente(array $x, int $desde): ?int
{
    for ($i = $desde + 1; $i < $x['total']; $i++) {
        if ($x['respuestas'][$i] === null) {
            return $i;
        }
    }
    return null;
}

/** ¿Queda alguna pregunta sin responder? (PL8) */
function hayPendientes(array $x): bool
{
    return in_array(null, $x['respuestas'], true);
}

function aciertos(array $x): int
{
    $total = 0;
    foreach ($x['respuestas'] as $i => $respuesta) {
        $total += $respuesta === PREGUNTAS[$i][2] ? 1 : 0;
    }
    return $total;
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function es0(string $nombre = '', array $avisos = []): void
{
    echo '<h2>Examen de PHP</h2><p><label>Su nombre: <input type="text" name="nombre" value="' . e($nombre) . '"></label></p>';
    echo '<p>Preguntas: <label><input type="radio" name="total" value="5" checked> 5</label> <label><input type="radio" name="total" value="10"> 10</label></p>';
    echo '<p>Corrección: <label><input type="radio" name="modo" value="inmediata" checked> después de cada pregunta</label> '
        . '<label><input type="radio" name="modo" value="final"> al final</label></p>';
    foreach ($avisos as $aviso) {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    echo '<p><button type="submit" name="accion" value="comenzar">Comenzar</button></p>';
}

/** ES2: una pregunta (con aviso opcional). */
function es2(array $x, string $aviso = ''): void
{
    $i = $x['actual'];
    [$enunciado, $opciones] = PREGUNTAS[$i];
    $sin = count(array_filter($x['respuestas'], fn($r) => $r === null));
    echo '<h2>Pregunta ' . ($i + 1) . ' de ' . $x['total'] . '</h2><p class="enunciado">' . e($enunciado) . '</p>';
    foreach ($opciones as $n => $opcion) {
        echo '<p><label><input type="radio" name="opcion" value="' . $n . '"> ' . LETRAS[$n] . ') <code>' . e($opcion) . '</code></label></p>';
    }
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    echo "<p class=\"info\">Sin responder: $sin</p>";
    echo '<p><button type="submit" name="accion" value="responder">Responder</button> '
        . '<button type="submit" name="accion" value="saltar">Saltar</button> '
        . '<button type="submit" name="accion" value="entregar">Entregar ya</button></p>';
}

/** ES3: corrección inmediata de la pregunta que se acaba de responder. */
function es3(array $x, int $i, bool $correcta): void
{
    [$enunciado, $opciones, $buena] = PREGUNTAS[$i];
    echo '<h2>Pregunta ' . ($i + 1) . '</h2><p class="enunciado">' . e($enunciado) . '</p>';
    echo $correcta ? '<p class="ok">¡Correcto!</p>'
        : '<p class="aviso">Incorrecto. La respuesta era ' . LETRAS[$buena] . ') <code>' . e($opciones[$buena]) . '</code></p>';
    echo '<p><button type="submit" name="accion" value="continuar">Continuar</button></p>';
}

/** ES4: revisión de las preguntas sin responder. */
function es4(array $x, string $aviso = ''): void
{
    echo '<h2>Preguntas sin responder</h2><ul>';
    foreach ($x['respuestas'] as $i => $respuesta) {
        if ($respuesta === null) {
            echo '<li><button type="submit" name="accion" value="ir:' . $i . '">Pregunta ' . ($i + 1) . '</button> ' . e(PREGUNTAS[$i][0]) . '</li>';
        }
    }
    echo '</ul>';
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    echo '<p><button type="submit" name="accion" value="entregar">Entregar así</button></p>';
}

/** ES5: resultados. */
function es5(array $x): void
{
    $aciertos = aciertos($x);
    $nota = round($aciertos * 10 / $x['total'], 1);
    $resultado = $nota >= 5 ? 'Aprobado' : 'Suspenso';
    echo '<h2>Resultados de ' . e($x['nombre']) . '</h2>';
    echo "<p class=\"nota\">Aciertos: $aciertos de {$x['total']} · Nota: " . number_format($nota, 1, ',', '') . " · $resultado</p><table>";
    foreach ($x['respuestas'] as $i => $respuesta) {
        [$enunciado, $opciones, $buena] = PREGUNTAS[$i];
        $suya = $respuesta === null ? 'sin responder' : LETRAS[$respuesta] . ') ' . $opciones[$respuesta];
        $marca = $respuesta === $buena ? '✔' : '✘';
        echo '<tr><td>' . ($i + 1) . '</td><td>' . e($enunciado) . '</td><td>' . e($suya) . '</td><td>' . $marca . '</td></tr>';
    }
    echo '</table><p><button type="submit" name="accion" value="repetir">Hacer otro examen</button></p>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
[$accion, $dato] = array_pad(explode(':', (string) ($_GET['accion'] ?? 'carga'), 2), 2, '');
$evento = EVENTOS[$accion] ?? EVENTOS['carga'];
$transicion = $automata[$estado][$evento] ?? 1;
$x = in_array($transicion, [3, 4, 5, 6, 7, 8], true) ? recuperarExamen($_GET) : null;   // AC1
if (in_array($transicion, [3, 4, 5, 6, 7], true) && $x === null) {   // !PL3
    $transicion = 0;
}
$examen = null;                                            // lo que se guarda en el campo oculto
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Examen tipo test (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .aviso { color: #c0392b; } .ok { color: #2e7d32; font-weight: bold; } .info { color: #777; }
        table { border-collapse: collapse; } td { border: 1px solid #ccc; padding: 3px 8px; }
    </style>
</head>
<body>
<h1>Examen tipo test</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
switch ($transicion) {
    case 0:                                                // ---- !PL3 en T3..T7
        $estado = ST1;
        es0('', ['El examen no era válido: empiece otro.']);
        break;

    case 1:                                                // ---- T1 (Carga)
        $estado = ST1;
        es0();
        break;

    case 2:                                                // ---- T2 (ST1, Comenzar)
        $nombre = trim((string) ($_GET['nombre'] ?? ''));  // AC2: leer la configuración
        $total = filter_var($_GET['total'] ?? '', FILTER_VALIDATE_INT);
        $modo = (string) ($_GET['modo'] ?? '');
        $pl0 = preg_match('/^[\p{L} .\'-]{2,60}$/u', $nombre) === 1;
        $pl1 = in_array($total, [5, 10], true);
        $pl2 = in_array($modo, ['inmediata', 'final'], true);
        if (!$pl0 || !$pl1 || !$pl2) {                     // !PL0 || !PL1 || !PL2
            $estado = ST1;
            $avisos = [];
            if (!$pl0) {
                $avisos[] = 'Escriba su nombre.';
            }
            if (!$pl1 || !$pl2) {
                $avisos[] = 'Elija el número de preguntas y el tipo de corrección.';
            }
            es0($nombre, $avisos);
        } else {                                           // PL0 && PL1 && PL2
            $estado = ST2;
            $examen = ['nombre' => $nombre, 'modo' => $modo, 'total' => $total,   // AC3: preparar el examen
                'respuestas' => array_fill(0, $total, null), 'actual' => 0];
            es2($examen);
        }
        break;

    case 3:                                                // ---- T3 (ST2, Responder)
        $opcion = filter_var($_GET['opcion'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 3]]);   // AC4
        $i = $x['actual'];
        if ($opcion === false) {                           // PL3 && !PL4
            $estado = ST2;
            $examen = $x;
            es2($x, 'Elija una respuesta (o pulse Saltar).');
            break;
        }
        $x['respuestas'][$i] = $opcion;                    // AC5: guardar la respuesta
        $pl5 = $opcion === PREGUNTAS[$i][2];
        $siguiente = siguientePendiente($x, $i);
        if ($x['modo'] === 'inmediata') {                  // PL6
            $estado = ST3;                                 // PL3 && PL4 && PL6 && PL5 / !PL5
            $examen = $x;
            es3($x, $i, $pl5);
        } elseif ($siguiente !== null) {                   // PL3 && PL4 && !PL6 && PL7
            $estado = ST2;
            $x['actual'] = $siguiente;                     // AC6: ir a la siguiente pendiente
            $examen = $x;
            es2($x);
        } elseif (hayPendientes($x)) {                     // PL3 && PL4 && !PL6 && !PL7 && PL8
            $estado = ST4;
            $examen = $x;
            es4($x);
        } else {                                           // PL3 && PL4 && !PL6 && !PL7 && !PL8
            $estado = ST5;
            $examen = $x;
            es5($x);
        }
        break;

    case 4:                                                // ---- T4 (ST2, Saltar)
        $siguiente = siguientePendiente($x, $x['actual']);
        $examen = $x;
        if ($siguiente !== null) {                         // PL3 && PL7
            $estado = ST2;
            $examen['actual'] = $siguiente;                // AC6
            es2($examen);
        } else {                                           // PL3 && !PL7 (la actual queda pendiente: PL8 es cierto)
            $estado = ST4;
            es4($x);
        }
        break;

    case 5:                                                // ---- T5 (ST3, Continuar)
        $siguiente = siguientePendiente($x, $x['actual']);
        $examen = $x;
        if ($siguiente !== null) {                         // PL3 && PL7
            $estado = ST2;
            $examen['actual'] = $siguiente;                // AC6
            es2($examen);
        } elseif (hayPendientes($x)) {                     // PL3 && !PL7 && PL8
            $estado = ST4;
            es4($x);
        } else {                                           // PL3 && !PL7 && !PL8
            $estado = ST5;
            es5($x);
        }
        break;

    case 6:                                                // ---- T6 (ST4, IrA)
        $pedida = filter_var($dato, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $x['total'] - 1]]);
        $examen = $x;
        if ($pedida !== false && $x['respuestas'][$pedida] === null) {   // PL3 && PL9
            $estado = ST2;
            $examen['actual'] = $pedida;                   // AC7
            es2($examen);
        } else {                                           // PL3 && !PL9
            $estado = ST4;
            es4($x, 'Esa pregunta ya está respondida.');
        }
        break;

    case 7:                                                // ---- T7 (ST2 o ST4, Entregar)
        $estado = ST5;
        $examen = $x;
        es5($x);                                           // AC8: las que no se han respondido cuentan como fallo
        break;

    case 8:                                                // ---- T8 (ST5, Repetir)
        $estado = ST1;
        es0($x['nombre'] ?? '');                           // AC9: se conserva el nombre
        break;
}
if ($examen !== null) {
    echo '<input type="hidden" name="examen" value="' . e(json_encode($examen)) . '">';   // AC10
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
</form>
</body>
</html>
