<?php
/*
 * >>> PASO 2 de 4: la carta con require, el formulario pegajoso generado desde la carta y la validación con todos los errores <<<
 *
 * TAREA-02 BÁSICA: Comanda del Bar Baio
 *  - una única página que se envía a sí misma por POST: el formulario de la comanda y, si es correcta, el ticket;
 *  - la carta está en carta.php (un array que se recoge con require) y los cálculos en funciones.php;
 *  - no hay que recordar nada entre peticiones: cada envío trae la comanda completa.
 */
// cargar las funciones y la carta
require __DIR__ . '/funciones.php';
$carta = require __DIR__ . '/carta.php';

// valores iniciales: comanda vacía, sin errores y sin ticket
$comanda = ['mesa' => '', 'cantidades' => array_fill_keys(array_keys($carta), 0), 'socio' => false, 'nota' => ''];
$errores = [];
$ticket = null;

// botón "Calcular": validar la comanda
if (isset($_POST['calcular'])) {
    [$comanda, $errores] = validarComanda($_POST, $carta);
    // TODO: si es correcta, preparar el ticket: líneas por categoría y suma
        // TODO: promoción 3x2, descuento de socio y desglose del IVA
}

// agrupar la carta por categorías para dibujar el formulario
$categorias = [];
foreach ($carta as $codigo => $producto) {
    $categorias[$producto['categoria']][$codigo] = $producto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda del Bar Baio</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Bar Baio — Comanda</h1>

<!-- mensajes de error -->
<?php if ($errores !== []): ?>
    <ul class="errores">
        <?php foreach ($errores as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <!-- mesa, un grupo (fieldset) por categoría con la cantidad de cada producto, socio y nota (todo pegajoso) -->
    <p><label>Mesa (1-<?= MESAS ?>): <input name="mesa" value="<?= e($comanda['mesa']) ?>" size="3"></label></p>
    <?php foreach ($categorias as $categoria => $productos): ?>
        <fieldset>
            <legend><?= e($categoria) ?></legend>
            <?php foreach ($productos as $codigo => $producto): ?>
                <label class="producto">
                    <span><?= e($producto['nombre']) ?> (<?= e(number_format($producto['precio'], 2, ',', '.')) ?> €)</span>
                    <input name="cantidad[<?= e($codigo) ?>]" value="<?= e($comanda['cantidades'][$codigo]) ?>" size="3">
                </label>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>
    <p><label><input type="checkbox" name="socio"<?= $comanda['socio'] ? ' checked' : '' ?>> Socio del club Baio (10 % de descuento)</label></p>
    <p><label>Nota para la cocina: <input name="nota" value="<?= e($comanda['nota']) ?>" size="50"></label></p>
    <p>
        <button name="calcular">Calcular el ticket</button>
        <!-- TODO: enlace para empezar una comanda nueva -->
    </p>
</form>

<!-- TODO: el ticket: una tabla con las líneas agrupadas por categoría, el subtotal de cada una y la suma -->
            <!-- TODO: promoción, descuento de socio, total y desglose del IVA -->
        <!-- TODO: la nota para la cocina, si la hay -->
</body>
</html>
