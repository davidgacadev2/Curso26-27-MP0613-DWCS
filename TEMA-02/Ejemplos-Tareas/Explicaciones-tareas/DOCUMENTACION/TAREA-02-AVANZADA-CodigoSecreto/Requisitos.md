# Requisitos — TAREA-02 (AVANZADA): Código secreto

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R8) del que procede cada uno.

## Glosario

| Término | Significado |
|---|---|
| Código secreto | Combinación de colores que hay que adivinar. Se calcula a partir de la semilla; nunca está en la página. |
| Negras | Posiciones con el color correcto. |
| Blancas | Colores que están en el código, pero en otra posición. |
| Autómata | Diagrama de estado-transición ampliado + tablas de descriptores, eventos-estados y transiciones. |

---

## 1. Requisitos funcionales

### RF-01. Diseño con el autómata
| | |
|---|---|
| **Origen** | R1, R2 |
| **Descripción** | El diagrama y las tablas del autómata se entregan antes que el código, y el código los sigue. |
| **Criterios de aceptación** | Cada transición de la tabla es un `case`; cada camino de una transición, un `if`/`elseif` con el mismo predicado; un evento `---` (por ejemplo, *Probar* en la configuración, `estado=7` o `evento[]`) muestra la configuración con el marcador a cero. |

### RF-02. Configuración
| | |
|---|---|
| **Origen** | R3 |
| **Descripción** | Posiciones, colores, intentos y repetición, validados. |
| **Criterios de aceptación** | Posiciones 6, colores 9 e intentos 20: "4 o 5 posiciones", "6, 7 u 8 colores" y "entre 6 y 12", y sigue en la configuración (`estado` 1). `posiciones[]`, `colores[]`, `maxintentos[]`: los mismos errores, sin avisos. |

### RF-03. Empezar y código secreto
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Al empezar se guarda una semilla; el código se calcula con `hash_hmac()` en cada petición. |
| **Criterios de aceptación** | 4 posiciones, 6 colores, sin repetir y semilla 1: "4 posiciones, 6 colores, sin repetir", `estado` 2, `semilla` 1 y 10 intentos. |

### RF-04. Probar
| | |
|---|---|
| **Origen** | R5, R7 |
| **Descripción** | Validar el intento, puntuarlo, añadirlo al historial y terminar la partida si acierta o no quedan intentos. |
| **Criterios de aceptación** | Con la semilla 1: rojo, verde, azul, amarillo → "1 negras y 1 blancas.", quedan 9, `intentos` = `0123`. Una posición sin color: "Elige un color para cada una de las 4 posiciones." (el historial no cambia). Rojo dos veces: "no se repiten". Un color `<b>` o 7: "Elige un color", sin `<b>` en la página. Rojo, morado, verde, naranja: "¡Has descubierto el código en 2 intentos!", el código y `estado` 3. Con 6 intentos y colores repetibles (con la semilla 1, el código es azul, azul, verde, naranja), seis fallos: "Se han acabado los intentos." (`estado` 4). |

### RF-05. Pista
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Una por partida, cuesta un intento y exige que queden dos. |
| **Criterios de aceptación** | Tras el primer intento de RF-04: "Pista: en la posición 2 va" morado, quedan 8, `pista` = 1. Otra vez: "Ya has usado la pista de esta partida.". Con un solo intento: "No te quedan intentos para pagar la pista.". |

### RF-06. Fin, marcador, nueva partida y configuración
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Rendirse, nueva partida con la misma configuración, cambiar la configuración, marcador. |
| **Criterios de aceptación** | Tras ganar, *Nueva partida*: "Ganadas: 1", 10 intentos, `semilla` 2. *Rendirse*: "Te has rendido." y el código; *Nueva* y *Rendirse* otra vez: "Perdidas: 2". *Cambiar la configuración*: el formulario con "Ganadas: 1 · Perdidas: 2". |

### RF-07. Estado recibido
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | La partida de los campos ocultos se comprueba antes de jugar. |
| **Criterios de aceptación** | Con la semilla 1, `intentos=0514` (ya acertado), `01234`, `0193` (color 9), `posiciones=9`, `pista=7` o `-5`, `maxintentos=99`, `semilla=0`, diez intentos de diez, `intentos[]` o `colores=<script>…`: "La partida recibida no es válida" y la configuración (`estado` 1), sin `<script>` en la página. |

---

## 2. Requisitos no funcionales

| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | PHP 8 en XAMPP o con `php -S`; una única página por POST; sin `$_SESSION`, bases de datos ni ficheros. | Tema 2 |
| RNF-02 | El código sigue el autómata: constantes, tabla `$automata`, `switch` con un `case` por transición, predicados en variables y una función por evento saliente. | R2 |
| RNF-03 | El código secreto no aparece en el HTML en ningún momento de la partida (solo al terminar). | R4 |
| RNF-04 | Todo lo que llega se valida (también los campos ocultos) y toda la salida se escapa. | R8 |
| RNF-05 | El diagrama y las tablas se entregan en PDF junto al código, en el repositorio privado de GitHub del alumno (carpeta `TEMA-02`). | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Deshacer el último intento.
- Modo de dos jugadores.
- Puntuación y récord.
