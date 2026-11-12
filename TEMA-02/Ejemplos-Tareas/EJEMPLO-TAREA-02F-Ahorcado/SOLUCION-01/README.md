# TAREA-02 MEDIA: El ahorcado — SOLUCIÓN-01

Enfoque: `index.php` es la única página. Arriba decide qué hacer según el botón pulsado (*Empezar*, una letra,
*Resolver* o *Nueva partida*); abajo dibuja el formulario o la partida. `palabras.php` devuelve el array de
palabras por categorías y `funciones.php` tiene la lógica sin HTML: `validarConfiguracion()`, `nuevaPartida()`,
`palabraSecreta()`, `leerEstado()`, `quitarTildes()`, `letrasUsadas()`, `palabraOculta()`, `probarLetra()`,
`contarFallos()`, `estadoPartida()`, `dibujo()`, `resolver()`, `leerMarcador()` y `estadoValido()`.

La partida es un array **mínimo** que viaja en campos ocultos: la categoría, el **índice** de la palabra (la palabra
no se escribe nunca en el HTML mientras se juega), los fallos permitidos, las letras usadas en un solo texto
(`"UÑZ"`) y cuántas palabras se han fallado al resolver. Los huecos, los fallos y si la partida ha terminado se
**calculan** cada vez a partir de eso. Las letras se comparan sin tildes (`quitarTildes()` con `strtr`), y
siempre con funciones `mb_` por la Ñ.

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000 (o copiarla a `htdocs`).

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición en TODOs: el orden de `index.php` (botón pulsado → jugar → cómo va la partida) y la lista de funciones con su firma. |
| paso2 | Las palabras con `require`, `e()`, `validarConfiguracion()`, `nuevaPartida()` con la semilla y la partida guardada en campos ocultos (categoría e índice, nunca la palabra). |
| paso3 | `leerEstado()`, `quitarTildes()`, `letrasUsadas()`, `palabraOculta()` y `probarLetra()`: la palabra con huecos y el teclado de 27 botones. |
| paso4 | `contarFallos()`, `estadoPartida()` y `dibujo()`: los fallos calculados, el ahorcado en un `<pre>` y el fin de la partida ganada o perdida. |
| paso5 | `resolver()` (2 fallos si se equivoca, botón invisible para *Intro*), `leerMarcador()` con victorias y derrotas y *Nueva partida*. |
| **paso6 (último)** | **Solución completa.** Añade `estadoValido()`: no fiarse de los campos ocultos (categoría, índice, dificultad, letras del abecedario sin repetir, fallos posibles). |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados* (para
carpetas enteras, la extensión *Compare Folders*).

**Terminal** (desde esta carpeta; las comillas son necesarias por los espacios):

    git diff --no-index "PASO A PASO/paso1" "PASO A PASO/paso2"
    git diff --no-index --stat "PASO A PASO/paso3" "PASO A PASO/paso4"

## Propuesta de uso en clase

1. Entregar el **paso1** con el enunciado y pedir que se completen los TODOs.
2. Si alguien se atasca, darle el paso siguiente como pista.
3. Al corregir, revisar en clase los diffs **paso2 → paso3** (por qué `mb_str_split` y no `str_split`, y cómo
   `strtr` hace que la U descubra la Ü) y **paso5 → paso6** (cambiar con F12 `indice` o `usadas` antes y después).
4. Antes del paso 2, preguntar en clase qué pasaría si la palabra fuera en un campo oculto (Ctrl+U).
5. Para repetir siempre la misma palabra al depurar, cambiar el campo oculto `semilla` del formulario: en
   Animales, la semilla 15 da CIGÜEÑA y la 1, JABALÍ; en Galicia, la 4 da A CORUÑA.
