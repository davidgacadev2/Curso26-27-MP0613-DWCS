<?php
/*
 * >>> PASO 2 de 5: configuración, tablero vacío y estado en campos hidden <<<
 *
 * TAREA-02 AVANZADA: Buscaminas  (SOLUCIÓN 01: estado en campos hidden, fila a fila)
 *
 *  index.php hace de "controlador":
 *      1) lee la petición ($_POST)
 *      2) recupera / crea la partida y aplica la jugada (logica.php, estado.php)
 *      3) genera la página (vista.php)
 */
require_once 'estado.php';
require_once 'vista.php';

$errores = [];
$mensaje = '';
$partida = null;

// Modo de juego: lista blanca de valores
$modo = ($_POST['modo'] ?? '') === 'bandera' ? 'bandera' : 'descubrir';

// Valores por defecto del formulario de configuración
$config = ['nivel' => 'principiante'] + NIVELES['principiante'];

if (isset($_POST['nueva'])) {
    // ---- Petición de NUEVA PARTIDA ----
    foreach (['nivel', 'filas', 'columnas', 'minas'] as $campo) {
        $config[$campo] = (string) ($_POST[$campo] ?? '');   // formulario pegajoso
    }
    $dimensiones = validarConfiguracion($_POST, $errores);
    if ($dimensiones !== null) {
        [$filas, $columnas, $minas] = $dimensiones;
        $partida = nuevaPartida($filas, $columnas, $minas);
        $modo    = 'descubrir';
    }
} elseif (isset($_POST['celda'])) {
    // ---- JUGADA: clic en una casilla ----
    $partida = recuperarPartida($_POST['partida'] ?? null);

    if ($partida === null) {
        $errores[] = 'Los datos de la partida no son válidos (¿se ha modificado la página?). Empieza una nueva partida.';
    } elseif ($partida['resultado'] == EN_JUEGO) {
        $coordenadas = leerCoordenadas((string) $_POST['celda'], $partida['filas'], $partida['columnas']);
        if ($coordenadas === null) {
            $errores[] = 'La casilla indicada no existe.';
        } else {
            [$fila, $columna] = $coordenadas;
            // TODO (paso 4): en modo 'bandera', poner/quitar la bandera con alternarBandera()
            // TODO (paso 3): descubrir la casilla con jugarCasilla()
        }
    }

    if ($partida !== null) {
        $config = configuracionDe($partida);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscaminas</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <h1>💣 Buscaminas</h1>

    <?= pintarConfiguracion($config) ?>

    <?php if (count($errores) > 0): ?>
        <div class="errores">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($mensaje !== ''): ?>
        <p class="aviso"><?= e($mensaje) ?></p>
    <?php endif; ?>

    <?php
    if ($partida !== null) {
        // TODO (paso 3): marcador y mensaje de fin de partida con pintarMarcador()
        echo pintarTablero($partida, $modo, partidaACamposOcultos($partida));
    } elseif (count($errores) == 0) {
        echo "<p class='ayuda'>Elige un nivel y pulsa <strong>Empezar</strong>. El primer clic nunca tiene mina.</p>";
    }
    ?>
</body>

</html>
