<!--
  >>> PASO 1 de 5: la maqueta (solo HTML y CSS) <<<

  EJEMPLO-TAREA-02A: agenda de contactos en una única página, con el estado en campos hidden.
  Aunque el fichero se llame .php, todavía no tiene PHP. Se incluyen ejemplos de todos los estados
  (el aviso, dos contactos en la agenda, el botón Vaciar) para ver cómo quedan. En los pasos
  siguientes, cada ejemplo se sustituye por el PHP que lo genera.
-->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea Unidad2</title>
	<link href='estilos.css' rel='stylesheet' type='text/css'>
</head>

<body>

<!-- Ejemplo de aviso: solo aparece si se envía el formulario sin nombre -->
<p class='aviso'>AVISO: el Nombre es obligatorio</p>

<h3>Agenda</h3>
<!-- Ejemplo de agenda con dos contactos: solo aparece si la agenda no está vacía -->
<fieldset>
    <legend>Datos Agenda</legend>
    <input type='text' value='Ana Pérez'   size='18' disabled class='visualizarAgenda'>
    <input type='text' value='986111222' size='12' disabled class='visualizarAgenda'><br>
    <input type='text' value='Brais Otero' size='18' disabled class='visualizarAgenda'>
    <input type='text' value='600333444' size='12' disabled class='visualizarAgenda'><br>
</fieldset>

<!-- Formulario de introducción de un nuevo contacto: aparece siempre -->
<fieldset>
    <legend>Nuevo Contacto</legend>
    <form action="index.php" method="get">

        <label for="campoNombre">Nombre:</label>
        <input type="text" id="campoNombre" name="nombre" maxlength="18"><br/>

		<label for="campoTelefono">Teléfono:</label>
        <input type="text" id="campoTelefono" name="telefono"/><br>
		<br>
        <input type="submit" class="botonEnviar" value="Añadir Contacto" name='enviar'/>
        <input type="reset"  class="botonReset"  value="Limpiar Campos" />

    </form>
</fieldset>

<!-- Ejemplo del botón de vaciar la agenda: solo aparece si la agenda tiene contactos -->
<fieldset><legend>Vaciar Agenda</legend>
    <button class="botonVaciar" ><a href="index.php?limpiar=1">Vaciar</a></button>
</fieldset>

</body>
</html>
