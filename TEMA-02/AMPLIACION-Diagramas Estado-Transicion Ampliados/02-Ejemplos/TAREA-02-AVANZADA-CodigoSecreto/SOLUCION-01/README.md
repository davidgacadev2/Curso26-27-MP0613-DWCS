# TAREA-02 AVANZADA: Código secreto — SOLUCIÓN-01

Enfoque: **programada a partir del autómata** de [`../DIAGRAMA ESTADO-TRANSICION/Automata-CodigoSecreto.html`](../DIAGRAMA%20ESTADO-TRANSICION/Automata-CodigoSecreto.html)
(5 estados, 7 eventos entrantes, 7 transiciones). `index.php` tiene las constantes `ST0`…`ST4` y `EE0`…`EE6`, la
tabla `$automata[estado][evento]`, un `switch` con un `case` por transición (los caminos, con los predicados en
`$pl0`…`$pl5`) y los eventos salientes como funciones (`es0()`, `es2()`, `esFinal()`). `funciones.php` tiene las
acciones y los predicados largos: `validarConfiguracion()` (PL0), `codigoSecreto()` (AC4), `leerPartida()` (AC1),
`leerIntento()` (AC5, PL2), `puntuar()`, `elegirPista()` (AC8) y `partidaValida()` (PL1).

El **código secreto no viaja en la página**: se guarda la semilla y `codigoSecreto()` lo recalcula en cada petición
con `mt_srand(crc32(hash_hmac('sha256', semilla, CLAVE)))`. En el estado de configuración solo se guarda el
marcador en campos ocultos, para que no choquen con los campos del formulario.

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000.

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición a partir del autómata: constantes, tabla, cada transición y cada función, solo con TODOs. |
| paso2 | El esqueleto del autómata: constantes, tabla de eventos-estados, estado y evento recibidos, `switch` con T1 (y `default`), ES0 y AC10 (campos ocultos). Cualquier botón vuelve a la configuración. |
| paso3 | T2 *Empezar*: `validarConfiguracion()` (PL0), `codigoSecreto()` con `hash_hmac()`, `leerPartida()` y ES2. |
| paso4 | T3 *Probar*: `leerIntento()` (PL2), `puntuar()`, historial, intentos restantes, PL3 y PL4, ES3 y ES4. |
| paso5 | T4 *Pista* (PL5, AC8) y T5 *Rendirse*. |
| **paso6 (último)** | **Solución completa.** Añade T6 *Nueva partida*, T7 *Cambiar la configuración* y PL1 `partidaValida()` en T3, T4 y T5. |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados*.

**Terminal** (desde esta carpeta):

    git diff --no-index "PASO A PASO/paso2" "PASO A PASO/paso3"
    git diff --no-index --stat "PASO A PASO/paso3" "PASO A PASO/paso4"

## Propuesta de uso en clase

1. Entregar el enunciado y pedir primero el **autómata** (diagrama y tablas). Comparar con el de
   [`../DIAGRAMA ESTADO-TRANSICION`](../DIAGRAMA%20ESTADO-TRANSICION/Automata-CodigoSecreto.html) antes de programar.
2. Entregar el **paso2** (el esqueleto del autómata) y pedir que se programen las transiciones una a una.
3. Al corregir, revisar el diff **paso3 → paso4** (cómo cada fila de la tabla de transiciones es un `if`) y el
   **paso5 → paso6** (PL1: cambiar `intentos` con F12 a `0514` con la semilla 1).
