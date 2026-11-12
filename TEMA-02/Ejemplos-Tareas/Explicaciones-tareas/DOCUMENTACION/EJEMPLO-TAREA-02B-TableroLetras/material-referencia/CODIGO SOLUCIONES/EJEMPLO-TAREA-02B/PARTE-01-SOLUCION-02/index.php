<?php  

function generarInformacionTablero(&$tablero, &$letras, $lado, &$palabra) {  // ojo que pasamos palabra, el tablero y las letras por referencia        
        
    
        // Aprovechando el ejercicio de dibujar un rombo con asteriscos:
        $medio = floor($lado / 2) + 1;
        $siAsterisco = 0;  
        $numeroAsteriscos = 0;
        for ($fila = 1; $fila <= $lado; $fila++) {
            for ($columna = 1; $columna <= $lado; $columna++) {
                if ( ($columna >= $medio - $siAsterisco) && ($columna <= $medio + $siAsterisco) ) {
                    $tablero[$fila][$columna] = "*";
                    $numeroAsteriscos++;  // Aprovechamos para llevar la cuenta de asteriscos
                } else {
                    $tablero[$fila][$columna] = "o";
                }
            }
            $fila < $medio ? ++$siAsterisco : --$siAsterisco;
        }

        // Crear array con info posicion letras ocultas, relativa a cada uno de los asteriscos
        $listaLetras = str_split($palabra);
        $listaLetras = array_pad($listaLetras, $numeroAsteriscos , 0);   // 0 = no hay letra oculta
        shuffle($listaLetras);  // las barajamos
        $letras = array_fill(1, $lado, array_fill(1, $lado, 0));  // creamos un array bidimensional todo a 0
        for ($fila = 1; $fila <= $lado; $fila++) {
            for ($columna = 1; $columna <= $lado; $columna++) {
                if ( $tablero[$fila][$columna] == "*") {
                    $letras[$fila][$columna] = array_shift($listaLetras);
                }
            }
        }
              
        // Eliminar de la palabra las letras que no caben:
        $palabra = substr($palabra, 0, $numeroAsteriscos);
}

function dibujarTablero($tablero, $letras, $deshabilitar = false) {
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
                        if ( $deshabilitar )  // si nos invocan con $deshabilitar = true, deshabilitamos los boton jugadas
                           echo "<td><button type=\"submit\" name=\"botonJugada\" disabled>*</button></td>";
                        else
                            echo "<td><button type=\"submit\" name=\"botonJugada\" value=\"{$fila}x{$columna}\" >*</button></td>";
                    } else { // las letras siempre están deshabilitadas
                        echo '<td><button type="submit" name="botonJugada" disabled>' . $tablero[$fila][$columna] . '</button></td>';
                    }
                     
                    // Mantenemos el estado del tablero y las letras en campos ocultos
                    echo "<input type='hidden' name=\"tablero[$fila][$columna]\" value=\"" . $tablero[$fila][$columna]  . "\">";
                    echo "<input type='hidden' name=\"letras[$fila][$columna]\" value=\""  . $letras[$fila][$columna] . "\">";
                } else {
                    echo '<td>o</td>';
                    echo "<input type='hidden' name=\"tablero[$fila][$columna]\" value=\"o\">";
                    echo "<input type='hidden' name=\"letras[$fila][$columna]\" value=\"1\">";
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
    $tablero    = empty($_GET['tablero'])   ? "" : $_GET['tablero'];    
    $letras     = empty($_GET['letras'])   ? "" : $_GET['letras'];    

    echo '<input type="hidden" name="primeraVez" value="false"/>';
    if( !isset($_GET['botonJugada']) ) {
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
        $puntos         = empty($_GET['puntos']) ? 0: intval($_GET['puntos']);
        $fallosSeguidos = empty($_GET['fallosSeguidos']) ? 0: intval($_GET['fallosSeguidos']);
        $finJuego       = empty($_GET['finJuego']) ? 0: intval($_GET['finJuego']);

        if( isset($_GET['botonJugada']) ) {
            if ( $finJuego != 1) {
                // Obtenemos la posición donde se hizo clic
                $coordenadas = explode("x", $_GET['botonJugada']);
                $posicionX   = intval($coordenadas[0]);
                $posicionY   = intval($coordenadas[1]);

                // Comprobamos si en la posición hay una letra oculta
                if($letras[$posicionX][$posicionY] != "0" ) {
                    // Sí la hay: cambiamos el asterisco por la letra
                    $tablero[$posicionX][$posicionY] = $letras[$posicionX][$posicionY];
                    // Quitamos la letra de la palabra
                    $palabra = substr_replace($palabra, "", strpos($palabra, $letras[$posicionX][$posicionY]), 1);
                    // Comprobamos si quedan o no letras en la palabra
                    if ( strlen($palabra) === 0 ) {
                        $finJuego = 1;
                    }
                    $puntos += 5;
                    $fallosSeguidos = 0;
                } else {
                    // Como no hay letra oculta:
                    $puntos -= 2;
                    if ( $fallosSeguidos < 3 ) {
                        $fallosSeguidos++;
                    } else {
                        $fallosSeguidos++;
                        $finJuego = 1;
                    }
                }
            
                include "entrada.php";
                include "marcador.php";
                        
                if ($finJuego == 1) {
                    echo "<h1>FIN DEL JUEGO</h1> ";
                    dibujarTablero($tablero, $letras, true);
                    // no ponemos el campo oculto name="primeraVez"
                } else {
                    dibujarTablero($tablero, $letras); 
                }
            }
        }
    }
?>  
    </form>
</body>
</html>