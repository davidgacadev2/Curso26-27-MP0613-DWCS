# Requisitos — TAREA-02 (AVANZADA): Siete y media

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R9) del que procede cada uno.

## Glosario

| Término | Significado |
|---|---|
| Mano | Una apuesta, las cartas del jugador y, si se planta, las de la banca. |
| Banca | El servidor: roba cartas mientras tenga menos puntos que el jugador; gana los empates. |
| Siete y media "de mano" | 7 y media con dos cartas (un 7 y una figura): paga el doble. |
| Evento interno | El turno de la banca: lo genera el programa, no el navegador. |
| Estado transitorio | ST3 (turno de la banca): se atiende en la misma petición y nunca llega a la página. |

---

## 1. Requisitos funcionales

### RF-01. Diseño con el autómata
| | |
|---|---|
| **Origen** | R1, R2 |
| **Descripción** | Diagrama con el estado transitorio y el evento interno, tablas y código que los sigue. |
| **Criterios de aceptación** | `estado=3` (el transitorio), `evento[]` o un evento `---` (*Pedir* sin mano, *Apostar* después de retirarse) empiezan otra partida: "Fichas: 100" y `estado` 1. |

### RF-02. Apuesta
| | |
|---|---|
| **Origen** | R3, R5 |
| **Descripción** | Apuesta de 5 a 50 y no más que el saldo; reparte la primera carta. |
| **Criterios de aceptación** | Primera petición con la semilla 7: "Fichas: 100". `abc`, `51`, `importe[]` o `<script>`: "La apuesta debe ser un número entero de fichas entre 5 y 50.", sin `<script>` en la página. 10: "Mano 1", "Caballo de oros", "Tus cartas: media" y `estado` 2. |

### RF-03. Pedir carta y plantarse
| | |
|---|---|
| **Origen** | R6, R7 |
| **Descripción** | Pedir: seguir, pasarse o llegar a 7 y media (se planta solo). Plantarse: juega la banca. |
| **Criterios de aceptación** | Semilla 7, mano 1: pedir → "3 de oros", "Tus cartas: 3 y media"; pedir → "4 de copas", "7 y media", la banca saca "2 de bastos" y "6 de copas" ("La banca: 8"), "La banca se pasa: ganas 10 fichas." y "Fichas: 110" (`estado` 4). Mano 2 apostando 50, plantarse con el rey: "La banca gana con 5: pierdes 50 fichas." y "Fichas: 60". Mano 3 (sota, as, 4, 3) apostando 20: "Te has pasado (8 y media): pierdes 20 fichas." sin cartas de la banca. |

### RF-04. Siete y media de mano
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | 7 y media con dos cartas paga el doble si la banca se pasa. |
| **Criterios de aceptación** | Semilla 12, mano 2 (7 de bastos), apostando 10, pedir → "Caballo de oros", "ganas 20 fichas (¡siete y media con dos cartas: paga doble!)" y "Fichas: 120". |

### RF-05. Fin de la mano y de la partida
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Siguiente mano, retirarse y quedarse sin fichas. |
| **Criterios de aceptación** | *Siguiente mano*: "Manos: 1 (ganadas 1, perdidas 0)", `estado` 1 y `cartas` 0. *Retirarse* con 60: "Te retiras con 60 fichas: pierdes 40." (`estado` 6). Pasarse apostando las 20 últimas fichas: "Te has pasado (8 y media) y te has quedado sin fichas." (`estado` 5); con 10 fichas, plantarse con el rey: "La banca gana con 5 y te has quedado sin fichas.". *Nueva partida*: "Fichas: 100" y "Manos: 0". |

### RF-06. Estado recibido
| | |
|---|---|
| **Origen** | R9 |
| **Descripción** | Los datos deben ser posibles en el estado en el que dicen estar. |
| **Criterios de aceptación** | Turno del jugador con 7 y media (semilla 7, mano 1, 3 cartas), apuesta 60, apuesta mayor que el saldo, manos mal contadas, semilla 0, saldo `<script>` o `saldo[]`; apuesta con saldo 0 o manos mal contadas en ST1; manos negativas en ST4: "La partida recibida no es válida" y `estado` 1. |

---

## 2. Requisitos no funcionales

| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | PHP 8 en XAMPP o con `php -S`; una única página por POST; sin `$_SESSION`, bases de datos ni ficheros. | Tema 2 |
| RNF-02 | El código sigue el autómata; el evento interno se resuelve con la propia tabla de eventos-estados. | R2 |
| RNF-03 | Ninguna carta que no se haya mostrado aparece en el HTML. | R4 |
| RNF-04 | Todo lo que llega se valida (también los campos ocultos) y toda la salida se escapa. | R9 |
| RNF-05 | El diagrama y las tablas se entregan en PDF junto al código, en el repositorio privado de GitHub del alumno (carpeta `TEMA-02`). | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Doblar la apuesta.
- Otras reglas para la banca.
- Estadísticas de juego.
