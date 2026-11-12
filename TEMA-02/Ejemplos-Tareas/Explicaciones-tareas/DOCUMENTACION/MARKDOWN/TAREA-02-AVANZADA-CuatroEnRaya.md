# Cuatro en raya (TAREA-02, nivel avanzado): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-AVANZADA-CuatroEnRaya/ENUNCIADO.md) ·
[requisitos](../TAREA-02-AVANZADA-CuatroEnRaya/Requisitos.md) ·
[solución](../TAREA-02-AVANZADA-CuatroEnRaya/SOLUCION-01/) ·
[paso a paso](../TAREA-02-AVANZADA-CuatroEnRaya/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> La asociación juvenil del barrio quiere un cuatro en raya para practicar antes de su torneo, y **Juan** se lo
> encarga a **Carlos**. Su primera idea es guardar las 42 casillas del tablero en campos ocultos. **María** le
> propone otra cosa: "Guarda solo las **jugadas**, la columna de cada ficha en orden. Con eso puedes volver a
> poner las fichas una a una y saber cómo está el tablero. Si alguien cambia la lista por una imposible, lo
> notarás al repetirla. Y deshacer será quitar la última". Carlos tarda un poco en verlo, pero cuando lo ve se da
> cuenta de que esa decisión le ahorra la mitad del trabajo.

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

El enunciado tiene 9 requisitos (R1…R9) que [Requisitos.md](../TAREA-02-AVANZADA-CuatroEnRaya/Requisitos.md)
convierte en 9 requisitos funcionales comprobables. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Configurar | R1, R2 · RF-01, RF-02 | Modo, nombres, nivel y quién empieza; todos los errores a la vez; nombres contados con `mb_strlen`. |
| Tablero y jugadas | R3, R4, R5 · RF-03, RF-04 | La ficha **cae**; los turnos alternan; solo se guardan las jugadas; columnas inexistentes o llenas. |
| Terminar | R6 · RF-05, RF-06 | Cuatro en raya en **cuatro** direcciones, empate, línea resaltada y *Nueva partida*. |
| La máquina | R7 · RF-07 | Contesta en la misma petición; dos niveles; **reproducible** con una semilla. |
| Deshacer e historial | R8 · RF-08 | Quitar la última jugada (y la de la máquina); listar las jugadas numeradas. |
| No fiarse del cliente | R9 · RF-09 | Nombres, máquina, nivel, semilla y jugadas posibles; nada sin escapar. |

Los requisitos no funcionales que más condicionan el código son tres: **sin `$_SESSION`** ni cookies (RNF-03),
**estado mínimo**: solo las jugadas (RNF-04), y **constantes** para las dimensiones, los colores, los niveles y
las direcciones (RNF-05).

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Cómo se numeran las columnas? | En las jugadas, de 0 a 6 (un carácter por jugada); en pantalla y en el historial, de 1 a 7 (`+ 1`). |
| ¿Qué color lleva cada jugador? | Quien empieza lleva las rojas, y las rojas son siempre las jugadas de posición **par**. |
| ¿Cómo se sabe quién es la máquina? | Un campo `maquina` con su color (`R`, `A`) o `''` si juegan dos personas; se llama siempre *Máquina*. |
| ¿Qué nivel se guarda si juegan dos personas? | `facil`: no se usa, pero así el nivel siempre es válido. |
| ¿Se puede deshacer una partida terminada? | Sí: el botón *Deshacer* solo se deshabilita cuando no hay jugadas. |
| ¿Y si hay cinco en raya? | `lineaGanadora()` devuelve todas las casillas de la línea (`>= 4`), y se resaltan todas. |
| ¿Si todas las columnas "regalan" la victoria al rival? | La máquina elige entre todas las libres. |
| ¿Cómo se repite la misma jugada de la máquina tras deshacer? | La semilla de cada decisión es `semilla + número de jugadas`. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). En esta
> tarea hay varias que el enunciado deja abiertas (deshacer tras terminar, el nivel en el modo de dos personas);
> si no las escribes, cambiarás de criterio a mitad del código sin darte cuenta.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son ejemplos concretos de entrada y salida. Escríbelos en una tabla
antes de programar: serán las pruebas de cada paso. Las jugadas se escriben como se guardan (columnas de 0 a 6).

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Abrir la página | `value="maquina" checked` y el nivel "normal: gana, tapa y prefiere el centro" |
| 2 | Modo `x` y sin nombre | "Elige si juegan dos personas…" y "(tiene 0)" |
| 3 | Dos personas: `Ana` y `ana` (con espacios a los lados) | "Los dos jugadores no pueden llamarse igual." |
| 4 | Contra la máquina, nombre `máquina` y nivel `experto` | "Elige el nivel de la máquina." y "«Máquina» es el nombre de la máquina" |
| 5 | Nombre `ÑandúÑandúÑandúX` | "(tiene 16)" y el campo conserva lo escrito |
| 6 | `modo[]`, `jugador1[]`, `empieza[]` | Errores, sin avisos de PHP |
| 7 | Dos personas, empieza Ana, columna 4 | `name="jugadas" value="3"`, la casilla `ficha R ultima` y "Turno de Brais (amarillas)" |
| 8 | `columna=7` o `columna[]=1` | "Esa columna no existe." |
| 9 | Seis fichas en la columna 1 y pulsarla otra vez | "La columna 1 está llena.", las jugadas siguen siendo `000000` y el botón está deshabilitado |
| 10 | Jugadas `3434343` | "¡Cuatro en raya! Gana Ana con las rojas en 7 jugadas." y casillas `ganadora`; otra jugada: "La partida ha terminado" |
| 11 | Jugadas `01026364` | "Gana Brais con las amarillas en 8 jugadas." (horizontal) |
| 12 | Jugadas `01122323363` | "Gana Ana con las rojas en 11 jugadas." (diagonal) |
| 13 | Las 42 jugadas de `Requisitos.md` | "El tablero está lleno: empate." |
| 14 | Máquina normal, semilla 7: Ana juega 0; después 0 otra vez | "La máquina ha jugado en la columna 4." (`03`); después tapa en la columna 1 (`030300`) |
| 15 | Empieza la máquina | "La máquina ha empezado en la columna 4." y `name="maquina" value="R"` |
| 16 | Dos personas, jugadas `34`, *Deshacer* | "Jugada deshecha.", jugadas `3`; en el historial "Ana (rojas): columna 4" |
| 17 | La máquina ha empezado (`3`), *Deshacer* | "No hay ninguna jugada tuya que deshacer." |
| 18 | Jugadas `7`, `0000000` o `34343430`; `nombreA=ana`; `maquina=X`; `nivel=dios`; `semilla=0` | "La partida recibida no es válida", sin avisos |

Los casos 6, 8 y 18 son **maliciosos**: no se pueden provocar con la página normal, hay que editar el HTML con F12
o enviar la petición con `curl`. Los casos 10 a 13 son la forma de probar el cuatro en raya **sin jugar**: basta
con escribir las jugadas en el campo oculto.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada clic en una columna es una petición nueva: el script empieza desde cero y
tiene que **reconstruir la partida** a partir de lo que envíe el formulario.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Nombres de las rojas y las amarillas | strings | campos ocultos `nombreR`, `nombreA` | Para el turno, el historial y el mensaje final |
| Color de la máquina | `R`, `A` o `''` | campo oculto | Decide si contesta la máquina y qué se deshace |
| Nivel | `facil` / `normal` | campo oculto | Lo usa la máquina |
| Semilla | int | campo oculto | Para que la máquina sea reproducible |
| **Jugadas** | **un solo** string (`"3324"`) | campo oculto | El estado mínimo (R4) |
| Tablero, última ficha, ganador, línea ganadora | — | **se calculan** (`reconstruir()`) | Salen de repetir las jugadas |
| Turno | — | **se calcula** (`turno()`) | Paridad de la longitud de las jugadas |
| Historial | — | **se calcula** (`historial()`) | Una línea por carácter de las jugadas |
| Errores, mensaje de la máquina, mensaje final | array / string | variables | Solo duran la petición |

> **Debes conocer**
>
> **Guardar las jugadas en lugar del tablero** cambia toda la tarea. El tablero tiene 42 casillas que habría que
> validar una a una, y aun así podría ser imposible (fichas flotando, cinco rojas más que amarillas). Las jugadas
> son un texto corto y, al **repetirlas**, cualquier lista imposible se descubre sola: una columna llena, un
> carácter raro o una ficha después de ganar. Y de regalo: el turno es la paridad de `strlen()`, deshacer es
> quitar el último carácter y el historial es recorrer el texto.

La partida queda como un array asociativo (comentario de cabecera de `funciones.php`):

```php
['nombres' => ['R' => 'Ana', 'A' => 'Máquina'], 'maquina' => 'A', 'nivel' => 'normal',
 'semilla' => 1234, 'jugadas' => '3324']
```

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Dos ficheros PHP con una responsabilidad cada uno: `funciones.php` (lógica, sin HTML) e `index.php` (decide qué
hacer y muestra la página), más `estilos.css`. `index.php` sigue la estructura de siempre, con un detalle: la
recuperación y validación de la partida es **común** a la columna y a *Deshacer*, así que va en un bloque propio
antes de los dos:

```text
1. Inicializar          -> cargar funciones, valores por defecto (contra la máquina, nivel normal)
2. Recibir y validar    -> Empezar | columna o Deshacer: leer el estado y validarlo
3. Procesar             -> soltar la ficha (y contesta la máquina) | deshacer | Nueva partida
4. Preparar mensajes    -> reconstruir el tablero, estado de la partida y mensaje final
5. Mostrar la página    -> formulario de configuración O jugadores, tablero, campos ocultos e historial
```

Cada tarea del paso 3 es una función pequeña con tipos (RNF-06):

| Función | Responsabilidad | Paso |
|---|---|---|
| `e()` / `texto()` | Escapar para el HTML / leer un campo de texto sin avisos y sin espacios a los lados | 2 |
| `validarConfiguracion()` | El formulario → `[configuración, errores]` | 2 |
| `nuevaPartida()` / `turno()` | La partida recién creada (quien empieza lleva las rojas) / de quién es el turno | 2 |
| `leerEstado()` | La partida de los campos ocultos, sin comprobar aún | 3 |
| `tableroVacio()` / `soltarFicha()` / `columnasLibres()` | El tablero, la gravedad y las columnas en las que cabe una ficha | 3 |
| `reconstruir()` | Repetir las jugadas → tablero, última ficha, ganador y línea, o `null` | 3, 4 |
| `lineaGanadora()` / `estadoPartida()` | El cuatro en raya que pasa por una ficha / `ganada`, `empate` o `jugando` | 4 |
| `ganaria()` / `regala()` | ¿Gana esa ficha en esa columna? / ¿Gana el rival justo encima? | 5 |
| `jugadaMaquina()` / `moverMaquina()` | La columna que elige la máquina / jugar si le toca | 5 |
| `deshacer()` / `historial()` / `estadoValido()` | Quitar jugadas / listarlas / ¿es una partida posible? | 6 |

> **Recomendación**
>
> Mira qué funciones reciben el array **por referencia**: `soltarFicha(array &$tablero, …)`,
> `moverMaquina(array &$partida)` y `deshacer(array &$partida)`. Son las únicas que cambian lo que reciben; el
> resto calcula y devuelve. RNF-06 lo pide así ("por referencia solo donde se modifica"), y la máquina depende de
> ello, como se verá en el paso 5.

### 2.2.- Estados de la página

La página tiene cuatro estados y el botón pulsado decide la transición (*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Sin partida | *Empezar* [configuración válida] | crear la partida; si empieza la máquina, juega | Jugando |
| Sin partida | *Empezar* [configuración no válida] | mostrar todos los errores | Sin partida |
| Con partida | columna o *Deshacer* [estado recibido no válido] | error | Sin partida |
| Jugando | columna [no existe, le toca a la máquina o está llena] | error; nada cambia | Jugando |
| Jugando | columna [válida] | añadir la jugada; contesta la máquina, si la hay y no ha terminado | Jugando, Ganada o Empate |
| Ganada / Empate | columna (petición forzada) | "La partida ha terminado: empieza otra." | igual |
| Con partida | *Deshacer* [hay una jugada de la persona] | quitar la última (y la de la máquina) | Jugando |
| Jugando | *Deshacer* [solo está la primera de la máquina] | "No hay ninguna jugada tuya que deshacer." | Jugando |
| Con partida | *Nueva partida* | formulario con los jugadores, el modo y el nivel | Sin partida |

Dos flechas no son evidentes y salen de dibujar el diagrama. La primera: la jugada de la persona puede **terminar**
la partida, y entonces la máquina no debe contestar; por eso `moverMaquina()` mira el estado antes de jugar. La
segunda: *Deshacer* puede salir de *Ganada* y volver a *Jugando*, y si contra la máquina solo quitara una jugada,
la partida se quedaría atascada: le tocaría a la máquina, que solo juega cuando juega la persona, y cualquier
columna daría "Ahora le toca a la máquina.".

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden: primero la
configuración, después el tablero con **dos personas** (que es más fácil de probar), luego el final de la partida y
solo entonces la máquina, que necesita todo lo anterior.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs y esqueleto HTML | — |
| 2 | Formulario, validación, partida nueva en campos ocultos y turno | R1, R2, R4 |
| 3 | Tablero reconstruido a partir de las jugadas: gravedad, turnos, columnas llenas | R3, R4, R5 |
| 4 | Cuatro en raya en las cuatro direcciones, empate, línea resaltada y *Nueva partida* | R6 |
| 5 | La máquina: fácil y normal, reproducible | R7 |
| **6** | **Solución completa:** deshacer, historial y validar el estado recibido | R8, R9 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué empezar así.** Es una tarea larga (casi veinte funciones) con muchas condiciones que se cruzan: quién
juega, si la partida ha terminado, si hay máquina. Los TODO son el **plan** dentro del propio código: en
`index.php`, uno por cada botón, en el orden en que se ejecutará; en `funciones.php`, la **firma** de cada
función con lo que recibe y lo que devuelve:

```php
// TODO: función reconstruir(string $jugadas): ?array -> repetir las jugadas: tablero, última ficha y ganador (null si las jugadas no son posibles)
        // TODO: después de un cuatro en raya no puede haber más jugadas
        // TODO: ¿ha hecho cuatro en raya con esta ficha?
```

**Decisión clave.** El comentario de cabecera de `funciones.php` ya contiene la estructura de la partida y la
frase que guía toda la tarea: solo se guardan las jugadas; el tablero, el turno y el ganador se calculan cada vez
con `reconstruir()`. En `index.php`, dentro del bloque de la columna, hay dos TODO que se resolverán en pasos
distintos: "una partida terminada no admite más fichas" (paso 4) y "nadie puede jugar por la máquina" (paso 5).

> **Pruébalo**
>
> Abre la página: sale el título y nada más, pero **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code
> verás la lista de lo que queda por hacer. A partir de ahora, cada TODO que se programa pierde la palabra `TODO`.

### 3.2.- Paso 2: configuración, partida nueva y turno

**Objetivo.** El formulario pegajoso con su validación (R1, R2), crear la partida y dejarla en campos ocultos
(R4), y saber de quién es el turno.

**Por qué ahora.** Sin una partida no hay nada que jugar. Y es el momento de fijar los datos del estado: si los
campos ocultos están bien desde el principio, los pasos siguientes solo **leen** de ellos.

**Qué se escribe.**

- Las constantes (`FILAS`, `COLUMNAS`, `ROJA`, `AMARILLA`, `COLORES`, `NIVELES`, `MAX_NOMBRE`, `NOMBRE_MAQUINA`).
  `NIVELES` es un array `clave => descripción`: con él se valida (`array_key_exists`) y se genera el `<select>`.
- `texto()` es la función auxiliar que evita casi todos los avisos: devuelve el campo sin espacios a los lados, o
  `''` si no es un texto (por ejemplo, `modo[]`).
- `validarConfiguracion()` cuenta los caracteres con `mb_strlen()` y compara sin mayúsculas con
  `mb_strtolower()`. Las comprobaciones dependen del modo: el jugador 2 solo con dos personas; el nivel y el
  nombre reservado *Máquina*, solo contra la máquina.
- `nuevaPartida()` traduce la configuración (jugador 1, jugador 2, quién empieza) al estado (rojas, amarillas,
  color de la máquina): es el único sitio donde se decide quién lleva cada color.
- `turno()` es una línea (código debajo de esta lista).
- En el HTML, los nombres, "Turno de … (rojas)" y los seis campos ocultos, todos con `e()`.

```php
// turno() (paso 2, funciones.php)
return strlen($jugadas) % 2 === 0 ? ROJA : AMARILLA;
```

> **Error típico**
>
> Usar `strlen()` y `strtolower()` con los nombres. En UTF-8 la Ñ y las vocales con tilde ocupan **2 bytes**:
> `ÑandúÑandúÑandú` tiene 15 letras pero `strlen()` dice 21, y daría un error que no es verdad. Y
> `strtolower('MÁQUINA')` no convierte la Á, así que un jugador podría llamarse así. Con `mb_strlen()` y
> `mb_strtolower()` las dos comprobaciones son correctas.

**Un truco provisional.** `index.php` deja `$estado = $partida === null ? 'sin partida' : 'jugando';`: todavía no
se sabe detectar el final, pero la vista ya puede usar `$estado` para enseñar el turno. El paso 4 lo sustituye por
`estadoPartida()`.

> **Pruébalo**
>
> Casos 1 a 6. Después, dos personas, Ana y Brais, empieza Ana: deben salir los dos nombres, "Turno de Ana
> (rojas)" y, con Ctrl+U, `name="jugadas" value=""`. Repite con "empieza el jugador 2": ahora Brais lleva las
> rojas. Contra la máquina y empezando ella sale "Turno de Máquina (rojas)": la máquina todavía no sabe jugar.

### 3.3.- Paso 3: el tablero reconstruido a partir de las jugadas

**Objetivo.** Dibujar el tablero con un botón por columna, soltar fichas con gravedad y turnos alternos, y
rechazar columnas inexistentes o llenas (R3, R4, R5).

**Por qué ahora.** Es el corazón de la decisión de María: comprobar que de un texto como `"3324"` sale el tablero
correcto. Se hace con **dos personas**, sin máquina y sin ganador, para que lo único que pueda fallar sea la
reconstrucción.

**Qué se escribe.**

- `tableroVacio()` con `array_fill(0, FILAS, array_fill(0, COLUMNAS, VACIA))`; la fila 0 es la de arriba.
- `soltarFicha()` recorre la columna desde abajo (`FILAS - 1`) hasta encontrar una casilla vacía. Recibe el
  tablero **por referencia** porque lo cambia, y devuelve la fila, o `null` si la columna está llena.
- `columnasLibres()`: una columna admite ficha si su casilla de arriba está vacía.
- `reconstruir()` repite las jugadas (código debajo de esta lista). Si algo es imposible, devuelve `null`.
- `leerEstado()` usa `texto()` para todo y `filter_var()` para la semilla. En `index.php`, la columna pasa por
  `filter_var(..., FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => COLUMNAS - 1]])`: un 7,
  un texto o un array dan `false` y "Esa columna no existe.".
- Si la jugada vale, se **añade un carácter** a las jugadas (`$partida['jugadas'] .= $columna;`) y el tablero se
  vuelve a reconstruir al final de la lógica. No se actualiza el tablero a mano: hay una sola fuente de verdad.

```php
// reconstruir() (paso 3, funciones.php)
foreach (str_split($jugadas) as $numero => $caracter) {
    $columna = (int) $caracter;
    $ficha = $numero % 2 === 0 ? ROJA : AMARILLA;
    $fila = soltarFicha($tablero, $columna, $ficha);
    if ($fila === null) {
        return null;                                    // la columna ya estaba llena
    }
    $ultima = "$fila-$columna";
```

> **Para saber más**
>
> Antes del bucle, `reconstruir()` comprueba el texto con `preg_match('/^[0-6]*$/D', $jugadas)` y que no pase de
> 42 caracteres. Sin la `D`, el `$` aceptaría un salto de línea al final y `"33\n"` pasaría el filtro. En esta
> solución `texto()` ya quita ese salto con `trim()`, así que la `D` es una **segunda barrera**: si mañana alguien
> llama a `reconstruir()` con un texto sin recortar, seguirá siendo correcta.

**Una validación a medias, a propósito.** Al recuperar la partida, este paso ya rechaza las jugadas imposibles,
aunque todavía no comprueba los nombres ni la máquina. Fíjate en que el TODO se queda encima:

```php
// TODO: no fiarse del navegador: comprobar toda la partida (nombres, máquina, nivel, semilla y jugadas)
$valida = reconstruir($partida['jugadas']) !== null;
```

Es la primera recompensa de guardar las jugadas: la validación más difícil (¿es posible este tablero?) sale
**gratis** de la función que hacía falta de todas formas para dibujarlo. El paso 6 cambiará esa línea por
`estadoValido()`.

La vista del tablero se escribe ya completa, con las clases `ultima` y `ganadora`, aunque `linea` todavía es
siempre un array vacío.

> **Pruébalo**
>
> Casos 7, 8 y 9. Con el depurador, pon un punto de ruptura en `reconstruir()` y mira cómo se llena `$tablero`
> ficha a ficha. Con F12 cambia `jugadas` por `7` o por `0000000`: "La partida recibida no es válida". Haz cuatro
> en raya vertical: el juego **no** se entera todavía, y eso es lo que añade el paso siguiente.

### 3.4.- Paso 4: cuatro en raya, empate y nueva partida

**Objetivo.** Detectar el cuatro en raya en las cuatro direcciones y el empate, resaltar la línea, cerrar la
partida y volver al formulario (R6).

**Por qué ahora.** Con la reconstrucción funcionando, detectar el ganador es "solo" mirar cada ficha que se pone.
Y la máquina (paso 5) lo necesita: para ganar o tapar tiene que saber qué jugada hace cuatro en raya.

**Qué se escribe.**

- La constante `DIRECCIONES = [[0, 1], [1, 0], [1, 1], [1, -1]]`: horizontal, vertical y las dos diagonales.
- `lineaGanadora()` recorre las cuatro direcciones con **un solo bucle**: para cada una, cuenta desde la ficha
  hacia los dos sentidos (código debajo de esta lista) y, si junta 4 o más casillas, las devuelve.
- `reconstruir()` resuelve sus dos TODO: después de cada ficha llama a `lineaGanadora()` y, si ya había ganador
  antes de una jugada, devuelve `null` (una ficha después de ganar es imposible).
- `estadoPartida()`: `ganada` si hay ganador; `empate` si hay 42 jugadas; si no, `jugando`.
- En `index.php`, el TODO "una partida terminada no admite más fichas", el `$estado` real, el mensaje final y
  *Nueva partida*, que con `leerEstado()` rellena el formulario. Contra la máquina, el jugador 1 es quien no es la
  máquina, y "empieza" se deduce de su color.

```php
// lineaGanadora() (paso 4, funciones.php)
foreach (DIRECCIONES as [$df, $dc]) {
    $casillas = ["$fila-$columna"];
    foreach ([1, -1] as $sentido) {                     // hacia un lado y hacia el otro
```

> **Debes conocer**
>
> **Basta con mirar la última ficha.** Un cuatro en raya solo puede aparecer en la jugada que lo completa, y esa
> ficha forma parte de la línea. Por eso `lineaGanadora()` recibe una posición y no recorre el tablero entero.
> Pero la ficha puede estar **en medio** de la línea (rellenar el hueco de `R R _ R`): por eso se cuenta hacia los
> dos sentidos y se suman, en lugar de mirar solo hacia la derecha o hacia abajo.

> **Pruébalo**
>
> Casos 10 a 13, escribiendo las jugadas con F12 en el campo oculto y pulsando una columna libre (o la que
> completa la línea). Comprueba las cuatro direcciones, las casillas `ganadora` y que una jugada más da
> "La partida ha terminado: empieza otra.". Con `34343430` (una ficha después de ganar) debe salir
> "La partida recibida no es válida". Por último, *Nueva partida*: el formulario debe traer `value="dos" checked`
> y `name="jugador2" value="Brais"`.

### 3.5.- Paso 5: la máquina

**Objetivo.** Que la máquina juegue en la misma petición que la persona, en sus dos niveles, y siempre igual con
la misma semilla (R7).

**Por qué ahora.** La máquina se construye con piezas que ya existen: `columnasLibres()` (paso 3) para saber
dónde puede jugar, `soltarFicha()` y `lineaGanadora()` (pasos 3 y 4) para probar jugadas, y `estadoPartida()`
(paso 4) para no jugar después de que la persona gane.

**Qué se escribe.**

- `ganaria()`: suelta la ficha en el tablero y mira si hace cuatro en raya. El tablero que recibe es una **copia**
  (código debajo de esta lista).
- `regala()`: suelta la ficha propia y pregunta a `ganaria()` si el rival ganaría en la misma columna, es decir,
  **justo encima**.
- `jugadaMaquina()`: en fácil, una columna libre al azar; en normal, las cuatro reglas en orden: ganar, tapar,
  evitar las que regalan y, de las que quedan, la más cercana al centro (al azar entre dos igual de cercanas).
- `moverMaquina()`: si hay máquina, le toca y la partida no ha terminado, elige columna y la **añade** a las
  jugadas (por eso recibe la partida por referencia).
- En `index.php`, la máquina juega al pulsar *Empezar* (si empieza ella) y después de cada jugada de la persona; y
  se resuelve el TODO "nadie puede jugar por la máquina" ("Ahora le toca a la máquina.").

```php
// ganaria() (paso 5, funciones.php)
// $tablero es una copia (los arrays se pasan por valor): se puede probar sin estropear el de verdad
$fila = soltarFicha($tablero, $columna, $ficha);
return $fila !== null && lineaGanadora($tablero, $fila, $columna) !== [];
```

> **Debes conocer**
>
> **Probar en una copia.** En PHP los arrays se pasan **por valor**: `ganaria(array $tablero, …)` recibe su propia
> copia y `soltarFicha()` cambia esa copia, no el tablero de verdad. Así la máquina puede "imaginar" siete jugadas
> (y, en `regala()`, la respuesta del rival a cada una) sin deshacer nada. Si `ganaria()` recibiera el tablero con
> `&`, después de probar la primera columna el tablero tendría una ficha de más.

**La semilla, una por decisión.** `moverMaquina()` llama a `jugadaMaquina()` con
`$partida['semilla'] + strlen($partida['jugadas'])`, y `jugadaMaquina()` hace `mt_srand($azar)` antes de elegir.
Cada turno tiene su propia semilla, pero siempre la misma para el mismo número de jugadas: si se deshace y se
repite la jugada, la máquina contesta igual. Es lo que hace la partida **reproducible** para probarla y depurarla.

> **Pruébalo**
>
> Casos 14 y 15: con F12 pon 7 en el campo `semilla` y juega contra la máquina en nivel normal. Si Ana juega en
> las columnas 1, 2, 3 y 7, debe ganar la máquina ("Gana Máquina con las amarillas en 8 jugadas."). Con el
> depurador, sigue `jugadaMaquina()` cuando tengas tres en raya y mira por qué tapa esa columna. Con F12, pon
> `jugadas` a `33` en una partida en la que la máquina lleva las rojas: "Ahora le toca a la máquina.".

### 3.6.- Paso 6 (solución completa): deshacer, historial y no fiarse del navegador

**Objetivo.** El botón *Deshacer* y el historial (R8), y comprobar que todo el estado recibido es una partida
posible (R9).

**Por qué al final.** *Deshacer* depende de la máquina: contra ella hay que quitar también su jugada, y para eso
hace falta saber de qué color es y de quién es el turno. La validación completa, como siempre, se escribe cuando
se conocen todas las piezas del estado; pero aquí es corta, porque lo más difícil (las jugadas) ya se validaba
desde el paso 3.

**Qué se escribe.**

- `deshacer()` quita caracteres **hasta que vuelva a tocarle a la persona** (código debajo de esta lista). Con dos
  personas, `maquina` es `''`, que nunca es igual al turno, y se quita exactamente una jugada. Si al final le
  tocaría a la máquina (solo estaba su primera jugada), no cambia nada y devuelve `false`.
- `historial()` recorre las jugadas: la posición da el color (par: rojas) y el carácter, la columna (`+ 1`).
- `estadoValido()` añade lo que faltaba: nombres de 1 a 15 caracteres y distintos sin mirar mayúsculas, `maquina`
  en `['', ROJA, AMARILLA]`, el nivel en `NIVELES`, que el color de la máquina se llame *Máquina* y la semilla
  `>= 1`. Termina con la comprobación de siempre: `reconstruir($partida['jugadas']) !== null`.
- En `index.php`, la línea provisional del paso 3 se cambia por `$valida = estadoValido($partida);`, y el botón
  *Deshacer* se deshabilita si no hay jugadas.

```php
// deshacer() (paso 6, funciones.php)
do {
    $jugadas = substr($jugadas, 0, -1);
} while ($jugadas !== '' && turno($jugadas) === $partida['maquina']);
```

> **Debes conocer**
>
> **Cada decisión del análisis simplifica un paso posterior.** Como el estado son las jugadas, deshacer es
> `substr($jugadas, 0, -1)` y el historial es un `foreach` sobre `str_split()`: ninguna de las dos funciones
> necesita un dato nuevo. Si se hubiera guardado el tablero, deshacer exigiría saber **dónde** cayó la última
> ficha, y habría que guardarlo aparte.

> **Pruébalo**
>
> Casos 16, 17 y 18. Contra la máquina, juega una vez y pulsa *Deshacer*: desaparecen tu ficha **y** la de la
> máquina. Con F12 o con `curl -d "columna=0&nombreR=Ana&nombreA=ana&maquina=&nivel=facil&semilla=5&jugadas="
> http://localhost:8000/` prueba nombres iguales, `maquina=X`, `maquina=A` sin que las amarillas se llamen
> *Máquina*, `nivel=dios` y `semilla=0`: todas deben dar "La partida recibida no es válida", sin avisos.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 6 cambia la condición de validación y podría rechazar partidas que antes se aceptaban con razón (por
ejemplo, las de dos personas, en las que el nivel no se usa). Una lista de comprobación para esta tarea:

- [ ] Casos correctos: ganar con cada color en las cuatro direcciones, empate, contra la máquina en los dos niveles
      y empezando cada uno.
- [ ] Casos límite: cuatro en raya con la última ficha en medio de la línea; cinco en raya; ganar con la ficha 42;
      la máquina con una sola columna libre.
- [ ] Deshacer: con dos personas, contra la máquina, tras terminar y cuando solo está la primera de la máquina.
- [ ] Reproducibilidad: la misma semilla y las mismas jugadas dan siempre las mismas respuestas de la máquina.
- [ ] Maliciosos: arrays donde se espera un texto, jugadas imposibles, nombres con HTML, máquina y nivel
      inventados.
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)` y todos los textos que se escriben en el HTML pasan por
      `e()`.

## 5.- Refactorización y mejoras posibles

La solución no tiene `FIXME` pendientes, pero hay detalles que se podrían mejorar, buenos para practicar:

- **Trampas posibles.** Cambiando el campo `jugadas` se puede "hacer jugar" a la máquina donde uno quiera,
  siempre que la partida sea posible. La ampliación del enunciado lo arregla: con la semilla, repetir la partida
  y comprobar que cada jugada de la máquina es la que ella habría hecho.
- **`reconstruir()` varias veces por petición.** Se llama en `estadoValido()`, en el bloque de la columna, en
  `moverMaquina()` y al final. Con 42 jugadas como mucho no importa, y tener una sola fuente de verdad lo hace
  más simple; pero es un buen ejercicio pensar cuándo compensaría calcularlo una vez y pasarlo como parámetro.
- **El tamaño del tablero.** RNF-05 dice que cambiar el tamaño solo exige cambiar `FILAS` y `COLUMNAS`, pero la
  expresión regular de `reconstruir()` tiene la columna máxima escrita a mano (`[0-6]`), y guardar cada jugada en
  **un** carácter limita el tablero a 10 columnas. Para el "cinco en raya" de las ampliaciones también habría que
  convertir en constante el 4 de `count($casillas) >= 4`.
- **La semilla sin máximo.** `filter_var()` exige `min_range => 1` pero no un máximo, y `estadoValido()` solo
  comprueba `>= 1`. Con una semilla cercana a `PHP_INT_MAX`, la suma `semilla + strlen(jugadas)` deja de ser un
  `int` y no se puede pasar al parámetro `int $azar`. Bastaría con un `max_range` (por ejemplo, 999999, el mismo
  límite que usa el formulario).
- **`jugadas[]`.** `texto()` convierte un array en `''`, que es una lista de jugadas válida (la partida vacía). Si
  se quiere que un array sea siempre "estado no válido", como dice RF-09, hay que distinguir el campo ausente o
  con un array del campo vacío.
- Las otras ampliaciones (nivel difícil con *minimax*, marcador entre partidas) son la siguiente **vuelta** del
  proceso: cada una empieza otra vez por el análisis. El marcador, por ejemplo, es un dato que **no** se puede
  calcular a partir de las jugadas y tendría que viajar en sus propios campos ocultos.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Guardar el tablero entero | 42 campos que validar y un tablero que puede ser imposible | Guardar las jugadas y reconstruir (R4) |
| Contar el cuatro en raya solo hacia un lado | No se detecta al rellenar un hueco en medio de la línea | Contar hacia los dos sentidos y sumar |
| Olvidar comprobar los bordes en las diagonales | *Undefined array key* con `-1` o `7` | Comprobar `$f` y `$c` antes de leer la casilla |
| Pasar el tablero con `&` a `ganaria()` | La máquina deja fichas de prueba en el tablero | Por valor: trabaja sobre una copia |
| Dejar que la máquina conteste a una jugada ganadora | Pone una ficha después del cuatro en raya | `moverMaquina()` mira `estadoPartida()` antes |
| Deshacer solo una jugada contra la máquina | Le toca a la máquina y cualquier columna da "Ahora le toca a la máquina." | Quitar jugadas hasta que vuelva a tocar a la persona |
| `strlen()` / `strtolower()` con los nombres | Ñandú cuenta mal y `MÁQUINA` pasa como nombre | `mb_strlen()` y `mb_strtolower()` |
| `mt_rand()` sin semilla | La máquina no repite sus jugadas y no se puede probar | `mt_srand(semilla + número de jugadas)` |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué se guardan las jugadas y no el tablero? **Porque es el estado mínimo: el tablero, el turno y el
>   ganador se calculan repitiéndolas, y al repetirlas se descubre cualquier lista imposible.**
> - ¿Cómo se sabe de quién es el turno? **Por la paridad de `strlen($jugadas)`: con un número par, les toca a las
>   rojas.**
> - ¿Por qué `lineaGanadora()` solo mira la última ficha? **Porque un cuatro en raya solo aparece en la jugada que
>   lo completa, y esa ficha está en la línea.**
> - ¿Por qué `ganaria()` puede soltar fichas sin estropear el tablero? **Porque recibe el array por valor: trabaja
>   sobre una copia.**
> - ¿Para qué se suma el número de jugadas a la semilla? **Para que cada decisión de la máquina tenga su propia
>   semilla, pero siempre la misma con las mismas jugadas: la partida es reproducible.**
> - ¿Por qué la validación del paso 6 es tan corta? **Porque las jugadas, lo más difícil, ya las valida
>   `reconstruir()` desde el paso 3; solo faltan los nombres, la máquina, el nivel y la semilla.**
