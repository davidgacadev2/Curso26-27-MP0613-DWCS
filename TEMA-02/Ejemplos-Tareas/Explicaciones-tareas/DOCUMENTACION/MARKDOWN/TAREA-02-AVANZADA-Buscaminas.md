# Buscaminas (TAREA-02, nivel avanzado): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-AVANZADA-Buscaminas/ENUNCIADO.md) ·
[requisitos](../TAREA-02-AVANZADA-Buscaminas/Requisitos.md) ·
[solución 01](../TAREA-02-AVANZADA-Buscaminas/SOLUCION-01/) ·
[solución 02](../TAREA-02-AVANZADA-Buscaminas/SOLUCION-02/) ·
[paso a paso](../TAREA-02-AVANZADA-Buscaminas/SOLUCION%20PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> **Juan** reta a **Carlos**: "¿Serías capaz de programar un Buscaminas completo solo con PHP, sin una línea de
> JavaScript y sin sesiones?". Carlos acepta y abre el editor para dibujar la tabla de botones, pero **María** lo
> frena: "Un tablero de experto tiene 480 casillas. Cada clic es una petición nueva y el servidor no se acuerda de
> nada. ¿Cómo vas a mandar 480 casillas, dos veces (lo que hay y lo que se ve), en cada página? ¿Y cómo sabrás que
> el jugador no las ha tocado?". Carlos entiende que el primer problema no es el juego, sino **el formato del estado**.

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización.

**Un paso a paso para las dos soluciones.** La carpeta `SOLUCION PASO A PASO` está al nivel de la tarea porque
recorre las dos: el **paso 4** es idéntico a `SOLUCION-01` (solo cambia el comentario de cabecera de `index.php`) y
el **paso 5** es idéntico a `SOLUCION-02`. Es decir, primero se construye la solución que cumple R1–R10 y después,
sobre ella, se añaden las dos ampliaciones opcionales.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "SOLUCION PASO A PASO/paso3" "SOLUCION PASO A PASO/paso4"
```

(desde la carpeta `TAREA-02-AVANZADA-Buscaminas`; en VS Code: seleccionar los dos ficheros, botón derecho,
*Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 10 requisitos (R1…R10) que [Requisitos.md](../TAREA-02-AVANZADA-Buscaminas/Requisitos.md)
convierte en 13 requisitos funcionales y 22 no funcionales. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Configurar | R1 · RF-01…RF-03 | Niveles en `const NIVELES`; validar el personalizado mostrando **todos** los errores; formulario pegajoso. |
| Tablero y primer clic | R2, R3 · RF-04 | Una tabla de `<button name="celda" value="fila-columna">`. Las minas **no existen** hasta el primer clic. |
| Descubrir | R4 · RF-05, RF-06 | Números con color; los ceros abren a sus vecinas con una **función recursiva**. |
| Banderas y marcador | R5, R6 · RF-07, RF-08 | Modo *Descubrir* / *Bandera* que se mantiene; minas restantes, jugadas y tiempo `mm:ss`. |
| Terminar | R7 · RF-09…RF-11 | Victoria (banderas en las minas), derrota (mina en rojo, banderas mal puestas) y tablero sin botones. |
| Estado y desconfianza | R8, R9 · RF-12, RF-13 | La partida viaja en campos `hidden`, **una cadena por fila**; todo se revalida al volver. |
| Organización | R10 · RNF-07…RNF-10 | Cuatro ficheros con una responsabilidad cada uno; `vecinos()` reutilizada tres veces. |

Los requisitos no funcionales que más condicionan el código son: **sin `$_SESSION`** y con **una cadena por fila**
(RNF-03, RNF-04), **relleno recursivo** (RNF-06), **lógica sin HTML** (RNF-07) y **no confiar en el cliente**
(RNF-12, RNF-13, RNF-16).

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Cuál es el máximo de minas de un tablero personalizado? | El menor entre el 35 % y "casillas − 9" (`maximoMinas()`): así siempre cabe la zona segura del primer clic. En 5 × 5, 8 minas. |
| ¿Se pueden poner banderas antes del primer clic? | Sí (RF-07). Cuentan como jugadas, pero no ponen en marcha el reloj. |
| ¿Cuándo empieza el reloj? | En el primer clic de *descubrir*, que es cuando se colocan las minas (`inicio = time()`). |
| ¿Qué pasa al pulsar una casilla con bandera en modo *Descubrir*? | Aviso *Esa casilla tiene una bandera: quítala antes de descubrirla (modo «Bandera»).* y no cuenta como jugada. |
| ¿Y al pulsar una casilla ya descubierta? | Nada en la solución 01; en la 02 es el **acorde**. |
| ¿Con qué modo empieza una partida nueva? | *Descubrir*, aunque la anterior estuviera en modo bandera. |
| ¿Qué pasa si llega un clic sobre una partida terminada (petición forzada)? | Se ignora: solo se juega si `resultado` es `EN_JUEGO`. |
| ¿El reloj avanza en pantalla? | No: sin JavaScript, el tiempo solo se recalcula en cada petición. |
| ¿Cómo se representa una mina? | `MINA = -1` en la matriz y `'*'` en la cadena de la fila. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). Así quien
> corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de la tarea.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | *Experto* y *Empezar* | Tablero de 16 × 30 todo oculto; "Minas restantes: 99" |
| 2 | Personalizado 5 × 5 con 9 minas | "Para un tablero de 5 x 5 el número de minas debe estar entre 1 y 8." y los valores conservados |
| 3 | Filas 3, columnas 40, minas `abc` | Tres errores a la vez |
| 4 | Primer clic en cualquier casilla | Nunca es una mina y siempre abre una zona vacía |
| 5 | Personalizado 16 × 30 con 1 mina, un clic | Se descubre todo el tablero y se gana; la mina queda con bandera |
| 6 | Modo bandera, clic en una casilla; después modo descubrir sobre ella | 🚩, "Minas restantes" baja en 1; el aviso de la bandera |
| 7 | Pisar una mina | "¡BOOM! Has pisado la mina de la fila…", todas las minas, la pisada en rojo, ❌ en las banderas mal puestas, sin botones |
| 8 | `celda=99-99` | "La casilla indicada no existe." y la partida no cambia |
| 9 | Cambiar `partida[minas]` o una fila del tablero en el HTML | "Los datos de la partida no son válidos (¿se ha modificado la página?). Empieza una nueva partida." |
| 10 | Ctrl+U en mitad de una partida | Solución 01: se ven los `*`. Solución 02: un único campo en base64 ilegible |

Los casos 3, 8 y 9 no se pueden provocar con el formulario normal (los `<input type="number">` tienen `min` y
`max`, y los botones los genera el servidor): hay que editar el HTML con F12 o enviar la petición con `curl`.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada clic en una casilla es una petición nueva: el script empieza desde cero y tiene
que **reconstruir la partida** entera a partir de lo que envíe el formulario. La pista del enunciado propone un
único array asociativo, que se documenta en la cabecera de `logica.php` desde el paso 1:

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Niveles, límites, estados, resultados | constantes | `config.php` | Añadir un nivel solo toca este fichero (RNF-08) |
| `filas`, `columnas`, `minas` | int | campos ocultos | Dimensiones de la partida |
| `colocadas` | bool (`0`/`1`) | campo oculto | Distingue "antes" y "después" del primer clic (R3) |
| `tablero` | matriz de int (`MINA` o 0…8) | **una cadena por fila** (`"01*1000"`) | R8: 16 campos en vez de 480 |
| `estado` | matriz de `'O'`, `'D'`, `'B'` | **una cadena por fila** (`"DDOOBOO"`) | Lo que ve el jugador |
| `jugadas`, `inicio`, `fin` | int (`time()`) | campos ocultos | Marcador (R6) |
| `resultado`, `explotada` | string | campos ocultos | Fin de la partida y mina en rojo (R7) |
| Modo del clic | `'descubrir'` / `'bandera'` | botones de radio del formulario del tablero | No es parte de la partida, es una preferencia |
| Minas restantes, tiempo `mm:ss` | — | **se calculan** | A partir de las banderas y de `inicio` / `fin` |
| Errores, aviso de la jugada | array / string | variables | Solo duran la petición |

> **Debes conocer**
>
> **Elegir bien el formato del estado ahorra cientos de campos.** Con un campo por casilla, un tablero de experto
> necesitaría 960 campos ocultos (480 del tablero y 480 del estado). Con una cadena por fila son 32, más 9 datos
> sueltos. El truco funciona porque cada casilla cabe en **un carácter**: por eso las constantes de estado son
> letras (`OCULTA = 'O'`) y la mina se escribe `'*'`. `implode('', $fila)` convierte la fila en cadena y
> `str_split($texto)` hace el camino inverso.

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

R10 fija los ficheros, y la solución añade un quinto, `estado.php`, para separar **cómo se guarda** la partida de
**cómo se juega**. Esa separación es la que permite, en el paso 5, cambiar el formato del estado sin tocar el resto.
Cada fichero carga el que necesita con `require_once`:

```text
config.php   <- logica.php   <- estado.php  (guardar / recuperar la partida, validar la configuración)
                             <- vista.php   (funciones que DEVUELVEN HTML)
index.php    -> estado.php + vista.php      (lee $_POST, llama a la lógica, hace el echo)
```

| Función | Fichero | Responsabilidad | Paso |
|---|---|---|---|
| `crearMatriz()` / `nuevaPartida()` / `maximoMinas()` | logica | Matriz rellena / partida **sin minas** / máximo de minas | 2 |
| `leerCoordenadas()` | logica | `"fila-columna"` → `[fila, columna]` o `null` | 2 |
| `partidaACamposOcultos()` / `recuperarPartida()` | estado | Partida → campos ocultos / campos → partida validada o `null` | 2 |
| `validarConfiguracion()` | estado | Nivel o personalizado → `[filas, columnas, minas]` o `null` + errores | 2 |
| `e()` / `configuracionDe()` / `pintarConfiguracion()` | vista | Escapar / nivel de la partida en juego / formulario | 2 |
| `pintarCasilla()` / `pintarTablero()` | vista | Una celda / la tabla con su formulario | 2 y 3 |
| `vecinos()` | logica | Coordenadas de las vecinas que existen | 3 |
| `colocarMinas()` / `calcularNumeros()` | logica | Minas al azar fuera de la zona segura / números | 3 |
| `descubrir()` / `jugarCasilla()` | logica | Descubrir (recursiva en el paso 4) / jugada completa | 3 y 4 |
| `pintarMarcador()` | vista | Marcador y mensaje final | 3 y 4 |
| `contarEstado()` / `haGanado()` / `alternarBandera()` / `segundosJugados()` | logica | Banderas, victoria, tiempo | 4 |
| `jugarAcorde()` / `comprobarVictoria()` / `cifrarPartida()` / `descifrarPartida()` | logica / estado | Ampliaciones (solución 02) | 5 |

> **Recomendación**
>
> Fíjate en **qué se pasa por referencia**: `colocarMinas(array &$partida, …)`, `calcularNumeros(array &$tablero)`,
> `alternarBandera(array &$partida, …)` y `jugarCasilla(array &$partida, …)` modifican lo que reciben;
> `descubrir(array $tablero, array &$estado, …)` recibe el tablero **por valor** (solo lo lee) y el estado **por
> referencia** (lo cambia). La firma documenta la intención: quien lee `descubrir()` sabe, sin mirar dentro, que no
> va a mover ninguna mina. Y `validarConfiguracion()` devuelve el resultado y deja los errores en `array &$errores`.

### 2.2.- Estados de la página

La partida tiene un subestado que no se ve pero que cambia el comportamiento: **antes** del primer clic de
*descubrir* no hay minas (`colocadas` es `false`). Las transiciones (*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Sin partida | *Empezar* [configuración válida] | `nuevaPartida()`, modo *descubrir* | En juego, sin minas |
| Sin partida | *Empezar* [configuración no válida] | mostrar todos los errores, formulario pegajoso | Sin partida |
| Cualquiera | clic [estado recibido no válido] | error | Sin partida |
| En juego | clic [la casilla no existe] | error | igual |
| En juego | clic en modo bandera [casilla no descubierta] | poner/quitar bandera, jugadas + 1 | igual |
| En juego | clic en modo descubrir [bandera] | aviso | igual |
| En juego, sin minas | clic en modo descubrir | colocar minas fuera de la zona, `inicio = time()`, descubrir | En juego, con minas (o Ganada) |
| En juego, con minas | clic en modo descubrir [mina] | `PERDIDA`, `explotada`, `fin = time()` | Perdida |
| En juego, con minas | clic en modo descubrir [no es mina] | descubrir (y sus vecinas si es un 0) | En juego / Ganada |
| Ganada / Perdida | clic (petición forzada) | se ignora | igual |
| Cualquiera | *Empezar* | nueva partida | En juego, sin minas |

La flecha "sin minas → con minas" es la que obliga a guardar `colocadas`, y la que hace que la validación del estado
tenga que distinguir dos casos: antes del primer clic el tablero no puede tener ninguna mina; después, exactamente
las declaradas.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden: primero el
**estado** (que la partida sobreviva a un clic), después **perder** (que es lo más sencillo y además enseña el
tablero entero), después **ganar** y por último las **ampliaciones**.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs en los seis ficheros | — |
| 2 | Configuración, tablero vacío y estado en campos `hidden` (ya validado) | R1, R2, R8, R9, R10 |
| 3 | Colocar minas, calcular números, descubrir (sin recursión) y perder | R3, R4 (en parte), R7 (derrota) |
| 4 | Recursividad, banderas, victoria y marcador = **SOLUCION-01** | R4, R5, R6, R7 |
| **5** | **Ampliaciones:** acorde, estado cifrado y relleno iterativo = **SOLUCION-02** | RF-A1, RNF-A1 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código sin programar nada todavía.

**Por qué empezar así.** Con cinco ficheros PHP es muy fácil escribir una función en el sitio equivocado (por
ejemplo, un `echo` dentro de `logica.php`). Los TODO reparten el trabajo **antes** de escribirlo: cada fichero dice
en su cabecera de qué se encarga y cada función aparece con su **firma** completa, incluidos los `&`:

```php
// Función auxiliar TODO: descubrir(array $tablero, array &$estado, int $fila, int $columna): int
//      RECURSIVA: si la casilla es un 0, descubre también sus vecinas
```

En `index.php` los TODO siguen la estructura de siempre: inicializar, leer el modo con una lista blanca, valores
por defecto del formulario, ¿nueva partida?, ¿jugada?, y el HTML. `estilos.css` ya está completo desde el paso 1
(no cambia hasta el paso 5): el aspecto no es lo que se está aprendiendo aquí.

**Decisión clave.** La cabecera de `logica.php` ya describe el array de la partida, con `'colocadas' => false` y el
comentario *las minas se colocan tras el PRIMER clic*. R3 está en el diseño desde el principio.

> **Pruébalo**
>
> Abre la página: sale el título y nada más, pero **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code
> verás la lista de lo que queda por hacer.

### 3.2.- Paso 2: configuración, tablero vacío y estado en campos ocultos

**Objetivo.** Elegir el nivel, crear una partida sin minas, dibujar el tablero de botones y conseguir que la
partida **vaya y vuelva** en campos ocultos (R1, R2, R8), validada (R9).

**Por qué ahora.** Sin estado no hay juego: cada clic empezaría de cero. Y, a diferencia de otras tareas, aquí la
validación del estado **no se deja para el final**: `recuperarPartida()` es la función inversa de
`partidaACamposOcultos()` y las dos dependen de la misma decisión de formato (`'*'`, `'O'`, `'D'`, `'B'`, una cadena
por fila). Se escriben juntas, con el formato fresco en la cabeza, y así `estado.php` queda terminado en este paso
y ya no cambia hasta la solución 02.

**Qué se escribe.**

- `config.php` completo: `NIVELES`, los límites del personalizado, `MINA`, `OCULTA`/`DESCUBIERTA`/`BANDERA` y
  `EN_JUEGO`/`GANADA`/`PERDIDA`.
- `validarConfiguracion()`: si el nivel está en `NIVELES` se usa tal cual; si es `personalizado`, se validan los
  tres números con `filter_var(..., FILTER_VALIDATE_INT)` y rangos. El máximo de minas **solo** se comprueba si
  filas y columnas son correctas (un `elseif`): con filas 3 no tiene sentido calcular el 35 % de nada.
- `partidaACamposOcultos()`: los datos sueltos, `colocadas` como `0`/`1` y dos cadenas por fila. Para el tablero
  hay que traducir `MINA` (-1) a `'*'` con `array_map()` antes del `implode()`.
- `recuperarPartida()`: enteros con `filter_var`, dimensiones y minas dentro de los límites, `resultado` en una
  lista blanca, `explotada` con formato, y cada fila con una expresión regular que comprueba **a la vez** la
  longitud y los caracteres (código debajo de la lista). Si algo falla, `null`.
- `leerCoordenadas()`: `preg_match('/^(\d{1,2})-(\d{1,2})$/', …)` y que la casilla esté dentro del tablero.
- `pintarConfiguracion()` genera los radios con un `foreach` sobre `NIVELES`; `configuracionDe()` hace el camino
  inverso (de una partida en juego al nivel que le corresponde) para que el formulario muestre con qué se juega
  (RF-03).
- `pintarCasilla()` todavía solo pinta botones (oculta o con bandera).

```php
// recuperarPartida() (paso 2, estado.php)
if (preg_match('/^[*0-8]{' . $columnas . '}$/', $textoTablero) !== 1
    || preg_match('/^[ODB]{' . $columnas . '}$/', $textoEstado) !== 1) {
    return null;
}
// ...
if ($minasContadas != ($partida['colocadas'] ? $minas : 0)) {
    return null;
}
```

La última comprobación ya tiene en cuenta el subestado del apartado 2.2, aunque en este paso todavía no se coloca
ninguna mina: así no habrá que volver a `estado.php`.

> **Debes conocer**
>
> **Lista blanca en todo lo que no es un número.** El modo se lee como
> `$modo = ($_POST['modo'] ?? '') === 'bandera' ? 'bandera' : 'descubrir';`: cualquier valor que no sea
> exactamente `bandera` se convierte en `descubrir`. `resultado` se compara con `in_array(..., true)` contra las
> tres constantes. Es más seguro enumerar lo que **sí** vale que intentar adivinar todo lo que no vale.

**Lo que queda pendiente.** En `index.php`, la jugada lee y valida la casilla, pero después solo hay dos TODO
(bandera en el paso 4, descubrir en el paso 3). Pulsar una casilla vuelve a pintar la misma partida: justo lo que
hace falta para comprobar que el estado sobrevive al viaje.

> **Pruébalo**
>
> Casos 1, 2, 3 y 8. Elige *Experto*: 16 × 30 botones. Con Ctrl+U busca `partida[tablero][0]` (30 ceros) y
> `partida[estado][0]` (30 `O`). Pulsa una casilla: la página se recarga igual y sin errores. Con F12, cambia el
> `value` de un botón por `99-99` ("La casilla indicada no existe.") y una letra de `partida[estado][0]` por una `X`
> ("Los datos de la partida no son válidos…"). Para el caso 3, quita los atributos `min`/`max` con F12 o usa
> `curl -d "nueva=1&nivel=personalizado&filas=3&columnas=40&minas=abc" http://localhost:8000/`.

### 3.3.- Paso 3: colocar minas, calcular números, descubrir y perder

**Objetivo.** Que el primer clic coloque las minas (R3), que cada casilla tenga su número y que pisar una mina
termine la partida (R7, derrota).

**Por qué ahora y por qué perder antes que ganar.** Perder es más sencillo (una sola casilla decide) y, sobre todo,
**al perder se enseñan todas las minas**: es la mejor herramienta de prueba para comprobar a ojo que los números
están bien calculados. Ganar necesita el relleno recursivo y contar casillas, y eso puede esperar al paso 4.

**Qué se escribe.**

- `vecinos()`: dos bucles de -1 a 1 que descartan la propia casilla y las que se salen del tablero. Es la función
  que la pista pide reutilizar: la usan `colocarMinas()`, `calcularNumeros()` y, en el paso 4, `descubrir()`.
- `colocarMinas()` sigue la pista del enunciado: numerar las casillas, quitar las prohibidas, barajar y coger las
  primeras (código debajo de la lista). Al final llama a `calcularNumeros()` y pone `colocadas` a `true`.
- `calcularNumeros(array &$tablero)`: para cada casilla sin mina, cuenta las vecinas con mina.
- `descubrir()`: de momento solo marca la casilla como descubierta. La recursión es un TODO del paso 4.
- `jugarCasilla()`: si es el primer clic, coloca las minas y arranca el reloj; suma la jugada; si hay mina, la
  partida se pierde y se apunta cuál ha explotado (`"{$fila}-{$columna}"`); si no, descubre.
- `pintarCasilla()` completa: número con su clase `n1`…`n8`, la mina explotada y, con la partida terminada, todas
  las minas y las banderas mal puestas (❌). `pintarMarcador()` de momento solo pinta el mensaje final.

```php
// colocarMinas() (paso 3, logica.php)
$prohibidas = [$filaSegura * $columnas + $columnaSegura];
foreach (vecinos($filaSegura, $columnaSegura, $filas, $columnas) as [$f, $c]) {
    $prohibidas[] = $f * $columnas + $c;
}
$candidatas = array_diff(range(0, $filas * $columnas - 1), $prohibidas);
shuffle($candidatas);
foreach (array_slice($candidatas, 0, $partida['minas']) as $posicion) {
    $partida['tablero'][intdiv($posicion, $columnas)][$posicion % $columnas] = MINA;
}
```

> **Debes conocer**
>
> **Por qué el primer clic siempre abre una zona.** Si se evitara solo la casilla pulsada, el primer clic nunca
> perdería, pero podría salir un 3 y el jugador tendría que adivinar. Evitando también sus vecinas, la casilla
> pulsada tiene **cero** minas alrededor: siempre es un 0. Por eso `maximoMinas()` deja libres al menos 9 casillas
> (la pulsada y sus 8 vecinas): con menos, `array_slice()` no encontraría sitio para todas las minas.

**Trucos provisionales.** En este paso `pintarMarcador()` ya tiene escrito el mensaje de victoria, que usa una
variable `$tiempo` que todavía no existe: no da problemas porque aún no se puede ganar, y el paso 4 la define. El
primer clic abre una sola casilla (vacía) en lugar de una zona: también lo arregla el paso 4.

> **Pruébalo**
>
> Caso 4 y caso 7. Empieza en *Principiante* y pulsa: la casilla sale vacía, nunca con mina. Sigue pulsando hasta
> pisar una: "¡BOOM! Has pisado la mina de la fila…", la mina en rojo, el resto de minas a la vista y ningún botón.
> Comprueba con el tablero destapado que cada número cuenta bien sus minas. Con Xdebug, pon un punto de ruptura en
> `colocarMinas()` y mira `$prohibidas`: 4 casillas en una esquina, 6 en un borde y 9 en el centro.

### 3.4.- Paso 4: recursividad, banderas, victoria y marcador (solución 01)

**Objetivo.** Abrir las zonas vacías con una función recursiva (R4), banderas (R5), marcador (R6) y victoria (R7).
Al terminar este paso se cumplen R1–R10: es la `SOLUCION-01`.

**Por qué ahora.** Con `vecinos()` y `descubrir()` escritos, la recursión es **una sola llamada** más. Las
banderas, la victoria y el marcador son cálculos sobre la matriz `estado`, que ya viaja y se valida.

**Qué se escribe.**

- La recursión en `descubrir()`: si la casilla es un 0, se llama a sí misma con cada vecina y suma lo que
  descubren (código debajo de la lista).
- `contarEstado()` usa `array_count_values()` en cada fila: sirve para las banderas (marcador) y para las
  descubiertas (victoria).
- `haGanado()`: descubiertas = filas × columnas − minas. Al ganar, `jugarCasilla()` pone bandera en todas las minas.
- `alternarBandera()`: oculta ↔ bandera (en una descubierta no hace nada) y suma una jugada.
- En `jugarCasilla()`, el aviso si la casilla tiene bandera; en `index.php`, el `if ($modo == 'bandera')`.
- `segundosJugados()` (0 si no ha empezado; si ha terminado, `fin − inicio`) y el formato `mm:ss` con
  `sprintf('%02d:%02d', intdiv($segundos, 60), $segundos % 60)`.
- En `pintarTablero()`, los radios del modo, **solo** si la partida sigue en juego (RF-11).

```php
// descubrir() (paso 4, logica.php)
// Caso base: ya descubierta o con bandera -> no hacemos nada
if ($estado[$fila][$columna] != OCULTA) {
    return 0;
}
$estado[$fila][$columna] = DESCUBIERTA;
$descubiertas = 1;
if ($tablero[$fila][$columna] == 0) {
    foreach (vecinos($fila, $columna, count($tablero), count($tablero[0])) as [$f, $c]) {
        $descubiertas += descubrir($tablero, $estado, $f, $c);
    }
}
```

> **Debes conocer**
>
> **El caso base hace dos trabajos.** `!= OCULTA` detiene la recursión en las casillas ya descubiertas (sin él, dos
> ceros vecinos se llamarían el uno al otro sin fin) y, a la vez, respeta las banderas (RF-06: *las casillas con
> bandera no se descubren*). Una sola condición cumple un requisito y evita una recursión infinita.

> **Para saber más**
>
> `$tablero` se pasa por valor en cada llamada recursiva y, aun así, no se copia 480 veces: PHP usa *copia en
> escritura* (*copy-on-write*), y un array que se recibe por valor y no se modifica no se duplica. Lo que sí crece
> con cada casilla vacía es la **pila de llamadas**: es lo que el enunciado te pide observar con Xdebug, y es el
> motivo de la versión iterativa de la solución 02.

> **Pruébalo**
>
> Caso 5: personalizado 16 × 30 con 1 mina. Un clic descubre todo y sale el mensaje de enhorabuena con la mina
> marcada. Caso 6: en modo bandera, una casilla pasa a 🚩 y "Minas restantes" baja; pon más banderas que minas y
> verás un número negativo; en modo descubrir sobre la bandera sale el aviso. Comprueba que el modo bandera **se
> mantiene** entre clics y que, al pulsar *Empezar*, vuelve a *descubrir*. Pierde con banderas mal puestas: ❌.

### 3.5.- Paso 5 (solución completa con ampliaciones): acorde y estado cifrado

**Objetivo.** Las dos ampliaciones opcionales: el **acorde** (RF-A1) y el **estado protegido** (RNF-A1). Al
terminar este paso se tiene la `SOLUCION-02`.

**Por qué al final.** Son opcionales y se apoyan en una base que funciona y está probada. Además, este paso
demuestra que la organización en ficheros funcionaba: `estado.php` cambia **por completo** y `index.php` no cambia
ni una línea (solo el comentario de cabecera), porque `partidaACamposOcultos()` y `recuperarPartida()` mantienen su
firma.

**Qué se escribe.**

- `config.php`: `CLAVE_SECRETA` y `ALGORITMO_CIFRADO = 'aes-256-gcm'`, con un comentario que avisa de que en una
  aplicación real la clave no se escribe en el código (se leería, por ejemplo, con `getenv()`).
- `estado.php`: `cifrarPartida()` hace `json_encode()` → `openssl_encrypt()` con un vector de inicialización
  aleatorio (`random_bytes()`) → `base64_encode($iv . $tag . $cifrado)`. `descifrarPartida()` hace el camino
  inverso y devuelve `null` si `openssl_decrypt()` falla. `partidaACamposOcultos()` genera **un único** campo.
- `logica.php`: `jugarAcorde()` (se llama desde `jugarCasilla()` al pulsar una casilla **ya descubierta**),
  `comprobarVictoria()` y `descubrir()` iterativa.
- `vista.php`: los números descubiertos son botones (`class='numero'`) mientras la partida sigue en juego.

```php
// recuperarPartida() (paso 5, estado.php): ya no se revalida casilla a casilla
function recuperarPartida(mixed $datos): ?array
{
    return is_string($datos) ? descifrarPartida($datos) : null;
}
```

**Una refactorización por el camino.** El bloque "si se ha ganado, cerrar la partida y poner banderas en las minas"
estaba dentro de `jugarCasilla()`. El acorde también puede ganar la partida, así que, en vez de copiarlo, se saca
a `comprobarVictoria(array &$partida)` y se llama desde los dos sitios: la señal "código repetido" de los apuntes.

> **Pruébalo**
>
> Con Ctrl+U: un solo `<input type='hidden' name='partida'>` con texto en base64, y cada vez distinto aunque la
> partida no cambie (el vector de inicialización es aleatorio). Cambia un carácter con F12: "Los datos de la
> partida no son válidos…". Acorde: pon las banderas alrededor de un 2 y pulsa el 2; con una sola bandera, "Para
> despejar alrededor de un 2 necesitas 2 bandera(s) a su alrededor (tienes 1)."; con una bandera mal puesta,
> explota.

## 4.- Las dos soluciones comparadas

| Aspecto | SOLUCION-01 (paso 4) | SOLUCION-02 (paso 5) | Por qué |
|---|---|---|---|
| Estado en la página | 9 campos + 2 cadenas por fila, en claro | **Un** campo: JSON cifrado con AES-256-GCM y en base64 | Confidencialidad: con Ctrl+U ya no se ven las minas |
| Validar lo recibido | `recuperarPartida()` lo revisa todo (enteros, límites, regex de cada fila, minas contadas) | Si `openssl_decrypt()` no devuelve `false`, el contenido lo generó **nuestro** servidor | Integridad: GCM añade una etiqueta (`$tag`) que detecta cualquier bit cambiado |
| Casilla pulsada | `leerCoordenadas()` | `leerCoordenadas()` (igual) | `celda` no va dentro del campo cifrado: sigue siendo un dato del cliente |
| Relleno de zonas vacías | **Recursivo** (R4, RNF-06) | **Iterativo** con una pila (`array_push` / `array_pop`) | Mismo resultado; la pila se gestiona a mano en vez de usar la de llamadas de PHP |
| Clic en un número | No hace nada | **Acorde** (`jugarAcorde()`) | Ampliación RF-A1; por eso los números son botones |
| Victoria | Dentro de `jugarCasilla()` | `comprobarVictoria()`, usada dos veces | Evitar código repetido |
| Volver *Atrás* tras perder | Se puede seguir jugando | **También** | Un estado antiguo sigue siendo auténtico: solo las sesiones (temas posteriores) lo evitan |

Dos detalles de la versión iterativa: una casilla puede entrar **varias veces** en `$pendientes` (es vecina de
varios ceros), y por eso el `continue` comprueba al sacarla si sigue oculta; y el orden en que se descubren las
casillas cambia, pero el resultado final es el mismo.

> **Recomendación**
>
> Si entregas las ampliaciones, **no sustituyas** la versión recursiva: R4 y RNF-06 la exigen y la rúbrica le da
> 1,5 puntos. La versión iterativa de la solución 02 está para comparar las dos formas de recorrer una zona (pon un
> punto de ruptura en `descubrir()` y observa la pila de llamadas en una y el array `$pendientes` en la otra).

> **Para saber más**
>
> Cifrar **no es lo mismo** que codificar. `base64_encode()` solo convierte bytes en texto imprimible: cualquiera lo
> deshace con `base64_decode()`. Lo que protege la partida es `openssl_encrypt()` con una clave que **solo** está en
> el servidor. Y aun así el cifrado no impide reenviar una página anterior válida (pregunta de reflexión 2): para
> eso el servidor tiene que **recordar** cuál es el estado actual, y eso ya es una sesión.

## 5.- Pruebas finales

Con cada solución hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso: el paso
5 cambia por completo cómo viaja la partida y podría haber estropeado algo que antes funcionaba.

- [ ] Los tres niveles y un personalizado; formulario pegajoso con errores; RF-03 (el formulario muestra la partida en juego).
- [ ] Casos límite: 5 × 5 con 8 minas (el máximo), 16 × 30 con 1 mina, primer clic en una esquina y en un borde.
- [ ] Banderas: antes del primer clic, más banderas que minas, quitar una bandera, el modo se mantiene.
- [ ] Final: ganar (minas con bandera, mensaje con tiempo y jugadas) y perder (mina en rojo, ❌), tablero sin botones.
- [ ] Maliciosos: `celda=99-99`, `celda=a-b`, filas del tablero con otra longitud o con letras, `partida[minas]` cambiado tras el primer clic, `partida[resultado]=hackeado`.
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)`.

## 6.- Refactorización y mejoras posibles

La solución ya está organizada en funciones cortas, así que la refactorización se hizo sobre la marcha (la más
visible, `comprobarVictoria()` en el paso 5). Quedan detalles que se pueden mejorar, buenos para practicar:

- **Estado mínimo.** La solución 01 guarda los números del tablero, pero se pueden **calcular** a partir de las
  minas con `calcularNumeros()`. Hoy `recuperarPartida()` comprueba que hay tantas `*` como minas, pero no que los
  números sean los correctos: si alguien cambia un `1` por un `2`, la partida se acepta. Guardar solo las minas (o
  recalcular los números al recuperar) elimina ese problema sin escribir una validación más.
- **Arrays donde se espera texto.** `index.php` hace `(string) $_POST['celda']` y el formulario pegajoso
  `(string) ($_POST[$campo] ?? '')`; `recuperarPartida()` hace `(string)` con cada fila. Si llega `celda[]=1` o
  `nivel[]=x`, la conversión de un array a texto produce un aviso de PHP (y `array_key_exists()` con un array como
  clave, un `TypeError`). Comprobar antes con `is_string()`, como ya hace la solución 02 en `recuperarPartida()`,
  cumpliría del todo RNF-16.
- **Las preguntas de reflexión.** RNF-11 y el criterio 12 de la rúbrica piden responderlas en un comentario al
  principio de `index.php`, y ninguna de las dos soluciones lo hace (las ideas están en los comentarios de
  `estado.php`). En tu entrega no deben faltar: la autoevaluación del apartado 8 te da las pistas.
- Pequeños detalles de la vista: "1 jugadas" en singular, y la clave secreta de la solución 02 escrita en
  `config.php`, que su propio comentario manda sacar del código.

## 7.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Un campo oculto por casilla | Cientos de campos; formularios enormes | Una cadena por fila con `implode` / `str_split` (R8) |
| Colocar las minas al crear la partida | El primer clic puede perder (R3) | `colocadas = false` y colocarlas en el primer clic de *descubrir* |
| Evitar solo la casilla pulsada | El primer clic puede dar un número y obligar a adivinar | Evitar también sus vecinas con `vecinos()` |
| Recursión sin caso base para las descubiertas | Recursión infinita entre dos ceros vecinos | `if ($estado[$fila][$columna] != OCULTA) return 0;` |
| Escribir `vecinos()` tres veces con bucles distintos | Errores en los bordes en un sitio sí y en otro no | Una función reutilizada (RNF-10) |
| `echo` dentro de `logica.php` o `vista.php` | Mezcla de responsabilidades; no se puede probar ni reutilizar | La lógica devuelve datos, la vista devuelve HTML, `index.php` hace el `echo` |
| Confiar en el `value` del botón | `celda=99-99` produce *Undefined array key* | `leerCoordenadas()` con formato y límites |
| Creer que `base64_encode()` protege | Cualquiera lo descodifica | Cifrado autenticado con una clave del servidor |

## 8.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué las minas no se colocan al crear la partida? **Porque R3 exige que el primer clic sea seguro, y
>   hasta que no se pulsa no se sabe qué casilla (y qué vecinas) hay que evitar.**
> - ¿Para qué sirve el campo `colocadas`? **Para distinguir una partida sin minas (todavía no se ha descubierto
>   nada) de una con minas; la validación espera 0 minas en el primer caso y exactamente las declaradas en el
>   segundo.**
> - ¿Por qué `descubrir()` recibe `$tablero` por valor y `$estado` por referencia? **Porque solo lee el tablero y
>   solo modifica el estado; la firma lo deja claro y PHP no copia un array que no se modifica.**
> - En la solución 01, ¿puedes saber con Ctrl+U dónde están las minas? **Sí: las `*` de `partida[tablero]`.
>   Guardar el estado en el cliente significa que el cliente lo puede leer y modificar.**
> - ¿Qué pasa si, después de perder, pulsas *Atrás* y vuelves a hacer clic? **Que el navegador reenvía un estado
>   anterior que es válido (y, en la solución 02, auténtico), y la partida continúa como si no hubieras perdido.**
> - ¿Por qué hay que validar el `value` del botón aunque lo genere el servidor? **Porque cualquiera puede cambiarlo
>   con F12 o enviar la petición con `curl`: todo lo que llega del navegador es un dato del cliente.**
> - ¿Qué aporta la "G" de AES-256-GCM? **Autenticación: además de cifrar, calcula una etiqueta y
>   `openssl_decrypt()` devuelve `false` si se ha cambiado un solo bit.**
