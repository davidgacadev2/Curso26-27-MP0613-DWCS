# TAREA-02 (nivel AVANZADO): Código secreto

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

La biblioteca del instituto organiza un concurso de lógica y le pide a **BK Programación** un juego de **código
secreto**: el ordenador piensa una combinación de colores y el jugador la tiene que adivinar en pocos intentos con
las pistas que recibe (cuántos colores están en su sitio y cuántos están pero en otro sitio).

**Juan** le encarga el juego a **Carlos** con una condición: antes de programar, quiere ver el **diagrama de
estado-transición ampliado** y las tablas del autómata, como en los ejercicios del tema. **María** le añade otra:
en un concurso habrá quien mire el código fuente de la página, así que **el código secreto no puede estar en el
HTML**.

## Enunciado

Diseña con el método de los **diagramas de estado-transición ampliados** (`Claude/tema-02/DIAGRAMAS
ESTADO-TRANSICION/00-Metodo`) y programa **a partir del autómata** una aplicación web en **una única página**
(`index.php`, con las acciones y los predicados en `funciones.php`) que se envía a sí misma por **POST**. El estado
viaja en **campos ocultos**, **sin usar `$_SESSION`**, bases de datos ni ficheros.

### Parte 1: el diseño

- **R1. Autómata.** Entrega, antes del código, el **diagrama de estado-transición ampliado** y las tres tablas del
  autómata: **descriptores** (EE, ES, ST, VE, PL, AC), **eventos-estados** y **transiciones** (con las acciones
  antes y después de los predicados), y la **comprobación de los predicados** con tablas de verdad de las
  transiciones con más de dos caminos.
- **R2. Código que sigue el autómata.** Constantes `ST0`, `ST1`... y `EE0`, `EE1`..., la tabla
  `$automata[estado][evento]`, un `case` por transición, los predicados en variables `$pl0`, `$pl1`... y una
  función por evento saliente. Un evento que no puede darse en un estado (`---`) se trata como la primera carga.

### Parte 2: el juego

- **R3. Configuración.** Posiciones del código (4 o 5), número de colores (6, 7 u 8: rojo, verde, azul, amarillo,
  naranja, morado, blanco, negro), intentos (de 6 a 12) y si los colores se pueden **repetir**. Se validan todos
  los datos y se muestran todos los errores. El formulario es pegajoso.
- **R4. Código secreto.** Al empezar, el servidor elige el código al azar. El código **no viaja en la página**: se
  guarda una **semilla** y el código se calcula en cada petición con `hash_hmac('sha256', $semilla, CLAVE)` (una
  clave que solo está en el servidor) como semilla de `mt_srand()`. Sin repetir colores, los colores son
  distintos.
- **R5. Intentos.** Un desplegable por posición (que recuerda el intento anterior) y un botón *Probar*. Un intento
  incompleto, con un color que no existe o con colores repetidos cuando no se permiten no cuenta y se explica por
  qué. Cada intento válido se añade al **historial** con sus **negras** (color y posición) y **blancas** (color en
  otra posición) y se indican los intentos que quedan.

  | Código | Intento | Negras | Blancas |
  |---|---|---:|---:|
  | rojo, morado, verde, naranja | rojo, verde, azul, amarillo | 1 | 1 |
  | rojo, rojo, verde, naranja | rojo, verde, rojo, rojo | 1 | 2 |

- **R6. Pista.** Una vez por partida, el botón *Pista* enseña el color de la primera posición que el jugador no ha
  acertado nunca. **Cuesta un intento** y solo se puede pedir si quedan al menos dos.
- **R7. Fin.** La partida se **gana** al acertar el código (*¡Has descubierto el código en 4 intentos!*) y se
  **pierde** al agotar los intentos o con el botón *Rendirse*. En los dos casos se enseña el código. Un
  **marcador** de ganadas y perdidas se conserva de una partida a otra. *Nueva partida* empieza otra con la misma
  configuración y *Cambiar la configuración* vuelve al formulario.
- **R8. No fiarse del navegador.** Antes de usar la partida que llega en los campos ocultos se comprueba que es
  **posible en ese estado**: configuración válida, semilla positiva, intentos bien formados, ninguno igual al
  código (la partida ya habría terminado) y que quedan intentos. Si no lo es, se vuelve a la configuración con un
  aviso. Ninguna entrada produce avisos de PHP ni se muestra sin escapar.

### Pistas

- Las negras se cuentan posición a posición. Para las blancas, cuenta las coincidencias de color sin mirar la
  posición (`array_count_values()` de los dos códigos y, para cada color, el mínimo) y réstale las negras.
- `shuffle()` usa el mismo generador que `mt_rand()`: después de `mt_srand($n)` baraja siempre igual.
- Guarda los intentos en un solo campo oculto: `"0123-4512"` (un grupo de cifras por intento).
- **Trampa de los formularios**: si el formulario de configuración tiene un campo `posiciones` y además escribes un
  campo oculto `posiciones`, el navegador manda los dos y PHP se queda con el último. En el estado de
  configuración, guarda en campos ocultos solo lo que no está en el formulario.
- Para probar con un código conocido, el formulario puede enviar la semilla: con la semilla 1, 4 posiciones, 6
  colores y sin repetir, el código es rojo, morado, verde, naranja.

### Ampliaciones opcionales

- *Deshacer* el último intento (si no se ha pedido la pista).
- Un modo para dos jugadores: uno escribe el código y el otro lo adivina. ¿Cómo lo guardarías sin que se vea?
- Puntuación según los intentos y récord de la sesión de juego.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Diagrama de estado-transición ampliado y tablas del autómata, con la comprobación de predicados (R1) | 2,00 |
| 2. Código programado a partir del autómata (R2) | 1,00 |
| 3. Configuración validada y pegajosa (R3) | 0,75 |
| 4. Código secreto calculado y nunca enviado al navegador (R4) | 1,00 |
| 5. Intentos con su validación, negras, blancas e historial (R5) | 2,00 |
| 6. Pista (R6) | 0,75 |
| 7. Fin de la partida, marcador, nueva partida y configuración (R7) | 1,00 |
| 8. Validación del estado recibido y salida escapada (R8) | 1,00 |
| 9. Código documentado y sin avisos de PHP | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

- **Autómata**: [`DIAGRAMA ESTADO-TRANSICION/Automata-CodigoSecreto.pdf`](DIAGRAMA%20ESTADO-TRANSICION/Automata-CodigoSecreto.pdf)
  (y `.html`). Contiene el análisis, el diagrama, las tablas, la comprobación de predicados, el paso del autómata
  al código y las pruebas.
- **Código**: [`SOLUCION-01`](SOLUCION-01/), con su construcción paso a paso en
  [`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
