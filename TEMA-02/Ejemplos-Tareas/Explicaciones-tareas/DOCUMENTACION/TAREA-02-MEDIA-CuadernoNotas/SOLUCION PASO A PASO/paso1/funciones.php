<?php
/*
 * funciones.php
 *  Lógica del cuaderno de notas. Ninguna función hace echo: todas reciben datos
 *  y devuelven resultados (o modifican arrays recibidos POR REFERENCIA, indicado con &).
 *
 *  Estructura del cuaderno (array asociativo de arrays numéricos):
 *      $cuaderno = [
 *          'Ana Pérez Lago' => [7.5, 6.0, 8.25],     // notas de 1ª, 2ª y 3ª evaluación
 *          'Brais Otero'    => [4.0, 5.5, 3.0],
 *      ];
 */

// TODO: constantes EVALUACIONES, PESOS (0.30, 0.30, 0.40), CALIFICACIONES y NOTA_APROBADO

// Función auxiliar TODO: normalizarNombre(string $nombre): string
//      quitar espacios sobrantes y poner mayúscula inicial en cada palabra (funciones mb_)

// Función auxiliar TODO: nombreValido(string $nombre): bool
//      3-40 caracteres: letras (con tildes, ñ...), espacios, guiones o apóstrofos (preg_match con /u)

// Función auxiliar TODO: validarNota(?string $texto, string $campo, array &$errores): ?float
//      admite coma o punto decimal; número entre 0 y 10

// Función auxiliar TODO: validarAlumno(string $nombre, array $notasTexto, array &$errores): ?array
//      devuelve [nombreNormalizado, [n1, n2, n3]] o null

// Función auxiliar TODO: recuperarCuaderno(mixed $datos): array
//      reconstruir el cuaderno de los campos hidden, descartando lo que no sea válido

// Función auxiliar TODO: mediaPonderada(array $notas): float

// Función auxiliar TODO: calificacion(float $nota): string
//      Insuficiente < 5 <= Suficiente < 6 <= Bien < 7 <= Notable < 9 <= Sobresaliente

// Función auxiliar TODO: procesarCargaMasiva(string $texto, array &$cuaderno, array &$errores): int
//      una línea por alumno; ignorar vacías y las que empiezan por #; errores con nº de línea

// Función auxiliar TODO: ordenarCuaderno(array $cuaderno, string $criterio): array
//      'nombre' (ksort) o 'media' (arsort sobre un array nombre => media)

// Función auxiliar TODO: estadisticas(array $cuaderno): array
//      nº alumnos, media del grupo, mejor y peor, aprobados, porcentaje y distribución por calificación

// Función auxiliar TODO: formatoNota(float $nota): string      6.5 -> "6,50"

// Función auxiliar TODO: e(string $texto): string             atajo de htmlspecialchars()
