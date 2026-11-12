<?php
/*
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
    // los botones de emergencia y de rearmar
    echo '<button name="evento" value="emergencia" class="rojo"' . ($funciona ? '' : ' disabled') . '>Parada de emergencia</button> ';
    echo '<button name="evento" value="rearmar"' . ($estado === ST5 ? '' : ' disabled') . '>Rearmar</button> ';
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
    // PL0: ¿cumplen los datos los invariantes del estado en el que dicen estar?
    $pl0 = coherente($estado, $ascensor);
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

    // T2: ST1 + Llamar: la llamada con el ascensor parado
    case 2:     // ---- T2: ST1 + Llamar. Antes: AC2 leer la planta
        $planta = leerPlanta($_POST);
        $pl1 = $planta !== null;                                // PL1: planta válida
        $pl2 = $planta === $ascensor['piso'];                   // PL2: es la planta en la que está
        if (!$pl1) {                                            // !PL1 -> ST1, ES2 con el error
            $mensaje = 'Esa planta no existe.';
        } elseif ($pl2) {                                       // PL1 && PL2 -> ST4, ES2
            $estado = ST4;
            $mensaje = 'El ascensor ya está aquí: se abren las puertas en la ' . PLANTAS[$planta] . '.';
        } else {                                                // PL1 && !PL2 -> ST1, AC3, ES2
            anadirLlamada($ascensor, $planta);
            $mensaje = 'Llamada desde la ' . PLANTAS[$planta] . '.';
        }
        break;

    // T3: ST2/ST3 + Llamar y T4: ST4 + Llamar (llamadas mientras se mueve o con las puertas abiertas)
    case 3:     // ---- T3: ST2/ST3 + Llamar. Antes: AC2
    case 4:     // ---- T4: ST4 + Llamar. Antes: AC2
        $planta = leerPlanta($_POST);
        $pl1 = $planta !== null;
        $pl2 = $planta === $ascensor['piso'];
        if (!$pl1) {                                            // !PL1 -> mismo estado, ES2 con el error
            $mensaje = 'Esa planta no existe.';
        } elseif ($transicion === 4 && $pl2) {                  // T4: PL1 && PL2 -> ST4, ES2
            $mensaje = 'Las puertas ya están abiertas en la ' . PLANTAS[$planta] . '.';
        } else {                                                // T3: PL1; T4: PL1 && !PL2 -> mismo estado, AC3
            anadirLlamada($ascensor, $planta);
            $mensaje = 'Llamada desde la ' . PLANTAS[$planta] . '.';
        }
        break;

    case 5:     // ---- T5: ST1 + Avanzar: arrancar hacia la llamada más cercana
        $pl3 = $ascensor['llamadas'] !== [];                    // PL3: hay llamadas
        $pl4 = masCercanaArriba($ascensor);                     // PL4: la más cercana está arriba
        if (!$pl3) {                                            // !PL3 -> ST1, ES2
            $mensaje = 'No hay llamadas: el ascensor sigue parado.';
        } elseif ($pl4) {                                       // PL3 && PL4 -> ST2, AC4 dirección "sube"
            $estado = ST2;
            $ascensor['direccion'] = 'sube';
            $mensaje = 'Se cierran las puertas y arranca hacia arriba.';
        } else {                                                // PL3 && !PL4 -> ST3, AC4 dirección "baja"
            $estado = ST3;
            $ascensor['direccion'] = 'baja';
            $mensaje = 'Se cierran las puertas y arranca hacia abajo.';
        }
        break;

    case 6:     // ---- T6: ST2 + Avanzar. Antes: AC5 subir una planta
    case 7:     // ---- T7: ST3 + Avanzar. Antes: AC5 bajar una planta
        mover($ascensor, $transicion === 6 ? 1 : -1);
        $pl5 = in_array($ascensor['piso'], $ascensor['llamadas'], true);    // PL5: hay llamada en esta planta
        if ($pl5) {                                             // PL5 -> ST4, AC6 parar, ES2
            $estado = ST4;
            parar($ascensor);
            $mensaje = 'Llega a la ' . PLANTAS[$ascensor['piso']] . ': se abren las puertas.';
        } else {                                                // !PL5 -> ST2 / ST3 (sigue), ES2
            $mensaje = 'Pasa por la ' . PLANTAS[$ascensor['piso']] . '.';
        }
        break;

    case 8:     // ---- T8: ST4 + Avanzar: se cierran las puertas y, si hay llamadas, sigue
        $pl3 = $ascensor['llamadas'] !== [];
        $pl6 = $ascensor['direccion'] === 'sube' && hayLlamadas($ascensor, 'arriba');   // PL6: subía y hay arriba
        $pl7 = $ascensor['direccion'] === 'baja' && hayLlamadas($ascensor, 'abajo');    // PL7: bajaba y hay abajo
        $pl4 = masCercanaArriba($ascensor);
        if (!$pl3) {                                            // !PL3 -> ST1, AC7 sin dirección, ES2
            $estado = ST1;
            $ascensor['direccion'] = '';
            $mensaje = 'Se cierran las puertas. No hay más llamadas.';
        } elseif ($pl6) {                                       // PL3 && PL6 -> ST2 (sigue subiendo), ES2
            $estado = ST2;
            $mensaje = 'Se cierran las puertas y sigue subiendo.';
        } elseif ($pl7) {                                       // PL3 && !PL6 && PL7 -> ST3 (sigue bajando), ES2
            $estado = ST3;
            $mensaje = 'Se cierran las puertas y sigue bajando.';
        } elseif ($pl4) {                                       // PL3 && !PL6 && !PL7 && PL4 -> ST2, AC4 "sube", ES2
            $estado = ST2;
            $ascensor['direccion'] = 'sube';
            $mensaje = 'Se cierran las puertas y sube.';
        } else {                                                // PL3 && !PL6 && !PL7 && !PL4 -> ST3, AC4 "baja", ES2
            $estado = ST3;
            $ascensor['direccion'] = 'baja';
            $mensaje = 'Se cierran las puertas y baja.';
        }
        break;

    // T9: ST1-ST4 + Emergencia y T10: ST5 + Rearmar
    case 9:     // ---- T9: ST1, ST2, ST3, ST4 + Emergencia -> ST5, AC9 (emergencias++, se borran las llamadas), ES3
        $estado = ST5;
        $ascensor['emergencias']++;
        $ascensor['llamadas'] = [];
        $ascensor['direccion'] = '';
        $mensaje = 'PARADA DE EMERGENCIA en la ' . PLANTAS[$ascensor['piso']] . '. Se han anulado todas las llamadas.';
        break;

    case 10:    // ---- T10: ST5 + Rearmar
        $pl8 = $ascensor['emergencias'] < MAX_EMERGENCIAS;      // PL8: quedan rearmes
        if ($pl8) {                                             // PL8 -> ST1, ES2
            $estado = ST1;
            $mensaje = 'Ascensor rearmado (emergencias: ' . $ascensor['emergencias'] . ' de ' . MAX_EMERGENCIAS . ').';
        } else {                                                // !PL8 -> ST6, ES4
            $estado = ST6;
            $mensaje = 'Fuera de servicio: ' . MAX_EMERGENCIAS . ' paradas de emergencia. Avisa al técnico (Reiniciar).';
        }
        break;
}

// 4) el evento saliente (la página) y AC8: el nuevo estado y el ascensor viajan con ella
edificio($estado, $ascensor, $mensaje, $aviso);
guardar($estado, $ascensor);
?>
</form>
</body>
</html>
