# Requisitos — TAREA-02 (AVANZADA): Cuatro en raya

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R9) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

## Glosario

| Término | Significado |
|---|---|
| Jugadas | Texto con la columna (0 a 6) de cada ficha, en orden: `"3324"`. Las de posición par son rojas. |
| Reconstruir | Repetir las jugadas sobre un tablero vacío para obtener el tablero, la última ficha y el ganador. |
| Línea ganadora | Las casillas (`"fila-columna"`) del cuatro en raya. |
| Columna libre | Columna cuya casilla de arriba (fila 0) está vacía. |
| Semilla | Número guardado en el estado con el que se inicializa `mt_srand` antes de cada decisión de la máquina. |

---

## 1. Requisitos funcionales

### RF-01. Formulario de configuración
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Modo, nombres, nivel y quién empieza; la primera vez propone contra la máquina y nivel normal. |
| **Criterios de aceptación** | Al abrir la página: `value="maquina" checked` y la opción "normal: gana, tapa y prefiere el centro". |

### RF-02. Validar la configuración
| | |
|---|---|
| **Origen** | R2 |
| **Descripción** | Todos los errores a la vez y formulario pegajoso. |
| **Criterios de aceptación** | Modo `x` y sin nombre: "Elige si juegan dos personas" y "(tiene 0)". Ana y ` ana `: "no pueden llamarse igual". Sin jugador 2 y empieza `3`: dos errores. Contra la máquina con `máquina` y nivel `experto`: "Elige el nivel de la máquina" y "«Máquina» es el nombre de la máquina". `ÑandúÑandúÑandúX`: "(tiene 16)" y el campo lo conserva. `modo[]`, `jugador1[]`, `empieza[]`: errores sin avisos. |

### RF-03. Soltar fichas
| | |
|---|---|
| **Origen** | R3, R4 |
| **Descripción** | La ficha cae a la casilla libre más baja; el turno alterna; solo se guardan las jugadas. |
| **Criterios de aceptación** | Dos personas, Ana empieza, columna 4 (valor 3): `name="jugadas" value="3"`, la casilla `ficha R ultima` y "Turno de Brais (amarillas)". El nombre `<b>Ana` se muestra como `&lt;b&gt;Ana`. |

### RF-04. Columnas no válidas
| | |
|---|---|
| **Origen** | R5 |
| **Descripción** | Columna inexistente o llena: error y nada cambia. |
| **Criterios de aceptación** | `columna=7` y `columna[]=1`: "Esa columna no existe.". Tras seis fichas en la columna 1: "La columna 1 está llena.", las jugadas siguen siendo `000000` y su botón está deshabilitado. |

### RF-05. Cuatro en raya y empate
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Se detecta el cuatro en raya en las cuatro direcciones y el empate; se resalta la línea. |
| **Criterios de aceptación** | Vertical (columnas 3 4 3 4 3 4 3): "¡Cuatro en raya! Gana &lt;b&gt;Ana con las rojas en 7 jugadas." y casillas `ganadora`; otra jugada: "La partida ha terminado". Horizontal (0 1 0 2 6 3 6 4): "Gana Brais con las amarillas en 8 jugadas.". Diagonal (0 1 1 2 2 3 2 3 3 6 3): "Gana Ana con las rojas en 11 jugadas.". Las 42 jugadas `664326132021514663654032511224451535430000`: "El tablero está lleno: empate.". |

### RF-06. Nueva partida
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | *Nueva partida* vuelve al formulario con los jugadores, el modo y el nivel anteriores. |
| **Criterios de aceptación** | Tras la partida de Ana y Brais: `value="dos" checked` y `name="jugador2" value="Brais"`. Tras una partida contra la máquina en fácil: `value="maquina" checked` y `value="facil" selected`. |

### RF-07. La máquina
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | La máquina juega en la misma petición; fácil al azar, normal con las cuatro reglas. |
| **Criterios de aceptación** | Contra la máquina (normal, semilla 7), Ana juega 0: "La máquina ha jugado en la columna 4." y jugadas `03`; tras 0 y 0 (`03030`), la máquina tapa: "La máquina ha jugado en la columna 1." (`030300`). Si Ana juega 0, 1, 2 y 6, "Gana Máquina con las amarillas en 8 jugadas.". Si empieza la máquina: "La máquina ha empezado en la columna 4.", `name="maquina" value="R"`. Jugar con las jugadas `33` (le toca a la máquina): "Ahora le toca a la máquina.". |

### RF-08. Deshacer e historial
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Deshacer quita la última jugada (y la de la máquina); el historial lista las jugadas. |
| **Criterios de aceptación** | Dos personas, jugadas `34`, *Deshacer*: "Jugada deshecha.", jugadas `3` y el historial con "Ana (rojas): columna 4" y sin la jugada de Brais. Máquina que ha empezado (`3`): "No hay ninguna jugada tuya que deshacer."; tras jugar Ana y contestar la máquina, *Deshacer* vuelve a `3`. |

### RF-09. Validar el estado recibido
| | |
|---|---|
| **Origen** | R9 |
| **Descripción** | Antes de jugar se comprueba la partida recibida; si no es posible, error y vuelta al formulario. |
| **Criterios de aceptación** | Jugadas `7`, `0000000` o `34343430` (una ficha después de ganar); `nombreR` vacío, `nombreA=ana` (igual que Ana), `maquina=X`, `maquina=A` sin que las amarillas se llamen Máquina, `nivel=dios`, `semilla=0`, `nombreR=<script>…` o `jugadas[]`: "La partida recibida no es válida", sin avisos ni `<script>` en la página. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP o con `php -S`. | Tema 1 |
| RNF-02 | Una única página que se envía a sí misma por **POST**. | Enunciado |
| RNF-03 | Sin `$_SESSION`, cookies, bases de datos ni ficheros: el estado viaja en campos ocultos. | Tema 2 |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-04 | El estado es mínimo (las jugadas); el tablero, el turno y el ganador se calculan. | R4 |
| RNF-05 | Las dimensiones, los colores, los niveles y las direcciones son constantes: cambiar el tamaño del tablero solo exige cambiar `FILAS` y `COLUMNAS`. | Buenas prácticas |
| RNF-06 | La lógica está en funciones con tipos; el tablero se pasa por referencia solo donde se modifica. | Tema 2 |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-07 | Todo lo que se escribe en el HTML pasa por `htmlspecialchars`; los campos ocultos se validan siempre. | R9 |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-08 | El tablero se parece al de verdad (fichas redondas sobre fondo azul); la última ficha y la línea ganadora se distinguen. | R3, R6 |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-09 | El trabajo se entrega en el repositorio privado de GitHub del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Nivel difícil con minimax.
- Comprobar las jugadas de la máquina con la semilla.
- Otros tamaños de tablero.
- Marcador entre partidas.
