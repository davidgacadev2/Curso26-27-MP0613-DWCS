<?php
/*
 * ===== CORRECCIONES (revisión de las soluciones del tema 2) =====
 * - empty() trataba el 0 como campo vacío: con velocidad inicial 0 pedía rellenar los campos.
 * - Sin validación: "abc" -> TypeError (Fatal) y el valor se escribía sin escapar (XSS). Ahora is_numeric().
 * - Con un campo vacío se escribía value="VACIO" en el formulario.
 * - XSS: $_SERVER['PHP_SELF'] cambiado por $_SERVER['SCRIPT_NAME'].
 * ==========
 * ==========
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DWES-PHP-B01-02</title>
</head>
<body style="font-family: monospace">

    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>"  method="get">
<?php
    $botonEnviar = !isset($_GET['botonEnviar']) || $_GET['botonEnviar'] != "Enviar" ? false : true;
    $botonReset  = !isset($_GET['botonReset'])  || $_GET['botonReset'] != "Reset" ? false : true;

    // faltaría validar los enteros
    // CORREGIDO: empty() trataba 0 como vacío (u = 0, el caso más habitual, daba error) y no se validaba
    // nada: "abc" provocaba TypeError y un valor con HTML se escribía tal cual (XSS). is_numeric() lo resuelve.
    $valorU = (isset($_GET['u']) && is_numeric($_GET['u'])) ? $_GET['u'] : "VACIO";
    $valorA = (isset($_GET['a']) && is_numeric($_GET['a'])) ? $_GET['a'] : "VACIO";
    $valorT = (isset($_GET['t']) && is_numeric($_GET['t'])) ? $_GET['t'] : "VACIO";
    
    if( $botonReset ) {
        $valorU = "VACIO"; 
        $valorA = "VACIO";
        $valorT = "VACIO";
    }

    // CORREGIDO: antes se escribía value="VACIO" en los campos
    $mostrarU = $valorU === "VACIO" ? "" : $valorU;
    $mostrarA = $valorA === "VACIO" ? "" : $valorA;
    $mostrarT = $valorT === "VACIO" ? "" : $valorT;

    echo <<<MARCA
    <p>
        <label for="velocidadInicial">Introduzca velocidad inicial (m/s):</label>
        <input type="number" step="any" id="velocidadInicial" name="u" value="{$mostrarU}" />
    </p>
    <p>
        <label for="aceleracion">Introduzca aceleración del objeto (m/s^2):</label>
        <input type="number"  step="any" id="aceleracion" name="a" value="{$mostrarA}"  />
    </p>
    <p>
        <label for="tiempoTranscurrido">Introduzca tiempo transcurrido (s):</label>
        <input type="number"  step="any" id="tiempoTranscurrido" name="t" value="{$mostrarT}" />
    </p>
MARCA;

    if ( $valorU === "VACIO" || $valorA === "VACIO" || $valorT === "VACIO" )  {
        if ( $botonEnviar ) {
            echo '<p style="color: red">Rellene todos los campos con números, por favor</p>';
        } else {
            echo '<p>&nbsp;</p>';
        }
            echo <<<MARCA
            <p id="velocidad">&nbsp;</p>
            <p id="espacio">&nbsp;</p>
MARCA;
    } else {
        echo '<p>&nbsp;</p>';
        echo "<p id='velocidad'>La velocidad (v) es: " .  ((float) $valorU + $valorA * $valorT) . "</p>";
        echo "<p id='espacio'>El espacio recorrido (s) es: " . ( (float) $valorU * $valorT + 0.5 * $valorA * ($valorT**2)) . "</p>";
    }
?>
        <button id="botonEnviar" type="submit" name="botonEnviar" value="Enviar">Enviar</button>      
        <button id="botonReset" type="submit" name="botonReset" value="Reset">Reset</button> 
    </form>
</body>
</html>