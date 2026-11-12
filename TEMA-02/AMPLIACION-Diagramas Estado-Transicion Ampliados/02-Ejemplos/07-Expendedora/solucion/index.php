<?php
/*
 * Ejemplo 07: máquina expendedora
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-Expendedora.html)
 *
 * 7 estados, 9 eventos entrantes y 9 transiciones. La máquina (existencias, monedas del cajón para dar cambio,
 * producto elegido y monedas introducidas) viaja en un campo oculto en JSON; cada transición empieza
 * comprobando que es coherente (PL0). Si no lo es, la máquina se reinicia.
 */
const PRODUCTOS = [                     // código => [nombre, precio en céntimos]
    'agua' => ['Agua', 80],
    'cafe' => ['Café', 110],
    'zumo' => ['Zumo', 150],
    'bocadillo' => ['Bocadillo', 235],
];
const EXISTENCIAS_INICIALES = ['agua' => 3, 'cafe' => 2, 'zumo' => 0, 'bocadillo' => 1];
const MONEDAS = [200, 100, 50, 20, 10, 5];                         // las que acepta, de mayor a menor
const CAJON_INICIAL = [200 => 0, 100 => 0, 50 => 1, 20 => 1, 10 => 2, 5 => 2];
const CLAVE_MANTENIMIENTO = '2468';
const REPONER = 5;                      // al reponer: 5 unidades de cada producto y 5 monedas de cada tipo

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga (se enciende la máquina)
const ST1 = 1;          // Esperando selección
const ST2 = 2;          // Esperando el pago
const ST3 = 3;          // Producto entregado
const ST4 = 4;          // Dinero devuelto
const ST5 = 5;          // Pidiendo la clave de mantenimiento
const ST6 = 6;          // Mantenimiento
const EVENTOS = ['carga' => 0, 'elegir' => 1, 'moneda' => 2, 'cancelar' => 3, 'recoger' => 4,
    'mantenimiento' => 5, 'clave' => 6, 'reponer' => 7, 'cerrar' => 8];

//                Carga Elegir Moneda Cancelar Recoger Manten. Clave Reponer Cerrar
$automata = [
    ST0 => [1, 1, 1, 1, 1, 1, 1, 1, 1],
    ST1 => [1, 2, null, null, null, 6, null, null, null],
    ST2 => [1, null, 3, 4, null, null, null, null, null],
    ST3 => [1, null, null, null, 5, null, null, null, null],
    ST4 => [1, null, null, null, 5, null, null, null, null],
    ST5 => [1, null, null, 9, null, null, 7, null, null],
    ST6 => [1, null, null, null, null, null, null, 8, 9],
];

function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function euros(int $centimos): string
{
    return number_format($centimos / 100, 2, ',', '.') . ' €';
}

// ----- Acciones (AC) -----------------------------------------------------------------------------
/** AC0: la máquina recién encendida. */
function maquinaNueva(): array
{
    return ['existencias' => EXISTENCIAS_INICIALES, 'cajon' => CAJON_INICIAL, 'producto' => null, 'credito' => [], 'bandeja' => []];
}

/** AC1: recupera la máquina del campo oculto; null si no es coherente (PL0 falso). */
function recuperarMaquina(array $datos): ?array
{
    $m = json_decode((string) ($datos['maquina'] ?? ''), true);
    if (!is_array($m) || !isset($m['existencias'], $m['cajon']) || !is_array($m['existencias']) || !is_array($m['cajon'])
        || !array_key_exists('producto', $m) || !is_array($m['credito'] ?? null)) {
        return null;
    }
    if (array_keys($m['existencias']) !== array_keys(PRODUCTOS) || array_map('intval', array_keys($m['cajon'])) !== MONEDAS) {
        return null;
    }
    foreach ([...$m['existencias'], ...array_values($m['cajon'])] as $cantidad) {
        if (!is_int($cantidad) || $cantidad < 0 || $cantidad > 99) {
            return null;
        }
    }
    foreach ($m['credito'] as $moneda) {
        if (!in_array($moneda, MONEDAS, true)) {
            return null;
        }
    }
    if ($m['producto'] !== null && !isset(PRODUCTOS[$m['producto']])) {
        return null;
    }
    $m['cajon'] = array_combine(MONEDAS, array_values($m['cajon']));
    $m['bandeja'] = [];
    return $m;
}

/**
 * PL5: busca cómo dar $cambio con las monedas disponibles ([valor => cantidad]). Devuelve la lista de monedas
 * o null si no se puede. Prueba primero con las monedas grandes y, si no sale, retrocede (con monedas limitadas,
 * coger siempre la mayor no basta: 60 con una de 50 y tres de 20).
 */
function buscarCambio(int $cambio, array $disponibles, int $desde = 0): ?array
{
    if ($cambio === 0) {
        return [];
    }
    for ($i = $desde; $i < count(MONEDAS); $i++) {
        $moneda = MONEDAS[$i];
        if ($moneda <= $cambio && $disponibles[$moneda] > 0) {
            $disponibles[$moneda]--;
            $resto = buscarCambio($cambio - $moneda, $disponibles, $i);
            if ($resto !== null) {
                return [$moneda, ...$resto];
            }
            $disponibles[$moneda]++;
        }
    }
    return null;
}

function texto(array $monedas): string
{
    return $monedas === [] ? 'ninguna' : implode(' + ', array_map(fn($m) => euros($m), $monedas));
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
/** ES0 (con aviso opcional): elegir producto. */
function es0(array $m, string $aviso = ''): void
{
    echo '<h2>Elija un producto</h2><div class="productos">';
    foreach (PRODUCTOS as $codigo => [$nombre, $precio]) {
        $quedan = $m['existencias'][$codigo];
        $agotado = $quedan === 0 ? ' (agotado)' : '';
        echo '<button type="submit" name="accion" value="elegir:' . e($codigo) . '">'
            . e($nombre) . '<br>' . euros($precio) . $agotado . '</button> ';
    }
    echo '</div>';
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    echo '<p class="servicio"><button type="submit" name="accion" value="mantenimiento">Mantenimiento</button></p>';
}

/** ES1 (con aviso opcional): pagar el producto elegido. */
function es1(array $m, string $aviso = ''): void
{
    [$nombre, $precio] = PRODUCTOS[$m['producto']];
    $credito = array_sum($m['credito']);
    echo '<h2>' . e($nombre) . ': ' . euros($precio) . '</h2>';
    echo '<p>Ha introducido ' . euros($credito) . '. Faltan <strong>' . euros($precio - $credito) . '</strong>.</p><p>';
    foreach (MONEDAS as $moneda) {
        echo '<button type="submit" name="accion" value="moneda:' . $moneda . '">' . euros($moneda) . '</button> ';
    }
    echo '</p>';
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    echo '<p><button type="submit" name="accion" value="cancelar">Cancelar</button></p>';
}

/** ES2: producto entregado. */
function es2(array $m): void
{
    echo '<h2>Recoja su ' . e(PRODUCTOS[$m['entregado']][0]) . '</h2>';
    echo '<p>Cambio: ' . e(texto($m['bandeja'])) . ' (' . euros(array_sum($m['bandeja'])) . ')</p>';
    echo '<p><button type="submit" name="accion" value="recoger">Recoger</button></p>';
}

/** ES3: dinero devuelto. */
function es3(array $m, string $motivo): void
{
    echo '<h2>' . e($motivo) . '</h2>';
    echo '<p>Recoja su dinero: ' . e(texto($m['bandeja'])) . ' (' . euros(array_sum($m['bandeja'])) . ')</p>';
    echo '<p><button type="submit" name="accion" value="recoger">Recoger</button></p>';
}

/** ES4 (con aviso opcional): pedir la clave. */
function es4(): void
{
    echo '<h2>Mantenimiento</h2><p>Clave: <input type="password" name="clave" maxlength="8"></p>';
    echo '<p><button type="submit" name="accion" value="clave">Entrar</button> <button type="submit" name="accion" value="cancelar">Volver</button></p>';
}

/** ES5: panel de mantenimiento. */
function es5(array $m, string $aviso = ''): void
{
    echo '<h2>Panel de mantenimiento</h2><table><tr><th>Producto</th><th>Unidades</th></tr>';
    foreach (PRODUCTOS as $codigo => [$nombre]) {
        echo '<tr><td>' . e($nombre) . '</td><td>' . $m['existencias'][$codigo] . '</td></tr>';
    }
    echo '</table><table><tr><th>Moneda</th><th>En el cajón</th></tr>';
    foreach ($m['cajon'] as $moneda => $cantidad) {
        echo '<tr><td>' . euros($moneda) . '</td><td>' . $cantidad . '</td></tr>';
    }
    echo '</table>';
    if ($aviso !== '') {
        echo '<p class="ok">' . e($aviso) . '</p>';
    }
    echo '<p><button type="submit" name="accion" value="reponer">Reponer todo</button> <button type="submit" name="accion" value="cerrar">Cerrar</button></p>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
// el botón dice el evento y, en Elegir y Moneda, también el dato: "elegir:cafe", "moneda:200"
[$accion, $dato] = array_pad(explode(':', (string) ($_GET['accion'] ?? 'carga'), 2), 2, '');
$evento = EVENTOS[$accion] ?? EVENTOS['carga'];
$transicion = $automata[$estado][$evento] ?? 1;
$m = $transicion === 1 ? null : recuperarMaquina($_GET);  // AC1 (en todas las transiciones menos T1)
if ($transicion !== 1 && $m === null) {                   // !PL0: se reinicia la máquina
    $transicion = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Máquina expendedora (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .productos button { width: 7em; height: 4em; margin: 2px; }
        .aviso { color: #c0392b; font-weight: bold; } .ok { color: #2e7d32; }
        table { border-collapse: collapse; display: inline-table; margin-right: 2em; } td, th { padding: 2px 10px; border: 1px solid #ccc; }
    </style>
</head>
<body>
<h1>Máquina expendedora</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
switch ($transicion) {
    case 0:                                                // ---- !PL0 en cualquier transición
        $estado = ST1;
        $m = maquinaNueva();                               // AC0
        es0($m, 'Los datos de la máquina no eran válidos: se ha reiniciado.');
        break;

    case 1:                                                // ---- T1 (Carga)
        $estado = ST1;
        $m = maquinaNueva();                               // AC0
        es0($m);
        break;

    case 2:                                                // ---- T2 (ST1, Elegir)
        $producto = $dato;                                 // AC2: leer el producto
        if (!isset(PRODUCTOS[$producto])) {                // PL0 && !PL1
            $estado = ST1;
            es0($m, 'Ese producto no existe.');
        } elseif ($m['existencias'][$producto] === 0) {    // PL0 && PL1 && !PL2
            $estado = ST1;
            es0($m, PRODUCTOS[$producto][0] . ': agotado. Elija otro producto.');
        } else {                                           // PL0 && PL1 && PL2
            $estado = ST2;
            $m['producto'] = $producto;                    // AC3
            $m['credito'] = [];
            es1($m);
        }
        break;

    case 3:                                                // ---- T3 (ST2, Moneda)
        $moneda = filter_var($dato, FILTER_VALIDATE_INT);  // AC2: leer la moneda
        $precio = PRODUCTOS[$m['producto']][1];
        $pl3 = in_array($moneda, MONEDAS, true);           // PL3: moneda aceptada
        $creditoTotal = array_sum($m['credito']) + ($pl3 ? $moneda : 0);
        $pl4 = $pl3 && $creditoTotal >= $precio;           // PL4: llega al precio
        if (!$pl3) {                                       // PL0 && !PL3
            $estado = ST2;
            es1($m, 'Moneda no aceptada: se la devolvemos.');
        } elseif (!$pl4) {                                 // PL0 && PL3 && !PL4
            $estado = ST2;
            $m['credito'][] = $moneda;                     // AC4
            es1($m);
        } else {
            $m['credito'][] = $moneda;                     // AC4
            $cajon = $m['cajon'];                          // las monedas introducidas también sirven para el cambio
            foreach ($m['credito'] as $c) {
                $cajon[$c]++;
            }
            $cambio = buscarCambio($creditoTotal - $precio, $cajon);
            if ($cambio !== null) {                        // PL0 && PL3 && PL4 && PL5
                $estado = ST3;
                foreach ($cambio as $c) {                  // AC5: cobrar, entregar y dar el cambio
                    $cajon[$c]--;
                }
                $m['cajon'] = $cajon;
                $m['existencias'][$m['producto']]--;
                $m['entregado'] = $m['producto'];
                $m['bandeja'] = $cambio;
                $m['producto'] = null;
                $m['credito'] = [];
                es2($m);
            } else {                                       // PL0 && PL3 && PL4 && !PL5
                $estado = ST4;
                $m['bandeja'] = $m['credito'];             // AC6: devolver el crédito
                $m['producto'] = null;
                $m['credito'] = [];
                es3($m, 'No hay cambio suficiente');
            }
        }
        break;

    case 4:                                                // ---- T4 (ST2, Cancelar)
        if ($m['credito'] !== []) {                        // PL0 && PL6
            $estado = ST4;
            $m['bandeja'] = $m['credito'];                 // AC6
            $m['producto'] = null;
            $m['credito'] = [];
            es3($m, 'Operación cancelada');
        } else {                                           // PL0 && !PL6
            $estado = ST1;
            $m['producto'] = null;
            es0($m);
        }
        break;

    case 5:                                                // ---- T5 (ST3 o ST4, Recoger)
        $estado = ST1;                                     // AC7: la bandeja ya se ha vaciado al recuperar la máquina
        es0($m);
        break;

    case 6:                                                // ---- T6 (ST1, Mantenimiento)
        $estado = ST5;
        es4();
        break;

    case 7:                                                // ---- T7 (ST5, Clave)
        if (hash_equals(CLAVE_MANTENIMIENTO, (string) ($_GET['clave'] ?? ''))) {   // PL0 && PL7
            $estado = ST6;
            es5($m);
        } else {                                           // PL0 && !PL7
            $estado = ST1;
            es0($m, 'Clave de mantenimiento incorrecta.');
        }
        break;

    case 8:                                                // ---- T8 (ST6, Reponer)
        $estado = ST6;
        $m['existencias'] = array_fill_keys(array_keys(PRODUCTOS), REPONER);   // AC8
        $m['cajon'] = array_fill_keys(MONEDAS, REPONER);
        es5($m, 'Productos y monedas repuestos.');
        break;

    case 9:                                                // ---- T9 (ST5 Cancelar o ST6 Cerrar)
        $estado = ST1;
        es0($m);
        break;
}
// AC9: la máquina viaja en un campo oculto (sin la bandeja: lo que hay en ella ya es del cliente)
$guardar = $m;
unset($guardar['bandeja'], $guardar['entregado']);
?>
    <input type="hidden" name="maquina" value="<?= e(json_encode($guardar)) ?>">
    <input type="hidden" name="estado" value="<?= $estado ?>">
</form>
</body>
</html>
