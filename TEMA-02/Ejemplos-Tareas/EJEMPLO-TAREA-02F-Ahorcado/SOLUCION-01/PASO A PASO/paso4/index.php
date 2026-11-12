<?php
/*
 * >>> PASO 4 de 6: fallos calculados, dibujo del ahorcado y fin de la partida (ganada o perdida) <<<
 *
 * TAREA-02 MEDIA: El ahorcado
 *  - una única página que se envía a sí misma por POST; sin $_SESSION: la partida viaja en campos ocultos;
 *  - en la página no aparece nunca la palabra secreta: solo su categoría y su posición en la lista
 *    (palabras.php), y las letras que ya se han probado; los fallos se calculan, no se guardan;
 *  - arriba, la lógica (qué botón se ha pulsado); abajo, el HTML; las funciones están en funciones.php.
 */
// cargar las funciones y las palabras
require __DIR__ . '/funciones.php';
$palabras = require __DIR__ . '/palabras.php';

// valores iniciales: sin partida y sin errores; el formulario propone Animales en dificultad normal
$partida = null;
$errores = [];
$mensaje = '';
$configuracion = ['categoria' => 'Animales', 'dificultad' => 'normal'];
// TODO: el marcador de victorias y derrotas también viaja en campos ocultos

// botón "Empezar": validar la configuración y elegir la palabra
if (isset($_POST['empezar'])) {
    [$configuracion, $errores] = validarConfiguracion($_POST, $palabras);
    if ($errores === []) {
        $semilla = filter_var($_POST['semilla'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $partida = nuevaPartida($configuracion, $palabras, $semilla === false ? random_int(1, PHP_INT_MAX) : $semilla);
    }
}

// botón de una letra (o "Resolver"): recuperar la partida de los campos ocultos y jugar
if (isset($_POST['letra']) || isset($_POST['resolver'])) {
    $partida = leerEstado($_POST);
    // TODO: no fiarse del navegador: comprobar que el estado recibido es una partida posible
    if ($partida !== null) {
        $palabra = palabraSecreta($partida, $palabras);
        // ¿cómo estaba la partida antes de esta jugada? (una partida terminada no admite más jugadas)
        $antes = estadoPartida($partida, $palabra);
        if ($antes !== 'jugando') {
            $mensaje = 'La partida ya ha terminado: empieza otra.';
        } elseif (isset($_POST['letra'])) {
            $mensaje = probarLetra($partida, $_POST['letra'], $palabra);
        }
        // TODO: botón "Resolver": decir la palabra entera
        // TODO: si esta jugada termina la partida, apuntarla en el marcador
    }
}

// TODO: botón "Nueva partida": volver al formulario con la categoría y la dificultad anteriores

// la palabra de la partida (solo para calcular: no se escribe en la página)
$palabra = $partida === null ? '' : palabraSecreta($partida, $palabras);

// ¿cómo va la partida? fallos y mensaje final
$estado = 'sin partida';
$fallos = 0;
$final = '';
if ($partida !== null) {
    $fallos = contarFallos($partida, $palabra);
    $estado = estadoPartida($partida, $palabra);
    if ($estado === 'ganada') {
        $final = "¡Has acertado {$palabra} con {$fallos} " . ($fallos === 1 ? 'fallo' : 'fallos') . '!';
    } elseif ($estado === 'perdida') {
        $final = "Te han ahorcado. La palabra era {$palabra}.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El ahorcado</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>El ahorcado</h1>

<!-- mensajes de error -->
<?php if ($errores !== []): ?>
    <ul class="errores">
        <?php foreach ($errores as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- TODO: marcador de las partidas jugadas -->

<!-- sin partida: formulario de configuración (pegajoso), con la semilla en un campo oculto -->
<?php if ($partida === null): ?>
    <form method="post" class="configuracion">
        <label>Categoría:
            <select name="categoria">
                <?php foreach (array_keys($palabras) as $categoria): ?>
                    <option<?= $categoria === $configuracion['categoria'] ? ' selected' : '' ?>><?= e($categoria) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Dificultad:
            <select name="dificultad">
                <?php foreach (DIFICULTADES as $dificultad => $permitidos): ?>
                    <option value="<?= e($dificultad) ?>"<?= $dificultad === $configuracion['dificultad'] ? ' selected' : '' ?>><?= e($dificultad) ?> (<?= $permitidos ?> fallos)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <input type="hidden" name="semilla" value="<?= random_int(1, 999999) ?>">
        <!-- TODO: el marcador sigue en la partida siguiente -->
        <button name="empezar">Empezar</button>
    </form>
<?php endif; ?>

<!-- con partida: la categoría y el número de letras -->
<?php if ($partida !== null): ?>
    <p class="datos">Categoría: <strong><?= e($partida['categoria']) ?></strong> ·
        <?= mb_strlen(str_replace(' ', '', $palabra)) ?> letras
        <!-- fallos de la partida -->
        · Fallos: <strong><?= $fallos ?></strong> de <?= $partida['maxFallos'] ?>
    </p>
    <!-- el dibujo del ahorcado según los fallos -->
    <pre class="dibujo <?= e($estado) ?>"><?= e(dibujo($fallos, $partida['maxFallos'])) ?></pre>
    <!-- la palabra con huecos (al perder, con las letras que faltaban) y el mensaje de la jugada -->
    <p class="palabra">
        <?php foreach (palabraOculta($palabra, letrasUsadas($partida), $estado === 'perdida') as $casilla): ?>
            <span class="<?= $casilla['clase'] ?>"><?= e($casilla['letra']) ?></span>
        <?php endforeach; ?>
    </p>
    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= e($mensaje) ?></p>
    <?php endif; ?>
    <!-- mensaje de fin de partida -->
    <?php if ($final !== ''): ?>
        <p class="final <?= e($estado) ?>"><?= e($final) ?></p>
    <?php endif; ?>
    <form method="post">
        <!-- TODO: botón invisible: Intro en el campo de la palabra pulsa "Resolver" y no la letra A -->
        <!-- el teclado: un botón por letra; las ya usadas, deshabilitadas y marcadas como acierto o fallo -->
        <div class="teclado">
            <?php $letrasPalabra = mb_str_split(quitarTildes($palabra)); ?>
            <?php foreach (mb_str_split(ABECEDARIO) as $letra): ?>
                <?php $usada = in_array($letra, letrasUsadas($partida), true); ?>
                <?php $clase = !$usada ? '' : (in_array($letra, $letrasPalabra, true) ? 'acierto' : 'fallo'); ?>
                <button name="letra" value="<?= $letra ?>" class="<?= $clase ?>"<?= $usada || $estado !== 'jugando' ? ' disabled' : '' ?>><?= $letra ?></button>
            <?php endforeach; ?>
        </div>
        <!-- TODO: resolver: escribir la palabra entera (mientras se juega) -->
        <!-- guardar la partida en campos ocultos (la palabra no: solo la categoría y el índice) -->
        <input type="hidden" name="categoria" value="<?= e($partida['categoria']) ?>">
        <input type="hidden" name="indice" value="<?= $partida['indice'] ?>">
        <input type="hidden" name="maxfallos" value="<?= $partida['maxFallos'] ?>">
        <input type="hidden" name="usadas" value="<?= e($partida['usadas']) ?>">
        <input type="hidden" name="palabrasfalladas" value="<?= $partida['palabrasFalladas'] ?>">
        <!-- TODO: el marcador y el botón para jugar otra -->
    </form>
<?php endif; ?>
</body>
</html>
