# Cuaderno de notas del profesor (TAREA-02, nivel medio): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-MEDIA-CuadernoNotas/ENUNCIADO.md) ·
[requisitos](../TAREA-02-MEDIA-CuadernoNotas/Requisitos.md) ·
[datos de prueba](../TAREA-02-MEDIA-CuadernoNotas/datos-prueba.txt) ·
[solución](../TAREA-02-MEDIA-CuadernoNotas/SOLUCION-01/) ·
[paso a paso](../TAREA-02-MEDIA-CuadernoNotas/SOLUCION%20PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> **María** lleva las notas de sus grupos en una hoja de papel y le pide a **Carlos** una pequeña aplicación que
> calcule las medias y las estadísticas. Carlos abre el editor dispuesto a empezar por la tabla, pero se para en
> seco: "Si añado a Ana y luego a Brais, ¿dónde está Ana cuando llega la segunda petición?". Todavía no saben usar
> sesiones ni bases de datos, así que el cuaderno tendrá que **viajar en la propia página**, en campos ocultos. Y
> **Juan**, que pasa por allí, le recuerda lo que eso significa: "Lo que viaja en la página lo puede cambiar
> cualquiera con F12. Cuando vuelva, valídalo otra vez".

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "SOLUCION PASO A PASO/paso2" "SOLUCION PASO A PASO/paso3"
```

(desde la carpeta de la tarea, `TAREA-02-MEDIA-CuadernoNotas`; en VS Code: seleccionar los dos ficheros, botón
derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 10 requisitos (R1…R10) que [Requisitos.md](../TAREA-02-MEDIA-CuadernoNotas/Requisitos.md)
convierte en 11 requisitos funcionales comprobables. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Alta y validación | R1, R2 · RF-01…RF-03 | Normalizar el nombre con funciones `mb_`, validar nombre y notas (coma o punto), **todos** los errores a la vez y formulario pegajoso. |
| Mostrar | R3, R6 · RF-04, RF-08 | Media ponderada, calificación, resaltado de suspensos, estadísticas y un gráfico de barras con `<div>`. |
| Actuar sobre el cuaderno | R4, R5, R8 · RF-05…RF-07, RF-10 | Botones *Editar* y *Eliminar* por fila, ordenar con lista blanca y vaciar con confirmación. |
| Carga masiva | R7 · RF-09 | Partir un texto en líneas y en campos, e informar de cada error con su **número de línea**. |
| Conservar el estado | R9 · RF-11, RNF-03, RNF-11, RNF-14 | El cuaderno viaja en campos `hidden` con la forma `alumnos[Nombre][0..2]` y se **revalida** al llegar. |
| Organización | R10 · RNF-06…RNF-08 | Funciones sin `echo` en `funciones.php`, paso por referencia y constantes con `const`. |

Los requisitos no funcionales que más condicionan el código son tres: **el estado en campos ocultos** (RNF-03 y
RNF-04: nada de `$_SESSION`, cookies ni ficheros), **no fiarse de esos campos** (RNF-11) y **un único formulario**
que envuelve toda la página (RNF-16), para que cualquier botón envíe también el cuaderno.

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

Al leer con calma aparecen preguntas. Conviene escribir la respuesta **antes** de programar:

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿`ana pérez` y `Ana Pérez` son el mismo alumno? | Sí: el nombre se **normaliza** antes de usarlo como clave, así que se actualiza en lugar de duplicarse. |
| ¿Se puede cambiar el nombre al editar? | No: guardar con otro nombre crea un alumno nuevo. Renombrar es una ampliación (campo oculto `nombreOriginal`). |
| ¿Con cuántos decimales se guardan las notas? | `validarNota()` las redondea a 2; la media también se redondea a 2. |
| ¿Qué pasa si un campo oculto llega manipulado? | `recuperarCuaderno()` descarta ese alumno **en silencio** (sus errores van a un array que no se muestra). |
| ¿Qué número de línea se da en la carga masiva? | El del texto pegado, contando también las líneas vacías y los comentarios: coincide con el de `datos-prueba.txt`. |
| ¿Qué botón "pulsa" la tecla *Intro*? | *Guardar*: un botón invisible al principio del formulario. |
| ¿Cómo se ordenan los nombres con tilde? | Con `ksort()`: las vocales con tilde quedan después de la "z". Ordenar bien en español (`Collator`) es una ampliación. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). Así quien
> corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de la tarea. En esta solución, la nota
> sobre las tildes y `Collator` está escrita como comentario dentro de `ordenarCuaderno()`.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` y el fichero `datos-prueba.txt` son ejemplos concretos de entrada y
salida. Escríbelos en una tabla antes de programar: serán las pruebas de cada paso.

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Guardar `brais   OTERO` con `4`, `5,5`, `3` | "Se ha añadido a «Brais Otero»."; en la tabla, *Brais Otero*; el formulario queda vacío |
| 2 | Añadir 3 alumnos uno tras otro | Los 3 siguen en la tabla (RF-11) |
| 3 | Guardar `ana pérez` cuando ya existe *Ana Pérez* | "Se han actualizado las notas de «Ana Pérez»." y no hay un segundo alumno |
| 4 | Nombre `X` y notas `5`, vacío, `abc` | 3 errores y el formulario conserva lo escrito |
| 5 | Notas `6,5` y `6.5` | Las dos se aceptan como la misma nota |
| 6 | Notas `6,75`, `7`, `6` | Media 6,53 y calificación *Bien* |
| 7 | *Editar* en *Ana Pérez* | El formulario muestra su nombre y sus notas con coma decimal |
| 8 | *Vaciar* sin marcar la casilla / marcándola | Error "Para vaciar el cuaderno debes marcar la casilla de confirmación." / "Se ha vaciado el cuaderno." |
| 9 | Pegar `datos-prueba.txt` en la carga masiva | 5 alumnos cargados y 5 errores (líneas 9 a 13); media del grupo 6,46, mejor *Carmen Núñez Vázquez* (9,55), peor *Brais Otero* (4,05), 3 aprobados de 5 |
| 10 | Cambiar con F12 una nota oculta por `99` | Ese alumno desaparece del cuaderno, sin avisos |
| 11 | Cambiar con F12 el `value` de una opción del desplegable de orden | Se ordena por nombre (lista blanca) |

Los casos 10 y 11 son **maliciosos**: no se pueden provocar con el formulario normal, hay que editar el HTML con
F12. Que estén en la lista desde el principio obliga a decidir, ya en el diseño, que el cuaderno recibido **se
valida igual que un alumno nuevo**.

### 1.4.- Los datos: qué se guarda y dónde

Es la decisión más importante de la tarea. Como **HTTP no tiene estado**, cada clic en un botón es una petición
nueva: el script empieza desde cero y tiene que **reconstruir el cuaderno** a partir de lo que envíe el formulario.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Cuaderno | array `nombre => [n1, n2, n3]` (floats) | campos ocultos `alumnos[Nombre][0..2]` | Es lo único que hay que recordar (R9) |
| Criterio de orden | string (`nombre` o `media`) | el propio `<select name="orden">` | Como está dentro del único formulario, se envía en cada petición (R5) |
| Nombre y notas del formulario | strings | campos de texto | Solo hacen falta en la petición en que se envían (formulario pegajoso) |
| Texto de la carga masiva | string | `<textarea name="csv">` | Se procesa y no se conserva |
| Confirmación de vaciar | casilla | `confirmarVaciar` | Solo importa si está (`isset`) |
| Medias, calificaciones, estadísticas | — | **se calculan** | Si se guardaran, podrían contradecirse con las notas |
| Errores y mensajes | arrays | variables | Solo duran la petición |

> **Debes conocer**
>
> **PHP reconstruye arrays a partir de los nombres de los campos.** Si la página contiene
> `name='alumnos[Brais Otero][0]' value='4'`, `name='alumnos[Brais Otero][1]' value='5.5'`… entonces
> `$_POST['alumnos']` llega como `['Brais Otero' => ['4', '5.5', '3']]`: el mismo array multidimensional del
> enunciado, pero con **textos** en lugar de números. Por eso el nombre tiene que ser una clave segura (sin `[`,
> `]` ni comillas, RNF-14) y por eso las notas hay que volver a validarlas: son textos que ha enviado el navegador.

El cuaderno, dentro del programa, es exactamente el del enunciado:

```php
['Ana Pérez Lago' => [7.5, 6.0, 8.25], 'Brais Otero' => [4.0, 5.5, 3.0]]
```

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Dos ficheros PHP con una responsabilidad cada uno: `funciones.php` (lógica, sin HTML) e `index.php` (decide qué
hacer y muestra la página), más `estilos.css`. `index.php` sigue la estructura de siempre de los apuntes:

```text
1. Inicializar          -> require_once 'funciones.php'; arrays $errores y $mensajes
2. Recuperar el estado  -> recuperarCuaderno($_POST['alumnos'] ?? []), ¡validándolo!
3. Recibir              -> valores por defecto del formulario; criterio de orden (lista blanca)
4. Procesar la acción   -> eliminar / editar / guardar / cargar / vaciar / ordenar
5. Preparar la vista    -> ordenarCuaderno() y estadisticas()
6. Mostrar la página    -> mensajes, tabla, estadísticas, formularios y los campos ocultos con el estado
```

R10 pide siete funciones como mínimo; la solución añade cinco más para no repetir código:

| Función | Responsabilidad | Paso |
|---|---|---|
| `e()` | Escapar para el HTML | 2 |
| `normalizarNombre()` / `nombreValido()` | Limpiar el nombre / comprobar su formato | 2 |
| `validarNota()` | Texto → nota (`float`) o `null`, añadiendo el error a `$errores` (por referencia) | 2 |
| `validarAlumno()` | Nombre y 3 textos → `[nombreNormalizado, [n1, n2, n3]]` o `null` | 2 |
| `recuperarCuaderno()` | `$_POST['alumnos']` → cuaderno, descartando lo que no sea válido | 2 |
| `mediaPonderada()` / `calificacion()` / `formatoNota()` | Calcular la media / su texto / escribir `6,50` | 3 |
| `ordenarCuaderno()` | Devolver una copia ordenada por nombre o por media | 3 |
| `procesarCargaMasiva()` | Texto → alumnos añadidos y errores con su número de línea | 4 |
| `estadisticas()` | Cuaderno → número de alumnos, media, mejor, peor, aprobados y distribución | 4 |

> **Recomendación**
>
> La función que más se reutiliza es `validarAlumno()`: la usan el botón *Guardar* (paso 2), `recuperarCuaderno()`
> (paso 2) y `procesarCargaMasiva()` (paso 4). Hay **una sola** definición de "alumno válido", así que es imposible
> que el formulario acepte algo que luego la carga masiva rechace, o que los campos ocultos admitan una nota que el
> formulario no admitiría. Cuando una regla se aplica en varios sitios, escríbela una vez.

### 2.2.- Estados de la página

El cuaderno puede estar vacío o tener alumnos, y el botón pulsado decide la transición (*evento [condición] /
acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Cualquiera | cualquier botón [un alumno oculto no es válido] | `recuperarCuaderno()` lo descarta | igual, sin ese alumno |
| Cualquiera | *Guardar* o *Intro* [datos válidos, nombre nuevo] | añadir; "Se ha añadido a…"; vaciar el formulario | Con alumnos |
| Con alumnos | *Guardar* [datos válidos, nombre existente] | sustituir las notas; "Se han actualizado…" | Con alumnos |
| Cualquiera | *Guardar* [datos no válidos] | mostrar **todos** los errores; formulario pegajoso | igual |
| Con alumnos | *Editar* [el alumno existe] | cargar su nombre y sus notas en el formulario | Con alumnos |
| Con alumnos | *Eliminar* [el alumno existe] | `unset()`; "Se ha eliminado a…" | Con alumnos o Vacío |
| Con alumnos | *Vaciar* [casilla marcada] | cuaderno vacío; "Se ha vaciado el cuaderno." | Vacío |
| Con alumnos | *Vaciar* [casilla sin marcar] | error | Con alumnos |
| Cualquiera | *Cargar* | añadir las líneas correctas; errores con su número de línea | Con alumnos (si alguna es correcta) |
| Con alumnos | *Ordenar* [criterio en la lista blanca / fuera de ella] | ordenar por ese criterio / por nombre | Con alumnos |

Dibujar la tabla descubre dos cosas. La primera fila no depende del botón: **todas** las peticiones empiezan
reconstruyendo y revalidando el cuaderno. Y *Editar* y *Eliminar* no llevan un nombre de acción, sino **el nombre
del alumno** (`<button name="eliminar" value="Nombre del alumno">`, como sugiere el enunciado), por lo que se
comprueban con `isset()` antes del `switch` que reparte el resto de acciones.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden no es
casual: primero lo que permite **guardar y conservar** alumnos, después lo que permite **trabajar** con ellos y al
final lo que se **construye encima** de todo lo anterior.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs y esqueleto HTML | — |
| 2 | Alta y modificación con validación, formulario pegajoso y estado en campos ocultos (revalidado) | R1, R2, R9, R10 |
| 3 | Medias, calificaciones, *Editar*, *Eliminar*, *Vaciar* y *Ordenar* | R3, R4, R5, R8 |
| **4** | **Solución completa:** estadísticas y carga masiva | R6, R7 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué empezar así.** La tarea tiene seis acciones distintas que comparten el mismo estado; si se empieza a
escribir código directamente, es fácil que una de ellas "olvide" el cuaderno. Los TODO son el **plan** escrito
dentro del propio código: en `index.php`, uno por cada parte de la estructura, en el orden en que se ejecutará; en
`funciones.php`, la **firma** de cada función con lo que recibe y lo que devuelve:

```php
// Función auxiliar TODO: validarNota(?string $texto, string $campo, array &$errores): ?float
//      admite coma o punto decimal; número entre 0 y 10
```

Lo más importante del paso 1 está en un único comentario del HTML: **un único formulario** que envuelve toda la
página, con un botón *Guardar* invisible al principio y los campos ocultos al final. Es la decisión de la que
dependen todas las demás.

**Decisión clave.** La estructura del cuaderno ya se escribe en el comentario de cabecera de `funciones.php`, y el
TODO de `index.php` que lo recupera dice expresamente *¡validándolo!*: la revalidación forma parte del plan desde
el primer minuto, no es un añadido final.

> **Pruébalo**
>
> Abre la página: sale el título "Cuaderno de notas" y nada más, pero **no hay errores de PHP**. Con la extensión
> *Todo Tree* de VS Code verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa se
> borra.

### 3.2.- Paso 2: alta de alumnos y estado en campos ocultos

**Objetivo.** Validar y guardar un alumno (R1, R2) y conseguir que el cuaderno **sobreviva** de una petición a la
siguiente (R9).

**Por qué ahora (y por qué las dos cosas juntas).** Sin alta no hay alumnos con los que probar nada, pero un alta
sin estado solo sirve para un alumno: al añadir el segundo, el primero ya no existe. Por eso este paso termina con
los campos ocultos y `recuperarCuaderno()`. Y la revalidación entra también aquí, no al final: como
`validarAlumno()` ya está escrita para el botón *Guardar*, validar el cuaderno recibido es solo **llamarla otra
vez** para cada alumno.

**Qué se escribe.**

- Las constantes `EVALUACIONES`, `PESOS`, `CALIFICACIONES` y `NOTA_APROBADO`, todas de golpe: casi todas se usan
  ya en este paso, para dibujar las cabeceras (`PESOS[$i] * 100`), el formulario (una caja de texto por evaluación)
  y la clase `nota-suspensa` de las notas menores que `NOTA_APROBADO`.
- `normalizarNombre()`: `preg_replace('/\s+/u', ' ', ...)`, `trim()` y `mb_convert_case(..., MB_CASE_TITLE, 'UTF-8')`.
- `nombreValido()`: una expresión regular con `\p{L}` (cualquier letra, también con tilde o ñ) y el modificador `u`,
  que además limita la longitud a 3-40 caracteres.
- `validarNota()`: cambia la coma por un punto, comprueba que no está vacía, que `is_numeric()` y que está entre 0 y
  10, y devuelve la nota redondeada o `null`.
- `validarAlumno()`: valida el nombre y las tres notas y acumula **todos** los errores en `$errores`. Para saber si
  *este* alumno ha fallado, aunque el array ya traiga errores de antes, compara la longitud del array al principio
  y al final (ver el código debajo de esta lista).
- `recuperarCuaderno()`: comprueba que `$datos` es un array y que cada alumno es un array, y lo pasa por
  `validarAlumno()` con un array de errores que se ignora.
- En `index.php`, la acción `guardar`: mensaje distinto según `array_key_exists($nombre, $cuaderno)` y, si todo es
  correcto, formulario vacío.
- En el HTML, los campos ocultos al final del formulario, con el nombre escapado.

```php
// validarAlumno() (paso 2, funciones.php)
$erroresAntes = count($errores);
// ... validar el nombre y las 3 notas, añadiendo a $errores ...
return count($errores) > $erroresAntes ? null : [$nombre, $notas];
```

```php
// index.php (paso 2): el estado, al final del único formulario
echo "<input type='hidden' name='alumnos[" . e($nombre) . "][{$i}]' value='{$nota}'>\n";
```

> **Debes conocer**
>
> **El botón invisible del principio.** Al pulsar *Intro* en un campo de texto, el navegador simula un clic en el
> **primer** botón *submit* del formulario. En el paso 3 aparecerán *Ordenar*, *Editar* y *Eliminar* por encima del
> formulario de alta: sin el botón `<button type="submit" name="accion" value="guardar" class="oculto">` del
> principio, *Intro* ordenaría la tabla y lo escrito se perdería. Se pone ya en el paso 2, **antes** de que
> haga falta, porque así lo decía el plan del paso 1.

> **Error típico**
>
> Poner un `<form>` distinto para cada parte de la página (uno para el alta, otro para la tabla...). Los campos
> ocultos solo se envían con el formulario en el que están, así que al pulsar un botón de otro formulario el
> cuaderno llega vacío. Por eso la pista del enunciado insiste: **un único formulario** que envuelva toda la página.

**Lo que queda provisional.** La tabla muestra las notas tal cual (`<?= $nota ?>`, es decir, `5.5` con punto) y
tiene TODOs donde irán la media, la calificación y los botones. Los TODOs de este paso llevan anotado **en qué paso**
se resolverán (`// TODO (paso 3): ...`), y las funciones pendientes ya tienen su comentario de documentación
escrito encima: el plan sigue a la vista.

> **Pruébalo**
>
> Casos 1 a 5. Añade tres alumnos seguidos y comprueba que no se pierde ninguno. Con Ctrl+U, mira los campos ocultos:
> `name='alumnos[Brais Otero][0]' value='4'`. Con F12, cambia uno de esos `value` por `99` y pulsa *Guardar alumno*
> con el formulario vacío: saldrán los errores del formulario y Brais habrá **desaparecido** de la tabla (caso 10).
> Con el depurador, pon un punto de ruptura después de `recuperarCuaderno()` y mira cómo llega `$_POST['alumnos']`.

### 3.3.- Paso 3: medias, calificaciones y acciones sobre el cuaderno

**Objetivo.** Completar la tabla (R3) y añadir las acciones que trabajan sobre un cuaderno que ya se conserva:
*Editar*, *Eliminar* (R4), *Ordenar* (R5) y *Vaciar* (R8).

**Por qué ahora.** Con el estado funcionando, cada una de estas acciones es pequeña: unas líneas que modifican
`$cuaderno` antes de que la vista lo vuelva a escribir en los campos ocultos. Y la media y la calificación son
necesarias para ordenar por media, así que van en el mismo paso.

**Qué se escribe.**

- `mediaPonderada()` recorre las notas y multiplica cada una por `PESOS[$i]`: cambiar un peso es tocar una línea
  (RNF-08).
- `calificacion()` con `match (true)` y las condiciones en orden (`$nota < 5 => 'Insuficiente'`, …).
- `formatoNota()` con `number_format($nota, 2, ',', '.')`, que sustituye al `<?= $nota ?>` provisional del paso 2.
- `ordenarCuaderno()` **devuelve una copia** ordenada (no modifica el original). Por nombre, con `ksort()`. Por
  media, con un array auxiliar `nombre => media` y `arsort()` (ver el código debajo de esta lista).
- En `index.php`: el criterio de orden con lista blanca, al principio, **antes** de procesar la acción; *Eliminar*
  y *Editar* comprueban con `array_key_exists()` que el alumno existe; *Editar* rellena el formulario con
  `array_map('formatoNota', $cuaderno[$nombre])`, de modo que las notas salen con coma; *Vaciar* solo actúa con
  `isset($_POST['confirmarVaciar'])`; y el `case 'ordenar'` no hace nada, porque el criterio ya se ha leído.
- La ordenación se aplica **después** de la acción, justo antes de la vista: así un alumno recién añadido aparece
  ya en su sitio.

```php
// ordenarCuaderno() (paso 3, funciones.php)
arsort($medias);
// 3) reconstruimos el cuaderno en ese orden
```

```php
// index.php (paso 3): lista blanca del criterio de orden
$orden = in_array($_POST['orden'] ?? '', ['nombre', 'media']) ? $_POST['orden'] : 'nombre';
```

> **Error típico**
>
> Ordenar las medias con `sort()` o `rsort()`. Esas funciones **renumeran** las claves (0, 1, 2…), así que se
> pierde a qué alumno pertenece cada media. `arsort()` ordena por valor de mayor a menor **conservando las
> claves**, y `ksort()` ordena por clave: justo lo que pide la pista del enunciado.

> **Para saber más**
>
> ¿Dónde se guarda el criterio de orden entre peticiones? En ningún campo oculto: en el propio `<select
> name="orden">`, que tiene `selected` en la opción actual y, como está dentro del único formulario, se envía con
> cualquier botón. Un control visible también es estado. (Consecuencia curiosa: el desplegable solo se dibuja si
> hay alumnos, así que al vaciar el cuaderno el criterio vuelve a ser *nombre*.)

> **Pruébalo**
>
> Casos 6, 7, 8 y 11. Con `6,75`, `7`, `6` la fila debe decir 6,53 y *Bien*; con una media menor que 5, la fila sale
> resaltada. Pulsa *Editar*: las notas aparecen con coma, y al guardar sale "Se han actualizado las notas de…".
> Ordena por media, añade otro alumno y comprueba que el criterio se mantiene. Escribe un nombre y pulsa *Intro*:
> debe guardar, no ordenar.

### 3.4.- Paso 4 (solución completa): estadísticas y carga masiva

**Objetivo.** Calcular y dibujar las estadísticas del grupo (R6) y cargar muchos alumnos de golpe desde un
`<textarea>` (R7).

**Por qué al final.** Las dos funciones se construyen **encima** de lo anterior. `estadisticas()` es un bucle que
llama a `mediaPonderada()` y `calificacion()`, y `procesarCargaMasiva()` es un bucle que parte el texto y llama a
`validarAlumno()`. Si estuvieran al principio, habría que escribir dos veces la validación y la media.

**Qué se escribe.**

- `procesarCargaMasiva()`: separa las líneas con `preg_split('/\R/', $texto)` (sirve para `\n` y `\r\n`), salta las
  vacías y las que empiezan por `#`, comprueba que hay exactamente `1 + count(EVALUACIONES)` campos y valida el
  resto con `validarAlumno()`, recogiendo los errores de la línea en un array propio para añadirles delante
  `"Línea {$numLinea}: "`.
- `estadisticas()`: inicializa todo a cero, con la distribución creada con
  `array_fill_keys(CALIFICACIONES, 0)`, y sale enseguida si no hay alumnos (así nunca se divide entre 0).
- En la vista, el gráfico: una fila por calificación con un `<div class="barra">` cuyo ancho es el porcentaje de
  alumnos de esa calificación.

```php
// procesarCargaMasiva() (paso 4, funciones.php)
$erroresLinea = [];
$alumno = validarAlumno($campos[0], array_slice($campos, 1), $erroresLinea);
```

```php
// estadisticas() (paso 4, funciones.php)
$stats['distribucion'][calificacion($media)]++;
```

> **Debes conocer**
>
> **Cada decisión del análisis simplifica un paso posterior.** Como `validarAlumno()` añade los errores a un array
> recibido **por referencia** y devuelve `null` si este alumno ha fallado, la carga masiva no necesita saber nada
> de nombres ni de notas: le pasa un array vacío por línea y, si hay errores, les pone delante el número de línea.
> Las líneas incorrectas no impiden cargar las correctas (RF-09) sin escribir ni una regla de validación nueva.

> **Pruébalo**
>
> Caso 9. Pega `datos-prueba.txt` y pulsa *Cargar*: "Carga masiva: 5 alumno(s) añadidos o actualizados." y cinco
> errores, de la línea 9 ("se esperaban 4 campos separados por «;» y hay 3") a la 13 (el nombre con un número). En
> las estadísticas: media del grupo 6,46, mejor *Carmen Núñez Vázquez* (9,55), peor *Brais Otero* (4,05) y 3
> aprobados de 5. En el gráfico, *Insuficiente* tiene 2 alumnos (barra del 40 %) y *Suficiente*, ninguno.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 4 añade una acción nueva al `switch` y código a la vista, y podría haber estropeado algo que antes
funcionaba. Una lista de comprobación para esta tarea:

- [ ] Casos correctos: alta, modificación, edición, eliminación, orden por nombre y por media, carga masiva.
- [ ] Casos límite: notas 0 y 10; 10,01 y −0,5 (no valen); una media de 4,99 y otra de 5 (suspenso y aprobado);
      un nombre de 3 caracteres y otro de 41; un nombre con guion o apóstrofo.
- [ ] Multibyte: `"  ana   PÉREZ "` → *Ana Pérez*; nombres con ñ y tildes en la tabla, en los botones y en los
      campos ocultos.
- [ ] Estado: después de **cada** acción (también de un error o de *Vaciar* sin confirmar) siguen todos los alumnos.
- [ ] Maliciosos: nota `99` o `abc` en un campo oculto, un nombre oculto con `<script>`, un criterio de orden
      inventado, HTML en el nombre del formulario (debe salir escapado en el mensaje de error).
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)`.

## 5.- Refactorización y mejoras posibles

La solución ya está organizada en funciones cortas, así que la refactorización se hizo sobre la marcha. Aun así,
quedan detalles que se podrían mejorar, buenos para practicar:

- **Pendiente: arrays donde se espera un texto.** `validarAlumno()` y `validarNota()` declaran parámetros `string` y
  `?string`, pero el código no comprueba con `is_string()` lo que llega antes de llamarlas. Si alguien envía
  `nombre[]=x`, `notas[0][]=5` o `alumnos[Ana Pérez][0][]=5`, PHP lanza un `TypeError` y la página se rompe; con
  `eliminar[]=x`, `(string)` da el aviso *Array to string conversion*. Eso contradice RNF-15 ("ningún aviso ni
  error con ninguna entrada"). La corrección es la de siempre: `is_string($_POST['nombre'] ?? null)` antes de
  usarlo, igual que en la tarea básica de la comanda.
- `calificacion()` escribe a mano el 5 y los cinco textos, aunque existen `NOTA_APROBADO` y `CALIFICACIONES`. Hoy
  funciona, pero si alguien cambia un texto de la constante, `$stats['distribucion'][calificacion($media)]++`
  apuntaría a una clave que no existe. Mejor que las dos cosas salgan de las mismas constantes.
- `mediaPonderada()` se llama varias veces para el mismo alumno en cada petición (al ordenar por media, en la tabla
  y en las estadísticas). Se podría calcular una vez un array `nombre => media` y reutilizarlo.
- El mensaje "debe ser un número (has escrito «…»)" muestra el texto **después** de cambiar la coma por el punto:
  quien escribe `6,5x` ve `6.5x`. Basta con guardar el texto original para el mensaje.
- El `<textarea>` de la carga masiva no es pegajoso: si hay líneas con errores, el texto se pierde y hay que volver
  a pegarlo para corregirlas.
- Las ampliaciones del enunciado (renombrar con `nombreOriginal`, exportar al formato de la carga masiva, ordenar
  con `Collator`) son la siguiente **vuelta** del proceso: cada una empieza otra vez por el análisis. Por ejemplo,
  renombrar obliga a decidir qué pasa si el nombre nuevo ya existe.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Guardar el cuaderno solo en una variable | Al añadir el segundo alumno, el primero ha desaparecido | Campos ocultos `alumnos[Nombre][i]` (R9) |
| Varios `<form>` en la página | Los botones de un formulario no envían los campos ocultos del otro | Un único formulario |
| Confiar en los campos ocultos | Una nota de 99 en la tabla y en las estadísticas | `recuperarCuaderno()` con `validarAlumno()` |
| Usar el nombre sin validar como clave | Un `]` en el nombre rompe la estructura de `$_POST['alumnos']` | `nombreValido()` (RNF-14) y `e()` en el `name` |
| `ucwords(strtolower(...))` | Las mayúsculas con tilde no se convierten bien | `mb_convert_case(..., MB_CASE_TITLE, 'UTF-8')` |
| Comparar el nombre sin normalizar | *ana pérez* y *Ana Pérez* son dos alumnos | Normalizar antes de buscar la clave |
| `(float) '6,5'` | Da 6: se pierde el decimal | `str_replace(',', '.', ...)` antes de convertir |
| `sort()` / `rsort()` de las medias | Se pierden los nombres | `arsort()` y `ksort()` |
| No poner el *Guardar* invisible al principio | *Intro* ordena la tabla y se pierde lo escrito | Primer botón *submit* = *Guardar* |
| Escribir nombres o errores sin `e()` | Un nombre con HTML se ejecuta | **Todo** lo que se escribe en el HTML se escapa |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Qué recibe PHP en `$_POST['alumnos']` si la página tiene los campos `alumnos[Ana Pérez][0]`, `[1]` y `[2]`?
>   **Un array multidimensional `['Ana Pérez' => ['7.5', '6', '8.25']]`, con las notas como textos.**
> - ¿Por qué `recuperarCuaderno()` vuelve a validar cada alumno? **Porque los campos ocultos se pueden modificar
>   con F12: todo lo que llega del navegador es una entrada más y se valida igual que el formulario.**
> - ¿Por qué hay un único formulario en toda la página? **Porque solo así cualquier botón (Editar, Ordenar,
>   Cargar…) envía también los campos ocultos con el cuaderno.**
> - ¿Cómo sabe `validarAlumno()` si **este** alumno tiene errores, si `$errores` ya puede traer otros?
>   **Comparando `count($errores)` al principio y al final.**
> - ¿Por qué se usa `arsort()` y no `rsort()` para ordenar por media? **Porque `arsort()` conserva las claves (los
>   nombres) y `rsort()` las renumera.**
> - ¿Dónde se guarda el criterio de orden entre peticiones? **En el propio `<select name="orden">`, que se envía con
>   cualquier botón porque está dentro del único formulario.**
> - ¿Por qué la carga masiva va en el último paso? **Porque se apoya en `validarAlumno()`: solo hay que partir el
>   texto en líneas y campos y añadir el número de línea a los errores.**
