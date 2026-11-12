<?php
/*
 * funciones.php
 *  Funciones auxiliares del calendario.
 *  Este fichero solo contiene código PHP, por eso NO cerramos la etiqueta "?>"
 *  (así evitamos enviar al navegador espacios o saltos de línea accidentales).
 */

// TODO: constantes de la aplicación: ANIO_MIN, ANIO_MAX, NOMBRES_MESES (1 => 'Enero', ...) y DIAS_SEMANA ('Lun' ... 'Dom')

// Función auxiliar TODO: esBisiesto(int $anio): bool
//      divisible por 4 pero no por 100, o divisible por 400

// Función auxiliar TODO: diasEnMes(int $mes, int $anio): int
//      con switch; ¡prohibido date('t') y cal_days_in_month()!

// Función auxiliar TODO: primerDiaSemana(int $mes, int $anio): int
//      día de la semana del día 1: 1 = lunes ... 7 = domingo (date('N', mktime(...)))

// Función auxiliar TODO: validarEntero(?string $valor, int $min, int $max, string $campo, array &$errores): ?int
//      devuelve el entero o null, añadiendo el mensaje de error a $errores (paso POR REFERENCIA)

// Función auxiliar TODO: mesAnterior(int $mes, int $anio): array      -> [mes, año]
// Función auxiliar TODO: mesSiguiente(int $mes, int $anio): array     -> [mes, año]

// Función auxiliar TODO: claveFestivo(int $mes, int $dia): string
//      'mm-dd', la clave con la que buscamos en el array de festivos

// Función auxiliar TODO: generarCalendario(int $mes, int $anio, array $festivos): string
//      devuelve el HTML de la tabla: cabecera, celdas vacías iniciales, días (clases finde, festivo, hoy),
//      celdas vacías finales

// Función auxiliar TODO: festivosDelMes(int $mes, array $festivos): array      -> [día => descripción]

// Función auxiliar TODO: contarLaborables(int $mes, int $anio, array $festivos): int
