<?php
/*
 * >>> PASO 4 de 5: el estado de la agenda viaja en campos hidden <<<
 *
 * EJEMPLO-TAREA-02A: agenda de contactos en una única página, con el estado en campos hidden.
 * La agenda se escribe en campos hidden del formulario y se recupera en la petición siguiente
 * (RF-09). Con eso, modificar un teléfono funciona sin escribir nada más: la clave del array
 * es el nombre (RF-05).
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
			// TODO (paso 5): si el teléfono está vacío, eliminar el contacto  [RF-06]
			// FIXME (paso 5): ahora, con el teléfono vacío, se guarda un contacto sin teléfono
		}
	}
	
	// TODO (paso 5): si llega "limpiar" por GET, vaciar la agenda  [RF-08, RNF-05]

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
// TODO (paso 5): si la agenda tiene contactos, un fieldset "Vaciar Agenda" con un enlace a esta
//                misma página con ?limpiar=1  [RF-07]
?>

</body>
</html>