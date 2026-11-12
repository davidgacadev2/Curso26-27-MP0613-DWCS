# Siete y media (TAREA-02, nivel avanzado): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-AVANZADA-SieteYMedia/ENUNCIADO.md) ·
[requisitos](../TAREA-02-AVANZADA-SieteYMedia/Requisitos.md) ·
[autómata](../TAREA-02-AVANZADA-SieteYMedia/DIAGRAMA%20ESTADO-TRANSICION/Automata-SieteYMedia.html)
([PDF](../TAREA-02-AVANZADA-SieteYMedia/DIAGRAMA%20ESTADO-TRANSICION/Automata-SieteYMedia.pdf)) ·
[solución](../TAREA-02-AVANZADA-SieteYMedia/SOLUCION-01/) ·
[README](../TAREA-02-AVANZADA-SieteYMedia/SOLUCION-01/README.md) ·
[paso a paso](../TAREA-02-AVANZADA-SieteYMedia/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html) ·
[método de los autómatas](../COMUN/metodo-automatas/Metodo-Automatas.html)

> **Caso práctico**
>
> El departamento de Matemáticas pide a **BK Programación** una **siete y media** contra la banca para un taller de
> probabilidad. **Carlos** ya hizo un tres en raya contra el servidor y reconoce el problema: cuando el jugador se
> planta, la banca tiene que jugar **sin que el navegador envíe nada más**. **María** le recuerda el *evento
> interno* de los diagramas ampliados y añade una advertencia: "Las cartas que quedan en el mazo no pueden estar en
> la página. Si están, cualquiera sabrá con Ctrl+U cuándo le conviene pedir carta". **Juan** remata: "Entonces,
> ¿qué guardas, si no puedes guardar las cartas?".

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes (análisis,
diseño, codificación incremental, pruebas y refactorización) con una particularidad: aquí el diseño **es** el
autómata (R1), y el código se escribe **a partir de él** (R2).

**Cómo usarlo.** Lee primero el enunciado e intenta dibujar tú el autómata antes de abrir el
[documento del autómata](../TAREA-02-AVANZADA-SieteYMedia/DIAGRAMA%20ESTADO-TRANSICION/Automata-SieteYMedia.html).
Después, para cada paso del apartado 3, compara su carpeta con la anterior:

```bash
git diff --no-index "PASO A PASO/paso3" "PASO A PASO/paso4"
```

(desde la carpeta `SOLUCION-01`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 9 requisitos (R1…R9) que [Requisitos.md](../TAREA-02-AVANZADA-SieteYMedia/Requisitos.md)
convierte en 6 requisitos funcionales con criterios de aceptación muy concretos. Agrupados:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Diseño | R1, R2 · RF-01, RNF-02 | Diagrama ampliado con un estado **transitorio** y un **evento interno**; tablas; código que sigue la tabla `$automata[estado][evento]`. |
| Cartas | R3, R4 · RNF-03 | Baraja de 40, figuras a medio punto, "3 y media". El mazo se **recalcula**: no viaja. |
| Apostar | R5 · RF-02 | De 5 a 50 y no más que el saldo; si no vale, se explica y se vuelve a pedir. |
| Jugar la mano | R6, R7 · RF-03, RF-04 | Pedir, pasarse, 7 y media (se planta solo), plantarse; la banca roba mientras tenga menos; empates para la banca; pago doble. |
| Terminar | R8 · RF-05 | Siguiente mano, retirarse, sin fichas, nueva partida; saldo y manos siempre visibles. |
| No fiarse | R9 · RF-06, RNF-04 | Los datos deben ser **posibles en el estado** en el que dicen estar; nada sin escapar, ningún aviso. |

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Qué pasa si llega un evento que no toca en ese estado (*Pedir* sin mano)? | Se trata como la carga: `$automata[$estado][$evento] ?? 1` (T1, partida nueva). |
| ¿Qué pasa si llega `estado=3`? | ST3 nunca se envía; si llega, se trata como ST0 (primera carga). |
| ¿*Nueva partida* es un evento nuevo? | No: el botón manda `evento=nueva`, que no está en `EVENTOS`, y eso es EE0 (*Carga*). |
| ¿Cuándo se descuenta la apuesta? | Al **perder** la mano; mientras se juega, el saldo incluye lo apostado (por eso la apuesta debe "caber" en él). |
| ¿Cómo se prueba siempre con las mismas cartas? | La **primera** petición (sin campo `estado`) puede mandar `semilla`; con la 7, la primera mano reparte caballo de oros, 3 de oros, 4 de copas, 2 de bastos y 6 de copas. |
| ¿Con qué texto se dice el resultado al retirarse? | "Te retiras con 60 fichas: pierdes 40." (o "ganas N." si se tiene más de 100). |

> **Recomendación**
>
> Las dos primeras filas no son detalles: son **celdas de la tabla de eventos-estados**. Si cada `---` de la tabla
> tiene una respuesta decidida, el programa no puede llegar a una situación que no se haya pensado.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son caminos completos por el autómata. Con la semilla 7:

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Primera petición con `semilla=7` | "Fichas: 100" y `estado` 1 |
| 2 | Apostar `abc`, `51`, `importe[]` o `<script>` | "La apuesta debe ser un número entero de fichas entre 5 y 50.", sin `<script>` en la página |
| 3 | Apostar 10 | "Mano 1", "Caballo de oros", "Tus cartas: media" y `estado` 2 |
| 4 | Pedir | "3 de oros", "Tus cartas: 3 y media" |
| 5 | Pedir otra vez | "4 de copas", 7 y media: la banca saca "2 de bastos" y "6 de copas" ("La banca: 8"), "La banca se pasa: ganas 10 fichas.", "Fichas: 110" (`estado` 4) |
| 6 | *Siguiente mano* | "Manos: 1 (ganadas 1, perdidas 0)", `estado` 1 y `cartas` 0 |
| 7 | Mano 2: apostar 50 y plantarse con el rey | "La banca gana con 5: pierdes 50 fichas." y "Fichas: 60" |
| 8 | *Retirarse* con 60 | "Te retiras con 60 fichas: pierdes 40." (`estado` 6) |
| 9 | Mano 3 (sota, as, 4, 3) apostando 20 | "Te has pasado (8 y media): pierdes 20 fichas." sin cartas de la banca |
| 10 | Semilla 12, mano 2 (7 de bastos), apostando 10, pedir | "Caballo de oros", "ganas 20 fichas (¡siete y media con dos cartas: paga doble!)" y "Fichas: 120" |
| 11 | Pasarse apostando las 20 últimas fichas | "Te has pasado (8 y media) y te has quedado sin fichas." (`estado` 5) |
| 12 | `estado=3`, `evento[]`, *Pedir* sin mano | Partida nueva: "Fichas: 100" y `estado` 1 |
| 13 | ST2 con 7 y media, apuesta 60, manos mal contadas, semilla 0, `saldo[]`… | "La partida recibida no es válida" y `estado` 1 |

Los casos 12 y 13 son **maliciosos**: hay que editar los campos ocultos con F12 o enviar la petición con `curl`.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada botón es una petición nueva y el script tiene que reconstruir la partida a partir
de los campos ocultos. La decisión central es **qué no se guarda**: las cartas.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Baraja, dinero, límite, clave | constantes | `funciones.php` | `PALOS`, `VALORES`, `FIGURAS`, `SALDO_INICIAL`, `LIMITE = 7.5`, `CLAVE` |
| Estado del autómata (VE0) | int | campo oculto `estado` | Nunca vale ST3 en la página |
| `saldo`, `apuesta` (VE1) | int | campos ocultos | Fichas |
| `semilla`, `manos` (VE2) | int | campos ocultos | De ellas sale el mazo de la mano |
| `cartas` (VE3) | int | campo oculto | **Cuántas** cartas tiene el jugador, no cuáles |
| `ganadas`, `perdidas` (VE4) | int | campos ocultos | Marcador; además permiten comprobar `manos` |
| Las cartas del jugador y de la banca | array de `[palo, valor]` | **se calculan** con `mazo()` | Si viajaran, Ctrl+U enseñaría la siguiente |
| Puntos | float | **se calculan** con `puntos()` | Dependen de las cartas |

> **Debes conocer**
>
> **Un mazo que se puede volver a barajar igual.** Si el mazo se baraja con un generador inicializado con la misma
> semilla, sale **siempre en el mismo orden**. Así, guardando la semilla de la partida y el número de mano, el
> servidor puede rehacer el mazo en cada petición: el jugador tiene las `cartas` primeras y la banca roba las
> siguientes. La semilla viaja en la página, pero el mazo se obtiene de ella con `hash_hmac()` y una **clave que
> solo conoce el servidor**: sin la clave, la semilla no sirve para adivinar las cartas.

La partida queda como un array asociativo (cabecera de `funciones.php`, desde el paso 1):

```php
['saldo' => 100, 'apuesta' => 10, 'semilla' => 1234, 'manos' => 3, 'cartas' => 2, 'ganadas' => 1, 'perdidas' => 1]
```

## 2.- Diseño: el autómata

### 2.1.- Divide y vencerás: ficheros y funciones

Dos ficheros. `funciones.php` tiene las acciones (AC) y los predicados (PL) que necesitan más de una línea, **sin
HTML**. `index.php` tiene las constantes del autómata, la tabla, las funciones de los **eventos salientes** (ES, que
escriben HTML) y el bucle de las transiciones. Cada función lleva en el comentario el código del autómata al que
corresponde, de modo que el diagrama y el código se pueden leer en paralelo:

| Función | Código del autómata | Fichero | Paso |
|---|---|---|---|
| `partidaNueva()` / `leerPartida()` | AC0 / AC1 | funciones | 2 |
| `resumen()` / `es0()` / `guardar()` | ES0, ES1 / AC9 | index | 2 |
| `leerApuesta()` | AC2 y PL1 | funciones | 3 |
| `mazo()` / `puntos()` / `nombreCarta()` / `textoPuntos()` / `cartasJugador()` | (auxiliares) | funciones | 3 |
| `mano()` / `es2()` | ES2 | index | 3 |
| `juegaBanca()` / `esFinMano()` | AC5 / ES3, ES4 | funciones / index | 4 |
| `esFinPartida()` | ES5, ES6 | index | 5 |
| `partidaValida()` | PL0 | funciones | 6 |

### 2.2.- Estados, eventos y transiciones

Siete estados (ST0 carga, ST1 apuesta, ST2 turno del jugador, **ST3 turno de la banca**, ST4 mano terminada, ST5 sin
fichas, ST6 retirado) y siete eventos entrantes, seis del navegador y uno **interno** (EE4, el turno de la banca).
La tabla de eventos-estados del documento del autómata pasa al código **tal cual**:

```php
//                Carga Apostar Pedir Plantarse Interno Siguiente Retirarse
$automata = [
    ST0 => [1, 1, 1, 1, null, 1, 1],
    ST1 => [1, 2, null, null, null, null, 7],
    ST2 => [1, null, 3, 4, null, null, null],
    ST3 => [null, null, null, null, 5, null, null],
    // ... ST4 => [1, null, null, null, null, 6, 7], ST5 y ST6 solo con T1
];
```

La fila de ST3 y la columna *Interno* tienen **una sola celda**: en ST3 solo puede ocurrir el evento interno y el
evento interno solo puede ocurrir en ST3. Las transiciones, en forma *evento [condición] / acción*:

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Cualquiera | *Carga*, *Nueva partida* o evento imposible (T1) | AC0: 100 fichas, semilla nueva | ST1 |
| ST1, ST2, ST4 | cualquier evento salvo T1 [!PL0: datos imposibles] | AC0 con aviso (ES1) | ST1 |
| ST1 | *Apostar* [!PL1] (T2) | explicar el error | ST1 |
| ST1 | *Apostar* [PL1] (T2) | AC3: manos + 1, una carta | ST2 |
| ST2 | *Pedir* [PL2 se pasa && PL4 sin fichas] (T3) | AC6 pierde | ST5 |
| ST2 | *Pedir* [PL2 && !PL4] (T3) | AC6 pierde | ST4 |
| ST2 | *Pedir* [!PL2 && PL3 justo 7 y media] (T3) | — | **ST3** |
| ST2 | *Pedir* [!PL2 && !PL3] (T3) | — | ST2 |
| ST2 | *Plantarse* (T4) | — | **ST3** |
| ST3 | *Interno* [PL5 la banca se pasa] (T5) | AC5, AC7 gana (doble con PL6) | ST4 |
| ST3 | *Interno* [!PL5 && PL4] (T5) | AC5, AC6 pierde | ST5 |
| ST3 | *Interno* [!PL5 && !PL4] (T5) | AC5, AC6 pierde | ST4 |
| ST4 | *Siguiente* (T6) | AC8: sin apuesta ni cartas | ST1 |
| ST1, ST4 | *Retirarse* (T7) | — | ST6 |

**Dos caminos llevan a ST3** (plantarse y llegar a 7 y media) y **una sola transición lo atiende** (T5). Es la razón
de dibujar el turno de la banca como un estado aunque nunca llegue al navegador: la banca juega igual venga de
donde venga, y su código se escribe una vez. Fíjate también en que PL4 (sin fichas) se evalúa **después** de AC6
(perder la apuesta): es un predicado sobre el resultado de una acción.

> **Para saber más**
>
> El documento del autómata incluye la **comprobación de predicados** de T3 y T5 (R1): una tabla con todas las
> combinaciones de PL2, PL3 y PL4 (o PL5, PL6 y PL4) que demuestra que cada combinación lleva a **un único**
> camino. Por ejemplo, PL2 y PL3 no pueden ser ciertos a la vez (no se puede tener más de 7 y media y justo 7 y
> media), así que con PL2 cierto PL3 se marca como "x".

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden sigue el
autómata: primero el **esqueleto** (que ya sabe a qué transición ir), después las transiciones **en el orden en que
se juega** una mano y, al final, la validación del estado.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición a partir del autómata, solo con TODOs | R1 |
| 2 | El esqueleto: constantes, tabla, estado y evento, bucle del evento interno, T1, ES0 y AC9 | R2 |
| 3 | T2 *Apostar*: `leerApuesta()`, `mazo()`, `puntos()`, la primera carta y ES2 | R3, R4, R5 |
| 4 | T3 *Pedir*, T4 *Plantarse* y T5, el evento interno: `juegaBanca()`, pago doble, ES3 y ES4 | R6, R7 |
| 5 | T6 *Siguiente mano*, T7 *Retirarse* y el final sin fichas (PL4, ES5, ES6) | R8 |
| **6** | **Solución completa:** PL0, `partidaValida()` | R9 |

### 3.1.- Paso 1: descomponer el problema a partir del autómata

**Objetivo.** Escribir el plan del código con los nombres del autómata, sin programar nada.

**Por qué empezar así.** Con el autómata hecho, la descomposición es casi mecánica: cada fila de la **tabla de
descriptores** es una constante o una función, y cada transición, un `case`. Los TODO usan los mismos códigos (ST,
EE, AC, PL, ES) para que se pueda ir del diagrama al código y volver:

```php
// TODO: AC5 juegaBanca(array $partida): array -> la banca roba cartas (las siguientes) mientras tenga menos que el jugador
// TODO: PL0 partidaValida(int $estado, array $partida): bool -> ¿son posibles estos datos en este estado?
```

En `index.php` los TODO siguen el orden en que se ejecutará cada petición: 1) estado, evento y transición; 2) AC1 y
PL0 antes de cualquier transición salvo T1; 3) la transición, con el evento interno; 4) AC9, guardar.

**Decisión clave.** Ya en el paso 1 el comentario de cabecera dice que **ST3 nunca llega al navegador** y que las
cartas no viajan: las dos ideas del caso práctico están en el plan antes que en el código.

> **Pruébalo**
>
> Abre la página: título y un formulario vacío, **sin errores de PHP**. Con *Todo Tree* verás la lista del trabajo.

### 3.2.- Paso 2: el esqueleto del autómata

**Objetivo.** Que cada petición sepa **en qué estado está**, **qué evento ha llegado** y **qué transición toca**, con
el bucle del evento interno ya escrito, aunque solo funcione T1.

**Por qué ahora.** Es el armazón del que cuelga todo lo demás: a partir de aquí, programar una transición es
**rellenar un `case`**. Y conviene escribir el bucle del evento interno desde el principio, cuando todavía no hay
nada que lo use, para no tener que reorganizar el `switch` en el paso 4.

**Qué se escribe.**

- Las constantes `ST0`…`ST6`, `EE0`…`EE6`, `EVENTOS` (el `value` de cada botón → su evento) y la tabla `$automata`.
- La lectura del estado y del evento, que ya trata los valores imposibles (código debajo de la lista).
- `leerPartida()` (AC1): cada campo con `filter_var(..., FILTER_VALIDATE_INT)` y, si no vale, `-1`. **No comprueba
  nada más**: eso será PL0. Así un `saldo[]` o un `<script>` no producen avisos y luego serán fáciles de rechazar.
- El bucle `do … while` alrededor del `switch`, con T1 en `case 1:` **y** en `default:`.
- `es0()` (el campo de la apuesta y el botón *Repartir*), `resumen()` y `guardar()` (AC9), que escapa cada valor.

```php
$estado = filter_var($_POST['estado'] ?? '', FILTER_VALIDATE_INT);
if ($estado === false || !isset($automata[$estado]) || $estado === ST3) {
    $estado = ST0;
}
$evento = EVENTOS[is_string($_POST['evento'] ?? null) ? $_POST['evento'] : ''] ?? EE0;
$transicion = $automata[$estado][$evento] ?? 1;      // un evento imposible ("---") se trata como T1
```

```php
do {
    switch ($transicion) {
        case 1:     // ---- T1: cualquier estado + Carga (o evento imposible, o !PL0) -> ST1, AC0, ES0 (ES1 con aviso)
        default:    //      (default: una transición que todavía no esté programada se trata igual)
            // ...
    }
    $transicion = $estado === ST3 ? $automata[ST3][EE4] : null;
} while ($transicion !== null);
```

**Dos trucos provisionales.** PL0 todavía no existe, así que el código deja `$pl0 = true;` con un TODO: es un valor
consciente y marcado que el paso 6 sustituye por `partidaValida()`. Y el `default:` hace que cualquier transición
sin programar (T2…T7) se comporte como T1: la aplicación **funciona en cada paso**, aunque haga menos.

> **Debes conocer**
>
> **La semilla es una herramienta de prueba.** T1 solo acepta `$_POST['semilla']` si **no** llega el campo `estado`,
> es decir, en la primera petición; en cualquier otra (incluida *Nueva partida*), usa `random_int(1, 999999)`. Para
> probar con la semilla 7 desde el navegador: F12, borra el campo oculto `estado`, pon 7 en el campo oculto
> `semilla` y pulsa cualquier botón. Es un ejemplo de diseñar pensando en **cómo vas a probar**.

> **Pruébalo**
>
> Casos 1 y 12. La página muestra "Fichas: 100 · Manos: 0 (ganadas 0, perdidas 0)" y el campo de la apuesta. Pulsa
> *Repartir*: como T2 aún no existe, el `default:` empieza otra partida (cambia el campo `semilla`). Con F12, pon
> `estado` a 3, o cambia el `name` del botón por `evento[]`: siempre una partida nueva, sin avisos.

### 3.3.- Paso 3: T2, apostar y recibir la primera carta

**Objetivo.** Validar la apuesta (PL1) y repartir la primera carta, con un mazo que **no viaja** (R3, R4, R5).

**Por qué ahora.** Es la primera transición de una mano, y obliga a resolver la decisión más delicada (de dónde
salen las cartas) antes de construir nada encima. Las funciones de cartas que se escriben aquí las usarán todas las
transiciones siguientes.

**Qué se escribe.**

- `leerApuesta()` (AC2 y PL1): `filter_var` con `min_range` 5 y `max_range` `min(APUESTA_MAXIMA, $saldo)`. Una sola
  llamada comprueba que es un entero, el mínimo, el máximo **y** el saldo.
- `mazo()`: las 40 cartas como pares `[palo, valor]`, barajadas con una semilla que sale de `hash_hmac()` (código
  debajo de la lista). El `'-' . manos` hace que cada mano tenga un mazo distinto.
- `puntos()` (`$valor <= 7 ? $valor : 0.5`), `nombreCarta()` (con `FIGURAS` para el as y las figuras) y
  `textoPuntos()`, que escribe 0.5 como "media" y 3.5 como "3 y media".
- `cartasJugador()`: `array_slice(mazo($partida), 0, $partida['cartas'])`.
- El `case 2:` con sus dos caminos, y `mano()` y `es2()` para pintar las cartas y los botones *Pedir carta* y
  *Plantarse*.

```php
// mazo() (paso 3, funciones.php)
mt_srand(crc32(hash_hmac('sha256', $partida['semilla'] . '-' . $partida['manos'], CLAVE)));
shuffle($cartas);
```

> **Error típico**
>
> Barajar con `mt_srand($partida['semilla'])` a secas. Funcionaría, pero la semilla está en la página: cualquiera
> podría copiar `mazo()` en su ordenador, ejecutarla con esa semilla y saber todas las cartas de la mano. Con
> `hash_hmac()` y la `CLAVE`, el resultado depende de un dato que **no** sale del servidor.

**Decisión clave.** Los predicados se guardan en **variables con su nombre** antes del `if`
(`$pl1 = $apuesta !== null;`), como pide R2: el `if (!$pl1)` se lee igual que la fila de la tabla de transiciones.

> **Pruébalo**
>
> Casos 2 y 3. Con la semilla 7, apuesta `abc`, `51` y `<script>` (y `importe[]` con F12): siempre el mismo mensaje
> de error y la página escapada. Apuesta 10: "Mano 1", "Caballo de oros", "Tus cartas: media". Con Ctrl+U comprueba
> que los campos ocultos no nombran ninguna carta: solo `cartas` con valor 1. Pulsa *Pedir carta*: T3 aún no
> existe y el `default:` empieza otra partida.

### 3.4.- Paso 4: pedir, plantarse y el evento interno

**Objetivo.** Las transiciones del turno del jugador (T3, T4) y el turno de la banca (T5), que se atiende en la
**misma petición** (R6, R7).

**Por qué ahora.** Es el corazón del juego y el motivo del autómata ampliado. Como el bucle del evento interno ya
existe desde el paso 2, T4 es literalmente una línea: `$estado = ST3;`. El bucle hace el resto.

**Qué se escribe.**

- `case 3:` (AC4 `cartas++`): `$pl2 = $puntos > LIMITE;` y `$pl3 = $puntos === LIMITE;`. Si se pasa, pierde la
  apuesta (AC6) y va a ST4; si tiene justo 7 y media, `$estado = ST3` (se planta solo); si no, ES2 otra vez.
- `case 4:` solo cambia el estado a ST3.
- `juegaBanca()` (AC5): toma las cartas **siguientes** a las del jugador mientras tenga menos puntos (código debajo
  de la lista).
- `case 5:` con PL5 (la banca se pasa) y PL6 (7 y media con **dos** cartas: paga doble).
- `esFinMano()` (ES3, ES4): las dos manos, el mensaje y el saldo. Si el jugador se ha pasado, la banca no juega y
  sus cartas no se muestran (`$banca !== []`).

```php
// juegaBanca() (paso 4, funciones.php)
while (puntos($banca) < $objetivo) {                // la banca gana los empates: le basta con igualar
    $banca[] = $mazo[$siguiente];
    $siguiente++;
}
```

> **Debes conocer**
>
> **Cómo funciona la segunda vuelta.** Al pulsar *Plantarse*, la primera vuelta del `do … while` ejecuta `case 4`
> y deja `$estado = ST3`. Al final de la vuelta, `$transicion = $estado === ST3 ? $automata[ST3][EE4] : null;` vale
> 5, el bucle se repite y `case 5` hace jugar a la banca, que deja el estado en ST4 (o ST5). Al final de esa
> segunda vuelta `$transicion` es `null` y se sale. **El navegador nunca ve ST3.** Pon un punto de ruptura en el
> `while` para verlo con Xdebug.

> **Para saber más**
>
> `7.5 === 7.5` es `true` y la comparación estricta de `$pl3` es segura, aunque normalmente no se deban comparar
> números en coma flotante con `===`: los medios puntos (0.5, 1.5…) se representan **exactamente** en binario, así
> que las sumas no acumulan error. Con décimas (0.1 + 0.2) no pasaría lo mismo.

**Lo que queda pendiente.** Dos TODO `PL4: sin fichas -> ST5, ES5`, uno en cada camino que pierde. En este paso, si
el jugador pierde sus últimas fichas, la partida se queda en ST4 con saldo 0. Y `esFinMano()` todavía no tiene los
botones para seguir.

> **Pruébalo**
>
> Casos 4 y 5 con la semilla 7: pedir → "3 de oros", "Tus cartas: 3 y media"; pedir → "4 de copas", 7 y media, y
> **en la misma respuesta** la banca saca "2 de bastos" y "6 de copas" ("La banca: 8"): "La banca se pasa: ganas
> 10 fichas.", "Fichas: 110" y `estado` 4 en el código fuente (nunca 3).

### 3.5.- Paso 5: siguiente mano, retirarse y sin fichas

**Objetivo.** Cerrar el ciclo de la partida: T6, T7 y el camino PL4 (R8).

**Por qué ahora.** Hasta ahora solo se podía jugar una mano. Con estas transiciones se pueden recorrer **todos los
caminos** del autómata, que es lo que necesita el paso 6 para comprobar que la validación no estropea nada.

**Qué se escribe.**

- Los dos TODO de PL4: si tras AC6 el saldo es 0, `$estado = ST5`, `esFinPartida()` y `break`.
- `case 6:` (AC8: `apuesta` y `cartas` a 0) y `case 7:` (el mensaje con la diferencia respecto a `SALDO_INICIAL`).
- Los botones: *Retirarse* en ES0, *Siguiente mano* y *Retirarse* en ES3/ES4 y *Nueva partida* en `esFinPartida()`
  (ES5, ES6), con `value="nueva"`, que el autómata interpreta como EE0.

> **Error típico**
>
> Pensar que el `break` del camino PL4 sale del bucle. Dentro de un `switch`, `break` sale **del `switch`**, no del
> `do … while`. Funciona porque, al llegar al final de la vuelta, el estado es ST5 y no ST3, así que
> `$transicion` vale `null` y el bucle termina. Lo que decide si hay otra vuelta es **el estado**, no el `break`:
> por eso hay que ponerlo antes de salir del `case`.

> **Pruébalo**
>
> Casos 6 a 9 y 11. *Siguiente mano*: "Manos: 1 (ganadas 1, perdidas 0)" y en el HTML `estado` 1 y `cartas` 0.
> Mano 2 apostando 50, plantarse con el rey: "La banca gana con 5: pierdes 50 fichas.", "Fichas: 60". *Retirarse*:
> "Te retiras con 60 fichas: pierdes 40.". Si en vez de retirarte sigues, la mano 3 (apostando 20) se pasa con 8 y
> media. Pierde todo: "…y te has quedado sin fichas." y solo el botón *Nueva partida*, que vuelve a "Fichas: 100" y
> "Manos: 0".

### 3.6.- Paso 6 (solución completa): PL0, no fiarse del navegador

**Objetivo.** Comprobar, antes de cualquier transición salvo T1, que los datos son **posibles en el estado** en el
que dicen estar (R9).

**Por qué al final (y no al principio).** Los invariantes de ST2 usan `puntos(cartasJugador())`, que no existe
hasta el paso 3, y solo se pueden enunciar bien cuando se conocen **todas** las transiciones: por ejemplo, "en ST2
el jugador no puede tener 7 y media" es cierto **porque** T3 lo manda a ST3 (paso 4). Hasta ahora el juego
funcionaba con un **cliente honrado**; este paso lo prepara para uno que no lo es. El único cambio en `index.php`
es sustituir `$pl0 = true;` por la llamada.

```php
// partidaValida() (paso 6, funciones.php), en ST2
return $partida['apuesta'] >= APUESTA_MINIMA && $partida['apuesta'] <= min(APUESTA_MAXIMA, $partida['saldo'])
    && $partida['manos'] >= 1 && $partida['ganadas'] + $partida['perdidas'] === $partida['manos'] - 1
    && $partida['cartas'] >= 1 && $partida['cartas'] <= 15 && puntos(cartasJugador($partida)) < LIMITE;
```

Cada condición sale de una pregunta: ¿puede haber una mano en juego sin cartas? ¿Puede la apuesta superar el saldo,
si el saldo todavía la incluye? ¿Cuántas manos se han cerrado si se está jugando la número `manos`? Antes, para
todos los estados, la semilla y el saldo deben ser al menos 1 y las manos ganadas y perdidas, no negativas; entre
manos (ST1, ST4), ganadas + perdidas = manos. Como `leerPartida()` convirtió los valores raros en `-1`, todos caen
aquí.

> **Pruébalo**
>
> Caso 13. Con F12: en ST2 (semilla 7, mano 1) pon `cartas` a 3 (7 y media), `apuesta` a 60, una apuesta mayor que
> el saldo, `manos` a 5, `semilla` a 0, `saldo` a `<script>` o su `name` a `saldo[]`; en ST1, apuesta con `saldo` 0;
> en ST4, `manos` negativo. Todos: "La partida recibida no es válida (¿se han modificado los campos ocultos?).
> Empieza otra." y `estado` 1, sin avisos ni nada sin escapar.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3: PL0 se evalúa antes de cada
transición y, si fuera demasiado estricto, rechazaría partidas honradas. El documento del autómata propone **un
camino por fila** de la tabla de transiciones:

- [ ] T2 con apuesta válida y no válida; T3 por sus cuatro caminos; T4; T5 por sus cuatro caminos (incluido el doble).
- [ ] T6, T7 desde ST1 y desde ST4, y T1 desde ST5 y ST6 (*Nueva partida*).
- [ ] Casos límite: apostar exactamente el saldo; empatar con la banca (gana la banca); 7 y media con tres cartas (no paga doble).
- [ ] Maliciosos: `estado=3`, eventos imposibles, `evento[]`, todos los de PL0.
- [ ] Ninguna carta sin mostrar en el código fuente; ningún aviso de PHP con `error_reporting(E_ALL)`.

## 5.- Refactorización y mejoras posibles

La solución sigue el autómata casi línea a línea, y eso ya es una forma de orden. Aun así hay detalles que
mejorar, buenos para practicar:

- **Un saldo que no llega a la apuesta mínima.** PL4 es `saldo === 0`, pero se puede llegar a ST1 con 1 a 4 fichas
  (por ejemplo, 100 − 50 − 47 = 3). Entonces el campo dice "Apuesta (5-3 fichas)", ninguna apuesta es válida y
  solo queda *Retirarse*. Definir PL4 como `saldo < APUESTA_MINIMA` terminaría la partida en ese caso.
- **Datos posibles no es lo mismo que datos auténticos.** PL0 rechaza lo imposible, pero no lo que es posible y
  falso: en ST2, el jugador puede subir con F12 su `apuesta` a 50 después de ver una buena carta, y la partida
  sigue siendo "posible". Firmar los campos ocultos con `hash_hmac()` (la misma función que ya baraja el mazo) y
  la `CLAVE` detectaría cualquier cambio; lo mismo hace la solución 02 del Buscaminas con el cifrado autenticado.
- **El botón *Atrás*.** Después de pasarse, *Atrás* devuelve una página de ST2 perfectamente válida: el jugador ya
  sabe qué carta viene y puede plantarse. Ni PL0 ni una firma lo evitan; hace falta que el servidor recuerde el
  estado actual (sesiones, en temas posteriores).
- **La semilla de prueba.** Borrando el campo `estado` se puede elegir la semilla de una partida nueva. Es cómodo
  para probar, pero en producción se quitaría, igual que la `CLAVE` escrita en `funciones.php`.
- **La vista dentro de las transiciones.** Los ES escriben HTML con `echo` desde dentro del `switch`, que se ejecuta
  en medio del `<form>`. Es la traducción directa de "ESs" en la tabla de transiciones, pero se podría separar:
  cada `case` decidiría qué ES toca y con qué mensaje, y el HTML se generaría al final.
- Las ampliaciones del enunciado (*Doblar*, otra regla para la banca, estadísticas) son la siguiente **vuelta** del
  proceso, y empiezan por el autómata: *Doblar* es un evento nuevo en ST2 que, si el jugador no se pasa con la
  carta extra, lleva a ST3, así que T5 se reutiliza sin cambios.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Guardar las cartas en campos ocultos | Ctrl+U revela el mazo | Guardar semilla, mano y **cuántas** cartas (R4) |
| Barajar con la semilla sin clave | Cualquiera puede reproducir el mazo | `hash_hmac()` con una clave del servidor |
| Enviar ST3 al navegador o hacer que la banca juegue en otra petición | La banca necesita un clic más; el jugador podría no enviarlo | Bucle `do … while` con el evento interno |
| Programar la banca dos veces (al plantarse y al llegar a 7 y media) | Dos copias que acaban siendo distintas | Los dos caminos terminan en ST3 y T5 la atiende |
| `$puntos > 7` en lugar de `> LIMITE` | 7 y media cuenta como pasarse | La constante `LIMITE = 7.5` |
| Que la banca robe "mientras tenga menos o igual" | Los empates dejan de ser de la banca | `while (puntos($banca) < $objetivo)` |
| Validar el estado solo con `filter_var` | Partidas imposibles (7 y media en ST2, manos que no cuadran) | Invariantes por estado: PL0 |
| Leer `$_POST['evento']` sin `is_string` | Avisos o errores con `evento[]` | `is_string(...) ? ... : ''` y `?? EE0` |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué ST3 es un estado si nunca llega al navegador? **Porque dos transiciones (T3 y T4) terminan en él y desde
>   ahí el evento interno decide el resultado: la banca juega igual venga de donde venga, y se programa una vez.**
> - ¿Qué pasa si alguien manda `estado=3`? **Que se trata como ST0 y se empieza una partida nueva.**
> - ¿Por qué basta con guardar cuántas cartas tiene el jugador? **Porque el mazo de la mano se vuelve a barajar
>   igual a partir de la semilla y del número de mano; las del jugador son las primeras y las de la banca, las
>   siguientes.**
> - ¿Para qué sirve la clave de `hash_hmac()`? **Para que la semilla, que sí viaja en la página, no permita a nadie
>   fuera del servidor calcular el mazo.**
> - En ST2, ¿por qué es imposible tener 7 y media? **Porque al llegar a 7 y media T3 lleva a ST3 y, en la misma
>   petición, T5 lleva a ST4 o ST5.**
> - ¿Por qué PL0 llega en el último paso? **Porque sus invariantes usan funciones de pasos anteriores
>   (`cartasJugador()`, `puntos()`) y solo se pueden enunciar cuando se conocen todas las transiciones.**
> - ¿Qué hace `$automata[$estado][$evento] ?? 1`? **Convierte cualquier celda `---` (un evento que no toca en ese
>   estado) en T1: partida nueva.**
