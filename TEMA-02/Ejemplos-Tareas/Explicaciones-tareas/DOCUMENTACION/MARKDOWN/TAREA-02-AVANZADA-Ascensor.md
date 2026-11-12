# El ascensor del instituto (TAREA-02, nivel avanzado): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-AVANZADA-Ascensor/ENUNCIADO.md) ·
[requisitos](../TAREA-02-AVANZADA-Ascensor/Requisitos.md) ·
[autómata (HTML)](../TAREA-02-AVANZADA-Ascensor/DIAGRAMA%20ESTADO-TRANSICION/Automata-Ascensor.html) ·
[autómata (PDF)](../TAREA-02-AVANZADA-Ascensor/DIAGRAMA%20ESTADO-TRANSICION/Automata-Ascensor.pdf) ·
[solución](../TAREA-02-AVANZADA-Ascensor/SOLUCION-01/) ·
[paso a paso](../TAREA-02-AVANZADA-Ascensor/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)
y [el método de los diagramas de estado-transición ampliados](../COMUN/metodo-automatas/Metodo-Automatas.html)

> **Caso práctico**
>
> El departamento de Tecnología quiere explicar en clase cómo funciona el ascensor del instituto y **Juan** le
> encarga a **Carlos** la simulación: cinco plantas, un botón *Llamar* en cada una y un ascensor que se mueve
> instante a instante. Carlos mira el panel del ascensor de verdad: *Parado*, *Subiendo*, *Bajando*, *Puertas
> abiertas*, *Emergencia*... "Esto ya es un autómata", piensa. **María** le añade una pregunta incómoda: "Vale, pero
> en cada estado, ¿qué cosas **no pueden pasar nunca**? Un ascensor que sube sin nadie esperando arriba, por
> ejemplo". Carlos entiende que la tarea no empieza por dibujar el edificio, sino por el autómata y por esas
> condiciones, los **invariantes**, que luego servirán para no fiarse de los campos ocultos.

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización. Lo particular de esta tarea es que el diseño **es** el
autómata: el diagrama, sus tablas y el código se corresponden fila a fila.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis y el autómata (apartados 1 y 2) antes de
leer el nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "PASO A PASO/paso3" "PASO A PASO/paso4"
```

(desde la carpeta `SOLUCION-01`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 7 requisitos (R1…R7) que [Requisitos.md](../TAREA-02-AVANZADA-Ascensor/Requisitos.md)
convierte en 5 requisitos funcionales con criterios de aceptación. Agrupados por lo que significan para el
programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Diseñar con el autómata | R1, R2 · RF-01, RNF-02 | Diagrama y tablas **antes** del código; constantes `ST0`…`ST6` y `EE0`…`EE4`, tabla `$automata`, un `case` por transición, predicados en `$pl0`, `$pl1`… |
| Llamar | R3, R4 · RF-02 | La planta va en el valor del botón (`llamar:3`); añadir sin repetir; abrir si está parado allí; nada si ya está abierto allí. |
| Moverse | R5 · RF-03 | Cada *Avanzar* es un instante; ir a la más cercana (empate: arriba) y **seguir en la misma dirección** mientras queden llamadas en ella. |
| Emergencia | R6 · RF-04 | Anular las llamadas; *Rearmar*; a la tercera, fuera de servicio. |
| No fiarse del cliente | R7 · RF-05, RNF-03, RNF-04 | Los datos de los campos ocultos deben ser posibles **en el estado en el que dicen estar** (invariantes); nunca salir del edificio. |

El requisito no funcional que más condiciona el código es **RNF-02**: el código tiene que *parecerse* al autómata.
No basta con que el ascensor funcione; quien corrija debe poder poner el diagrama al lado del `switch` y encontrar
cada flecha.

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Qué pasa si llaman desde la planta en la que el ascensor está **en marcha** (acaba de salir)? | Se añade la llamada como cualquier otra (T3): la atenderá cuando vuelva. |
| ¿Cuenta como parada abrir las puertas porque llaman desde la planta en la que ya está parado? | No: `paradas` solo sube en `parar()`, al **llegar** a una planta con llamada. |
| ¿Qué hace un evento que no tiene botón en ese estado (*Avanzar* en emergencia)? | Es una celda `---` de la tabla: se trata como T1 y se reinicia. |
| ¿Con cuántas emergencias queda fuera de servicio? | Con 3: constante `MAX_EMERGENCIAS`; la tercera se cuenta en T9 y es *Rearmar* (T10) quien pasa a ST6. |
| ¿Qué dirección tiene el ascensor parado? | Ninguna (`''`): el invariante de ST1 lo exige. |
| ¿Y si las puertas se abrieron sin viajar (T2)? | ST4 con dirección `''`; al cerrarlas (T8) no hay dirección que seguir y se usa la más cercana. |

> **Recomendación**
>
> Cuando una decisión sale del **diagrama** y no del enunciado (como "un `---` reinicia"), escríbela en la tabla de
> eventos-estados y en un comentario del código. Así queda documentada en los dos sitios a la vez y nadie la tomará
> por un olvido.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son, en realidad, un **recorrido** completo por el autómata. Conviene
escribirlos en una tabla antes de programar (los textos son los mensajes exactos del código):

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Abrir la página | "El ascensor espera en la planta baja." (estado 1) |
| 2 | Llamar a la planta baja estando en ella | "El ascensor ya está aquí: se abren las puertas en la Planta baja." (estado 4) |
| 3 | Reiniciar; llamar a la 3.ª y a la 1.ª | `llamadas` = `13` y los dos botones dicen "Esperando" |
| 4 | `llamar:9` o `llamar:<b>` | "Esa planta no existe.", sin `<b>` en la página |
| 5 | Desde el caso 3, *Avanzar* | "Se cierran las puertas y arranca hacia arriba." (la 1.ª está más cerca) |
| 6 | *Avanzar*; con las puertas abiertas en la 1.ª, llamar a la 1.ª y a la baja | "Llega a la 1.ª planta: se abren las puertas."; "Las puertas ya están abiertas en la 1.ª planta."; `llamadas` = `03` |
| 7 | *Avanzar*; en marcha, llamar a la 2.ª | "Se cierran las puertas y sigue subiendo." (aunque llamen desde la baja); `llamadas` = `023` |
| 8 | *Avanzar* hasta terminar | Para en la 2.ª y en la 3.ª; "Se cierran las puertas y baja."; "Pasa por la 2.ª planta."; "Pasa por la 1.ª planta."; llega a la Planta baja con "Plantas recorridas: 6" y "Paradas: 4" |
| 9 | *Avanzar* dos veces más | "Se cierran las puertas. No hay más llamadas." y "No hay llamadas: el ascensor sigue parado." |
| 10 | Parado en la 2.ª con llamadas en la baja y la 4.ª, *Avanzar* | Sube (empate) |
| 11 | Tres emergencias con sus rearmes | "Ascensor rearmado (emergencias: 1 de 3).", "2 de 3"; a la tercera, "Fuera de servicio: 3 paradas de emergencia. Avisa al técnico (Reiniciar)." (estado 6) |
| 12 | `estado=9`, `evento[]`, o *Avanzar* en emergencia | Se reinicia: "El ascensor espera en la planta baja." |
| 13 | `estado=2` con `piso=4` y sin llamadas, `piso=7`, `llamadas=31` o `33`, `direccion=x`, `piso[]`, `llamadas=<script>…` | "Los datos del ascensor no son válidos…" y estado 1, sin avisos de PHP |

Los casos 12 y 13 son **maliciosos**: solo se pueden provocar cambiando los campos ocultos con F12 o con `curl`.
Que estén aquí desde el principio obliga a pensar en ellos en el diseño (apartado 2.3).

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada pulsación de *Avanzar* es una petición nueva: el script empieza desde cero y tiene
que reconstruir el ascensor a partir de lo que envía el formulario. Hay que decidir el **mínimo** de datos que lo
describen:

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Estado del autómata | int (`ST1`…`ST6`) | campo oculto `estado` | Decide qué fila de la tabla se usa |
| Planta | int de 0 a 4 | campo oculto `piso` | Dónde está la cabina |
| Dirección | `''`, `sube` o `baja` | campo oculto `direccion` | **Hace falta también con las puertas abiertas** (ver la caja) |
| Llamadas pendientes | array de enteros, viaja como texto ordenado (`"024"`) | campo oculto `llamadas` | Un texto corto que se valida con una sola expresión regular |
| Emergencias | int | campo oculto | Para saber cuándo queda fuera de servicio |
| Plantas recorridas, paradas | int | campos ocultos | Contadores de R3 |
| Mensaje, aviso | string | variables | Solo duran la petición |

> **Debes conocer**
>
> **¿Por qué una variable `direccion` si ya hay estados *Subiendo* y *Bajando*?** Porque con las puertas abiertas
> (ST4) el ascensor no se mueve, pero al cerrarlas tiene que seguir en la dirección que llevaba (R5). Ese dato no se
> puede deducir de la planta ni de las llamadas: si está en la 2.ª con llamadas en la 0 y en la 4, solo la dirección
> dice si va a por la 4.ª o a por la baja. Lo que no se puede calcular, **se guarda**.

El ascensor queda como un array asociativo (comentario de cabecera de `funciones.php`):

```php
['piso' => 2, 'direccion' => 'sube', 'llamadas' => [0, 4], 'emergencias' => 0, 'recorridas' => 7, 'paradas' => 3]
```

## 2.- Diseño: el autómata

### 2.1.- Estados, eventos y la tabla de eventos-estados

El [autómata](../TAREA-02-AVANZADA-Ascensor/DIAGRAMA%20ESTADO-TRANSICION/Automata-Ascensor.html)
tiene 7 estados, 5 eventos entrantes y 10 transiciones. Lo especial de esta tarea es que **los estados de la página
son los del propio ascensor**: la pregunta del método "¿necesito saber algo de la petición anterior?" tiene una
respuesta física: si se mueve y hacia dónde.

| | Carga | Llamar | Avanzar | Emergencia | Rearmar |
|---|---|---|---|---|---|
| ST0 Carga | T1 | T1 | T1 | T1 | T1 |
| ST1 Parado | T1 | T2 | T5 | T9 | --- |
| ST2 Subiendo | T1 | T3 | T6 | T9 | --- |
| ST3 Bajando | T1 | T3 | T7 | T9 | --- |
| ST4 Puertas abiertas | T1 | T4 | T8 | T9 | --- |
| ST5 Emergencia | T1 | --- | --- | --- | T10 |
| ST6 Fuera de servicio | T1 | --- | --- | --- | --- |

Fíjate en la columna *Avanzar*: es el evento más interesante, porque hace **una cosa distinta en cada estado**
(arrancar, subir, bajar, cerrar puertas). Por eso tiene cuatro transiciones distintas. Las celdas `---` son eventos
que la página no permite (sus botones están deshabilitados): si llegan, alguien ha manipulado la petición.

### 2.2.- Las transiciones como evento [condición] / acción

Cada transición tiene uno o varios caminos, según sus predicados. Resumidas (la tabla completa, con acciones antes y
después, está en el apartado 5.3 del autómata):

| Transición | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| T1 | Carga, `---` o datos no válidos | AC0: planta baja, sin llamadas, contadores a 0 | ST1 |
| T2 | Llamar [!PL1 planta no válida] | "Esa planta no existe." | ST1 |
| T2 | Llamar [PL1 && PL2 es su planta] | abrir las puertas | ST4 |
| T2 | Llamar [PL1 && !PL2] | AC3 añadir la llamada | ST1 |
| T3 / T4 | Llamar en marcha / con puertas abiertas | AC3 (en T4 con PL2: "ya están abiertas") | el mismo |
| T5 | Avanzar parado [!PL3 / PL4 / !PL4] | nada / AC4 sube / AC4 baja | ST1 / ST2 / ST3 |
| T6, T7 | Avanzar subiendo o bajando: AC5 mover y después [PL5 llamada aquí] | AC6 parar | ST4 (si no, sigue) |
| T8 | Avanzar con puertas abiertas [!PL3 / PL6 / PL7 / PL4 / !PL4] | sin dirección / sigue / sigue / AC4 sube / AC4 baja | ST1 / ST2 / ST3 / ST2 / ST3 |
| T9 | Emergencia en ST1…ST4 | AC9: emergencias++, anular llamadas, sin dirección | ST5 |
| T10 | Rearmar [PL8 menos de 3 / !PL8] | mensaje | ST1 / ST6 |

La transición con más caminos es **T8** (cinco), y por eso el autómata incluye su **comprobación de predicados**:
una tabla de verdad que demuestra que cada combinación de PL3, PL6, PL7 y PL4 lleva a un solo destino. Esa tabla
fija también el **orden** de los `if`: PL6 y PL7 (seguir en la dirección que llevaba) se comprueban **antes** que
PL4 (la más cercana). Al revés, un ascensor que sube hacia la 4.ª se daría la vuelta en cuanto alguien llamara desde
una planta más cercana por debajo.

### 2.3.- Los invariantes: qué no puede pasar en cada estado

Es la idea que María le pedía a Carlos, y la que convierte el autómata en una herramienta de **validación**. Para
cada estado se escribe qué tienen que cumplir los datos:

| Estado | Invariante |
|---|---|
| todos | planta de 0 a 4; llamadas con cifras de 0 a 4, ordenadas y sin repetir; contadores ≥ 0; dirección `''`, `sube` o `baja`; emergencias de 0 a 2 (3 solo en ST5) |
| ST1 Parado | no hay llamada en su planta (la habría atendido abriendo las puertas) y no tiene dirección |
| ST2 Subiendo | hay alguna llamada por encima y la dirección es `sube` |
| ST3 Bajando | hay alguna llamada por debajo y la dirección es `baja` |
| ST4 Puertas abiertas | no hay llamada en su planta (se quitó al llegar) |
| ST5 Emergencia | no hay llamadas y ha habido al menos una emergencia |

El predicado **PL0** ("los datos cumplen los invariantes de su estado") está en **todas** las transiciones salvo
T1. Y los invariantes de ST2 y ST3 dan gratis el requisito RNF-04: si un ascensor sube, es porque hay una llamada más
arriba, así que **nunca puede pasar de la 4.ª**.

> **Debes conocer**
>
> **Un invariante es mucho más fuerte que validar cada campo por separado.** `estado=2` es válido, `piso=4` es
> válido y `llamadas` vacío es válido; pero los tres juntos describen un ascensor imposible (subiendo desde la última
> planta). Solo se detecta preguntando "¿es posible **en este estado**?".

### 2.4.- Divide y vencerás: del autómata a los ficheros y funciones

`index.php` tiene lo que **es** el autómata: las constantes, la tabla `$automata`, `evento()` (qué botón ha llegado),
`edificio()` (los eventos salientes ES0…ES4: todas las variantes de la página), `guardar()` (AC8) y el `switch`.
`funciones.php` tiene las acciones y los predicados que ocupan más de una línea, sin HTML:

| Función | Elemento del autómata | Paso |
|---|---|---|
| `e()` | Escapar para el HTML | 2 |
| `ascensorInicial()` | AC0 | 2 |
| `leerAscensor()` | AC1 recuperar (sin comprobar: eso es PL0) | 2 |
| `evento()`, `edificio()`, `guardar()` | EE, ES0…ES4, AC8 (en `index.php`) | 2 |
| `leerPlanta()` | AC2; PL1 es "no devuelve `null`" | 3 |
| `anadirLlamada()` | AC3 | 3 |
| `masCercanaArriba()` | PL4 | 4 |
| `hayLlamadas()` | base de PL6, PL7 (y de los invariantes de ST2/ST3) | 4 |
| `mover()` / `parar()` | AC5 / AC6 | 4 |
| `coherente()` | PL0: los invariantes, con un `match` | 6 |

Los predicados de una línea (PL2, PL3, PL5, PL8) no merecen una función: se escriben directamente en su variable
`$plN` dentro del `case`, que es lo que pide R2.

> **Recomendación**
>
> `anadirLlamada()`, `mover()` y `parar()` reciben el ascensor **por referencia** (`array &$ascensor`) porque son
> acciones: su trabajo es cambiarlo. Los predicados, en cambio, lo reciben por valor y solo devuelven `bool`. Esa
> separación (las acciones cambian, los predicados preguntan) es la misma que hay en las tablas del autómata.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden sigue al
autómata: primero el **esqueleto** que lo representa, después las transiciones de una en una (de la más sencilla a
la más compleja) y al final PL0, que las protege a todas.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición a partir del autómata, solo con TODOs | R1 |
| 2 | Esqueleto: constantes, tabla, `evento()`, `edificio()`, `guardar()`, AC1 y T1 | R2, R3 |
| 3 | T2: llamar con el ascensor parado | R4 |
| 4 | El movimiento: T5, T6/T7, T8 y las llamadas en marcha T3/T4 | R4, R5 |
| 5 | T9 (emergencia) y T10 (rearmar; fuera de servicio a la tercera) | R6 |
| **6** | **Solución completa:** PL0 con `coherente()` | R7 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el autómata al código como comentarios, sin programar nada todavía.

**Por qué empezar así.** Con diez transiciones y nueve predicados es fácil perderse. Los TODO son el plan escrito
dentro del propio código. En `funciones.php`, la **firma** de cada acción y predicado, con su nombre del autómata:

```php
// TODO: AC2 leerPlanta(array $datos): ?int -> la planta del botón "llamar:3" (null si no es válida); PL1 es "no es null"
// TODO: PL0 coherente(int $estado, array $ascensor): bool -> ¿son posibles estos datos EN ESTE ESTADO? (invariantes)
```

En `index.php`, las cuatro partes del programa en el orden en que se ejecutarán: (1) el estado, el evento y la
transición que dice la tabla; (2) AC1 y PL0 antes de cualquier transición salvo T1; (3) la transición; (4) el evento
saliente y AC8.

**Decisión clave.** Ya en el paso 1, la cabecera de `funciones.php` fija la estructura del ascensor (con la
dirección) y la de `index.php` dice que el estado se comprueba con los invariantes **antes** de cualquier
transición. Esas dos decisiones condicionan todo lo demás.

> **Pruébalo**
>
> Abre la página: sale el título y nada más, pero **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code
> verás la lista de lo que queda por hacer.

### 3.2.- Paso 2: el esqueleto del autómata

**Objetivo.** Que el autómata exista en PHP: constantes, tabla, evento recibido, T1, la página y los campos ocultos.

**Por qué ahora.** Sin esqueleto no hay dónde "colgar" las transiciones. Y con él, cada paso siguiente consiste
solo en añadir un `case`: el resto del programa ya no cambia.

**Qué se escribe.**

- Las constantes `ST0`…`ST6` y `EE0`…`EE4`, y la tabla con `null` donde el diagrama tiene `---`.
- `evento()` traduce el botón pulsado a un `EE` con un `match (true)`: `str_starts_with($valor, 'llamar:')` es
  *Llamar*; cualquier otra cosa (también *Reiniciar*) es la carga, EE0.
- `leerAscensor()` reconstruye el ascensor sin avisos: los enteros pasan por `filter_var(..., FILTER_VALIDATE_INT)`
  y, si no valen, se ponen a `-1`; las llamadas, con `array_map('intval', str_split($llamadas))`. Guarda también el
  texto original en `textoLlamadas`, que necesitará PL0 en el paso 6.
- `edificio()` dibuja las plantas **de arriba abajo** (`for ($planta = ULTIMA; $planta >= 0; $planta--)`), con la
  cabina, la flecha y los botones deshabilitados en ST5 y ST6. `guardar()` escribe siete campos ocultos, todos con
  `e()`.
- El `switch` solo tiene T1.

La línea más importante del paso es la que consulta la tabla:

```php
$transicion = $automata[$estado][$evento] ?? 1;                 // un evento imposible ("---") se trata como T1
```

El operador `??` trata el `null` de una celda `---` igual que una clave que no existe: en los dos casos sale T1. Y
para PL0 se deja un **valor provisional** consciente: `$pl0 = true;` con su TODO, que el paso 6 sustituirá por
`coherente()`.

> **Debes conocer**
>
> **`case 1:` y `default:` juntos.** El `switch` del paso 2 empieza así: `case 1:` seguido de `default:`. Mientras se
> construye paso a paso, una transición de la tabla que todavía no está programada (por ejemplo, T5) cae en el
> `default` y se comporta como T1, en lugar de dejar la página en blanco. Es un andamio que permite probar cada paso
> sin que falte nada; en la solución completa todas las transiciones tienen su `case` y el `default` solo recoge
> lo imposible.

> **Pruébalo**
>
> Caso 1: "El ascensor espera en la planta baja." y el edificio con la cabina abajo. Con Ctrl+U, comprueba los siete
> campos ocultos (`estado` vale 1). Pulsa cualquier botón: todos vuelven al principio, porque solo existe T1. Con
> F12, cambia `estado` por `9`: también.

### 3.3.- Paso 3: T2, llamar con el ascensor parado

**Objetivo.** La primera transición "de verdad": las llamadas desde ST1 (R4).

**Por qué ahora.** Es la más sencilla con predicados (PL1 y PL2, tres caminos) y la que **crea** las llamadas: sin
ellas, el movimiento del paso 4 no tendría a dónde ir.

**Qué se escribe.**

- `leerPlanta()` corta lo que va detrás de `llamar:` y lo valida con `FILTER_VALIDATE_INT` y el rango de 0 a
  `ULTIMA`. Devuelve `?int`: `null` si no vale, y PL1 es simplemente `$planta !== null`.
- `anadirLlamada()` añade la planta si no está y ordena con `sort()`: así `guardar()` escribe siempre un texto
  ordenado (`"13"`), que es lo que exigirá el invariante.
- El `case 2` copia la tabla de transiciones: un `if` por camino, en el mismo orden y con el predicado en un
  comentario.

```php
$pl1 = $planta !== null;                                // PL1: planta válida
$pl2 = $planta === $ascensor['piso'];                   // PL2: es la planta en la que está
if (!$pl1) {                                            // !PL1 -> ST1, ES2 con el error
```

> **Error típico**
>
> Comparar con `in_array($planta, $ascensor['llamadas'], true)` (estricto) cuando las llamadas son **textos**. Por
> eso `leerAscensor()` las convierte con `intval`: `'3' === 3` es falso, y sin la conversión la planta 3 nunca
> estaría "esperando".

> **Pruébalo**
>
> Casos 2, 3 y 4. Llama a la planta baja: se abren las puertas (estado 4). *Reiniciar* y llama a la 3.ª y a la 1.ª:
> el campo `llamadas` vale `13`. Con F12 cambia el valor de un botón por `llamar:9` y por `llamar:<b>`. Y una
> comprobación del andamio: pulsa *Avanzar*. Vuelve al principio, porque T5 todavía no existe y cae en el `default`.

### 3.4.- Paso 4: el movimiento

**Objetivo.** Que el ascensor se mueva: T5 (arrancar), T6 y T7 (subir y bajar), T8 (cerrar las puertas y seguir) y
las llamadas en marcha y con las puertas abiertas, T3 y T4 (R4, R5).

**Por qué ahora, y todo junto.** Estas transiciones forman un ciclo (ST1 → ST2 → ST4 → ST2…): por separado no se
pueden probar, porque cada una deja el ascensor en un estado cuya transición haría falta a continuación. T3 y T4 van
en el mismo paso porque solo tienen sentido con el ascensor en marcha o con las puertas abiertas.

**Qué se escribe.**

- `masCercanaArriba()` (PL4) calcula la distancia a la llamada más cercana por arriba y por abajo, empezando en
  `PHP_INT_MAX`, y devuelve `$arriba <= $abajo && $arriba !== PHP_INT_MAX`: el `<=` es el **empate hacia arriba**
  del enunciado, y la segunda condición descarta el caso de que no haya ninguna llamada por encima.
- `hayLlamadas($ascensor, 'arriba')` o `'abajo'`: con ella, PL6 y PL7 son una línea cada uno.
- `mover()` suma `+1` o `-1` y cuenta la planta; `parar()` quita la llamada de esa planta con `array_diff()` y
  cuenta la parada.
- T6 y T7 comparten `case`: solo cambia el signo (`mover($ascensor, $transicion === 6 ? 1 : -1)`). Y PL5 se evalúa
  **después** de mover: es "hay llamada en la planta a la que **acaba de llegar**".
- T3 y T4 también comparten `case`; el camino "ya están abiertas" se distingue con `$transicion === 4 && $pl2`.
- T8, con sus cinco caminos en el orden de la tabla de verdad:

```php
$pl6 = $ascensor['direccion'] === 'sube' && hayLlamadas($ascensor, 'arriba');   // PL6: subía y hay arriba
$pl7 = $ascensor['direccion'] === 'baja' && hayLlamadas($ascensor, 'abajo');    // PL7: bajaba y hay abajo
```

> **Debes conocer**
>
> **El algoritmo del ascensor colectivo está en el orden de los `if`.** En T8, `elseif ($pl6)` y `elseif ($pl7)` van
> antes que `elseif ($pl4)`. Ese orden es la regla "sigue en la dirección que llevaba mientras queden llamadas en
> ella". La tabla de verdad del autómata es lo que garantiza que los cinco caminos son completos (siempre se cumple
> uno) y excluyentes (nunca dos).

> **Pruébalo**
>
> Casos 5 a 10, en orden: es el recorrido de RF-03. Al final deben salir "Plantas recorridas: 6" y "Paradas: 4". Para
> el empate (caso 10), lleva el ascensor a la 2.ª y deja allí las llamadas en la baja y la 4.ª (o escríbelas con F12:
> `estado=1`, `piso=2`, `llamadas=04`): debe subir. Con el depurador, pon un punto de ruptura en el `case 8` y mira
> los valores de `$pl3`, `$pl6`, `$pl7` y `$pl4` en cada parada.

### 3.5.- Paso 5: emergencia y rearme

**Objetivo.** T9 (parada de emergencia) y T10 (rearmar; fuera de servicio a la tercera) (R6).

**Por qué ahora.** Son las transiciones que **salen** del ciclo normal y lo interrumpen desde cualquier estado de
ST1 a ST4. Con el movimiento funcionando, por fin hay algo que interrumpir.

**Qué se escribe.**

- La constante `MAX_EMERGENCIAS = 3`, que estaba como TODO desde el paso 1.
- Los botones *Parada de emergencia* (deshabilitado en ST5 y ST6) y *Rearmar* (habilitado **solo** en ST5).
  Deshabilitar los botones es lo que hace que las celdas `---` de la tabla no puedan darse... con un cliente honrado.
- T9: AC9 en cuatro líneas: `emergencias++`, llamadas a `[]`, dirección `''` y el mensaje "PARADA DE EMERGENCIA en
  la …".
- T10 con PL8: `$pl8 = $ascensor['emergencias'] < MAX_EMERGENCIAS;`.

Fíjate en que la tercera emergencia se **cuenta** en T9 (el ascensor sigue en ST5) y es el *Rearmar* siguiente el
que comprueba PL8 y lo deja fuera de servicio. Por eso el invariante general dice "emergencias de 0 a 2 (3 solo en
ST5)".

> **Pruébalo**
>
> Caso 11. Provoca una emergencia en marcha: "PARADA DE EMERGENCIA en la …", `llamadas` vacío y "Emergencias: 1".
> Rearma dos veces; a la tercera, "Fuera de servicio" (estado 6) y solo queda *Reiniciar*. Ahora la prueba
> importante del paso: con F12 pon `estado=2`, `piso=4`, `llamadas` vacío y pulsa *Avanzar*. El ascensor sube a una
> "planta 5" que no existe y PHP avisa de *Undefined array key 5* al buscar su nombre en `PLANTAS`. Es exactamente lo
> que arregla el paso 6.

### 3.6.- Paso 6 (solución completa): los invariantes

**Objetivo.** Programar PL0: comprobar que los datos recibidos son posibles en su estado (R7).

**Por qué al final (y no al principio).** PL0 usa `hayLlamadas()`, que no existía hasta el paso 4, y la constante
`MAX_EMERGENCIAS`, del paso 5. Además, escribir los invariantes exige conocer **todas** las transiciones: el de ST5
("sin llamadas") solo se entiende cuando T9 ya las anula. Hasta ahora el ascensor funcionaba con un **cliente
honrado**; este paso lo prepara para uno que no lo es. Y, gracias al esqueleto, el cambio en `index.php` es una sola
línea: `$pl0 = true;` pasa a ser `$pl0 = coherente($estado, $ascensor);`.

`coherente()` es casi una copia de la tabla del apartado 2.3: primero lo que vale en cualquier estado y después un
`match` con una línea por estado.

```php
return match ($estado) {
    ST1 => !$aqui && $ascensor['direccion'] === '',                                         // parado
    ST2 => hayLlamadas($ascensor, 'arriba') && $ascensor['direccion'] === 'sube',           // subiendo
```

La comprobación de las llamadas usa la expresión regular de las pistas del enunciado: `'/^0?1?2?3?4?$/D'` acepta
`"024"` y rechaza `"31"` (desordenado), `"33"` (repetido) y `"<script>"`, todo a la vez. El modificador `D` hace que
`$` no acepte un salto de línea al final. Se aplica al **texto** recibido (`textoLlamadas`), no al array: por eso
`leerAscensor()` lo guardaba desde el paso 2.

> **Para saber más**
>
> ¿Por qué `default => false` en el `match`, si ST6 es un estado válido? Porque `coherente()` nunca se llama en ST0
> ni en ST6: en esos estados todas las celdas de la tabla son T1 o `---` (que también es T1), y PL0 no se comprueba
> en T1. El `default` solo recoge lo imposible.

> **Pruébalo**
>
> Casos 12 y 13. Repite la prueba del paso 5 (`estado=2` con `piso=4`): ahora sale "Los datos del ascensor no son
> válidos (¿se han modificado los campos ocultos?). Se reinicia." Prueba también `piso=7`, `llamadas=31`,
> `llamadas=33`, `direccion=x`, `estado=1` con una llamada en su planta, `estado=5` con llamadas, 3 emergencias en
> ST1, `piso[]=1` y `llamadas=<script>alert(1)</script>`: todas deben reiniciar con el aviso, sin avisos de PHP y
> sin que se ejecute nada.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
PL0 se comprueba antes de todas las transiciones, y un invariante demasiado estricto rompería un camino que antes
funcionaba. El método propone **un camino por fila** de la tabla de transiciones (apartado 7 del autómata):

- [ ] Cada camino de T2, T4, T5, T8 y T10 recorrido al menos una vez (los de T8, con la tabla de verdad delante).
- [ ] El recorrido completo de RF-03: 6 plantas recorridas y 4 paradas.
- [ ] Casos límite: el empate de PL4; llamar desde la planta en la que está con las puertas abiertas; la tercera
      emergencia; *Reiniciar* desde fuera de servicio.
- [ ] Ninguna secuencia de botones saca el ascensor del edificio (RNF-04).
- [ ] Maliciosos: cada invariante de la tabla incumplido por separado, arrays donde se espera un texto, HTML en
      cualquier campo, eventos `---`.
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)`.

> **Recomendación**
>
> Para cada invariante, piensa la petición más pequeña que lo incumple **solo a él**. Si una prueba incumple tres a
> la vez, no sabrás cuál de las tres comprobaciones la ha detectado.

## 5.- Refactorización y mejoras posibles

La solución no tiene FIXME pendientes: los únicos valores provisionales (`$pl0 = true;` y el `default` del `switch`)
se resuelven en el paso 6. Aun así, quedan detalles que se podrían mejorar sin cambiar el comportamiento:

- T2 y T3/T4 repiten las tres mismas líneas (AC2, PL1 y PL2). Como AC2 es "acción antes" de las tres
  transiciones, se podría calcular una sola vez antes del `switch` cuando el evento es *Llamar*.
- Los mensajes de T5 dicen "Se cierran las puertas y arranca…", aunque en ST1 las puertas ya estaban cerradas.
- `coherente()` no comprueba relaciones entre contadores. Por ejemplo, `paradas` solo crece en `parar()`, que
  siempre va después de `mover()`: ¿es `paradas <= recorridas` un invariante de todos los estados? Si lo es,
  añadirlo detectaría una manipulación más.
- Las ampliaciones del enunciado (*Abrir puertas*, *Sobrecarga*, dos ascensores) son la siguiente **vuelta** del
  proceso, y cada una empieza otra vez por el autómata: ¿es un evento nuevo, un predicado nuevo o un estado nuevo?
  ¿Qué invariantes tiene?

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| No guardar la dirección con las puertas abiertas | Al cerrarlas, no sabe si seguir subiendo o bajar | Variable de estado `direccion` |
| Comprobar PL4 antes que PL6/PL7 en T8 | Se da la vuelta con llamadas pendientes en su dirección | El orden de la tabla de verdad |
| Evaluar PL5 antes de mover | Para en la planta de la que sale, no en la que llega | AC5 es "acción antes" de T6/T7 |
| Validar cada campo por separado | `estado=2` con `piso=4` pasa y el ascensor sale del edificio | Invariantes por estado (PL0) |
| Usar `PLANTAS[$piso]` sin validar | *Undefined array key* con `piso=7` o `piso[]` | PL0 antes de cualquier transición |
| Llamadas como texto e `in_array` estricto | Nunca encuentra la planta | `intval` al leerlas |
| Olvidar `sort()` en `anadirLlamada()` | El propio programa guarda `llamadas=31` y PL0 lo rechaza en la petición siguiente | Guardar siempre ordenado |
| Olvidar que un `---` puede llegar | *Rearmar* en ST1 hace cosas imprevistas | `?? 1`: todo `---` es T1 |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué la dirección es una variable de estado si ya hay estados *Subiendo* y *Bajando*? **Porque hace falta
>   también en ST4: con las puertas abiertas el ascensor no se mueve, pero al cerrarlas tiene que seguir en la
>   dirección que llevaba.**
> - Alguien envía `estado=2` con el ascensor en la 4.ª y sin llamadas. ¿Qué pasa en el paso 5? ¿Y en el 6? **En el
>   paso 5 sube a una planta que no existe (avisos de PHP); en el paso 6 no se cumple el invariante de ST2, PL0 es
>   falso y se reinicia con un aviso.**
> - ¿Por qué PL0 se comprueba una sola vez, antes del `switch`? **Porque está en todas las transiciones salvo T1:
>   si falla, la transición pasa a ser T1 con un aviso, y no hay que repetirlo en cada `case`.**
> - ¿Qué hace `$automata[$estado][$evento] ?? 1` con una celda `---`? **La celda vale `null` y `??` la trata como
>   inexistente: devuelve 1, es decir, T1.**
> - ¿Qué comprueba `/^0?1?2?3?4?$/D`? **Que las llamadas son cifras de 0 a 4, ordenadas y sin repetir, en una sola
>   expresión.**
> - ¿Por qué T5…T8 se programan en el mismo paso? **Porque forman un ciclo: cada una deja el ascensor en un estado
>   que necesita la siguiente, y por separado no se podrían probar.**
