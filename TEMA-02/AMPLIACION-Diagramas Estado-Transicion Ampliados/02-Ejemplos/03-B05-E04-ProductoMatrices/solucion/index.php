<?php
/*
 * Boletín 05, ejercicio 4: producto de dos matrices
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-B05-E04.html)
 *
 * Dos formularios seguidos: primero las dimensiones (ST1) y después los datos de las dos matrices
 * (ST2). Las dimensiones viajan en campos ocultos entre una petición y la siguiente, así que en T3
 * se vuelven a validar: alguien las puede haber cambiado en el navegador.
 */
const MAXIMO = 10;

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Esperando las dimensiones
const ST2 = 2;          // Esperando los datos de A y B
const ST3 = 3;          // Producto mostrado
const EE0 = 0;          // Carga (la URL sin parámetros; también el enlace "Empezar de nuevo")
const EE1 = 1;          // Enviar

// ----- Tabla de eventos-estados ampliada (null = el evento no puede ocurrir en ese estado) -------
$automata = [
    ST0 => [EE0 => 1, EE1 => 1],
    ST1 => [EE0 => 1, EE1 => 2],
    ST2 => [EE0 => 1, EE1 => 3],
    ST3 => [EE0 => 1, EE1 => null],       // en ST3 no hay botón Enviar
];

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// ----- Acciones (AC) -----------------------------------------------------------------------------
/** AC0: lee y valida las dimensiones m, n, p, q: [valores escritos, valores válidos o false]. */
function leerDimensiones(array $datos): array
{
    $escritas = [];
    $validas = [];
    foreach (['m', 'n', 'p', 'q'] as $nombre) {
        $escritas[$nombre] = trim((string) ($datos[$nombre] ?? ''));
        $validas[$nombre] = filter_var($escritas[$nombre], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => MAXIMO]]);
    }
    return [$escritas, $validas];
}

/** AC1: lee las celdas de una matriz de $filas x $columnas: [texto de cada celda, número o false]. */
function leerMatriz(mixed $datos, int $filas, int $columnas): array
{
    $textos = [];
    $numeros = [];
    for ($i = 0; $i < $filas; $i++) {
        for ($j = 0; $j < $columnas; $j++) {
            $texto = is_array($datos) && isset($datos[$i][$j]) && is_string($datos[$i][$j]) ? trim($datos[$i][$j]) : '';
            $textos[$i][$j] = $texto;
            $numeros[$i][$j] = filter_var(str_replace(',', '.', $texto), FILTER_VALIDATE_FLOAT);
        }
    }
    return [$textos, $numeros];
}

/** ¿Son números todas las celdas? (PL2 y PL3) */
function todasNumeros(array $numeros): bool
{
    foreach ($numeros as $fila) {
        if (in_array(false, $fila, true)) {
            return false;
        }
    }
    return true;
}

/** AC3: producto matricial (m x n) · (n x q) = (m x q). */
function productoMatricial(array $a, array $b): array
{
    $producto = [];
    foreach ($a as $i => $filaA) {
        foreach (array_keys($b[0]) as $j) {
            $suma = 0;
            foreach ($filaA as $k => $valor) {
                $suma += $valor * $b[$k][$j];
            }
            $producto[$i][$j] = $suma;
        }
    }
    return $producto;
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function formularioDimensiones(array $valores, array $errores, string $aviso = ''): void
{
    $campos = ['m' => 'Filas de A', 'n' => 'Columnas de A', 'p' => 'Filas de B', 'q' => 'Columnas de B'];
    echo '<p>Dimensiones (de 1 a ' . MAXIMO . '):</p>';
    foreach ($campos as $nombre => $etiqueta) {
        echo '<p><label for="' . $nombre . '">' . $etiqueta . ': </label>';
        echo '<input type="text" id="' . $nombre . '" name="' . $nombre . '" size="3" value="' . e($valores[$nombre] ?? '') . '"> ';
        if (isset($errores[$nombre])) {
            echo '<span class="error">' . e($errores[$nombre]) . '</span>';
        }
        echo '</p>';
    }
    if ($aviso !== '') {
        echo '<p class="error">' . e($aviso) . '</p>';
    }
}

/** Parrilla de inputs de una matriz; marca en rojo las celdas no válidas. */
function parrilla(string $nombre, array $textos, array $numeros, int $filas, int $columnas): void
{
    echo '<table class="matriz"><caption>' . strtoupper($nombre) . " ($filas x $columnas)</caption>";
    for ($i = 0; $i < $filas; $i++) {
        echo '<tr>';
        for ($j = 0; $j < $columnas; $j++) {
            $texto = $textos[$i][$j] ?? '';
            $mal = isset($numeros[$i][$j]) && $numeros[$i][$j] === false ? ' class="mal"' : '';
            echo "<td><input type=\"text\" size=\"4\" name=\"{$nombre}[$i][$j]\" value=\"" . e($texto) . "\"$mal></td>";
        }
        echo '</tr>';
    }
    echo '</table>';
}

function mostrarMatriz(string $titulo, array $matriz): void
{
    echo '<table class="matriz"><caption>' . e($titulo) . '</caption>';
    foreach ($matriz as $fila) {
        echo '<tr>';
        foreach ($fila as $valor) {
            echo '<td class="valor">' . e((string) round($valor, 4)) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

/** AC4: guarda las dimensiones en campos ocultos. */
function ocultarDimensiones(array $d): void
{
    foreach (['m', 'n', 'p', 'q'] as $nombre) {
        echo "<input type=\"hidden\" name=\"$nombre\" value=\"{$d[$nombre]}\">";
    }
}

/** ES0: formulario de dimensiones vacío. */
function es0(): void
{
    formularioDimensiones([], []);
}

/** ES1: formulario de dimensiones con el aviso de cada campo no válido. */
function es1(array $escritas, array $errores): void
{
    formularioDimensiones($escritas, $errores);
}

/** ES2: formulario de dimensiones con el aviso de que no se pueden multiplicar. */
function es2(array $escritas, array $d): void
{
    formularioDimensiones($escritas, [], "No se pueden multiplicar: A tiene {$d['n']} columnas y B tiene {$d['p']} filas (deben ser iguales).");
}

/** ES3 (y ES4 con valores y celdas marcadas): las dos parrillas. */
function es3(array $d, array $textosA = [], array $numerosA = [], array $textosB = [], array $numerosB = []): void
{
    ocultarDimensiones($d);                                    // AC4
    parrilla('a', $textosA, $numerosA, $d['m'], $d['n']);
    parrilla('b', $textosB, $numerosB, $d['p'], $d['q']);
    if ($numerosA !== []) {
        echo '<p class="error">Las celdas en rojo no son números.</p>';
    }
}

/** ES5: las dos matrices y su producto. */
function es5(array $a, array $b, array $c): void
{
    mostrarMatriz('A', $a);
    echo '<span class="signo">×</span>';
    mostrarMatriz('B', $b);
    echo '<span class="signo">=</span>';
    mostrarMatriz('A × B', $c);
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
$evento = isset($_GET['enviar']) ? EE1 : EE0;
$transicion = $automata[$estado][$evento] ?? 1;            // evento imposible (página manipulada): empezar de nuevo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín 05 - Ejercicio 4 (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .error { color: #c0392b; }
        table.matriz { display: inline-table; border-collapse: collapse; margin: 0.5em 1em 0.5em 0; vertical-align: middle; }
        table.matriz td { padding: 2px; } td.valor { border: 1px solid #999; min-width: 3em; text-align: right; padding: 2px 6px; }
        input.mal { background: #f8d7d7; border: 1px solid #c0392b; }
        .signo { font-size: 1.6em; margin-right: 1em; vertical-align: middle; }
    </style>
</head>
<body>
<h1>Producto de dos matrices</h1>
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
        [$escritas, $d] = leerDimensiones($_GET);              // AEs PRE: AC0
        $pl0 = !in_array(false, $d, true);                     // PL0: m, n, p, q enteros entre 1 y 10
        if (!$pl0) {                                           // !PL0
            $estado = ST1;
            $errores = [];                                     // AC2: avisos de las dimensiones no válidas
            foreach ($d as $nombre => $valor) {
                if ($valor === false) {
                    $errores[$nombre] = 'Debe ser un entero entre 1 y ' . MAXIMO;
                }
            }
            es1($escritas, $errores);
        } elseif ($d['n'] !== $d['p']) {                       // PL0 && !PL1
            $estado = ST1;
            es2($escritas, $d);
        } else {                                               // PL0 && PL1
            $estado = ST2;
            es3($d);
        }
        break;

    // ---- TRANSICIÓN T3 -----------------------------------------------------------------------
    case 3:
        [, $d] = leerDimensiones($_GET);                       // AEs PRE: AC0 (de los campos ocultos)
        $pl0 = !in_array(false, $d, true);
        if (!$pl0 || $d['n'] !== $d['p']) {                    // !PL0 || !PL1: datos ocultos manipulados
            $estado = ST1;
            es0();
            break;
        }
        [$textosA, $numerosA] = leerMatriz($_GET['a'] ?? null, $d['m'], $d['n']);   // AEs PRE: AC1
        [$textosB, $numerosB] = leerMatriz($_GET['b'] ?? null, $d['p'], $d['q']);
        $pl2 = todasNumeros($numerosA);                        // PL2: todas las celdas de A son números
        $pl3 = todasNumeros($numerosB);                        // PL3: todas las celdas de B son números
        if (!$pl2 || !$pl3) {                                  // PL0 && PL1 && (!PL2 || !PL3)
            $estado = ST2;
            es3($d, $textosA, $numerosA, $textosB, $numerosB);  // ES4
        } else {                                               // PL0 && PL1 && PL2 && PL3
            $estado = ST3;
            $c = productoMatricial($numerosA, $numerosB);      // AC3
            es5($numerosA, $numerosB, $c);
        }
        break;
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
<?php if ($estado !== ST3): ?>
    <button type="submit" name="enviar" value="1">Enviar</button>
<?php endif; ?>
    <a href="<?= e($_SERVER['PHP_SELF']) ?>">Empezar de nuevo</a>
</form>
</body>
</html>
