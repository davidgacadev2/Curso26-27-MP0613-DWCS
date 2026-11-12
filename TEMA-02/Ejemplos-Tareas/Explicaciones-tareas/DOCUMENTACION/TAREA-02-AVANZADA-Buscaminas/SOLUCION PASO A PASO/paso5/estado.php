<?php
/*
 * estado.php  (SOLUCIÓN 02)
 *  Guardar y recuperar la partida en UN ÚNICO campo oculto, CIFRADO y AUTENTICADO.
 *
 *  Problema de la SOLUCIÓN 01: con "Ver código fuente" el jugador ve dónde están las minas, y con
 *  las herramientas de desarrollador puede cambiar el tablero.
 *
 *  Idea:
 *      partida (array) --json_encode--> texto JSON --openssl_encrypt--> bytes cifrados --base64--> campo hidden
 *  y al recibirlo, el camino inverso.
 *
 *  Usamos AES-256-GCM, un cifrado "autenticado": además de cifrar, calcula una "etiqueta" (tag).
 *  Si alguien cambia aunque sea un solo bit del campo oculto, openssl_decrypt() devuelve false.
 *  Así conseguimos:
 *      - CONFIDENCIALIDAD: el jugador no puede leer dónde están las minas
 *      - INTEGRIDAD: el jugador no puede modificar la partida
 *
 *  Lo que NO evitamos: que el jugador reenvíe un estado ANTERIOR válido (por ejemplo, pulsando "Atrás"
 *  en el navegador después de pisar una mina). Para eso hace falta guardar el estado en el servidor
 *  ($_SESSION o una base de datos), que veremos en temas posteriores.
 */
require_once 'logica.php';

/**
 * Clave binaria de 32 bytes (256 bits) derivada de la clave secreta de config.php.
 */
function claveCifrado(): string
{
    return hash('sha256', CLAVE_SECRETA, true);
}

/**
 * Convierte la partida en un texto seguro para poner en el value de un campo hidden.
 */
function cifrarPartida(array $partida): string
{
    $json = json_encode($partida);

    // Vector de inicialización: debe ser aleatorio y distinto en cada cifrado (no es secreto)
    $iv  = random_bytes(openssl_cipher_iv_length(ALGORITMO_CIFRADO));
    $tag = '';   // openssl_encrypt lo rellena (parámetro por referencia)
    $cifrado = openssl_encrypt($json, ALGORITMO_CIFRADO, claveCifrado(), OPENSSL_RAW_DATA, $iv, $tag);

    // Juntamos iv (12 bytes) + tag (16 bytes) + datos cifrados y lo pasamos a base64 (texto "imprimible")
    return base64_encode($iv . $tag . $cifrado);
}

/**
 * Operación inversa. Devuelve la partida o null si el texto ha sido manipulado o no es válido.
 */
function descifrarPartida(string $texto): ?array
{
    $binario = base64_decode($texto, true);   // true = modo estricto: falla si hay caracteres no válidos
    $longitudIv = openssl_cipher_iv_length(ALGORITMO_CIFRADO);
    if ($binario === false || strlen($binario) <= $longitudIv + 16) {
        return null;
    }

    $iv      = substr($binario, 0, $longitudIv);
    $tag     = substr($binario, $longitudIv, 16);
    $cifrado = substr($binario, $longitudIv + 16);

    $json = openssl_decrypt($cifrado, ALGORITMO_CIFRADO, claveCifrado(), OPENSSL_RAW_DATA, $iv, $tag);
    if ($json === false) {
        return null;   // ¡datos manipulados!
    }

    $partida = json_decode($json, true);   // true = devuelve arrays asociativos (no objetos)
    return is_array($partida) ? $partida : null;
}

/**
 * Genera el (único) campo hidden con la partida cifrada.
 */
function partidaACamposOcultos(array $partida): string
{
    return "<input type='hidden' name='partida' value='" . cifrarPartida($partida) . "'>\n";
}

/**
 * Reconstruye la partida a partir de $_POST['partida'].
 *  Como el contenido está autenticado, sabemos que lo generó NUESTRO servidor: no hace falta
 *  revalidar casilla a casilla como en la SOLUCIÓN 01.
 */
function recuperarPartida(mixed $datos): ?array
{
    return is_string($datos) ? descifrarPartida($datos) : null;
}

/**
 * Valida el formulario de configuración. Devuelve [filas, columnas, minas] o null (con los errores en $errores).
 */
function validarConfiguracion(array $datos, array &$errores): ?array
{
    $nivel = $datos['nivel'] ?? '';
    if (array_key_exists($nivel, NIVELES)) {
        return [NIVELES[$nivel]['filas'], NIVELES[$nivel]['columnas'], NIVELES[$nivel]['minas']];
    }
    if ($nivel !== 'personalizado') {
        $errores[] = 'Elige un nivel de dificultad.';
        return null;
    }

    $filas    = filter_var($datos['filas'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => FILAS_MIN, 'max_range' => FILAS_MAX]]);
    $columnas = filter_var($datos['columnas'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => COLUMNAS_MIN, 'max_range' => COLUMNAS_MAX]]);
    $minas    = filter_var($datos['minas'] ?? '', FILTER_VALIDATE_INT);

    if ($filas === false) {
        $errores[] = 'Las filas deben ser un número entero entre ' . FILAS_MIN . ' y ' . FILAS_MAX . '.';
    }
    if ($columnas === false) {
        $errores[] = 'Las columnas deben ser un número entero entre ' . COLUMNAS_MIN . ' y ' . COLUMNAS_MAX . '.';
    }
    if ($minas === false) {
        $errores[] = 'El número de minas debe ser un número entero.';
    } elseif ($filas !== false && $columnas !== false) {
        $maximo = maximoMinas($filas, $columnas);
        if ($minas < 1 || $minas > $maximo) {
            $errores[] = "Para un tablero de {$filas} x {$columnas} el número de minas debe estar entre 1 y {$maximo}.";
        }
    }
    return count($errores) > 0 ? null : [$filas, $columnas, $minas];
}
