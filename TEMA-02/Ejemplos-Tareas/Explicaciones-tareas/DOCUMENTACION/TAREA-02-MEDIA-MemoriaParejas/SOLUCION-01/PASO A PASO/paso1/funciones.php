<?php
/*
 * >>> PASO 1 de 6: descomposición del problema (solo TODOs y el esqueleto HTML, sin código PHP) <<<
 *
 * TAREA-02 MEDIA: Memoria de parejas — funciones.php
 *  Funciones sin HTML (fáciles de depurar paso a paso): validar la configuración, crear el tablero, leer el
 *  estado de los campos ocultos, jugar una carta, saber si la partida ha terminado y validar el estado.
 */

// TODO: constantes del juego

// TODO: función auxiliar e(): escapar un texto para escribirlo en el HTML

// TODO: función validarEntrada(array $datos): array -> [configuración, errores] del formulario inicial

// TODO: función crearTablero(string $letras, int $columnas, int $semilla): array -> cada letra dos veces, barajadas, en filas

// TODO: función nuevaPartida(array $configuracion, int $semilla): array -> la partida recién empezada

// TODO: función leerEstado(array $datos): array -> la partida guardada en los campos ocultos

// TODO: función letraEn(array $partida, string $posicion): ?string -> la letra de "fila-columna" (null si no existe)

// TODO: función jugar(array &$partida, string $posicion): array -> gira una carta: [mensaje, cartas que se ven solo ahora]
    // TODO: si la partida ya ha terminado, no se juega

// TODO: función cartaVisible(array $partida, string $posicion, array $temporales): bool -> ¿se ve la letra?
    // TODO: al perder, se enseñan todas las cartas

// TODO: función estadoPartida(array $partida): string -> 'jugando', 'ganada' o 'perdida'

// TODO: función estadoValido(array $partida): bool -> ¿los campos ocultos son una partida posible?
