<?php
/*
 * config.php
 *  Constantes de configuración del juego.
 */

// Niveles predefinidos
const NIVELES = [
    'principiante' => ['nombre' => 'Principiante (9 x 9, 10 minas)',   'filas' => 9,  'columnas' => 9,  'minas' => 10],
    'intermedio'   => ['nombre' => 'Intermedio (16 x 16, 40 minas)',    'filas' => 16, 'columnas' => 16, 'minas' => 40],
    'experto'      => ['nombre' => 'Experto (16 x 30, 99 minas)',       'filas' => 16, 'columnas' => 30, 'minas' => 99],
];

// Límites del nivel personalizado
const FILAS_MIN    = 5;
const FILAS_MAX    = 16;
const COLUMNAS_MIN = 5;
const COLUMNAS_MAX = 30;
const PORCENTAJE_MAX_MINAS = 35;   // como mucho el 35 % de las casillas pueden ser minas

// Contenido de una casilla del tablero: MINA o el número de minas vecinas (0..8)
const MINA = -1;

// Estado de una casilla para el jugador (un carácter para que ocupe poco en los campos ocultos)
const OCULTA      = 'O';
const DESCUBIERTA = 'D';
const BANDERA     = 'B';

// Resultado de la partida
const EN_JUEGO = '';
const GANADA   = 'ganada';
const PERDIDA  = 'perdida';
