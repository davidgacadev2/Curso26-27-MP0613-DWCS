<?php
/*
 * >>> PASO 1 de 4: descomposición del problema (solo TODOs y el esqueleto HTML, sin código PHP) <<<
 *
 * TAREA-02 BÁSICA: Comanda del Bar Baio
 *  - una única página que se envía a sí misma por POST: el formulario de la comanda y, si es correcta, el ticket;
 *  - la carta está en carta.php (un array que se recoge con require) y los cálculos en funciones.php;
 *  - no hay que recordar nada entre peticiones: cada envío trae la comanda completa.
 */
// TODO: cargar las funciones y la carta

// TODO: valores iniciales: comanda vacía, sin errores y sin ticket

// TODO: botón "Calcular": validar la comanda
    // TODO: si es correcta, preparar el ticket: líneas por categoría y suma
        // TODO: promoción 3x2, descuento de socio y desglose del IVA

// TODO: agrupar la carta por categorías para dibujar el formulario
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

<!-- TODO: mensajes de error -->

<form method="post">
    <!-- TODO: mesa, un grupo (fieldset) por categoría con la cantidad de cada producto, socio y nota (todo pegajoso) -->
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
