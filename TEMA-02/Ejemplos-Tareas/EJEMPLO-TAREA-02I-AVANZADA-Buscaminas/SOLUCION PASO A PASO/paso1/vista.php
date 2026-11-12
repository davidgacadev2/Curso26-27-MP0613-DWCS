<?php
/*
 * vista.php
 *  Funciones que GENERAN el HTML (lo devuelven como string; el echo lo hace index.php).
 */
// TODO: incluir logica.php (require_once)

// Función auxiliar TODO: e(string $texto): string             atajo de htmlspecialchars()

// Función auxiliar TODO: configuracionDe(array $partida): array
//      valores del formulario de configuración a partir de la partida en juego

// Función auxiliar TODO: pintarConfiguracion(array $config): string
//      formulario "Nueva partida": radios de nivel + filas, columnas y minas + botón "Empezar"

// Función auxiliar TODO: pintarMarcador(array $partida): string
//      minas restantes, jugadas, tiempo (mm:ss) y mensaje de victoria / derrota

// Función auxiliar TODO: pintarCasilla(array $partida, int $f, int $c): string
//      en juego: botón (oculta o bandera) o número descubierto
//      terminada: minas, mina explotada, banderas mal puestas

// Función auxiliar TODO: pintarTablero(array $partida, string $modo, string $camposOcultos): string
//      formulario con los radios de modo, la tabla de casillas y el estado en campos hidden
