<?php
/*
 * Ejemplo 06: cajero automático
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-Cajero.html)
 *
 * 8 estados, 9 eventos entrantes y 11 transiciones. Todo lo que hay que recordar (saldo, intentos, lo retirado
 * hoy, los últimos movimientos) viaja en campos ocultos, así que va FIRMADO con hash_hmac: si alguien cambia el
 * saldo con las herramientas del navegador, la firma deja de coincidir (PL0 falso) y el cajero retiene la tarjeta.
 */
const PIN = '1234';
const SALDO_INICIAL = 25000;            // en céntimos: 250,00 €
const LIMITE_DIARIO = 30000;            // 300 € retirados como máximo
const MAXIMO_INGRESO = 300000;          // 3.000 € por ingreso
const MAXIMOS_INTENTOS = 3;
const SECRETO = 'cambia-esta-clave-y-guardala-fuera-de-htdocs';   // en un proyecto real, en un fichero de configuración

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Pidiendo el PIN
const ST2 = 2;          // Menú
const ST3 = 3;          // Pidiendo el importe a retirar
const ST4 = 4;          // Pidiendo el importe a ingresar
const ST5 = 5;          // Mostrando el comprobante
const ST6 = 6;          // Tarjeta retenida
const ST7 = 7;          // Despedida
const EVENTOS = ['carga' => 0, 'aceptar' => 1, 'retirar' => 2, 'ingresar' => 3, 'saldo' => 4,
    'confirmar' => 5, 'cancelar' => 6, 'otra' => 7, 'salir' => 8];

// ----- Tabla de eventos-estados ampliada: columnas EE0..EE8 (null = el evento no puede darse) -------
//                Carga  Aceptar Retirar Ingresar Saldo Confirmar Cancelar Otra  Salir
$automata = [
    ST0 => [1, 1, 1, 1, 1, 1, 1, 1, 1],
    ST1 => [1, 2, null, null, null, null, 8, null, null],
    ST2 => [1, null, 3, 4, 5, null, null, null, 9],
    ST3 => [1, null, null, null, null, 6, 10, null, null],
    ST4 => [1, null, null, null, null, 7, 10, null, null],
    ST5 => [1, null, null, null, null, null, null, 11, 9],
    ST6 => [1, null, null, null, null, null, null, null, null],
    ST7 => [1, null, null, null, null, null, null, null, null],
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
/** AC0: la cuenta al introducir la tarjeta. */
function cuentaNueva(): array
{
    return ['saldo' => SALDO_INICIAL, 'intentos' => 0, 'retirado' => 0, 'movimientos' => []];
}

/** Firma del estado y de la cuenta (la usan AC1 para comprobar y AC8 para guardar). */
function firma(int $estado, string $cuentaJson): string
{
    return hash_hmac('sha256', $estado . '|' . $cuentaJson, SECRETO);
}

/** AC1: recupera la cuenta de los campos ocultos si la firma es correcta (PL0); si no, null. */
function recuperarCuenta(int $estado, array $datos): ?array
{
    $json = (string) ($datos['cuenta'] ?? '');
    $recibida = (string) ($datos['firma'] ?? '');
    if (!hash_equals(firma($estado, $json), $recibida)) {
        return null;
    }
    $cuenta = json_decode($json, true);
    return is_array($cuenta) ? $cuenta : null;
}

/** Añade un movimiento y deja solo los 5 últimos. */
function anotar(array $cuenta, string $concepto, int $importe): array
{
    $cuenta['movimientos'][] = [$concepto, $importe, $cuenta['saldo']];
    $cuenta['movimientos'] = array_slice($cuenta['movimientos'], -5);
    return $cuenta;
}

/** Lee un importe en euros con hasta 2 decimales ("25", "25,5", "25.50"): céntimos o false. */
function importeEnCentimos(string $texto): int|false
{
    if (preg_match('/^(\d{1,4})(?:[.,](\d{1,2}))?$/', trim($texto), $partes) !== 1) {
        return false;
    }
    return (int) $partes[1] * 100 + (int) str_pad($partes[2] ?? '0', 2, '0');
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
function pantalla(string $titulo, string $contenido, array $botones): void
{
    echo '<div class="pantalla"><h2>' . e($titulo) . '</h2>' . $contenido . '</div><div class="botones">';
    foreach ($botones as $valor => $texto) {
        echo '<button type="submit" name="accion" value="' . e($valor) . '">' . e($texto) . '</button> ';
    }
    echo '</div>';
}

/** ES0 (y ES2 con aviso): pedir el PIN. */
function es0(string $aviso = ''): void
{
    $avisoHtml = $aviso === '' ? '' : '<p class="aviso">' . e($aviso) . '</p>';
    pantalla('Introduzca su PIN', '<p><input type="password" name="pin" maxlength="4" autofocus></p>' . $avisoHtml,
        ['aceptar' => 'Aceptar', 'cancelar' => 'Cancelar']);
}

/** ES1: menú. */
function es1(): void
{
    pantalla('¿Qué operación desea hacer?', '', ['retirar' => 'Sacar dinero', 'ingresar' => 'Ingresar dinero',
        'saldo' => 'Consultar saldo', 'salir' => 'Salir']);
}

/** ES3 (con aviso opcional): importe a retirar. */
function es3(array $cuenta, string $escrito = '', string $aviso = ''): void
{
    $disponible = min($cuenta['saldo'], LIMITE_DIARIO - $cuenta['retirado']);
    $html = '<p>Múltiplo de 10 €, como mucho ' . euros(max(0, $disponible)) . ' hoy.</p>'
        . '<p><input type="text" name="importe" value="' . e($escrito) . '"> €</p>';
    if ($aviso !== '') {
        $html .= '<p class="aviso">' . e($aviso) . '</p>';
    }
    pantalla('¿Cuánto quiere sacar?', $html, ['confirmar' => 'Confirmar', 'cancelar' => 'Cancelar']);
}

/** ES4 (con aviso opcional): importe a ingresar. */
function es4(string $escrito = '', string $aviso = ''): void
{
    $html = '<p>Hasta ' . euros(MAXIMO_INGRESO) . ', con céntimos si quiere.</p>'
        . '<p><input type="text" name="importe" value="' . e($escrito) . '"> €</p>';
    if ($aviso !== '') {
        $html .= '<p class="aviso">' . e($aviso) . '</p>';
    }
    pantalla('¿Cuánto quiere ingresar?', $html, ['confirmar' => 'Confirmar', 'cancelar' => 'Cancelar']);
}

/** ES5: comprobante con el saldo y los últimos movimientos. */
function es5(array $cuenta, string $operacion): void
{
    $html = '<p class="ok">' . e($operacion) . '</p><p>Saldo: <strong>' . euros($cuenta['saldo']) . '</strong></p>';
    if ($cuenta['movimientos'] !== []) {
        $html .= '<table><tr><th>Movimiento</th><th>Importe</th><th>Saldo</th></tr>';
        foreach (array_reverse($cuenta['movimientos']) as [$concepto, $importe, $saldo]) {
            $html .= '<tr><td>' . e($concepto) . '</td><td>' . euros($importe) . '</td><td>' . euros($saldo) . '</td></tr>';
        }
        $html .= '</table>';
    }
    pantalla('Comprobante', $html, ['otra' => 'Otra operación', 'salir' => 'Salir']);
}

/** ES6: tarjeta retenida. */
function es6(string $motivo): void
{
    pantalla('Tarjeta retenida', '<p class="aviso">' . e($motivo) . '</p><p>Póngase en contacto con su oficina.</p>', []);
    echo '<p><a href="' . e($_SERVER['PHP_SELF']) . '">Introducir otra tarjeta</a></p>';
}

/** ES7: despedida. */
function es7(): void
{
    pantalla('Gracias', '<p>Retire su tarjeta.</p>', []);
    echo '<p><a href="' . e($_SERVER['PHP_SELF']) . '">Introducir la tarjeta</a></p>';
}

// ----- 1. Estado y evento entrante ---------------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
$evento = EVENTOS[$_GET['accion'] ?? 'carga'] ?? EVENTOS['carga'];
$transicion = $automata[$estado][$evento] ?? 1;            // evento imposible (---): empezar de nuevo
$cuenta = null;
$retenida = 'Los datos de la operación no son válidos (página manipulada).';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cajero automático (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .pantalla { background: #0b3d5c; color: #fff; padding: 1em 1.5em; width: 28em; border-radius: 8px; }
        .pantalla table { color: #fff; border-collapse: collapse; } .pantalla td, .pantalla th { padding: 2px 10px; }
        .aviso { color: #ffb4a8; font-weight: bold; } .ok { color: #b9f6ca; }
        .botones { margin-top: 0.6em; }
    </style>
</head>
<body>
<h1>Cajero automático</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
switch ($transicion) {
    case 1:                                                // ---- T1 (Carga)
        $estado = ST1;
        $cuenta = cuentaNueva();                           // AC0
        es0();
        break;

    case 2:                                                // ---- T2 (ST1, Aceptar)
        $cuenta = recuperarCuenta($estado, $_GET);         // AC1
        $pin = (string) ($_GET['pin'] ?? '');              // AC2
        if ($cuenta === null) {                            // !PL0
            $estado = ST6;
            es6($retenida);
        } elseif (hash_equals(PIN, $pin)) {                // PL0 && PL1
            $estado = ST2;
            $cuenta['intentos'] = 0;                       // AC3
            es1();
        } elseif ($cuenta['intentos'] < MAXIMOS_INTENTOS - 1) {   // PL0 && !PL1 && !PL2
            $estado = ST1;
            $cuenta['intentos']++;                         // AC4
            $quedan = MAXIMOS_INTENTOS - $cuenta['intentos'];
            es0($quedan === 1 ? 'PIN incorrecto. Le queda 1 intento.' : "PIN incorrecto. Le quedan $quedan intentos.");   // ES2
        } else {                                           // PL0 && !PL1 && PL2
            $estado = ST6;
            $cuenta['intentos']++;                         // AC4
            es6('Ha escrito un PIN incorrecto ' . MAXIMOS_INTENTOS . ' veces.');
        }
        break;

    case 3:                                                // ---- T3 (ST2, Retirar)
    case 4:                                                // ---- T4 (ST2, Ingresar)
    case 5:                                                // ---- T5 (ST2, Saldo)
        $cuenta = recuperarCuenta($estado, $_GET);         // AC1
        if ($cuenta === null) {                            // !PL0
            $estado = ST6;
            es6($retenida);
        } elseif ($transicion === 3) {                     // PL0
            $estado = ST3;
            es3($cuenta);
        } elseif ($transicion === 4) {                     // PL0
            $estado = ST4;
            es4();
        } else {                                           // PL0
            $estado = ST5;
            es5($cuenta, 'Consulta de saldo');
        }
        break;

    case 6:                                                // ---- T6 (ST3, Confirmar)
        $cuenta = recuperarCuenta($estado, $_GET);         // AC1
        $escrito = trim((string) ($_GET['importe'] ?? '')); // AC7: leer el importe
        $euros = filter_var($escrito, FILTER_VALIDATE_INT);
        $importe = $euros === false ? 0 : $euros * 100;
        if ($cuenta === null) {                            // !PL0
            $estado = ST6;
            es6($retenida);
        } elseif ($euros === false || $euros < 10 || $euros > LIMITE_DIARIO / 100 || $euros % 10 !== 0) {   // PL0 && !PL3
            $estado = ST3;
            es3($cuenta, $escrito, 'El importe debe ser un múltiplo de 10 € entre 10 y 300 €.');
        } elseif ($importe > $cuenta['saldo']) {           // PL0 && PL3 && !PL4
            $estado = ST3;
            es3($cuenta, $escrito, 'No tiene saldo suficiente.');
        } elseif ($cuenta['retirado'] + $importe > LIMITE_DIARIO) {   // PL0 && PL3 && PL4 && !PL5
            $estado = ST3;
            es3($cuenta, $escrito, 'Supera el límite diario: hoy puede sacar ' . euros(LIMITE_DIARIO - $cuenta['retirado']) . '.');
        } else {                                           // PL0 && PL3 && PL4 && PL5
            $estado = ST5;
            $cuenta['saldo'] -= $importe;                  // AC5: retirar
            $cuenta['retirado'] += $importe;
            $cuenta = anotar($cuenta, 'Reintegro', -$importe);
            es5($cuenta, 'Retire su dinero: ' . euros($importe));
        }
        break;

    case 7:                                                // ---- T7 (ST4, Confirmar)
        $cuenta = recuperarCuenta($estado, $_GET);         // AC1
        $escrito = trim((string) ($_GET['importe'] ?? '')); // AC7
        $importe = importeEnCentimos($escrito);
        if ($cuenta === null) {                            // !PL0
            $estado = ST6;
            es6($retenida);
        } elseif ($importe === false || $importe < 1 || $importe > MAXIMO_INGRESO) {   // PL0 && !PL6
            $estado = ST4;
            es4($escrito, 'Escriba un importe entre 0,01 y 3.000 € (con 2 decimales como mucho).');
        } else {                                           // PL0 && PL6
            $estado = ST5;
            $cuenta['saldo'] += $importe;                  // AC6: ingresar
            $cuenta = anotar($cuenta, 'Ingreso', $importe);
            es5($cuenta, 'Ingreso realizado: ' . euros($importe));
        }
        break;

    case 8:                                                // ---- T8 (ST1, Cancelar)
    case 9:                                                // ---- T9 (ST2 o ST5, Salir)
        $estado = ST7;
        es7();
        break;

    case 10:                                               // ---- T10 (ST3 o ST4, Cancelar)
    case 11:                                               // ---- T11 (ST5, Otra operación)
        $cuenta = recuperarCuenta($estado, $_GET);         // AC1
        if ($cuenta === null) {                            // !PL0
            $estado = ST6;
            es6($retenida);
        } else {                                           // PL0
            $estado = ST2;
            es1();
        }
        break;
}

// AC8: guardar el estado y la cuenta, firmados, en campos ocultos (solo en los estados que siguen la operación)
if ($cuenta !== null && in_array($estado, [ST1, ST2, ST3, ST4, ST5], true)) {
    $json = json_encode($cuenta);
    echo '<input type="hidden" name="cuenta" value="' . e($json) . '">';
    echo '<input type="hidden" name="firma" value="' . firma($estado, $json) . '">';
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
</form>
</body>
</html>
