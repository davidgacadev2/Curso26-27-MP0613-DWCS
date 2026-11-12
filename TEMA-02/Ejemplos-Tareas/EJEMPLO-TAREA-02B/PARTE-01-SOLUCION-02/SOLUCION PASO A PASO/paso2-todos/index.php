<?php  
/*
 * >>> PASO 2 de 7: descomposición del problema en TODOs <<<
 *
 * EJEMPLO-TAREA-02B, SOLUCIÓN-02: juego de tablero con letras ocultas bajo un rombo de asteriscos.
 * La página es la maqueta del paso 1 repartida ya en ficheros (entrada.php) y funciones, con un
 * comentario TODO en cada parte que hay que programar. Las funciones solo tienen la firma.
 * Ya se puede ejecutar: muestra el formulario de entrada.
 * (Con la extensión Todo Tree de VSC verás todos los TODO y FIXME en el panel lateral.)
 */

function generarInformacionTablero(&$tablero, &$letras, $lado, &$palabra) {  // ojo que pasamos palabra, el tablero y las letras por referencia        
        // TODO (paso 4): rellenar $tablero ($lado x $lado, índices desde 1) con "*" en el rombo y "o" fuera,
        //                aprovechando el ejercicio de dibujar un rombo con asteriscos, y contar los asteriscos  [RF-03]
        // TODO (paso 5): lista con las letras de la palabra (str_split), completada con 0 = no hay letra
        //                hasta el número de asteriscos (array_pad) y barajada (shuffle)  [RF-04]
        // TODO (paso 5): $letras = matriz $lado x $lado a 0 (array_fill); a cada casilla con asterisco
        //                se le da el siguiente elemento de la lista barajada (array_shift)
        // TODO (paso 5): eliminar de $palabra las letras que no caben
}

// TODO (paso 7): añadir el parámetro $deshabilitar = false, para dibujar el tablero sin botones activos
function dibujarTablero($tablero, $letras) {
        // TODO (paso 4): dibujar una tabla HTML recorriendo las filas y las columnas (como en el rombo)  [RF-03]
        // TODO (paso 4): casillas fuera del rombo: una celda con "o"
        // TODO (paso 4): casillas con "*": un botón submit name="botonJugada" value="FILAxCOLUMNA"  [RF-05]
        // TODO (paso 4): casillas ya descubiertas: un botón deshabilitado con la letra
        // TODO (paso 6): guardar el estado de cada casilla en campos hidden (tablero[f][c] y letras[f][c])  [RF-10]
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles.css" /> 

    <title>Juego</title>
</head>
<body >
    <h1>Juego de Tablero:</h1>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>"  method="get">
<?php 
    // TODO (paso 3): recoger de $_GET el lado (entero; 5 si no llega) y la palabra (solo letras sin
    //                acentos ni ñ; "" si no es válida)  [RF-01]
    // TODO (paso 3): $primeraVez = true si todavía no se ha enviado el formulario (campo oculto primeraVez)
    $lado       = 5;     // valores provisionales para que la página se pueda ejecutar
    $palabra    = "";
    $primeraVez = true;
    // TODO (paso 6): recuperar el tablero y las letras ocultas de los campos hidden  [RF-10]

    // TODO (paso 3): campo oculto primeraVez=false, para saber en la siguiente petición que ya no es la primera
    if( !isset($_GET['botonJugada']) ) {
        // TODO (paso 3): validar: $lado impar entre 5 y 29, $palabra con 5 letras o más  [RF-02]
        // TODO (paso 3): si hay errores y no es la primera vez, un mensaje por cada error
        // TODO (paso 4): si todo es correcto, generar la información del tablero y dibujarlo  [RF-03]
        include "entrada.php";
    } else {  // Comienza el juego 
        // TODO (paso 6): recoger puntos, fallos seguidos y fin de juego de los campos hidden (0 si no llegan)
        // TODO (paso 6): fila y columna del asterisco pulsado (value "FILAxCOLUMNA" de botonJugada)
        // TODO (paso 6): acierto: +5 puntos, cambiar el "*" por la letra, quitarla de $palabra y fallos a 0  [RF-06]
        // TODO (paso 6): fallo: -2 puntos y un fallo seguido más  [RF-07]
        // TODO (paso 6): mostrar la entrada, el marcador (marcador.php) y el tablero  [RF-08]
        // TODO (paso 7): fin del juego: no quedan letras o 4 fallos seguidos; tablero deshabilitado  [RF-09]
    }
?>  
    </form>
</body>
</html>