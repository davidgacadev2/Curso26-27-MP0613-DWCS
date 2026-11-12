<?php
/*
 * >>> PASO 2 de 6: formulario de configuración, elegir la palabra con una semilla y guardar la partida en campos ocultos <<<
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

// TODO: botón de una letra (o "Resolver"): recuperar la partida de los campos ocultos y jugar
    // TODO: no fiarse del navegador: comprobar que el estado recibido es una partida posible
        // TODO: ¿cómo estaba la partida antes de esta jugada? (una partida terminada no admite más jugadas)
        // TODO: botón "Resolver": decir la palabra entera
        // TODO: si esta jugada termina la partida, apuntarla en el marcador

// TODO: botón "Nueva partida": volver al formulario con la categoría y la dificultad anteriores

// la palabra de la partida (solo para calcular: no se escribe en la página)
$palabra = $partida === null ? '' : palabraSecreta($partida, $palabras);

// TODO: ¿cómo va la partida? fallos y mensaje final
$estado = $partida === null ? 'sin partida' : 'jugando';
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
        <!-- TODO: fallos de la partida -->
    </p>
    <!-- TODO: el dibujo del ahorcado según los fallos -->
    <!-- TODO: la palabra con huecos (al perder, con las letras que faltaban) y el mensaje de la jugada -->
    <!-- TODO: mensaje de fin de partida -->
    <form method="post">
        <!-- TODO: botón invisible: Intro en el campo de la palabra pulsa "Resolver" y no la letra A -->
        <!-- TODO: el teclado: un botón por letra; las ya usadas, deshabilitadas y marcadas como acierto o fallo -->
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
