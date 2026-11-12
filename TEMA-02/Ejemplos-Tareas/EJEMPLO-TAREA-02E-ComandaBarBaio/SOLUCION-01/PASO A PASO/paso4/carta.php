<?php
/*
 * >>> PASO 4 de 4 (ÚLTIMO): SOLUCIÓN COMPLETA <<<
 *
 * TAREA-02 BÁSICA: Comanda del Bar Baio — carta.php
 *  La carta del bar. Este fichero no escribe nada: devuelve un array con return, y index.php lo recoge con
 *  $carta = require 'carta.php';
 */
// devolver la carta: código del producto => nombre, categoría y precio (IVA incluido)
return [
    'cafe'      => ['nombre' => 'Café',                 'categoria' => 'Bebidas',   'precio' => 1.30],
    'colacao'   => ['nombre' => 'Cola Cao',             'categoria' => 'Bebidas',   'precio' => 1.60],
    'zumo'      => ['nombre' => 'Zumo de naranja',      'categoria' => 'Bebidas',   'precio' => 2.50],
    'agua'      => ['nombre' => 'Agua (50 cl)',         'categoria' => 'Bebidas',   'precio' => 1.20],
    'tostada'   => ['nombre' => 'Tostada con tomate',   'categoria' => 'Desayunos', 'precio' => 1.80],
    'croissant' => ['nombre' => 'Croissant',            'categoria' => 'Desayunos', 'precio' => 1.60],
    'tortilla'  => ['nombre' => 'Pincho de tortilla',   'categoria' => 'Desayunos', 'precio' => 2.50],
    'pulpo'     => ['nombre' => 'Ración de pulpo',      'categoria' => 'Raciones',  'precio' => 12.00],
    'zorza'     => ['nombre' => 'Zorza con patatas',    'categoria' => 'Raciones',  'precio' => 8.50],
    'empanada'  => ['nombre' => 'Empanada de zamburiñas', 'categoria' => 'Raciones', 'precio' => 3.20],
];
