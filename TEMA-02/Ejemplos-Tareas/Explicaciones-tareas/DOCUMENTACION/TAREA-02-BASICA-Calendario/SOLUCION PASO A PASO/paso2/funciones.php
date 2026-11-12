<?php
/*
 * funciones.php
 *  Funciones auxiliares del calendario.
 *  Este fichero solo contiene código PHP, por eso NO cerramos la etiqueta "?>"
 *  (así evitamos enviar al navegador espacios o saltos de línea accidentales).
 */

// Constantes de la aplicación
const ANIO_MIN = 1900;
const ANIO_MAX = 2100;

const NOMBRES_MESES = [
    1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];  // al poner la clave 1 en el primer elemento, el resto continúan en 2, 3, ...

const DIAS_SEMANA = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];


/**
 * Un año es bisiesto si es divisible por 4 pero no por 100, o si es divisible por 400.
 */
// TODO (paso 3): function esBisiesto(int $anio): bool

/**
 * Devuelve el número de días del mes indicado (sin usar date('t') ni cal_days_in_month()).
 */
// TODO (paso 3): function diasEnMes(int $mes, int $anio): int

/**
 * Día de la semana del día 1 del mes: 1 = lunes, ..., 7 = domingo (formato 'N' de date()).
 */
// TODO (paso 3): function primerDiaSemana(int $mes, int $anio): int

/**
 * Valida que $valor sea un entero dentro del rango [$min, $max].
 *  - si es correcto devuelve el entero
 *  - si no, añade un mensaje al array $errores (pasado POR REFERENCIA) y devuelve null
 */
function validarEntero(?string $valor, int $min, int $max, string $campo, array &$errores): ?int
{
    $valor = trim($valor ?? '');
    if ($valor === '') {
        $errores[] = "El campo «{$campo}» es obligatorio.";
        return null;
    }
    // filter_var devuelve el entero si la cadena representa un entero en el rango, o false si no
    $numero = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
    if ($numero === false) {
        $errores[] = "El campo «{$campo}» debe ser un número entero entre {$min} y {$max}.";
        return null;
    }
    return $numero;
}

/**
 * Devuelve [mes, año] del mes anterior. Usamos un array para devolver 2 valores.
 */
// TODO (paso 4): function mesAnterior(int $mes, int $anio): array

/**
 * Devuelve [mes, año] del mes siguiente.
 */
// TODO (paso 4): function mesSiguiente(int $mes, int $anio): array

/**
 * Clave con la que buscamos un día en el array de festivos: 'mm-dd' (por ejemplo '05-17').
 */
// TODO (paso 3): function claveFestivo(int $mes, int $dia): string

/**
 * Genera y devuelve (no hace echo) el HTML de la tabla del calendario.
 *  - $festivos: array asociativo 'mm-dd' => 'Descripción'
 */
// TODO (paso 3): function generarCalendario(int $mes, int $anio, array $festivos): string

/**
 * Devuelve los festivos del mes indicado: array 'día' => 'descripción'.
 */
// TODO (paso 4): function festivosDelMes(int $mes, array $festivos): array

/**
 * Cuenta los días laborables (lunes a viernes que no son festivo) del mes.
 */
// TODO (paso 4): function contarLaborables(int $mes, int $anio, array $festivos): int
