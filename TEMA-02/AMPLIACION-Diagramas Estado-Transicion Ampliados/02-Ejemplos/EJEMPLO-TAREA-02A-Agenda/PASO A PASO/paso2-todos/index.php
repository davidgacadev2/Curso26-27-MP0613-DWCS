<?php
/*
 * >>> PASO 2 de 5: descomposición del problema en TODOs <<<
 *
 * EJEMPLO-TAREA-02A: agenda de contactos en una única página, con el estado en campos hidden.
 * La página es la maqueta del paso 1 con un comentario TODO en cada parte que hay que programar,
 * en el orden en que se hará. Ya se puede ejecutar: muestra el formulario y una agenda vacía.
 * (Con la extensión Todo Tree de VSC verás todos los TODO y FIXME en el panel lateral.)
 */
    // activar las 2 siguientes líneas solo para ir ejecutando el script paso a paso en modo
    //  depuración
    ob_end_flush();
    ob_implicit_flush(true);

// TODO (paso 4): si llega la agenda en la petición ($_GET['agenda']), recuperarla; si no, agenda vacía  [RF-09]
$almacenAgenda = array();   // provisional. Array asociativo nombre => teléfono  [RNF-04]
    
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
	// TODO (paso 3): si llega el botón "enviar", limpiar nombre y teléfono con trim()  [RNF-09]
	// TODO (paso 3): nombre vacío: aviso "el Nombre es obligatorio" y no tocar la agenda  [RF-03]
	// TODO (paso 3): nombre con teléfono: $almacenAgenda[nombre] = teléfono (añade o modifica)  [RF-04, RF-05]
	// TODO (paso 5): nombre sin teléfono: eliminar el contacto  [RF-06]
	
	// TODO (paso 5): si llega "limpiar" por GET, vaciar la agenda  [RF-08, RNF-05]

?>

<h3>Agenda</h3>
<!-- Mostramos los contactos de la agenda -->
<?php
// TODO (paso 3): si la agenda tiene contactos, un fieldset "Datos Agenda" con el nombre y el teléfono
//                de cada uno (foreach)  [RF-01]
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
        // TODO (paso 4): por cada contacto, un campo hidden name="agenda[nombre]" value="teléfono"  [RF-09, RNF-02]
        ?>
        
		
    </form>
</fieldset>
<?php
// TODO (paso 5): si la agenda tiene contactos, un fieldset "Vaciar Agenda" con un enlace a esta
//                misma página con ?limpiar=1  [RF-07]
?>

</body>
</html>