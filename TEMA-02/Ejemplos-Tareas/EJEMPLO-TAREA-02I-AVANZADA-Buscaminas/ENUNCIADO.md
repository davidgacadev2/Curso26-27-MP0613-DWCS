# TAREA-02 (nivel AVANZADO): Buscaminas

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

Tras el éxito del juego de tablero con letras ocultas, **Juan** reta a **Carlos**: *"¿Serías capaz de programar un
Buscaminas completo solo con PHP, sin una línea de JavaScript y sin sesiones?"*. Carlos acepta el reto: cada clic
será una petición al servidor y toda la partida tendrá que viajar en la propia página.

## Enunciado

Programa el clásico juego del **Buscaminas** como aplicación web en PHP. El tablero es una cuadrícula de casillas
ocultas; algunas esconden una mina. Al descubrir una casilla sin mina aparece el número de minas que hay en sus
8 casillas vecinas. El objetivo es descubrir todas las casillas que no tienen mina.

### Requisitos

- **R1. Configuración de la partida.** Un formulario permite elegir el nivel:

  | Nivel | Filas | Columnas | Minas |
  |---|---:|---:|---:|
  | Principiante | 9 | 9 | 10 |
  | Intermedio | 16 | 16 | 40 |
  | Experto | 16 | 30 | 99 |
  | Personalizado | 5–16 | 5–30 | 1 – máximo |

  Para el nivel personalizado, el máximo de minas es el 35 % de las casillas, y como mínimo deben quedar 9
  casillas libres. Se validará todo en el servidor, mostrando todos los errores y conservando los valores
  (formulario pegajoso). Los niveles se definirán en un **array constante** (`const NIVELES = [...]`).
- **R2. Tablero.** Se dibuja como una tabla HTML. Cada casilla oculta es un botón
  `<button type="submit" name="celda" value="fila-columna">`.
- **R3. Primer clic seguro.** Las minas **no** se colocan al crear la partida, sino al hacer el **primer clic**: se
  reparten al azar evitando la casilla pulsada y sus vecinas. Después se calcula, para cada casilla, el número de
  minas vecinas.
- **R4. Descubrir.** Al descubrir una casilla:
  - si tiene mina, se **pierde** la partida;
  - si tiene un número mayor que 0, se muestra el número (cada número con su color);
  - si tiene un 0, se muestra vacía y se descubren automáticamente **todas sus vecinas**, y si alguna de ellas es
    también un 0, las vecinas de esta, etc. Debes implementarlo con una **función recursiva**.
- **R5. Banderas.** Un selector (botones de radio) permite cambiar el modo del clic entre *Descubrir* y
  *Poner/quitar bandera*. Una casilla con bandera no se puede descubrir. El modo elegido se mantiene entre clics.
- **R6. Marcador.** Encima del tablero se muestra:
  - minas restantes (minas − banderas puestas; puede ser negativo);
  - número de jugadas;
  - tiempo de juego en formato `mm:ss`, contado desde el primer clic (usa `time()`).
- **R7. Fin de la partida.**
  - **Victoria**: cuando todas las casillas sin mina están descubiertas. Se marcan con bandera todas las minas.
  - **Derrota**: se muestran todas las minas, se resalta en rojo la que ha explotado y se marcan las banderas que
    estaban mal puestas.
  - En ambos casos se muestra un mensaje y el tablero queda bloqueado (sin botones).
- **R8. Estado en la página.** Toda la partida (dimensiones, tablero, estado de cada casilla, jugadas, hora de
  inicio...) se guarda en campos `hidden` del formulario del tablero. **Prohibido usar `$_SESSION`**.
  Para que no haya cientos de campos, guarda **cada fila como una cadena** (por ejemplo, tablero `"01*1000"` y
  estado `"DDOOBOO"`) y conviértela de nuevo en array con `str_split()`.
- **R9. Desconfía del cliente.** Todo lo que llega del navegador puede haber sido manipulado:
  - al recuperar la partida de los campos ocultos se comprobará que es coherente (dimensiones dentro de los
    límites, longitud y caracteres de cada fila, número de minas...); si no lo es, se mostrará un error;
  - se comprobará que el `value` del botón pulsado es una casilla que existe.
- **R10. Organización del código.** Como mínimo en cuatro ficheros:
  - `config.php`: constantes;
  - `logica.php`: reglas del juego (sin HTML);
  - `vista.php`: funciones que **devuelven** el HTML del formulario, del marcador y del tablero;
  - `index.php`: recibe la petición, llama a la lógica y muestra la vista.

### Restricciones

- Sin JavaScript, sin `$_SESSION`, sin cookies, sin ficheros ni bases de datos, sin clases (las veremos en el tema 3).
- Se usarán `require_once`, constantes, funciones con parámetros por valor y **por referencia**, arrays
  multidimensionales y `htmlspecialchars()` donde corresponda.

### Pistas

- Representa la partida con un único array asociativo
  (`['filas' => 9, 'columnas' => 9, 'minas' => 10, 'tablero' => [...], 'estado' => [...], ...]`) y pásalo **por
  referencia** a las funciones que lo modifican.
- `array_fill(0, $filas, array_fill(0, $columnas, 0))` crea una matriz llena de ceros.
- Para colocar las minas al azar sin repeticiones: numera las casillas de 0 a `filas*columnas-1`, quita las
  prohibidas con `array_diff()`, desordena con `shuffle()` y coge las primeras con `array_slice()`.
  La casilla número `n` está en la fila `intdiv(n, columnas)` y en la columna `n % columnas`.
- Escribe una función `vecinos($fila, $columna, $filas, $columnas)` que devuelva las coordenadas de las vecinas
  que existen: te servirá para calcular los números, para el relleno recursivo y para el primer clic seguro.
- Con `foreach ($vecinos as [$f, $c])` desestructuras cada par de coordenadas directamente.
- Prueba el relleno con un tablero grande y pocas minas (16 x 30 con 1 mina).

### Para reflexionar (respóndelo en un comentario al principio de `index.php`)

1. Abre *Ver código fuente* en mitad de una partida. ¿Puedes saber dónde están las minas? ¿Qué implicaciones tiene
   guardar el estado de una aplicación en el cliente?
2. ¿Qué pasa si después de perder pulsas el botón *Atrás* del navegador y vuelves a hacer clic?
3. ¿Por qué hay que validar el `value` del botón aunque lo hayamos generado nosotros?

### Ampliaciones opcionales (para nota, hasta +1 punto)

- **Acorde** (*chording*): al hacer clic sobre un número descubierto que tiene a su alrededor tantas banderas como
  indica, se descubren todas sus vecinas sin bandera (si alguna bandera estaba mal puesta, explota).
- **Estado protegido**: guarda toda la partida en **un único** campo oculto, cifrado y autenticado, para que el
  jugador no pueda ver las minas ni modificar la partida (`json_encode()` + `openssl_encrypt()` con
  `aes-256-gcm` + `base64_encode()`, y el camino inverso).

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Formulario de configuración con niveles en array constante, validación y formulario pegajoso. | 1,00 |
| 2. Tablero como tabla de botones con `value` `fila-columna`. | 0,50 |
| 3. Colocación aleatoria de minas tras el primer clic, evitando la casilla y sus vecinas. | 1,25 |
| 4. Cálculo correcto de los números (función `vecinos()` reutilizada). | 0,75 |
| 5. Descubrimiento con relleno **recursivo** de las zonas vacías. | 1,50 |
| 6. Modo bandera persistente y bloqueo de casillas con bandera. | 0,75 |
| 7. Marcador: minas restantes, jugadas y tiempo `mm:ss`. | 0,75 |
| 8. Detección de victoria y derrota, con el tablero final correcto y bloqueado. | 1,00 |
| 9. Estado en campos `hidden` fila a fila (`implode` / `str_split`). | 1,00 |
| 10. Validación del estado recibido y de la casilla pulsada. | 0,75 |
| 11. Organización en ficheros, funciones sin HTML en la lógica, paso por referencia. | 0,50 |
| 12. Respuestas a las preguntas de reflexión, comentarios y diseño. | 0,25 |
| **Total** | **10,00** |
| *Ampliaciones: acorde (+0,5) y estado cifrado (+0,5).* | *+1,00* |

## Soluciones propuestas

- [`SOLUCION-01`](SOLUCION-01/): cumple los requisitos R1–R10. El estado viaja fila a fila en campos ocultos,
  se revalida entero en cada petición y el relleno de casillas vacías es **recursivo**.
- [`SOLUCION-02`](SOLUCION-02/): incluye las dos ampliaciones. Compara los ficheros con los de la solución 01:
  - `estado.php`: la partida viaja en **un único campo oculto cifrado y autenticado** (AES-256-GCM). Ya no se
    ven las minas en el código fuente y cualquier modificación se detecta;
  - `logica.php`: relleno **iterativo** con una pila (`array_push` / `array_pop`) en lugar de recursivo, y jugada
    de **acorde**;
  - `vista.php`: los números descubiertos son botones (para el acorde).

  Fíjate en que ni siquiera el cifrado evita el problema de la pregunta 2 (volver *Atrás*): para eso habrá que
  guardar el estado en el servidor con **sesiones**, que veremos más adelante.

Estudia las soluciones con Xdebug: pon un punto de ruptura en `descubrir()` y observa cómo crece la pila de
llamadas (solución 01) o el array `$pendientes` (solución 02) al pulsar sobre una zona vacía.
