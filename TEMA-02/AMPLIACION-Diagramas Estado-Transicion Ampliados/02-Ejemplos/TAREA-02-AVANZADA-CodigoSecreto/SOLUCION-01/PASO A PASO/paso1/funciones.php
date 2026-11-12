<?php
/*
 * >>> PASO 1 de 6: descomposición a partir del autómata: estados, eventos, tabla de eventos-estados y transiciones, solo con TODOs <<<
 *
 * TAREA-02 AVANZADA: Código secreto — funciones.php
 *  Las acciones (AC) y los predicados (PL) del autómata que necesitan más de una línea, sin HTML.
 *  La partida es un array:
 *    ['posiciones' => 4, 'colores' => 6, 'maxIntentos' => 10, 'repetir' => false, 'semilla' => 1234,
 *     'intentos' => [[0, 1, 2, 3], ...], 'pista' => -1, 'ganadas' => 0, 'perdidas' => 0]
 *  El código secreto NO se guarda en la página: se calcula a partir de la semilla con una clave que solo
 *  conoce el servidor (hash_hmac), así que ver el código fuente de la página no sirve para hacer trampas.
 */

// TODO: constantes: los colores (nombre y color CSS), los límites de la configuración y la clave del servidor

// TODO: función auxiliar e(): escapar un texto para escribirlo en el HTML

// TODO: AC2 validarConfiguracion(array $datos): array -> [configuración, errores]; PL0 es "no hay errores"

// TODO: AC3 nuevaPartida(array $configuracion, int $semilla, int $ganadas, int $perdidas): array -> sin intentos ni pista

// TODO: codigoSecreto(array $partida): array -> el código (índices de color), siempre el mismo para la misma semilla

// TODO: AC1 leerPartida(array $datos): array -> la partida de los campos ocultos (sin comprobar: eso es PL1)

// TODO: AC5 leerIntento(array $datos, array $partida): array -> [intento o null, motivo]; PL2 es "intento no null"

// TODO: puntuar(array $intento, array $secreto): array -> [negras, blancas]: color y posición bien / solo color bien

// TODO: intentosRestantes(array $partida): int -> los permitidos menos los hechos (y menos uno si se pidió la pista)
    // TODO: la pista cuesta un intento

// TODO: textoIntentos(array $intentos): string -> [[0,1,2,3],[4,5,1,2]] pasa a "0123-4512" para el campo oculto

// TODO: AC8 elegirPista(array $partida, array $secreto): int -> la primera posición que el jugador no ha acertado nunca

// TODO: PL1 partidaValida(array $partida): bool -> ¿es una partida posible en ST2 (jugando)?
