# TAREA-02 (nivel AVANZADO): Siete y media

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

Para la semana cultural, el departamento de Matemáticas quiere un taller de probabilidad con juegos de cartas
tradicionales y le pide a **BK Programación** una **siete y media** contra la banca, con fichas de mentira.

**Carlos** ya hizo un tres en raya contra el servidor y reconoce el problema: cuando el jugador se planta, la
**banca** tiene que jugar sin que el navegador envíe nada más. **María** le recuerda el **evento interno** de los
diagramas ampliados (un estado transitorio que se atiende en la misma petición) y le advierte: las cartas que
quedan en el mazo **no pueden estar en la página**, o cualquiera sabría cuándo pedir carta.

## Enunciado

Diseña con el método de los **diagramas de estado-transición ampliados** (`Claude/tema-02/DIAGRAMAS
ESTADO-TRANSICION/00-Metodo`) y programa **a partir del autómata** una aplicación web en **una única página**
(`index.php`, con las acciones y los predicados en `funciones.php`) que se envía a sí misma por **POST**. El estado
viaja en **campos ocultos**, **sin usar `$_SESSION`**, bases de datos ni ficheros.

### Parte 1: el diseño

- **R1. Autómata.** Entrega, antes del código, el **diagrama de estado-transición ampliado** (con el turno de la
  banca como estado **transitorio** y su **evento interno**) y las tablas de **descriptores**, **eventos-estados** y
  **transiciones**, con la **comprobación de los predicados** de las transiciones con más de dos caminos.
- **R2. Código que sigue el autómata.** Constantes, la tabla `$automata[estado][evento]`, un `case` por transición
  y los predicados en variables. El evento interno se atiende en la misma petición: si una transición deja el
  autómata en el turno de la banca, la tabla dice qué transición lo atiende. El estado transitorio **nunca** llega al
  navegador; si llega, se trata como la primera carga.

### Parte 2: el juego

- **R3. Baraja y puntos.** Baraja española de 40 cartas (oros, copas, espadas y bastos; del as al 7, sota, caballo y
  rey). Del as al 7 valen lo que marcan; las figuras, **medio punto**. Los puntos se escriben "3 y media", "media",
  "7 y media".
- **R4. Las cartas no viajan.** El mazo de cada mano se baraja a partir de la semilla de la partida y del número de
  mano con `hash_hmac()` y una clave del servidor. El jugador tiene las primeras cartas del mazo y la banca, las
  siguientes: basta con guardar **cuántas** cartas tiene el jugador.
- **R5. Apuesta.** El jugador empieza con **100 fichas**. En cada mano apuesta entre 5 y 50 (y no más de lo que tiene)
  y recibe una carta. Una apuesta no válida se explica y se vuelve a pedir.
- **R6. Turno del jugador.** *Pedir carta*: si pasa de 7 y media, **pierde** la apuesta (*Te has pasado (8 y media):
  pierdes 20 fichas.*); si llega justo a **7 y media**, se planta solo. *Plantarse*: juega la banca.
- **R7. Turno de la banca.** La banca roba cartas **mientras tenga menos puntos que el jugador**; los **empates los
  gana la banca**. Si la banca se pasa, el jugador gana lo apostado; si hizo 7 y media con **dos cartas**, cobra el
  **doble**. Se muestran las cartas de los dos.
- **R8. Fin de la mano y de la partida.** Tras cada mano, *Siguiente mano* o *Retirarse* (*Te retiras con 60 fichas:
  pierdes 40.*). Si el jugador se queda sin fichas, la partida termina. Siempre se ven el saldo y las manos
  jugadas, ganadas y perdidas, y *Nueva partida* empieza otra.
- **R9. No fiarse del navegador.** Antes de cada transición (salvo la carga) se comprueba que los datos son posibles
  en el estado en el que dicen estar: en el turno del jugador, la apuesta cabe en el saldo, hay cartas, las manos
  cuadran y el jugador **no tiene 7 y media ni más** (la mano ya habría terminado). Si no, se empieza otra partida
  con un aviso. Ninguna entrada produce avisos de PHP ni se muestra sin escapar.

### Pistas

- El evento interno se programa con un bucle alrededor del `switch`:
  `$transicion = $estado === ST3 ? $automata[ST3][EE_INTERNO] : null;` al final de cada vuelta.
- Las sumas de medios puntos son exactas en coma flotante: `7.5 === 7.5` es `true`.
- Para probar, la primera petición puede mandar la semilla: con la semilla 7, la primera mano reparte caballo de
  oros, 3 de oros, 4 de copas, 2 de bastos y 6 de copas.

### Ampliaciones opcionales

- *Doblar*: con una carta, doblar la apuesta, recibir una más y plantarse.
- Otra regla para la banca (plantarse con 5 y media) y comparar los resultados.
- Estadísticas: cuántas veces se ha pasado el jugador y con qué puntos se planta.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Diagrama (con el estado transitorio y el evento interno), tablas y comprobación de predicados (R1) | 2,00 |
| 2. Código programado a partir del autómata, con el evento interno (R2) | 1,25 |
| 3. Baraja, puntos y mazo calculado sin enviar las cartas (R3, R4) | 1,25 |
| 4. Apuesta validada (R5) | 0,50 |
| 5. Pedir carta y plantarse, pasarse y 7 y media (R6) | 1,50 |
| 6. Turno de la banca, empates y pago doble (R7) | 1,25 |
| 7. Siguiente mano, retirarse y sin fichas (R8) | 0,75 |
| 8. Validación del estado recibido y salida escapada (R9) | 1,00 |
| 9. Código documentado y sin avisos de PHP | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

- **Autómata**: [`DIAGRAMA ESTADO-TRANSICION/Automata-SieteYMedia.pdf`](DIAGRAMA%20ESTADO-TRANSICION/Automata-SieteYMedia.pdf)
  (y `.html`). Contiene el análisis, el diagrama, las tablas, la comprobación de predicados, el paso del autómata
  al código y las pruebas.
- **Código**: [`SOLUCION-01`](SOLUCION-01/), con su construcción paso a paso en
  [`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
