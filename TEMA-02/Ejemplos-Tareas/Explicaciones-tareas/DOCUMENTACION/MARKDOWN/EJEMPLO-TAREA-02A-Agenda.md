# La agenda de la comunidad de vecinos (EJEMPLO-TAREA-02A): cómo se llega a la solución paso a paso

**Material de la tarea:** [requisitos](../EJEMPLO-TAREA-02A-Agenda/material-referencia/TAREAS/Requisitos-TAREA-UD02A.md) ·
[enunciado resuelto (PDF)](../EJEMPLO-TAREA-02A-Agenda/material-referencia/TAREAS/MP0613-DWCS-EJEMPLO-RESUELTO-TAREA-UD02A.pdf) ·
[solución de referencia](../EJEMPLO-TAREA-02A-Agenda/material-referencia/CODIGO%20SOLUCIONES/EJEMPLO-TAREA-02A/) ·
[autómata (PDF)](../EJEMPLO-TAREA-02A-Agenda/material-referencia/CODIGO%20SOLUCIONES/EJEMPLO-TAREA-02A/docs/Automata%20-%20TAREA-02A.pdf) ·
[README del paso a paso](../EJEMPLO-TAREA-02A-Agenda/README.md) ·
[paso a paso](../EJEMPLO-TAREA-02A-Agenda/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> El presidente de la comunidad de vecinos donde vive **Carlos** le pide una forma cómoda de consultar el nombre y
> el teléfono de los vecinos. Carlos cree que con lo que sabe de formularios le basta, y se lo cuenta a **María**:
> "Un formulario, un array y listo". María le hace una sola pregunta: "Añades a Ana y luego a Brais. Cuando llega
> la petición de Brais, ¿dónde está Ana?". Carlos se queda pensando. El script termina en cada petición y el array
> desaparece con él. **Juan**, que pasaba por allí, sonríe: "Esa es la tarea. Lo del formulario es lo fácil".

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización. Es la descomposición de la **solución de referencia**
del módulo: el último paso es esa solución sin cambios en el código, con sus defectos señalados como
`FIXME (pendiente)` para que los revises tú (apartado 5).

**Cómo usarlo.** Lee primero los requisitos e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "PASO A PASO/paso3-nuevo-contacto" "PASO A PASO/paso4-estado-hidden"
```

(desde la carpeta `EJEMPLO-TAREA-02A-Agenda`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar
seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene cuatro reglas (R1…R4), una *Pista* y doce criterios de puntuación, que
[Requisitos-TAREA-UD02A.md](../EJEMPLO-TAREA-02A-Agenda/material-referencia/TAREAS/Requisitos-TAREA-UD02A.md) convierte en
9 requisitos funcionales. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Ver la agenda y el formulario | RF-01, RF-02 | Arriba los contactos (solo si hay alguno); abajo un formulario con nombre, teléfono, *Añadir Contacto* y *Limpiar Campos*. |
| Añadir, modificar, eliminar | R1, R2, R3 · RF-03…RF-06 | Un único formulario hace tres cosas distintas según el nombre exista o no y según llegue teléfono o no. Sin nombre, aviso. |
| Vaciar | R4 · RF-07, RF-08 | Un enlace *Vaciar* que solo aparece si hay contactos y que manda una variable por la URL (`?limpiar=1`). |
| Que no se pierda nada | Pista · RF-09 | Después de cada operación, el resto de contactos sigue ahí. |

Los requisitos no funcionales que más condicionan el código son tres: **sin `$_SESSION`** (RNF-03), **el estado en
campos `hidden`** (RNF-02) y **un array asociativo nombre → teléfono** (RNF-04). Los tres apuntan a la misma
pregunta del caso práctico: dónde vive la agenda entre una petición y la siguiente.

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

Al leer con calma aparecen preguntas. Conviene escribir la respuesta **antes** de programar. Algunas ya están
recogidas en el apartado 3 de los requisitos; otras salen de leer el código de referencia:

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿*juan* y *Juan* son el mismo contacto? | Sí: el nombre pasa por `ucwords()`, que pone en mayúscula la inicial de cada palabra. Ojo: *JUAN* sigue siendo distinto (`ucwords()` no pasa nada a minúsculas). |
| ¿Hay que validar el formato del teléfono? | No: solo importa si está vacío o no. |
| ¿El formulario va por GET o por POST? | Todo por GET (el enunciado solo lo exige para *Vaciar*). Consecuencia: la agenda entera viaja en la URL. |
| ¿Qué pasa con un nombre que no existe y sin teléfono? | Nada (R1 solo añade si hay teléfono). |
| ¿Qué hace *Limpiar Campos*? | Es un `<input type="reset">`: lo resuelve el navegador y **no llega al servidor**. En el autómata aparece como "HTML/JS". |
| ¿Cuánto puede medir el nombre? | `maxlength="18"`, que solo limita el navegador. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). Así quien
> corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de la tarea.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

El README del paso a paso trae seis casos (1 a 6). Se añaden aquí los que salen de las decisiones anteriores y de
los `FIXME (pendiente)` (7 a 10). Todos se escriben **antes** de programar: serán las pruebas de cada paso.

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Nombre vacío (o solo espacios), teléfono `123` | "AVISO: el Nombre es obligatorio"; la agenda no cambia |
| 2 | `ana perez` / `986111222` | Aparece *Ana Perez — 986111222* |
| 3 | Después del caso 2, `brais` / `600` | Están los dos contactos |
| 4 | `ana perez` / `111` | Cambia el teléfono de Ana Perez; sigue habiendo dos contactos |
| 5 | `brais` y teléfono vacío | Desaparece Brais |
| 6 | Pulsar *Vaciar* | Agenda vacía y el bloque *Vaciar Agenda* desaparece |
| 7 | `carla` y teléfono vacío, sin que Carla exista | No pasa nada |
| 8 | `JUAN` / `1` y después `juan` / `2` | Dos contactos distintos: *JUAN* y *Juan* |
| 9 | Un nombre con apóstrofo, `O'Brien` | Se ve entero en la lista (hoy **falla**: FIXME pendiente) |
| 10 | `index.php?limpiar=1&agenda[Juan]=555` escrito a mano | Agenda vacía (hoy **falla**: FIXME pendiente) |

Los casos 9 y 10 son los que la solución de referencia no supera. Tenerlos en la lista desde el principio es lo
que permite descubrirlos.

### 1.4.- Los datos: qué se guarda y dónde

Es la decisión más importante de la tarea. Como **HTTP no tiene estado**, cada clic en *Añadir Contacto* es una
petición nueva: el script empieza desde cero, sin ninguna variable de la petición anterior.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| La agenda | array asociativo `nombre => teléfono` | variable `$almacenAgenda` mientras dura la petición; **campos `hidden`** entre peticiones | La clave impide nombres repetidos (RNF-04); los `hidden` la llevan a la petición siguiente (RNF-02) |
| Nombre y teléfono escritos | string | `$_GET['nombre']`, `$_GET['telefono']` | Solo interesan en esta petición |
| ¿Se ha pulsado *Añadir*? | — | `$_GET['enviar']` (el `name` del botón) | Distingue el envío de la primera visita |
| Orden de vaciar | — | `?limpiar=1` en la URL del enlace | RF-08, RNF-05 |
| El aviso | string | se escribe con `echo` en su sitio | Solo dura la petición |

Elegir el **nombre como clave** del array no es un detalle: hace imposible tener dos contactos con el mismo nombre
y, como se verá en el paso 4, hace que *modificar* un teléfono no necesite ni una línea de código.

> **Debes conocer**
>
> **Los corchetes en el `name` de un campo crean un array en PHP.** Si el formulario lleva un campo
> `<input type='hidden' name="agenda[Ana Perez]" value='986111222'>` y otro `name="agenda[Brais]"`, PHP no recibe
> dos variables sueltas, sino una sola: `$_GET['agenda']` vale `['Ana Perez' => '986111222', 'Brais' => '600']`.
> Es decir, el array asociativo **se reconstruye solo** al llegar la petición: basta con escribir un campo por
> contacto.

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: un único fichero, por bloques

El enunciado pide **una única página** que se llama a sí misma (RNF-01), así que hay dos ficheros: `index.php` y
`estilos.css`. La solución de referencia es una tarea del principio del curso y no usa funciones: la
descomposición se hace en **bloques** de `index.php`, cada uno con su comentario, en el orden en que se ejecutan:

```text
1. Recuperar el estado     -> antes del DOCTYPE: $almacenAgenda desde $_GET['agenda'] o vacía
2. Procesar el envío       -> si llega "enviar": trim, aviso si no hay nombre, añadir/modificar/eliminar
3. Procesar el vaciado     -> si llega "limpiar": vaciar la agenda
4. Mostrar la agenda       -> fieldset "Datos Agenda" solo si hay contactos
5. Formulario + estado     -> formulario "Nuevo Contacto" y un campo hidden por contacto
6. Enlace Vaciar           -> fieldset "Vaciar Agenda" solo si hay contactos
```

Fíjate en que los bloques 2 y 3 están **dentro** del `<body>`: el aviso se escribe con `echo` justo donde debe
aparecer, encima de la agenda. Funciona, pero mezcla el procesamiento con la salida; en el apartado 5 se propone
separarlo.

> **Recomendación**
>
> Aunque no escribas funciones, piensa en cada bloque como si lo fuera: qué necesita (la agenda, el nombre, el
> teléfono) y qué deja hecho. En esta tarea el orden importa: el bloque 5 escribe en los `hidden` la agenda **ya
> modificada** por el bloque 2; si se escribieran antes, el cambio se perdería en la petición siguiente.

### 2.2.- Estados de la página

El material de referencia incluye un [autómata](../EJEMPLO-TAREA-02A-Agenda/material-referencia/CODIGO%20SOLUCIONES/EJEMPLO-TAREA-02A/docs/Automata%20-%20TAREA-02A.pdf)
con tres estados (ST0 arrancar, ST1 sin datos en la agenda, ST2 con datos) y tres eventos (*Enviar*, *Limpiar*,
*Vaciar*). Completado con lo que hace el código, queda así (*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Primera visita | — | agenda vacía | Sin contactos |
| Sin contactos / Con contactos | *Añadir* [nombre vacío] | aviso; la agenda no cambia | igual |
| Sin contactos / Con contactos | *Añadir* [nombre y teléfono] | añadir o sustituir el teléfono | Con contactos |
| Sin contactos | *Añadir* [nombre sin teléfono] | nada | Sin contactos |
| Con contactos | *Añadir* [nombre existente, sin teléfono] | eliminar el contacto | Con contactos, o Sin contactos si era el último |
| Con contactos | *Vaciar* | (el enlace no lleva la agenda) | Sin contactos |
| Cualquiera | *Limpiar Campos* | lo hace el navegador, no hay petición | igual |

Comparar esta tabla con la del autómata es un buen ejercicio, porque **no coinciden del todo**: en la tabla de
transiciones del PDF la condición de error es `!PL0 || !PL1` (nombre **o** teléfono no válidos), lo que llevaría a
mostrar el aviso cuando falta el teléfono, y no aparece la eliminación de un contacto (R3) ni la vuelta a "sin
datos" cuando se elimina el último. Dibujar las flechas con todas las condiciones es justo lo que hace aparecer esos
casos.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden no es casual:
primero lo que se **ve**, después **un** contacto, después **muchos** (el estado) y al final lo que **quita**
contactos.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 `paso1-maqueta` | Solo HTML y CSS, con ejemplos de todos los estados | RF-02, RNF-06, RNF-08 |
| 2 `paso2-todos` | La maqueta con un TODO en cada parte por programar | — |
| 3 `paso3-nuevo-contacto` | Recepción, validación, guardar y mostrar el contacto | RF-01, RF-03, RF-04 |
| 4 `paso4-estado-hidden` | La agenda viaja en campos `hidden` | RF-05, RF-09, RNF-02 |
| **5** `paso5-solucion-completa` | **Solución completa:** eliminar y vaciar | RF-06, RF-07, RF-08 |

### 3.1.- Paso 1: la maqueta (solo HTML y CSS)

**Objetivo.** Ver la página terminada antes de escribir una línea de PHP.

**Por qué empezar así.** El enunciado viene con capturas: la maqueta las convierte en HTML y obliga a decidir
**dónde** sale cada cosa. Por eso incluye a la vez ejemplos de **todos** los estados, aunque nunca coincidan: el
aviso, dos contactos inventados (*Ana Pérez* y *Brais Otero*) y el bloque *Vaciar*. Ese HTML no se tira: en los
pasos siguientes, cada ejemplo se sustituye por el PHP que lo genera.

**Decisión clave.** Los contactos se muestran como en las capturas, con campos de texto deshabilitados y una clase
que les quita el borde:

```html
<input type='text' value='Ana Pérez'   size='18' disabled class='visualizarAgenda'>
```

Esta decisión tendrá consecuencias: como el nombre irá **dentro de un atributo** `value='...'`, un apóstrofo en el
nombre lo romperá (caso 9). Y otra cosa que ya está en la maqueta y llega hasta la solución: el enlace *Vaciar* va
dentro de un `<button>`, que no es HTML válido. Los defectos de la maqueta **se heredan**.

> **Pruébalo**
>
> Abre `index.php`: se ven todos los bloques a la vez. Escribe un nombre y pulsa *Añadir Contacto*: la página no
> cambia (no hay PHP), pero mira la barra de direcciones: `?nombre=...&telefono=...&enviar=A%C3%B1adir+Contacto`.
> Eso es exactamente lo que recibirá el script en `$_GET`. Pasa el HTML por el validador del W3C: ya avisa del
> `<a>` dentro del `<button>`.

### 3.2.- Paso 2: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué ahora.** Con la maqueta delante es fácil ver qué parte depende de qué. Cada ejemplo de la maqueta se
cambia por un TODO que dice **en qué paso** se programa y **qué requisito** cumple, en el orden en que se ejecutará:

```php
// TODO (paso 3): nombre con teléfono: $almacenAgenda[nombre] = teléfono (añade o modifica)  [RF-04, RF-05]
// TODO (paso 5): nombre sin teléfono: eliminar el contacto  [RF-06]
```

**Lo más sencillo que funcione.** La agenda empieza como un array vacío marcado como provisional, para que la
página ya se ejecute:

```php
$almacenAgenda = array();   // provisional. Array asociativo nombre => teléfono  [RNF-04]
```

Además, el `action` del formulario deja de ser `index.php` fijo y pasa a `$_SERVER['PHP_SELF']` (la página se
llama a sí misma aunque cambie de nombre), y aparecen dos líneas, `ob_end_flush()` y `ob_implicit_flush(true)`,
que sirven para ver la salida mientras se depura paso a paso.

**Por qué este orden de TODOs.** Fíjate en que el paso 3 (un contacto) va antes que el paso 4 (conservar la
agenda). Así, el paso 4 queda como un cambio **pequeño y aislado** que contiene la idea central de la tarea.

> **Pruébalo**
>
> La página muestra el formulario, sin aviso y sin agenda, y **no hay errores de PHP**. Con la extensión *Todo
> Tree* de VS Code verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa se borra.

### 3.3.- Paso 3: recibir, validar y guardar un contacto

**Objetivo.** Recoger el nombre y el teléfono, avisar si falta el nombre, guardar el contacto y mostrar la agenda
(RF-01, RF-03, RF-04).

**Por qué ahora.** Sin datos que entren no hay nada que guardar. Y todo esto se puede probar con **una sola
petición**, sin pensar todavía en el estado.

**Qué se escribe.**

```php
if (isset($_GET['enviar'])) {
    //Limpiar de espacios en blanco al principio y al fin
    $nombre   = trim($_GET['nombre']);
    $telefono = trim($_GET['telefono']);
    if (strlen($nombre) == 0) {
        echo "<p class='aviso'>AVISO: el Nombre es obligatorio</p>";
    } else {
        $nombre = ucwords($nombre); //Ponemos en mayúsculas la primera letra de cada palabra.
        $almacenAgenda[$nombre] = $telefono;
```

- `isset($_GET['enviar'])`: en la primera visita no llega nada; sin esa comprobación, leer `$_GET['nombre']`
  daría un aviso *Undefined array key*. Se pregunta por el **botón**, que solo llega cuando se pulsa.
- `trim()` antes de comprobar la longitud: así un nombre de solo espacios cuenta como vacío (RNF-09).
- `$almacenAgenda[$nombre] = $telefono;` es **una sola línea para dos requisitos**: si el nombre no existe, añade
  (RF-04); si existe, sustituye el teléfono (RF-05). Es el premio de haber elegido el nombre como clave.
- La agenda se muestra con un `foreach ($almacenAgenda as $nombre => $telefono)` dentro de un
  `if (count($almacenAgenda) != 0)`, porque con la agenda vacía no debe salir el bloque (regla de RF-01).

**Dos FIXME provisionales.** El paso funciona, pero a medias, y se dice en el código:

```php
// FIXME (paso 4): la agenda empieza vacía en cada petición: solo se ve el último contacto (HTTP no tiene estado)
// FIXME (paso 5): ahora, con el teléfono vacío, se guarda un contacto sin teléfono
```

Un FIXME no es un olvido: es un defecto **conocido**, con el paso en que se arreglará. En este paso aparecen
también los dos primeros `FIXME (pendiente)` (escapar con `htmlspecialchars()` y el `</ul>` que sobra), que vienen
de la solución de referencia y no se corrigen en ningún paso.

> **Error típico**
>
> Comprobar si el nombre está vacío **antes** de hacer `trim()`: `"   "` tiene longitud 3 y se guardaría un contacto
> sin nombre visible. Primero se limpia, después se valida.

> **Pruébalo**
>
> Casos 1 y 2. Después añade `brais` / `600`: Ana **desaparece**. No es un error de este paso, es el FIXME: el
> script de la segunda petición no sabe nada de la primera. Es el mejor momento para entender de verdad que HTTP no
> tiene estado. Pon un punto de ruptura en `$almacenAgenda[$nombre] = $telefono;` y mira cómo el array tiene
> siempre un solo elemento.

### 3.4.- Paso 4: la agenda viaja en campos hidden

**Objetivo.** Que la agenda sobreviva de una petición a la siguiente (RF-09, RNF-02).

**Por qué ahora.** El paso 3 ha dejado el problema a la vista. La solución tiene **dos mitades** que no sirven la
una sin la otra: escribir la agenda en la página y leerla al principio del script.

**Qué se escribe.** Al final del formulario *Nuevo Contacto*, un campo oculto por contacto:

```php
foreach ($almacenAgenda as $nombre => $telefono) {
    echo "<input type='hidden' name=\"agenda[$nombre]\" value='$telefono'>";
}
```

Y al principio del script, en lugar del array vacío provisional:

```php
if (isset($_GET['agenda'])) {
    $almacenAgenda = $_GET['agenda'];
} else  // si no, creamos  $almacenAgenda como un array vacío
    $almacenAgenda = array();
```

Gracias a los corchetes del `name` (apartado 1.4), `$_GET['agenda']` ya es el array asociativo: no hay que
recorrer nada ni convertir nada. El diff de este paso es de muy pocas líneas y desaparece el FIXME (paso 4).

> **Debes conocer**
>
> **Cada decisión del análisis simplifica un paso posterior.** Con el estado recuperado, el caso 4 (modificar un
> teléfono) funciona **sin escribir nada más**: la línea del paso 3 ya sustituía el valor de una clave existente.
> Si la agenda fuera una lista de pares `[nombre, teléfono]`, ahora habría que buscar el nombre recorriendo la
> lista.

> **Para saber más**
>
> Como el formulario usa GET, la agenda entera aparece en la URL (`agenda%5BAna+Perez%5D=986111222&...`): se queda
> en el historial y, con muchos contactos, puede superar el límite de longitud de la URL. Con POST no se vería en la
> barra de direcciones, pero **seguiría siendo visible y modificable** con F12: los campos `hidden` están ocultos a
> la vista, no protegidos. Por eso esta técnica "arcana", como la llama el enunciado, se sustituirá por
> `$_SESSION` en temas posteriores.

> **Pruébalo**
>
> Casos 3 y 4. Mira el código fuente (Ctrl+U): hay un `<input type='hidden'>` por contacto. Con F12, cambia el
> `value` de uno de ellos y pulsa *Añadir Contacto* con un nombre nuevo: el teléfono cambiado se ha "guardado". El
> estado está en manos del cliente, y el servidor se cree lo que le llega.

### 3.5.- Paso 5 (solución completa): eliminar y vaciar

**Objetivo.** Eliminar un contacto si el teléfono llega vacío (RF-06) y el enlace *Vaciar* (RF-07, RF-08).

**Por qué al final.** Las dos cosas **quitan** contactos, y solo tiene sentido probarlas cuando la agenda conserva
varios (paso 4). Además, eliminar arregla el FIXME (paso 5).

**Eliminar.** La solución de referencia lo resuelve con una línea justo después de guardar:

```php
$almacenAgenda[$nombre] = $telefono;
if (strlen($telefono) == 0) unset($almacenAgenda[$nombre]);
```

Primero asigna y, si el teléfono está vacío, borra. Es corto y cubre dos casos de una vez: si el nombre existía, lo
elimina (caso 5); si no existía, lo crea y lo borra en el acto, es decir, no pasa nada (caso 7). Un
`if`/`else` explícito (si hay teléfono, guardar; si no, `unset`) haría lo mismo y se leería mejor.

**Vaciar.** Si hay contactos, se escribe con un *heredoc* un bloque con un enlace a la misma página:

```php
<button class="botonVaciar" ><a href="{$_SERVER['PHP_SELF']}?limpiar=1">Vaciar</a></button>
```

Y al procesar, `if (isset($_GET['limpiar']) && count($almacenAgenda) > 0)` vacía la agenda. Pero detente en un
detalle: el enlace **no lleva los campos `hidden`** (están en otro formulario, y un enlace no envía formularios).
Así que cuando se pulsa *Vaciar*, `$_GET['agenda']` no llega, la agenda ya empieza vacía y ese `if` **nunca se
cumple**. La agenda se vacía... por no enviarla. Es el origen del primer `FIXME (pendiente)` (apartado 5.1).

> **Pruébalo**
>
> Casos 5, 6 y 7. Pon un punto de ruptura dentro del `if (isset($_GET['limpiar']) ...)` y pulsa *Vaciar*: el
> depurador **no se detiene**. Después escribe a mano en la URL el caso 10, que sí lleva la agenda: ahora el
> depurador entra en el `if`... y la página termina con un error fatal.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 5 añade un `unset()` justo después de guardar y podría haber estropeado el añadir o el modificar. Una lista
de comprobación para esta tarea:

- [ ] Casos correctos: añadir, modificar y eliminar, con uno y con varios contactos.
- [ ] Casos límite: eliminar el último contacto (desaparecen *Datos Agenda* y *Vaciar*); nombre de solo espacios;
      nombre inexistente sin teléfono.
- [ ] Normalización: *juan* y *Juan* son el mismo contacto; *JUAN*, no.
- [ ] Caracteres especiales: un apóstrofo, comillas dobles o `<` en el nombre y en el teléfono.
- [ ] Peticiones escritas a mano: `?limpiar=1&agenda[Juan]=555`, `?agenda=hola`, `?enviar=1` sin nombre ni teléfono.
- [ ] HTML válido (validador del W3C) y ningún aviso de PHP con `error_reporting(E_ALL)`.

Las tres últimas líneas fallan con la solución de referencia: eso es lo que trata el apartado siguiente.

## 5.- Refactorización y mejoras posibles

### 5.1.- Los FIXME (pendiente): ejercicio de revisión

La solución de referencia cumple los requisitos funcionales con un usuario que usa el formulario con normalidad,
pero tiene tres defectos que se han dejado marcados sin corregir. Encontrarlos y arreglarlos es un buen ejercicio de
**revisión de código**:

| FIXME (pendiente) | Cómo se comprueba | Cómo se arregla |
|---|---|---|
| `unset($almacenAgenda)` destruye la **variable entera**, y el `count($almacenAgenda)` del bloque siguiente falla. | Caso 10: `index.php?limpiar=1&agenda[Juan]=555`. En PHP 8, `count()` de una variable que no existe es un error fatal. | `$almacenAgenda = array();` (vaciar, no destruir). |
| El nombre y el teléfono se escriben **sin `htmlspecialchars()`** (RNF-10), en la lista y en los `hidden`. | Caso 9: con `O'Brien`, en la lista solo se ve *O* (el apóstrofo cierra `value='...'`). Con el teléfono `600'1`, el que se rompe es el `hidden`: en la petición siguiente el teléfono es `600`. | `htmlspecialchars($nombre, ENT_QUOTES)` en cada sitio donde se escribe un dato; `ENT_QUOTES` porque los atributos usan comillas simples. |
| Sobra un `</ul>` y hay un `<a>` dentro de un `<button>`. | Validador del W3C. | Borrar el `</ul>`; dejar solo el enlace con la clase `botonVaciar`, o hacer un formulario GET con un botón `name="limpiar"` (el enunciado habla de "un nuevo formulario"). |

> **Debes conocer**
>
> **Que un defecto no se note no significa que no exista.** El primer FIXME no se ve nunca usando la página
> normalmente, porque el enlace *Vaciar* no envía la agenda. Pero basta con que alguien cambie el diseño (por
> ejemplo, que *Vaciar* sea un botón del mismo formulario, con los `hidden`) para que la página empiece a fallar.
> Los casos de prueba "escritos a mano" sirven para encontrar estos defectos dormidos.

### 5.2.- Otras mejoras

Detalles que se podrían mejorar sin cambiar el comportamiento, buenos para practicar:

- **Separar el procesamiento de la salida.** Guardar el aviso en una variable `$aviso` y mover los bloques 2 y 3
  antes del `<!DOCTYPE html>`; en el HTML, solo `if ($aviso !== '')` y `echo`. Es la estructura de siempre de los
  apuntes (recibir y validar, procesar, mostrar).
- **No fiarse de lo que llega.** `?agenda=hola` hace que `$almacenAgenda` sea un string y el `count()` da un error
  fatal; `?enviar=1` sin nombre ni teléfono produce avisos. Se arregla con `is_array($_GET['agenda'] ?? null)` y
  con `$_GET['nombre'] ?? ''`.
- **Nombres con tildes.** `ucwords()` trabaja con bytes: `ángel` se queda con la *á* minúscula.
  `mb_convert_case($nombre, MB_CASE_TITLE)` lo resuelve y, además, convierte *JUAN* en *Juan* (caso 8).
- **Las dos líneas de depuración.** `ob_end_flush()` y `ob_implicit_flush(true)` están activas aunque el comentario
  dice que son "solo para" depurar. Si el servidor no tiene un búfer de salida activo, `ob_end_flush()` produce un
  aviso. Lo correcto es dejarlas comentadas en la versión que se entrega.
- **`$_SERVER['PHP_SELF']`** se escribe en el `action` y en el enlace sin escapar; la versión segura es
  `htmlspecialchars($_SERVER['PHP_SELF'])`, o directamente `action=""`.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Guardar la agenda solo en una variable PHP | Solo se ve el último contacto | Escribirla en campos `hidden` y leerla al empezar |
| Escribir los `hidden` antes de procesar el envío | El último cambio se pierde en la petición siguiente | Procesar primero, escribir el estado después |
| Usar una lista en lugar de un array asociativo | Hay que buscar el nombre a mano; puede haber repetidos | Nombre como clave (RNF-04) |
| Validar antes de `trim()` | Contactos con nombre de solo espacios | Limpiar primero |
| `unset($agenda)` para vaciarla | Error fatal en el siguiente `count()` | `$agenda = array();` |
| No escapar al escribir en atributos | Un apóstrofo rompe la lista o el estado | `htmlspecialchars(..., ENT_QUOTES)` |
| Leer `$_GET['nombre']` sin comprobar | Avisos de PHP en la primera visita | `isset($_GET['enviar'])` o `?? ''` |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué en el paso 3 solo se ve el último contacto? **Porque HTTP no tiene estado: cada petición ejecuta el
>   script desde cero y el array de la petición anterior ya no existe.**
> - ¿Qué recibe PHP si el formulario tiene campos `name="agenda[Ana]"` y `name="agenda[Brais]"`? **Un único array
>   `$_GET['agenda']` con las claves `Ana` y `Brais`.**
> - ¿Por qué modificar un teléfono no necesita código propio? **Porque `$almacenAgenda[$nombre] = $telefono`
>   sustituye el valor si la clave ya existe.**
> - ¿Por qué funciona *Vaciar* aunque el `if` del vaciado no llegue a ejecutarse? **Porque el enlace no envía los
>   campos `hidden`: la agenda llega vacía.**
> - ¿Qué diferencia hay entre `unset($agenda)` y `$agenda = array()`? **La primera destruye la variable; la
>   segunda la deja como un array vacío, que se puede seguir usando con `count()` o `foreach`.**
> - ¿Por qué un campo `hidden` no es un sitio seguro para guardar datos? **Porque el usuario puede verlo con Ctrl+U
>   y cambiarlo con F12 antes de enviar el formulario.**
