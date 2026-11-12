<?php
/*
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

// Criterio de ordenación (lista blanca: solo admitimos valores conocidos)
$orden = in_array($_POST['orden'] ?? '', ['nombre', 'media']) ? $_POST['orden'] : 'nombre';

// 2) ¿Qué acción ha pedido el usuario?
if (isset($_POST['eliminar'])) {
    // Botón "Eliminar" de una fila: su value es el nombre del alumno
    $nombre = (string) $_POST['eliminar'];
    if (array_key_exists($nombre, $cuaderno)) {
        unset($cuaderno[$nombre]);
        $mensajes[] = "Se ha eliminado a «{$nombre}».";
    }
} elseif (isset($_POST['editar'])) {
    // Botón "Editar": cargamos los datos del alumno en el formulario
    $nombre = (string) $_POST['editar'];
    if (array_key_exists($nombre, $cuaderno)) {
        $formNombre = $nombre;
        $formNotas  = array_map('formatoNota', $cuaderno[$nombre]);
    }
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

        case 'cargar':
            $cargados = procesarCargaMasiva($_POST['csv'] ?? '', $cuaderno, $errores);
            $mensajes[] = "Carga masiva: {$cargados} alumno(s) añadidos o actualizados.";
            break;

        case 'vaciar':
            if (isset($_POST['confirmarVaciar'])) {
                $cuaderno   = [];
                $mensajes[] = "Se ha vaciado el cuaderno.";
            } else {
                $errores[] = "Para vaciar el cuaderno debes marcar la casilla de confirmación.";
            }
            break;

        case 'ordenar':
            // No hay nada que hacer: el criterio ya lo hemos leído arriba
            break;
    }
}

$cuaderno = ordenarCuaderno($cuaderno, $orden);
$stats    = estadisticas($cuaderno);
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
                <p>
                    <label for="orden">Ordenar por:</label>
                    <select name="orden" id="orden">
                        <option value="nombre" <?= $orden == 'nombre' ? 'selected' : '' ?>>Nombre</option>
                        <option value="media" <?= $orden == 'media' ? 'selected' : '' ?>>Media (mayor a menor)</option>
                    </select>
                    <button type="submit" name="accion" value="ordenar">Ordenar</button>
                </p>
                <table class="notas">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <?php foreach (EVALUACIONES as $i => $evaluacion): ?>
                                <th><?= $evaluacion ?><br><small><?= PESOS[$i] * 100 ?>%</small></th>
                            <?php endforeach; ?>
                            <th>Media</th>
                            <th>Calificación</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cuaderno as $nombre => $notas):
                            $media = mediaPonderada($notas);
                            $clase = $media >= NOTA_APROBADO ? 'aprobado' : 'suspenso';
                        ?>
                            <tr class="<?= $clase ?>">
                                <td class="nombre"><?= e($nombre) ?></td>
                                <?php foreach ($notas as $nota): ?>
                                    <td class="<?= $nota < NOTA_APROBADO ? 'nota-suspensa' : '' ?>"><?= formatoNota($nota) ?></td>
                                <?php endforeach; ?>
                                <td><strong><?= formatoNota($media) ?></strong></td>
                                <td><?= calificacion($media) ?></td>
                                <td class="acciones">
                                    <button type="submit" name="editar" value="<?= e($nombre) ?>">Editar</button>
                                    <button type="submit" name="eliminar" value="<?= e($nombre) ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </fieldset>

        <!-- ===== Estadísticas ===== -->
        <?php if ($stats['alumnos'] > 0): ?>
            <fieldset>
                <legend>Estadísticas del grupo</legend>
                <ul class="estadisticas">
                    <li>Media del grupo: <strong><?= formatoNota($stats['mediaGrupo']) ?></strong></li>
                    <li>Mejor media: <strong><?= e($stats['mejor'][0]) ?></strong> (<?= formatoNota($stats['mejor'][1]) ?>)</li>
                    <li>Peor media: <strong><?= e($stats['peor'][0]) ?></strong> (<?= formatoNota($stats['peor'][1]) ?>)</li>
                    <li>Aprobados: <strong><?= $stats['aprobados'] ?></strong> de <?= $stats['alumnos'] ?>
                        (<?= number_format($stats['porcentaje'], 1, ',', '.') ?> %)</li>
                </ul>
                <table class="distribucion">
                    <?php foreach ($stats['distribucion'] as $calificacion => $cantidad):
                        $ancho = round($cantidad * 100 / $stats['alumnos']);
                    ?>
                        <tr>
                            <th><?= $calificacion ?></th>
                            <td><div class="barra" style="width: <?= $ancho ?>%"></div></td>
                            <td><?= $cantidad ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </fieldset>
        <?php endif; ?>

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

        <!-- ===== Carga masiva ===== -->
        <fieldset>
            <legend>Carga masiva</legend>
            <p class="ayuda">Un alumno por línea con el formato <code>Nombre Apellidos;nota1;nota2;nota3</code>.
                Las líneas que empiezan por <code>#</code> se ignoran.</p>
            <textarea name="csv" rows="5" cols="50" placeholder="Ana Pérez Lago;7,5;6;8&#10;Brais Otero;4;5,5;3"></textarea>
            <br>
            <button type="submit" name="accion" value="cargar">Cargar</button>
        </fieldset>

        <!-- ===== Vaciar ===== -->
        <?php if (count($cuaderno) > 0): ?>
            <fieldset>
                <legend>Vaciar cuaderno</legend>
                <label><input type="checkbox" name="confirmarVaciar" value="1"> Confirmo que quiero borrar todos los datos</label>
                <button type="submit" name="accion" value="vaciar" class="peligro">Vaciar</button>
            </fieldset>
        <?php endif; ?>

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
