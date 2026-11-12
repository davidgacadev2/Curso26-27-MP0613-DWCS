# TAREA-02 AVANZADA: El ascensor del instituto — SOLUCIÓN-01

Enfoque: **programada a partir del autómata** de `../DIAGRAMA ESTADO-TRANSICION/Automata-Ascensor.pdf`
(7 estados, 5 eventos entrantes, 10 transiciones). Los estados de la página son los del ascensor: parado, subiendo,
bajando, puertas abiertas, emergencia y fuera de servicio. `index.php` tiene las constantes, la tabla
`$automata[estado][evento]`, `evento()` (qué botón ha llegado), `edificio()` (todas las variantes de la página) y el
`switch` con un `case` por transición. `funciones.php` tiene las acciones y los predicados: `leerAscensor()` (AC1),
`leerPlanta()` (AC2), `anadirLlamada()` (AC3), `masCercanaArriba()` (PL4), `hayLlamadas()` (PL6, PL7), `mover()`
(AC5), `parar()` (AC6) y `coherente()` (PL0: los invariantes de cada estado, con un `match`).

PL0 se comprueba **una vez, antes del `switch`**, porque está en todas las transiciones salvo T1: si falla, la
transición pasa a ser T1 con un aviso.

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000.

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición a partir del autómata, solo con TODOs. |
| paso2 | El esqueleto del autómata: constantes, tabla, `evento()`, `edificio()`, `guardar()`, AC1 antes del `switch` y T1. |
| paso3 | T2: llamadas con el ascensor parado (`leerPlanta()`, `anadirLlamada()`): añadir o abrir las puertas. |
| paso4 | El movimiento: T5 (arrancar hacia la más cercana), T6/T7 (subir y bajar, parar), T8 (cerrar puertas y seguir) y T3/T4 (llamadas en marcha y con las puertas abiertas). |
| paso5 | T9 (emergencia) y T10 (rearmar; fuera de servicio a la tercera). |
| **paso6 (último)** | **Solución completa.** Añade PL0: `coherente()`, los invariantes de cada estado. |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados*.

**Terminal** (desde esta carpeta):

    git diff --no-index "PASO A PASO/paso3" "PASO A PASO/paso4"
    git diff --no-index "PASO A PASO/paso5/funciones.php" "PASO A PASO/paso6/funciones.php"

## Propuesta de uso en clase

1. Entregar el enunciado y pedir el **autómata** y la **tabla de invariantes** antes de programar.
2. Entregar el **paso2** (el esqueleto) y pedir las transiciones.
3. Al corregir, revisar el diff **paso3 → paso4** (T8, cuatro caminos con PL3, PL6, PL7 y PL4) y el
   **paso5 → paso6** (los invariantes). En el paso 5, cambiar con F12 el estado a 2 con el ascensor en la 4.ª:
   ¿qué pasa? ¿Y en el paso 6?
