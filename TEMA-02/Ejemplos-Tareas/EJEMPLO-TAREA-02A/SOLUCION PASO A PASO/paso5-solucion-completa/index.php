<?php
/*
 * >>> PASO 5 de 5: eliminar un contacto y vaciar la agenda <<<
 *
 * EJEMPLO-TAREA-02A: agenda de contactos en una única página, con el estado en campos hidden.
 * Se añade eliminar el contacto si el teléfono llega vacío (RF-06) y el enlace Vaciar, que solo
 * aparece si hay contactos (RF-07, RF-08).
 *
 * ÚLTIMO PASO: SOLUCIÓN COMPLETA. El código es el de la solución de referencia; solo se han
 * añadido comentarios: esta cabecera y los FIXME (pendiente) que señalan defectos conocidos.
 */
    // activar las 2 siguientes líneas solo para ir ejecutando el script paso a paso en modo
    //  depuración
    ob_end_flush();
    ob_implicit_flush(true);

//si hemos enviado agenda rellenamos el array
if (isset($_GET['agenda'])) {  
    $almacenAgenda = $_GET['agenda'];
} else  // si no, creamos  $almacenAgenda como un array vacío
    $almacenAgenda = array();
    
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea Unidad2</title>
	<link href='estilos.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
    // Lo primero que hacemos es recibir la cadena de variables y procesarla
	//  ¿es un nuevo envío?:
	if (isset($_GET['enviar'])) {
		//Limpiar de espacios en blanco al principio y al fin
		$nombre   = trim($_GET['nombre']);
		$telefono = trim($_GET['telefono']);
		if (strlen($nombre) == 0) {
			echo "<p class='aviso'>AVISO: el Nombre es obligatorio</p>";
		} else {
			$nombre = ucwords($nombre); //Ponemos en mayúsculas la primera letra de cada palabra.
			$almacenAgenda[$nombre] = $telefono;
			if (strlen($telefono) == 0) unset($almacenAgenda[$nombre]);
		}
	}
	
	//  ¿es una solicitud de borrar agenda?
	if (isset($_GET['limpiar']) && count($almacenAgenda) > 0) {
		// FIXME (pendiente): unset() destruye la variable y el count($almacenAgenda) de más abajo da un
		//        error fatal en PHP 8. Debería ser $almacenAgenda = array();  No se nota porque el enlace
		//        Vaciar no envía la agenda (es eso lo que la vacía) y esta línea no llega a ejecutarse.
		unset($almacenAgenda);
	}

?>

<h3>Agenda</h3>
<!-- Mostramos los contactos de la agenda -->
<?php
if (count($almacenAgenda) != 0) {
    echo "<fieldset>";
    echo "<legend>Datos Agenda</legend>";

    foreach ($almacenAgenda as $nombre => $telefono) {
        // FIXME (pendiente): escapar $nombre y $telefono con htmlspecialchars(): un nombre con comillas
        //        o con < rompe el HTML  [RNF-10]
        echo "<input type='text' value='$nombre'   size='18' disabled class='visualizarAgenda'>";
        echo "<input type='text' value='$telefono' size='12' disabled class='visualizarAgenda'><br>";

    }
    // FIXME (pendiente): este </ul> sobra: no se ha abierto ninguna lista
    echo "</ul>";
    echo "</fieldset>";
}
?>

<!-- Creamos el formulario de introducción de un nuevo contacto -->
<fieldset>
    <legend>Nuevo Contacto</legend>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
        
        <label for="campoNombre">Nombre:</label>
        <input type="text" id="campoNombre" name="nombre" maxlength="18"><br/>
 
		<label for="campoTelefono">Teléfono:</label>
        <input type="text" id="campoTelefono" name="telefono"/><br>
		<br>
        <input type="submit" class="botonEnviar" value="Añadir Contacto" name='enviar'/>
        <input type="reset"  class="botonReset"  value="Limpiar Campos" />
		
		<!-- Codigo para guardar en este formulario los datos  -->
		<!--  recibidos hasta ahora                            -->
        <?php
        foreach ($almacenAgenda as $nombre => $telefono) {
            // FIXME (pendiente): escapar también aquí con htmlspecialchars()  [RNF-10]
            echo "<input type='hidden' name=\"agenda[$nombre]\" value='$telefono'>";
        }
        ?>
        
		
    </form>
</fieldset>
<?php
// Si existen datos en la agenda, mostramos el botón de vaciar la agenda
if (count($almacenAgenda) > 0) {
    // FIXME (pendiente): un <a> dentro de un <button> no es HTML válido; basta el enlace con estilo de botón
    echo <<<TEXTO
        <fieldset><legend>Vaciar Agenda</legend>
			
			<button class="botonVaciar" ><a href="{$_SERVER['PHP_SELF']}?limpiar=1">Vaciar</a></button>
        </fieldset>
TEXTO;
}
?>

</body>
</html>