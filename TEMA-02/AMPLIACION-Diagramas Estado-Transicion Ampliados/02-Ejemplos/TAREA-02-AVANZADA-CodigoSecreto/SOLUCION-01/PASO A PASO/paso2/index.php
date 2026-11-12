<?php
/*
 * >>> PASO 2 de 6: el autómata en PHP: constantes, tabla de eventos-estados, estado y evento recibidos, T1 (formulario de configuración) y el estado en campos ocultos <<<
 *
 * TAREA-02 AVANZADA: Código secreto
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "../../../DIAGRAMA ESTADO-TRANSICION/Automata-CodigoSecreto.html")
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

// TODO: ficha(int $color, string $clase): una ficha de color

// TODO: ES2: la partida en juego: intentos anteriores, pista y el formulario del intento (con un aviso opcional)
    // TODO: el historial de intentos y los intentos que quedan
    // TODO: la pista, si se ha pedido
    // TODO: el formulario del intento: un desplegable por posición, que recuerda el último intento
    // TODO: los botones de pista y de rendirse

// TODO: historial(array $partida, array $secreto): una fila por intento con sus fichas, las negras y las blancas

// TODO: ES3 / ES4: partida ganada o perdida: el historial, el código y el marcador
    // TODO: botones para jugar otra vez o cambiar la configuración

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

    // TODO: T2: ST1 + Empezar: validar la configuración y empezar la partida
            // TODO: para poder probar, el formulario puede mandar la semilla (si no, una al azar)
    // TODO: T3: ST2 + Probar
        // TODO: PL1: ¿es una partida posible? (si no: la página se ha manipulado)

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
