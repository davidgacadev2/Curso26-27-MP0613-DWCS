<?php
/*
 * Boletín 03, ejercicio 8: ¿qué día de la semana es una fecha?
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-B03-E08.html)
 *
 * Fíjate en el orden de los predicados de T2: PL3 ("el día existe en ese mes") solo se puede
 * calcular si PL0, PL1 y PL2 son ciertos, por eso va siempre detrás en un && (cortocircuito).
 */
const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Esperando una fecha
const EE0 = 0;          // Carga (la URL sin parámetros)
const EE1 = 1;          // Enviar
// EE2 Borrar es un <button type="reset">: lo resuelve el navegador (HTML/JS), no llega al servidor

// ----- Tabla de eventos-estados ampliada ---------------------------------------------------------
$automata = [
    ST0 => [EE0 => 1, EE1 => 1],          // (Enviar sin estado: la página se ha manipulado; empezamos de nuevo)
    ST1 => [EE0 => 1, EE1 => 2],
];

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// ----- Funciones auxiliares de los predicados y acciones ---------------------------------------
/** AC3: ¿es bisiesto? */
function esBisiesto(int $anio): bool
{
    return ($anio % 4 === 0 && $anio % 100 !== 0) || $anio % 400 === 0;
}

/** AC4: días que tiene un mes en un año. */
function diasDelMes(int $mes, int $anio): int
{
    return match ($mes) {
        2 => esBisiesto($anio) ? 29 : 28,
        4, 6, 9, 11 => 30,
        default => 31,
    };
}

/** AC6: fórmula de Michael Keith y Tom Craver: 0 = domingo ... 6 = sábado. */
function diaDeLaSemana(int $dia, int $mes, int $anio): int
{
    // (d+=m<3?y--:y-2,23*m/9+d+4+y/4-y/100+y/400)%7   en C, con división entera
    if ($mes < 3) {
        $dia += $anio;                     // y-- : se suma el año y DESPUÉS se resta 1
        $anio--;
    } else {
        $dia += $anio - 2;
    }
    return (intdiv(23 * $mes, 9) + $dia + 4 + intdiv($anio, 4) - intdiv($anio, 100) + intdiv($anio, 400)) % 7;
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function formulario(array $valores, array $errores): void
{
    $campos = ['anio' => 'Año (1-3000)', 'mes' => 'Mes (1-12)', 'dia' => 'Día (1-31)'];
    foreach ($campos as $nombre => $etiqueta) {
        echo '<p><label for="' . $nombre . '">' . $etiqueta . ': </label>';
        echo '<input type="text" id="' . $nombre . '" name="' . $nombre . '" value="' . e($valores[$nombre] ?? '') . '"> ';
        if (isset($errores[$nombre])) {
            echo '<span class="error">' . e($errores[$nombre]) . '</span>';
        }
        echo '</p>';
    }
}

/** ES0: formulario vacío. */
function es0(): void
{
    formulario([], []);
}

/** ES1: formulario con el aviso de cada campo fuera de rango. */
function es1(array $valores, array $errores): void
{
    formulario($valores, $errores);
}

/** ES2: formulario con el aviso de que ese día no existe en ese mes. */
function es2(array $valores, int $mes, int $anio, int $dias): void
{
    formulario($valores, []);
    echo '<p class="error">El mes ' . $mes . ' del año ' . $anio . ' solo tiene ' . $dias . ' días.</p>';
}

/** ES3: formulario y el día de la semana. */
function es3(array $valores, int $dia, int $mes, int $anio, string $nombreDia): void
{
    formulario($valores, []);
    echo '<p class="resultado">El ' . $dia . '/' . $mes . '/' . $anio . ' es <strong>' . e($nombreDia) . '</strong>.</p>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
$evento = isset($_GET['enviar']) ? EE1 : EE0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín 03 - Ejercicio 8 (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .error { color: #c0392b; }
        .resultado { background: #dbe9fb; padding: 4px 8px; display: inline-block; }
    </style>
</head>
<body>
<h1>¿Qué día de la semana es?</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
switch ($automata[$estado][$evento]) {
    // ---- TRANSICIÓN T1 -----------------------------------------------------------------------
    case 1:
        $estado = ST1;
        es0();
        break;

    // ---- TRANSICIÓN T2 -----------------------------------------------------------------------
    case 2:
        // AEs PRE: AC0, AC1, AC2 limpiar y validar año, mes y día
        $valores = [];
        foreach (['anio', 'mes', 'dia'] as $campo) {
            $valores[$campo] = trim((string) ($_GET[$campo] ?? ''));
        }
        $anio = filter_var($valores['anio'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3000]]);
        $mes = filter_var($valores['mes'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
        $dia = filter_var($valores['dia'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 31]]);
        $pl0 = $anio !== false;                                // PL0: año entero entre 1 y 3000
        $pl1 = $mes !== false;                                 // PL1: mes entero entre 1 y 12
        $pl2 = $dia !== false;                                 // PL2: día entero entre 1 y 31

        if (!$pl0 || !$pl1 || !$pl2) {                         // !PL0 || !PL1 || !PL2
            $estado = ST1;
            $errores = [];                                     // AC5: avisos de los campos fuera de rango
            if (!$pl0) {
                $errores['anio'] = 'El año debe ser un entero entre 1 y 3000';
            }
            if (!$pl1) {
                $errores['mes'] = 'El mes debe ser un entero entre 1 y 12';
            }
            if (!$pl2) {
                $errores['dia'] = 'El día debe ser un entero entre 1 y 31';
            }
            es1($valores, $errores);
        } elseif ($dia > diasDelMes($mes, $anio)) {            // PL0 && PL1 && PL2 && !PL3
            $estado = ST1;
            es2($valores, $mes, $anio, diasDelMes($mes, $anio));
        } else {                                               // PL0 && PL1 && PL2 && PL3
            $estado = ST1;
            $diaSemana = diaDeLaSemana($dia, $mes, $anio);     // AC6
            $nombreDia = DIAS[$diaSemana];                     // AC7
            es3($valores, $dia, $mes, $anio, $nombreDia);
        }
        break;
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
    <button type="submit" name="enviar" value="1">Enviar</button>
    <button type="reset">Borrar</button>
</form>
</body>
</html>
