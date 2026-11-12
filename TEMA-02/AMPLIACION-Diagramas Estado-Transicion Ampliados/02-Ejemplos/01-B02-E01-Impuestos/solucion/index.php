<?php
/*
 * Boletín 02, ejercicio 1: impuestos de las ventas de un mes
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-B02-E01.html)
 *
 * El programa sigue el autómata paso a paso:
 *   1. Se leen y comprueban el estado (campo oculto) y el evento entrante (botón pulsado).
 *   2. La tabla de eventos-estados dice qué transición toca: $automata[$estado][$evento].
 *   3. En cada transición (case): acciones previas (AEs PRE), predicados (if / else if),
 *      nuevo estado, acciones y eventos salientes (las funciones es0(), es1()...).
 */
const IMPUESTO_ESTATAL = 0.05;
const IMPUESTO_AUTONOMICO = 0.04;
const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre',
    'Noviembre', 'Diciembre'];

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Esperando datos
const EE0 = 0;          // Calcular
const EE1 = 1;          // Borrar (o primera carga de la página, sin parámetros)

// ----- Tabla de eventos-estados ampliada: $automata[estado][evento] = transición ----------------
$automata = [
    ST0 => [EE0 => 1, EE1 => 1],
    ST1 => [EE0 => 2, EE1 => 1],
];

/** htmlspecialchars corto. */
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/** Importe con dos decimales y coma: 2095,32 */
function euros(float $cantidad): string
{
    return number_format($cantidad, 2, ',', '.');
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
/** El formulario (común a ES0, ES1 y ES2). */
function formulario(string $importe, string $mes, array $errores): void
{
    echo '<p><label for="importe">Introduzca el importe mensual total de ventas: </label>';
    echo '<input type="text" id="importe" name="importe" value="' . e($importe) . '"> ';
    if (isset($errores['importe'])) {
        echo '<span class="error">' . e($errores['importe']) . '</span>';
    }
    echo '</p><p><label for="mes">Introduzca el nombre del mes: </label><select id="mes" name="mes">';
    foreach (MESES as $nombre) {
        $elegido = $nombre === $mes ? ' selected' : '';
        echo '<option value="' . e($nombre) . '"' . $elegido . '>' . e($nombre) . '</option>';
    }
    echo '</select> ';
    if (isset($errores['mes'])) {
        echo '<span class="error">' . e($errores['mes']) . '</span>';
    }
    echo '</p>';
}

/** ES0: formulario vacío. */
function es0(): void
{
    formulario('', MESES[0], []);
}

/** ES1: formulario con los datos escritos y el aviso de cada campo no válido. */
function es1(string $importe, string $mes, array $errores): void
{
    formulario($importe, $mes, $errores);
}

/** ES2: formulario y resultados. */
function es2(string $importe, string $mes, float $total, float $liquido, float $estatal, float $autonomico, float $impuestos): void
{
    formulario($importe, $mes, []);
    echo '<table class="resultados"><caption>' . e($mes) . '</caption>';
    echo '<tr><th>Total recaudado</th><td>' . euros($total) . '</td></tr>';
    echo '<tr><th>Total líquido</th><td>' . euros($liquido) . '</td></tr>';
    echo '<tr><th>Impuestos estatales (5 %)</th><td>' . euros($estatal) . '</td></tr>';
    echo '<tr><th>Impuestos autonómicos (4 %)</th><td>' . euros($autonomico) . '</td></tr>';
    echo '<tr><th>Impuestos totales</th><td>' . euros($impuestos) . '</td></tr>';
    echo '</table>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null) {
    $estado = ST0;                                        // sin campo oculto: primera carga
} elseif ($estado === false || !isset($automata[$estado])) {
    $estado = ST0;                                        // estado manipulado en el cliente: empezamos de nuevo
}
$evento = ($_GET['evento'] ?? '') === 'calcular' ? EE0 : EE1;   // cualquier otra cosa (o nada) cuenta como Borrar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín 02 - Ejercicio 1 (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .error { color: #c0392b; }
        .resultados th { text-align: left; padding-right: 2em; }
        .resultados td { text-align: right; background: #dbe9fb; padding: 2px 8px; }
    </style>
</head>
<body>
<h1>Impuestos de las ventas del mes</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
// ----- 2. Transición que toca según la tabla de eventos-estados -------------------------------
switch ($automata[$estado][$evento]) {
    // ---- TRANSICIÓN T1 -----------------------------------------------------------------------
    case 1:
        $estado = ST1;
        es0();
        break;

    // ---- TRANSICIÓN T2 -----------------------------------------------------------------------
    case 2:
        // AEs PRE: AC0 limpiar y validar el importe; AC1 validar el mes
        $importeEscrito = trim((string) ($_GET['importe'] ?? ''));
        $importe = filter_var(str_replace(',', '.', $importeEscrito), FILTER_VALIDATE_FLOAT);
        $mes = (string) ($_GET['mes'] ?? '');
        $pl0 = $importe !== false && $importe > 0;           // PL0: el importe es un número mayor que 0
        $pl1 = in_array($mes, MESES, true);                    // PL1: el mes es uno de los 12

        if (!$pl0 || !$pl1) {                                  // !PL0 || !PL1
            $estado = ST1;
            $errores = [];                                     // AC6: avisos de los campos no válidos
            if (!$pl0) {
                $errores['importe'] = 'Introduzca un importe mayor que 0';
            }
            if (!$pl1) {
                $errores['mes'] = 'Elija un mes de la lista';
            }
            es1($importeEscrito, $pl1 ? $mes : MESES[0], $errores);
        } else {                                               // PL0 && PL1
            $estado = ST1;
            $liquido = $importe / (1 + IMPUESTO_ESTATAL + IMPUESTO_AUTONOMICO);   // AC2
            $estatal = $liquido * IMPUESTO_ESTATAL;                                  // AC3
            $autonomico = $liquido * IMPUESTO_AUTONOMICO;                            // AC4
            $impuestos = $estatal + $autonomico;                                     // AC5
            es2($importeEscrito, $mes, $importe, $liquido, $estatal, $autonomico, $impuestos);
        }
        break;
}
?>
    <!-- Estado del autómata: viaja con el formulario -->
    <input type="hidden" name="estado" value="<?= $estado ?>">
    <button type="submit" name="evento" value="calcular">Calcular</button>
    <button type="submit" name="evento" value="borrar">Borrar</button>
</form>
</body>
</html>
