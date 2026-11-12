# TAREA-02 AVANZADA: Cuatro en raya — SOLUCIÓN-01

Enfoque: `index.php` es la única página. Arriba decide qué hacer según el botón pulsado (*Empezar*, una columna,
*Deshacer* o *Nueva partida*); abajo dibuja el formulario o la partida. `funciones.php` tiene la lógica sin HTML.

La partida que viaja en campos ocultos es **mínima**: los nombres de las rojas y las amarillas, el color de la
máquina (`''` si juegan dos personas), el nivel, una semilla y las **jugadas** (`"3324"`). En cada petición,
`reconstruir()` repite las jugadas sobre un tablero vacío y devuelve el tablero, la última ficha, el ganador y la
línea ganadora, o `null` si las jugadas son imposibles (un carácter que no es de 0 a 6, una columna con más de 6
fichas o una ficha después de un cuatro en raya). Por eso:

- validar las jugadas recibidas es casi gratis (`estadoValido()` solo añade los nombres, la máquina, el nivel y
  la semilla);
- el turno es la paridad de `strlen($jugadas)` y deshacer es quitar el último carácter;
- la máquina es reproducible: antes de cada decisión hace `mt_srand(semilla + número de jugadas)`.

La máquina (`jugadaMaquina()`) prueba las columnas sobre **copias** del tablero (los arrays se pasan por valor):
`ganaria()` suelta una ficha en la copia y mira si hace cuatro en raya; `regala()` mira si, después, el rival
ganaría justo encima.

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000 (o copiarla a `htdocs`).

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición en TODOs: el orden de `index.php` (botón pulsado → jugar → cómo va la partida) y la lista de funciones con su firma. |
| paso2 | Constantes, `e()`, `texto()`, `validarConfiguracion()`, `nuevaPartida()` y `turno()`: el formulario pegajoso, la partida nueva con sus campos ocultos y de quién es el turno. |
| paso3 | `leerEstado()`, `tableroVacio()`, `soltarFicha()`, `columnasLibres()` y `reconstruir()`: el tablero con los botones de las columnas, la gravedad, los turnos, las columnas llenas y las jugadas imposibles. |
| paso4 | `lineaGanadora()` en las cuatro direcciones y `estadoPartida()`: cuatro en raya, empate, casillas resaltadas, fin de la partida y *Nueva partida*. |
| paso5 | La máquina: `ganaria()`, `regala()`, `jugadaMaquina()` (fácil y normal) y `moverMaquina()`, que juega al empezar y después de cada jugada de la persona. |
| **paso6 (último)** | **Solución completa.** Añade `deshacer()`, `historial()` y `estadoValido()` (nombres, máquina, nivel y semilla, además de las jugadas). |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados* (para
carpetas enteras, la extensión *Compare Folders*).

**Terminal** (desde esta carpeta; las comillas son necesarias por los espacios):

    git diff --no-index "PASO A PASO/paso2" "PASO A PASO/paso3"
    git diff --no-index --stat "PASO A PASO/paso4" "PASO A PASO/paso5"

## Propuesta de uso en clase

1. Entregar el **paso1** con el enunciado y pedir que se completen los TODOs.
2. Si alguien se atasca, darle el paso siguiente como pista.
3. Al corregir, revisar en clase los diffs **paso2 → paso3** (por qué basta con guardar las jugadas y cómo
   `reconstruir()` valida al repetirlas), **paso3 → paso4** (las cuatro direcciones con un solo bucle) y
   **paso4 → paso5** (probar jugadas en copias del tablero).
4. Debate: ¿puede un alumno hacer trampas cambiando el campo oculto `jugadas`? (Sí, mientras sean posibles: ver la
   ampliación de comprobar las jugadas de la máquina con la semilla.)
5. Para reproducir siempre la misma partida contra la máquina, cambiar el campo oculto `semilla` del formulario:
   con la semilla 7, si Ana juega en las columnas 1, 2, 3 y 7, gana la máquina en la columna 4.
