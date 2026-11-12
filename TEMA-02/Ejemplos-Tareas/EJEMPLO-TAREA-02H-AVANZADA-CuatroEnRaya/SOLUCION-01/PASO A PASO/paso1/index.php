<?php
/*
 * >>> PASO 1 de 6: descomposición del problema (solo TODOs y el esqueleto HTML, sin código PHP) <<<
 *
 * TAREA-02 AVANZADA: Cuatro en raya
 *  - una única página que se envía a sí misma por POST; sin $_SESSION: la partida viaja en campos ocultos;
 *  - del tablero solo se guardan las JUGADAS ("3324": la columna de cada ficha, en orden). En cada petición se
 *    repiten con reconstruir(), que devuelve el tablero, la última ficha y el ganador, o null si las jugadas
 *    son imposibles: así validar el estado es casi gratis, y deshacer es quitar el último carácter;
 *  - contra la máquina, la máquina contesta en la misma petición en la que juega la persona.
 */
// TODO: cargar las funciones

// TODO: valores iniciales: sin partida, sin errores; el formulario propone jugar contra la máquina en nivel normal

// TODO: botón "Empezar": validar el formulario y crear la partida
        // TODO: si empieza la máquina, juega ya

// TODO: botón de una columna (o "Deshacer"): recuperar la partida de los campos ocultos
    // TODO: no fiarse del navegador: comprobar toda la partida (nombres, máquina, nivel, semilla y jugadas)

// TODO: botón de una columna: soltar la ficha del jugador al que le toca
    // TODO: una partida terminada no admite más fichas
    // TODO: nadie puede jugar por la máquina
        // TODO: y contesta la máquina

// TODO: botón "Deshacer": quitar la última jugada (contra la máquina, también la suya)

// TODO: botón "Nueva partida": volver al formulario con los jugadores de la partida anterior

// TODO: el tablero de la partida, repitiendo las jugadas

// TODO: ¿cómo va la partida? mensaje final
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

<!-- TODO: mensajes de error -->

<!-- TODO: sin partida: formulario de configuración (pegajoso), con la semilla en un campo oculto -->

<!-- TODO: con partida: los jugadores y a quién le toca -->
    <!-- TODO: el mensaje de la jugada de la máquina (o de deshacer) -->
    <!-- TODO: mensaje de fin de partida -->
        <!-- TODO: el tablero: un botón encima de cada columna y una casilla por posición (con la ganadora y la última resaltadas) -->
        <!-- TODO: guardar la partida en campos ocultos: los jugadores, la máquina, el nivel, la semilla y las jugadas -->
            <!-- TODO: botón para deshacer la última jugada -->
            <!-- TODO: botón para empezar otra partida con los mismos jugadores -->
    <!-- TODO: el historial de jugadas -->
</body>
</html>
