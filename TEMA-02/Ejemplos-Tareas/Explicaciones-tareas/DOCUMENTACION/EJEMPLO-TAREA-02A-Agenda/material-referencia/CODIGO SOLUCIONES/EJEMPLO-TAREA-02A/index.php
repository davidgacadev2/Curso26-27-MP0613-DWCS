<?php
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
        echo "<input type='text' value='$nombre'   size='18' disabled class='visualizarAgenda'>";
        echo "<input type='text' value='$telefono' size='12' disabled class='visualizarAgenda'><br>";

    }
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
            echo "<input type='hidden' name=\"agenda[$nombre]\" value='$telefono'>";
        }
        ?>
        
		
    </form>
</fieldset>
<?php
// Si existen datos en la agenda, mostramos el botón de vaciar la agenda
if (count($almacenAgenda) > 0) {
    echo <<<TEXTO
        <fieldset><legend>Vaciar Agenda</legend>
			
			<button class="botonVaciar" ><a href="{$_SERVER['PHP_SELF']}?limpiar=1">Vaciar</a></button>
        </fieldset>
TEXTO;
}
?>

</body>
</html>