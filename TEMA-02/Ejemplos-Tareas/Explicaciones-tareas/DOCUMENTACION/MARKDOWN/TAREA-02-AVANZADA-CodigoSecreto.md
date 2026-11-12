# Código secreto (TAREA-02, nivel avanzado): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-AVANZADA-CodigoSecreto/ENUNCIADO.md) ·
[requisitos](../TAREA-02-AVANZADA-CodigoSecreto/Requisitos.md) ·
[autómata (HTML)](../TAREA-02-AVANZADA-CodigoSecreto/DIAGRAMA%20ESTADO-TRANSICION/Automata-CodigoSecreto.html) ·
[autómata (PDF)](../TAREA-02-AVANZADA-CodigoSecreto/DIAGRAMA%20ESTADO-TRANSICION/Automata-CodigoSecreto.pdf) ·
[solución](../TAREA-02-AVANZADA-CodigoSecreto/SOLUCION-01/) ·
[paso a paso](../TAREA-02-AVANZADA-CodigoSecreto/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)
y [el método de los diagramas de estado-transición ampliados](../COMUN/metodo-automatas/Metodo-Automatas.html)

> **Caso práctico**
>
> La biblioteca del instituto organiza un concurso de lógica y **Juan** le encarga a **Carlos** un juego de código
> secreto, con una condición: antes de programar quiere ver el **diagrama de estado-transición ampliado** y las
> tablas del autómata. **María** añade otra: "En un concurso habrá quien mire el código fuente de la página". Carlos
> sonríe: "Pues el código no estará en la página". "¿Y cómo sabrá el servidor, en el intento siguiente, cuál era?",
> le pregunta María. Carlos se queda pensando: la tarea no empieza por las fichas de colores, sino por decidir qué
> datos viajan, cuáles se **calculan** y en qué estado está la página en cada momento.

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización. El diseño se hace con el método de los autómatas, y el
código se corresponde con sus tablas fila a fila.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis y el autómata (apartados 1 y 2) antes de
leer el nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "PASO A PASO/paso3" "PASO A PASO/paso4"
```

(desde la carpeta `SOLUCION-01`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 8 requisitos (R1…R8) que [Requisitos.md](../TAREA-02-AVANZADA-CodigoSecreto/Requisitos.md)
convierte en 7 requisitos funcionales con criterios de aceptación. Agrupados por lo que significan para el
programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Diseñar con el autómata | R1, R2 · RF-01, RNF-02 | Diagrama y tablas antes del código; constantes `ST0`…`ST4` y `EE0`…`EE6`, tabla `$automata`, un `case` por transición y **una función por evento saliente**. |
| Configurar y empezar | R3, R4 · RF-02, RF-03 | Validar posiciones, colores e intentos con todos los errores; formulario pegajoso; código secreto **calculado**, nunca enviado. |
| Jugar | R5, R6 · RF-04, RF-05 | Validar el intento, contar negras y blancas, historial, intentos restantes; una pista que cuesta un intento. |
| Terminar | R7 · RF-06 | Ganar, perder (sin intentos o rindiéndose), marcador, nueva partida, volver a la configuración. |
| No fiarse del cliente | R8 · RF-07, RNF-03, RNF-04 | La partida recibida tiene que ser **posible en ese estado**; toda la salida escapada. |

Los requisitos no funcionales que más condicionan el código son **RNF-03** (el código secreto no aparece en el HTML
mientras se juega) y **RNF-02** (el código sigue el autómata).

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Qué color enseña la pista? | El de la **primera** posición que el jugador no ha acertado **en ningún intento** (`elegirPista()`). |
| ¿La pista cuenta como intento en el historial? | No: no se añade al historial, pero `intentosRestantes()` resta 1 si se ha pedido. |
| ¿Se gana o se pierde si el último intento acierta? | Se gana: PL3 (acierta) se comprueba antes que PL4 (último intento). |
| ¿Qué semilla usa *Nueva partida*? | La siguiente (`semilla + 1`), con la misma configuración y el marcador. |
| ¿Qué muestra el formulario si un dato no es válido? | El valor por defecto de ese dato (4, 6 o 10) y el error; los válidos se conservan. |
| ¿Cómo pruebo siempre con el mismo código? | Si la petición de *Empezar* trae un campo `semilla` (entero ≥ 1), se usa; si no, `random_int(1, 999999)`. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario, en el README o en la tabla de
> descriptores del autómata). Así quien corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de
> la tarea.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

El enunciado da la pista clave para probar: con la **semilla 1**, 4 posiciones, 6 colores y sin repetir, el código
es rojo, morado, verde, naranja. Con eso, los criterios de aceptación de `Requisitos.md` se convierten en una tabla de
casos con resultados exactos:

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Abrir la página | Formulario de configuración y marcador a cero (estado 1) |
| 2 | Posiciones 6, colores 9, intentos 20 (o `posiciones[]`…) | "El código debe tener 4 o 5 posiciones.", "Se juega con 6, 7 u 8 colores." y "Los intentos deben ser un entero entre 6 y 12."; sigue en estado 1, sin avisos |
| 3 | 4, 6, 10, sin repetir, semilla 1 | "Adivina el código: 4 posiciones, 6 colores, sin repetir"; estado 2, "Te quedan 10 intentos." |
| 4 | Rojo, verde, azul, amarillo | "1 negras y 1 blancas.", quedan 9, `intentos` = `0123` |
| 5 | Una posición sin color / rojo dos veces / un color `<b>` o 7 | "Elige un color para cada una de las 4 posiciones." / "…no se repiten…"; el historial no cambia, sin `<b>` en la página |
| 6 | *Pista* | "Pista: en la posición 2 va" morado; quedan 8 |
| 7 | *Pista* otra vez / con un solo intento | "Ya has usado la pista de esta partida." / "No te quedan intentos para pagar la pista." |
| 8 | Rojo, morado, verde, naranja | "¡Has descubierto el código en 2 intentos!", el código y estado 3 |
| 9 | *Nueva partida*, *Rendirse*, *Nueva*, *Rendirse*, *Cambiar la configuración* | "Ganadas: 1", `semilla` 2; "Te has rendido."; el formulario con "Ganadas: 1 · Perdidas: 2" |
| 10 | 6 intentos, repetibles, semilla 1, seis fallos | "Se han acabado los intentos." (estado 4) |
| 11 | *Probar* en la configuración, `estado=7`, `evento[]` | La configuración con el marcador a cero |
| 12 | Semilla 1 con `intentos=0514`, `01234`, `0193`, `pista=7`, `semilla=0`, `maxintentos=99`, `colores=<script>…` | "La partida recibida no es válida…" y la configuración, sin `<script>` en la página |

Los casos 11 y 12 son **maliciosos**: solo se provocan cambiando la página con F12 o enviando la petición con
`curl`. El caso 12 con `intentos=0514` es especialmente interesante: `0514` **es** el código secreto (rojo = 0,
morado = 5, verde = 1, naranja = 4), así que esa partida ya habría terminado.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada intento es una petición nueva y el script tiene que reconstruir la partida a
partir de lo que envía el formulario. Pero hay un dato que **no puede** viajar: el código secreto.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Estado del autómata | int (`ST1`…`ST4`) | campo oculto `estado` | Decide qué fila de la tabla se usa |
| Configuración | posiciones, colores, intentos, repetir | formulario en ST1; campos ocultos en ST2…ST4 | Ver la "trampa de los formularios", más abajo |
| Semilla | int ≥ 1 | campo oculto | **En lugar del código**: el código se calcula a partir de ella |
| Intentos hechos | array de arrays, viaja como `"0123-4512"` | campo oculto `intentos` | Con ellos se calculan el historial, las negras, las blancas y los restantes |
| Pista | int (posición, `-1` si no se ha pedido) | campo oculto | Una por partida |
| Marcador | ganadas, perdidas | campos ocultos (en todos los estados) | Sobrevive de una partida a otra |
| Código secreto | array de índices de color | **se calcula** en cada petición (AC4) | Si viajara, Ctrl+U lo revelaría |
| Negras, blancas, restantes | int | **se calculan** | Guardarlos permitiría datos contradictorios |

> **Debes conocer**
>
> **El código secreto no es una variable de estado: es una acción.** Se guarda la semilla y el servidor recalcula el
> código en cada petición con una **clave** que solo él conoce (`CLAVE` en `funciones.php`). Aunque el jugador vea la
> semilla en el código fuente, no puede calcular el código sin la clave. Y como la semilla siempre da el mismo
> código, el servidor "recuerda" la partida sin guardar nada.

La partida es un array asociativo (comentario de cabecera de `funciones.php`):

```php
['posiciones' => 4, 'colores' => 6, 'maxIntentos' => 10, 'repetir' => false, 'semilla' => 1234,
 'intentos' => [[0, 1, 2, 3], ...], 'pista' => -1, 'ganadas' => 0, 'perdidas' => 0]
```

## 2.- Diseño: el autómata

### 2.1.- Estados, eventos y la tabla de eventos-estados

El [autómata](../TAREA-02-AVANZADA-CodigoSecreto/DIAGRAMA%20ESTADO-TRANSICION/Automata-CodigoSecreto.html)
tiene 5 estados (ST0 carga, ST1 configuración, ST2 jugando, ST3 ganada, ST4 perdida), 7 eventos entrantes (la carga
y un botón por acción, todos en el campo `evento`) y 7 transiciones:

| | Carga | Empezar | Probar | Pista | Rendirse | Nueva | Configurar |
|---|---|---|---|---|---|---|---|
| ST0 | T1 | T1 | T1 | T1 | T1 | T1 | T1 |
| ST1 Configuración | T1 | T2 | --- | --- | --- | --- | --- |
| ST2 Jugando | T1 | --- | T3 | T4 | T5 | --- | --- |
| ST3 Ganada | T1 | --- | --- | --- | --- | T6 | T7 |
| ST4 Perdida | T1 | --- | --- | --- | --- | T6 | T7 |

Una pregunta que el método obliga a hacerse: si ganada y perdida admiten **los mismos eventos**, ¿por qué son dos
estados? Porque la página que se muestra es distinta (ES3 y ES4) y lo que se ha sumado al marcador también. Un estado
es una situación de la página, no solo la lista de botones que tiene.

### 2.2.- Las transiciones como evento [condición] / acción

| Transición | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| T1 | Carga o `---` | AC0: configuración por defecto, marcador a cero | ST1 |
| T2 | Empezar [!PL0 configuración no válida] | mostrar los errores | ST1 |
| T2 | Empezar [PL0] | AC3: semilla nueva, sin intentos ni pista | ST2 |
| T3 | Probar [!PL1 partida imposible] | AC0, aviso | ST1 |
| T3 | Probar [PL1 && !PL2 intento no válido] | aviso con el motivo | ST2 |
| T3 | Probar [PL1 && PL2 && PL3 acierta] | AC6 añadir, AC7 ganadas++ | ST3 |
| T3 | Probar [PL1 && PL2 && !PL3 && PL4 último] | AC6, AC9 perdidas++ | ST4 |
| T3 | Probar [PL1 && PL2 && !PL3 && !PL4] | AC6, "n negras y m blancas" | ST2 |
| T4 | Pista [!PL1 / !PL5 / PL5] | aviso / motivo / AC8 elegir la pista | ST1 / ST2 / ST2 |
| T5 | Rendirse [!PL1 / PL1] | aviso / AC9 perdidas++ | ST1 / ST4 |
| T6 | Nueva [!PL0 / PL0] | aviso / AC3 con la misma configuración | ST1 / ST2 |
| T7 | Configurar | conservar el marcador | ST1 |

**T3 tiene cinco caminos**, y el autómata incluye su **comprobación de predicados**: una tabla de verdad en la que
cada fila tiene exactamente un destino. Dos decisiones salen de ella:

- **PL3 antes que PL4.** Si el último intento acierta, se gana; con el orden contrario, se perdería una partida
  ganada.
- **PL4 se evalúa después de una acción.** "Era el último intento" solo tiene sentido después de AC6 (añadir el
  intento). Como AC6 se hace en todos los caminos con PL2, no cambia el resultado de los demás predicados.

### 2.3.- Dónde está la desconfianza: PL1

En ST2 llegan en campos ocultos la configuración, la semilla, los intentos y la pista. **PL1** ("partida posible")
comprueba que juntos describen una partida que puede existir **en ST2**: configuración válida, semilla positiva,
intentos bien formados, ninguno igual al código (si no, la partida ya habría terminado) y que quedan intentos. Está
en T3, T4 y T5, las tres transiciones que salen de ST2.

> **Debes conocer**
>
> **La trampa de los formularios.** En ST1 el formulario tiene los campos `posiciones`, `colores` y `maxintentos`. Si
> AC10 escribiera también campos ocultos con esos nombres, el navegador mandaría **los dos** valores y PHP se
> quedaría con el último (el oculto): la configuración que elige el jugador no serviría de nada. Por eso, en ST1,
> `guardar()` solo escribe el estado y el marcador.

### 2.4.- Divide y vencerás: del autómata a los ficheros y funciones

`index.php` contiene el autómata: constantes, tabla `$automata`, `switch` con un `case` por transición y los
**eventos salientes como funciones** (R2): `es0()` (configuración, con errores opcionales: ES0/ES1), `es2()` (el
juego) y `esFinal()` (ES3/ES4), con sus auxiliares `marcador()`, `ficha()` e `historial()`, más `guardar()` (AC10).
`funciones.php` tiene las acciones y los predicados largos, sin HTML:

| Función | Elemento del autómata | Paso |
|---|---|---|
| `e()`, constantes (`COLORES`, `CLAVE`…) | — | 2 |
| `nuevaPartida()` | AC3 (y AC0 con la configuración por defecto) | 2 |
| `textoIntentos()` | parte de AC10: `[[0,1,2,3]]` a `"0123"` | 2 |
| `validarConfiguracion()` | AC2; PL0 es "no hay errores" | 3 |
| `codigoSecreto()` | AC4 | 3 |
| `leerPartida()` | AC1 (sin comprobar: eso es PL1) | 3 |
| `leerIntento()` | AC5; PL2 es "el intento no es `null`" | 4 |
| `puntuar()`, `intentosRestantes()` | negras y blancas; base de PL4 y PL5 | 4 |
| `elegirPista()` | AC8 | 5 |
| `partidaValida()` | PL1 | 6 |

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden sigue al
autómata: el esqueleto, después las transiciones en el orden en que se recorren al jugar (empezar, probar, pista y
rendirse, terminar) y al final PL1, que protege a las de ST2.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición a partir del autómata, solo con TODOs | R1 |
| 2 | Esqueleto: constantes, tabla, estado y evento, T1, ES0 y AC10 | R2, R3 |
| 3 | T2 *Empezar*: configuración, código secreto, ES2 | R3, R4 |
| 4 | T3 *Probar*: intento, negras y blancas, historial, fin de la partida | R5, R7 |
| 5 | T4 *Pista* y T5 *Rendirse* | R6, R7 |
| **6** | **Solución completa:** T6, T7 y PL1 `partidaValida()` | R7, R8 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el autómata al código como comentarios, sin programar nada todavía.

**Por qué empezar así.** Siete transiciones, seis predicados y once acciones son demasiadas piezas para tenerlas en
la cabeza. Los TODO son el plan escrito dentro del código: en `funciones.php`, la **firma** de cada acción y
predicado con su nombre del autómata; en `index.php`, cada evento saliente (con sus partes) y cada transición:

```php
// TODO: AC5 leerIntento(array $datos, array $partida): array -> [intento o null, motivo]; PL2 es "intento no null"
// TODO: PL1 partidaValida(array $partida): bool -> ¿es una partida posible en ST2 (jugando)?
```

**Decisión clave.** La cabecera de `funciones.php` ya dice que el código secreto **no** se guarda en la página y
cómo se calcula (`hash_hmac`). Es la decisión del apartado 1.4, y todo lo demás depende de ella.

> **Pruébalo**
>
> Abre la página: sale el título y nada más, pero **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code
> verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa se borra.

### 3.2.- Paso 2: el esqueleto del autómata

**Objetivo.** Que el autómata exista en PHP: constantes, tabla, estado y evento recibidos, T1 con su página (ES0) y
los campos ocultos (AC10).

**Por qué ahora.** Sin esqueleto no hay dónde colgar las transiciones. Con él, cada paso siguiente consiste en añadir
un `case` y las funciones que necesita.

**Qué se escribe.**

- Las constantes, con `EVENTOS` como traductor del botón al evento: `'probar' => EE2`, `'pista' => EE3`…
- El evento recibido, en una línea que no da avisos con ninguna entrada:

```php
$evento = EVENTOS[is_string($_POST['evento'] ?? null) ? $_POST['evento'] : ''] ?? EE0;
```

- El `switch ($automata[$estado][$evento] ?? 1)`: el `?? 1` convierte las celdas `---` (que valen `null`) en T1. Y
  `case 1:` va junto a `default:`, de modo que una transición que todavía no está programada se comporta como T1
  en lugar de dejar la página en blanco.
- `es0()`, el formulario de configuración pegajoso (con `selected`, `checked` y `value`) y el marcador.
- `guardar()`, que ya resuelve la trampa del apartado 2.3: si `$estado !== ST1`, añade la configuración, la semilla,
  los intentos y la pista; si no, solo el estado y el marcador.

> **Para saber más**
>
> En los campos ocultos, la opción de repetir colores se llama `repite` y vale `'1'` o `'0'`, mientras que la casilla
> del formulario se llama `repetir`. No es casualidad: una casilla **sin marcar no se envía**, así que en el
> formulario se pregunta con `isset($datos['repetir'])`; en un campo oculto, en cambio, el valor tiene que viajar
> siempre, sea cual sea.

> **Pruébalo**
>
> Caso 1. Con Ctrl+U, comprueba que en ST1 solo hay tres campos ocultos: `estado`, `ganadas` y `perdidas`. Pulsa
> *Empezar*: vuelve al formulario, porque T2 todavía no existe y cae en el `default`. Con F12 cambia `estado` por
> `7`: también.

### 3.3.- Paso 3: T2, empezar la partida

**Objetivo.** Validar la configuración (PL0), crear la partida y calcular el código secreto (R3, R4).

**Por qué ahora.** Sin partida no se puede probar nada del juego. Y es el momento de comprobar la decisión más
delicada (el código no está en el HTML) antes de construir nada encima.

**Qué se escribe.**

- `validarConfiguracion()` devuelve `[configuración, errores]` y acumula **todos** los errores. `filter_var()` con
  `FILTER_VALIDATE_INT` devuelve `false` también cuando llega un array (`posiciones[]`), así que no hacen falta
  comprobaciones aparte. Para los intentos usa `min_range` y `max_range`.
- `leerPartida()` (AC1): en T2 solo sirve para recuperar el marcador, pero ya lee todos los campos sin avisos (los
  enteros que no valen toman un valor por defecto) y guarda el texto original de los intentos en `textoIntentos`,
  que necesitará PL1.
- `codigoSecreto()` (AC4), el corazón de R4:

```php
mt_srand(crc32(hash_hmac('sha256', (string) $partida['semilla'], CLAVE)));
```

`hash_hmac()` mezcla la semilla con la clave y devuelve un texto hexadecimal; `crc32()` lo convierte en el entero
que necesita `mt_srand()`. A partir de ahí, sin repetir se baraja la lista de colores con `shuffle()` y se toman
las primeras posiciones (`array_slice`); con repetición, un `mt_rand()` por posición.

- `es2()` provisional: solo el título ("Adivina el código: …") y el marcador. El formulario del intento llega en
  el paso 4.

> **Debes conocer**
>
> **`shuffle()` usa el mismo generador que `mt_rand()`.** Después de `mt_srand($n)`, baraja siempre igual. Por eso la
> misma semilla da siempre el mismo código, y por eso el formulario puede mandar una `semilla` para probar: con la
> semilla 1 (4 posiciones, 6 colores, sin repetir) el código es rojo, morado, verde, naranja. Es un ejemplo de
> diseñar pensando en **cómo vas a probar**.

> **Pruébalo**
>
> Caso 2 (y con F12, `posiciones[]`: los mismos errores, sin avisos). Caso 3: el formulario no tiene campo
> `semilla`, así que añádelo con F12 (`<input name="semilla" value="1">`) o usa `curl -d
> "evento=empezar&posiciones=4&colores=6&maxintentos=10&semilla=1" http://localhost:8000/`. Con Ctrl+U, comprueba
> que hay `name="semilla" value="1"` y que **no** aparece ningún color del código. Con el depurador, pon un punto de
> ruptura al final de `codigoSecreto()`: debe devolver `[0, 5, 1, 4]`.

### 3.4.- Paso 4: T3, probar un intento

**Objetivo.** Leer y validar el intento, puntuarlo, añadirlo al historial y terminar la partida si acierta o si no
quedan intentos (R5, R7).

**Por qué ahora.** Es el corazón del juego y la transición con más caminos. Con la partida creada en el paso 3 ya se
puede jugar de verdad.

**Qué se escribe.**

- `leerIntento()` (AC5) devuelve `[intento, motivo]`: `null` y un mensaje si falta una posición o el color no está
  en el rango (`max_range` es `colores - 1`), o si se repite un color cuando no está permitido
  (`count(array_unique($intento)) < count($intento)`).
- `puntuar()`: las negras posición a posición; las blancas, como dice la pista del enunciado, con
  `array_count_values()` de los dos códigos, el mínimo de cada color y restando las negras.
- `intentosRestantes()`: `maxIntentos` menos los intentos hechos. El TODO "la pista cuesta un intento" sigue dentro:
  se resolverá en el paso 5.
- `es2()` completa: el historial (con `historial()`), los restantes y un `<select>` por posición que **recuerda el
  último intento** (`end($partida['intentos'])`). `esFinal()` para ganar y perder, con el código descubierto.
- El `case 3`, con los cinco caminos en el orden de la tabla de verdad. Fíjate en dónde está AC6:

```php
$partida['intentos'][] = $intento;                              // AC6: añadir el intento
$pl3 = $intento === $secreto;                                   // PL3: acierta
$pl4 = intentosRestantes($partida) === 0;                       // PL4: era el último intento
```

**Un valor provisional.** PL1 todavía no existe, así que el código deja `$pl1 = true;` con su TODO: el camino
`!PL1` ya está escrito (vuelve a la configuración con el aviso), pero nunca se toma. El paso 6 sustituye esa línea
por la llamada a `partidaValida()`. Avanzar con lo más sencillo que funcione y arreglarlo después es desarrollo
incremental, siempre que no se olvide (para eso están los TODO).

> **Error típico**
>
> Contar las blancas como "colores del intento que están en el código" con `in_array()`. Con el código rojo, rojo,
> verde, naranja y el intento rojo, verde, rojo, rojo, contarías cuatro coincidencias de color en vez de tres: el
> tercer rojo del intento no tiene pareja. La solución correcta (1 negra y 2 blancas) sale del **mínimo** de cada
> color en los dos códigos.

> **Pruébalo**
>
> Casos 4, 5 y 8 con la semilla 1. Rojo, verde, azul, amarillo: "1 negras y 1 blancas." y el campo `intentos` vale
> `0123`. Deja una posición en `--`, repite el rojo, y con F12 cambia un `value` por `7` o por `<b>`: el historial
> no cambia. Después, rojo, morado, verde, naranja: "¡Has descubierto el código en 2 intentos!" y el código. Para el
> caso 10, empieza con 6 intentos y colores repetibles. En la página final todavía no hay botones para seguir: llegan
> en el paso 6.

### 3.5.- Paso 5: T4 pista y T5 rendirse

**Objetivo.** La pista, que cuesta un intento (R6), y rendirse (R7).

**Por qué ahora.** Las dos salen de ST2 igual que T3 y comparten su estructura: AC1, PL1 y AC4. Con *Probar*
funcionando, ya hay intentos sobre los que calcular una pista.

**Qué se escribe.**

- `intentosRestantes()` resuelve su TODO: `$gastados += $partida['pista'] >= 0 ? 1 : 0;`.
- `elegirPista()` (AC8) recorre el código y devuelve la primera posición que ningún intento ha acertado.
- T4 y T5 comparten `case`; T5 se separa con `if ($evento === EE4)`. PL5 es una línea:

```php
$pl5 = $partida['pista'] < 0 && intentosRestantes($partida) >= 2;   // PL5: se puede pedir la pista
```

- `es2()` muestra la pista ("Pista: en la posición 2 va …") y los botones *Pista (cuesta un intento)* y *Rendirse*.

Fíjate en que la pista se guarda como una **posición** (`pista = 1`) y no como un color: el color se vuelve a sacar
del código calculado en cada petición, igual que todo lo demás. Así la pista no revela nada en el código fuente que
no revele ya la página.

> **Pruébalo**
>
> Casos 6 y 7 (tras el primer intento del caso 4: "posición 2", morado, quedan 8). Y la prueba que prepara el paso 6:
> con la semilla 1, cambia con F12 el campo `intentos` a `0514` y pulsa *Probar* con cualquier intento válido. El
> historial muestra una fila con 4 negras… y la partida sigue, aunque ya estaba ganada. Cambia también `pista` a
> `7`: PHP avisa de *Undefined array key 7* al buscar el color de esa posición y la página se corta con un error.
> Son las dos caras del mismo problema: el paso 5 todavía se fía de los campos ocultos.

### 3.6.- Paso 6 (solución completa): terminar y no fiarse del navegador

**Objetivo.** Las transiciones desde las páginas finales, T6 *Nueva partida* y T7 *Cambiar la configuración* (R7), y
PL1 `partidaValida()` en T3, T4 y T5 (R8).

**Por qué al final (y no al principio).** PL1 usa casi todo lo anterior: `codigoSecreto()` (paso 3), para saber si
algún intento ya acertaba, e `intentosRestantes()` con la pista (paso 5), para saber si quedan intentos. Hasta ahora
el juego funcionaba con un **cliente honrado**; este paso lo prepara para uno que no lo es. Gracias al esqueleto, en
T3, T4 y T5 el cambio es una línea: `$pl1 = true;` pasa a ser `$pl1 = partidaValida($partida);`.

`partidaValida()` comprueba en este orden:

1. La configuración, la semilla (≥ 1) y la pista (de `-1` a `posiciones - 1`).
2. El formato de los intentos con una expresión regular **construida** a partir de la configuración ya validada:
   con 4 posiciones y 6 colores, cada grupo es `[0-5]{4}`.
3. Que ningún intento sea el código (la partida habría terminado) y que queden intentos.

```php
$grupo = '[0-' . ($partida['colores'] - 1) . ']{' . $partida['posiciones'] . '}';
if ($partida['textoIntentos'] !== '' && !preg_match("/^$grupo(-$grupo)*$/D", $partida['textoIntentos'])) {
```

El orden importa: la expresión regular solo tiene sentido con `colores` y `posiciones` ya comprobados (con
`colores=0` el patrón sería `[0--1]`), y `codigoSecreto()` solo se llama cuando la configuración es válida.

**T6 y T7.** *Nueva partida* recupera la configuración de los campos ocultos, comprueba PL0 y empieza otra con
`max(1, $anterior['semilla'] + 1)` y el mismo marcador. No puede reutilizar `validarConfiguracion()` tal cual,
porque esta lee la casilla `repetir`, y en ST3/ST4 la opción viaja en el campo oculto `repite`. *Cambiar la
configuración* no tiene predicados en la tabla: en lugar de rechazar, **ajusta** cada valor (los intentos, a su
rango; las posiciones y los colores que no están en la lista, al valor por defecto) para que el formulario salga
pegajoso.

> **Debes conocer**
>
> **Una semilla predecible no es un problema.** *Nueva partida* usa la semilla siguiente, así que cualquiera puede
> adivinar que tras la 1 viene la 2. Da igual: sin `CLAVE` no se puede calcular el código a partir de la semilla. La
> seguridad está en la clave, que nunca sale del servidor, no en que la semilla sea secreta.

> **Pruébalo**
>
> Casos 9, 11 y 12. Repite la prueba del paso 5 (`intentos=0514` con la semilla 1): ahora sale "La partida recibida
> no es válida (¿se han modificado los campos ocultos?). Empieza otra." y el formulario. Prueba también `01234`,
> `0193`, `pista=7` y `pista=-5`, `semilla=0`, `maxintentos=99`, diez intentos de diez y
> `colores=<script>alert(1)</script>`: todas deben volver a la configuración, sin avisos de PHP y sin que se ejecute
> nada.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
PL1 se añade al principio de tres transiciones y una comprobación demasiado estricta rompería una partida normal. El
método propone **un camino por fila** de la tabla de transiciones (apartado 7 del autómata):

- [ ] Cada camino de T2, T3, T4, T5 y T6 recorrido al menos una vez (los de T3, con la tabla de verdad delante).
- [ ] Casos límite: acertar en el último intento (debe ganar); pedir la pista con exactamente 2 intentos (se puede)
      y con 1 (no); 5 posiciones con 8 colores; colores repetibles (el ejemplo de 1 negra y 2 blancas).
- [ ] El código no aparece en el código fuente mientras se juega, ni siquiera después de pedir la pista.
- [ ] El marcador sobrevive a *Nueva partida* y a *Cambiar la configuración*, y se pone a cero con un `---`.
- [ ] Maliciosos: arrays donde se espera un texto, intentos mal formados o ya acertados, pista fuera de rango, HTML
      en cualquier campo, eventos `---`.
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)`.

## 5.- Refactorización y mejoras posibles

La solución no tiene FIXME pendientes: los valores provisionales (`$pl1 = true;` en T3 y en T4/T5, el TODO de la
pista en `intentosRestantes()` y el `default` del `switch`) se resuelven en los pasos 5 y 6. Aun así, quedan
detalles que se podrían mejorar sin cambiar el comportamiento:

- La condición "configuración válida" está escrita **tres veces**: en `validarConfiguracion()`, en
  `partidaValida()` y en el `case 6`. Una función `configuracionValida(array $partida): bool` evitaría que un día se
  cambie un límite en un sitio y no en los otros.
- El bloque `!PL1` (volver a la configuración con el aviso) se repite en T3 y en T4/T5. Se podría comprobar PL1 una
  vez antes del `switch`, cuando la transición es 3, 4 o 5, como hace el ascensor con PL0.
- `partidaValida()` comprueba el formato de los intentos, pero no que respeten la regla de no repetir colores
  cuando la partida no lo permite. ¿Es una partida "posible" la que tiene `0000` en el historial sin repetición?
- Los mensajes no distinguen singular y plural: "1 negras y 1 blancas.".
- Las ampliaciones del enunciado (*Deshacer*, dos jugadores, puntuación) son la siguiente **vuelta** del proceso y
  empiezan otra vez por el autómata. En el modo de dos jugadores, el código lo escribe una persona y ya no sale de
  una semilla: ¿cómo viajaría sin que se pudiera leer?

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Guardar el código en un campo oculto | Ctrl+U lo revela | Guardar la semilla y calcularlo con la clave (AC4) |
| Repetir la configuración en campos ocultos en ST1 | PHP se queda con el oculto y se ignora lo que elige el jugador | En ST1, solo el marcador |
| `mt_srand(hash_hmac(...))` sin `crc32()` | `hash_hmac()` devuelve un texto, no un entero | `crc32()` del resultado |
| Comprobar PL4 antes que PL3 | Se pierde una partida ganada en el último intento | El orden de la tabla de verdad |
| Evaluar PL4 antes de añadir el intento | Se juega un intento de más | PL4 después de AC6 |
| Contar las blancas con `in_array()` | Más blancas de las reales con colores repetidos | `array_count_values()` y el mínimo |
| Olvidar que la pista cuesta un intento | Un intento gratis | `intentosRestantes()` resta la pista |
| Construir la expresión regular antes de validar la configuración | Un patrón sin sentido con `colores=0` | Validar primero, construir después |
| Fiarse de los intentos recibidos | Una partida ya ganada sigue abierta | PL1: ninguno igual al código |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué el código secreto no es una variable de estado? **Porque viajaría en la página y se podría leer con el
>   código fuente. Se calcula en cada petición (AC4) a partir de la semilla y de una clave que solo tiene el
>   servidor.**
> - ¿Por qué ganada y perdida son dos estados si admiten los mismos eventos? **Porque la página que se muestra es
>   distinta (ES3 y ES4) y lo que se ha sumado al marcador también.**
> - ¿Qué pasa si en ST2 llegan los intentos `0514` con la semilla 1? ¿Y en el paso 5? **En la solución, PL1 es falso
>   (ese intento es el código: la partida ya habría terminado) y se vuelve a la configuración. En el paso 5, la
>   partida sigue como si nada.**
> - ¿Por qué en ST1 los campos ocultos solo llevan el estado y el marcador? **Porque el formulario ya tiene los
>   campos de la configuración: si se repitieran ocultos, PHP se quedaría con el último y se ignoraría lo elegido.**
> - En T3, ¿por qué PL3 se comprueba antes que PL4? **Porque si el último intento acierta, la partida se gana.**
> - ¿Es un problema que la semilla de *Nueva partida* sea la anterior más uno? **No: sin la clave del servidor no se
>   puede calcular el código a partir de la semilla.**
