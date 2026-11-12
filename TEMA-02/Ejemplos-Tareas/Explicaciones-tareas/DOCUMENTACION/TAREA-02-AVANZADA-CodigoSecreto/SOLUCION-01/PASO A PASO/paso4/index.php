<?php
/*
 * >>> PASO 4 de 6: T3 Probar: leer el intento, puntuar con negras y blancas, historial y fin de la partida ganada o perdida <<<
 *
 * TAREA-02 AVANZADA: Código secreto
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "DIAGRAMA ESTADO-TRANSICION/Automata-CodigoSecreto.pdf")
 *  - 5 estados, 7 eventos entrantes y 7 transiciones; una única página que se envía a sí misma por POST;
 *  - el estado y la partida viajan en campos ocultos (sin $_SESSION); el código secreto no: se calcula;
 *  - cada transición es un "case" del switch; cada evento saliente (ES), una función que escribe la página.
 */
// cargar las acciones y los predicados
require __DIR__ . '/funciones.php';

// estados (ST) y eventos entrantes (EE) del autómata
const ST0 = 0;          // Carga de página
const ST1 = 1;          // Configuración
const ST2 = 2;          // Jugando
const ST3 = 3;          // Ganada
const ST4 = 4;          // Perdida
const EE0 = 0;          // Carga (sin evento)
const EE1 = 1;          // Empezar
const EE2 = 2;          // Probar
const EE3 = 3;          // Pista
const EE4 = 4;          // Rendirse
const EE5 = 5;          // Nueva partida
const EE6 = 6;          // Cambiar la configuración
const EVENTOS = ['empezar' => EE1, 'probar' => EE2, 'pista' => EE3, 'rendirse' => EE4, 'nueva' => EE5, 'configurar' => EE6];

// tabla de eventos-estados ampliada: $automata[estado][evento] = transición (null = "---")
//                Carga Empezar Probar Pista Rendirse Nueva Configurar
$automata = [
    ST0 => [1, 1, 1, 1, 1, 1, 1],
    ST1 => [1, 2, null, null, null, null, null],
    ST2 => [1, null, 3, 4, 5, null, null],
    ST3 => [1, null, null, null, null, 6, 7],
    ST4 => [1, null, null, null, null, 6, 7],
];

// ----- Eventos salientes (ES): cada uno escribe una variante de la página ------------------------------------

// marcador(array $partida): las partidas ganadas y perdidas
function marcador(array $partida): void
{
    echo '<p class="marcador">Ganadas: <strong>' . $partida['ganadas'] . '</strong> · Perdidas: <strong>'
        . $partida['perdidas'] . '</strong></p>';
}

// ES0 / ES1: el formulario de configuración (ES1, con los errores o un aviso)
function es0(array $partida, array $errores = []): void
{
    if ($errores !== []) {
        echo '<ul class="errores">';
        foreach ($errores as $error) {
            echo '<li>' . e($error) . '</li>';
        }
        echo '</ul>';
    }
    echo '<h2>Nueva partida</h2><p><label>Posiciones: <select name="posiciones">';
    foreach (POSICIONES as $n) {
        echo "<option" . ($n === $partida['posiciones'] ? ' selected' : '') . ">$n</option>";
    }
    echo '</select></label> <label>Colores: <select name="colores">';
    foreach (NUM_COLORES as $n) {
        echo "<option" . ($n === $partida['colores'] ? ' selected' : '') . ">$n</option>";
    }
    echo '</select></label> <label>Intentos (' . MIN_INTENTOS . '-' . MAX_INTENTOS . '): <input name="maxintentos" size="3" value="'
        . e($partida['maxIntentos']) . '"></label> <label><input type="checkbox" name="repetir"'
        . ($partida['repetir'] ? ' checked' : '') . '> Los colores se pueden repetir</label></p>';
    marcador($partida);
    echo '<p><button name="evento" value="empezar">Empezar</button></p>';
}

// ficha(int $color, string $clase): una ficha de color
function ficha(int $color, string $clase = 'ficha'): string
{
    return '<span class="' . $clase . '" style="background:' . COLORES[$color][1] . '" title="' . COLORES[$color][0] . '"></span>';
}

// ES2: la partida en juego: intentos anteriores, pista y el formulario del intento (con un aviso opcional)
function es2(array $partida, array $secreto, string $aviso = ''): void
{
    echo '<h2>Adivina el código: ' . $partida['posiciones'] . ' posiciones, ' . $partida['colores'] . ' colores'
        . ($partida['repetir'] ? ', que se pueden repetir' : ', sin repetir') . '</h2>';
    // el historial de intentos y los intentos que quedan
    historial($partida, $secreto);
    echo '<p class="restantes">Te quedan <strong>' . intentosRestantes($partida) . '</strong> intentos.</p>';
    // TODO: la pista, si se ha pedido
    if ($aviso !== '') {
        echo '<p class="aviso">' . e($aviso) . '</p>';
    }
    // el formulario del intento: un desplegable por posición, que recuerda el último intento
    $ultimo = $partida['intentos'] === [] ? [] : end($partida['intentos']);
    echo '<p class="intento">';
    for ($i = 0; $i < $partida['posiciones']; $i++) {
        echo '<select name="intento[' . $i . ']"><option value="">--</option>';
        for ($c = 0; $c < $partida['colores']; $c++) {
            echo '<option value="' . $c . '"' . (($ultimo[$i] ?? null) === $c ? ' selected' : '') . '>' . COLORES[$c][0] . '</option>';
        }
        echo '</select> ';
    }
    echo '<button name="evento" value="probar">Probar</button></p>';
    // TODO: los botones de pista y de rendirse
    marcador($partida);
}

// historial(array $partida, array $secreto): una fila por intento con sus fichas, las negras y las blancas
function historial(array $partida, array $secreto): void
{
    if ($partida['intentos'] === []) {
        return;
    }
    echo '<table class="historial"><tr><th>#</th><th>Intento</th><th>Negras</th><th>Blancas</th></tr>';
    foreach ($partida['intentos'] as $n => $intento) {
        [$negras, $blancas] = puntuar($intento, $secreto);
        echo '<tr><td>' . ($n + 1) . '</td><td>' . implode('', array_map('ficha', $intento)) . '</td>'
            . '<td class="negras">' . str_repeat('●', $negras) . " $negras</td>"
            . '<td class="blancas">' . str_repeat('○', $blancas) . " $blancas</td></tr>";
    }
    echo '</table>';
}

// ES3 / ES4: partida ganada o perdida: el historial, el código y el marcador
function esFinal(array $partida, array $secreto, string $mensaje, string $clase): void
{
    echo '<h2 class="' . $clase . '">' . e($mensaje) . '</h2>';
    historial($partida, $secreto);
    echo '<p class="secreto">El código era: ' . implode('', array_map('ficha', $secreto)) . '</p>';
    marcador($partida);
    // TODO: botones para jugar otra vez o cambiar la configuración
}

// AC10 guardar(int $estado, array $partida): el estado y la partida en campos ocultos
function guardar(int $estado, array $partida): void
{
    $campos = ['estado' => $estado, 'ganadas' => $partida['ganadas'], 'perdidas' => $partida['perdidas']];
    // en ST1 la configuración está en el formulario: si fuera también en campos ocultos, el navegador mandaría
    // los dos valores y PHP se quedaría con el último (el oculto)
    if ($estado !== ST1) {
        $campos += [
            'posiciones' => $partida['posiciones'],
            'colores' => $partida['colores'],
            'maxintentos' => $partida['maxIntentos'],
            'repite' => $partida['repetir'] ? '1' : '0',
            'semilla' => $partida['semilla'],
            'intentos' => textoIntentos($partida['intentos']),
            'pista' => $partida['pista'],
        ];
    }
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
    <title>Código secreto</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Código secreto</h1>
<form method="post">
<?php
// 1) el estado (campo oculto, validado) y el evento entrante (el botón pulsado; sin botón, Carga)
$estado = filter_var($_POST['estado'] ?? '', FILTER_VALIDATE_INT);
if ($estado === false || !isset($automata[$estado])) {
    $estado = ST0;
}
$evento = EVENTOS[is_string($_POST['evento'] ?? null) ? $_POST['evento'] : ''] ?? EE0;

// 2) la transición que dice la tabla (un evento imposible, "---", se trata como T1)
switch ($automata[$estado][$evento] ?? 1) {
    case 1:     // ---- T1: cualquier estado + Carga (o evento imposible) -> ST1, marcador a cero (AC0), ES0
    default:    //      (default: una transición de la tabla que todavía no esté programada se trata igual)
        $estado = ST1;
        $partida = nuevaPartida(['posiciones' => 4, 'colores' => 6, 'maxIntentos' => 10, 'repetir' => false], 0, 0, 0);
        es0($partida);
        break;

    // T2: ST1 + Empezar: validar la configuración y empezar la partida
    case 2:     // ---- T2: ST1 + Empezar. Antes: AC1 recuperar el marcador, AC2 leer la configuración
        $anterior = leerPartida($_POST);
        [$configuracion, $errores] = validarConfiguracion($_POST);
        $pl0 = $errores === [];                                         // PL0: configuración válida
        $partida = nuevaPartida($configuracion, random_int(1, 999999), $anterior['ganadas'], $anterior['perdidas']);
        if (!$pl0) {                                                    // !PL0 -> ST1, ES1
            $estado = ST1;
            es0($partida, $errores);
        } else {                                                        // PL0 -> ST2, AC3 nueva partida, ES2
            // para poder probar, el formulario puede mandar la semilla (si no, una al azar)
            $semilla = filter_var($_POST['semilla'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($semilla !== false) {
                $partida['semilla'] = $semilla;
            }
            $estado = ST2;
            es2($partida, codigoSecreto($partida));
        }
        break;

    // T3: ST2 + Probar
    case 3:     // ---- T3. Antes: AC1 recuperar la partida, AC4 calcular el código secreto, AC5 leer el intento
        $partida = leerPartida($_POST);
        // TODO: PL1: ¿es una partida posible? (si no: la página se ha manipulado)
        $pl1 = true;
        if (!$pl1) {                                                    // !PL1 -> ST1, ES1
            $estado = ST1;
            $partida = nuevaPartida(['posiciones' => 4, 'colores' => 6, 'maxIntentos' => 10, 'repetir' => false], 0, 0, 0);
            es0($partida, ['La partida recibida no es válida (¿se han modificado los campos ocultos?). Empieza otra.']);
            break;
        }
        $secreto = codigoSecreto($partida);
        [$intento, $motivo] = leerIntento($_POST, $partida);
        $pl2 = $intento !== null;                                       // PL2: intento completo y válido
        if (!$pl2) {                                                    // PL1 && !PL2 -> ST2, ES2 con aviso
            es2($partida, $secreto, $motivo);
            break;
        }
        $partida['intentos'][] = $intento;                              // AC6: añadir el intento
        $pl3 = $intento === $secreto;                                   // PL3: acierta
        $pl4 = intentosRestantes($partida) === 0;                       // PL4: era el último intento
        if ($pl3) {                                                     // PL1 && PL2 && PL3 -> ST3, AC7, ES3
            $estado = ST3;
            $partida['ganadas']++;
            $n = count($partida['intentos']);
            esFinal($partida, $secreto, "¡Has descubierto el código en $n " . ($n === 1 ? 'intento' : 'intentos') . '!', 'ganada');
        } elseif ($pl4) {                                               // ... && !PL3 && PL4 -> ST4, AC9, ES4
            $estado = ST4;
            $partida['perdidas']++;
            esFinal($partida, $secreto, 'Se han acabado los intentos.', 'perdida');
        } else {                                                        // ... && !PL3 && !PL4 -> ST2, ES2
            [$negras, $blancas] = puntuar($intento, $secreto);
            es2($partida, $secreto, "$negras negras y $blancas blancas.");
        }
        break;

    // TODO: T4: ST2 + Pista y T5: ST2 + Rendirse
        // TODO: PL1: ¿es una partida posible?

    // TODO: T6: ST3/ST4 + Nueva partida y T7: ST3/ST4 + Cambiar la configuración
}

// 3) AC10: el nuevo estado y la partida viajan con la página
guardar($estado, $partida);
?>
</form>
</body>
</html>
