<?php
/*
 * Boletín 01, ejercicio 1: suma, resta, producto, cociente y resto de dos números
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-B01-E01.html)
 *
 * Es el autómata de los ejemplos del repositorio (Boletín 01-E01, no incluido en este material) con lo que le
 * faltaba: el predicado PL2 (el segundo número no es 0), el cociente que pide el enunciado y un evento
 * saliente para cuando no se puede dividir. Se conserva su numeración: lo nuevo va a continuación.
 */

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Arranque / inicio
const ST1 = 1;          // Atención de peticiones
const EE0 = 0;          // Enviar
const EE1 = 1;          // Reset (o primera carga de la página, sin botón)

// ----- Tabla de eventos-estados ampliada ---------------------------------------------------------
$automata = [
    ST0 => [EE0 => 1, EE1 => 1],
    ST1 => [EE0 => 2, EE1 => 1],
];

function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/**
 * AC5 y AC6: el texto es un entero que cabe en un int de PHP (sin PHP_INT_MIN). Con la expresión regular de
 * formatoEnteroValido() de los ejemplos del repositorio, "99999999999999999999" pasaba y se convertía en PHP_INT_MAX sin avisar, e
 * intdiv(PHP_INT_MIN, -1) lanza ArithmeticError: FILTER_VALIDATE_INT con un rango evita las dos cosas.
 */
function enteroValido(string $texto): int|false
{
    return filter_var($texto, FILTER_VALIDATE_INT, ['options' => ['min_range' => -PHP_INT_MAX, 'max_range' => PHP_INT_MAX]]);
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function formulario(string $numero1, string $numero2, array $errores = []): void
{
    foreach (['numero1' => 'primer', 'numero2' => 'segundo'] as $campo => $orden) {
        $valor = $campo === 'numero1' ? $numero1 : $numero2;
        echo "<p><label for=\"$campo\">Introduzca el $orden número: </label>";
        echo "<input type=\"text\" id=\"$campo\" name=\"$campo\" value=\"" . e($valor) . '"> ';
        if (isset($errores[$campo])) {
            echo '<span class="error">' . e($errores[$campo]) . '</span>';
        }
        echo '</p>';
    }
}

/** ES0: formularioInicio (vacío). */
function es0(): void
{
    formulario('', '');
}

/** ES1: formularioResultados (las cinco operaciones). Suma, resta y producto pueden desbordar un int y ser float. */
function es1(string $texto1, string $texto2, int|float $suma, int|float $resta, int|float $producto, int $cociente, int $resto): void
{
    formulario($texto1, $texto2);
    echo "<p id=\"suma\">La suma es: $suma</p><p id=\"resta\">La resta es: $resta</p>";
    echo "<p id=\"producto\">El producto es: $producto</p>";
    echo "<p id=\"cociente\">El cociente es: $cociente</p><p id=\"resto\">El resto es: $resto</p>";
}

/** ES2: formularioConErrores (lo escrito y un aviso junto a cada campo no válido). */
function es2(string $texto1, string $texto2, array $errores): void
{
    formulario($texto1, $texto2, $errores);
}

/** ES3: formularioResultadosSinDivision (el segundo número es 0). */
function es3(string $texto1, string $texto2, int|float $suma, int|float $resta, int|float $producto): void
{
    formulario($texto1, $texto2);
    echo "<p id=\"suma\">La suma es: $suma</p><p id=\"resta\">La resta es: $resta</p>";
    echo "<p id=\"producto\">El producto es: $producto</p>";
    echo '<p class="error">El cociente y el resto no se pueden calcular: el segundo número es 0.</p>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;                                        // primera carga o estado manipulado
}
$evento = isset($_GET['botonEnviar']) && !isset($_GET['botonReset']) ? EE0 : EE1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín 01 - Ejercicio 1 (autómata)</title>
    <style>
        body { font-family: monospace; margin: 2em; }
        .error { color: #c0392b; }
    </style>
</head>
<body>
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
        // AEs PRE: AC5 y AC6 limpiar y validar los dos números
        $texto1 = trim((string) ($_GET['numero1'] ?? ''));
        $texto2 = trim((string) ($_GET['numero2'] ?? ''));
        $numero1 = enteroValido($texto1);
        $numero2 = enteroValido($texto2);
        $pl0 = $numero1 !== false;                         // PL0: numero1 es un entero válido
        $pl1 = $numero2 !== false;                         // PL1: numero2 es un entero válido

        if (!$pl0 || !$pl1) {                              // !PL0 || !PL1
            $estado = ST1;
            $errores = [];                                 // AC7: un aviso por campo no válido
            if (!$pl0) {
                $errores['numero1'] = 'Escriba un número entero';
            }
            if (!$pl1) {
                $errores['numero2'] = 'Escriba un número entero';
            }
            es2($texto1, $texto2, $errores);
        } elseif ($numero2 === 0) {                        // PL0 && PL1 && !PL2
            $estado = ST1;
            $suma = $numero1 + $numero2;                   // AC0
            $resta = $numero1 - $numero2;                  // AC1
            $producto = $numero1 * $numero2;               // AC2
            es3($texto1, $texto2, $suma, $resta, $producto);
        } else {                                           // PL0 && PL1 && PL2
            $estado = ST1;
            $suma = $numero1 + $numero2;                   // AC0
            $resta = $numero1 - $numero2;                  // AC1
            $producto = $numero1 * $numero2;               // AC2
            $resto = $numero1 % $numero2;                  // AC3
            $cociente = intdiv($numero1, $numero2);        // AC4
            es1($texto1, $texto2, $suma, $resta, $producto, $cociente, $resto);
        }
        break;
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
    <button type="submit" name="botonEnviar" value="Enviar">Enviar</button>
    <button type="submit" name="botonReset" value="Reset">Reset</button>
</form>
</body>
</html>
