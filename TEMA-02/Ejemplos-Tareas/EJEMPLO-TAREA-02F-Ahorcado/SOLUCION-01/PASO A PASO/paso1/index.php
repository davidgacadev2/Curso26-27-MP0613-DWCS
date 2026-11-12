<?php
/*
 * >>> PASO 1 de 6: descomposición del problema (solo TODOs y el esqueleto HTML, sin código PHP) <<<
 *
 * TAREA-02 MEDIA: El ahorcado
 *  - una única página que se envía a sí misma por POST; sin $_SESSION: la partida viaja en campos ocultos;
 *  - en la página no aparece nunca la palabra secreta: solo su categoría y su posición en la lista
 *    (palabras.php), y las letras que ya se han probado; los fallos se calculan, no se guardan;
 *  - arriba, la lógica (qué botón se ha pulsado); abajo, el HTML; las funciones están en funciones.php.
 */
// TODO: cargar las funciones y las palabras

// TODO: valores iniciales: sin partida y sin errores; el formulario propone Animales en dificultad normal
// TODO: el marcador de victorias y derrotas también viaja en campos ocultos

// TODO: botón "Empezar": validar la configuración y elegir la palabra

// TODO: botón de una letra (o "Resolver"): recuperar la partida de los campos ocultos y jugar
    // TODO: no fiarse del navegador: comprobar que el estado recibido es una partida posible
        // TODO: ¿cómo estaba la partida antes de esta jugada? (una partida terminada no admite más jugadas)
        // TODO: botón "Resolver": decir la palabra entera
        // TODO: si esta jugada termina la partida, apuntarla en el marcador

// TODO: botón "Nueva partida": volver al formulario con la categoría y la dificultad anteriores

// TODO: la palabra de la partida (solo para calcular: no se escribe en la página)

// TODO: ¿cómo va la partida? fallos y mensaje final
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

<!-- TODO: mensajes de error -->

<!-- TODO: marcador de las partidas jugadas -->

<!-- TODO: sin partida: formulario de configuración (pegajoso), con la semilla en un campo oculto -->
        <!-- TODO: el marcador sigue en la partida siguiente -->

<!-- TODO: con partida: la categoría y el número de letras -->
        <!-- TODO: fallos de la partida -->
    <!-- TODO: el dibujo del ahorcado según los fallos -->
    <!-- TODO: la palabra con huecos (al perder, con las letras que faltaban) y el mensaje de la jugada -->
    <!-- TODO: mensaje de fin de partida -->
        <!-- TODO: botón invisible: Intro en el campo de la palabra pulsa "Resolver" y no la letra A -->
        <!-- TODO: el teclado: un botón por letra; las ya usadas, deshabilitadas y marcadas como acierto o fallo -->
        <!-- TODO: resolver: escribir la palabra entera (mientras se juega) -->
        <!-- TODO: guardar la partida en campos ocultos (la palabra no: solo la categoría y el índice) -->
        <!-- TODO: el marcador y el botón para jugar otra -->
</body>
</html>
