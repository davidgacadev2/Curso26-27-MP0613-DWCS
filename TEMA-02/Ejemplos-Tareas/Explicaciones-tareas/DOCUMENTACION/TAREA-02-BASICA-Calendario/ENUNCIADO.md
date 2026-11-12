# TAREA-02 (nivel BÁSICO): Calendario mensual con festivos

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

En la secretaría del instituto necesitan consultar rápidamente cuántos días lectivos tiene cada mes y qué
festivos caen en él. **Carlos** se ofrece a programar una pequeña página en PHP que muestre el calendario de
cualquier mes, señalando los fines de semana, los festivos y el día de hoy.

## Enunciado

Programa una aplicación web en PHP, en **una única página** (`index.php`) que se llama a sí misma usando el
método **GET**, que muestre el calendario de un mes.

### Requisitos

- **R1. Formulario.** En la parte superior habrá un formulario con:
  - una lista desplegable (`<select>`) con los 12 meses (se muestra el nombre y se envía el número);
  - un campo numérico para el año;
  - un botón *Mostrar* y un enlace *Hoy* que vuelve al mes actual.

  La primera vez que se carga la página (sin parámetros en la URL) se mostrará el **mes actual**.
- **R2. Validación en el servidor.** Aunque uses atributos HTML5 (`min`, `max`...), el script debe comprobar en
  PHP que:
  - el mes es un entero entre 1 y 12;
  - el año es un entero entre 1900 y 2100.

  Si hay errores, se mostrará una lista con **todos** los errores encontrados (no solo el primero) y no se dibujará
  el calendario. Comprueba qué pasa si escribes a mano en la URL `?mes=13&anio=hola`.
- **R3. Formulario "pegajoso".** Tras enviar, el formulario conserva los valores elegidos.
- **R4. Calendario.** Se dibujará una tabla HTML con:
  - la semana empezando en **lunes** (`Lun Mar Mié Jue Vie Sáb Dom`);
  - el número correcto de celdas vacías antes del día 1 y después del último día para completar la última semana;
  - el título del mes y el año (por ejemplo, *Mayo de 2027*).
- **R5. Cálculo de días.** El número de días del mes lo calcularás con una función propia `diasEnMes($mes, $anio)`
  que use una estructura `switch` y otra función `esBisiesto($anio)`.
  **No se permite** usar `date('t')` ni `cal_days_in_month()`.
  Para saber en qué día de la semana cae el día 1 **sí** puedes usar `date('N', mktime(...))`.
- **R6. Resaltado.** Con clases CSS se distinguirán:
  - los sábados y domingos;
  - los festivos (al pasar el ratón por encima se verá su nombre: atributo `title`);
  - el día de hoy.
- **R7. Festivos.** Los festivos de fecha fija se guardarán en un **array asociativo** en un fichero aparte
  `festivos.php`, con claves en formato `'mm-dd'` (por ejemplo `'05-17' => 'Día das Letras Galegas'`). El fichero
  hará `return` del array, y en `index.php` lo cargarás con `$festivos = require 'festivos.php';`.
- **R8. Navegación.** Encima del calendario aparecerán dos enlaces, *« mes anterior* y *mes siguiente »*, que
  pasan el mes y el año por la URL. Fíjate en el cambio de año (diciembre → enero y enero → diciembre). No deben
  aparecer enlaces que lleven fuera del rango 1900–2100.
- **R9. Resumen.** Debajo del calendario se mostrará:
  - el número de días del mes;
  - el número de **días laborables** (lunes a viernes que no son festivo);
  - la lista de festivos del mes, o un mensaje si no tiene ninguno.
- **R10. Organización del código.** Todas las funciones irán en `funciones.php`, que se incluirá con `require_once`.
  Las funciones deben **recibir parámetros y devolver valores**; no se permite usar `global`.

### Restricciones

- No se permite usar `$_SESSION`, cookies ni bases de datos.
- Todo dato que provenga del usuario y se vuelva a escribir en la página debe pasar por `htmlspecialchars()`.

### Pistas

- `mktime(0, 0, 0, $mes, 1, $anio)` devuelve la marca de tiempo del día 1 del mes, y `date('N', $marca)` el día de
  la semana (1 = lunes … 7 = domingo).
- `filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]])` devuelve el entero
  o `false`.
- Una función puede devolver dos valores dentro de un array: `return [$mes, $anio];`, y se recogen con
  `[$m, $a] = mesSiguiente($mes, $anio);`.
- `sprintf('%02d-%02d', $mes, $dia)` construye la clave `'05-07'`.

### Ampliaciones opcionales (no puntúan, pero se valoran)

- Añadir los festivos móviles **Jueves Santo** y **Viernes Santo** usando `easter_date($anio)` (extensión
  *calendar*).
- Mostrar el número de semana ISO (`date('W', ...)`) en una primera columna.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Estructura correcta de la página y del formulario GET (mes actual por defecto, botón Hoy). | 1,00 |
| 2. Validación en el servidor de mes y año, mostrando todos los errores. | 1,00 |
| 3. Formulario pegajoso y uso de `htmlspecialchars()`. | 0,50 |
| 4. Funciones `esBisiesto()` y `diasEnMes()` con `switch`, correctas. | 1,00 |
| 5. Tabla del calendario empezando en lunes, con celdas vacías inicial y final correctas. | 1,50 |
| 6. Resaltado de fines de semana, festivos (con `title`) y día de hoy. | 1,00 |
| 7. Array asociativo de festivos en fichero aparte devuelto con `return`. | 0,75 |
| 8. Navegación mes anterior / siguiente, con cambio de año y límites. | 1,00 |
| 9. Resumen: días, laborables y lista de festivos del mes. | 1,00 |
| 10. Organización en ficheros, funciones con parámetros y retorno, sin `global`. | 0,75 |
| 11. Comentarios en el código y diseño cuidado de la página. | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

En la carpeta [`SOLUCION-01`](SOLUCION-01/) tienes una propuesta de solución. Estúdiala ejecutándola paso a paso
con Xdebug, en especial la función `generarCalendario()`: observa cómo la variable `$columna` controla cuándo se
cierra cada fila `<tr>`.
