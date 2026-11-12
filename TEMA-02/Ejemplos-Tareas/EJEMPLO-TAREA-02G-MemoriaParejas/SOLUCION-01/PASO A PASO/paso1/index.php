<?php
/*
 * >>> PASO 1 de 6: descomposición del problema (solo TODOs y el esqueleto HTML, sin código PHP) <<<
 *
 * TAREA-02 MEDIA: Memoria de parejas
 *  - una única página que se envía a sí misma por POST; sin $_SESSION: toda la partida (el tablero, las
 *    parejas encontradas, la carta pendiente y el marcador) viaja en campos ocultos;
 *  - arriba, la lógica (qué botón se ha pulsado); abajo, el HTML; las funciones están en funciones.php.
 */
// TODO: cargar las funciones

// TODO: valores iniciales: sin partida, sin errores; el formulario propone 4 x 4 con 8 letras

// TODO: botón "Empezar": validar el formulario de configuración
    // TODO: si no hay errores, empezar la partida con el tablero barajado

// TODO: botón de una carta: recuperar la partida de los campos ocultos
    // TODO: no fiarse del navegador: comprobar que el estado recibido es una partida posible
    // TODO: girar la carta pulsada

// TODO: botón "Nueva partida": volver al formulario con la configuración anterior

// TODO: ¿ha terminado la partida? mensaje final
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

<!-- TODO: mensajes de error -->

<!-- TODO: sin partida: formulario de configuración (pegajoso), con la semilla del barajado en un campo oculto -->

<!-- TODO: con partida: el tablero; cada carta boca abajo es un botón -->
    <!-- TODO: mensaje de la jugada y marcador (a partir de la primera carta girada) -->
        <!-- TODO: guardar la partida en campos ocultos: configuración y tablero (una carta por campo) -->
        <!-- TODO: guardar en campos ocultos las parejas encontradas, la carta pendiente y el marcador -->
        <!-- TODO: botón para empezar otra partida con la misma configuración -->
</body>
</html>
