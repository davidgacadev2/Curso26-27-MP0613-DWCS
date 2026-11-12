<?php
/*
 * >>> PASO 1 de 5: descomposición del problema (solo TODOs, sin código PHP) <<<
 *
 * TAREA-02 AVANZADA: Buscaminas  (SOLUCIÓN 01: estado en campos hidden, fila a fila)
 *
 *  index.php hace de "controlador":
 *      1) lee la petición ($_POST)
 *      2) recupera / crea la partida y aplica la jugada (logica.php, estado.php)
 *      3) genera la página (vista.php)
 */
// TODO: incluir estado.php y vista.php (require_once)

// TODO: inicializar $errores, $mensaje y $partida (null = no hay partida)

// TODO: leer el modo de juego (lista blanca: 'descubrir' o 'bandera')

// TODO: valores por defecto del formulario de configuración (nivel principiante)

// TODO: ¿NUEVA PARTIDA? (botón "Empezar")
//          - entrada de datos: nivel, filas, columnas, minas (formulario pegajoso)
//          - validar datos con validarConfiguracion()
//          - si son correctos: crear la partida con nuevaPartida()

// TODO: ¿JUGADA? (clic en una casilla: botón name="celda")
//          - comprobar estado: recuperar la partida de los campos hidden; si no es válida -> error
//          - si la partida sigue en juego: leer y validar la casilla pulsada con leerCoordenadas()
//          - modo 'bandera': poner/quitar bandera; modo 'descubrir': jugarCasilla()
//          - actualizar el formulario de configuración con los datos de la partida
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscaminas</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h1>💣 Buscaminas</h1>

    <!-- TODO: formulario de configuración con pintarConfiguracion() -->

    <!-- TODO: sacar mensajes de error (todos) y avisos, si procede -->

    <!-- TODO: si hay partida:
               - marcador y mensaje de fin de partida con pintarMarcador()
               - dibujar tablero con pintarTablero(), que incluye guardar el estado en variables hidden
         si no: mensaje de ayuda -->
</body>

</html>
