<?php
/*
 * Ejemplo 08: asistente de reservas del Bar Baio
 * SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver ../Automata-ReservaBarBaio.html)
 *
 * 7 estados, 6 eventos entrantes y 10 transiciones. Un asistente en tres pasos (datos, fecha y hora,
 * comensales y zona) con un resumen final. Desde el resumen se puede editar cualquier paso: al terminar de
 * editarlo se vuelve directamente al resumen (variable de estado $editando, predicado PL2). Lo que no se pide
 * en el paso actual viaja en campos ocultos y se vuelve a validar todo al confirmar.
 */
date_default_timezone_set('Europe/Madrid');

const FRANJAS = ['13:30', '14:30', '20:30', '21:30'];
const FRANJAS_CENA = ['20:30', '21:30'];
const AFORO = 20;                       // comensales por franja
const MAX_COMENSALES = 12;
const MAX_TERRAZA = 6;
const DIAS_ANTELACION = 30;
const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
const CAMPOS = ['nombre', 'telefono', 'fecha', 'franja', 'comensales', 'zona'];

// ----- Estados (ST) y eventos entrantes (EE) ---------------------------------------------------
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Paso 1: datos de contacto
const ST2 = 2;          // Paso 2: fecha y hora
const ST3 = 3;          // Paso 3: comensales y zona
const ST4 = 4;          // Resumen
const ST5 = 5;          // Reserva confirmada
const ST6 = 6;          // Reserva cancelada
const EVENTOS = ['carga' => 0, 'siguiente' => 1, 'atras' => 2, 'editar' => 3, 'confirmar' => 4, 'cancelar' => 5];

//                Carga Siguiente Atrás Editar Confirmar Cancelar
$automata = [
    ST0 => [1, 1, 1, 1, 1, 1],
    ST1 => [1, 2, null, null, null, 9],
    ST2 => [1, 3, 5, null, null, 9],
    ST3 => [1, 4, 6, null, null, 9],
    ST4 => [1, null, 7, 8, 10, 9],
    ST5 => [1, null, null, null, null, null],
    ST6 => [1, null, null, null, null, null],
];

function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// ----- Predicados (PL) ---------------------------------------------------------------------------
function plNombre(string $nombre): bool                       // PL0
{
    return preg_match('/^[\p{L} .\'-]{2,60}$/u', $nombre) === 1;
}

function plTelefono(string $telefono): bool                   // PL1
{
    return preg_match('/^[6789]\d{8}$/', $telefono) === 1;
}

function fecha(string $texto): ?DateTimeImmutable
{
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $texto);
    return $fecha !== false && $fecha->format('Y-m-d') === $texto ? $fecha : null;
}

function plFechaValida(string $texto): bool                   // PL3
{
    return fecha($texto) !== null;
}

function plFechaEnPlazo(string $texto): bool                  // PL4: de mañana a dentro de 30 días
{
    $f = fecha($texto);
    $hoy = new DateTimeImmutable('today');
    return $f > $hoy && $f <= $hoy->modify('+' . DIAS_ANTELACION . ' days');
}

function plAbre(string $texto): bool                          // PL5: los lunes cierra
{
    return fecha($texto)->format('w') !== '1';
}

function plFranja(string $texto, string $franja): bool        // PL6: los domingos solo hay comidas
{
    $domingo = fecha($texto)->format('w') === '0';
    return in_array($franja, FRANJAS, true) && !($domingo && in_array($franja, FRANJAS_CENA, true));
}

function plComensales(string $texto): bool                    // PL7
{
    return filter_var($texto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => MAX_COMENSALES]]) !== false;
}

function plZona(string $zona): bool                           // PL8
{
    return in_array($zona, ['interior', 'terraza'], true);
}

function plTerraza(string $zona, int $comensales): bool       // PL9: en la terraza, 6 como mucho
{
    return $zona !== 'terraza' || $comensales <= MAX_TERRAZA;
}

/** Plazas libres en una franja (simulación de las reservas que ya hay: depende del día y de la franja). */
function plazasLibres(string $texto, string $franja): int
{
    $ocupadas = ((int) fecha($texto)->format('j') * 3 + array_search($franja, FRANJAS, true) * 5) % (AFORO + 1);
    return AFORO - $ocupadas;
}

function plPlazas(string $texto, string $franja, int $comensales): bool   // PL10
{
    return plazasLibres($texto, $franja) >= $comensales;
}

/** Todos los predicados de los tres pasos (para T10: los datos ocultos pueden haber cambiado). */
function reservaValida(array $r): bool
{
    return plNombre($r['nombre']) && plTelefono($r['telefono']) && plFechaValida($r['fecha']) && plFechaEnPlazo($r['fecha'])
        && plAbre($r['fecha']) && plFranja($r['fecha'], $r['franja']) && plComensales($r['comensales']) && plZona($r['zona'])
        && plTerraza($r['zona'], (int) $r['comensales']) && plPlazas($r['fecha'], $r['franja'], (int) $r['comensales']);
}

// ----- Eventos salientes (ES) ------------------------------------------------------------------
/** Campos ocultos con los datos que no se piden en este paso (AC2). */
function ocultos(array $r, array $menos): void
{
    foreach (CAMPOS as $campo) {
        if (!in_array($campo, $menos, true)) {
            echo '<input type="hidden" name="' . $campo . '" value="' . e($r[$campo]) . '">';
        }
    }
}

function avisos(array $avisos): void
{
    foreach ($avisos as $aviso) {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
}

function botones(array $botones): void
{
    echo '<p>';
    foreach ($botones as $valor => $texto) {
        echo '<button type="submit" name="accion" value="' . e($valor) . '">' . e($texto) . '</button> ';
    }
    echo '</p>';
}

/** ES0 / ES1: paso 1 (vacío o con avisos). */
function paso1(array $r, array $avisos = []): void
{
    echo '<h2>Paso 1 de 3: sus datos</h2>';
    echo '<p><label>Nombre: <input type="text" name="nombre" value="' . e($r['nombre']) . '"></label></p>';
    echo '<p><label>Teléfono: <input type="text" name="telefono" value="' . e($r['telefono']) . '"></label></p>';
    avisos($avisos);
    ocultos($r, ['nombre', 'telefono']);
    botones(['siguiente' => 'Siguiente', 'cancelar' => 'Cancelar']);
}

/** ES2: paso 2 (con avisos opcionales). */
function paso2(array $r, array $avisos = []): void
{
    $manana = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
    $ultimo = (new DateTimeImmutable('today'))->modify('+' . DIAS_ANTELACION . ' days')->format('Y-m-d');
    echo '<h2>Paso 2 de 3: fecha y hora</h2>';
    echo '<p><label>Fecha: <input type="date" name="fecha" min="' . $manana . '" max="' . $ultimo . '" value="' . e($r['fecha']) . '"></label>';
    echo ' <small>(los lunes cerramos; los domingos, solo comidas)</small></p><p>Hora: ';
    foreach (FRANJAS as $franja) {
        $marcada = $r['franja'] === $franja ? ' checked' : '';
        echo '<label><input type="radio" name="franja" value="' . $franja . '"' . $marcada . '> ' . $franja . '</label> ';
    }
    echo '</p>';
    avisos($avisos);
    ocultos($r, ['fecha', 'franja']);
    botones(['siguiente' => 'Siguiente', 'atras' => 'Atrás', 'cancelar' => 'Cancelar']);
}

/** ES3: paso 3 (con avisos opcionales). */
function paso3(array $r, array $avisos = []): void
{
    echo '<h2>Paso 3 de 3: comensales y zona</h2>';
    echo '<p><label>Comensales (1-' . MAX_COMENSALES . '): <input type="text" name="comensales" size="3" value="' . e($r['comensales']) . '"></label></p><p>Zona: ';
    foreach (['interior' => 'Interior', 'terraza' => 'Terraza (hasta ' . MAX_TERRAZA . ')'] as $zona => $texto) {
        $marcada = $r['zona'] === $zona ? ' checked' : '';
        echo '<label><input type="radio" name="zona" value="' . $zona . '"' . $marcada . '> ' . e($texto) . '</label> ';
    }
    echo '</p>';
    avisos($avisos);
    ocultos($r, ['comensales', 'zona']);
    botones(['siguiente' => 'Siguiente', 'atras' => 'Atrás', 'cancelar' => 'Cancelar']);
}

function fechaLarga(string $texto): string
{
    $f = fecha($texto);
    return DIAS[(int) $f->format('w')] . ' ' . $f->format('j/n/Y');
}

/** ES4: resumen, con un botón para editar cada paso. */
function resumen(array $r, array $avisos = []): void
{
    echo '<h2>Resumen de la reserva</h2><table>';
    $filas = [1 => ['Nombre y teléfono', $r['nombre'] . ' · ' . $r['telefono']],
        2 => ['Fecha y hora', fechaLarga($r['fecha']) . ' a las ' . $r['franja']],
        3 => ['Comensales y zona', $r['comensales'] . ' · ' . $r['zona']]];
    foreach ($filas as $paso => [$titulo, $valor]) {
        echo '<tr><th>' . e($titulo) . '</th><td>' . e($valor) . '</td><td><button type="submit" name="accion" value="editar:' . $paso . '">Editar</button></td></tr>';
    }
    echo '</table>';
    avisos($avisos);
    ocultos($r, []);
    botones(['confirmar' => 'Confirmar la reserva', 'atras' => 'Atrás', 'cancelar' => 'Cancelar']);
}

/** ES5: reserva confirmada. */
function confirmada(array $r, string $codigo): void
{
    echo '<h2>¡Reserva confirmada!</h2><p class="ok">Código: <strong>' . e($codigo) . '</strong></p>';
    echo '<p>' . e($r['nombre']) . ', le esperamos el ' . e(fechaLarga($r['fecha'])) . ' a las ' . e($r['franja'])
        . ' (' . e($r['comensales']) . ' comensales, ' . e($r['zona']) . ').</p>';
    echo '<p><a href="' . e($_SERVER['PHP_SELF']) . '">Hacer otra reserva</a></p>';
}

/** ES6: reserva cancelada. */
function cancelada(): void
{
    echo '<h2>Reserva cancelada</h2><p>No se ha guardado nada.</p>';
    echo '<p><a href="' . e($_SERVER['PHP_SELF']) . '">Empezar de nuevo</a></p>';
}

// ----- 1. Estado, evento entrante y datos ------------------------------------------------------
$estado = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);
if ($estado === null || $estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
[$accion, $dato] = array_pad(explode(':', (string) ($_GET['accion'] ?? 'carga'), 2), 2, '');
$evento = EVENTOS[$accion] ?? EVENTOS['carga'];
$transicion = $automata[$estado][$evento] ?? 1;

$r = [];                                                   // AC1: leer y limpiar los datos (del paso o de los ocultos)
foreach (CAMPOS as $campo) {
    $r[$campo] = trim((string) ($_GET[$campo] ?? ''));
}
$editando = ($_GET['editando'] ?? '') === '1';             // PL2: se está editando desde el resumen
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas del Bar Baio (autómata)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .aviso { color: #c0392b; } .ok { color: #2e7d32; }
        table { border-collapse: collapse; } td, th { padding: 4px 10px; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>
<h1>Reservas del Bar Baio</h1>
<form action="<?= e($_SERVER['PHP_SELF']) ?>" method="get">
<?php
switch ($transicion) {
    case 1:                                                // ---- T1 (Carga)
        $estado = ST1;
        $editando = false;
        $r = array_fill_keys(CAMPOS, '');                  // AC0: reserva vacía
        paso1($r);
        break;

    case 2:                                                // ---- T2 (ST1, Siguiente)
        $pl0 = plNombre($r['nombre']);
        $pl1 = plTelefono($r['telefono']);
        if (!$pl0 || !$pl1) {                              // !PL0 || !PL1
            $estado = ST1;
            $avisos = [];                                  // AC3: avisos del paso 1
            if (!$pl0) {
                $avisos[] = 'Escriba su nombre (de 2 a 60 letras).';
            }
            if (!$pl1) {
                $avisos[] = 'El teléfono debe tener 9 cifras y empezar por 6, 7, 8 o 9.';
            }
            paso1($r, $avisos);
        } elseif (!$editando) {                            // PL0 && PL1 && !PL2
            $estado = ST2;
            paso2($r);
        } else {                                           // PL0 && PL1 && PL2
            $estado = ST4;
            $editando = false;                             // AC4: fin de la edición
            resumen($r);
        }
        break;

    case 3:                                                // ---- T3 (ST2, Siguiente)
        if (!plFechaValida($r['fecha'])) {                 // !PL3
            $estado = ST2;
            paso2($r, ['Elija una fecha.']);
        } elseif (!plFechaEnPlazo($r['fecha'])) {          // PL3 && !PL4
            $estado = ST2;
            paso2($r, ['La fecha debe estar entre mañana y dentro de ' . DIAS_ANTELACION . ' días.']);
        } elseif (!plAbre($r['fecha'])) {                  // PL3 && PL4 && !PL5
            $estado = ST2;
            paso2($r, ['Los lunes el Bar Baio está cerrado.']);
        } elseif (!plFranja($r['fecha'], $r['franja'])) {  // PL3 && PL4 && PL5 && !PL6
            $estado = ST2;
            paso2($r, ['Elija una hora' . (fecha($r['fecha'])->format('w') === '0' ? ' de comida (los domingos no hay cenas).' : '.')]);
        } elseif (!$editando) {                            // PL3..PL6 && !PL2
            $estado = ST3;
            paso3($r);
        } elseif (plPlazas($r['fecha'], $r['franja'], (int) $r['comensales'])) {   // PL3..PL6 && PL2 && PL10
            $estado = ST4;
            $editando = false;                             // AC4
            resumen($r);
        } else {                                           // PL3..PL6 && PL2 && !PL10
            $estado = ST3;
            $editando = false;                             // AC4: hay que revisar el paso 3
            paso3($r, ['En esa franja solo quedan ' . plazasLibres($r['fecha'], $r['franja']) . ' plazas: cambie los comensales o vuelva atrás.']);
        }
        break;

    case 4:                                                // ---- T4 (ST3, Siguiente)
        $pl7 = plComensales($r['comensales']);
        $pl8 = plZona($r['zona']);
        if (!$pl7 || !$pl8) {                              // !PL7 || !PL8
            $estado = ST3;
            $avisos = [];                                  // AC3: avisos del paso 3
            if (!$pl7) {
                $avisos[] = 'Los comensales deben ser entre 1 y ' . MAX_COMENSALES . '.';
            }
            if (!$pl8) {
                $avisos[] = 'Elija la zona.';
            }
            paso3($r, $avisos);
        } elseif (!plTerraza($r['zona'], (int) $r['comensales'])) {   // PL7 && PL8 && !PL9
            $estado = ST3;
            paso3($r, ['En la terraza caben ' . MAX_TERRAZA . ' comensales por reserva como mucho.']);
        } elseif (!plFechaValida($r['fecha']) || !plFranja($r['fecha'], $r['franja'])
            || !plPlazas($r['fecha'], $r['franja'], (int) $r['comensales'])) {   // PL7 && PL8 && PL9 && !PL10
            $estado = ST3;
            $libres = plFechaValida($r['fecha']) && in_array($r['franja'], FRANJAS, true) ? plazasLibres($r['fecha'], $r['franja']) : 0;
            paso3($r, ["En esa franja solo quedan $libres plazas."]);
        } else {                                           // PL7 && PL8 && PL9 && PL10
            $estado = ST4;
            $editando = false;                             // AC4
            resumen($r);
        }
        break;

    case 5:                                                // ---- T5 (ST2, Atrás)
        $estado = ST1;
        paso1($r);
        break;

    case 6:                                                // ---- T6 (ST3, Atrás)
        $estado = ST2;
        paso2($r);
        break;

    case 7:                                                // ---- T7 (ST4, Atrás)
        $estado = ST3;
        paso3($r);
        break;

    case 8:                                                // ---- T8 (ST4, Editar)
        $paso = filter_var($dato, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3]]);
        if ($paso === false) {                             // !PL11
            $estado = ST4;
            resumen($r);
        } else {                                           // PL11
            $estado = [1 => ST1, 2 => ST2, 3 => ST3][$paso];
            $editando = true;                              // AC5: empieza la edición
            [1 => 'paso1', 2 => 'paso2', 3 => 'paso3'][$paso]($r);
        }
        break;

    case 9:                                                // ---- T9 (ST1..ST4, Cancelar)
        $estado = ST6;
        cancelada();
        break;

    case 10:                                               // ---- T10 (ST4, Confirmar)
        if (reservaValida($r)) {                           // PL0 && PL1 && PL3..PL10
            $estado = ST5;
            $codigo = 'BB-' . strtoupper(substr(sha1(implode('|', $r)), 0, 6));   // AC6
            confirmada($r, $codigo);
        } else {                                           // algún predicado falla (datos ocultos manipulados)
            $estado = ST1;
            $editando = false;
            paso1($r, ['Los datos de la reserva no son válidos: revíselos.']);
        }
        break;
}
if ($editando && in_array($estado, [ST1, ST2, ST3], true)) {
    echo '<input type="hidden" name="editando" value="1">';
}
?>
    <input type="hidden" name="estado" value="<?= $estado ?>">
</form>
</body>
</html>
