<?php
/*
 * >>> PASO 1 de 4: descomposición del problema (solo TODOs, sin código PHP) <<<
 *
 * TAREA-02 MEDIA: Cuaderno de notas del profesor
 *  - una única página que se envía a sí misma por POST
 *  - el ESTADO (el cuaderno) se guarda en campos hidden dentro del propio formulario
 *    (prohibido usar $_SESSION hasta temas posteriores)
 */
// TODO: incluir funciones.php (require_once)

// TODO: inicializar los arrays $errores y $mensajes

// TODO: comprobar estado: recuperar el cuaderno de los campos ocultos ($_POST['alumnos']) ¡validándolo!

// TODO: valores por defecto del formulario de alumno (nombre y 3 notas vacíos)

// TODO: leer el criterio de ordenación (lista blanca: 'nombre' o 'media')

// TODO: ¿qué acción ha pedido el usuario?
//          - botón "Eliminar" de una fila (su value es el nombre del alumno)
//          - botón "Editar": cargar los datos del alumno en el formulario
//          - 'guardar': entrada de datos, validar datos, añadir o actualizar el alumno, mensaje
//          - 'cargar': carga masiva con procesarCargaMasiva()
//          - 'vaciar': solo si se marca la casilla de confirmación
//          - 'ordenar'

// TODO: ordenar el cuaderno con ordenarCuaderno()
// TODO: calcular las estadísticas con estadisticas()
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuaderno de notas</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h1>Cuaderno de notas</h1>

    <!-- TODO: sacar mensajes de error (todos) y de información, si procede -->

    <!-- TODO: UN ÚNICO formulario POST (así, pulse el botón que pulse, siempre se envían los campos hidden) con:
               - un botón "Guardar" invisible al principio (es el que "pulsa" el navegador al pulsar Intro)
               - tabla de alumnos: nombre, notas, media, calificación y botones Editar / Eliminar
                 (o un mensaje si el cuaderno está vacío) y desplegable "Ordenar por"
               - estadísticas del grupo (media, mejor, peor, aprobados y gráfico de distribución)
               - formulario de alta / modificación (nombre + 3 notas), pegajoso
               - carga masiva (textarea con líneas Nombre;nota1;nota2;nota3 y botón "Cargar")
               - vaciar cuaderno (casilla de confirmación + botón "Vaciar")
               - guardar el estado en campos hidden: alumnos[Nombre][0], alumnos[Nombre][1], alumnos[Nombre][2] -->
</body>

</html>
