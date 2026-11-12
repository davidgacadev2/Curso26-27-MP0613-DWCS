# TAREA-02 (nivel MEDIO): El ahorcado

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

El departamento de Lengua del instituto quiere un juego para repasar vocabulario en los ordenadores del aula y
se lo pide a **BK Programación**: el ahorcado de toda la vida, con palabras por temas. **Juan** le pasa el
encargo a **Carlos**, con una condición de la profesora: las palabras tienen que escribirse **bien**, con sus
tildes, su Ñ y su diéresis (CIGÜEÑA, JABALÍ, A CORUÑA), y el alumno no tiene que preocuparse de ellas al jugar.

**María** le hace dos advertencias. La primera: como todavía no han visto sesiones, la partida viajará en
campos ocultos, y los alumnos más espabilados abrirán el código de la página, así que **la palabra secreta no
puede estar en el HTML**. La segunda: `strlen('Ñ')` vale 2.

## Enunciado

Programa en PHP una aplicación web en **una única página** (`index.php`) que se envía a sí misma por **POST** y
que permita jugar al ahorcado. Las palabras estarán en `palabras.php`, que **devuelve un array** con `return`, y
la lógica en funciones (en `funciones.php` si quieres). El estado de la partida se guardará en **campos ocultos**,
**sin usar `$_SESSION`**, cookies, bases de datos ni ficheros.

### Parte 1: empezar la partida

- **R1. Las palabras.** `palabras.php` devuelve un array con, al menos, tres **categorías** (Animales, Frutas,
  Galicia) de 10 palabras cada una, en mayúsculas y con su ortografía: tildes, Ñ, Ü y, si hace falta, espacios
  (A CORUÑA).
- **R2. Configuración.** Un formulario pide la **categoría** (una lista desplegable generada a partir del array)
  y la **dificultad**: fácil (8 fallos permitidos), normal (6) o difícil (4). Se valida en el servidor que las dos
  son de la lista y se muestran todos los errores. Al pulsar *Empezar* se elige una palabra **al azar** de esa
  categoría.
- **R3. La palabra no se envía al navegador.** En los campos ocultos se guardan la categoría y la **posición** de
  la palabra en su lista, pero nunca la palabra.

### Parte 2: el juego

- **R4. Palabra con huecos.** La palabra se muestra con un hueco `_` por letra; los espacios se ven como un
  espacio, no como un hueco. Encima se indican la categoría y el número de letras (sin contar los espacios).
- **R5. Teclado.** Hay un **botón por letra**, de la A a la Z con la Ñ (27). Al pulsar una:
  - si está en la palabra, se descubren **todas** sus apariciones, y con sus tildes: la U descubre la Ü de
    CIGÜEÑA y la A, la Á de JABALÍ. Mensaje: *¡Bien! La A aparece 2 veces.*;
  - si no está, cuenta como un fallo: *La K no está en la palabra.*;
  - las letras ya usadas se ven de otro color (verde si estaban, rojo si no) y sus botones se deshabilitan. Si a
    pesar de todo llega una letra repetida, el mensaje es *Ya habías probado la A.* y no cuenta como fallo.
- **R6. Fallos y dibujo.** Se muestran los fallos (*Fallos: 2 de 6*) y un dibujo del ahorcado en texto (en un
  `<pre>`) con 7 etapas, de la horca vacía al muñeco completo. La etapa es proporcional a los fallos: con 4
  fallos permitidos se avanza más deprisa que con 8. **Los fallos no se guardan**: se cuentan a partir de las
  letras usadas.
- **R7. Resolver.** Un campo de texto permite escribir la palabra entera y pulsar *Resolver*. Se comparan sin
  tener en cuenta mayúsculas, tildes ni espacios de los extremos (`cigueña` vale por CIGÜEÑA, pero la Ñ sigue
  siendo Ñ). Si acierta, gana; si no, cuenta como **2 fallos**. Pulsar *Intro* en ese campo debe resolver, no
  pulsar la letra A.
- **R8. Fin de la partida.**
  - **Ganada** cuando no quedan huecos: *¡Has acertado CIGÜEÑA con 1 fallo!*
  - **Perdida** al llegar a los fallos permitidos: *Te han ahorcado. La palabra era A CORUÑA.*, y se muestra la
    palabra con las letras que faltaban marcadas en rojo.

  Al terminar se deshabilita el teclado y desaparece *Resolver*. Un botón *Nueva partida* vuelve al formulario
  con la categoría y la dificultad anteriores.
- **R9. Marcador.** Se lleva la cuenta de **victorias y derrotas** de una partida a otra (también en campos
  ocultos) y se muestra en cuanto se ha terminado la primera.
- **R10. No fiarse del navegador.** Antes de jugar se comprueba que el estado recibido es una partida posible:
  categoría existente, posición dentro de la lista, dificultad válida, letras usadas del abecedario y sin
  repetir, y no más fallos de los posibles. Si no lo es, se muestra un error y se vuelve al formulario. Ninguna
  entrada (`letra[]=A`, `indice=99`, una categoría con `<script>`) puede producir avisos de PHP ni mostrarse sin
  escapar.

### Pistas

- `mb_str_split('AÑ')` da `['A', 'Ñ']`; `str_split` partiría la Ñ en dos bytes. Usa también `mb_strlen`,
  `mb_strtoupper`, `mb_strpos` y `mb_substr_count`.
- `strtr($texto, ['Á' => 'A', 'É' => 'E', ...])` cambia varias letras de una vez: así se comparan las letras sin
  tildes. No incluyas la Ñ: es otra letra.
- Para **probar** siempre con la misma palabra, el formulario puede mandar una semilla en un campo oculto y hacer
  `mt_srand($semilla)` antes de `mt_rand()`.
- Guarda las letras usadas en un único campo oculto (`"AEKÑ"`): los fallos y los huecos se calculan a partir de
  ellas.
- *Intro* en un formulario pulsa el **primer** botón: pon al principio un botón *Resolver* fuera de la vista.
- Para estudiar la solución, ejecútala con el depurador y mira cómo cambia `$partida['usadas']` en cada clic.

### Ampliaciones opcionales

- Una pista por partida: descubrir una letra al azar a cambio de un fallo.
- Un modo para dos jugadores en el que uno escribe la palabra secreta (¿cómo guardarla sin que se vea en el HTML?
  Piensa en `base64_encode`… y en por qué no basta).
- Puntuación según la longitud de la palabra y los fallos, con récord.
- Que la misma palabra no salga dos veces seguidas.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Palabras en un fichero que devuelve un array; formulario de configuración generado y validado (R1, R2) | 1,00 |
| 2. Palabra elegida al azar y no enviada al navegador (R3) | 0,75 |
| 3. Palabra con huecos y espacios (R4) | 1,00 |
| 4. Teclado: aciertos con tildes, Ñ y Ü, fallos, repetidas y botones deshabilitados (R5) | 2,00 |
| 5. Fallos calculados y dibujo proporcional (R6) | 1,00 |
| 6. Resolver la palabra entera, también con *Intro* (R7) | 1,00 |
| 7. Fin de la partida ganada y perdida y nueva partida (R8) | 1,00 |
| 8. Marcador de victorias y derrotas (R9) | 0,50 |
| 9. Validación del estado recibido y salida siempre escapada (R10) | 1,25 |
| 10. Código organizado en funciones, comentado y sin avisos de PHP | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

En [`SOLUCION-01`](SOLUCION-01/): `index.php` decide qué hacer según el botón pulsado y dibuja la página;
`funciones.php` tiene la lógica (validar, elegir la palabra, leer el estado, probar una letra, resolver, contar
fallos, saber cómo va la partida, dibujar el ahorcado y validar el estado), y `palabras.php` las palabras. Con el
depurador, pon un punto de ruptura en `probarLetra()` y observa `quitarTildes($palabra)` y
`$partida['usadas']`. Construcción paso a paso en [`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
