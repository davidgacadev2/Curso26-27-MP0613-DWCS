<?php
/*
 * >>> PASO 5 de 6: la máquina: al azar o ganar, tapar, no regalar y preferir el centro <<<
 *
 * TAREA-02 AVANZADA: Cuatro en raya
 *  - una única página que se envía a sí misma por POST; sin $_SESSION: la partida viaja en campos ocultos;
 *  - del tablero solo se guardan las JUGADAS ("3324": la columna de cada ficha, en orden). En cada petición se
 *    repiten con reconstruir(), que devuelve el tablero, la última ficha y el ganador, o null si las jugadas
 *    son imposibles: así validar el estado es casi gratis, y deshacer es quitar el último carácter;
 *  - contra la máquina, la máquina contesta en la misma petición en la que juega la persona.
 */
// cargar las funciones
require __DIR__ . '/funciones.php';

// valores iniciales: sin partida, sin errores; el formulario propone jugar contra la máquina en nivel normal
$partida = null;
$errores = [];
$mensaje = '';
$configuracion = ['modo' => 'maquina', 'jugador1' => '', 'jugador2' => '', 'nivel' => 'normal', 'empieza' => '1'];

// botón "Empezar": validar el formulario y crear la partida
if (isset($_POST['empezar'])) {
    [$configuracion, $errores] = validarConfiguracion($_POST);
    if ($errores === []) {
        $semilla = filter_var($_POST['semilla'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $partida = nuevaPartida($configuracion, $semilla === false ? random_int(1, 999999) : $semilla);
        // si empieza la máquina, juega ya
        $columnaMaquina = moverMaquina($partida);
        if ($columnaMaquina !== null) {
            $mensaje = 'La máquina ha empezado en la columna ' . ($columnaMaquina + 1) . '.';
        }
    }
}

// botón de una columna (o "Deshacer"): recuperar la partida de los campos ocultos
if (isset($_POST['columna']) || isset($_POST['deshacer'])) {
    $partida = leerEstado($_POST);
    // TODO: no fiarse del navegador: comprobar toda la partida (nombres, máquina, nivel, semilla y jugadas)
    $valida = reconstruir($partida['jugadas']) !== null;
    if (!$valida) {
        $errores[] = 'La partida recibida no es válida (¿se han modificado los campos ocultos?). Empieza una nueva.';
        $partida = null;
    }
}

// botón de una columna: soltar la ficha del jugador al que le toca
if ($partida !== null && isset($_POST['columna'])) {
    $situacion = reconstruir($partida['jugadas']);
    $columna = filter_var($_POST['columna'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => COLUMNAS - 1]]);
    if ($columna === false) {
        $errores[] = 'Esa columna no existe.';
    }
    // una partida terminada no admite más fichas
    elseif (estadoPartida($situacion, $partida['jugadas']) !== 'jugando') {
        $errores[] = 'La partida ha terminado: empieza otra.';
    }
    // nadie puede jugar por la máquina
    elseif (turno($partida['jugadas']) === $partida['maquina']) {
        $errores[] = 'Ahora le toca a la máquina.';
    }
    elseif (!in_array($columna, columnasLibres($situacion['tablero']), true)) {
        $errores[] = 'La columna ' . ($columna + 1) . ' está llena.';
    } else {
        $partida['jugadas'] .= $columna;
        // y contesta la máquina
        $columnaMaquina = moverMaquina($partida);
        if ($columnaMaquina !== null) {
            $mensaje = 'La máquina ha jugado en la columna ' . ($columnaMaquina + 1) . '.';
        }
    }
}

// TODO: botón "Deshacer": quitar la última jugada (contra la máquina, también la suya)

// botón "Nueva partida": volver al formulario con los jugadores de la partida anterior
if (isset($_POST['nueva'])) {
    $anterior = leerEstado($_POST);
    if ($anterior['maquina'] === ROJA || $anterior['maquina'] === AMARILLA) {
        $persona = $anterior['maquina'] === ROJA ? AMARILLA : ROJA;
        $configuracion = ['modo' => 'maquina', 'jugador1' => $anterior['nombres'][$persona], 'jugador2' => '',
            'nivel' => $anterior['nivel'], 'empieza' => $persona === ROJA ? '1' : '2'];
    } else {
        $configuracion = ['modo' => 'dos', 'jugador1' => $anterior['nombres'][ROJA],
            'jugador2' => $anterior['nombres'][AMARILLA], 'nivel' => 'normal', 'empieza' => '1'];
    }
}

// el tablero de la partida, repitiendo las jugadas
$situacion = $partida === null ? null : reconstruir($partida['jugadas']);

// ¿cómo va la partida? mensaje final
$estado = $situacion === null ? 'sin partida' : estadoPartida($situacion, $partida['jugadas']);
$final = '';
if ($estado === 'ganada') {
    $ganador = $situacion['ganador'];
    $final = "¡Cuatro en raya! Gana {$partida['nombres'][$ganador]} con las " . COLORES[$ganador] . ' en '
        . strlen($partida['jugadas']) . ' jugadas.';
} elseif ($estado === 'empate') {
    $final = 'El tablero está lleno: empate.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuatro en raya</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Cuatro en raya</h1>

<!-- mensajes de error -->
<?php if ($errores !== []): ?>
    <ul class="errores">
        <?php foreach ($errores as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- sin partida: formulario de configuración (pegajoso), con la semilla en un campo oculto -->
<?php if ($partida === null): ?>
    <form method="post" class="configuracion">
        <p>
            <label><input type="radio" name="modo" value="maquina"<?= $configuracion['modo'] === 'maquina' ? ' checked' : '' ?>> Contra la máquina</label>
            <label><input type="radio" name="modo" value="dos"<?= $configuracion['modo'] === 'dos' ? ' checked' : '' ?>> Dos personas</label>
        </p>
        <label>Jugador 1: <input name="jugador1" value="<?= e($configuracion['jugador1']) ?>" size="15"></label>
        <label>Jugador 2 (solo si juegan dos personas): <input name="jugador2" value="<?= e($configuracion['jugador2']) ?>" size="15"></label>
        <label>Nivel de la máquina:
            <select name="nivel">
                <?php foreach (NIVELES as $nivel => $descripcion): ?>
                    <option value="<?= e($nivel) ?>"<?= $nivel === $configuracion['nivel'] ? ' selected' : '' ?>><?= e($descripcion) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Empieza (con las rojas):
            <select name="empieza">
                <option value="1"<?= $configuracion['empieza'] === '1' ? ' selected' : '' ?>>el jugador 1</option>
                <option value="2"<?= $configuracion['empieza'] === '2' ? ' selected' : '' ?>>el jugador 2 o la máquina</option>
            </select>
        </label>
        <input type="hidden" name="semilla" value="<?= random_int(1, 999999) ?>">
        <button name="empezar">Empezar</button>
    </form>
<?php endif; ?>

<!-- con partida: los jugadores y a quién le toca -->
<?php if ($partida !== null): ?>
    <p class="jugadores">
        <span class="ficha R"></span> <?= e($partida['nombres'][ROJA]) ?> ·
        <span class="ficha A"></span> <?= e($partida['nombres'][AMARILLA]) ?>
    </p>
    <?php if ($estado === 'jugando'): ?>
        <?php $toca = turno($partida['jugadas']); ?>
        <p class="turno">Turno de <strong><?= e($partida['nombres'][$toca]) ?></strong> (<?= COLORES[$toca] ?>)</p>
    <?php endif; ?>
    <!-- el mensaje de la jugada de la máquina (o de deshacer) -->
    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= e($mensaje) ?></p>
    <?php endif; ?>
    <!-- mensaje de fin de partida -->
    <?php if ($final !== ''): ?>
        <p class="final"><?= e($final) ?></p>
    <?php endif; ?>
    <form method="post">
        <!-- el tablero: un botón encima de cada columna y una casilla por posición (con la ganadora y la última resaltadas) -->
        <table class="tablero">
            <tr>
                <?php $libres = columnasLibres($situacion['tablero']); ?>
                <?php for ($c = 0; $c < COLUMNAS; $c++): ?>
                    <th><button name="columna" value="<?= $c ?>"<?= $estado !== 'jugando' || !in_array($c, $libres, true) ? ' disabled' : '' ?>><?= $c + 1 ?></button></th>
                <?php endfor; ?>
            </tr>
            <?php foreach ($situacion['tablero'] as $f => $fila): ?>
                <tr>
                    <?php foreach ($fila as $c => $casilla): ?>
                        <?php $clases = $casilla === VACIA ? 'vacia' : "ficha $casilla"; ?>
                        <?php $clases .= in_array("$f-$c", $situacion['linea'], true) ? ' ganadora' : ''; ?>
                        <?php $clases .= "$f-$c" === $situacion['ultima'] ? ' ultima' : ''; ?>
                        <td><span class="<?= $clases ?>"></span></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
        <!-- guardar la partida en campos ocultos: los jugadores, la máquina, el nivel, la semilla y las jugadas -->
        <input type="hidden" name="nombreR" value="<?= e($partida['nombres'][ROJA]) ?>">
        <input type="hidden" name="nombreA" value="<?= e($partida['nombres'][AMARILLA]) ?>">
        <input type="hidden" name="maquina" value="<?= e($partida['maquina']) ?>">
        <input type="hidden" name="nivel" value="<?= e($partida['nivel']) ?>">
        <input type="hidden" name="semilla" value="<?= $partida['semilla'] ?>">
        <input type="hidden" name="jugadas" value="<?= e($partida['jugadas']) ?>">
        <p>
            <!-- TODO: botón para deshacer la última jugada -->
            <!-- botón para empezar otra partida con los mismos jugadores -->
            <button name="nueva">Nueva partida</button>
        </p>
    </form>
    <!-- TODO: el historial de jugadas -->
<?php endif; ?>
</body>
</html>
