<?php
/*
 *
 * TAREA-02 MEDIA: Memoria de parejas
 *  - una única página que se envía a sí misma por POST; sin $_SESSION: toda la partida (el tablero, las
 *    parejas encontradas, la carta pendiente y el marcador) viaja en campos ocultos;
 *  - arriba, la lógica (qué botón se ha pulsado); abajo, el HTML; las funciones están en funciones.php.
 */
// cargar las funciones
require __DIR__ . '/funciones.php';

// valores iniciales: sin partida, sin errores; el formulario propone 4 x 4 con 8 letras
$partida = null;
$errores = [];
$mensaje = '';
$temporales = [];                               // cartas de un fallo, que solo se ven en esta respuesta
$configuracion = ['filas' => 4, 'columnas' => 4, 'letras' => 'ABCDEFGH', 'maxFallos' => 8];

// botón "Empezar": validar el formulario de configuración
if (isset($_POST['empezar'])) {
    [$configuracion, $errores] = validarEntrada($_POST);
    // si no hay errores, empezar la partida con el tablero barajado
    if ($errores === []) {
        $semilla = filter_var($_POST['semilla'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $partida = nuevaPartida($configuracion, $semilla === false ? random_int(1, PHP_INT_MAX) : $semilla);
    }
}

// botón de una carta: recuperar la partida de los campos ocultos
if (isset($_POST['carta'])) {
    $partida = leerEstado($_POST);
    // no fiarse del navegador: comprobar que el estado recibido es una partida posible
    if (!estadoValido($partida)) {
        $errores[] = 'La partida recibida no es válida (¿se han modificado los campos ocultos?). Empieza una nueva.';
        $partida = null;
    }
    // girar la carta pulsada
    if ($partida !== null) {
        $posicion = is_string($_POST['carta']) ? $_POST['carta'] : '';
        [$mensaje, $temporales] = jugar($partida, $posicion);
    }
}

// botón "Nueva partida": volver al formulario con la configuración anterior
if (isset($_POST['nueva'])) {
    $anterior = leerEstado($_POST);
    $configuracion = [
        'filas' => $anterior['filas'],
        'columnas' => $anterior['columnas'],
        'letras' => $anterior['letras'],
        'maxFallos' => $anterior['maxFallos'],
    ];
}

// ¿ha terminado la partida? mensaje final
$estado = $partida === null ? 'sin partida' : estadoPartida($partida);
if ($estado === 'ganada') {
    $mensaje = 'Has encontrado las ' . intdiv($partida['filas'] * $partida['columnas'], 2)
        . " parejas con {$partida['puntos']} puntos. ¡Enhorabuena!";
} elseif ($estado === 'perdida') {
    $mensaje = "Has superado los {$partida['maxFallos']} fallos permitidos: fin de la partida.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memoria de parejas</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Memoria de parejas</h1>

<!-- mensajes de error -->
<?php if ($errores !== []): ?>
    <ul class="errores">
        <?php foreach ($errores as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- sin partida: formulario de configuración (pegajoso), con la semilla del barajado en un campo oculto -->
<?php if ($partida === null): ?>
    <form method="post" class="configuracion">
        <label>Filas (<?= MIN_LADO ?>-<?= MAX_LADO ?>): <input name="filas" value="<?= e($configuracion['filas']) ?>" size="3"></label>
        <label>Columnas (<?= MIN_LADO ?>-<?= MAX_LADO ?>): <input name="columnas" value="<?= e($configuracion['columnas']) ?>" size="3"></label>
        <label>Letras de las cartas: <input name="letras" value="<?= e($configuracion['letras']) ?>" size="20"></label>
        <label>Fallos permitidos (<?= MIN_FALLOS ?>-<?= MAX_FALLOS ?>): <input name="maxfallos" value="<?= e($configuracion['maxFallos']) ?>" size="3"></label>
        <input type="hidden" name="semilla" value="<?= random_int(1, 999999) ?>">
        <button name="empezar">Empezar</button>
        <button type="reset">Borrar</button>
    </form>
<?php endif; ?>

<!-- con partida: el tablero; cada carta boca abajo es un botón -->
<?php if ($partida !== null): ?>
    <!-- mensaje de la jugada y marcador (a partir de la primera carta girada) -->
    <?php if ($mensaje !== ''): ?>
        <p class="mensaje <?= e($estado) ?>"><?= e($mensaje) ?></p>
    <?php endif; ?>
    <?php if ($partida['giradas'] > 0): ?>
        <p class="marcador">
            Puntos: <strong><?= $partida['puntos'] ?></strong> ·
            Parejas: <?= intdiv(count($partida['encontradas']), 2) ?> de <?= intdiv($partida['filas'] * $partida['columnas'], 2) ?> ·
            Fallos: <?= $partida['fallos'] ?> (se permiten <?= $partida['maxFallos'] ?>)
        </p>
    <?php endif; ?>
    <form method="post">
        <table class="tablero">
            <?php foreach ($partida['cartas'] as $f => $fila): ?>
                <tr>
                    <?php foreach ($fila as $c => $letra): ?>
                        <?php $posicion = "$f-$c"; ?>
                        <td>
                            <?php if (cartaVisible($partida, $posicion, $temporales)): ?>
                                <span class="carta <?= in_array($posicion, $partida['encontradas'], true) ? 'encontrada' : 'girada' ?>"><?= e($letra) ?></span>
                            <?php else: ?>
                                <button class="carta" name="carta" value="<?= e($posicion) ?>"<?= $estado === 'jugando' ? '' : ' disabled' ?>>?</button>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
        <!-- guardar la partida en campos ocultos: configuración y tablero (una carta por campo) -->
        <input type="hidden" name="filas" value="<?= $partida['filas'] ?>">
        <input type="hidden" name="columnas" value="<?= $partida['columnas'] ?>">
        <input type="hidden" name="letras" value="<?= e($partida['letras']) ?>">
        <input type="hidden" name="maxfallos" value="<?= $partida['maxFallos'] ?>">
        <?php foreach ($partida['cartas'] as $f => $fila): ?>
            <?php foreach ($fila as $c => $letra): ?>
                <input type="hidden" name="cartas[<?= $f ?>][<?= $c ?>]" value="<?= e($letra) ?>">
            <?php endforeach; ?>
        <?php endforeach; ?>
        <!-- guardar en campos ocultos las parejas encontradas, la carta pendiente y el marcador -->
        <input type="hidden" name="encontradas" value="<?= e(implode(',', $partida['encontradas'])) ?>">
        <input type="hidden" name="pendiente" value="<?= e($partida['pendiente']) ?>">
        <input type="hidden" name="puntos" value="<?= $partida['puntos'] ?>">
        <input type="hidden" name="fallos" value="<?= $partida['fallos'] ?>">
        <input type="hidden" name="giradas" value="<?= $partida['giradas'] ?>">
        <!-- botón para empezar otra partida con la misma configuración -->
        <p><button name="nueva">Nueva partida</button></p>
    </form>
<?php endif; ?>
</body>
</html>
