<?php
/*
 * >>> PASO 3 de 6: el tablero reconstruido a partir de las jugadas: soltar fichas con gravedad y turnos alternos <<<
 *
 * TAREA-02 AVANZADA: Cuatro en raya — funciones.php
 *  Funciones sin HTML (fáciles de depurar paso a paso). La partida es un array:
 *    ['nombres' => ['R' => 'Ana', 'A' => 'Máquina'], 'maquina' => 'A', 'nivel' => 'normal',
 *     'semilla' => 1234, 'jugadas' => '3324']
 *  Solo se guardan las jugadas (la columna de cada una, de 0 a 6, en orden): el tablero, el turno y el ganador
 *  se calculan cada vez repitiendo las jugadas con reconstruir().
 */

// constantes del juego
const FILAS = 6;
const COLUMNAS = 7;
const ROJA = 'R';                   // las rojas empiezan siempre
const AMARILLA = 'A';
const COLORES = [ROJA => 'rojas', AMARILLA => 'amarillas'];
const NIVELES = ['facil' => 'fácil: juega al azar', 'normal' => 'normal: gana, tapa y prefiere el centro'];
const MAX_NOMBRE = 15;
const NOMBRE_MAQUINA = 'Máquina';
// constante de la casilla vacía
const VACIA = '.';
// TODO: constantes de las cuatro direcciones en las que se puede hacer cuatro en raya: [fila, columna]

// función auxiliar e(): escapar un texto para escribirlo en el HTML
function e(string|int $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// función auxiliar texto(array $datos, string $campo): string -> el campo sin espacios a los lados ('' si no es texto)
function texto(array $datos, string $campo): string
{
    return is_string($datos[$campo] ?? null) ? trim($datos[$campo]) : '';
}

// función validarConfiguracion(array $datos): array -> [configuración, errores] del formulario inicial
function validarConfiguracion(array $datos): array
{
    $configuracion = [
        'modo' => texto($datos, 'modo'),
        'jugador1' => texto($datos, 'jugador1'),
        'jugador2' => texto($datos, 'jugador2'),
        'nivel' => texto($datos, 'nivel'),
        'empieza' => texto($datos, 'empieza'),
    ];
    $errores = [];
    if (!in_array($configuracion['modo'], ['dos', 'maquina'], true)) {
        $errores[] = 'Elige si juegan dos personas o contra la máquina.';
    }
    $largo1 = mb_strlen($configuracion['jugador1']);
    if ($largo1 < 1 || $largo1 > MAX_NOMBRE) {
        $errores[] = 'El nombre del jugador 1 debe tener entre 1 y ' . MAX_NOMBRE . " caracteres (tiene $largo1).";
    }
    if ($configuracion['modo'] === 'dos') {
        // el jugador 2 solo hace falta si juegan dos personas
        $largo2 = mb_strlen($configuracion['jugador2']);
        if ($largo2 < 1 || $largo2 > MAX_NOMBRE) {
            $errores[] = 'El nombre del jugador 2 debe tener entre 1 y ' . MAX_NOMBRE . " caracteres (tiene $largo2).";
        } elseif (mb_strtolower($configuracion['jugador1']) === mb_strtolower($configuracion['jugador2'])) {
            $errores[] = 'Los dos jugadores no pueden llamarse igual.';
        }
    }
    if ($configuracion['modo'] === 'maquina') {
        if (!array_key_exists($configuracion['nivel'], NIVELES)) {
            $errores[] = 'Elige el nivel de la máquina.';
        }
        if (mb_strtolower($configuracion['jugador1']) === mb_strtolower(NOMBRE_MAQUINA)) {
            $errores[] = '«' . NOMBRE_MAQUINA . '» es el nombre de la máquina: elige otro.';
        }
    }
    if (!in_array($configuracion['empieza'], ['1', '2'], true)) {
        $errores[] = 'Elige quién empieza.';
    }
    return [$configuracion, $errores];
}

// función nuevaPartida(array $configuracion, int $semilla): array -> quien empieza lleva las rojas
function nuevaPartida(array $configuracion, int $semilla): array
{
    $contraMaquina = $configuracion['modo'] === 'maquina';
    $segundo = $contraMaquina ? NOMBRE_MAQUINA : $configuracion['jugador2'];
    $empiezaUno = $configuracion['empieza'] === '1';
    return [
        'nombres' => [
            ROJA => $empiezaUno ? $configuracion['jugador1'] : $segundo,
            AMARILLA => $empiezaUno ? $segundo : $configuracion['jugador1'],
        ],
        'maquina' => $contraMaquina ? ($empiezaUno ? AMARILLA : ROJA) : '',     // color de la máquina, o ''
        'nivel' => $contraMaquina ? $configuracion['nivel'] : 'facil',
        'semilla' => $semilla,
        'jugadas' => '',
    ];
}

// función turno(string $jugadas): string -> ROJA si se han hecho un número par de jugadas, AMARILLA si no
function turno(string $jugadas): string
{
    return strlen($jugadas) % 2 === 0 ? ROJA : AMARILLA;
}

// función leerEstado(array $datos): array -> la partida de los campos ocultos (sin comprobar aún)
function leerEstado(array $datos): array
{
    $semilla = filter_var($datos['semilla'] ?? '', FILTER_VALIDATE_INT);
    return [
        'nombres' => [ROJA => texto($datos, 'nombreR'), AMARILLA => texto($datos, 'nombreA')],
        'maquina' => texto($datos, 'maquina'),
        'nivel' => texto($datos, 'nivel'),
        'semilla' => $semilla === false ? 0 : $semilla,
        'jugadas' => texto($datos, 'jugadas'),
    ];
}

// función tableroVacio(): array -> FILAS x COLUMNAS casillas vacías (la fila 0 es la de arriba)
function tableroVacio(): array
{
    return array_fill(0, FILAS, array_fill(0, COLUMNAS, VACIA));
}

// función soltarFicha(array &$tablero, int $columna, string $ficha): ?int -> la ficha cae a la casilla libre más baja; devuelve su fila (null si la columna está llena)
function soltarFicha(array &$tablero, int $columna, string $ficha): ?int
{
    for ($fila = FILAS - 1; $fila >= 0; $fila--) {
        if ($tablero[$fila][$columna] === VACIA) {
            $tablero[$fila][$columna] = $ficha;
            return $fila;
        }
    }
    return null;
}

// función columnasLibres(array $tablero): array -> columnas en las que todavía cabe una ficha
function columnasLibres(array $tablero): array
{
    $libres = [];
    for ($columna = 0; $columna < COLUMNAS; $columna++) {
        if ($tablero[0][$columna] === VACIA) {           // si la casilla de arriba está vacía, cabe
            $libres[] = $columna;
        }
    }
    return $libres;
}

// función reconstruir(string $jugadas): ?array -> repetir las jugadas: tablero, última ficha y ganador (null si las jugadas no son posibles)
function reconstruir(string $jugadas): ?array
{
    // solo dígitos del 0 al 6 (con /D, el $ no acepta un salto de línea al final)
    if (!preg_match('/^[0-6]*$/D', $jugadas) || strlen($jugadas) > FILAS * COLUMNAS) {
        return null;
    }
    $tablero = tableroVacio();
    $ultima = '';
    $ganador = '';
    $linea = [];
    foreach (str_split($jugadas) as $numero => $caracter) {
        // TODO: después de un cuatro en raya no puede haber más jugadas
        $columna = (int) $caracter;
        $ficha = $numero % 2 === 0 ? ROJA : AMARILLA;
        $fila = soltarFicha($tablero, $columna, $ficha);
        if ($fila === null) {
            return null;                                    // la columna ya estaba llena
        }
        $ultima = "$fila-$columna";
        // TODO: ¿ha hecho cuatro en raya con esta ficha?
    }
    return ['tablero' => $tablero, 'ultima' => $ultima, 'ganador' => $ganador, 'linea' => $linea];
}

// TODO: función lineaGanadora(array $tablero, int $fila, int $columna): array -> casillas ("f-c") del cuatro en raya que pasa por esa ficha, o []

// TODO: función estadoPartida(array $situacion, string $jugadas): string -> "ganada", "empate" o "jugando"

// TODO: función ganaria(array $tablero, int $columna, string $ficha): bool -> ¿hace cuatro en raya esa ficha en esa columna?

// TODO: función regala(array $tablero, int $columna, string $ficha): bool -> si juego ahí, ¿puede ganar el rival justo encima?

// TODO: función jugadaMaquina(array $tablero, string $ficha, string $nivel, int $azar): int -> la columna que elige la máquina

// TODO: función moverMaquina(array &$partida): ?int -> si le toca a la máquina y no ha terminado, juega; devuelve la columna (o null)

// TODO: función deshacer(array &$partida): bool -> quitar la última jugada (contra la máquina, hasta que vuelva a tocar a la persona)

// TODO: función historial(array $partida): array -> "Ana (rojas): columna 4", una línea por jugada

// TODO: función estadoValido(array $partida): bool -> ¿son posibles los nombres, la máquina, el nivel, la semilla y las jugadas?
