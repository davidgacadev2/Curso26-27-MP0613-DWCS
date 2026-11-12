<?php
/*
 * >>> PASO 2 de 6: formulario de configuración y validación con mensajes de error (formulario pegajoso) <<<
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
    // TODO: si no hay errores, empezar la partida con el tablero barajado
}

// TODO: botón de una carta: recuperar la partida de los campos ocultos
    // TODO: no fiarse del navegador: comprobar que el estado recibido es una partida posible
    // TODO: girar la carta pulsada

// TODO: botón "Nueva partida": volver al formulario con la configuración anterior

// TODO: ¿ha terminado la partida? mensaje final
$estado = $partida === null ? 'sin partida' : 'jugando';
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

<!-- TODO: con partida: el tablero; cada carta boca abajo es un botón -->
    <!-- TODO: mensaje de la jugada y marcador (a partir de la primera carta girada) -->
        <!-- TODO: guardar la partida en campos ocultos: configuración y tablero (una carta por campo) -->
        <!-- TODO: guardar en campos ocultos las parejas encontradas, la carta pendiente y el marcador -->
        <!-- TODO: botón para empezar otra partida con la misma configuración -->
</body>
</html>
