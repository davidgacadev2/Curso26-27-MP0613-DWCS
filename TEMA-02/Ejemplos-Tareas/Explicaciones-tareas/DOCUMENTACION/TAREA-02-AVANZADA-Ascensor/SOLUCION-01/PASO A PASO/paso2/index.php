<?php
/*
 * >>> PASO 2 de 6: el autómata en PHP: constantes, tabla de eventos-estados, estado y evento recibidos, T1, el edificio y el ascensor en campos ocultos <<<
 *
 * TAREA-02 AVANZADA: El ascensor del instituto
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "DIAGRAMA ESTADO-TRANSICION/Automata-Ascensor.pdf")
 *  - 7 estados, 5 eventos entrantes y 10 transiciones; una única página que se envía a sí misma por POST;
 *  - aquí el autómata de la página ES el del ascensor: parado, subiendo, bajando, puertas abiertas, emergencia...
 *  - el estado y los datos del ascensor viajan en campos ocultos (sin $_SESSION) y se comprueban con los
 *    invariantes de cada estado (PL0) antes de cualquier transición.
 */
// cargar las acciones y los predicados
require __DIR__ . '/funciones.php';

// estados (ST) y eventos entrantes (EE) del autómata
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Parado, puertas cerradas
const ST2 = 2;          // Subiendo
const ST3 = 3;          // Bajando
const ST4 = 4;          // Puertas abiertas
const ST5 = 5;          // Parada de emergencia
const ST6 = 6;          // Fuera de servicio
const NOMBRES_ESTADOS = [ST1 => 'Parado', ST2 => 'Subiendo', ST3 => 'Bajando', ST4 => 'Puertas abiertas',
    ST5 => 'Parada de emergencia', ST6 => 'Fuera de servicio'];
const EE0 = 0;          // Carga (también el botón "Reiniciar")
const EE1 = 1;          // Llamar (llamar:0 ... llamar:4)
const EE2 = 2;          // Avanzar (pasa un instante)
const EE3 = 3;          // Emergencia
const EE4 = 4;          // Rearmar

// tabla de eventos-estados ampliada: $automata[estado][evento] = transición (null = "---")
//                Carga Llamar Avanzar Emergencia Rearmar
$automata = [
    ST0 => [1, 1, 1, 1, 1],
    ST1 => [1, 2, 5, 9, null],
    ST2 => [1, 3, 6, 9, null],
    ST3 => [1, 3, 7, 9, null],
    ST4 => [1, 4, 8, 9, null],
    ST5 => [1, null, null, null, 10],
    ST6 => [1, null, null, null, null],
];

// evento(array $datos): int -> el evento entrante según el botón pulsado ("llamar:3" es Llamar)
function evento(array $datos): int
{
    $valor = is_string($datos['evento'] ?? null) ? $datos['evento'] : '';
    return match (true) {
        str_starts_with($valor, 'llamar:') => EE1,
        $valor === 'avanzar' => EE2,
        $valor === 'emergencia' => EE3,
        $valor === 'rearmar' => EE4,
        default => EE0,
    };
}

// ES0...ES4: el edificio con el ascensor, los botones de llamada, el mensaje y los mandos
function edificio(int $estado, array $ascensor, string $mensaje, string $aviso): void
{
    $funciona = $estado !== ST5 && $estado !== ST6;
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';                       // ES1: aviso de datos no válidos
    }
    echo '<p class="estado estado-' . $estado . '">' . e(NOMBRES_ESTADOS[$estado]) . ' — ' . e(PLANTAS[$ascensor['piso']]) . '</p>';
    echo '<table class="edificio">';
    for ($planta = ULTIMA; $planta >= 0; $planta--) {
        $aqui = $planta === $ascensor['piso'];
        $esperando = in_array($planta, $ascensor['llamadas'], true);
        echo '<tr><th>' . e(PLANTAS[$planta]) . '</th><td class="hueco">';
        if ($aqui) {
            $flecha = [ST2 => ' ▲', ST3 => ' ▼'][$estado] ?? '';
            echo '<span class="cabina cabina-' . $estado . '">' . ($estado === ST4 ? '[  ]' : '[▮▮]') . $flecha . '</span>';
        }
        echo '</td><td><button name="evento" value="llamar:' . $planta . '" class="' . ($esperando ? 'esperando' : '') . '"'
            . ($funciona ? '' : ' disabled') . '>' . ($esperando ? 'Esperando' : 'Llamar') . '</button></td></tr>';
    }
    echo '</table>';
    if ($mensaje !== '') {
        echo '<p class="mensaje">' . e($mensaje) . '</p>';
    }
    echo '<p class="mandos"><button name="evento" value="avanzar"' . ($funciona ? '' : ' disabled') . '>Avanzar ⏵</button> ';
    // TODO: los botones de emergencia y de rearmar
    echo '<button name="evento" value="reiniciar">Reiniciar</button></p>';
    echo '<p class="contadores">Plantas recorridas: <strong>' . $ascensor['recorridas'] . '</strong> · Paradas: <strong>'
        . $ascensor['paradas'] . '</strong> · Emergencias: <strong>' . $ascensor['emergencias'] . '</strong></p>';
}

// AC8 guardar(int $estado, array $ascensor): el estado y el ascensor en campos ocultos
function guardar(int $estado, array $ascensor): void
{
    $campos = ['estado' => $estado, 'piso' => $ascensor['piso'], 'direccion' => $ascensor['direccion'],
        'llamadas' => implode('', $ascensor['llamadas']), 'emergencias' => $ascensor['emergencias'],
        'recorridas' => $ascensor['recorridas'], 'paradas' => $ascensor['paradas']];
    foreach ($campos as $nombre => $valor) {
        echo '<input type="hidden" name="' . $nombre . '" value="' . e($valor) . '">';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El ascensor del instituto</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>El ascensor del instituto</h1>
<form method="post">
<?php
// 1) el estado (campo oculto, validado), el evento entrante y la transición que dice la tabla
$estado = filter_var($_POST['estado'] ?? '', FILTER_VALIDATE_INT);
if ($estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
$evento = evento($_POST);
$transicion = $automata[$estado][$evento] ?? 1;                 // un evento imposible ("---") se trata como T1
$mensaje = '';
$aviso = '';

// 2) antes de cualquier transición salvo T1: AC1 recuperar el ascensor y comprobarlo (PL0)
$ascensor = ascensorInicial();
if ($transicion !== 1) {
    $ascensor = leerAscensor($_POST);
    // TODO: PL0: ¿cumplen los datos los invariantes del estado en el que dicen estar?
    $pl0 = true;
    if (!$pl0) {                                                // !PL0 -> como T1, pero con ES1 (aviso)
        $transicion = 1;
        $aviso = 'Los datos del ascensor no son válidos (¿se han modificado los campos ocultos?). Se reinicia.';
    }
}

// 3) la transición
switch ($transicion) {
    case 1:     // ---- T1: cualquier estado + Carga (o evento imposible, o !PL0) -> ST1, AC0, ES0 (ES1 con aviso)
    default:    //      (default: una transición que todavía no esté programada se trata igual)
        $estado = ST1;
        $ascensor = ascensorInicial();
        $mensaje = 'El ascensor espera en la planta baja.';
        break;

    // TODO: T2: ST1 + Llamar: la llamada con el ascensor parado
    // TODO: T3: ST2/ST3 + Llamar y T4: ST4 + Llamar (llamadas mientras se mueve o con las puertas abiertas)

    // TODO: T9: ST1-ST4 + Emergencia y T10: ST5 + Rearmar
}

// 4) el evento saliente (la página) y AC8: el nuevo estado y el ascensor viajan con ella
edificio($estado, $ascensor, $mensaje, $aviso);
guardar($estado, $ascensor);
?>
</form>
</body>
</html>
