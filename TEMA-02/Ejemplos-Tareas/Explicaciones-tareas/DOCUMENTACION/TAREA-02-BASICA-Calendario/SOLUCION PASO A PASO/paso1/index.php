<?php
/*
 * >>> PASO 1 de 4: descomposición del problema (solo TODOs, sin código PHP) <<<
 *
 * TAREA-02 BÁSICA: Calendario mensual con festivos
 *  - toda la aplicación está en una única página que se llama a sí misma por GET
 *  - la lógica está en funciones.php y los datos de festivos en festivos.php
 */
// TODO: incluir funciones.php (require_once) y cargar el array de festivos ($festivos = require 'festivos.php')

// TODO: entrada de datos: ¿llegan 'mes' y 'anio' por GET?
//          - sí: leerlos y validarlos (validarEntero), acumulando los errores en un array $errores
//          - no (primera visita o enlace "Hoy"): usar el mes y el año actuales (date('n'), date('Y'))

// TODO: preparar los valores con los que se volverá a rellenar el formulario ("formulario pegajoso")
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h1>Calendario mensual</h1>

    <!-- TODO: formulario GET con un <select> de meses (NOMBRES_MESES), un campo numérico para el año,
               botón "Mostrar" y enlace "Hoy". Usar htmlspecialchars() al escribir datos del usuario -->

<?php
// TODO: si hay errores, mostrarlos todos en una lista y NO dibujar el calendario
// TODO: si no hay errores:
//          - enlaces « mes anterior / mes siguiente » (cambio de año y límites 1900-2100)
//          - dibujar la tabla del calendario con generarCalendario()
//          - resumen del mes: nº de días, nº de laborables y lista de festivos
?>
</body>

</html>
