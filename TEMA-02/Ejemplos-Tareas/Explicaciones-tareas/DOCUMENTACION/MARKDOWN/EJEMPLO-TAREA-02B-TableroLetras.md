# Tablero de letras ocultas (EJEMPLO-TAREA-02B): cómo se llega a la solución paso a paso

**Material de la tarea:** [requisitos](../EJEMPLO-TAREA-02B-TableroLetras/material-referencia/TAREAS/Requisitos-TAREA-UD02B.md) ·
[enunciado resuelto (PDF)](../EJEMPLO-TAREA-02B-TableroLetras/material-referencia/TAREAS/MP0613-DWCS-EJEMPLO-RESUELTO-TAREA-UD02B.pdf) ·
[soluciones de referencia](../EJEMPLO-TAREA-02B-TableroLetras/material-referencia/CODIGO%20SOLUCIONES/EJEMPLO-TAREA-02B/) ·
[README del paso a paso](../EJEMPLO-TAREA-02B-TableroLetras/README.md) ·
paso a paso de la [solución 01](../EJEMPLO-TAREA-02B-TableroLetras/PARTE-01-SOLUCION-01/PASO%20A%20PASO/),
la [solución 02](../EJEMPLO-TAREA-02B-TableroLetras/PARTE-01-SOLUCION-02/PASO%20A%20PASO/) y
la [solución 03](../EJEMPLO-TAREA-02B-TableroLetras/PARTE-01-SOLUCION-03/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> **Juan** deja sobre la mesa tres soluciones del mismo juego, escritas por tres programadores de BK Programación:
> "Las tres funcionan. Quiero que me digáis cuál mantendríais vosotros". **Carlos** abre los tres `index.php` a la
> vez y se agobia: "Son casi iguales... ¿dónde está la diferencia?". **María** sonríe: "Esa es la primera lección.
> Si las tres comparten la maqueta, la validación, el dibujo del tablero y las jugadas, la diferencia está en **una
> sola función**: la que esconde las letras. Y para elegir no basta con que funcionen: hay que mirar qué estructura
> de datos usa cada una, si el reparto es de verdad al azar y qué pasa en los casos raros".

Este documento no repite el código de las soluciones: explica **por qué** se construyen en ese orden, qué decisión
se toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y revisión. Los pasos comunes a las tres soluciones (1 a 4, 6 y 7) se
explican una sola vez; el paso 5, donde se separan, se explica para las tres y se comparan.

**Cómo usarlo.** Lee primero los requisitos e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior. Desde la
carpeta de una solución (por ejemplo `PARTE-01-SOLUCION-01`):

```bash
git diff --no-index "PASO A PASO/paso5-letras-ocultas" "PASO A PASO/paso6-jugadas"
```

Y para comparar dos soluciones en el mismo paso, desde la carpeta `EJEMPLO-TAREA-02B-TableroLetras`:

```bash
git diff --no-index "PARTE-01-SOLUCION-01/PASO A PASO/paso5-letras-ocultas" "PARTE-01-SOLUCION-03/PASO A PASO/paso5-letras-ocultas"
```

(en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado del PDF es corto, y
[Requisitos-TAREA-UD02B.md](../EJEMPLO-TAREA-02B-TableroLetras/material-referencia/TAREAS/Requisitos-TAREA-UD02B.md) lo convierte en
11 requisitos funcionales y 13 no funcionales. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| Configurar | RF-01, RF-02 | Un formulario con el lado y la palabra; validar en el servidor (impar entre 5 y 29, 5 letras como mínimo) y un mensaje **por cada** error. |
| Tablero | RF-03, RF-05, RNF-06 | Una matriz lado × lado con `o` fuera y `*` en el rombo; reutilizar el algoritmo del rombo de los boletines de bucles; cada `*` es un **botón**. |
| Esconder | RF-04 | Repartir las letras de la palabra **al azar** bajo los asteriscos (una por asterisco). |
| Jugar | RF-06, RF-07, RF-08 | Acierto: +5, se ve la letra y se quita de la caja; fallo: −2 y un fallo seguido más; el marcador aparece al primer clic. |
| Terminar | RF-09 | Todas las letras acertadas o **más de 3** fallos seguidos: FIN DEL JUEGO y tablero deshabilitado. |
| Recordar | RF-10, RNF-02, RNF-03 | El tablero (un **array**) viaja en campos `hidden`; nada de `$_SESSION`. |

El requisito no funcional que da sentido a la tarea es el **RNF-02**: es el ejemplo del tema en el que se ve cómo
**guardar un array en campos ocultos** y recuperarlo en la petición siguiente. El RNF-09 (solo letras sin tilde ni ñ)
es una simplificación consciente: así se pueden usar `str_split()` y `strlen()`, que trabajan con bytes.

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

| Pregunta | Decisión tomada en las soluciones |
|---|---|
| ¿"Filas y columnas" o un solo valor? | Un único **lado**: el tablero es cuadrado y el rombo, simétrico. |
| ¿"Entre 5 y 30" incluye el 30? | Da igual: tiene que ser impar, así que en la práctica es de 5 a 29 (la etiqueta del formulario dice "impar de 5-29"). |
| ¿"Más de 3 fallos seguidos" es el 3.º o el 4.º? | El **4.º** fallo consecutivo termina el juego. |
| ¿Y las letras repetidas de `caracola`? | Cada aparición va bajo un asterisco distinto; al acertar se quita **una** aparición de la caja. |
| ¿Y si la palabra tiene más letras que asteriscos? | Se esconden las que caben y la palabra **se recorta** (lado 5 tiene 13 asteriscos). |
| ¿Puede haber puntos negativos? | Sí: no se pone mínimo. |
| ¿Ñ y tildes? | No se admiten (`/^[A-Za-z]+$/`). |
| ¿Cómo sabe la página que es la primera visita (y no debe sacar errores)? | Con un campo oculto `primeraVez`. |

> **Recomendación**
>
> Cuando el enunciado es ambiguo ("filas y columnas", "más de 3"), escribe la interpretación que eliges en el README
> o en un comentario. Así quien corrija sabrá que es una decisión y no un despiste.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los casos del [README](../EJEMPLO-TAREA-02B-TableroLetras/README.md) son las pruebas de cada
paso. Se añaden al final los que salen de los `FIXME (pendiente)`, que hoy **fallan**:

| Caso | Entrada | Salida esperada | Desde |
|---|---|---|---|
| 1 | Lado `6`, palabra `sol` | Dos mensajes: lado impar y palabra de 5 caracteres como mínimo; sin tablero | paso 3 |
| 2 | Lado `31`, palabra `caracola` | Mensaje de rango del lado; sin tablero | paso 3 |
| 3 | Lado `7`, palabra `caracola` | Tablero 7×7 con 25 asteriscos: 1 en la fila 1, 7 en la fila 4, 1 en la fila 7 | paso 4 |
| 4 | Pulsar un asterisco con letra | +5 puntos, se ve la letra, desaparece de la caja, fallos seguidos a 0 | paso 6 |
| 5 | Pulsar un asterisco sin letra | −2 puntos y un fallo seguido más | paso 6 |
| 6 | Acertar las 8 letras de `caracola` | 40 puntos (menos los fallos), FIN DEL JUEGO y botones deshabilitados | paso 7 |
| 7 | Cuatro fallos seguidos | −8 puntos, FIN DEL JUEGO y botones deshabilitados | paso 7 |
| 8 | Lado `3`, palabra `caracola` | Debería dar error de rango, pero **se dibuja el tablero** | FIXME |
| 9 | Añadir `&botonJugada=9x9` a la URL | Debería rechazarse, pero hay avisos de PHP y la jugada se procesa | FIXME |
| 10 | Solución 02: lado `5` y `abcdefghijklmnopqrst` | Las letras escondidas y las de la caja deberían coincidir, pero no siempre | FIXME |

Para los casos 4 a 7, las letras ocultas se pueden consultar con F12 en los campos `hidden`. Los casos 8 a 10 son
el material del apartado 5: un buen ejercicio es encontrarlos tú **antes** de leer los FIXME.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada clic en un asterisco es una petición nueva: el script empieza desde cero y tiene
que reconstruir la partida con lo que le envía el formulario. Y como el formulario usa `method="get"`, todo lo que
envía queda además a la vista en la **barra de direcciones**.

| Dato | Estructura | Dónde vive | Comentario |
|---|---|---|---|
| Lado | int | campo de texto `lado` | Viaja en cada clic, pero en la jugada no se usa: `dibujarTablero()` lo calcula con `count($tablero)` |
| **Palabra que queda** | string | **la caja de texto** `palabra` | El RF-06 pide que la letra acertada desaparezca de la caja: la caja visible **es** el estado |
| Primera vez | "false" | campo oculto `primeraVez` | Para no sacar errores en la primera visita |
| Lo que se ve de cada casilla | matriz de strings (`*`, `o` o la letra) | campos ocultos `tablero[f][c]` | Soluciones 01 y 02 |
| La letra oculta de cada casilla | matriz (`"0"` = ninguna) | campos ocultos `letras[f][c]` | Soluciones 01 y 02 |
| Las dos cosas a la vez | matriz de pares `[0]` visible, `[1]` oculta | campos ocultos `tablero[f][c][0]` y `tablero[f][c][1]` | Solución 03 |
| Puntos, fallos seguidos, fin del juego | int | campos ocultos (`marcador.php`) | Aparecen a partir del primer clic |
| Casilla pulsada | string `"FILAxCOLUMNA"` | `value` del botón `botonJugada` | Solo se envía el botón pulsado |
| Mensajes de error | string | variable `$mensaje` | Solo dura la petición |

> **Debes conocer**
>
> **Un array en campos ocultos.** Si el nombre de un campo lleva corchetes, PHP reconstruye el array al recibirlo:
> con `name="tablero[2][3]"`, el valor llega en `$_GET['tablero'][2][3]`. Basta con escribir un campo oculto por
> casilla al generar la página y leer `$_GET['tablero']` en la petición siguiente: vuelve la matriz entera. Las claves
> numéricas (`"2"`) se convierten en enteros, pero los **valores llegan siempre como strings** (el `0` de "no hay
> letra" vuelve como `"0"`), y por eso las soluciones comparan con `!= "0"`.

Así queda en el HTML una casilla del rombo con la letra `r` escondida (soluciones 01 y 02):

```html
<td><button type="submit" name="botonJugada" value="2x3" >*</button></td>
<input type='hidden' name="tablero[2][3]" value="*">
<input type='hidden' name="letras[2][3]" value="r">
```

> **Para saber más**
>
> Fíjate en que el estado de esta tarea **no es mínimo**, a diferencia del ahorcado: `finJuego` se podría calcular
> (no quedan letras en la palabra o hay más de 3 fallos seguidos) y el lado también (`count($tablero)`). Cada dato
> guardado que se puede calcular es un dato más que alguien puede manipular y que podría contradecirse con los demás.
> Lo retomamos en el apartado 5.

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Las tres soluciones reparten el trabajo igual:

| Fichero | Responsabilidad |
|---|---|
| `index.php` | Las dos funciones, el script que decide qué hacer y el esqueleto HTML |
| `entrada.php` | El formulario del lado y la palabra (un `echo` con *heredoc*), con lo escrito como `value` |
| `marcador.php` | Los puntos y los fallos seguidos, y sus tres campos ocultos (`puntos`, `fallosSeguidos`, `finJuego`) |
| `styles.css` | El aspecto de la tabla (RNF-12) |

Y dos funciones, cuya **firma** ya revela la estructura de datos elegida:

| Función | Soluciones 01 y 02 | Solución 03 |
|---|---|---|
| Generar | `generarInformacionTablero(&$tablero, &$letras, $lado, &$palabra)` | `generarInformacionTablero(&$tablero, $lado, &$palabra)` |
| Dibujar | `dibujarTablero($tablero, $letras, $deshabilitar = false)` | `dibujarTablero($tablero, $deshabilitar = false)` |

El script sigue la estructura de los apuntes. La pregunta que decide el camino es **qué botón se ha pulsado**: si
no llega `botonJugada`, es el formulario de entrada (o la primera visita); si llega, es una jugada.

```text
1. Recibir         -> lado, palabra, primeraVez y, si los hay, tablero y letras
2. ¿botonJugada?
   NO  -> validar lado y palabra
          - errores: formulario + un mensaje por error (salvo la primera vez)
          - correcto: generarInformacionTablero() + formulario + dibujarTablero()
   SÍ  -> recibir puntos, fallos seguidos y fin del juego
          - acierto o fallo en la casilla pulsada
          - ¿fin del juego?
          - formulario + marcador + dibujarTablero() (deshabilitado si ha terminado)
```

> **Recomendación**
>
> `generarInformacionTablero()` recibe `$palabra` **por referencia** porque puede recortarla. Por eso, en el script,
> `include "entrada.php"` va **después** de generar el tablero (el comentario del código lo avisa): si fuera antes, la
> caja mostraría la palabra sin recortar. Una función que modifica sus parámetros obliga a pensar en el orden de las
> llamadas; si es posible, es más claro que **devuelva** el resultado.

### 2.2.- Estados de la página

La página tiene cuatro estados. El evento es el botón pulsado (*Enviar*, un asterisco o el enlace *Borrar*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| Inicio (sin `primeraVez`) | *Enviar* [datos válidos] | generar el tablero y esconder las letras | Jugando (sin marcador) |
| Inicio / Errores | *Enviar* [datos no válidos] | un mensaje por error | Errores |
| Jugando | asterisco [hay letra] | +5, se ve la letra, se quita de la caja, fallos a 0 | Jugando |
| Jugando | asterisco [hay letra y ya no quedan letras] | +5 | Fin |
| Jugando | asterisco [no hay letra y fallos seguidos < 3] | −2, fallos + 1 | Jugando |
| Jugando | asterisco [no hay letra y ya había 3 fallos seguidos] | −2, fallos + 1 | Fin |
| Jugando | *Enviar* [la palabra que queda es válida] | tablero nuevo con esa palabra; el marcador vuelve a empezar | Jugando |
| Fin | (botones deshabilitados) | — | Fin |
| Cualquiera | *Borrar* | enlace a la página sin parámetros | Inicio |

La flecha *Jugando → Enviar* no la pide nadie, pero existe: la caja con la palabra restante y el botón *Enviar*
siguen en la página durante la partida. Pulsar *Enviar* (o *Intro* en la caja) no envía `botonJugada`, así que el
script lo trata como una partida nueva con la palabra que queda, o saca el error de longitud si quedan menos de 5
letras. Dibujar el diagrama sirve precisamente para descubrir estas flechas antes de que las descubra el usuario.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden va de lo que se
**ve** (maqueta, formulario, tablero) a lo que se **juega** (letras, jugadas, fin):

| Paso | Qué se añade | Requisitos | ¿Difieren las soluciones? |
|---|---|---|---|
| 1 | Maqueta: solo HTML y CSS, con todos los estados de ejemplo | RNF-12 | No |
| 2 | Ficheros y funciones (solo la firma) con TODOs | RNF-05 | Solo en los TODO y las firmas |
| 3 | Recepción y validación del lado y la palabra | RF-01, RF-02 | No |
| 4 | Rellenar el rombo y dibujarlo con un botón por asterisco | RF-03, RF-05, RNF-06 | La 03 usa una matriz de pares |
| **5** | **Esconder las letras al azar** | RF-04 | **Sí: es el paso clave** |
| 6 | Estado en campos `hidden`, acierto, fallo y marcador | RF-06, RF-07, RF-08, RF-10 | La 03 accede con `[0]` y `[1]` |
| 7 | **Solución completa:** fin del juego y tablero deshabilitado | RF-09 | No |

### 3.1.- Paso 1: la maqueta

**Objetivo.** Ver la página terminada antes de programar nada: el formulario, los tres mensajes de error, el
marcador, el texto FIN DEL JUEGO y un tablero de lado 5 con una letra descubierta.

**Por qué empezar así.** La maqueta responde preguntas de la interfaz que luego condicionan el código: dónde salen
los errores, que el marcador va encima del tablero, que una letra descubierta es un **botón deshabilitado** y que
cada asterisco es un `<button>` con `name="botonJugada"` y un `value` con su posición:

```html
<td><button type="submit" name="botonJugada" value="1x3" >*</button></td>
```

**Decisión clave.** Un solo nombre (`botonJugada`) para todos los botones y la posición en el `value`: como un
formulario solo envía el botón que se ha pulsado, el servidor recibirá un único dato que dice qué casilla es.

> **Pruébalo**
>
> Abre la maqueta y pulsa un asterisco: la página se recarga igual (aún no hay PHP), pero mira la barra de
> direcciones: `?lado=5&palabra=pulpo&botonJugada=1x3`. Es exactamente lo que recibirá el servidor. Pulsa la `p`
> descubierta: no hace nada, porque un botón `disabled` no se puede pulsar ni se envía.

### 3.2.- Paso 2: descomponer el problema en TODOs

**Objetivo.** Repartir la maqueta en ficheros (`entrada.php`) y funciones (solo la firma), con un TODO en cada parte
que hay que programar y el paso en que se hará.

**Por qué ahora.** Los TODO son el plan escrito dentro del código. Además, la página ya se ejecuta: usa valores
provisionales (`$lado = 5; $palabra = "";`) para que el formulario salga aunque todavía no se lea nada de `$_GET`.

**La decisión de diseño ya está aquí.** Compara el paso 2 de las tres soluciones: el script es el mismo, pero los TODO
de `generarInformacionTablero()` describen tres formas distintas de esconder las letras, y la solución 03 cambia
además las firmas (sin `$letras`). En la solución 01:

```php
// TODO (paso 5): recorrer las casillas con asterisco, una y otra vez, decidiendo a cara o cruz
//                (random_int) si se esconde ahí la siguiente letra, hasta colocarlas todas o
//                quedarse sin asteriscos libres  [RF-04]
```

En la 02, una lista barajada (`str_split`, `array_pad`, `shuffle`, `array_shift`); en la 03, una matriz de pares y
una fórmula para contar los asteriscos. Decidir la estructura de datos **antes** de programar es lo que permite que
el resto del código no dependa de cómo se esconden las letras.

> **Pruébalo**
>
> Sale el formulario con lado 5 y la palabra vacía. Escribe `7` y `caracola` y pulsa *Enviar*: la página vuelve con
> 5 y vacío otra vez. No es un error: son los valores provisionales, y el TODO del paso 3 dice cómo quitarlos. Con
> la extensión *Todo Tree* de VS Code verás la lista de lo que queda por hacer.

### 3.3.- Paso 3: recibir y validar el lado y la palabra

**Objetivo.** Leer el lado y la palabra, validarlos y sacar un mensaje por cada error, conservando lo escrito en el
formulario (RF-01, RF-02).

**Por qué ahora.** Ninguna otra parte puede funcionar con un lado par o una palabra vacía: validar primero permite
que el código del tablero **suponga** que los datos son correctos.

**Qué se escribe.**

- La recepción, con un valor por defecto si no llega nada (el código va debajo de esta lista). La palabra que no
  cumple `/^[A-Za-z]+$/` se convierte en `""`, y eso garantiza que `strlen()` y `str_split()` cuenten bien.
- Un campo oculto `primeraVez=false` que se escribe **siempre**: en la primera visita no llega, `$primeraVez` vale
  `true` y no se sacan mensajes de error aunque la palabra esté vacía.
- Una condición que decide si hay errores y, dentro, un `if` por cada error, acumulando los mensajes en `$mensaje`.
- Si todo es correcto, un texto provisional: "Datos correctos: aquí se dibujará un tablero de lado 7".

```php
$lado       = empty($_GET['lado'])       ? 5: intval($_GET['lado']);
$palabra    = (empty($_GET['palabra']) || preg_match("/^[A-Za-z]+$/", $_GET['palabra']) !== 1)    ? "": $_GET['palabra'];
$primeraVez = empty($_GET['primeraVez']) ? true: false;
```

**Un defecto que se queda.** La condición general y los mensajes no usan los mismos números:

```php
// FIXME (pendiente): aquí se comprueba $lado < 1, pero el mensaje de más abajo dice "mayor o igual
//        a 5": los lados 1 y 3 pasan la validación y se dibuja el tablero  [RF-02]
if ( $lado < 1 || $lado % 2 != 1 || $lado > 30 || strlen($palabra) < 5 || $palabra == "") { // validamos datos
```

Como el último paso es la solución de referencia sin cambios, el defecto no se corrige: se marca como
`FIXME (pendiente)` y se trabaja en el apartado 5.

> **Error típico**
>
> Escribir la misma regla en dos sitios (aquí, `$lado < 1` en la condición y `$lado < 5` en el mensaje). Cuando
> cambia una, la otra se queda atrás. Si la regla está en una constante (`LADO_MINIMO = 5`) o en una función que
> devuelva la lista de errores, la condición general es simplemente "¿la lista está vacía?".

> **Pruébalo**
>
> Casos 1 y 2: con `6` y `sol`, dos mensajes; con `31` y `caracola`, el de rango. En la primera visita no debe salir
> ningún mensaje. Prueba también `pañuelo`: la caja se vacía y sale el mensaje de longitud (la palabra no válida se
> ha convertido en `""`). Y el caso 8: con lado `3` sale "Datos correctos", que es el FIXME.

### 3.4.- Paso 4: el rombo y el tablero

**Objetivo.** Rellenar la matriz del tablero y dibujarla como una tabla en la que cada asterisco es un botón
(RF-03, RF-05).

**Por qué ahora.** Es lo que se **ve**, y las letras del paso 5 se esconden debajo de los asteriscos: sin rombo no
hay dónde esconderlas.

**Qué se escribe.** `generarInformacionTablero()` reutiliza el ejercicio del rombo de asteriscos (RNF-06): en cada
fila, una casilla está en el rombo si su columna dista del centro como mucho `$siAsterisco`, que crece hasta la fila
central y después decrece. Los índices van de 1 a `$lado`, como la posición del botón:

```php
$medio = floor($lado / 2) + 1;
// ... dentro de los dos for:
if ( ($columna >= $medio - $siAsterisco) && ($columna <= $medio + $siAsterisco) ) {
    $tablero[$fila][$columna] = "*";
// ... al terminar cada fila:
$fila < $medio ? ++$siAsterisco : --$siAsterisco;
```

`dibujarTablero()` recorre el tablero con el **mismo** algoritmo y escribe una celda `o`, un botón `*` con
`value="{$fila}x{$columna}"` o, si la casilla ya tiene una letra, un botón deshabilitado con ella.

**Diferencias entre soluciones.** Aquí aparece la primera, y es de estructura de datos:

- **Solución 01**: rellena a la vez `$letras` con `"0"` ("no hay letra oculta") y **cuenta los asteriscos**
  (`$numeroAsteriscos++`), que necesitará el paso 5.
- **Solución 02**: solo el tablero y la cuenta; la matriz `$letras` se crea entera en el paso 5.
- **Solución 03**: cada casilla es un par: `$tablero[$fila][$columna][0] = "*"` (lo visible) y `[1]` (lo oculto, de
  momento `"0"`). **No** cuenta los asteriscos en el bucle: en el paso 5 los necesitará *antes* de recorrerlo.

**Dos FIXME de paso.** El código ya funciona, pero a medias, y lo dice:

- `// FIXME (paso 5): todavía no hay ninguna letra escondida`
- `// FIXME (paso 6): al pulsar un asterisco el tablero desaparece: su estado no viaja en la petición (HTTP no tiene estado)`

El segundo es una lección en sí mismo: el tablero existe solo mientras se ejecuta la petición que lo ha generado.

> **Pruébalo**
>
> Caso 3: lado `7` y `caracola`. Cuenta los asteriscos por fila (1, 3, 5, 7, 5, 3, 1 = 25) y, con Ctrl+U, comprueba
> que el del centro es `value="4x4"`. Prueba lados 5, 9 y 29. Después pulsa un asterisco: el tablero desaparece y
> solo queda el formulario, como anuncia el FIXME.

### 3.5.- Paso 5: esconder las letras al azar (donde se separan las tres soluciones)

**Objetivo.** Completar `generarInformacionTablero()`: repartir las letras de la palabra bajo asteriscos elegidos al
azar, una por asterisco, y recortar la palabra si no caben (RF-04).

**Por qué ahora (y por qué aislado).** Necesita el rombo y el número de asteriscos del paso 4. Y está **encerrado en
una función**: al resto del programa le da igual cómo se esconden las letras, solo le importa el resultado (qué letra
hay bajo cada casilla). Por eso las soluciones 01 y 02, que usan la misma estructura de datos, son idénticas en todo
lo demás; la 03, que cambia la estructura, se diferencia también en los pasos 4 y 6. **Cambiar de algoritmo afecta a
una función; cambiar de estructura de datos afecta a todo el programa.**

#### Solución 01: recorrer el tablero echando a cara o cruz

Recorre las casillas una tras otra con un contador que, al llegar al final, vuelve a empezar; en cada asterisco
libre lanza una moneda (`random_int(0,1)`) y, si sale 0, coloca **la siguiente letra** de la palabra. Para no poner
dos letras en la misma casilla, marca las usadas en una copia del tablero:

```php
$tableroAux = $tablero;  // copiamos el tablero para no modificar el original
while ( $numeroAsteriscos > 0 && $indiceLetra < strlen($palabra) ) {
    $fila    = floor( $contador/$lado) + 1;  // calculamos indice de fila (1, ..., $lado)
    $columna = $contador % $lado + 1;     // calculamos indice de columna (1, ..., $lado)
    if ( $tableroAux[$fila][$columna] == "*" ) {
        if (random_int(0,1) == 0 ) {   // echamos a cara o cruz si colocamos una letra
            $tableroAux[$fila][$columna] = "0";
            $letras[$fila][$columna]     = substr($palabra, $indiceLetra++, 1);
            $numeroAsteriscos--;
```

Al terminar, `$palabra = substr($palabra, 0, $indiceLetra);` deja en la caja exactamente las letras colocadas.

- **A favor:** no necesita ninguna función de arrays, solo bucles y aritmética (fila y columna a partir de un único
  contador con `/` y `%`); el recorte de la palabra es siempre correcto.
- **En contra:** el número de vueltas es aleatorio (termina, pero no se sabe cuándo), y sobre todo **el reparto no es
  uniforme**. Las letras se colocan en el orden de la palabra según se recorre el tablero, de arriba abajo, y cada
  asterisco tiene un 50 % de quedarse con la siguiente. Simulando el algoritmo 100 000 veces con lado 7 y `caracola`:
  el asterisco de la fila 1 esconde letra en la mitad de las partidas, y el de la fila 7 en menos del 2 % (con un
  reparto uniforme, los dos estarían en 8/25 = 32 %); en el 60 % de las partidas las 8 letras están en las 4
  primeras filas (uniforme: alrededor del 1 %), y en casi el 98 % aparecen en el orden de la palabra leyendo el
  tablero. Un jugador que lo sepa empieza siempre por arriba.

#### Solución 02: barajar una lista y repartirla

Convierte el problema en otro más sencillo: en lugar de elegir casillas al azar, **baraja una lista** con tantos
elementos como asteriscos (las letras más ceros de relleno) y se la reparte a los asteriscos en orden:

```php
$listaLetras = str_split($palabra);
$listaLetras = array_pad($listaLetras, $numeroAsteriscos , 0);   // 0 = no hay letra oculta
shuffle($listaLetras);  // las barajamos
$letras = array_fill(1, $lado, array_fill(1, $lado, 0));  // creamos un array bidimensional todo a 0
// ... en los dos for, si la casilla es "*":
$letras[$fila][$columna] = array_shift($listaLetras);
```

- **A favor:** el reparto es **uniforme** (`shuffle()` da cualquier orden con la misma probabilidad); el número de
  operaciones es fijo; y es el uso de la biblioteca estándar que pide el RNF-04: cada función hace una parte del
  trabajo y su nombre dice cuál.
- **En contra:** `array_shift()` saca el primer elemento y renumera el resto en cada llamada (con lado 29 son 421
  asteriscos: no se nota, pero un índice que avanza, como en la 03, es más barato). Y tiene un defecto cuando la
  palabra **no cabe**: `array_pad()` no recorta, así que se baraja la palabra entera y los asteriscos se quedan con
  letras **cualesquiera**, mientras que `$palabra = substr($palabra, 0, $numeroAsteriscos);` conserva **las primeras**.
  Es el `FIXME (pendiente)` de esta solución (caso 10).

#### Solución 03: una sola matriz y una fórmula

Usa la misma idea de la lista barajada, pero en el orden correcto y en **una sola pasada**: primero calcula cuántos
asteriscos tendrá el rombo, recorta la palabra, baraja y, al rellenar el rombo, cada asterisco se lleva el siguiente
elemento de la lista. Como el recorrido es el mismo en el que se crea el tablero, necesita el número de asteriscos
**antes** del bucle, y lo obtiene con una suma (1 + 3 + … + (lado − 2), dos veces, más la fila central):

```php
$numeroAsteriscos = 0;
for ($i=1; $i <= $lado -2; $i = $i +2)
    $numeroAsteriscos += $i;
$numeroAsteriscos *= 2;
$numeroAsteriscos += $lado;

// Eliminar de la palabra las letras que no caben:
$palabra = substr($palabra, 0, $numeroAsteriscos);
```

Y en el bucle del rombo, en lugar del `"0"` provisional del paso 4:

```php
$tablero[$fila][$columna][1] = $letras[$indiceLetra++];  // colocamos letra
```

- **A favor:** recorta **antes** de barajar, así que la caja y las letras escondidas siempre coinciden; reparto
  uniforme; una sola pasada y sin `array_shift()`; y una sola estructura que viaja entera (lo que se ve y lo que se
  esconde de una casilla van juntos, no pueden desincronizarse dos matrices).
- **En contra:** `$tablero[$fila][$columna][1]` se lee peor que `$letras[$fila][$columna]` (¿qué era el 1?); los
  campos ocultos tienen nombres más largos (`tablero[4][4][1]`); y la **forma del rombo está escrita dos veces**: en la
  fórmula y en el bucle. Si mañana el rombo cambia, hay que acordarse de cambiar las dos.

> **Para saber más**
>
> La suma de la solución 03 tiene una fórmula cerrada. Los impares 1 + 3 + … + (lado − 2) suman ((lado − 1) / 2)²,
> así que el total es 2 · ((lado − 1) / 2)² + lado = (lado² + 1) / 2. Con lado 7, (49 + 1) / 2 = 25; con lado 5, 13.
> En PHP: `intdiv($lado * $lado + 1, 2)`.

#### Comparación y elección

| | Solución 01 | Solución 02 | Solución 03 |
|---|---|---|---|
| Estructura | Dos matrices: `$tablero` y `$letras` | Dos matrices | Una matriz de pares `[0]` / `[1]` |
| Algoritmo | Recorrer y echar a cara o cruz | Barajar la lista y repartir | Contar, recortar, barajar y repartir al rellenar |
| ¿Reparto uniforme? | **No**: letras arriba y en orden | Sí | Sí |
| Número de operaciones | Aleatorio | Fijo (dos pasadas) | Fijo (una pasada) |
| Palabra más larga que el rombo | Correcto | **Defecto** (FIXME) | Correcto |
| Funciones de la biblioteca | `random_int`, `substr` | `str_split`, `array_pad`, `shuffle`, `array_fill`, `array_shift` | `str_split`, `array_pad`, `shuffle` |
| Legibilidad | Bucle con contador y `%`: hay que pensarlo | La más clara | Índices `[0]` y `[1]` poco expresivos |

> **Ejercicio resuelto**
>
> **¿Cuál elegirías y por qué?** La solución 01 queda descartada por el requisito: "al azar" significa que cualquier
> asterisco tiene la misma probabilidad de esconder una letra, y en la 01 no es así. Entre la 02 y la 03, el
> algoritmo correcto es el de la 03 (recortar **antes** de barajar), y la estructura más legible es la de la 02 (dos
> matrices con nombre). La mejor opción combina las dos: **la solución 02 con su FIXME corregido**, que consiste solo
> en mover una línea: `$palabra = substr($palabra, 0, $numeroAsteriscos);` pasa a estar **antes** de
> `str_split($palabra)`. Así se conservan los nombres claros, el reparto uniforme, la cuenta de asteriscos en el
> mismo bucle que dibuja el rombo (sin fórmula duplicada) y la caja coincide siempre con lo escondido. Si se quiere
> una sola estructura, la de la 03 es válida, pero mejor con claves con nombre (`['ve' => '*', 'oculta' => 'r']`)
> que con `[0]` y `[1]`.

> **Pruébalo**
>
> En este paso **no se ve nada nuevo**: las letras están en `$letras` (o en `[1]`), pero todavía no se escriben en la
> página. Es el momento del depurador (RNF-10): pon un punto de ruptura justo después de llamar a
> `generarInformacionTablero()` y mira la matriz. Genera varios tableros de lado 7 con `caracola` en la solución 01 y
> fíjate en qué filas caen las letras; repite con la 02. Prueba también lado `5` con `abcdefghijklmnopqrst`: la caja
> debe mostrar 13 letras en las tres soluciones (en la 02 son las 13 primeras, pero las escondidas son otras).

### 3.6.- Paso 6: las jugadas (el tablero viaja en campos ocultos)

**Objetivo.** Que la partida sobreviva al clic: guardar el tablero en campos `hidden`, recuperarlo, decidir si la
casilla pulsada es acierto o fallo y mostrar el marcador (RF-06, RF-07, RF-08, RF-10).

**Por qué ahora.** Es el FIXME del paso 4: hasta que el estado no viaja, no hay segunda petición posible. Y ahora ya
hay algo que guardar: las letras escondidas del paso 5.

**Qué se escribe.**

- En `dibujarTablero()`, junto a cada celda, sus campos ocultos (`tablero[f][c]` y `letras[f][c]`; en la 03,
  `tablero[f][c][0]` y `tablero[f][c][1]`). Se aprovecha el mismo recorrido que dibuja la celda: "en una sola
  tirada", dice el comentario.
- Al principio del script, la recuperación: `$tablero = empty($_GET['tablero']) ? "" : $_GET['tablero'];` (y lo
  mismo con `$letras` en la 01 y la 02).
- En la rama de la jugada, los puntos, los fallos seguidos y el fin del juego (0 si no llegan) y la posición pulsada
  con `explode("x", $_GET['botonJugada'])`.
- El acierto, si la letra oculta no es `"0"` (código debajo de esta lista); el fallo, `$puntos -= 2` y
  `$fallosSeguidos++`.
- `marcador.php`: los puntos y los fallos seguidos a la vista, y los tres campos ocultos. Como solo se incluye en la
  rama de la jugada, el marcador aparece a partir del primer clic (RF-08) sin ningún `if` más.

```php
if($letras[$posicionX][$posicionY] != "0" ) {
    // Sí la hay: cambiamos el asterisco por la letra
    $tablero[$posicionX][$posicionY] = $letras[$posicionX][$posicionY];
    // Quitamos la letra de la palabra
    $palabra = substr_replace($palabra, "", strpos($palabra, $letras[$posicionX][$posicionY]), 1);
```

`strpos()` encuentra **la primera** aparición de la letra en la palabra que queda y `substr_replace()` la sustituye
por `""`: con `caracola`, acertar una `a` quita una sola `a` de la caja. Al cambiar la casilla del tablero por la
letra, `dibujarTablero()` la pinta ya como botón deshabilitado.

> **Debes conocer**
>
> **La caja de texto también es estado.** La palabra que queda no está en ningún campo oculto: viaja en el campo de
> texto `palabra` de `entrada.php`, porque el RF-06 pide que se vea cómo desaparecen las letras. Cada jugada lee la
> palabra de `$_GET['palabra']` (con la misma expresión regular del paso 3), le quita una letra y la vuelve a escribir
> como `value`. Un dato que el usuario puede ver y **editar** es un dato que hay que validar.

**Lo que queda pendiente.** La jugada confía en todo lo que llega: no comprueba que `botonJugada` tenga la forma
`FxC`, que la casilla exista, que sea un asterisco sin descubrir ni que los puntos sean los que había. Se marca con
un `FIXME (pendiente)` (RNF-08). Y otro FIXME de paso: `// FIXME (paso 7): $finJuego siempre vale 0: la partida no
termina nunca`.

> **Pruébalo**
>
> Casos 4 y 5. Con F12 busca los campos `letras[...]` que no valen `"0"`: son las letras escondidas. Pulsa una
> (+5, se ve la letra, desaparece de la caja, fallos a 0) y una vacía (−2, un fallo más). Con `caracola`, acierta una
> `a` y comprueba que en la caja queda `cracola` (se ha quitado la primera `a`). Mira también la barra de direcciones: con `method="get"` el tablero
> entero viaja en la URL. Y haz 5 o 6 fallos seguidos: la partida no termina, que es el FIXME del paso 7.

### 3.7.- Paso 7 (solución completa): el fin del juego

**Objetivo.** Terminar la partida al acertar todas las letras o al 4.º fallo seguido, y dibujar el tablero
deshabilitado (RF-09).

**Por qué al final.** El fin del juego es una consecuencia de las jugadas: hasta que no hay aciertos y fallos que
cuenten, no hay nada que pueda terminar. Y se añade sin romper nada de lo anterior.

**Qué se escribe.**

- `dibujarTablero()` recibe un parámetro nuevo **con valor por defecto**: `function dibujarTablero($tablero, $letras,
  $deshabilitar = false)`. Las llamadas que ya existían no cambian; solo la del fin del juego pasa `true`, y entonces
  los asteriscos se escriben con `disabled` y sin `value`.
- Tras un acierto, `if ( strlen($palabra) === 0 )` significa que no quedan letras: `$finJuego = 1`.
- Tras un fallo, el 4.º seguido también termina (código debajo de esta lista).
- Si `$finJuego == 1`, se escribe `<h1>FIN DEL JUEGO</h1>` y el tablero deshabilitado. El valor viaja en el campo
  oculto `finJuego`, y la jugada solo se procesa si `$finJuego != 1`.

```php
if ( $fallosSeguidos < 3 ) {
    $fallosSeguidos++;
} else {
    $fallosSeguidos++;
    $finJuego = 1;
}
```

Fíjate en que `$fallosSeguidos++` está en las dos ramas: es la forma de decir "más de 3" (el 4.º fallo termina, como
se decidió en el análisis), aunque se podría escribir más corto (apartado 5).

> **Pruébalo**
>
> Casos 6 y 7: acierta las 8 letras de `caracola` (40 puntos menos los fallos) y, en otra partida, falla 4 veces
> seguidas (−8). En los dos casos: FIN DEL JUEGO y ningún botón se puede pulsar. Si con F12 habilitas un botón y le
> pones un `value`, la jugada no se procesa (`finJuego` vale 1), aunque la página sale vacía, solo con el título.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 7 toca `dibujarTablero()` y las dos ramas de la jugada, que se usan en todos los demás casos.

- [ ] Validación: lados 5, 7, 29 (bien); 4, 6, 31, `abc` (mal); palabras de 4 y 5 letras; `pañuelo` y `canción`.
- [ ] Tablero: el número de asteriscos es (lado² + 1) / 2 (13 con lado 5, 25 con lado 7) y el rombo es simétrico.
- [ ] Letras repetidas: con `caracola`, cada `a` acertada quita una sola `a` de la caja.
- [ ] Palabra más larga que el rombo: lado 5 con 20 letras; la caja se queda con 13 (y, en la 02, el caso 10).
- [ ] Fin del juego: ganar con 0 fallos; perder exactamente en el 4.º fallo seguido; un acierto después de 3 fallos
      pone el contador a 0 y la partida sigue.
- [ ] El marcador no aparece hasta el primer clic; la primera visita no muestra errores.
- [ ] Los FIXME (pendiente): casos 8, 9 y 10. Deben **fallar** en las soluciones de referencia; si los corriges
      (apartado 5), deben pasar y los demás casos seguir dando lo mismo.

## 5.- Revisión: los FIXME (pendiente) y otras mejoras

El último paso es la solución de referencia **sin cambiar el código**: solo se han añadido comentarios, entre ellos
los `FIXME (pendiente)`, que señalan defectos conocidos. Corregirlos es un buen ejercicio de revisión de código
ajeno: primero se reproduce el defecto con un caso de prueba y después se corrige sin romper los demás.

> **Ejercicio resuelto**
>
> **FIXME 1 (las tres soluciones): la validación del lado.** Con lado `3` y `caracola` se dibuja el tablero, porque
> la condición dice `$lado < 1` y el mensaje "mayor o igual a 5". Corrección: usar los mismos límites en la condición
> y en los mensajes (`$lado < 5 || $lado > 29`) y que el mensaje los diga los dos ("entre 5 y 29"). Ahora, con lado
> `31`, el mensaje que sale ("mayor o igual a 5") ni siquiera describe el error.

> **Ejercicio resuelto**
>
> **FIXME 2 (solución 02): la palabra que no cabe.** Con lado 5 (13 asteriscos) y `abcdefghijklmnopqrst`, se esconden
> 13 letras cualesquiera de las 20, pero la caja conserva `abcdefghijklm`. Si se descubre, por ejemplo, la `q`,
> `strpos()` no la encuentra (devuelve `false`) y `substr_replace()` acaba quitando otra letra. Corrección: recortar
> la palabra **antes** de partirla y barajarla, como hace la solución 03; es mover una línea.

El tercer FIXME es el más amplio:

```php
// FIXME (pendiente): no se valida lo que llega del cliente (botonJugada, tablero, letras,
//        puntos...): con la URL manipulada hay errores o se puede hacer trampa  [RNF-08]
```

Con `&botonJugada=9x9` en la URL, `$letras[9][9]` no existe: salen avisos *Undefined array key* y el script sigue
adelante con un valor que no existe. También se puede pulsar dos veces la misma letra (cambiando a mano el `value`
de un botón deshabilitado), escribir otra palabra en la caja a mitad de partida o poner `puntos=1000`. Una parte se
puede arreglar validando la jugada antes de procesarla. Por ejemplo (es una propuesta, no está en las soluciones):

```php
// solo se acepta un asterisco que exista y que no esté descubierto
$jugadaValida = is_array($tablero) && is_string($_GET['botonJugada'])
    && preg_match('/^(\d+)x(\d+)$/', $_GET['botonJugada'], $m) === 1
    && ($tablero[$m[1]][$m[2]] ?? null) === '*';
```

> **Para saber más**
>
> Validar la jugada evita los avisos y las casillas imposibles, pero **no** impide hacer trampa: las letras
> escondidas y los puntos están en la página, y quien los ve los puede cambiar. Es la limitación de diseño que señala
> el README: con campos `hidden`, el estado está en manos del cliente. Para protegerlo hace falta guardarlo en el
> servidor (sesiones, en un tema posterior) o, al menos, firmarlo (`hash_hmac()`) para detectar si alguien lo ha
> modificado. Además, todo lo que viene del cliente y se escribe en la página debería pasar por `htmlspecialchars()`,
> que estas soluciones no usan.

Otras mejoras que no cambian el comportamiento (refactorización) o lo mejoran un poco:

- **Un mensaje para cada error de la palabra.** `pañuelo` se convierte en `""` y sale "debe tener 5 caracteres como
  mínmo" (con la errata): el usuario no sabe que el problema es la ñ. El RF-02 pide decir *cuál* es el error.
- **El fin del juego se puede calcular**: `$finJuego` es `strlen($palabra) === 0 || $fallosSeguidos > 3`. Un campo
  oculto menos que se pueda manipular. Y el `if` de los fallos se queda en `$fallosSeguidos++;` seguido de
  `if ($fallosSeguidos > 3) $finJuego = 1;`.
- **El rombo se calcula dos veces** (en `generarInformacionTablero()` y en `dibujarTablero()`; en la 03, tres con la
  fórmula). `dibujarTablero()` no lo necesita: la propia matriz dice si la casilla es `o`, `*` o una letra.
- **Menos campos ocultos.** Las casillas `o` no se usan nunca en la jugada y cada fila tiene al menos un asterisco
  (así que `count($tablero)` sigue dando el lado): se podrían dejar de enviar. Con `method="get"`, cada campo alarga
  la URL, que crece con lado²; con lados grandes la URL puede pasar del límite del servidor. `method="post"` lo evita
  (y saca el estado de la barra de direcciones, aunque siga en el código fuente).
- **HTML válido.** El botón *Borrar* es un `<button type="reset">` con un enlace dentro (un elemento interactivo
  dentro de otro, que el HTML no permite), y los campos ocultos están dentro de `<tr>`, entre celdas. Los navegadores
  lo toleran, pero un enlace normal a la página y los campos ocultos fuera de la tabla son más correctos.
- **Comentarios que no mienten.** En el fin del juego, el comentario dice "no ponemos el campo oculto
  name="primeraVez"", pero ese campo se escribe siempre al principio del script.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Comprobar un límite y anunciar otro en el mensaje | Lados 1 y 3 aceptados (FIXME) | Constantes o una sola función de validación |
| Recortar la palabra después de barajar | La caja y lo escondido no coinciden (solución 02) | Recortar primero, como la 03 |
| "Al azar" con decisiones secuenciales a cara o cruz | Letras casi siempre arriba y en orden (solución 01) | Barajar una lista (`shuffle`) |
| Olvidar que el tablero no sobrevive al clic | El tablero desaparece al pulsar (FIXME del paso 4) | Escribir el estado en campos `hidden` |
| Comparar la letra oculta con `!== 0` (estricto) | Todas las casillas parecen esconder letra: llega `"0"`, no `0` | Lo que llega de un formulario es siempre string |
| `include "entrada.php"` antes de generar el tablero | La caja muestra la palabra sin recortar | Incluirlo después de la función que modifica `$palabra` |
| Terminar en el 3.º fallo | No cumple "más de 3 fallos seguidos" | Escribir la interpretación en el análisis |
| Usar la casilla pulsada sin validarla | Avisos de PHP y trampas con la URL (FIXME) | `preg_match` y comprobar que existe y es `*` |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Cómo se guarda una matriz en campos ocultos? **Con un campo por casilla cuyo nombre lleva los índices entre
>   corchetes (`tablero[2][3]`); PHP reconstruye el array en `$_GET['tablero']`.**
> - ¿Por qué el servidor sabe qué asterisco se ha pulsado? **Porque todos los botones se llaman `botonJugada` y solo
>   se envía el pulsado, con su posición en el `value` (`"2x3"`).**
> - ¿Dónde viaja la palabra que queda por descubrir? **En la caja de texto visible `palabra`: el requisito pide que
>   las letras acertadas desaparezcan de ella.**
> - ¿Por qué las soluciones 01 y 02 solo se diferencian en una función y la 03 en varios pasos? **Porque 01 y 02
>   cambian el algoritmo con la misma estructura de datos; la 03 cambia la estructura, y eso afecta a todo el código
>   que la usa.**
> - ¿Por qué la solución 01 no reparte las letras de verdad al azar? **Porque coloca las letras en orden mientras
>   recorre el tablero de arriba abajo y cada asterisco se queda con la siguiente con un 50 % de probabilidad: los
>   primeros asteriscos tienen muchas más posibilidades.**
> - ¿Qué hay que cambiar en la solución 02 para que la palabra recortada coincida con las letras escondidas?
>   **Recortar la palabra antes de `str_split()` y `shuffle()`.**
> - ¿Por qué `dibujarTablero()` recibe `$deshabilitar = false` con valor por defecto? **Para añadir el fin del juego
>   sin cambiar las llamadas que ya existían.**
> - ¿Impide la validación de la jugada que se haga trampa? **No: las letras y los puntos siguen en la página; para
>   protegerlos hay que guardar el estado en el servidor o firmarlo.**
