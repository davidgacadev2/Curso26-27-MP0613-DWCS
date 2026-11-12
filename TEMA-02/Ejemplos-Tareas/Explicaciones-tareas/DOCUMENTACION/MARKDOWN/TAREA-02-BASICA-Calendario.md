# Calendario mensual con festivos (TAREA-02, nivel básico): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-BASICA-Calendario/ENUNCIADO.md) ·
[requisitos](../TAREA-02-BASICA-Calendario/Requisitos.md) ·
[solución](../TAREA-02-BASICA-Calendario/SOLUCION-01/) ·
[paso a paso](../TAREA-02-BASICA-Calendario/SOLUCION%20PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> En la secretaría del instituto necesitan saber rápidamente cuántos días lectivos tiene cada mes y qué festivos
> caen en él. **Carlos** se ofrece a hacer una página en PHP que muestre el calendario de cualquier mes. "Es una
> tabla de siete columnas, en una tarde está", le dice a **María**. Ella coge un calendario de papel y le pregunta:
> "¿Cuántas celdas vacías hay antes del 1 de mayo de 2027? ¿Y cuántas filas tiene febrero de ese año?". Carlos no
> lo sabe. "Pues eso es lo que tiene que calcular tu programa", sonríe María, "y tienes que saber la respuesta
> **antes** de programarlo, o no sabrás si está bien".

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "SOLUCION PASO A PASO/paso2" "SOLUCION PASO A PASO/paso3"
```

(desde la carpeta `TAREA-02-BASICA-Calendario`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar
seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 10 requisitos (R1…R10) que [Requisitos.md](../TAREA-02-BASICA-Calendario/Requisitos.md)
convierte en 10 requisitos funcionales comprobables. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Pedir el mes | R1, R2, R3 · RF-01…RF-05 | Formulario GET con `<select>` de meses y año; sin parámetros, el mes actual; validar **en el servidor** y mostrar **todos** los errores; formulario pegajoso. |
| Dibujar el calendario | R4, R5, R6 · RF-06, RF-07 | Tabla que empieza en lunes, con celdas vacías al principio y al final; días del mes calculados **a mano**; clases para fin de semana, festivo y hoy. |
| Festivos y resumen | R7, R9 · RF-08, RF-10 | Un array `'mm-dd' => nombre` en un fichero que hace `return`; contar laborables y listar los festivos del mes. |
| Navegar | R8 · RF-09 | Enlaces al mes anterior y al siguiente, con cambio de año y sin salir de 1900–2100. |

Los requisitos no funcionales que más condicionan el código son tres: **prohibido `date('t')` y
`cal_days_in_month()`** (RNF-04: hay que escribir `esBisiesto()` y `diasEnMes()`), **funciones que reciben
parámetros y devuelven valores, sin `global`** (RNF-07: también las que generan HTML, que lo devuelven como cadena) y
**ningún aviso de PHP con ninguna entrada** (RNF-12), incluidas las URL escritas a mano.

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

Al leer con calma aparecen preguntas. Conviene escribir la respuesta **antes** de programar:

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Qué pasa si llega solo uno de los dos datos (`?mes=5`)? | Si llega alguno, los dos son obligatorios (RF-04): sale el error del año. |
| ¿Qué muestra el formulario si el mes enviado no vale (`mes=13`)? | El año se devuelve tal como llegó; el mes no se puede seleccionar (no existe la opción 13) y el desplegable queda en el primero. |
| ¿Un festivo que cae en domingo cuenta? | Se marca con las dos clases (`finde festivo`); para los laborables da igual: ya no lo era por ser domingo. |
| En los límites, ¿enlace deshabilitado u oculto? | Oculto: en enero de 1900 no hay enlace al mes anterior y en diciembre de 2100, al siguiente. |
| ¿Qué es "hoy"? | La fecha del **servidor** (`date('Y-n-j')`), con su zona horaria. |
| ¿Y los festivos móviles (Semana Santa)? | Fuera de alcance: son una ampliación opcional. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). Así quien
> corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de la tarea.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son ejemplos concretos de entrada y salida. Escríbelos en una tabla
antes de programar, comprobando las respuestas con un calendario de papel: serán las pruebas de cada paso. Los
casos 7 y 8 no están en los requisitos; se añaden porque son los **límites** del algoritmo de la tabla.

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | `index.php` sin parámetros | Mes actual, con hoy resaltado y ese mes y año en el formulario |
| 2 | `?mes=13&anio=hola` | Dos errores (mes y año) y ningún calendario |
| 3 | `?mes=5` | "El campo «Año» es obligatorio." |
| 4 | `?mes=5&anio=1899` | Error del año: "debe ser un número entero entre 1900 y 2100" |
| 5 | Mayo de 2027 | Empieza en sábado (5 celdas vacías); 31 días, 20 laborables; festivos el 1 y el 17, con *Día das Letras Galegas* al pasar el ratón |
| 6 | Febrero de 2024 / febrero de 2100 | 29 días / 28 días (2100 es divisible por 100 y no por 400) |
| 7 | Febrero de 2027 | Empieza en lunes y tiene 28 días: **4 filas exactas**, sin ninguna celda vacía |
| 8 | Agosto de 2027 | Empieza en domingo: 6 celdas vacías antes del día 1 |
| 9 | Diciembre de 2026 / enero de 1900 | Enlace siguiente a *Enero 2027* / ningún enlace al mes anterior |
| 10 | Marzo de 2027 | "Este mes no tiene festivos de fecha fija." |
| 11 | `?mes=5&anio=<b>hola</b>` | Error del año y, en el código fuente (Ctrl+U), el valor del campo escapado: `&lt;b&gt;hola&lt;/b&gt;` |

### 1.4.- Los datos: qué se guarda y dónde

En esta tarea **no hay estado que conservar**: cada petición trae en la URL todo lo que necesita (`?mes=5&anio=2027`).
HTTP no tiene estado, pero aquí no importa, porque cada página es independiente de la anterior; por eso los
enlaces de navegación son simples `<a href>` y cualquier mes se puede guardar en marcadores.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Mes y año pedidos | int (o `null` si no valen) | `$_GET`, o `date('n')` y `date('Y')` | Lo único que entra |
| Errores | array de strings | variable `$errores` | Hay que mostrar **todos** (R2) |
| Festivos | array `'mm-dd' => nombre` | `festivos.php` (`return [...]`) | Datos fijos, separados del código (R7) |
| Límites, nombres de meses y días | constantes | `funciones.php` | Valores fijos con nombre (RNF-08) |
| Días del mes, día 1, laborables, festivos del mes | — | **se calculan** | Dependen solo de mes y año |

La clave `'mm-dd'` **no lleva año**: así el mismo array vale para cualquier año, que es lo que define un festivo de
fecha fija. Y con ceros a la izquierda (`'05-07'`, no `'5-7'`), que es lo que construye `sprintf('%02d-%02d', ...)`.

> **Debes conocer**
>
> **Un fichero que hace `return`.** `festivos.php` no crea ninguna variable: devuelve el array, y quien lo carga
> elige el nombre (`$festivos = require 'festivos.php';`). Así no aparecen variables "escondidas" que vienen de un
> `include` y nadie sabe de dónde salen, y las funciones que lo necesitan lo reciben **como parámetro**, sin `global`.

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Cuatro ficheros con una responsabilidad cada uno: `festivos.php` (datos), `funciones.php` (constantes y lógica),
`index.php` (decide qué hacer y muestra la página) y `estilos.css`. `index.php` sigue la estructura de siempre de
los apuntes:

```text
1. Inicializar          -> require_once funciones.php, cargar festivos, $errores vacío
2. Recibir y validar    -> ¿llegan mes o año? validarlos; si no, el mes actual
3. Preparar el formulario -> valores del formulario pegajoso
4. Mostrar la página    -> formulario; si hay errores, la lista; si no, navegación + calendario + resumen
```

En la solución, los cálculos de la navegación y del resumen (la fase de *procesar*) se hacen dentro del `else` de
la vista, justo antes de escribirlos. Como son llamadas a funciones que devuelven valores, la vista sigue siendo
fácil de leer; aun así, se podrían calcular antes del `<!DOCTYPE html>` (apartado 5).

Cada cálculo es una función pequeña con un verbo (o una pregunta) en el nombre, que **recibe** lo que necesita y
**devuelve** un resultado:

| Función | Responsabilidad | Paso |
|---|---|---|
| `validarEntero()` | ¿Es un entero del rango? Devuelve el entero o `null` y apunta el error | 2 |
| `esBisiesto()` / `diasEnMes()` | Regla del bisiesto / días del mes con `switch` | 3 |
| `primerDiaSemana()` | Día de la semana del día 1 (1 = lunes … 7 = domingo) | 3 |
| `claveFestivo()` | La clave `'mm-dd'` de un día | 3 |
| `generarCalendario()` | El HTML de la tabla, como cadena | 3 |
| `mesAnterior()` / `mesSiguiente()` | `[mes, año]` de destino, con cambio de año | 4 |
| `festivosDelMes()` / `contarLaborables()` | Festivos del mes ordenados / lunes a viernes no festivos | 4 |

> **Recomendación**
>
> Fíjate en `validarEntero(?string $valor, int $min, int $max, string $campo, array &$errores): ?int`. Devuelve
> el número, y el error lo **añade** al array que recibe por referencia (`&$errores`). Así, llamándola dos veces
> (mes y año), se acumulan todos los errores, que es lo que pide R2. La firma documenta las dos salidas: el valor de
> retorno y el parámetro que se modifica.

### 2.2.- Estados de la página

No hay estado guardado, pero la página sí tiene tres "pantallas", y lo que llega en la URL decide cuál se muestra
(*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Cualquiera | abrir la página o pulsar *Hoy* [sin `mes` ni `anio`] | mes y año actuales | Calendario |
| Cualquiera | *Mostrar* o enlace de navegación [mes y año válidos] | calcular y dibujar | Calendario |
| Cualquiera | *Mostrar* o URL a mano [falta uno o alguno no vale] | lista con todos los errores | Errores (sin calendario) |
| Calendario | *« anterior* [año de destino ≥ 1900] | mes anterior (de enero, a diciembre del año anterior) | Calendario |
| Calendario | *siguiente »* [año de destino ≤ 2100] | mes siguiente (de diciembre, a enero del año siguiente) | Calendario |

Las dos últimas filas tienen condición porque el enlace **solo se escribe** si la cumple. La tercera es la que
obliga a que el calendario y el resumen vayan dentro de un `else`: con errores, `$mes` o `$anio` valen `null` y no
se puede calcular nada.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` de `SOLUCION PASO A PASO` es la aplicación completa al terminar ese paso, y todas se ejecutan.
El orden no es casual: primero lo que **entra** (sin datos válidos no se puede calcular nada), después lo que se
**ve** (la tabla) y al final lo que se apoya en todo lo anterior.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs en los tres ficheros PHP | — |
| 2 | Constantes, festivos, formulario, lectura y validación | R1, R2, R3, R7 |
| 3 | Bisiestos, días del mes y la tabla del calendario | R4, R5, R6 |
| **4** | **Solución completa:** navegación entre meses y resumen | R8, R9 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué empezar así.** El HTML de esta tarea es pequeño (un formulario y una tabla que se genera entera), así que
no hay un paso de maqueta aparte: el paso 1 ya reparte el trabajo entre los ficheros. En `index.php`, un TODO por
cada parte de la estructura de los apuntes; en `funciones.php`, la **firma** de cada función con lo que recibe y lo
que devuelve:

```php
// Función auxiliar TODO: validarEntero(?string $valor, int $min, int $max, string $campo, array &$errores): ?int
//      devuelve el entero o null, añadiendo el mensaje de error a $errores (paso POR REFERENCIA)
```

Y en `festivos.php`, el formato de los datos: `// TODO: return [ '01-01' => 'Año Nuevo', ... ];`.

**Decisión clave.** Ya en el paso 1, `funciones.php` lleva un comentario que explica por qué **no se cierra** la
etiqueta `?>`: un fichero que solo tiene PHP no debe enviar al navegador espacios o saltos de línea accidentales.

> **Pruébalo**
>
> Abre la página: sale el título *Calendario mensual* y nada más, pero **no hay errores de PHP**. Con la extensión
> *Todo Tree* de VS Code verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa se
> borra.

### 3.2.- Paso 2: el formulario, la validación y los festivos

**Objetivo.** Formulario GET con el mes actual por defecto, validación en el servidor con todos los errores y
formulario pegajoso (R1, R2, R3). También se rellenan los datos: constantes y `festivos.php` (R7).

**Por qué ahora.** Todo lo demás necesita un mes y un año **enteros y válidos**. Además, el `<select>` necesita
`NOMBRES_MESES`, así que las constantes se escriben ya. Los TODO que quedan pasan a llevar el paso en que se harán:
`// TODO (paso 3): function esBisiesto(int $anio): bool`.

**Qué se escribe.**

- Si llega `mes` **o** `anio`, se validan los dos; si no llega ninguno, se usa el mes actual. El `?? null` evita
  el aviso *Undefined array key* cuando falta uno:

```php
if (isset($_GET['mes']) || isset($_GET['anio'])) {
    $mes  = validarEntero($_GET['mes'] ?? null, 1, 12, 'Mes', $errores);
    $anio = validarEntero($_GET['anio'] ?? null, ANIO_MIN, ANIO_MAX, 'Año', $errores);
} else {
```

- `validarEntero()` hace `trim()`, distingue **vacío** ("El campo «Año» es obligatorio.") de **no válido** ("…debe
  ser un número entero entre 1900 y 2100.") y usa `filter_var()` con `FILTER_VALIDATE_INT` y
  `min_range`/`max_range`: una sola llamada comprueba que es un entero y que está en el rango. `'hola'`, `'5.5'` y
  `'13'` dan `false`.
- `NOMBRES_MESES` empieza con `1 => 'Enero'`, y las demás claves siguen en 2, 3… Así `NOMBRES_MESES[$mes]` funciona
  directamente con el número del mes.
- El formulario pegajoso usa los valores válidos o, si no lo eran, lo que llegó:
  `$anioFormulario = $anio ?? ($_GET['anio'] ?? '');`, que se escribe con `htmlspecialchars()`. También
  `$_SERVER['PHP_SELF']` pasa por `htmlspecialchars()` en el `action` y en el enlace *Hoy*.
- Si hay errores se muestran todos en una lista; si no, de momento no se dibuja nada: el `else` solo tiene los TODO
  de los pasos 3 y 4.

> **Error típico**
>
> Fiarse de `min="1900" max="2100"` del `<input type="number">`. Eso solo lo comprueba el navegador; escribiendo la
> URL a mano (`?mes=13&anio=hola`) llega cualquier cosa. La validación del navegador es una ayuda para el usuario,
> no una medida de seguridad: el servidor tiene que validar **siempre** (RNF-10).

> **Pruébalo**
>
> Casos 2, 3, 4 y 11. Con `?mes=13&anio=hola` deben salir **dos** errores, no uno. Con `?mes=5&anio=2027` no sale
> ningún error y tampoco calendario (todavía no existe), pero el desplegable muestra *Mayo* y el año *2027*. Pon un
> punto de ruptura en `validarEntero()` y mira cómo crece `$errores` en la segunda llamada.

### 3.3.- Paso 3: los días del mes y la tabla

**Objetivo.** Calcular los días del mes sin `date('t')` y dibujar la tabla que empieza en lunes, con los
resaltados (R4, R5, R6).

**Por qué ahora.** Con mes y año válidos ya se puede calcular. Es la parte con más trampas, y conviene tenerla bien
probada antes de añadir el resumen, que reutiliza las mismas funciones.

**Qué se escribe.**

- `esBisiesto()`: `($anio % 4 == 0 && $anio % 100 != 0) || $anio % 400 == 0`.
- `diasEnMes()` con `switch`: el caso 2 pregunta a `esBisiesto()`; los casos 4, 6, 9 y 11 van **seguidos sin
  `return` entre ellos** (todos caen en el mismo `return 30;`); el `default` devuelve 31.
- `primerDiaSemana()`: `date('N', mktime(0, 0, 0, $mes, 1, $anio))`, que da 1 para el lunes y 7 para el domingo,
  justo el orden de la tabla. Las celdas vacías iniciales son ese número menos 1.
- `generarCalendario()` en tres partes: celdas vacías iniciales, días del mes y celdas vacías finales. La variable
  `$columna` (0 = lunes … 6 = domingo) controla cuándo se cierra cada fila:

```php
// Si llegamos al domingo cerramos la fila (y abrimos otra si quedan días)
$columna++;
if ($columna == 7) {
    $html .= "</tr>\n";
    if ($dia < $totalDias) {
        $html .= "<tr>";
    }
    $columna = 0;
}
```

El `if ($dia < $totalDias)` es el detalle fino: si el último día cae en domingo, no se abre una fila que se quedaría
vacía; y como `$columna` vuelve a 0, tampoco se añaden celdas finales (`if ($columna != 0)`). Es justo el caso 7.

- Los resaltados son una lista de clases que se unen con `implode(' ', $clases)`: `finde` si `$columna >= 5`,
  `festivo` si `array_key_exists(claveFestivo($mes, $dia), $festivos)` y `hoy` si `"{$anio}-{$mes}-{$dia}"` coincide
  con `date('Y-n-j')`. Un día puede tener varias a la vez.
- El nombre del festivo va en `title='...'`, escapado con `htmlspecialchars(..., ENT_QUOTES)`: el atributo usa
  comillas simples, y un festivo con apóstrofo lo cerraría antes de tiempo.

> **Error típico**
>
> Usar `date('w')` en lugar de `date('N')`: `'w'` da 0 para el **domingo**, así que en los meses que empiezan en
> domingo saldrían -1 celdas vacías (ninguna) y el día 1 aparecería en la columna del lunes. Otro clásico: comparar la fecha de hoy con `date('Y-m-d')` (con ceros,
> `2027-05-07`) contra una cadena construida con enteros (`2027-5-7`). Por eso la solución usa `'Y-n-j'`, sin ceros.

> **Pruébalo**
>
> Casos 5, 6, 7 y 8. Mayo de 2027 debe tener 5 celdas vacías antes del día 1, febrero de 2027 exactamente cuatro
> filas y agosto de 2027 seis celdas vacías. Pasa el ratón por el 17 de mayo. Como propone el enunciado, ejecuta
> `generarCalendario()` paso a paso con Xdebug y vigila `$columna`: es la mejor forma de entender cuándo se cierra
> cada `<tr>`. Mira también el código fuente (Ctrl+U): el HTML de la tabla tiene que estar bien anidado.

### 3.4.- Paso 4 (solución completa): navegación y resumen

**Objetivo.** Enlaces al mes anterior y al siguiente (R8) y el resumen del mes (R9).

**Por qué al final.** Son funciones que se apoyan en todo lo anterior: `contarLaborables()` usa `diasEnMes()`,
`primerDiaSemana()` y `claveFestivo()`; la navegación necesita un mes y un año ya validados.

**Qué se escribe.**

- `mesAnterior()` y `mesSiguiente()` devuelven **dos valores en un array**, y `index.php` los recoge con
  desestructuración: `[$mesSig, $anioSig] = mesSiguiente($mes, $anio);`. El cambio de año está en la propia
  función: `$mes == 12 ? [1, $anio + 1] : [$mes + 1, $anio]`.
- Cada enlace se escribe solo si el año de destino está dentro del rango: `if ($anioAnt >= ANIO_MIN)`. Cuando no
  hay enlace anterior se escribe un `<span></span>` vacío para que el enlace siguiente siga a la derecha (la
  navegación es un `display: flex` con `space-between`). En la URL, el `&` se escribe `&amp;`.
- `festivosDelMes()` parte cada clave con `explode('-', $clave)`, convierte las dos partes a entero y se queda con
  las del mes; `ksort()` las ordena por día, aunque en el fichero estuvieran desordenadas.
- `contarLaborables()` recorre los días llevando el día de la semana, que avanza con `$diaSemana % 7 + 1`
  (del 7, domingo, vuelve al 1, lunes); cuenta los que son `<= 5` y no son festivo.

> **Debes conocer**
>
> **Cada decisión del análisis simplifica un paso posterior.** Como los festivos tienen la clave `'mm-dd'` y existe
> `claveFestivo()`, comprobar si un día es festivo es lo mismo en la tabla (paso 3) que en los laborables (paso 4):
> un `array_key_exists()`. Y como el array no lleva año, la navegación a cualquier año no necesita ningún dato nuevo.

> **Pruébalo**
>
> Casos 9 y 10. Desde diciembre de 2026, el enlace siguiente debe decir *Enero 2027*; en enero de 1900 no debe haber
> enlace anterior, y en diciembre de 2100, ninguno siguiente. Mayo de 2027: 31 días y 20 laborables (el 1 de mayo cae
> en sábado y no resta). Diciembre de 2026: festivos el 6, el 8 y el 25, en ese orden.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 4 añade código dentro del mismo `else` que dibuja el calendario y podría haber estropeado algo que antes
funcionaba. Una lista de comprobación para esta tarea:

- [ ] Casos correctos: varios meses de distintos años, el mes actual y el enlace *Hoy* desde otro mes.
- [ ] Casos límite: meses que empiezan en lunes y en domingo; meses que terminan en domingo; febrero en años
      bisiestos (2024, 2000) y no bisiestos (2100, 1900); enero de 1900 y diciembre de 2100.
- [ ] Errores: falta un dato, los dos datos mal, decimales (`5.5`), espacios, valores fuera de rango.
- [ ] Maliciosos: HTML en `anio`; arrays donde se espera un texto (`?mes[]=1&anio=2027`).
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)` y el HTML de la tabla válido.

Una de estas líneas **falla**: la de los arrays (ver el apartado siguiente).

## 5.- Refactorización y mejoras posibles

La solución ya está organizada en funciones cortas y no tiene FIXME pendientes. Aun así, quedan detalles que se
podrían mejorar, buenos para practicar:

- **Un array donde se espera un texto.** Con `?mes[]=1&anio=2027`, `$_GET['mes']` es un array, y
  `validarEntero()` declara `?string $valor`: PHP lanza un `TypeError` y la página termina con un error fatal, lo
  que contradice RNF-12. Se arregla comprobando `is_string()` antes de llamarla (o aceptando `mixed` y
  comprobándolo dentro), como se hace en los apuntes.
- **Lógica repetida.** "Es fin de semana" se decide dos veces y de dos formas: `$columna >= 5` en
  `generarCalendario()` y `$diaSemana <= 5` en `contarLaborables()`. Una función `esLaborable()` o un único recorrido
  de los días evitaría que un cambio (por ejemplo, que el sábado sea laborable) se haga solo en un sitio.
- **Una función más fácil de probar.** `generarCalendario()` llama a `date('Y-n-j')` por dentro, así que su
  resultado cambia según el día en que se ejecuta. Si recibiera la fecha de hoy como parámetro, se podría probar el
  resaltado de hoy con cualquier fecha.
- **Procesar antes de mostrar.** Calcular `mesAnterior()`, `mesSiguiente()`, `festivosDelMes()` y
  `contarLaborables()` en la parte de arriba de `index.php`, guardando los resultados en variables, y dejar en el
  HTML solo los `echo`. Es la estructura de siempre de los apuntes y hace que la vista no llame a la lógica.
- **El mes en el formulario pegajoso.** Con `mes=13` no hay ninguna opción seleccionada y el navegador muestra
  *Enero*, que no es lo que se envió. Se podría añadir una opción vacía *(elige un mes)* seleccionada en ese caso.
- **Las ampliaciones** (Jueves y Viernes Santo con `easter_date()`, número de semana ISO con `date('W', ...)`) son la
  siguiente **vuelta** del proceso: cada una empieza otra vez por el análisis. Por ejemplo, los festivos móviles
  dependen del año, así que ya no caben en un array `'mm-dd'` fijo: habría que calcularlos y añadirlos al array del
  año pedido.

> **Para saber más**
>
> "Hoy" es la fecha del **servidor**: si en `php.ini` no está configurada `date.timezone` (por ejemplo,
> `Europe/Madrid`), PHP usa UTC, y entre las 00:00 y la 01:00 o las 02:00 de la hora española el día resaltado sería
> todavía el de ayer. Se puede fijar en el código con `date_default_timezone_set('Europe/Madrid');`.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Validar solo con `min`/`max` del HTML | `?mes=13` llega igual | Validar en PHP con `filter_var()` |
| Parar en el primer error | Solo se ve un error de dos | Acumular en `$errores` y mostrarlos todos |
| `date('w')` en lugar de `date('N')` | Los meses que empiezan en domingo salen empezando en lunes | `'N'`: 1 = lunes … 7 = domingo |
| Olvidar el `% 100` o el `% 400` del bisiesto | Febrero de 2100 con 29 días | `(% 4 y no % 100) o % 400` |
| Abrir siempre un `<tr>` al cerrar el domingo | Fila vacía al final de febrero de 2027 | `if ($dia < $totalDias)` |
| Claves de festivos sin ceros (`'5-1'`) | Ningún festivo coincide | `sprintf('%02d-%02d', ...)` |
| `$mes - 1` y `$mes + 1` sin más | Enlaces a los meses 0 y 13 | `mesAnterior()` / `mesSiguiente()` con cambio de año |
| `echo` dentro de las funciones y `global $festivos` | Funciones que no se pueden reutilizar ni probar | Recibir parámetros y devolver el HTML como cadena |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué esta tarea no necesita campos `hidden`? **Porque cada petición lleva en la URL todo lo que necesita
>   (mes y año): no hay nada que recordar de una petición a la siguiente.**
> - ¿Por qué `validarEntero()` recibe `$errores` por referencia? **Para añadir el mensaje al array del que llama y
>   poder acumular los errores del mes y del año.**
> - ¿Cuántas celdas vacías hay antes del día 1 si `date('N', ...)` devuelve 6? **5: el día 1 cae en sábado.**
> - ¿Es bisiesto el año 2100? ¿Y el 2000? **2100 no (divisible por 100 y no por 400); 2000 sí (divisible por
>   400).**
> - ¿Por qué las claves de los festivos no llevan el año? **Porque son festivos de fecha fija: el mismo array vale
>   para cualquier año.**
> - ¿Qué pasa en `generarCalendario()` si el último día del mes cae en domingo? **Se cierra la fila, no se abre
>   otra porque `$dia` ya es el último, y como `$columna` vuelve a 0 no se añaden celdas vacías finales.**
