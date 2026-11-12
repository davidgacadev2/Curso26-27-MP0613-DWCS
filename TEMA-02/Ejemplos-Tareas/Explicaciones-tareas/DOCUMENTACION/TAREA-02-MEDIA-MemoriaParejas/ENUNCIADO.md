# TAREA-02 (nivel MEDIO): Memoria de parejas

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

La biblioteca municipal organiza tardes de juegos para niños y le pide a **BK Programación** un juego sencillo para
los ordenadores de la sala: el clásico *memory* de parejas de cartas. **Juan** se lo encarga a **Carlos**, que acaba
de terminar el juego de letras escondidas en un rombo (TAREA-02B): esta vez el tablero también guarda un secreto en
cada casilla, pero hay que recordar **dos** cartas por turno y decidir si forman pareja.

Como todavía no han visto sesiones ni bases de datos, **María** le recuerda que toda la partida tendrá que viajar
en la propia página, en campos ocultos, y que el navegador puede devolver cualquier cosa en ellos.

## Enunciado

Programa en PHP una aplicación web en **una única página** (`index.php`, con las funciones en `funciones.php` si
quieres) que se envía a sí misma por **POST** y que permita jugar a encontrar parejas de cartas. El estado de la
partida se guardará en **campos ocultos**, **sin usar `$_SESSION`**, bases de datos ni ficheros.

### Parte 1: configuración de la partida

- **R1. Formulario.** La página pide:
  - el número de **filas** y de **columnas** del tablero (enteros entre 2 y 6);
  - las **letras** de las cartas, en un solo campo (por ejemplo `ABCDEFGH`);
  - el número de **fallos permitidos** (entero entre 1 y 30).

  Tendrá un botón *Empezar* y un botón *Borrar*. Al cargar la página por primera vez propone 4 filas, 4 columnas,
  las letras `ABCDEFGH` y 8 fallos.
- **R2. Validación.** Se comprueba en el servidor y se muestran **todos** los errores a la vez, con un mensaje que
  diga cuál es el problema:
  - filas y columnas fuera de rango o no numéricas;
  - un número **impar** de cartas (`filas × columnas`): con 3 × 3 hay 9 cartas y no se pueden hacer parejas;
  - letras que no sean de la A a la Z (sin espacios, tildes ni ñ), **letras repetidas** (indicando cuáles) o menos
    letras que parejas (con 2 × 3 hacen falta al menos 3 letras);
  - fallos permitidos fuera de rango.

  Las letras se pasan a mayúsculas y se quitan los espacios de los extremos. Si sobran letras, se usan las
  primeras. Si hay errores, el formulario conserva lo escrito (formulario pegajoso).
- **R3. Tablero.** Con datos correctos aparece el tablero: cada letra está **dos veces**, repartidas **al azar**,
  y todas las cartas empiezan **boca abajo**. Cada carta boca abajo es un **botón** (`?`).

### Parte 2: el juego

- **R4. Girar cartas.** En cada turno el jugador gira **dos** cartas:
  - la **primera** se queda boca arriba y se le pide la segunda;
  - si la **segunda** tiene la misma letra, es una **pareja**: las dos se quedan boca arriba para siempre y el
    jugador **suma 10 puntos**;
  - si no, es un **fallo**: **resta 2 puntos**, se suma un fallo y las dos cartas se enseñan **solo en esa
    respuesta** (con el mensaje "No son pareja (C y B)"); en la jugada siguiente vuelven a estar boca abajo.

  No se puede elegir una carta ya encontrada ni volver a pulsar la primera carta del turno.
- **R5. Marcador.** En cuanto se gira la primera carta aparece un marcador con los **puntos**, las **parejas**
  encontradas (`2 de 8`) y los **fallos**. Los puntos pueden ser negativos.
- **R6. Fin de la partida.** La partida termina:
  - **ganada**, cuando se han encontrado todas las parejas: "Has encontrado las 8 parejas con 64 puntos";
  - **perdida**, cuando se **superan** los fallos permitidos (con 3 permitidos, al 4.º fallo): se enseñan todas
    las cartas.

  Al terminar ya no se puede girar ninguna carta (los botones se deshabilitan) y aparece un botón *Nueva partida*
  que vuelve al formulario con la configuración anterior.
- **R7. El estado viaja en la página.** Tras cada clic se conservan el tablero, las parejas encontradas, la carta
  pendiente del turno y el marcador, guardados en campos `hidden`. El tablero (un array de filas) se guarda con
  un campo por carta: `<input type="hidden" name="cartas[1][2]" value="A">`.
- **R8. No fiarse del navegador.** El usuario puede cambiar los campos ocultos con las herramientas del navegador.
  Antes de jugar se comprueba que lo recibido es una partida posible (dimensiones válidas, cada letra exactamente
  dos veces, parejas encontradas completas, carta pendiente existente...). Si no lo es, se muestra un error y se
  vuelve al formulario. Ninguna entrada, por extraña que sea (`carta[]=1`, `cartas[0][0]=<script>`), puede producir
  avisos de PHP ni mostrarse sin escapar.

### Pistas

- `str_split($letras . $letras)` da las cartas; `shuffle()` las baraja y `array_chunk($cartas, $columnas)` las
  reparte en filas.
- Para poder **probar** siempre con el mismo reparto, el formulario puede enviar una semilla en un campo oculto y
  hacer `mt_srand($semilla)` antes de `shuffle()` (que usa el mismo generador que `mt_rand`).
- Identifica cada carta por su posición `"fila-columna"` en el `value` del botón:
  `<button name="carta" value="1-2">?</button>`. Las parejas encontradas se pueden guardar en un único campo
  oculto como lista: `implode(',', ...)` y `explode(',', ...)`.
- `count_chars($letras, 1)` dice cuántas veces aparece cada letra (útil para las repetidas) y
  `array_count_values()` cuenta los elementos de un array.
- Para estudiar la solución, ejecútala con el depurador paso a paso y mira cómo cambia `$partida` en cada clic.

### Ampliaciones opcionales

- Contar las **jugadas** (turnos) y dar una puntuación extra si se termina en pocas.
- Usar **emojis** o nombres de imágenes en lugar de letras (cuidado con `str_split` y los caracteres multibyte).
- Guardar todo el estado en **un solo** campo oculto (`base64_encode(json_encode($partida))`) y comparar las dos
  formas.
- Un modo para **dos jugadores** por turnos, con un marcador para cada uno.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Formulario de configuración con valores iniciales, *Empezar* y *Borrar*, pegajoso (R1) | 0,75 |
| 2. Validación completa con todos los mensajes de error a la vez (R2) | 1,50 |
| 3. Tablero con cada letra dos veces, barajado, y cartas como botones (R3) | 1,25 |
| 4. Girar la primera y la segunda carta; parejas y fallos con su puntuación (R4) | 2,00 |
| 5. Marcador visible desde la primera carta (R5) | 0,50 |
| 6. Fin de la partida ganada y perdida, botones deshabilitados y *Nueva partida* (R6) | 1,25 |
| 7. Estado completo en campos ocultos, también el tablero como array (R7) | 1,25 |
| 8. Validación del estado recibido y salida siempre escapada (R8) | 1,00 |
| 9. Código organizado en funciones, comentado y sin avisos de PHP | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

En [`SOLUCION-01`](SOLUCION-01/): `index.php` decide qué hacer según el botón pulsado y dibuja la página;
`funciones.php` tiene la lógica (validar, crear el tablero, leer el estado, jugar una carta, saber si la partida ha
terminado y validar el estado). Con el depurador, pon un punto de ruptura en `jugar()` y observa `$partida`
(sobre todo `pendiente` y `encontradas`) en dos clics seguidos. Construcción paso a paso en
[`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
