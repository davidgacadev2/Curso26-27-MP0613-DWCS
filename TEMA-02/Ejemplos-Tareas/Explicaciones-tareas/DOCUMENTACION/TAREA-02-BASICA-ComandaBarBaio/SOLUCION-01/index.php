<?php
/*
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
    // si es correcta, preparar el ticket: líneas por categoría y suma
    if ($errores === []) {
        $grupos = lineasTicket($comanda['cantidades'], $carta);
        $suma = 0.0;
        foreach ($grupos as $lineas) {
            $suma += subtotal($lineas);
        }
        $ticket = ['grupos' => $grupos, 'suma' => round($suma, 2)];
        // promoción 3x2, descuento de socio y desglose del IVA
        $ticket['totales'] = calcularTotales($comanda, $carta, $ticket['suma']);
    }
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
        <!-- enlace para empezar una comanda nueva -->
        <a href="index.php">Nueva comanda</a>
    </p>
</form>

<!-- el ticket: una tabla con las líneas agrupadas por categoría, el subtotal de cada una y la suma -->
<?php if ($ticket !== null): ?>
    <section class="ticket">
        <h2>Ticket de la mesa <?= e($comanda['mesa']) ?></h2>
        <table>
            <tr><th>Uds.</th><th>Producto</th><th>Precio</th><th>Importe</th></tr>
            <?php foreach ($ticket['grupos'] as $categoria => $lineas): ?>
                <tr class="categoria"><th colspan="4"><?= e($categoria) ?></th></tr>
                <?php foreach ($lineas as $linea): ?>
                    <tr>
                        <td class="numero"><?= $linea['cantidad'] ?></td>
                        <td><?= e($linea['nombre']) ?></td>
                        <td class="numero"><?= euros($linea['precio']) ?></td>
                        <td class="numero"><?= euros($linea['importe']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="subtotal"><td colspan="3">Subtotal <?= e($categoria) ?></td><td class="numero"><?= euros(subtotal($lineas)) ?></td></tr>
            <?php endforeach; ?>
            <tr class="total"><td colspan="3">Suma</td><td class="numero"><?= euros($ticket['suma']) ?></td></tr>
            <!-- promoción, descuento de socio, total y desglose del IVA -->
            <?php $totales = $ticket['totales']; ?>
            <?php if ($totales['gratis'] > 0): ?>
                <tr><td colspan="3">Promoción 3x2 en cafés (<?= $totales['gratis'] ?> gratis)</td><td class="numero">−<?= euros($totales['promocion']) ?></td></tr>
            <?php endif; ?>
            <?php if ($comanda['socio']): ?>
                <tr><td colspan="3">Descuento de socio (<?= DESCUENTO_SOCIO ?> %)</td><td class="numero">−<?= euros($totales['descuento']) ?></td></tr>
            <?php endif; ?>
            <tr class="total"><td colspan="3">TOTAL (IVA incluido)</td><td class="numero"><?= euros($totales['total']) ?></td></tr>
            <tr class="iva"><td colspan="3">Base imponible</td><td class="numero"><?= euros($totales['base']) ?></td></tr>
            <tr class="iva"><td colspan="3">IVA (<?= IVA ?> %)</td><td class="numero"><?= euros($totales['iva']) ?></td></tr>
        </table>
        <!-- la nota para la cocina, si la hay -->
        <?php if ($comanda['nota'] !== ''): ?>
            <p class="nota">Nota para la cocina: <?= e($comanda['nota']) ?></p>
        <?php endif; ?>
    </section>
<?php endif; ?>
</body>
</html>
