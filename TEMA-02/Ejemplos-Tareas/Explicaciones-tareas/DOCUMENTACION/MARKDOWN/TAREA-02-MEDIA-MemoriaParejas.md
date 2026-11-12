# Memoria de parejas (TAREA-02, nivel medio): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-MEDIA-MemoriaParejas/ENUNCIADO.md) ·
[requisitos](../TAREA-02-MEDIA-MemoriaParejas/Requisitos.md) ·
[solución](../TAREA-02-MEDIA-MemoriaParejas/SOLUCION-01/) ·
[paso a paso](../TAREA-02-MEDIA-MemoriaParejas/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> La biblioteca municipal quiere un *memory* de parejas para las tardes de juegos y **Juan** se lo encarga a
> **Carlos**. Carlos ya se imagina el tablero de botones, pero **María** le hace una pregunta: "Cuando el jugador
> gira la segunda carta y no es pareja, las dos se ven un momento y luego vuelven a estar boca abajo. ¿Dónde
> guardas esas dos cartas?". Carlos piensa un rato: "En ningún sitio. Solo se ven en esa respuesta; en la
> siguiente petición ya no existen". "Exacto. Pues empieza por ahí: decide qué tiene que **recordar** la página
> entre un clic y el siguiente, y qué solo dura una respuesta".

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "PASO A PASO/paso3" "PASO A PASO/paso4"
```

(desde la carpeta `SOLUCION-01`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 8 requisitos (R1…R8) que [Requisitos.md](../TAREA-02-MEDIA-MemoriaParejas/Requisitos.md)
convierte en 12 requisitos funcionales comprobables. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Configurar | R1, R2 · RF-01, RF-02 | Cuatro datos con valores iniciales; **todos** los errores a la vez; formulario pegajoso; letras sin repetir y suficientes. |
| Crear el tablero | R3 · RF-03 | Cada letra **dos veces**, barajadas y repartidas en filas: `str_split`, `shuffle`, `array_chunk`. |
| Jugar un turno | R4, R5 · RF-04…RF-07 | Primera carta pendiente, segunda carta: pareja (+10) o fallo (−2). Marcador desde la primera carta. |
| Terminar | R6 · RF-08…RF-10 | Ganar al encontrar todas, perder al **superar** los fallos; botones deshabilitados; *Nueva partida*. |
| Estado y seguridad | R7, R8 · RF-11, RF-12 | Todo en campos ocultos (el tablero como `cartas[f][c]`); revalidar lo recibido; nada sin escapar. |

Los requisitos no funcionales que más condicionan el código son tres: **sin `$_SESSION`** (RNF-02), el **tablero
como array en campos ocultos** (RNF-03) y **ninguna entrada produce avisos de PHP** (RNF-09).

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Cómo se identifica una carta? | Por su posición `"fila-columna"`, empezando en 0 (`"1-2"`). Va en el `value` del botón. |
| ¿Qué pasa si sobran letras? | Se usan las primeras: con 2 × 3 y `ABCD` el tablero usa `ABC` (`substr()`). |
| ¿Qué es "superar" los fallos? | Tener **más** de los permitidos: con 1 permitido, se pierde al segundo fallo (`>` y no `>=`). |
| ¿Se guardan las cartas de un fallo? | No: se devuelven aparte (`$temporales`) y solo se pintan en esa respuesta. |
| ¿Cuenta como fallo pulsar una carta no válida? | No: mensaje *Esa carta no se puede elegir* y no cambia nada (ni siquiera las cartas giradas). |
| ¿Cuándo aparece el marcador? | Cuando se ha girado al menos una carta: un contador `giradas` que empieza en 0. |
| ¿Cómo pruebo siempre con el mismo reparto? | Una **semilla** en un campo oculto del formulario y `mt_srand($semilla)` antes de `shuffle()`. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). El caso
> de "superar" es típico: la diferencia entre `>` y `>=` es un fallo más o menos, y solo un ejemplo concreto del
> enunciado ("con 3 permitidos, al 4.º fallo") lo aclara.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son ejemplos concretos de entrada y salida. Escríbelos en una tabla
antes de programar: serán las pruebas de cada paso. Con la semilla 7, el tablero 2 × 3 con `ABC` queda
`A C B` / `B C A` (dato del README de la solución), y eso permite escribir los casos de juego con posiciones
exactas.

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Abrir la página | Formulario con 4, 4, `ABCDEFGH` y 8; no aparece "Puntos:" |
| 2 | 3 filas y 3 columnas | "Con 3 filas y 3 columnas hay 9 cartas: el número de cartas debe ser par." |
| 3 | Filas 7, columnas `x`, letras `aab`, fallos 0 | Cuatro errores a la vez, uno de ellos "Repetidas: A" |
| 4 | 2 × 3 con `AB` | "Para 3 parejas hacen falta al menos 3 letras (has escrito 2)." |
| 5 | Letras `<b>x` | Error, y el campo muestra `&lt;B&gt;X` en el código fuente |
| 6 | 2 × 3, `abcd` con espacios delante y detrás, semilla 7 | 6 botones (`0-0` a `1-2`) y solo las letras `ABC` |
| 7 | Pulsar `0-0`; después, otra vez `0-0` | "Has girado una A. Elige la segunda carta." y "Puntos: 0"; luego "Esa carta no se puede elegir" |
| 8 | `0-0` y `1-2` | "¡Pareja de A! +10 puntos." y "Parejas: 1 de 3" |
| 9 | Después, `0-1` y `1-0` | "No son pareja (C y B)", 8 puntos y 1 fallo; en el clic siguiente la B vuelve a estar boca abajo |
| 10 | 2 × 3 sin fallos | "Has encontrado las 3 parejas con 30 puntos"; un clic posterior no cambia el marcador |
| 11 | 2 × 2, 1 fallo permitido, dos fallos | "Has superado los 1 fallos permitidos" y ningún botón de carta |
| 12 | *Nueva partida* tras un 2 × 3 con `ABC` y 1 fallo | El formulario muestra esos valores |
| 13 | `cartas[0][0]=Z`, `encontradas=0-0`, `filas=9`, `cartas[1][1]=<script>` | "La partida recibida no es válida" |
| 14 | `carta[]=0-0` o `carta=9-9` | "Esa carta no se puede elegir", **sin avisos de PHP** |

Los casos 13 y 14 son **maliciosos**: no se pueden provocar con la página normal, hay que editar el HTML con F12 o
enviar la petición con `curl`. El caso 13 pide algo más fino que "que no dé error": `encontradas=0-0` es **media
pareja**, una posición que existe pero que no puede estar encontrada sola.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada clic en una carta es una petición nueva: el script empieza desde cero y tiene
que **reconstruir la partida** a partir de lo que envíe el formulario.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Filas, columnas, letras, fallos permitidos | int / string | campos ocultos | Para validar el tablero y volver al formulario con *Nueva partida* |
| Tablero | array de filas | un campo oculto **por carta**: `cartas[1][2]` | Lo pide R7: PHP reconstruye el array a partir de los corchetes |
| Parejas encontradas | lista de posiciones | **un** campo oculto: `"0-0,1-2"` | `implode(',', ...)` al escribir, `explode(',', ...)` al leer |
| Carta pendiente del turno | string (`''` si no hay) | campo oculto | Es lo que distingue "primera carta" de "segunda carta" |
| Puntos, fallos, cartas giradas | int | campos ocultos | El marcador y el fin de la partida dependen de ellos |
| Semilla | int | campo oculto **del formulario** | Solo se usa al crear el tablero |
| Cartas de un fallo (`$temporales`) | array | variable | Solo se ven en la respuesta a ese clic |
| Jugando / ganada / perdida | — | **se calcula** (`estadoPartida()`) | Sale de las encontradas y los fallos |
| Errores, mensaje de la jugada | array / string | variables | Solo duran la petición |

> **Debes conocer**
>
> **Aquí el secreto sí viaja en la página.** En el ahorcado se guardaba un índice para que la palabra no
> apareciera en el HTML. En esta tarea R7 obliga a guardar el tablero en campos ocultos, así que con Ctrl+U se
> ven todas las letras: es el límite de guardar el estado en el cliente, y se resolverá cuando veáis las
> sesiones. Lo que **sí** se puede (y se debe) evitar es que, cambiando esos campos, se juegue una partida
> **imposible**: eso es R8.

La partida queda como un array asociativo (`nuevaPartida()`):

```php
['filas' => 2, 'columnas' => 3, 'letras' => 'ABC', 'maxFallos' => 8,
 'cartas' => [['A', 'C', 'B'], ['B', 'C', 'A']],
 'encontradas' => [], 'pendiente' => '', 'puntos' => 0, 'fallos' => 0, 'giradas' => 0]
```

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Dos ficheros PHP con una responsabilidad cada uno: `funciones.php` (lógica, sin HTML) e `index.php` (decide qué
hacer y muestra la página), más `estilos.css`. `index.php` sigue la estructura de siempre:

```text
1. Inicializar          -> cargar funciones, valores por defecto (4 x 4, ABCDEFGH, 8)
2. Recibir y validar    -> ¿qué botón se ha pulsado? (Empezar, una carta, Nueva partida)
3. Procesar             -> crear el tablero, o leer el estado, validarlo y jugar la carta
4. Preparar mensajes    -> estado de la partida y mensaje final
5. Mostrar la página    -> formulario de configuración O tablero con marcador y campos ocultos
```

Cada tarea del paso 3 es una función pequeña que **recibe** lo que necesita y **devuelve** un resultado (sin
`echo`):

| Función | Responsabilidad | Paso |
|---|---|---|
| `e()` | Escapar para el HTML | 2 |
| `validarEntrada()` | Los cuatro datos del formulario → `[configuración, errores]` | 2 |
| `crearTablero()` | Cada letra dos veces, barajadas con la semilla, en filas | 3 |
| `nuevaPartida()` / `leerEstado()` | La partida recién empezada / la partida de los campos ocultos, sin avisos | 3 |
| `letraEn()` | La letra de `"fila-columna"`, o `null` si no existe | 4 |
| `jugar()` | Girar una carta → `[mensaje, cartas que se ven solo ahora]` | 4 |
| `cartaVisible()` | ¿Se ve la letra de esta posición? | 3 (provisional), 4, 5 |
| `estadoPartida()` | `jugando`, `ganada` o `perdida` | 3 (provisional), 5 |
| `estadoValido()` | ¿Es una partida posible? | 6 |

> **Recomendación**
>
> `jugar()` recibe la partida **por referencia** (`array &$partida`) y devuelve **dos** cosas: el mensaje y las
> cartas temporales. Devolver un array `[mensaje, temporales]` y recogerlo con
> `[$mensaje, $temporales] = jugar($partida, $posicion);` es más claro que usar variables globales o dos
> funciones que repitan la misma lógica.

### 2.2.- Estados de la página

La partida tiene dos estados "de juego" que no se ven a simple vista: **esperando la primera carta** (`pendiente`
vacía) y **esperando la segunda** (`pendiente` con una posición). El botón pulsado decide la transición
(*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Sin partida | *Empezar* [configuración válida] | crear el tablero barajado | Primera carta |
| Sin partida | *Empezar* [configuración no válida] | mostrar todos los errores | Sin partida |
| Con tablero | carta [estado recibido no válido] | error | Sin partida |
| Primera / Segunda carta | carta [no existe, ya encontrada o es la pendiente] | "Esa carta no se puede elegir" | igual |
| Primera carta | carta [válida] | `pendiente` = posición, `giradas` + 1 | Segunda carta |
| Segunda carta | carta [misma letra] | 2 encontradas más, +10 | Primera carta, o Ganada si no quedan |
| Segunda carta | carta [otra letra] | −2, fallos + 1, cartas temporales | Primera carta, o Perdida si fallos > permitidos |
| Ganada / Perdida | carta (petición forzada) | no se juega | igual |
| Con tablero | *Nueva partida* | formulario con la configuración anterior | Sin partida |

Dibujar los estados aclara dos cosas. La primera: tras un **fallo** se vuelve a "Primera carta" aunque las dos
cartas se estén viendo; por eso no se guardan. La segunda: la flecha "jugar sobre una partida terminada" obliga a
que `jugar()` mire el estado **antes** de girar nada.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden: primero lo
que permite **configurar**, después lo que permite **ver** el tablero, luego **jugar**, **terminar** y, al final,
hacerlo **robusto**.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs y esqueleto HTML | — |
| 2 | Constantes, `e()`, validación y formulario pegajoso | R1, R2 |
| 3 | Tablero barajado, cartas como botones, partida en campos ocultos | R3, R7 |
| 4 | Girar cartas: primera y segunda, pareja o fallo, marcador | R4, R5, R7 |
| 5 | Fin de la partida (ganada o perdida) y *Nueva partida* | R6 |
| **6** | **Solución completa:** validar el estado recibido | R8 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué empezar así.** El turno tiene muchas condiciones (carta no válida, primera, segunda, pareja, fallo,
partida terminada) y es fácil olvidar una. Los TODO son el **plan** dentro del propio código: en `index.php`, uno
por cada botón y en el orden en que se ejecutará; en `funciones.php`, la **firma** de cada función con lo que
recibe y lo que devuelve:

```php
// TODO: función jugar(array &$partida, string $posicion): array -> gira una carta: [mensaje, cartas que se ven solo ahora]
    // TODO: si la partida ya ha terminado, no se juega
```

Fíjate en que algunos TODO están **dentro** de otros (sangrados): son detalles que se resolverán más tarde que la
función que los contiene. El de `jugar()` no se programa hasta el paso 5, aunque `jugar()` se escribe en el 4.

**Decisión clave.** Ya en el paso 1, `index.php` tiene un TODO "no fiarse del navegador" justo después de
recuperar la partida. Queda reservado el sitio de la validación, aunque se programe la última.

> **Pruébalo**
>
> Abre la página: sale el título y nada más, pero **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code
> verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa pierde la palabra `TODO`.

### 3.2.- Paso 2: el formulario de configuración y su validación

**Objetivo.** Las constantes, `e()`, `validarEntrada()` con todos los mensajes y el formulario pegajoso (R1, R2).

**Por qué ahora.** Sin una configuración válida no hay tablero. Y la validación tiene bastantes casos (rangos,
paridad, letras repetidas, letras suficientes) que conviene probar **solos**, sin un juego encima que distraiga.

**Qué se escribe.**

- Constantes para los límites y los puntos (`MIN_LADO`, `MAX_LADO`, `MIN_FALLOS`, `MAX_FALLOS`, `PUNTOS_PAREJA`,
  `PUNTOS_FALLO`): los mensajes de error y las etiquetas del formulario se construyen con ellas.
- `validarEntrada()` lee los cuatro datos **sin avisos**: `filter_var(... ?? '', FILTER_VALIDATE_INT, $rango)`
  para los números y `is_string(... ?? null)` antes de `strtoupper(trim(...))` para las letras.
- Las comprobaciones dependen unas de otras: la paridad solo se mira si filas y columnas son válidas, y "hacen
  falta al menos N letras" solo si las letras son correctas y no hay repetidas. Así cada error es **útil**: no
  sale "faltan letras" cuando el problema es que hay un `<`.
- Las repetidas se sacan con `count_chars()` (código debajo de esta lista).
- Si no hay errores, la configuración devuelve las letras ya **recortadas** a las parejas
  (`substr($letras, 0, $parejas)`).
- En el formulario, un campo oculto `semilla` con `random_int(1, 999999)`: todavía no se usa, pero el formulario
  ya queda completo.

```php
// validarEntrada() (paso 2, funciones.php): las letras que aparecen más de una vez
$repetidas = array_keys(array_filter(count_chars($letras, 1), fn($veces) => $veces > 1));
```

`count_chars($letras, 1)` devuelve `código ASCII => veces`; por eso el mensaje pasa las claves por
`array_map('chr', ...)` para escribir "Repetidas: A" y no "Repetidas: 65".

**Un truco provisional.** `index.php` deja `$estado = $partida === null ? 'sin partida' : 'jugando';`. Todavía no
hay partida, pero la vista ya puede usar `$estado`; el paso 5 lo sustituirá por `estadoPartida()`.

> **Pruébalo**
>
> Casos 1 a 5. Comprueba que en el caso 3 salen los **cuatro** errores a la vez y que con `<b>x` el código fuente
> (Ctrl+U) muestra `&lt;B&gt;X`. Con datos correctos (2 × 3 y `abcd`) el formulario vuelve a salir, ahora con
> `ABC`: aún no hay tablero, pero ya se ve que las letras se normalizan y se recortan.

### 3.3.- Paso 3: el tablero barajado y la partida en campos ocultos

**Objetivo.** Crear el tablero (R3), dibujarlo como botones y guardarlo en campos ocultos para recuperarlo en la
petición siguiente (R7).

**Por qué ahora.** Es la decisión de datos más delicada de la tarea: un **array de arrays** que tiene que salir
de PHP, viajar como texto en el HTML y volver a ser un array. Conviene comprobar que ese viaje de ida y vuelta
funciona **antes** de escribir ninguna regla del juego.

**Qué se escribe.**

- `crearTablero()` son tres funciones de la biblioteca encadenadas (código debajo de esta lista).
- `nuevaPartida()` monta el array de la partida con todo a cero y `leerEstado()` hace lo contrario: lo reconstruye
  desde `$_POST`. Cada campo se protege: `(int) (... ?? 0)` para los números, `is_string` para los textos e
  `is_array($datos['cartas'] ?? null)` para el tablero. Todavía **no se valida** que la partida sea posible.
- En `index.php`, *Empezar* usa la semilla si es un entero válido y, si no, `random_int(1, PHP_INT_MAX)`.
- En el HTML, una tabla con un `<button name="carta" value="1-2">?</button>` por carta y, debajo, un campo oculto
  por carta: `name="cartas[<?= $f ?>][<?= $c ?>]"`.

```php
// crearTablero() (paso 3, funciones.php)
$cartas = str_split($letras . $letras);    // "ABC" -> A, B, C, A, B, C
mt_srand($semilla);                         // la misma semilla da siempre el mismo reparto (para probar)
shuffle($cartas);
return array_chunk($cartas, $columnas);     // [[fila 0], [fila 1], ...]
```

> **Debes conocer**
>
> **Los corchetes en el `name` crean arrays en `$_POST`.** Seis campos `cartas[0][0]` … `cartas[1][2]` llegan
> como `$_POST['cartas'] = [0 => ['A', 'C', 'B'], 1 => ['B', 'C', 'A']]`: el mismo array que se escribió. Por la
> misma razón, alguien puede enviar `cartas=hola` (un texto) o `carta[]=0-0` (un array donde esperas un texto), y
> por eso cada lectura empieza con `is_array` o `is_string`.

**Dos funciones provisionales.** La vista ya llama a `cartaVisible()` para decidir si pinta la letra o el botón,
y el paso 5 necesitará `estadoPartida()`. Para que la página funcione ya, se escriben con un valor fijo y un
comentario que lo dice:

```php
return false;                               // provisional: todas boca abajo
```

Así la vista se escribe **entera** ahora (con la clase `encontrada` o `girada` y el `disabled` según `$estado`) y
no hay que volver a tocarla cuando las funciones hagan su trabajo de verdad.

> **Pruébalo**
>
> Caso 6: con F12 pon 7 en el campo `semilla`, elige 2 × 3 y `abcd`. Deben salir 6 botones `?`; con Ctrl+U
> comprueba los seis campos `cartas[f][c]` (`A C B` / `B C A`). Pulsa cualquier carta: el tablero **vuelve
> igual**, porque todavía no se juega, pero eso demuestra que la partida sobrevive a la petición. Con el
> depurador, pon un punto de ruptura después de `leerEstado()` y mira `$partida['cartas']`.

### 3.4.- Paso 4: girar cartas, parejas, fallos y marcador

**Objetivo.** Programar el turno completo (R4), el marcador (R5) y el resto del estado en campos ocultos (R7).

**Por qué ahora.** Con el tablero viajando bien, ya hay sobre qué jugar. El fin de la partida se deja para el paso
5: primero hay que poder girar cartas para **llegar** a ganar o perder.

**Qué se escribe.**

- `letraEn()` es la **puerta de entrada** de cualquier posición: comprueba el formato con
  `preg_match('/^(\d+)-(\d+)$/', ...)` y busca la carta con `?? null`. Devuelve la letra o `null`, nunca un
  aviso, aunque llegue `9-9` o `hola`.
- `jugar()` rechaza primero lo que no se puede elegir (posición inexistente, carta encontrada o la propia carta
  pendiente) y **después** suma `giradas`. Luego, dos caminos: si no hay pendiente, la carta queda pendiente; si
  la hay, se compara.
- Las cartas de un fallo **no se guardan**: se devuelven en el segundo elemento del resultado (código debajo de
  esta lista). En la petición siguiente nadie las recuerda y vuelven a estar boca abajo sin hacer nada.
- `cartaVisible()` deja de ser provisional: se ve la letra si la carta está encontrada, es la pendiente o está en
  `$temporales`.
- En el HTML, el mensaje de la jugada y el marcador (solo si `giradas > 0`), y los campos ocultos que faltaban:
  `encontradas` (con `implode(',', ...)`), `pendiente`, `puntos`, `fallos` y `giradas`.

```php
// jugar() (paso 4, funciones.php): la segunda carta no es pareja
$partida['puntos'] -= PUNTOS_FALLO;
$partida['fallos']++;
$otra = letraEn($partida, $primera);
return ["No son pareja ($otra y $letra): −" . PUNTOS_FALLO . ' puntos.', [$primera, $posicion]];
```

> **Error típico**
>
> Guardar en un campo oculto las cartas del fallo "para enseñarlas" y borrarlas en la petición siguiente. Funciona,
> pero añade un dato más que viaja, que hay que validar y que hay que acordarse de vaciar. Lo que solo dura **una
> respuesta** es una variable (`$temporales`), no parte del estado.

**Por qué un contador `giradas`.** R5 pide el marcador "en cuanto se gira la primera carta". No sirve mirar los
puntos o los fallos (tras la primera carta siguen a 0) ni la pendiente (tras una pareja vuelve a estar vacía).
Hace falta un dato que diga "ya se ha jugado algo".

> **Pruébalo**
>
> Casos 7, 8 y 9 con la semilla 7. Pulsa `0-0` dos veces: la segunda vez, "Esa carta no se puede elegir" y el
> marcador no cambia. Tras el fallo C y B, pulsa cualquier otra carta: la B vuelve a salir como `?`. Con el
> depurador, pon un punto de ruptura en `jugar()` y mira `pendiente` y `encontradas` en dos clics seguidos.
> Con F12 cambia el `name` de un botón a `carta[]`: también "Esa carta no se puede elegir", sin avisos (caso 14).

### 3.5.- Paso 5: fin de la partida y nueva partida

**Objetivo.** Saber si la partida está ganada o perdida, mostrarlo y volver a empezar (R6).

**Por qué ahora.** Con los turnos funcionando, ganar y perder son solo **consecuencias** de las encontradas y los
fallos: no hace falta ningún campo oculto nuevo, solo una función que calcula.

**Qué se escribe.**

- `estadoPartida()` deja de ser provisional (código debajo de esta lista).
- `jugar()` resuelve su TODO pendiente: si la partida no está `jugando`, devuelve
  "La partida ha terminado: empieza una nueva." sin tocar nada.
- `cartaVisible()` resuelve el suyo: al perder, se ven **todas** las cartas.
- En `index.php`, `$estado` se calcula con `estadoPartida()` y se preparan los mensajes finales.
- *Nueva partida* reutiliza `leerEstado()` para recuperar filas, columnas, letras y fallos permitidos y
  rellenar con ellos el formulario.

```php
// estadoPartida() (paso 5, funciones.php)
if (count($partida['encontradas']) === $partida['filas'] * $partida['columnas']) {
    return 'ganada';
}
if ($partida['fallos'] > $partida['maxFallos']) {
    return 'perdida';
}
return 'jugando';
```

> **Debes conocer**
>
> **La vista ya estaba preparada.** Los botones llevan `disabled` cuando `$estado !== 'jugando'` desde el paso 3,
> pero hasta ahora `$estado` siempre valía `jugando`. En este paso no se toca esa línea del HTML: basta con que
> `$estado` empiece a valer `ganada` o `perdida`. Es la ventaja de haber dejado un valor provisional en lugar de
> no escribir nada.

Un detalle: cuando la partida ya está ganada y llega un clic forzado, `jugar()` devuelve su mensaje, pero
`index.php` lo **sustituye** por el mensaje final ("Has encontrado las 3 parejas…"). El mensaje de `jugar()` no
llega a verse; lo que importa (RF-08) es que el marcador no cambia.

> **Pruébalo**
>
> Casos 10, 11 y 12. En el caso 11 la partida **no** debe terminar con el primer fallo (1 permitido), sino con el
> segundo, y entonces se ven todas las letras. Tras ganar, con F12 cambia un `<span>` por un botón
> `name="carta" value="0-0"` y púlsalo: el marcador sigue igual.

### 3.6.- Paso 6 (solución completa): no fiarse del navegador

**Objetivo.** Comprobar que el estado recibido es una partida posible (R8).

**Por qué al final (y no al principio).** Para decidir qué es una partida "posible" hay que conocer todas sus
piezas: el tablero (paso 3), qué significa `pendiente` y `encontradas` (paso 4) y cuántos fallos caben en una
partida, incluida una ya perdida (paso 5). Hasta ahora el juego funcionaba con un **cliente honrado**; este paso
lo prepara para uno que no lo es.

```php
if (!estadoValido($partida)) {
    $errores[] = 'La partida recibida no es válida (¿se han modificado los campos ocultos?). Empieza una nueva.';
    $partida = null;
}
```

`estadoValido()` va de lo general a lo particular, y cada comprobación se apoya en las anteriores:

1. Dimensiones y fallos permitidos dentro de los rangos, y un número par de cartas.
2. Recorre **las posiciones que debería haber** (de `0-0` a la última) con `?? null`, no las que han llegado: así
   una carta que falta no da aviso, y cada letra debe ser una sola de `A` a `Z`.
3. Compara las letras del tablero, **ordenadas**, con las de `letras` duplicadas y ordenadas: "cada letra
   exactamente dos veces" en dos `sort()` y una comparación.
4. Las encontradas: posiciones que existen, sin repetir, y cada letra encontrada **dos** veces
   (`array_count_values()`): así se rechaza la media pareja `encontradas=0-0`.
5. La pendiente, si hay, existe y no está encontrada; los fallos entre 0 y `maxFallos + 1`.

¿Por qué `maxFallos + 1` y no `maxFallos`? Porque una partida **perdida** tiene exactamente un fallo más de los
permitidos, y también es un estado posible: si llega un clic forzado sobre una partida perdida, la respuesta
debe ser "la partida ha terminado", no "la partida no es válida".

> **Pruébalo**
>
> Casos 13 y 14. Con F12 cambia `cartas[0][0]` por `Z`, `encontradas` por `0-0` o `filas` por `9`; o con `curl`:
> `curl -d "carta=0-0&filas=9" http://localhost:8000/`. Todas deben dar "La partida recibida no es válida", sin
> avisos de PHP y sin que se ejecute nada.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 6 añade un `if` antes de jugar y podría rechazar una partida que sí es posible (por ejemplo, una perdida).
Una lista de comprobación para esta tarea:

- [ ] Casos correctos: ganar sin fallos, ganar con fallos (puntos que bajan) y perder.
- [ ] Casos límite: tableros 2 × 2 y 6 × 6; justo tantas letras como parejas; perder exactamente al superar el
      máximo; puntos negativos.
- [ ] Turno: pulsar la pendiente, una carta encontrada y una posición inexistente (no cuentan como giradas).
- [ ] Maliciosos: arrays donde se espera un texto, letras cambiadas, media pareja, pendiente encontrada, HTML en
      cualquier campo.
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)` y todos los textos que se escriben en el HTML pasan por
      `e()`.

## 5.- Refactorización y mejoras posibles

La solución ya está organizada en funciones cortas y no tiene `FIXME` pendientes. Aun así, quedan detalles que se
podrían mejorar, buenos para practicar:

- **Estado mínimo.** Los puntos se pueden calcular: `PUNTOS_PAREJA * parejas − PUNTOS_FALLO * fallos`. Y las
  cartas giradas también: las encontradas, más dos por cada fallo, más una si hay pendiente. Al guardarlos,
  `estadoValido()` tendría que comprobar que cuadran, y no lo hace: un `puntos=1000` cambiado con F12 se acepta.
  Calcularlos elimina dos campos ocultos y ese problema a la vez.
- **Validación más fina.** `estadoValido()` no comprueba que `letras` no tenga repetidas (con `letras=AA` en un
  2 × 2, un tablero de cuatro A pasa la validación) ni que cada fila tenga **exactamente** `columnas` cartas: la
  condición `count($todas) !== $filas * $columnas` siempre es falsa, porque el bucle ya añade justo esa cantidad,
  y una carta de más como `cartas[0][9]=Z` se acepta y se dibuja. Se arregla con `is_array($fila)` y
  `count($fila) === $columnas` para cada fila.
- **Avisos en el formulario.** En `validarEntrada()`, el valor pegajoso de un número no válido se obtiene con
  `(string) ($datos['filas'] ?? '')`; si llega `filas[]=1`, convertir un array en texto produce el aviso
  *Array to string conversion*. Se evita con la misma idea que en las letras: `is_string(...) ? ... : ''`.
- **Interfaz.** El botón *Nueva partida* se ve durante toda la partida; el enunciado dice que aparece al terminar.
  Bastaría con envolverlo en `if ($estado !== 'jugando')`, o dejarlo y documentar la decisión.
- Las ampliaciones del enunciado (jugadas, emojis, un solo campo con JSON, dos jugadores) son la siguiente
  **vuelta** del proceso. Con emojis, por ejemplo, `str_split()` y `strlen()` dejan de servir y hay que pasar a
  `mb_str_split()`; y en lugar del tablero se podría guardar solo la semilla y volver a barajar en cada petición.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| `>=` en lugar de `>` para perder | Se pierde un fallo antes de lo que dice el enunciado | Probar el ejemplo "con 3 permitidos, al 4.º fallo" |
| Guardar las cartas del fallo en el estado | Un dato más que hay que vaciar y validar | Devolverlas aparte (`$temporales`) |
| Sumar `giradas` antes de comprobar la carta | Un clic no válido hace aparecer el marcador | Validar primero con `letraEn()` |
| Leer `$_POST['cartas'][$f][$c]` sin `?? null` | *Undefined array key* si falta una carta | `?? null` e `is_string` |
| Validar solo que cada carta es una letra | Pasa un tablero con tres A y una B | Comparar las letras ordenadas con `letras` duplicadas |
| Aceptar cualquier posición en `encontradas` | Media pareja o la misma carta dos veces | `array_unique` y `array_count_values` |
| Rechazar `fallos = maxFallos + 1` | La partida perdida se ve como "no válida" | Ese estado también es posible |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué las dos cartas de un fallo no se guardan en un campo oculto? **Porque solo se ven en esa respuesta;
>   en la siguiente petición vuelven a estar boca abajo sin hacer nada, así que basta con una variable.**
> - ¿Qué llega en `$_POST['cartas']` si el formulario tiene los campos `cartas[0][0]` … `cartas[1][2]`? **Un array
>   de dos filas con tres letras cada una: PHP reconstruye el array a partir de los corchetes del `name`.**
> - ¿Para qué sirven las funciones provisionales del paso 3? **Para escribir ya la vista completa y que la
>   página funcione; en los pasos 4 y 5 se cambia su contenido sin tocar el HTML.**
> - ¿Por qué `jugar()` suma `giradas` después de comprobar la carta? **Porque una carta que no se puede elegir no
>   es una jugada: no debe cambiar nada, tampoco hacer aparecer el marcador.**
> - ¿Cómo comprueba `estadoValido()` que cada letra está exactamente dos veces? **Ordena las letras del tablero y
>   las de `letras` duplicadas, y las compara.**
> - ¿Por qué se permiten `maxFallos + 1` fallos en un estado válido? **Porque es el número de fallos de una
>   partida perdida, que también es una partida posible.**
