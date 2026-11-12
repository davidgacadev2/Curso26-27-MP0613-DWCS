<?php
/*
 * >>> PASO 2 de 4: alta de alumnos con validación y estado en campos hidden <<<
 *
 * TAREA-02 MEDIA: Cuaderno de notas del profesor
 *  - una única página que se envía a sí misma por POST
 *  - el ESTADO (el cuaderno) se guarda en campos hidden dentro del propio formulario
 *    (prohibido usar $_SESSION hasta temas posteriores)
 */
require_once 'funciones.php';

$errores  = [];
$mensajes = [];

// 1) Recuperamos el estado que viaja en los campos ocultos (¡validándolo!)
$cuaderno = recuperarCuaderno($_POST['alumnos'] ?? []);

// Valores del formulario de alumno (por defecto vacíos)
$formNombre = '';
$formNotas  = ['', '', ''];

// TODO (paso 3): leer el criterio de ordenación (lista blanca: 'nombre' o 'media')

// 2) ¿Qué acción ha pedido el usuario?
if (isset($_POST['eliminar'])) {
    // TODO (paso 3): botón "Eliminar" de una fila (su value es el nombre del alumno)
} elseif (isset($_POST['editar'])) {
    // TODO (paso 3): botón "Editar": cargar los datos del alumno en el formulario
} else {
    switch ($_POST['accion'] ?? '') {
        case 'guardar':
            $formNombre = $_POST['nombre'] ?? '';
            $formNotas  = $_POST['notas'] ?? ['', '', ''];
            $alumno = validarAlumno($formNombre, is_array($formNotas) ? $formNotas : [], $errores);
            if ($alumno !== null) {
                [$nombre, $notas] = $alumno;
                $mensajes[] = array_key_exists($nombre, $cuaderno)
                    ? "Se han actualizado las notas de «{$nombre}»."
                    : "Se ha añadido a «{$nombre}».";
                $cuaderno[$nombre] = $notas;
                // Todo correcto: vaciamos el formulario
                $formNombre = '';
                $formNotas  = ['', '', ''];
            }
            break;

        // TODO (paso 4): case 'cargar': carga masiva con procesarCargaMasiva()

        // TODO (paso 3): case 'vaciar' (solo si se marca la casilla de confirmación) y case 'ordenar'
    }
}

// TODO (paso 3): ordenar el cuaderno con ordenarCuaderno()
// TODO (paso 4): calcular las estadísticas con estadisticas()
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

    <!-- Mensajes de error y de información -->
    <?php if (count($errores) > 0): ?>
        <div class="errores">
            <p>Se han encontrado <?= count($errores) ?> error(es):</p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php foreach ($mensajes as $mensaje): ?>
        <p class="mensaje"><?= e($mensaje) ?></p>
    <?php endforeach; ?>

    <!-- UN ÚNICO formulario: así, pulse el botón que pulse, siempre se envían los campos hidden -->
    <form action="<?= e($_SERVER['PHP_SELF']) ?>" method="post">
        <!-- Al pulsar Intro en un campo de texto, el navegador "pulsa" el PRIMER botón submit del formulario.
             Ponemos aquí uno invisible de "Guardar" para que no se pulse "Ordenar" o "Editar" por accidente. -->
        <button type="submit" name="accion" value="guardar" class="oculto" tabindex="-1" aria-hidden="true">Guardar</button>

        <!-- ===== Tabla de alumnos ===== -->
        <fieldset>
            <legend>Alumnado (<?= count($cuaderno) ?>)</legend>
            <?php if (count($cuaderno) == 0): ?>
                <p>El cuaderno está vacío. Añade alumnos con el formulario o con la carga masiva.</p>
            <?php else: ?>
                <!-- TODO (paso 3): desplegable "Ordenar por" (nombre / media) y botón "Ordenar" -->
                <table class="notas">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <?php foreach (EVALUACIONES as $i => $evaluacion): ?>
                                <th><?= $evaluacion ?><br><small><?= PESOS[$i] * 100 ?>%</small></th>
                            <?php endforeach; ?>
                            <!-- TODO (paso 3): columnas Media, Calificación y acciones -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cuaderno as $nombre => $notas): ?>
                            <tr>
                                <td class="nombre"><?= e($nombre) ?></td>
                                <?php foreach ($notas as $nota): ?>
                                    <td class="<?= $nota < NOTA_APROBADO ? 'nota-suspensa' : '' ?>"><?= $nota ?></td>
                                <?php endforeach; ?>
                                <!-- TODO (paso 3): media ponderada, calificación y botones Editar / Eliminar -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </fieldset>

        <!-- TODO (paso 4): estadísticas del grupo (media, mejor, peor, aprobados y gráfico de distribución) -->

        <!-- ===== Alta / modificación de un alumno ===== -->
        <fieldset>
            <legend>Añadir o modificar alumno</legend>
            <p class="ayuda">Si el nombre ya existe, se sustituyen sus notas. Puedes usar coma o punto decimal.</p>
            <p>
                <label for="nombre">Nombre y apellidos:</label>
                <input type="text" id="nombre" name="nombre" maxlength="40" value="<?= e($formNombre) ?>">
            </p>
            <p>
                <?php foreach (EVALUACIONES as $i => $evaluacion): ?>
                    <label for="nota<?= $i ?>"><?= $evaluacion ?>:</label>
                    <input type="text" inputmode="decimal" size="5" id="nota<?= $i ?>" name="notas[<?= $i ?>]"
                           value="<?= e((string) ($formNotas[$i] ?? '')) ?>">
                <?php endforeach; ?>
            </p>
            <button type="submit" name="accion" value="guardar">Guardar alumno</button>
        </fieldset>

        <!-- TODO (paso 4): carga masiva (textarea con líneas Nombre;nota1;nota2;nota3 y botón "Cargar") -->

        <!-- TODO (paso 3): vaciar cuaderno (casilla de confirmación + botón "Vaciar") -->

        <!-- ===== ESTADO de la aplicación en campos ocultos =====
             alumnos[Nombre][0], alumnos[Nombre][1], alumnos[Nombre][2]
             PHP reconstruye automáticamente el array multidimensional $_POST['alumnos'] -->
        <?php
        foreach ($cuaderno as $nombre => $notas) {
            foreach ($notas as $i => $nota) {
                echo "<input type='hidden' name='alumnos[" . e($nombre) . "][{$i}]' value='{$nota}'>\n";
            }
        }
        ?>
    </form>
</body>

</html>
