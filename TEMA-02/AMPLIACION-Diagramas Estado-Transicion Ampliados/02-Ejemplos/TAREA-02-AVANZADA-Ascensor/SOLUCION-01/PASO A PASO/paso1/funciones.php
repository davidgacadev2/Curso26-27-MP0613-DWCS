<?php
/*
 * >>> PASO 1 de 6: descomposición a partir del autómata: estados, eventos, tabla de eventos-estados y transiciones, solo con TODOs <<<
 *
 * TAREA-02 AVANZADA: El ascensor del instituto — funciones.php
 *  Las acciones (AC) y los predicados (PL) del autómata que necesitan más de una línea, sin HTML.
 *  El ascensor es un array:
 *    ['piso' => 2, 'direccion' => 'sube', 'llamadas' => [0, 4], 'emergencias' => 0, 'recorridas' => 7, 'paradas' => 3]
 *  'direccion' es '' (parado), 'sube' o 'baja'; 'llamadas' es la lista ordenada de plantas que esperan.
 */

// TODO: constantes: las plantas y sus nombres
// TODO: constante: emergencias que se permiten antes de dejar el ascensor fuera de servicio

// TODO: función auxiliar e(): escapar un texto para escribirlo en el HTML

// TODO: AC0 ascensorInicial(): array -> en la planta baja, parado, sin llamadas y con los contadores a cero

// TODO: AC1 leerAscensor(array $datos): array -> el ascensor de los campos ocultos (sin comprobar: eso es PL0)

// TODO: AC2 leerPlanta(array $datos): ?int -> la planta del botón "llamar:3" (null si no es válida); PL1 es "no es null"

// TODO: AC3 anadirLlamada(array &$ascensor, int $planta): la añade a la lista (sin repetir y ordenada)

// TODO: PL4 masCercanaArriba(array $ascensor): bool -> la llamada más cercana está arriba (a igual distancia, también)

// TODO: hayLlamadas(array $ascensor, string $donde): bool -> ¿hay llamadas "arriba" o "abajo" del piso actual?

// TODO: AC5 mover(array &$ascensor, int $paso): sube (+1) o baja (-1) una planta y la cuenta

// TODO: AC6 parar(array &$ascensor): quita la llamada de la planta en la que está y cuenta la parada

// TODO: PL0 coherente(int $estado, array $ascensor): bool -> ¿son posibles estos datos EN ESTE ESTADO? (invariantes)
