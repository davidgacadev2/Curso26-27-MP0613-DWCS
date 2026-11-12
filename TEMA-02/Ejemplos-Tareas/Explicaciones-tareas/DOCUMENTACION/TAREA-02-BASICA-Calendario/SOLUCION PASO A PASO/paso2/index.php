<?php
/*
 * >>> PASO 2 de 4: formulario, lectura y validación de datos, festivos <<<
 *
 * TAREA-02 BÁSICA: Calendario mensual con festivos
 *  - toda la aplicación está en una única página que se llama a sí misma por GET
 *  - la lógica está en funciones.php y los datos de festivos en festivos.php
 */
require_once 'funciones.php';
$festivos = require 'festivos.php';

$errores = [];

// ¿Nos llegan datos? Si no (primera visita o enlace "Hoy") mostramos el mes actual
if (isset($_GET['mes']) || isset($_GET['anio'])) {
    $mes  = validarEntero($_GET['mes'] ?? null, 1, 12, 'Mes', $errores);
    $anio = validarEntero($_GET['anio'] ?? null, ANIO_MIN, ANIO_MAX, 'Año', $errores);
} else {
    $mes  = (int) date('n');
    $anio = (int) date('Y');
}

// Valores para volver a rellenar el formulario ("formulario pegajoso")
$mesFormulario  = $mes  ?? (int) ($_GET['mes'] ?? date('n'));
$anioFormulario = $anio ?? ($_GET['anio'] ?? '');
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

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="get">
        <fieldset>
            <legend>Elige mes y año</legend>
            <label for="mes">Mes:</label>
            <select name="mes" id="mes">
                <?php
                foreach (NOMBRES_MESES as $numero => $nombre) {
                    $seleccionado = ($numero == $mesFormulario) ? ' selected' : '';
                    echo "<option value='{$numero}'{$seleccionado}>{$nombre}</option>";
                }
                ?>
            </select>

            <label for="anio">Año:</label>
            <input type="number" id="anio" name="anio" min="<?= ANIO_MIN ?>" max="<?= ANIO_MAX ?>"
                   value="<?= htmlspecialchars((string) $anioFormulario) ?>">

            <button type="submit">Mostrar</button>
            <a class="boton" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Hoy</a>
        </fieldset>
    </form>

<?php
if (count($errores) > 0) {
    // Hay errores: los mostramos y no dibujamos el calendario
    echo "<div class='errores'><p>Revisa los datos:</p><ul>";
    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul></div>";
} else {
    // TODO (paso 4): enlaces « mes anterior / mes siguiente » (cambio de año y límites 1900-2100)

    // TODO (paso 3): dibujar la tabla del calendario con generarCalendario()

    // TODO (paso 4): resumen del mes: nº de días, nº de laborables y lista de festivos
}
?>
</body>

</html>
