# EJEMPLO-TAREA-02B: Juego de tablero con letras ocultas — soluciones paso a paso

Descomposición paso a paso de las tres soluciones de referencia de la tarea 02B
(`Material referencia/tema-02/CODIGO SOLUCIONES/Ejemplos-Tareas/EJEMPLO-TAREA-02B`), siguiendo el proceso de
[`proceso-desarrollo-software/Apuntes`](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html):
maqueta, lista de TODOs y desarrollo incremental. Los requisitos (RF-01…, RNF-01…) que se citan entre corchetes en
los comentarios son los de `Material referencia/tema-02/TAREAS/Requisitos-TAREA-UD02B.md`.

Hay una carpeta por solución (`PARTE-01-SOLUCION-01`, `-02` y `-03`) y, dentro, `PASO A PASO` con siete pasos. Cada
paso contiene la aplicación completa tal como queda al terminarlo, con los mismos nombres de fichero, y **todos se
pueden ejecutar**.

| Paso | Qué se hace | Requisitos |
|---|---|---|
| `paso1-maqueta` | Solo HTML y CSS, con ejemplos de todos los estados: errores, marcador, tablero de lado 5 con una letra descubierta, fin del juego. | RNF-12 |
| `paso2-todos` | La maqueta repartida en ficheros (`entrada.php`) y funciones (solo la firma), con un `TODO` en cada parte que hay que programar. Muestra el formulario. | RNF-05 |
| `paso3-validacion` | Recepción y validación del lado y la palabra, un mensaje por cada error y formulario que conserva lo escrito. | RF-01, RF-02 |
| `paso4-tablero` | `generarInformacionTablero()` rellena el rombo y `dibujarTablero()` lo dibuja con un botón por asterisco. | RF-03, RF-05, RNF-06 |
| `paso5-letras-ocultas` | Esconder las letras al azar. **Es el paso en el que se diferencian las tres soluciones.** | RF-04 |
| `paso6-jugadas` | El tablero viaja en campos `hidden`; acierto, fallo y marcador (`marcador.php`). | RF-06, RF-07, RF-08, RF-10 |
| **`paso7-solucion-completa` (último)** | **Solución completa.** Fin del juego (todas las letras o 4.º fallo seguido) y tablero deshabilitado. | RF-09 |

## En qué se diferencian las tres soluciones

La maqueta es la misma y el script principal apenas cambia. La decisión de diseño está escrita en los TODO de
`generarInformacionTablero()` del paso 2 y programada en el paso 5:

| Solución | Estructura de datos | Cómo esconde las letras |
|---|---|---|
| 01 | Dos matrices: `$tablero` (lo que se ve) y `$letras` (lo oculto) | Recorre los asteriscos una y otra vez y decide a cara o cruz (`random_int`) si coloca la siguiente letra |
| 02 | Dos matrices, como la 01 | `str_split` + `array_pad` + `shuffle`, y reparte la lista barajada con `array_shift` |
| 03 | Una sola matriz: cada casilla es `[0]` lo visible y `[1]` lo oculto | Calcula el número de asteriscos con una fórmula, baraja como la 02 y coloca las letras al rellenar el rombo |

Para compararlas, desde esta carpeta:

    git diff --no-index "PARTE-01-SOLUCION-01/PASO A PASO/paso5-letras-ocultas" "PARTE-01-SOLUCION-02/PASO A PASO/paso5-letras-ocultas"

## TODO y FIXME

- `// TODO (paso N): …` es una parte **por hacer** e indica en qué paso se resuelve. Al programarla, el TODO se borra.
- `// FIXME (paso N): …` señala algo que **ya funciona, pero mal o a medias**, y el paso en que se arregla:

  | Aparece en | FIXME | Se arregla en |
  |---|---|---|
  | paso4 | Todavía no hay ninguna letra escondida. | paso5 |
  | paso4, paso5 | Al pulsar un asterisco el tablero desaparece: su estado no viaja en la petición (HTTP no tiene estado). | paso6 |
  | paso6 | `$finJuego` siempre vale 0: la partida no termina nunca. | paso7 |

- `// FIXME (pendiente): …` marca defectos de las **soluciones de referencia** que se dejan sin corregir (el último
  paso es esa solución, sin cambios en el código). Sirven como ejercicio de revisión:

  | Solución | FIXME (pendiente) | Cómo comprobarlo |
  |---|---|---|
  | Las tres | La validación comprueba `$lado < 1`, pero el mensaje dice "mayor o igual a 5": los lados 1 y 3 se aceptan (RF-02). | Lado `3` y palabra `caracola`: se dibuja el tablero |
  | Las tres | No se valida lo que llega del cliente en la jugada (`botonJugada`, tablero, letras, puntos): hay errores con la URL manipulada y se puede hacer trampa (RNF-08). | Añadir `&botonJugada=9x9` a la URL |
  | 02 | Si la palabra tiene más letras que asteriscos, se esconden letras elegidas al azar pero se conservan las primeras de la palabra: pueden no coincidir. | Lado `5` (13 asteriscos) y `abcdefghijklmnopqrst` |

Con la extensión **Todo Tree** de Visual Studio Code se ven todos en el panel lateral. Otra limitación de diseño,
que no es un error: las letras ocultas viajan en campos `hidden`, así que se pueden leer con *Ver código fuente*.

## Cómo probar y comparar

`php -S localhost:8000` en la carpeta de un paso y abrir http://localhost:8000 (o copiarla a `htdocs`).

Para ver qué cambia de un paso a otro: en Visual Studio Code, seleccionar los dos ficheros, botón derecho y
*Comparar seleccionados*; o en una terminal abierta en la carpeta de una solución:

    git diff --no-index "PASO A PASO/paso5-letras-ocultas" "PASO A PASO/paso6-jugadas"

## Casos de prueba

| Caso | Entrada | Resultado esperado | Desde |
|---|---|---|---|
| 1 | Lado `6`, palabra `sol` | Dos mensajes: lado impar y palabra de 5 caracteres como mínimo; sin tablero | paso3 |
| 2 | Lado `31`, palabra `caracola` | Mensaje de rango del lado; sin tablero | paso3 |
| 3 | Lado `7`, palabra `caracola` | Tablero 7×7 con 25 asteriscos: 1 en la fila 1, 7 en la fila 4, 1 en la fila 7 | paso4 |
| 4 | Pulsar un asterisco con letra | +5 puntos, se ve la letra, desaparece de la caja de la palabra, fallos seguidos a 0 | paso6 |
| 5 | Pulsar un asterisco sin letra | −2 puntos y un fallo seguido más | paso6 |
| 6 | Acertar las 8 letras de `caracola` | 40 puntos (menos los fallos), FIN DEL JUEGO y botones deshabilitados | paso7 |
| 7 | Cuatro fallos seguidos | −8 puntos, FIN DEL JUEGO y botones deshabilitados | paso7 |

Para los casos 4 a 7, las letras ocultas se pueden consultar en los campos `hidden` con las herramientas del
navegador (F12).

## Propuesta de uso en clase

1. Entregar `paso2-todos` de una de las soluciones con el enunciado y pedir que se completen los TODO en orden.
2. A quien se atasque, darle el paso siguiente como pista.
3. Revisar en clase el diff **paso5 → paso6** (cómo viaja una matriz en campos `hidden` y cómo se recupera) y
   comparar el paso 5 de las tres soluciones.
4. Proponer como ampliación corregir los `FIXME (pendiente)`.
