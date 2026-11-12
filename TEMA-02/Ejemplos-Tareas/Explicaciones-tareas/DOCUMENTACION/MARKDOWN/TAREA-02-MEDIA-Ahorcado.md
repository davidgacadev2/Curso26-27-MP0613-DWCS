# El ahorcado (TAREA-02, nivel medio): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-MEDIA-Ahorcado/ENUNCIADO.md) ·
[requisitos](../TAREA-02-MEDIA-Ahorcado/Requisitos.md) ·
[solución](../TAREA-02-MEDIA-Ahorcado/SOLUCION-01/) ·
[paso a paso](../TAREA-02-MEDIA-Ahorcado/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> **Carlos** ya ha leído el enunciado del ahorcado dos veces y tiene ganas de abrir el editor. **María** lo para:
> "Antes, dime qué es lo único que no puede aparecer nunca en el HTML". Carlos duda: "¿La palabra?". "Exacto. Y
> eso lo cambia todo: si la palabra no viaja en la página, ¿cómo sabrá el servidor qué palabra se está jugando en
> la siguiente petición?". Carlos se da cuenta de que la tarea no empieza por el teclado de letras, sino por
> decidir **qué datos** se guardan y **dónde**.

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "PASO A PASO/paso2" "PASO A PASO/paso3"
```

(desde la carpeta `SOLUCION-01`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 10 requisitos (R1…R10) que [Requisitos.md](../TAREA-02-MEDIA-Ahorcado/Requisitos.md)
convierte en 8 requisitos funcionales comprobables. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Configurar y empezar | R1, R2 · RF-01, RF-02 | Un fichero que **devuelve** un array; un formulario generado a partir de él; validar categoría y dificultad con una **lista blanca**. |
| Jugar | R4, R5, R6 · RF-03, RF-04 | Mostrar huecos, un teclado de 27 botones, contar fallos y dibujar el muñeco. Todo con **multibyte** (Ñ, tildes, Ü). |
| Resolver y terminar | R7, R8, R9 · RF-05…RF-07 | Comparar sin tildes, penalizar 2 fallos, detectar partida ganada o perdida, marcador. |
| No fiarse del cliente | R3, R10 · RF-08, RNF-07, RNF-08 | La palabra **no** viaja en el HTML; todo lo que llega en campos ocultos se **revalida**. |

Los requisitos no funcionales que más condicionan el código son tres: **sin `$_SESSION`** (RNF-03: el estado viaja en
campos ocultos), **estado mínimo** (RNF-05: lo que se puede calcular no se guarda) y **la palabra no aparece en el
HTML** (RNF-07).

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

Al leer con calma aparecen preguntas. Conviene escribir la respuesta **antes** de programar:

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Con qué letra se descubre la Ü de CIGÜEÑA? | Con la U: se compara la **letra base** (`quitarTildes()`). La Ñ es otra letra y no se convierte en N. |
| ¿El espacio de A CORUÑA es una letra? | No: se muestra como espacio, no cuenta en "N letras" y no hay que adivinarlo. |
| ¿Qué pasa si llega una letra repetida aunque el botón esté deshabilitado? | Mensaje *Ya habías probado la A.* y no cuenta como fallo. |
| ¿Cuántos fallos cuesta resolver mal? | 2 (constante `FALLOS_PALABRA`). |
| ¿Qué etapa del dibujo corresponde a 3 fallos de 4? | La proporcional: `round(fallos * 6 / maxFallos)`. |
| ¿Cómo pruebo siempre con la misma palabra? | Una **semilla** en un campo oculto y `mt_srand($semilla)`. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). Así quien
> corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de la tarea.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son ejemplos concretos de entrada y salida. Escríbelos en una tabla
antes de programar: serán las pruebas de cada paso.

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Abrir la página | Formulario con Animales y normal (6 fallos) seleccionados |
| 2 | Animales, normal, semilla 15 | "7 letras"; en el HTML hay `indice=2` pero **no** CIGÜEÑA |
| 3 | Con CIGÜEÑA: U, Ñ, Z | Se ve la Ü, "La Ñ aparece una vez", Z en rojo; "Fallos: 1 de 6" |
| 4 | Con CIGÜEÑA: C, I, G, E, A | "¡Has acertado CIGÜEÑA con 1 fallo!" y teclado deshabilitado |
| 5 | Galicia, difícil, semilla 4: X, K, Q, W | "Te han ahorcado. La palabra era A CORUÑA." con la Ñ marcada en rojo |
| 6 | Resolver `jabali` (con espacios delante y detrás) con JABALÍ | "¡Correcto!" (no importan tildes, mayúsculas ni espacios de los extremos) |
| 7 | Resolver `a coruna` con A CORUÑA | 2 fallos: la Ñ no es una N |
| 8 | `indice=99`, `usadas=AA`, `maxfallos=7`, `letra[]=A` | "La partida recibida no es válida", **sin avisos de PHP** |
| 9 | `categoria=<script>…` | Error y el texto se ve escapado, no se ejecuta |

Los casos 8 y 9 son **maliciosos**: no se pueden provocar con el formulario normal, hay que editar el HTML con F12
o enviar la petición con `curl`. Que estén en la lista desde el principio obliga a pensar en ellos en el diseño.

### 1.4.- Los datos: qué se guarda y dónde

Es la decisión más importante de la tarea. Como **HTTP no tiene estado**, cada clic en una letra es una petición
nueva: el script empieza desde cero y tiene que **reconstruir la partida** a partir de lo que envíe el formulario.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Palabras por categoría | array `categoría => lista` | `palabras.php` (`return [...]`) | Datos fijos, separados del código (RNF-04) |
| Categoría de la partida | string | campo oculto | Para encontrar la lista |
| **Índice** de la palabra | int | campo oculto | En lugar de la palabra: así no aparece en el HTML (R3) |
| Fallos permitidos | int | campo oculto | Depende de la dificultad elegida |
| Letras usadas | **un solo** string (`"UÑZ"`) | campo oculto | Con ellas se calculan huecos y fallos |
| Palabras falladas al resolver | int | campo oculto | Cada una suma 2 fallos |
| Victorias y derrotas | int | campos ocultos | Sobreviven de una partida a otra (R9) |
| Huecos, fallos, ganada/perdida | — | **se calculan** | Si se guardaran, podrían contradecirse con las letras usadas |
| Errores, mensaje de la jugada | array / string | variables | Solo duran la petición |

> **Debes conocer**
>
> **Guarda el estado mínimo y calcula el resto.** Si guardaras también los fallos en un campo oculto, alguien podría
> enviar `fallos=0` con diez letras falladas, y tendrías que comprobar que los dos datos coinciden. Si los fallos se
> **cuentan** a partir de las letras usadas, esa contradicción es imposible. Menos datos guardados significa menos
> cosas que validar.

La partida queda como un array asociativo:

```php
['categoria' => 'Animales', 'indice' => 2, 'maxFallos' => 6, 'usadas' => 'UÑZ', 'palabrasFalladas' => 0]
```

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Tres ficheros con una responsabilidad cada uno: `palabras.php` (datos), `funciones.php` (lógica, sin HTML) e
`index.php` (decide qué hacer y muestra la página). `index.php` sigue la estructura de siempre de los apuntes:

```text
1. Inicializar          -> cargar funciones y palabras, valores por defecto, leer el marcador
2. Recibir y validar    -> ¿qué botón se ha pulsado? (empezar, letra, resolver, nueva partida)
3. Procesar             -> elegir palabra, probar letra, resolver, actualizar el marcador
4. Preparar mensajes    -> fallos, estado (jugando/ganada/perdida), mensaje final
5. Mostrar la página    -> formulario de configuración O partida (sin lógica, solo variables)
```

Cada tarea del paso 3 es una función pequeña con un verbo en el nombre, que **recibe** lo que necesita y
**devuelve** un resultado (sin `echo`), para poder probarla y depurarla por separado:

| Función | Responsabilidad | Paso |
|---|---|---|
| `e()` | Escapar para el HTML | 2 |
| `validarConfiguracion()` | Categoría y dificultad de la lista → `[configuración, errores]` | 2 |
| `nuevaPartida()` / `palabraSecreta()` | Elegir el índice al azar / recuperar la palabra con el índice | 2 |
| `leerEstado()` | Pasar de `$_POST` al array de la partida, sin avisos | 3 |
| `quitarTildes()` / `letrasUsadas()` | Letra base / partir `"UÑZ"` en letras (multibyte) | 3 |
| `palabraOculta()` | Casillas con su letra o un hueco | 3 |
| `probarLetra()` | Añadir la letra y devolver el mensaje | 3 |
| `contarFallos()` / `estadoPartida()` / `dibujo()` | Calcular fallos, ganada/perdida, dibujo | 4 |
| `resolver()` / `leerMarcador()` | Decir la palabra entera / victorias y derrotas | 5 |
| `estadoValido()` | ¿Es una partida posible? | 6 |

> **Recomendación**
>
> Fíjate en que `probarLetra()` y `resolver()` reciben la partida **por referencia** (`array &$partida`): modifican
> las letras usadas y, además, devuelven el mensaje. Es una decisión de diseño razonable aquí, pero cuando una
> función cambia sus parámetros conviene que el nombre lo deje claro y que esté documentado en la firma.

### 2.2.- Estados de la página

La página tiene cuatro estados y el botón pulsado decide la transición (*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Sin partida | *Empezar* [configuración válida] | elegir índice al azar | Jugando |
| Sin partida | *Empezar* [configuración no válida] | mostrar errores | Sin partida |
| Jugando | letra o *Resolver* [estado recibido no válido] | error | Sin partida |
| Jugando | letra o *Resolver* [quedan huecos y fallos < máximo] | añadir letra / comparar | Jugando |
| Jugando | letra o *Resolver* [no quedan huecos] | victoria + 1 | Ganada |
| Jugando | letra o *Resolver* [fallos ≥ máximo] | derrota + 1 | Perdida |
| Ganada / Perdida | letra (petición forzada) | "La partida ya ha terminado" | igual |
| Ganada / Perdida | *Nueva partida* | conservar categoría, dificultad y marcador | Sin partida |

Dos flechas no son evidentes y salen de dibujar el diagrama: la de "estado no válido" (R10) y la de "jugar sobre
una partida terminada". Esta última obliga a calcular **cómo estaba la partida antes de la jugada** (`$antes`), y es
también lo que evita sumar dos veces la misma victoria.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden no es
casual: primero lo que permite **ver** algo, después lo que permite **jugar** y al final lo que hace el juego
**robusto**.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs y esqueleto HTML | — |
| 2 | Palabras, configuración, palabra al azar, campos ocultos | R1, R2, R3 |
| 3 | Huecos, teclado, probar letras (multibyte) | R4, R5 |
| 4 | Fallos calculados, dibujo, fin de la partida | R6, R8 |
| 5 | Resolver, marcador, nueva partida | R7, R8, R9 |
| **6** | **Solución completa:** validar el estado recibido | R10 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué empezar así.** El enunciado tiene diez requisitos que se entrelazan; si se empieza a escribir código
directamente, es fácil olvidar uno (por ejemplo, el de la partida terminada). Los TODO son el **plan** escrito
dentro del propio código: en `index.php`, uno por cada parte de la estructura de los apuntes, en el orden en que se
ejecutará; en `funciones.php`, la **firma** de cada función con lo que recibe y lo que devuelve:

```php
// TODO: función probarLetra(array &$partida, mixed $letra, string $palabra): string -> añade la letra y devuelve el mensaje
// TODO: función contarFallos(array $partida, string $palabra): int -> letras que no están + palabras falladas
```

El HTML también se marca con TODOs: el formulario de configuración, la palabra con huecos, el teclado, los campos
ocultos... Es la "maqueta" de esta tarea: una página que ya carga, aunque solo muestra el título.

**Decisión clave.** Ya en el paso 1 se escribe la estructura de la partida (en el comentario de cabecera de
`funciones.php`) y se decide que la palabra **no** se guarda. Todo lo demás depende de eso.

> **Pruébalo**
>
> Abre la página: sale el título y nada más, pero **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code
> verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa se borra.

### 3.2.- Paso 2: empezar una partida y guardarla en campos ocultos

**Objetivo.** Formulario de configuración, elegir la palabra al azar y dejar la partida escrita en campos
ocultos (R1, R2, R3).

**Por qué ahora.** Sin una partida no se puede probar nada del juego. Y es el momento de comprobar la decisión más
delicada (la palabra no está en el HTML) antes de construir nada encima.

**Qué se escribe.**

- `palabras.php` **devuelve** el array: `$palabras = require __DIR__ . '/palabras.php';`. Añadir una categoría no
  obliga a tocar el resto (RNF-04), y el `<select>` se genera con `array_keys($palabras)`.
- `validarConfiguracion()` comprueba con `array_key_exists()` que la categoría y la dificultad están en la lista.
  Antes, `is_string($datos['categoria'] ?? null)`: si alguien envía `categoria[]=Frutas`, llega un array y, sin esa
  comprobación, `array_key_exists()` lanzaría un `TypeError`.
- `nuevaPartida()` elige el **índice**, no la palabra (ver el código debajo de esta lista).
- En el HTML, cinco campos ocultos (categoría, índice, fallos permitidos, letras usadas y palabras falladas), todos
  con `e()`.

```php
// nuevaPartida() (paso 2, funciones.php)
mt_srand($semilla);                         // la misma semilla elige siempre la misma palabra (para probar)
$indice = mt_rand(0, count($lista) - 1);
```

> **Debes conocer**
>
> **La semilla es una herramienta de prueba.** Con `mt_srand(15)` el primer `mt_rand()` da siempre el mismo
> número, así que puedes repetir la misma palabra en cada prueba (en Animales, la semilla 15 da CIGÜEÑA). Para
> jugar de verdad, el formulario manda una semilla aleatoria y, si no llega ninguna válida, se usa
> `random_int()`. Es un ejemplo de diseñar pensando en **cómo vas a probar**.

> **Pruébalo**
>
> Casos 1 y 2. Elige Animales, normal, pon 15 en el campo `semilla` con F12 y pulsa *Empezar*. Con Ctrl+U comprueba
> que el código fuente contiene `name="indice" value="2"` y que **no** contiene CIGÜEÑA. Prueba también a cambiar
> con F12 una opción del `<select>` por `Plantas`: debe salir el error.

### 3.3.- Paso 3: la palabra con huecos y el teclado

**Objetivo.** Recuperar la partida en cada petición, mostrar la palabra con huecos y probar letras (R4, R5).

**Por qué ahora.** Es el corazón del juego y la parte con más trampas (la Ñ, las tildes). Conviene tenerla
funcionando, y bien probada, antes de añadir fallos y finales que dependen de ella.

**Qué se escribe.**

- `leerEstado()` reconstruye la partida a partir de `$_POST`. Los enteros pasan por `filter_var(...,
  FILTER_VALIDATE_INT)` y, si no valen, se ponen a `-1`: así un valor raro **no produce avisos** y luego será
  fácil de rechazar. Todavía **no se valida** que la partida sea posible: eso se deja para el paso 6.
- `quitarTildes()` con `strtr()` y la constante `SIN_TILDE` (Á→A … Ü→U, **sin la Ñ**).
- `letrasUsadas()` con `mb_str_split()`, y `palabraOculta()`, que devuelve un array de casillas
  (`['letra' => 'Ü', 'clase' => 'acierto']`). La función **no escribe HTML**: devuelve datos y la vista los pinta.
- `probarLetra()`: valida la letra (string, un carácter, del abecedario), detecta las repetidas, la añade a
  `usadas` y cuenta las apariciones con `mb_substr_count(quitarTildes($palabra), $letra)`.
- En el HTML, el teclado: un `<button name="letra" value="A">` por letra, con clase `acierto` o `fallo` y
  `disabled` si ya se ha usado.

> **Error típico**
>
> Usar `str_split()` y `strlen()`. En UTF-8 la Ñ ocupa **2 bytes**: `str_split('AÑ')` da tres trozos y el segundo y
> el tercero no son letras. Con `mb_str_split('AÑ')` se obtiene `['A', 'Ñ']`. Lo mismo pasa con `strlen('Ñ')`, que
> vale 2: en esta tarea **todas** las funciones de texto deben ser `mb_`.

**Un truco provisional.** En este paso todavía no se sabe si la partida ha terminado, así que el código deja
`$antes = 'jugando';`. Es un valor provisional, consciente y marcado: el paso 4 lo sustituye por el cálculo
real. Avanzar con "lo más sencillo que funcione" y arreglarlo después es desarrollo incremental, siempre que no se
olvide (para eso están los TODO y los FIXME).

> **Pruébalo**
>
> Caso 3 con la semilla 15: la U debe descubrir la **Ü**, la Ñ debe decir "aparece una vez" y la Z salir en rojo.
> Con el depurador, pon un punto de ruptura en `probarLetra()` y mira cómo cambia `$partida['usadas']` en cada clic.
> Con F12, cambia el `value` de un botón por `AB`: "Esa letra no es válida.".

### 3.4.- Paso 4: fallos, dibujo y fin de la partida

**Objetivo.** Contar los fallos, dibujar el ahorcado y saber si la partida está ganada o perdida (R6, R8).

**Por qué ahora.** Con las letras funcionando, los fallos son solo una **consecuencia** de ellas: no hace falta
ningún campo oculto nuevo, solo funciones que calculan.

**Qué se escribe.**

- `contarFallos()`: las letras usadas que no están en la palabra (sin tildes).
- `estadoPartida()`: primero mira los fallos (si se ha llegado al máximo, `perdida`) y después si queda algún
  hueco (`jugando`) o no (`ganada`). Reutiliza `palabraOculta()`: no se repite la lógica de los huecos.
- `dibujo()`: siete etapas y una **regla de tres** para que la dificultad influya (código debajo de la lista).
- En `index.php` se sustituye el `$antes = 'jugando'` provisional por `estadoPartida()`: una partida terminada ya
  no admite jugadas ("La partida ya ha terminado").
- Al perder, `palabraOculta(..., true)` enseña las letras que faltaban con la clase `faltaba`.

```php
// dibujo() (paso 4, funciones.php)
$etapa = min(6, (int) round($fallos * 6 / $maxFallos));     // con 4 fallos permitidos se avanza más deprisa
```

> **Para saber más**
>
> El dibujo es un `<pre>` con texto, pero pasa igualmente por `e()`. ¿Por qué, si lo genera el propio programa y hoy
> no contiene `<` ni `&`? Porque es más fácil cumplir una regla sin excepciones que recordar cuáles son las
> excepciones: si mañana el dibujo incluye algún dato del usuario (su nombre, por ejemplo), ya estará escapado.
> **Todo** lo que se escribe en el HTML se escapa.

> **Pruébalo**
>
> Casos 4 y 5. Con 4 fallos permitidos el muñeco debe completarse en 4 fallos, y con 8, en 8. Al ganar, el teclado
> queda deshabilitado; si con F12 habilitas un botón y lo pulsas, sale "La partida ya ha terminado".

### 3.5.- Paso 5: resolver, marcador y nueva partida

**Objetivo.** Completar la funcionalidad: decir la palabra entera (R7), llevar el marcador (R9) y volver a jugar.

**Por qué ahora.** Son funciones que se apoyan en todo lo anterior: `resolver()` usa `quitarTildes()` y
`letrasUsadas()`; el marcador necesita `estadoPartida()` antes y después de la jugada.

**Qué se escribe.**

- `resolver()` normaliza lo escrito (`mb_strtoupper(trim(...))`) y compara **sin tildes**. Si acierta, marca como
  usadas todas las letras de la palabra: así `estadoPartida()` dirá `ganada` **sin necesidad de un campo nuevo**.
  Si falla, `palabrasFalladas++`, y `contarFallos()` suma `FALLOS_PALABRA` por cada una.
- El marcador se actualiza comparando `$antes` y `$despues`: solo cuenta la jugada que **termina** la partida.
- *Nueva partida* lee el estado anterior para proponer la misma categoría y dificultad
  (`array_search($anterior['maxFallos'], DIFICULTADES, true)` traduce 6 a `normal`).
- El botón invisible al principio del formulario: *Intro* pulsa el **primer** botón del formulario, que sin él
  sería la letra A.

> **Debes conocer**
>
> **Cada decisión del análisis simplifica un paso posterior.** Como la victoria se *calcula* a partir de las letras
> usadas, resolver bien la palabra no necesita ningún dato nuevo: basta con dar por usadas sus letras. Si en el
> paso 2 se hubiera guardado un campo `ganada=1`, ahora habría que mantener sincronizados dos datos.

> **Pruébalo**
>
> Casos 6 y 7: `jabali` (con espacios delante y detrás) vale por JABALÍ, pero `a coruna` no vale por A CORUÑA (la Ñ no es N) y cuesta 2 fallos.
> Escribe en el campo y pulsa *Intro*: debe resolver, no probar la A. Gana una partida, pulsa *Nueva partida* y
> comprueba que el formulario lleva `name="victorias" value="1"`.

### 3.6.- Paso 6 (solución completa): no fiarse del navegador

**Objetivo.** Comprobar que el estado recibido es una partida posible (R10).

**Por qué al final (y no al principio).** Validar el estado exige saber qué es una partida "posible": categoría
existente, índice dentro de la lista, dificultad válida, letras del abecedario sin repetir, y fallos que no
superen lo que cabe en una partida. Esa última comprobación usa `contarFallos()`, que no existía hasta el paso 4.
Hasta ahora el juego funcionaba con un **cliente honrado**; este paso lo prepara para uno que no lo es.

```php
if (!estadoValido($partida, $palabras)) {
    $errores[] = 'La partida recibida no es válida (¿se han modificado los campos ocultos?). Empieza una nueva.';
    $partida = null;
}
```

Fíjate en el orden de `estadoValido()`: primero se comprueba que la categoría y el índice existen y **solo después**
se llama a `palabraSecreta()`. Al revés, `indice=99` produciría un aviso de PHP *Undefined array key*.

> **Pruébalo**
>
> Casos 8 y 9. Con F12 (o con `curl -d "letra=A&categoria=Animales&indice=99&maxfallos=6&usadas=&palabrasfalladas=0"
> http://localhost:8000/`) prueba `indice=99`, `usadas=AA`, `usadas=1`, `maxfallos=7`, `usadas=QWXYZK` y una
> categoría con `<script>`. Todas deben dar "La partida recibida no es válida", sin avisos y sin que se ejecute
> nada.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 6 añade un `if` al principio de la jugada y podría haber estropeado algo que antes funcionaba. Una lista de
comprobación para esta tarea:

- [ ] Casos correctos: ganar y perder en las tres dificultades.
- [ ] Casos límite: perder exactamente en el último fallo permitido; ganar resolviendo con 1 fallo menos del máximo;
      resolver mal cuando solo queda 1 fallo (2 fallos de golpe: la partida termina igual).
- [ ] Multibyte: CIGÜEÑA, JABALÍ, PINGÜINO, A CORUÑA, PIÑA.
- [ ] Maliciosos: arrays donde se espera un texto, índices fuera de rango, letras repetidas o que no son letras,
      HTML en cualquier campo.
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)` y la palabra nunca en el código fuente mientras se juega.

## 5.- Refactorización y mejoras posibles

La solución ya está organizada en funciones cortas, así que la refactorización se hizo sobre la marcha. Aun así,
quedan detalles que se podrían mejorar sin cambiar el comportamiento, buenos para practicar:

- `letrasUsadas($partida)` se llama varias veces en cada petición (en el teclado, una vez por botón): se podría
  calcular una sola vez antes del HTML y guardarla en una variable.
- El teclado y la palabra mezclan algo de lógica en la vista (calcular la clase de cada botón). Se podría mover a
  una función `teclado(array $partida, string $palabra): array` que devuelva cada botón con su clase y si está
  deshabilitado, igual que hace `palabraOculta()`.
- Las ampliaciones del enunciado (pista, dos jugadores, puntuación) son la siguiente **vuelta** del proceso: cada una
  empieza otra vez por el análisis. Por ejemplo, en el modo de dos jugadores la palabra tendría que viajar en el
  HTML, y `base64_encode` no la protege: solo la disfraza.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Guardar la palabra en un campo oculto | Ctrl+U la revela | Guardar categoría e índice (R3) |
| `str_split`, `strlen`, `strtoupper` | La Ñ se rompe; las longitudes no cuadran | Funciones `mb_` |
| Incluir la Ñ en `SIN_TILDE` | PIÑA se resuelve con "pina" | La Ñ es una letra distinta |
| Guardar también los fallos | Datos que se contradicen y hay que validar | Calcularlos a partir de las letras |
| Leer `$_POST['indice']` sin comprobar | Avisos de PHP o `TypeError` con `indice[]=1` | `?? ''`, `is_string`, `filter_var` |
| Llamar a `palabraSecreta()` antes de validar | *Undefined array key* con `indice=99` | Validar categoría e índice primero |
| Sumar la victoria cada vez que se ve la partida ganada | El marcador crece al recargar | Comparar el estado antes y después de la jugada |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué se guarda el **índice** de la palabra y no la palabra? **Porque todo lo que está en el HTML lo puede
>   ver el jugador con Ctrl+U; con el índice, el servidor recupera la palabra de `palabras.php`.**
> - ¿Por qué los fallos no se guardan en un campo oculto? **Porque se pueden calcular a partir de las letras usadas;
>   guardarlos permitiría estados contradictorios que habría que validar.**
> - ¿Qué da `str_split('Ñ')`? ¿Y `mb_str_split('Ñ')`? **Dos bytes sueltos; `['Ñ']`.**
> - ¿Por qué la validación del estado (paso 6) llega al final? **Porque necesita funciones que se escriben antes
>   (`contarFallos()`), y porque primero hay que tener un juego que funcione con un cliente honrado.**
> - ¿Para qué sirve la semilla? **Para repetir siempre la misma palabra al probar y depurar.**
> - ¿Qué variable evita que una partida terminada admita más jugadas o sume dos victorias? **`$antes`: el estado de
>   la partida antes de aplicar la jugada.**
