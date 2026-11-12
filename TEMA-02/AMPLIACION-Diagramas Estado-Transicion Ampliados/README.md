# Avanzado: diagramas y autómatas de estado-transición ampliados

Aplicación de la metodología de los diagramas de estado-transición ampliados, explicada en
[`01-Metodo/Metodo-Automatas.html`](01-Metodo/Metodo-Automatas.html) (diagrama de estado-transición ampliado +
autómata: tabla de descriptores, tabla de eventos-estados y tabla de transiciones), a problemas del tema 2 que **no**
estaban resueltos así en los ejemplos del repositorio (que ya incluyen el Boletín 01-E01, el Boletín 03-E01 y la Tarea
02A). Todo está probado con **PHP 8.2**.

Todas las rutas de este material son relativas a esta carpeta (la raíz de todo el contenido):

```
AMPLIACION-Diagramas Estado-Transicion Ampliados/
  README.md                          este fichero
  01-Metodo/                         Metodo-Automatas.html: el método, los elementos, cómo comprobar los
                                     predicados, la plantilla de código y los errores frecuentes
  02-Ejemplos/
    01-B02-E01-Impuestos/            Automata-B02-E01.html + solucion/index.php
    02-B03-E08-DiaSemana/            Automata-B03-E08.html + solucion/index.php
    03-B05-E04-ProductoMatrices/     Automata-B05-E04.html + solucion/index.php
    EJEMPLO-TAREA-02B-JuegoTablero/  Automata-TAREA-02B.html + solucion/index.php
    05-B01-E01-Operaciones/          Automata-B01-E01.html + solucion/index.php
                                     (el autómata de los ejemplos del repositorio, rehecho con este formato y completado)
    06-Cajero/                       Automata-Cajero.html + solucion/index.php
    07-Expendedora/                  Automata-Expendedora.html + solucion/index.php
    08-ReservaBarBaio/               Automata-ReservaBarBaio.html + solucion/index.php
    09-TresEnRaya/                   Automata-TresEnRaya.html + solucion/index.php
    10-ExamenTest/                   Automata-ExamenTest.html + solucion/index.php
    EJEMPLO-TAREA-02A-Agenda/        README.md, DIAGRAMA ESTADO-TRANSICION/Automata-Agenda.html
                                     y PASO A PASO/ (la solución de la Tarea 02A, del código al autómata)
    TAREA-02-AVANZADA-SieteYMedia/   ENUNCIADO.md, Requisitos.md, DIAGRAMA ESTADO-TRANSICION/ y SOLUCION-01/
    TAREA-02-AVANZADA-CodigoSecreto/ ENUNCIADO.md, Requisitos.md, DIAGRAMA ESTADO-TRANSICION/ y SOLUCION-01/
    TAREA-02-AVANZADA-Ascensor/      ENUNCIADO.md, Requisitos.md, DIAGRAMA ESTADO-TRANSICION/ y SOLUCION-01/
```

## Índice

- Método: [`01-Metodo/Metodo-Automatas.html`](01-Metodo/Metodo-Automatas.html)
- Ejemplos de los boletines y tareas del tema:
  [Boletín 02-E01](02-Ejemplos/01-B02-E01-Impuestos/Automata-B02-E01.html) ·
  [Boletín 03-E08](02-Ejemplos/02-B03-E08-DiaSemana/Automata-B03-E08.html) ·
  [Boletín 05-E04](02-Ejemplos/03-B05-E04-ProductoMatrices/Automata-B05-E04.html) ·
  [Tarea 02B](02-Ejemplos/EJEMPLO-TAREA-02B-JuegoTablero/Automata-TAREA-02B.html) ·
  [Boletín 01-E01](02-Ejemplos/05-B01-E01-Operaciones/Automata-B01-E01.html) ·
  [Tarea 02A (Agenda)](02-Ejemplos/EJEMPLO-TAREA-02A-Agenda/README.md)
- Ejemplos nuevos con más estados:
  [Cajero](02-Ejemplos/06-Cajero/Automata-Cajero.html) ·
  [Expendedora](02-Ejemplos/07-Expendedora/Automata-Expendedora.html) ·
  [Reservas del Bar Baio](02-Ejemplos/08-ReservaBarBaio/Automata-ReservaBarBaio.html) ·
  [Tres en raya](02-Ejemplos/09-TresEnRaya/Automata-TresEnRaya.html) ·
  [Examen tipo test](02-Ejemplos/10-ExamenTest/Automata-ExamenTest.html)
- Tareas de nivel avanzado:
  [Siete y media](02-Ejemplos/TAREA-02-AVANZADA-SieteYMedia/ENUNCIADO.md) ·
  [Código secreto](02-Ejemplos/TAREA-02-AVANZADA-CodigoSecreto/ENUNCIADO.md) ·
  [Ascensor](02-Ejemplos/TAREA-02-AVANZADA-Ascensor/ENUNCIADO.md)

## Los cuatro problemas

| Problema | Estados | Eventos entrantes | Qué practica |
|---|---|---|---|
| Boletín 02-E01: impuestos de las ventas | 2 | Calcular, Borrar | validar dos campos con acciones previas; el más parecido al Boletín 01-E01 de los ejemplos del repositorio |
| Boletín 03-E08: día de la semana | 2 | Carga, Enviar, Borrar (`type="reset"`: HTML/JS) | cuatro predicados, uno (el día existe en ese mes) que solo se evalúa si los otros se cumplen: tabla de verdad con "x" y cortocircuito |
| Boletín 05-E04: producto de matrices | 4 | Carga, Enviar | dos formularios seguidos con el mismo botón; dimensiones en campos ocultos que se vuelven a validar; evento imposible (`---`) |
| Tarea 02B: juego de tablero | 5 | Carga, Enviar, Jugada | toda la partida en campos ocultos; caminos que no pueden darse en un estado (invariantes); predicados evaluados antes de las acciones |
| Boletín 01-E01: operaciones con dos números (el de los ejemplos del repositorio) | 2 | Enviar, Reset | el autómata del material, con la misma numeración y lo que le faltaba: PL2 (segundo número distinto de 0), el cociente que pide el enunciado, un camino para el 0 y la validación de que el número cabe en un `int`. Es el más sencillo: el primero para empezar |

Cada documento tiene: enunciado, análisis (cómo se encuentran los elementos), diagrama, tabla de descriptores, tabla
de eventos-estados, tabla de transiciones (con las acciones **antes** y **después** de los predicados, como en la Tarea
02A), comprobación de predicados (tablas de verdad), el código que sale del autómata, las pruebas y ejercicios para
practicar.

## Ejemplos nuevos con más estados (06 a 10)

Problemas nuevos (no salen de los boletines) con más estados, eventos y transiciones que los anteriores, y cada uno
con una idea nueva:

| Ejemplo | Estados | Eventos | Transiciones | Qué añade |
|---|---|---|---|---|
| 06 Cajero automático | 8 | 9 | 11 | la cuenta viaja en campos ocultos **firmados** con `hash_hmac`: un saldo cambiado en el navegador retiene la tarjeta; tres fallos de PIN; cascadas de predicados con avisos distintos |
| 07 Máquina expendedora | 7 | 9 | 9 | el dato del evento va en el valor del botón (`moneda:200`); PL5 **busca** cómo dar el cambio con las monedas del cajón (con vuelta atrás); modo mantenimiento con clave |
| 08 Reservas del Bar Baio | 7 | 6 | 10 | asistente en tres pasos con *Atrás* y *Editar* desde el resumen: `$editando` (PL2) cambia el destino de *Siguiente*; fechas relativas a hoy, lunes cerrado, domingos sin cenas, plazas por franja |
| 09 Tres en raya | 7 | 7 (uno interno) | 7 | **evento interno**: el turno de la máquina se atiende en la misma petición; ST3 es un estado transitorio que nunca llega al navegador; la máquina juega de forma determinista (ganar, tapar, centro, esquinas, lados) |
| 10 Examen tipo test | 6 | 8 | 8 | preguntas saltadas, revisión de pendientes y dos modos de corrección; T3 tiene 7 caminos; predicados que se evalúan después de una acción |

En los diagramas grandes se usan **cajas discontinuas** que agrupan estados (como STx, pero solo para los que
nombran) y, en el de las reservas, etiquetas cortas con el detalle en la tabla de transiciones.

Las soluciones se probaron igual que las demás, con peticiones que reenvían el formulario completo: 31, 30, 34, 31 y
44 peticiones (las de las reservas se generan con fechas calculadas a partir del día en que se ejecutan).

## Notación

Igual que en los diagramas de los ejemplos del repositorio: evento entrante en **rojo**, predicado en negro, acciones en **verde** y
evento saliente en **morado**; **STx** es "cualquier estado". Se añaden, en gris, las acciones previas ("antes: AC0")
y, en azul, el número de transición (T1, T2...) para enlazar el diagrama con las tablas.

## Las soluciones en PHP

Cada `solucion/index.php` está programado **a partir del autómata**, con la estructura del `index.php` del Boletín
01-E01 de los ejemplos del repositorio: constantes `ST0`, `EE0`..., el array `$automata[estado][evento]`, un `switch` con un `case`
por transición, los predicados en variables `$pl0`, `$pl1`... y una función por evento saliente (`es0()`,
`es1()`...). Se abren con `php -S localhost:8000` desde su carpeta.

Se probaron con peticiones reales que recorren **todos los caminos** de las tablas de transiciones (9, 13, 12, 20 y
13 peticiones; las de la Tarea 02B reenviando el formulario completo con todos sus campos ocultos, como el navegador,
y repetidas 13 veces porque las letras se esconden al azar), además de estados y campos ocultos manipulados y de
textos con HTML. Ninguna produjo avisos de PHP.

## Observaciones sobre los ejemplos del repositorio

- **Boletín 02-E01**: en el ejemplo del enunciado están cambiadas las etiquetas: 2095,32 es el impuesto **estatal**
  (5 %) y 1676,26 el **autonómico** (4 %). Los impuestos se calculan sobre el líquido (`total / 1,09`), como en la
  solución de los ejemplos del repositorio.
- **Boletín 01-E01** (autómata de los ejemplos del repositorio, rehecho en [`02-Ejemplos/05-B01-E01-Operaciones`](02-Ejemplos/05-B01-E01-Operaciones/Automata-B01-E01.html)): falta el predicado "el segundo número
  no es 0"; con 0, `$numero1 % $numero2` lanza `DivisionByZeroError` en PHP 8 (su tabla de descriptores ya tiene el
  hueco PL2 sin rellenar). Además, el enunciado pide el **cociente**, que no se calcula; en el ejemplo del boletín el
  producto de 30 y 10 aparece como 800 (es 300); y `formatoEnteroValido()` acepta números que no caben en un `int`
  (`99999999999999999999` se convierte en `PHP_INT_MAX` sin avisar).
- **Boletín 03-E08**: la fórmula de Keith y Craver está escrita en C; en PHP las divisiones deben ser `intdiv` y el
  `y--` se aplica **después** de sumar el año. Así da el mismo día que `DateTime::format('w')` en todas las fechas
  probadas.
