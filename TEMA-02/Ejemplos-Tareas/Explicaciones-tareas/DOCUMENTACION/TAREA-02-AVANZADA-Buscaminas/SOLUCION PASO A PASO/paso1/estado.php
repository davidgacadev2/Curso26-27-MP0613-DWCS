<?php
/*
 * estado.php  (SOLUCIÓN 01)
 *  Guardar y recuperar la partida en campos ocultos del formulario.
 *
 *  Para no generar cientos de campos hidden (un tablero 16 x 30 tiene 480 casillas), cada FILA
 *  del tablero se guarda como una cadena de texto con un carácter por casilla:
 *      tablero: '*' = mina, '0'..'8' = nº de minas vecinas       p.ej. "01*1000"
 *      estado : 'O' = oculta, 'D' = descubierta, 'B' = bandera     p.ej. "DDOOBOO"
 *  y se convierte de nuevo en array con str_split().
 *
 *  INCONVENIENTE: el jugador puede ver dónde están las minas mirando el código fuente de la página
 *  (o cambiar el estado de la partida). La SOLUCIÓN 02 resuelve este problema.
 */
// TODO: incluir logica.php (require_once)

// Función auxiliar TODO: partidaACamposOcultos(array $partida): string
//      guardar el estado en variables hidden: datos sueltos + una cadena por fila (implode)

// Función auxiliar TODO: recuperarPartida(mixed $datos): ?array
//      comprobar estado: validar TODO lo que llega (números, límites, longitud y caracteres de cada fila,
//      número de minas...) y reconstruir los arrays con str_split(); null si algo no cuadra

// Función auxiliar TODO: validarConfiguracion(array $datos, array &$errores): ?array
//      nivel predefinido o personalizado (filas, columnas y minas dentro de los límites) -> [filas, columnas, minas]
