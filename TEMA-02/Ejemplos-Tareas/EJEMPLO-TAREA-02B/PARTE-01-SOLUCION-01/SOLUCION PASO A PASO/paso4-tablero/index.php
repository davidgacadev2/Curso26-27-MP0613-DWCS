<?php  
/*
 * >>> PASO 4 de 7: generar y dibujar el tablero con el rombo de asteriscos <<<
 *
 * EJEMPLO-TAREA-02B, SOLUCIÓN-01: juego de tablero con letras ocultas bajo un rombo de asteriscos.
 * generarInformacionTablero() rellena el tablero aprovechando el ejercicio del rombo y
 * dibujarTablero() lo dibuja como una tabla en la que cada asterisco es un botón (RF-03, RF-05).
 * Todavía no hay letras escondidas ni se guarda el estado: al pulsar un asterisco se pierde todo.
 */

function generarInformacionTablero(&$tablero, &$letras, $lado, &$palabra) {  // ojo que pasamos palabra, el tablero y las letras por referencia        
        // Aprovechando el ejercicio de dibujar un rombo con asteriscos:
        $medio = floor($lado / 2) + 1;
        $siAsterisco = 0;  
        $numeroAsteriscos = 0;
        for ($fila = 1; $fila <= $lado; $fila++) {
            for ($columna = 1; $columna <= $lado; $columna++) {
                if ( ($columna >= $medio - $siAsterisco) && ($columna <= $medio + $siAsterisco) ) {
                    $tablero[$fila][$columna] = "*";
                    $letras[$fila][$columna]  = "0";  // 0 = no hay letra oculta
                    $numeroAsteriscos++;  // Aprovechamos para llevar la cuenta de asteriscos
                } else {
                    $tablero[$fila][$columna] = "o";
                    $letras[$fila][$columna]  = "0";  // este elemento no lo usamos, pero lo dejamos para que el array sea homogéneo
                }
            }
            $fila < $medio ? ++$siAsterisco : --$siAsterisco;
        }

        // TODO (paso 5): recorrer las casillas con asterisco, una y otra vez, decidiendo a cara o cruz
        //                (random_int) si se esconde ahí la siguiente letra, hasta colocarlas todas o
        //                quedarse sin asteriscos libres  [RF-04]
        // TODO (paso 5): eliminar de $palabra las letras que no caben
        // FIXME (paso 5): todavía no hay ninguna letra escondida
}

// TODO (paso 7): añadir el parámetro $deshabilitar = false, para dibujar el tablero sin botones activos
function dibujarTablero($tablero, $letras) {
        echo "<br><br>";
        echo "<table class='matrix'>";
        echo "<thead></thead>";
        echo "<tbody>";

        $lado = count($tablero);
        
        // Dibujar trablero en una sola tirada, usando el programa de dibujar rombo visto anteriormente
        //  y aprovechamos para guardar el estado del tablero y las letras en campos ocultos
        $medio = floor($lado / 2) + 1;
        $siAsterisco = 0;  
        for ($fila = 1; $fila <= $lado; $fila++) {
            echo "<tr>";
            for ($columna = 1; $columna <= $lado; $columna++) {
                if ( ($columna >= $medio - $siAsterisco) && ($columna <= $medio + $siAsterisco) ) {
                    if( $tablero[$fila][$columna] == "*") {
                        // TODO (paso 7): si $deshabilitar es true, dibujar el botón deshabilitado (disabled)
                        echo "<td><button type=\"submit\" name=\"botonJugada\" value=\"{$fila}x{$columna}\" >*</button></td>";
                    } else { // las letras siempre están deshabilitadas
                        echo '<td><button type="submit" name="botonJugada" disabled>' . $tablero[$fila][$columna] . '</button></td>';
                    }
                    
                    // TODO (paso 6): guardar el estado de la casilla en campos hidden (tablero[f][c] y letras[f][c])  [RF-10]
                } else {
                    echo '<td>o</td>';
                    // TODO (paso 6): guardar también en campos hidden las casillas "o"
                }
            }
            $fila < $medio ? ++$siAsterisco : --$siAsterisco;
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
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
            $tablero = [];
            $letras  = [];                
            generarInformacionTablero($tablero, $letras, $lado, $palabra);
            include "entrada.php";  // debemos ponerlo aquí porque el método anterior modifica $palabra si es demasiado larga para ocultar
            dibujarTablero($tablero, $letras);
        } 
    } else {  // Comienza el juego 
        // TODO (paso 6): recoger puntos, fallos seguidos y fin de juego de los campos hidden (0 si no llegan)
        // TODO (paso 6): fila y columna del asterisco pulsado (value "FILAxCOLUMNA" de botonJugada)
        // TODO (paso 6): acierto: +5 puntos, cambiar el "*" por la letra, quitarla de $palabra y fallos a 0  [RF-06]
        // TODO (paso 6): fallo: -2 puntos y un fallo seguido más  [RF-07]
        // TODO (paso 6): mostrar la entrada, el marcador (marcador.php) y el tablero  [RF-08]
        // TODO (paso 7): fin del juego: no quedan letras o 4 fallos seguidos; tablero deshabilitado  [RF-09]
        // FIXME (paso 6): al pulsar un asterisco el tablero desaparece: su estado no viaja en la petición
        //        (HTTP no tiene estado)
        include "entrada.php";
    }
?>  
    </form>
</body>
</html>