<?php
/*
 * festivos.php
 *  Festivos de fecha fija (en Galicia). Clave 'mm-dd' => descripción.
 *  Observa que el fichero "devuelve" el array con return: así podemos hacer
 *      $festivos = require 'festivos.php';
 *  sin crear variables globales "escondidas" dentro del include.
 *
 *  (Los festivos móviles, como Jueves y Viernes Santo, se proponen como ampliación)
 */
return [
    '01-01' => 'Año Nuevo',
    '01-06' => 'Epifanía del Señor',
    '05-01' => 'Fiesta del Trabajo',
    '05-17' => 'Día das Letras Galegas',
    '07-25' => 'Día Nacional de Galicia',
    '08-15' => 'Asunción de la Virgen',
    '10-12' => 'Fiesta Nacional de España',
    '11-01' => 'Todos los Santos',
    '12-06' => 'Día de la Constitución',
    '12-08' => 'Inmaculada Concepción',
    '12-25' => 'Navidad',
];
