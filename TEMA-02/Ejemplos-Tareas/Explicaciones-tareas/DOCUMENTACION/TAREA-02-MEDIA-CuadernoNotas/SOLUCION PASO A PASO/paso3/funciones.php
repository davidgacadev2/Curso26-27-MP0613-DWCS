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

const EVALUACIONES = ['1ª Ev.', '2ª Ev.', '3ª Ev.'];
const PESOS        = [0.30, 0.30, 0.40];             // deben sumar 1
const CALIFICACIONES = ['Insuficiente', 'Suficiente', 'Bien', 'Notable', 'Sobresaliente'];
const NOTA_APROBADO  = 5;

/**
 * Deja el nombre "limpio": sin espacios sobrantes y con la inicial de cada palabra en mayúscula.
 *  "  ana   PÉREZ  " -> "Ana Pérez"
 * Usamos las funciones mb_ porque los nombres pueden tener tildes y ñ (UTF-8).
 */
function normalizarNombre(string $nombre): string
{
    $nombre = trim(preg_replace('/\s+/u', ' ', $nombre));
    return mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
}

/**
 * Un nombre válido tiene entre 3 y 40 caracteres: letras (con tildes, ñ...), espacios, guiones o apóstrofos.
 *  Esto además nos garantiza que se puede usar sin problemas como clave en los campos hidden: alumnos[Nombre][0]
 */
function nombreValido(string $nombre): bool
{
    return preg_match("/^[\p{L}][\p{L} '\-]{2,39}$/u", $nombre) === 1;
}

/**
 * Valida una nota. Admite coma o punto decimal ("6,5" o "6.5").
 *  - devuelve la nota como float redondeada a 2 decimales
 *  - o null, añadiendo el error a $errores
 */
function validarNota(?string $texto, string $campo, array &$errores): ?float
{
    $texto = str_replace(',', '.', trim($texto ?? ''));
    if ($texto === '') {
        $errores[] = "La nota de «{$campo}» es obligatoria.";
        return null;
    }
    if (!is_numeric($texto)) {
        $errores[] = "La nota de «{$campo}» debe ser un número (has escrito «{$texto}»).";
        return null;
    }
    $nota = (float) $texto;
    if ($nota < 0 || $nota > 10) {
        $errores[] = "La nota de «{$campo}» debe estar entre 0 y 10.";
        return null;
    }
    return round($nota, 2);
}

/**
 * Valida nombre y las 3 notas recibidas como textos.
 *  Devuelve [nombreNormalizado, [n1, n2, n3]] o null si hay errores (que se añaden a $errores).
 */
function validarAlumno(string $nombre, array $notasTexto, array &$errores): ?array
{
    $erroresAntes = count($errores);

    $nombre = normalizarNombre($nombre);
    if ($nombre === '') {
        $errores[] = "El nombre es obligatorio.";
    } elseif (!nombreValido($nombre)) {
        $errores[] = "El nombre «{$nombre}» no es válido (3-40 letras, espacios, guiones o apóstrofos).";
    }

    $notas = [];
    foreach (EVALUACIONES as $i => $evaluacion) {
        $notas[$i] = validarNota($notasTexto[$i] ?? '', $evaluacion, $errores);
    }

    // Si el número de errores ha crecido, este alumno no es válido
    return count($errores) > $erroresAntes ? null : [$nombre, $notas];
}

/**
 * Reconstruye el cuaderno a partir de los campos hidden recibidos.
 *  ¡OJO! Los campos ocultos los puede modificar el usuario (herramientas de desarrollador),
 *  así que NO nos fiamos: volvemos a validar todo y descartamos lo que no sea correcto.
 */
function recuperarCuaderno(mixed $datos): array
{
    $cuaderno = [];
    if (!is_array($datos)) {
        return $cuaderno;
    }
    foreach ($datos as $nombre => $notas) {
        $erroresIgnorados = [];
        if (is_array($notas)) {
            $alumno = validarAlumno((string) $nombre, $notas, $erroresIgnorados);
            if ($alumno !== null) {
                $cuaderno[$alumno[0]] = $alumno[1];
            }
        }
    }
    return $cuaderno;
}

/**
 * Media ponderada según PESOS.
 */
function mediaPonderada(array $notas): float
{
    $media = 0;
    foreach ($notas as $i => $nota) {
        $media += $nota * PESOS[$i];
    }
    return round($media, 2);
}

/**
 * Calificación textual de una nota numérica.
 *  match(true) evalúa cada condición en orden y devuelve el valor de la primera que es true.
 */
function calificacion(float $nota): string
{
    return match (true) {
        $nota < 5 => 'Insuficiente',
        $nota < 6 => 'Suficiente',
        $nota < 7 => 'Bien',
        $nota < 9 => 'Notable',
        default   => 'Sobresaliente',
    };
}

/**
 * Procesa el texto de la carga masiva. Cada línea: Nombre;nota1;nota2;nota3
 *  - las líneas vacías o que empiezan por # se ignoran
 *  - las líneas correctas se añaden al cuaderno (o lo actualizan)
 *  - devuelve el número de alumnos cargados; los errores se añaden indicando el nº de línea
 */
// TODO (paso 4): function procesarCargaMasiva(string $texto, array &$cuaderno, array &$errores): int

/**
 * Devuelve el cuaderno ordenado según el criterio indicado (no modifica el original).
 *  - 'nombre'   : alfabético por nombre
 *  - 'media'    : de mayor a menor media
 */
function ordenarCuaderno(array $cuaderno, string $criterio): array
{
    if ($criterio === 'media') {
        // 1) array nombre => media   2) arsort lo ordena por valor de mayor a menor MANTENIENDO las claves
        $medias = [];
        foreach ($cuaderno as $nombre => $notas) {
            $medias[$nombre] = mediaPonderada($notas);
        }
        arsort($medias);
        // 3) reconstruimos el cuaderno en ese orden
        $ordenado = [];
        foreach ($medias as $nombre => $media) {
            $ordenado[$nombre] = $cuaderno[$nombre];
        }
        return $ordenado;
    }

    // Por nombre. SORT_FLAG_CASE hace que no distinga mayúsculas/minúsculas.
    // (Nota: las vocales con tilde se ordenan después de la "z": para un orden correcto en
    //  español habría que usar la clase Collator de la extensión intl, que veremos más adelante)
    ksort($cuaderno, SORT_STRING | SORT_FLAG_CASE);
    return $cuaderno;
}

/**
 * Calcula las estadísticas del grupo y las devuelve en un array asociativo.
 */
// TODO (paso 4): function estadisticas(array $cuaderno): array

/**
 * Formatea un número con coma decimal y 2 decimales: 6.5 -> "6,50"
 */
function formatoNota(float $nota): string
{
    return number_format($nota, 2, ',', '.');
}

/**
 * Atajo para escapar texto que se escribe en el HTML (en contenido o en atributos).
 */
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
