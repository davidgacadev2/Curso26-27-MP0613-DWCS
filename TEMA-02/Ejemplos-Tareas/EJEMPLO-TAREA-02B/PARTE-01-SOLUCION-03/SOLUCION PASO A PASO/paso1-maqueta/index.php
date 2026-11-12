<!--
  >>> PASO 1 de 7: la maqueta (solo HTML y CSS) <<<

  EJEMPLO-TAREA-02B: juego de tablero con letras ocultas bajo un rombo de asteriscos.
  Aunque el fichero se llame .php, todavía no tiene PHP. Se incluyen ejemplos de todos los estados
  (mensajes de error, marcador, tablero de lado 5 con una letra descubierta, fin del juego) para
  ver cómo quedan. En los pasos siguientes, cada ejemplo se sustituye por el PHP que lo genera.
-->
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
    <form action="index.php"  method="get">

        <!-- Entrada de datos: aparece siempre -->
        <div id="entrada">
            <p>
                <label for="lado">Introduzca la longitud del lado del tablero (impar de 5-29):</label>
                <input type="number" id="lado" name="lado" value="5"/>
            </p>
            <p>
                <label for="palabra">Introduzca una palabra (no usar acentos ni ñ):</label>
                <input type="text" id="palabra" name="palabra" value="pulpo"/>
            </p>
            <button type="submit" >Enviar</button>
            <button type="reset" ><a href="index.php" style="text-decoration: none;">Borrar</a></button>
        </div>

        <!-- Ejemplo de mensajes de error: solo si los datos enviados no son válidos (y entonces no hay tablero) -->
        <div>
            <p style="color: red">Por favor, recuerde que la longitud del lado debe ser mayor o igual a 5</p>
            <p style="color: red">Por favor, recuerde que la longitud del lado debe ser un número impar</p>
            <p style="color: red">Por favor, recuerde que la palabra debe tener 5 caracteres como mínimo</p>
        </div>

        <!-- Ejemplo de marcador: solo aparece a partir del primer clic en un asterisco -->
        <p>Puntos hasta ahora: 3 </p>
        <p>Fallos seguidos: 1 </p>

        <!-- Ejemplo de fin del juego: solo al acertar todas las letras o al 4.º fallo seguido -->
        <h1>FIN DEL JUEGO</h1>

        <!-- Ejemplo de tablero de lado 5: "o" fuera del rombo, un botón por asterisco y una letra descubierta -->
        <br><br>
        <table class='matrix'>
            <thead></thead>
            <tbody>
                <tr>
                    <td>o</td><td>o</td>
                    <td><button type="submit" name="botonJugada" value="1x3" >*</button></td>
                    <td>o</td><td>o</td>
                </tr>
                <tr>
                    <td>o</td>
                    <td><button type="submit" name="botonJugada" value="2x2" >*</button></td>
                    <td><button type="submit" name="botonJugada" disabled>p</button></td>
                    <td><button type="submit" name="botonJugada" value="2x4" >*</button></td>
                    <td>o</td>
                </tr>
                <tr>
                    <td><button type="submit" name="botonJugada" value="3x1" >*</button></td>
                    <td><button type="submit" name="botonJugada" value="3x2" >*</button></td>
                    <td><button type="submit" name="botonJugada" value="3x3" >*</button></td>
                    <td><button type="submit" name="botonJugada" value="3x4" >*</button></td>
                    <td><button type="submit" name="botonJugada" value="3x5" >*</button></td>
                </tr>
                <tr>
                    <td>o</td>
                    <td><button type="submit" name="botonJugada" value="4x2" >*</button></td>
                    <td><button type="submit" name="botonJugada" value="4x3" >*</button></td>
                    <td><button type="submit" name="botonJugada" value="4x4" >*</button></td>
                    <td>o</td>
                </tr>
                <tr>
                    <td>o</td><td>o</td>
                    <td><button type="submit" name="botonJugada" value="5x3" >*</button></td>
                    <td>o</td><td>o</td>
                </tr>
            </tbody>
        </table>
    </form>
</body>
</html>
