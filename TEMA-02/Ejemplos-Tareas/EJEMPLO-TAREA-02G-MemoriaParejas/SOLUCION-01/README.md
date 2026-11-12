# TAREA-02 MEDIA: Memoria de parejas — SOLUCIÓN-01

Enfoque: `index.php` es la única página. Arriba decide qué hacer según el botón pulsado (*Empezar*, una carta o
*Nueva partida*); abajo dibuja el formulario o el tablero. `funciones.php` tiene la lógica sin HTML:
`validarEntrada()`, `crearTablero()`, `nuevaPartida()`, `leerEstado()`, `jugar()`, `cartaVisible()`,
`estadoPartida()` y `estadoValido()`. La partida es un array (`$partida`) que viaja en campos ocultos: la
configuración, el tablero con un campo por carta (`cartas[f][c]`), las parejas encontradas como lista
(`"0-0,1-2"`), la carta pendiente del turno y el marcador. Las cartas de un fallo no se guardan: se ven solo en
la respuesta a ese clic (`$temporales`).

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000 (o copiarla a `htdocs`).

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición en TODOs: el orden de `index.php` (botón pulsado → jugar → fin) y la lista de funciones con su firma. |
| paso2 | Constantes, `e()`, `validarEntrada()` con todos los mensajes y el formulario pegajoso con la semilla oculta. |
| paso3 | `crearTablero()` (`str_split`, `mt_srand` + `shuffle`, `array_chunk`), `nuevaPartida()`, `leerEstado()`, el tablero de botones y la partida guardada en campos ocultos (`cartas[f][c]`). |
| paso4 | `jugar()`: primera y segunda carta, pareja (+10) o fallo (−2), cartas que solo se ven en esta respuesta, marcador y el resto del estado en campos ocultos. |
| paso5 | `estadoPartida()`: ganada o perdida, mensajes finales, botones deshabilitados, todas las cartas a la vista al perder y *Nueva partida*. |
| **paso6 (último)** | **Solución completa.** Añade `estadoValido()`: no fiarse de los campos ocultos (dimensiones, cada letra dos veces, parejas completas, pendiente válida). |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados* (para
carpetas enteras, la extensión *Compare Folders*).

**Terminal** (desde esta carpeta; las comillas son necesarias por los espacios):

    git diff --no-index "PASO A PASO/paso1" "PASO A PASO/paso2"
    git diff --no-index --stat "PASO A PASO/paso3" "PASO A PASO/paso4"

## Propuesta de uso en clase

1. Entregar el **paso1** con el enunciado y pedir que se completen los TODOs.
2. Si alguien se atasca, darle el paso siguiente como pista.
3. Al corregir, revisar en clase los diffs más interesantes: **paso3 → paso4** (cómo una sola función con el array
   por referencia lleva todo el turno) y **paso5 → paso6** (cambiar `cartas[0][0]` con F12 antes y después).
4. Para repetir siempre el mismo reparto al depurar, cambiar el valor del campo oculto `semilla` del formulario
   (con la semilla 7, un tablero 2 × 3 con `ABC` queda `A C B` / `B C A`).
