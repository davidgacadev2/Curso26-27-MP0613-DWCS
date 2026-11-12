<?php
/*
 * >>> PASO 1 de 6: descomposición del problema (solo TODOs y el esqueleto HTML, sin código PHP) <<<
 *
 * TAREA-02 AVANZADA: Cuatro en raya — funciones.php
 *  Funciones sin HTML (fáciles de depurar paso a paso). La partida es un array:
 *    ['nombres' => ['R' => 'Ana', 'A' => 'Máquina'], 'maquina' => 'A', 'nivel' => 'normal',
 *     'semilla' => 1234, 'jugadas' => '3324']
 *  Solo se guardan las jugadas (la columna de cada una, de 0 a 6, en orden): el tablero, el turno y el ganador
 *  se calculan cada vez repitiendo las jugadas con reconstruir().
 */

// TODO: constantes del juego
// TODO: constante de la casilla vacía
// TODO: constantes de las cuatro direcciones en las que se puede hacer cuatro en raya: [fila, columna]

// TODO: función auxiliar e(): escapar un texto para escribirlo en el HTML

// TODO: función auxiliar texto(array $datos, string $campo): string -> el campo sin espacios a los lados ('' si no es texto)

// TODO: función validarConfiguracion(array $datos): array -> [configuración, errores] del formulario inicial

// TODO: función nuevaPartida(array $configuracion, int $semilla): array -> quien empieza lleva las rojas

// TODO: función turno(string $jugadas): string -> ROJA si se han hecho un número par de jugadas, AMARILLA si no

// TODO: función leerEstado(array $datos): array -> la partida de los campos ocultos (sin comprobar aún)

// TODO: función tableroVacio(): array -> FILAS x COLUMNAS casillas vacías (la fila 0 es la de arriba)

// TODO: función soltarFicha(array &$tablero, int $columna, string $ficha): ?int -> la ficha cae a la casilla libre más baja; devuelve su fila (null si la columna está llena)

// TODO: función columnasLibres(array $tablero): array -> columnas en las que todavía cabe una ficha

// TODO: función reconstruir(string $jugadas): ?array -> repetir las jugadas: tablero, última ficha y ganador (null si las jugadas no son posibles)
        // TODO: después de un cuatro en raya no puede haber más jugadas
        // TODO: ¿ha hecho cuatro en raya con esta ficha?

// TODO: función lineaGanadora(array $tablero, int $fila, int $columna): array -> casillas ("f-c") del cuatro en raya que pasa por esa ficha, o []

// TODO: función estadoPartida(array $situacion, string $jugadas): string -> "ganada", "empate" o "jugando"

// TODO: función ganaria(array $tablero, int $columna, string $ficha): bool -> ¿hace cuatro en raya esa ficha en esa columna?

// TODO: función regala(array $tablero, int $columna, string $ficha): bool -> si juego ahí, ¿puede ganar el rival justo encima?

// TODO: función jugadaMaquina(array $tablero, string $ficha, string $nivel, int $azar): int -> la columna que elige la máquina

// TODO: función moverMaquina(array &$partida): ?int -> si le toca a la máquina y no ha terminado, juega; devuelve la columna (o null)

// TODO: función deshacer(array &$partida): bool -> quitar la última jugada (contra la máquina, hasta que vuelva a tocar a la persona)

// TODO: función historial(array $partida): array -> "Ana (rojas): columna 4", una línea por jugada

// TODO: función estadoValido(array $partida): bool -> ¿son posibles los nombres, la máquina, el nivel, la semilla y las jugadas?
