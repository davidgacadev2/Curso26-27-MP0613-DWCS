# TAREA-02 (nivel AVANZADO): Cuatro en raya

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

La asociación juvenil del barrio prepara un torneo de juegos de mesa y le pide a **BK Programación** un cuatro en
raya para practicar en los ordenadores del local: dos personas en el mismo ordenador o una persona contra el
ordenador. **Juan** se lo encarga a **Carlos**, que ya hizo el buscaminas.

**María** revisa su primera idea, guardar el tablero entero (42 casillas) en campos ocultos, y le propone otra:
*"Guarda solo las jugadas. Con la lista de columnas puedes volver a poner las fichas una a una y saber cómo está el
tablero. Si alguien cambia la lista por una imposible, lo notarás al repetirla. Y deshacer será quitar la última."*

## Enunciado

Programa en PHP una aplicación web en **una única página** (`index.php`, con las funciones en `funciones.php`) que
se envía a sí misma por **POST** y que permita jugar al cuatro en raya en un tablero de **6 filas y 7 columnas**.
El estado de la partida se guardará en **campos ocultos**, **sin usar `$_SESSION`**, cookies, bases de datos ni
ficheros.

### Parte 1: configuración

- **R1. Formulario.** La página pide:
  - el **modo**: contra la máquina o dos personas (botones de opción);
  - el nombre del **jugador 1** y, si juegan dos personas, el del **jugador 2** (de 1 a 15 caracteres);
  - el **nivel** de la máquina: fácil o normal;
  - **quién empieza**: el jugador 1, o el jugador 2 o la máquina. Quien empieza lleva las **rojas**.

  Al cargar la página por primera vez propone jugar contra la máquina en nivel normal.
- **R2. Validación.** Se muestran **todos** los errores a la vez: modo, nombres vacíos o demasiado largos (contando
  bien las tildes y la Ñ), dos jugadores con el mismo nombre (sin distinguir mayúsculas), nivel (solo contra la
  máquina), un jugador que se llame como la máquina (*Máquina*) y quién empieza. El formulario es pegajoso.

### Parte 2: el juego

- **R3. Tablero.** Se dibuja el tablero con una **casilla por posición** (vacía, roja o amarilla) y un **botón
  encima de cada columna** (1 a 7). La ficha **cae** a la casilla libre más baja de la columna. La última ficha
  puesta se resalta. Debajo de los nombres se indica de quién es el turno: *Turno de Ana (rojas)*.
- **R4. Solo se guardan las jugadas.** El estado de la partida son los nombres, el color de la máquina (si la
  hay), el nivel, una semilla y las **jugadas**: la columna de cada ficha, en orden, en un solo texto
  (`"3324"`). En cada petición, el tablero, el turno y el ganador se **calculan** repitiendo las jugadas.
- **R5. Errores al jugar.** Una columna que no existe (`columna=7`, `columna[]=1`) o llena (*La columna 1 está
  llena.*) da un error y no cambia nada. Los botones de las columnas llenas están deshabilitados.
- **R6. Fin de la partida.**
  - **Cuatro en raya** en horizontal, vertical o cualquiera de las dos diagonales: *¡Cuatro en raya! Gana Ana con
    las rojas en 7 jugadas.* Las casillas de la línea ganadora se resaltan.
  - **Empate** cuando el tablero está lleno sin ganador.

  Al terminar se deshabilitan las columnas y cualquier jugada da *La partida ha terminado*. Un botón *Nueva
  partida* vuelve al formulario con los mismos jugadores y modo.
- **R7. La máquina.** Contra la máquina, la máquina contesta **en la misma petición** en la que juega la persona
  (*La máquina ha jugado en la columna 4.*), y si le toca empezar, juega al pulsar *Empezar*.
  - **Fácil**: una columna libre al azar.
  - **Normal**: 1) si puede hacer cuatro en raya, lo hace; 2) si no, tapa la columna en la que ganaría el rival;
    3) si no, evita las columnas que dejan ganar al rival justo encima; 4) de las que quedan, elige la más
    cercana al **centro** (al azar entre dos igual de cercanas).

  Con la semilla guardada en el estado, la misma partida da siempre las mismas jugadas de la máquina.
- **R8. Deshacer e historial.** Un botón *Deshacer* quita la última jugada; contra la máquina, quita también la
  suya, para que vuelva a tocarle a la persona (si solo hay la primera jugada de la máquina, no se puede
  deshacer). Debajo del tablero se ve el historial numerado: *1. Ana (rojas): columna 4*.
- **R9. No fiarse del navegador.** Antes de jugar se comprueba que el estado recibido es una partida posible:
  nombres válidos y distintos, máquina y nivel válidos y jugadas posibles (solo columnas de 0 a 6, ninguna
  columna con más de 6 fichas y ninguna jugada después de un cuatro en raya). Si no lo es, se muestra un error y
  se vuelve al formulario. Ninguna entrada puede producir avisos de PHP ni mostrarse sin escapar.

### Pistas

- `array_fill(0, 6, array_fill(0, 7, '.'))` crea el tablero vacío; la fila 0 es la de arriba.
- La ficha cae: recorre la columna desde la fila 5 hacia arriba hasta encontrar una casilla vacía. Pasa el
  tablero **por referencia** (`array &$tablero`) a la función que suelta la ficha.
- Para saber si una ficha hace cuatro en raya, cuenta desde ella hacia los dos lados en cada una de las cuatro
  direcciones (`[0, 1]`, `[1, 0]`, `[1, 1]`, `[1, -1]`): basta con mirar la última ficha puesta.
- Para que la máquina "pruebe" una columna, pásale una **copia** del tablero (un parámetro sin `&`): puedes soltar
  la ficha en la copia sin estropear el tablero de verdad.
- `preg_match('/^[0-6]*$/D', $jugadas)`: sin la `D`, el `$` acepta un salto de línea al final.
- El turno sale de la longitud de las jugadas: con un número par, les toca a las rojas.
- Para estudiar la solución, pon un punto de ruptura en `reconstruir()` y mira cómo se llena `$tablero`.

### Ampliaciones opcionales

- Un nivel difícil que mire dos jugadas por delante (minimax con poca profundidad).
- Comprobar también que las jugadas de la máquina que llegan en el estado son las que ella habría hecho (con la
  semilla se pueden recalcular).
- Tableros de otros tamaños y "cinco en raya".
- Un marcador de partidas ganadas por cada jugador.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Formulario de configuración pegajoso y validación con todos los errores (R1, R2) | 1,00 |
| 2. Tablero, gravedad, turnos y última ficha resaltada (R3) | 1,25 |
| 3. Estado mínimo: solo las jugadas, con el tablero reconstruido (R4) | 1,00 |
| 4. Columnas no válidas o llenas (R5) | 0,50 |
| 5. Cuatro en raya en las cuatro direcciones, empate, línea resaltada y nueva partida (R6) | 2,00 |
| 6. La máquina en sus dos niveles, reproducible con la semilla (R7) | 2,00 |
| 7. Deshacer, también contra la máquina, e historial (R8) | 0,75 |
| 8. Validación del estado recibido y salida siempre escapada (R9) | 1,00 |
| 9. Código organizado en funciones, comentado y sin avisos de PHP | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

En [`SOLUCION-01`](SOLUCION-01/): `index.php` decide qué hacer según el botón pulsado y dibuja la página;
`funciones.php` tiene la lógica: validar la configuración, `reconstruir()` (repite las jugadas y devuelve el
tablero, la última ficha y el ganador, o `null` si son imposibles), `soltarFicha()`, `lineaGanadora()`, la máquina
(`ganaria()`, `regala()`, `jugadaMaquina()`, `moverMaquina()`), `deshacer()`, `historial()` y `estadoValido()`.
Con el depurador, sigue `jugadaMaquina()` en una partida en la que tengas tres en raya y mira por qué tapa esa
columna. Construcción paso a paso en [`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
