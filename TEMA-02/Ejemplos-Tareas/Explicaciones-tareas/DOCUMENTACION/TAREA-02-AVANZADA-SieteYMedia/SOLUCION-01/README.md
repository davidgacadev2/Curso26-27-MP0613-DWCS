# TAREA-02 AVANZADA: Siete y media — SOLUCIÓN-01

Enfoque: **programada a partir del autómata** de `../DIAGRAMA ESTADO-TRANSICION/Automata-SieteYMedia.pdf`
(7 estados, 7 eventos entrantes, uno de ellos **interno**, y 7 transiciones). `index.php` tiene las constantes, la
tabla `$automata[estado][evento]` y un `switch` con un `case` por transición **dentro de un bucle `do … while`**:
si una transición deja el autómata en ST3 (turno de la banca), al final de la vuelta se busca en la tabla la
transición del evento interno (T5) y se ejecuta en la misma petición. ST3 nunca se envía al navegador; si llega
`estado=3`, se trata como ST0.

`funciones.php` tiene las acciones y los predicados: `mazo()` (la baraja de la mano, con `hash_hmac()`),
`cartasJugador()`, `puntos()`, `juegaBanca()` (AC5), `leerApuesta()` (PL1) y `partidaValida()` (PL0, los
invariantes de cada estado). Solo se guarda **cuántas** cartas tiene el jugador: las cartas se recalculan.

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000.

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición a partir del autómata, solo con TODOs. |
| paso2 | El esqueleto del autómata: constantes, tabla, estado y evento, AC1 antes de las transiciones, el bucle del evento interno, T1 (ES0) y AC9. |
| paso3 | T2 *Apostar*: `leerApuesta()`, `mazo()`, `puntos()`, la primera carta y ES2. |
| paso4 | T3 *Pedir carta* (pasarse o llegar a 7 y media), T4 *Plantarse* y T5, el evento interno: `juegaBanca()`, pago doble, ES3 y ES4. |
| paso5 | T6 *Siguiente mano*, T7 *Retirarse* y el final sin fichas (PL4, ES5 y ES6). |
| **paso6 (último)** | **Solución completa.** Añade PL0: `partidaValida()`, los invariantes de cada estado. |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados*.

**Terminal** (desde esta carpeta):

    git diff --no-index "PASO A PASO/paso3" "PASO A PASO/paso4"
    git diff --no-index --stat "PASO A PASO/paso4" "PASO A PASO/paso5"

## Propuesta de uso en clase

1. Entregar el enunciado y pedir el **autómata** antes de programar. Discutir por qué el turno de la banca es un
   estado aunque nunca llegue al navegador.
2. Entregar el **paso2** (el esqueleto, ya con el bucle del evento interno) y pedir las transiciones.
3. Al corregir, revisar el diff **paso3 → paso4** (dos caminos que llevan a ST3 y una sola transición que lo atiende)
   y depurar con un punto de ruptura en el `do … while` para ver la segunda vuelta.
4. Con la semilla 7 (primera petición sin estado), la primera mano gana con 7 y media en tres cartas.
