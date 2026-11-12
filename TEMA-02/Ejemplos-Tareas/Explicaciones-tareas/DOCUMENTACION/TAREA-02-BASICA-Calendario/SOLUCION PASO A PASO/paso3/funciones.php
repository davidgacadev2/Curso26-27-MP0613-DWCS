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
function esBisiesto(int $anio): bool
{
    return ($anio % 4 == 0 && $anio % 100 != 0) || $anio % 400 == 0;
}

/**
 * Devuelve el número de días del mes indicado (sin usar date('t') ni cal_days_in_month()).
 */
function diasEnMes(int $mes, int $anio): int
{
    switch ($mes) {
        case 2:
            return esBisiesto($anio) ? 29 : 28;
        case 4:
        case 6:
        case 9:
        case 11:
            return 30;
        default:
            return 31;
    }
}

/**
 * Día de la semana del día 1 del mes: 1 = lunes, ..., 7 = domingo (formato 'N' de date()).
 */
function primerDiaSemana(int $mes, int $anio): int
{
    return (int) date('N', mktime(0, 0, 0, $mes, 1, $anio));
}

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
function claveFestivo(int $mes, int $dia): string
{
    return sprintf('%02d-%02d', $mes, $dia);
}

/**
 * Genera y devuelve (no hace echo) el HTML de la tabla del calendario.
 *  - $festivos: array asociativo 'mm-dd' => 'Descripción'
 */
function generarCalendario(int $mes, int $anio, array $festivos): string
{
    $html  = "<table class='calendario'>\n";
    $html .= "<caption>" . NOMBRES_MESES[$mes] . " de {$anio}</caption>\n";

    // Cabecera con los días de la semana
    $html .= "<thead><tr>";
    foreach (DIAS_SEMANA as $nombreDia) {
        $html .= "<th>{$nombreDia}</th>";
    }
    $html .= "</tr></thead>\n<tbody>\n<tr>";

    $hoy          = date('Y-n-j');                 // p.ej. "2026-10-16"
    $huecoInicial = primerDiaSemana($mes, $anio) - 1; // celdas vacías antes del día 1
    $totalDias    = diasEnMes($mes, $anio);

    // 1) Celdas vacías iniciales
    for ($i = 0; $i < $huecoInicial; $i++) {
        $html .= "<td class='vacio'></td>";
    }

    // 2) Días del mes. $columna indica la posición dentro de la semana (0 = lunes ... 6 = domingo)
    $columna = $huecoInicial;
    for ($dia = 1; $dia <= $totalDias; $dia++) {
        $clases = [];
        $titulo = '';
        if ($columna >= 5) {
            $clases[] = 'finde';
        }
        $clave = claveFestivo($mes, $dia);
        if (array_key_exists($clave, $festivos)) {
            $clases[] = 'festivo';
            $titulo   = " title='" . htmlspecialchars($festivos[$clave], ENT_QUOTES) . "'";
        }
        if ("{$anio}-{$mes}-{$dia}" === $hoy) {
            $clases[] = 'hoy';
        }
        $atributoClase = count($clases) > 0 ? " class='" . implode(' ', $clases) . "'" : '';
        $html .= "<td{$atributoClase}{$titulo}>{$dia}</td>";

        // Si llegamos al domingo cerramos la fila (y abrimos otra si quedan días)
        $columna++;
        if ($columna == 7) {
            $html .= "</tr>\n";
            if ($dia < $totalDias) {
                $html .= "<tr>";
            }
            $columna = 0;
        }
    }

    // 3) Celdas vacías finales para completar la última semana
    if ($columna != 0) {
        for (; $columna < 7; $columna++) {
            $html .= "<td class='vacio'></td>";
        }
        $html .= "</tr>\n";
    }

    $html .= "</tbody>\n</table>\n";
    return $html;
}

/**
 * Devuelve los festivos del mes indicado: array 'día' => 'descripción'.
 */
// TODO (paso 4): function festivosDelMes(int $mes, array $festivos): array

/**
 * Cuenta los días laborables (lunes a viernes que no son festivo) del mes.
 */
// TODO (paso 4): function contarLaborables(int $mes, int $anio, array $festivos): int
