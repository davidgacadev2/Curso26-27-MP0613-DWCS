# TAREA-02 (nivel AVANZADO): El ascensor del instituto

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

El departamento de Tecnología quiere explicar en clase cómo funciona el ascensor del instituto y le pide a **BK
Programación** una **simulación** en la web: un edificio de cinco plantas, un botón de llamada en cada una y un
ascensor que se mueve paso a paso, para y abre las puertas donde le llaman.

**Juan** se la encarga a **Carlos** y le hace notar algo: el panel del ascensor ya habla de estados (*Parado*,
*Subiendo*, *Bajando*, *Puertas abiertas*, *Emergencia*). Esta vez, el **diagrama de estado-transición ampliado**
de la página y el del ascensor serán el mismo. **María** le pide que piense, para cada estado, qué cosas **no pueden
pasar nunca**: con esas condiciones comprobará los campos ocultos.

## Enunciado

Diseña con el método de los **diagramas de estado-transición ampliados** y programa **a partir del autómata** una aplicación web en **una única página**
(`index.php`, con las acciones y los predicados en `funciones.php`) que se envía a sí misma por **POST**. El estado
viaja en **campos ocultos**, **sin usar `$_SESSION`**, bases de datos ni ficheros.

### Parte 1: el diseño

- **R1. Autómata.** Entrega, antes del código, el **diagrama de estado-transición ampliado** y las tablas de
  **descriptores**, **eventos-estados** y **transiciones**, la **tabla de invariantes** de cada estado y la
  **comprobación de los predicados** de las transiciones con más de dos caminos.
- **R2. Código que sigue el autómata.** Constantes `ST0`…, `EE0`…, la tabla `$automata[estado][evento]`, un `case`
  por transición y los predicados en variables `$pl0`, `$pl1`... Un evento que no puede darse en un estado (`---`)
  reinicia la simulación.

### Parte 2: la simulación

- **R3. El edificio.** Cinco plantas (planta baja y de la 1.ª a la 4.ª), dibujadas de arriba abajo, con el ascensor
  en su planta (con una flecha si sube o baja y distinto si tiene las puertas abiertas) y un botón **Llamar** en
  cada planta, resaltado mientras esa planta espera. Debajo, el **mensaje** de lo último que ha pasado, los botones
  **Avanzar**, **Parada de emergencia**, **Rearmar** y **Reiniciar**, y los contadores: plantas recorridas,
  paradas y emergencias. Al empezar, el ascensor está parado en la planta baja.
- **R4. Llamadas.** La planta va en el valor del botón (`llamar:3`). Una llamada se añade a la lista de pendientes
  (sin repetir). Si el ascensor está **parado en esa planta**, se abren las puertas al momento; si ya tiene las
  **puertas abiertas** en esa planta, no pasa nada (*Las puertas ya están abiertas*). Se puede llamar mientras se
  mueve.
- **R5. Avanzar.** Cada pulsación es un instante:
  - **parado**: si hay llamadas, cierra las puertas y arranca hacia la **más cercana** (a igual distancia, hacia
    arriba); si no, sigue parado;
  - **subiendo / bajando**: avanza una planta; si alguien espera en ella, para y **abre las puertas**;
  - **puertas abiertas**: las cierra y, si quedan llamadas, **sigue en la dirección que llevaba** mientras queden
    llamadas en esa dirección; si no, va hacia la más cercana. Sin llamadas, se queda parado.

  Ejemplo: parado en la planta baja con llamadas en la 3.ª y la 1.ª, sube, para en la 1.ª, sigue a la 3.ª (aunque
  mientras tanto llamen desde la baja) y después baja.
- **R6. Emergencia.** La *Parada de emergencia* detiene el ascensor donde esté y **anula todas las llamadas**.
  Mientras dura, solo se puede *Rearmar* o *Reiniciar*. A la **tercera** emergencia, *Rearmar* deja el ascensor
  **fuera de servicio** hasta que se reinicie.
- **R7. Invariantes.** Antes de cada transición (salvo la carga) se comprueba que los datos de los campos ocultos
  son posibles **en el estado en el que dicen estar**. Por ejemplo, un ascensor *subiendo* tiene que tener alguna
  llamada más arriba, y uno *parado* no puede tener una llamada en su propia planta. Si no se cumplen, se reinicia
  con un aviso. Ninguna entrada produce avisos de PHP ni se muestra sin escapar.

### Pistas

- La lista de llamadas cabe en un campo oculto como texto ordenado (`"024"`). La expresión regular
  `/^0?1?2?3?4?$/` comprueba a la vez que son cifras de 0 a 4, ordenadas y sin repetir.
- Hace falta una variable de estado con la **dirección** (`sube` o `baja`) incluso con las puertas abiertas: al
  cerrarlas, el ascensor tiene que saber hacia dónde iba.
- Los invariantes se escriben muy bien con `match ($estado) { ST1 => ..., ST2 => ..., ... }`.
- Como PL0 (los invariantes) se comprueba en todas las transiciones salvo T1, se puede comprobar una vez **antes**
  del `switch` y, si falla, cambiar la transición por T1.

### Ampliaciones opcionales

- *Abrir puertas*: con las puertas abiertas, retrasar el cierre un instante.
- Estado de *Sobrecarga*, con sus invariantes.
- Dos ascensores y un algoritmo que decida cuál atiende cada llamada.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Diagrama, tablas del autómata, invariantes y comprobación de predicados (R1) | 2,00 |
| 2. Código programado a partir del autómata (R2) | 1,00 |
| 3. Edificio, mandos, mensaje y contadores (R3) | 1,00 |
| 4. Llamadas en todos los estados (R4) | 1,00 |
| 5. Movimiento: arrancar, subir, bajar, parar y seguir en la misma dirección (R5) | 2,00 |
| 6. Emergencia, rearme y fuera de servicio (R6) | 1,00 |
| 7. Invariantes de cada estado y salida escapada (R7) | 1,50 |
| 8. Código documentado y sin avisos de PHP | 0,50 |
| **Total** | **10,00** |

## Solución propuesta

- **Autómata**: [`DIAGRAMA ESTADO-TRANSICION/Automata-Ascensor.html`](DIAGRAMA%20ESTADO-TRANSICION/Automata-Ascensor.html).
  Contiene el análisis, los invariantes, el diagrama, las tablas, la comprobación de predicados, el
  paso del autómata al código y las pruebas.
- **Código**: [`SOLUCION-01`](SOLUCION-01/), con su construcción paso a paso en
  [`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
