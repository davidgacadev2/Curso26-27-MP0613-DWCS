# Requisitos — TAREA-02 (AVANZADA): El ascensor del instituto

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R7) del que procede cada uno.

## Glosario

| Término | Significado |
|---|---|
| Llamada | Planta que espera al ascensor. Se guardan ordenadas en un texto: `"024"`. |
| Instante | Lo que pasa al pulsar *Avanzar*: arrancar, subir o bajar una planta, abrir o cerrar las puertas. |
| Invariante | Condición que deben cumplir los datos en un estado (PL0). |
| Ascensor colectivo | Sigue en su dirección mientras haya llamadas en ella; parado, va a la más cercana. |

---

## 1. Requisitos funcionales

### RF-01. Diseño con el autómata
| | |
|---|---|
| **Origen** | R1, R2 |
| **Descripción** | Diagrama, tablas e invariantes antes que el código; el código los sigue. |
| **Criterios de aceptación** | Una transición por `case`. `estado=9`, `evento[]` o un evento `---` (*Avanzar* en emergencia, *Llamar* fuera de servicio) reinician: "El ascensor espera en la planta baja." y `estado` 1. |

### RF-02. Llamadas
| | |
|---|---|
| **Origen** | R3, R4 |
| **Descripción** | Añadir llamadas; abrir si está parado en esa planta; nada si ya está abierto en ella. |
| **Criterios de aceptación** | Al empezar, llamar a la planta baja: "El ascensor ya está aquí: se abren las puertas en la Planta baja." (`estado` 4). Tras reiniciar, llamar a la 3.ª y a la 1.ª: `llamadas` = `13` y el botón "Esperando". `llamar:9` y `llamar:<b>`: "Esa planta no existe.", sin `<b>` en la página. Con las puertas abiertas en la 1.ª, llamar a la 1.ª: "Las puertas ya están abiertas en la 1.ª planta."; llamar a la baja: `llamadas` = `03`. En marcha, llamar a la 2.ª: `023`. |

### RF-03. Movimiento
| | |
|---|---|
| **Origen** | R5 |
| **Descripción** | Arrancar hacia la más cercana, avanzar planta a planta, parar donde llaman y seguir en la misma dirección. |
| **Criterios de aceptación** | Desde la baja con llamadas `13`: "arranca hacia arriba" (`estado` 2), "Llega a la 1.ª planta: se abren las puertas.", "sigue subiendo" (aunque llamen desde la baja), para en la 2.ª y en la 3.ª, "Se cierran las puertas y baja.", "Pasa por la 2.ª planta.", "Pasa por la 1.ª planta.", "Llega a la Planta baja" con "Plantas recorridas: 6" y "Paradas: 4"; después "No hay más llamadas." y "sigue parado". Parado en la 2.ª con llamadas en la baja y la 4.ª: sube (empate). |

### RF-04. Emergencia
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Parar, anular las llamadas, rearmar; fuera de servicio a la tercera. |
| **Criterios de aceptación** | "PARADA DE EMERGENCIA en la 4.ª planta.", `estado` 5, `llamadas` vacío y "Emergencias: 1". *Rearmar*: "Ascensor rearmado (emergencias: 1 de 3)."; la segunda, "2 de 3"; a la tercera, "Fuera de servicio: 3 paradas de emergencia." (`estado` 6, *Avanzar* deshabilitado). |

### RF-05. Invariantes
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Los datos recibidos deben cumplir los invariantes del estado. |
| **Criterios de aceptación** | Subiendo sin llamadas arriba (`estado=2`, `piso=0`, `llamadas` vacío o `piso=4` con `0`), bajando sin llamadas abajo, `piso=7`, `llamadas=31` o `33`, parado con llamada en su planta, emergencia con llamadas, 3 emergencias fuera de ST5, dirección `x`, `llamadas=<script>…`, `piso[]`: "Los datos del ascensor no son válidos" y `estado` 1, sin `<script>` en la página. |

---

## 2. Requisitos no funcionales

| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | PHP 8 en XAMPP o con `php -S`; una única página por POST; sin `$_SESSION`, bases de datos ni ficheros. | Tema 2 |
| RNF-02 | El código sigue el autómata: constantes, tabla `$automata`, `switch` con un `case` por transición y predicados en variables. | R2 |
| RNF-03 | Los invariantes están en una función, uno por estado, igual que en la tabla del diseño. | R7 |
| RNF-04 | El ascensor nunca sale del edificio, con ninguna secuencia de botones. | R5, R7 |
| RNF-05 | El diagrama y las tablas se entregan en PDF junto al código, en el repositorio privado de GitHub del alumno (carpeta `TEMA-02`). | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- *Abrir puertas* y *Sobrecarga*.
- Varios ascensores.
