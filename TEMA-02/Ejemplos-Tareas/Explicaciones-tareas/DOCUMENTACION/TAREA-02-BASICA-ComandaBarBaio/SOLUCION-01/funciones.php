<?php
/*
 *
 * TAREA-02 BÁSICA: Comanda del Bar Baio — funciones.php
 *  Funciones sin HTML: validar la comanda, sacar las líneas del ticket y calcular los totales.
 */

// constantes de la comanda
const MESAS = 12;                   // mesas de la 1 a la 12
const MAX_UNIDADES = 20;            // de 0 a 20 unidades de cada producto
const MAX_NOTA = 80;                // la nota para la cocina, como mucho 80 caracteres
// constantes de las promociones y del IVA
const PROMO_PRODUCTO = 'cafe';      // promoción 3x2 en cafés: de cada 3, uno gratis
const PROMO_CADA = 3;
const DESCUENTO_SOCIO = 10;         // los socios del club Baio tienen un 10 % de descuento
const IVA = 10;                     // IVA de hostelería: 10 %, ya incluido en los precios

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int|float $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// función auxiliar euros(float $importe): string -> "1.234,50 €"
function euros(float $importe): string
{
    return number_format($importe, 2, ',', '.') . ' €';
}

// función validarComanda(array $datos, array $carta): array -> [comanda, errores]
function validarComanda(array $datos, array $carta): array
{
    $errores = [];

    // la mesa: un entero de 1 a MESAS
    $textoMesa = is_string($datos['mesa'] ?? null) ? trim($datos['mesa']) : '';
    $mesa = filter_var($textoMesa, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => MESAS]]);
    if ($mesa === false) {
        $errores[] = 'La mesa debe ser un número entero entre 1 y ' . MESAS . '.';
    }

    // las cantidades llegan en un array cantidad[codigo]; solo se miran los códigos de la carta
    $recibidas = $datos['cantidad'] ?? [];
    if (!is_array($recibidas)) {
        $recibidas = [];
        $errores[] = 'Las cantidades no tienen el formato correcto.';
    }
    $cantidades = [];
    $unidades = 0;
    $hayMal = false;
    foreach ($carta as $codigo => $producto) {
        $texto = is_string($recibidas[$codigo] ?? null) ? trim($recibidas[$codigo]) : '';
        if ($texto === '') {
            $texto = '0';                                    // un campo vacío es 0 unidades
        }
        $cantidad = filter_var($texto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => MAX_UNIDADES]]);
        if ($cantidad === false) {
            $errores[] = "La cantidad de «{$producto['nombre']}» debe ser un entero entre 0 y " . MAX_UNIDADES
                . " (has escrito «{$texto}»).";
            $cantidades[$codigo] = $texto;                   // se guarda lo escrito, para el formulario pegajoso
            $hayMal = true;
        } else {
            $cantidades[$codigo] = $cantidad;
            $unidades += $cantidad;
        }
    }
    if ($unidades === 0 && !$hayMal) {
        $errores[] = 'La comanda está vacía: pide al menos un producto.';
    }

    // la nota para la cocina es opcional, pero no muy larga
    $nota = is_string($datos['nota'] ?? null) ? trim($datos['nota']) : '';
    if (mb_strlen($nota) > MAX_NOTA) {
        $errores[] = 'La nota no puede tener más de ' . MAX_NOTA . ' caracteres (tiene ' . mb_strlen($nota) . ').';
    }

    $comanda = [
        'mesa' => $mesa === false ? $textoMesa : $mesa,
        'cantidades' => $cantidades,
        'socio' => isset($datos['socio']),                  // un checkbox sin marcar no se envía
        'nota' => $nota,
    ];
    return [$comanda, $errores];
}

// función lineasTicket(array $cantidades, array $carta): array -> líneas agrupadas por categoría
function lineasTicket(array $cantidades, array $carta): array
{
    $grupos = [];
    foreach ($carta as $codigo => $producto) {
        $cantidad = $cantidades[$codigo];
        if ($cantidad > 0) {
            $importe = round($cantidad * $producto['precio'], 2);
            $grupos[$producto['categoria']][] = [
                'cantidad' => $cantidad,
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'importe' => $importe,
            ];
        }
    }
    return $grupos;
}

// función subtotal(array $lineas): float -> suma de los importes de unas líneas
function subtotal(array $lineas): float
{
    return round(array_sum(array_column($lineas, 'importe')), 2);
}

// función calcularTotales(array $comanda, array $carta, float $suma): array -> promoción, descuento, total e IVA
function calcularTotales(array $comanda, array $carta, float $suma): array
{
    // 3x2 en cafés: por cada 3 cafés, uno gratis
    $gratis = intdiv($comanda['cantidades'][PROMO_PRODUCTO], PROMO_CADA);
    $promocion = round($gratis * $carta[PROMO_PRODUCTO]['precio'], 2);

    // el descuento de socio se calcula después de la promoción
    $descuento = $comanda['socio'] ? round(($suma - $promocion) * DESCUENTO_SOCIO / 100, 2) : 0.0;
    $total = round($suma - $promocion - $descuento, 2);

    // los precios ya llevan el IVA: base = total / 1,10 y el IVA es lo que falta hasta el total
    $base = round($total / (1 + IVA / 100), 2);
    $cuotaIva = round($total - $base, 2);

    return [
        'suma' => $suma,
        'gratis' => $gratis,
        'promocion' => $promocion,
        'descuento' => $descuento,
        'total' => $total,
        'base' => $base,
        'iva' => $cuotaIva,
    ];
}
