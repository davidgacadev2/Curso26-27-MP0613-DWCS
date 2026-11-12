<?php  
/*
 * >>> PASO 3 de 7: recepción y validación de los datos de entrada <<<
 *
 * EJEMPLO-TAREA-02B, SOLUCIÓN-02: juego de tablero con letras ocultas bajo un rombo de asteriscos.
 * Se recogen el lado y la palabra, se validan y se muestra un mensaje por cada error. El formulario
 * conserva lo que se ha escrito (RF-01, RF-02).
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
    $lado       = empty($_GET['lado'])       ? 5: intval($_GET['lado']);
    $palabra    = (empty($_GET['palabra']) || preg_match("/^[A-Za-z]+$/", $_GET['palabra']) !== 1)    ? "": $_GET['palabra'];  // observar que no valen ni la Ñ, ñ ni acentos
    $primeraVez = empty($_GET['primeraVez']) ? true: false;
    // TODO (paso 6): recuperar el tablero y las letras ocultas de los campos hidden  [RF-10]

    echo '<input type="hidden" name="primeraVez" value="false"/>';
    if( !isset($_GET['botonJugada']) ) {
        // FIXME (pendiente): aquí se comprueba $lado < 1, pero el mensaje de más abajo dice "mayor o igual
        //        a 5": los lados 1 y 3 pasan la validación y se dibuja el tablero  [RF-02]
        if ( $lado < 1 || $lado % 2 != 1 || $lado > 30 || strlen($palabra) < 5 || $palabra == "") { // validamos datos
            include "entrada.php";
            if ( !$primeraVez ) {
                $mensaje = "";   
                if (  $lado < 5 || $lado > 30 ) {
                    $mensaje .= '<p id="salida" style="color: red">Por favor, recuerde que la longitud del lado debe ser mayor o igual a 5</p>';
                }
                if (  $lado % 2 != 1 ) {
                    $mensaje .= '<p id="salida" style="color: red">Por favor, recuerde que la longitud del lado debe ser un número impar</p>';
                }
                if (  strlen($palabra) < 5 || $palabra == "" ) {
                    $mensaje .= '<p id="salida" style="color: red">Por favor, recuerde que la palabra debe tener 5 caracteres como mínmo</p>';
                }
                echo "<div>$mensaje</div> ";
            }
        } else { // los datos son correctos, podemos dibujar el tablero
            // TODO (paso 4): generar la información del tablero y dibujarlo  [RF-03]
            include "entrada.php";
            echo "<p>Datos correctos: aquí se dibujará un tablero de lado $lado</p>";  // provisional
        } 
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