# Requisitos — TAREA-02 (MEDIA): El ahorcado

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R10) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

## Glosario

| Término | Significado |
|---|---|
| Palabra secreta | La palabra que hay que adivinar, con su ortografía (CIGÜEÑA). Nunca se escribe en el HTML mientras se juega. |
| Índice | Posición de la palabra en la lista de su categoría (empieza en 0). Es lo que se guarda en vez de la palabra. |
| Letras usadas | Letras ya pulsadas, en un único texto (`"UÑZ"`). |
| Fallo | Una letra usada que no está en la palabra, o 2 por cada palabra equivocada al resolver. |
| Letra base | La letra sin tilde ni diéresis: Á, Ü → A, U. La Ñ es su propia letra. |

---

## 1. Requisitos funcionales

### RF-01. Configuración de la partida
| | |
|---|---|
| **Origen** | R1, R2 |
| **Descripción** | Formulario con la categoría (desplegable generado desde `palabras.php`) y la dificultad (fácil 8, normal 6, difícil 4). |
| **Salida** | La primera vez propone Animales y normal. |
| **Criterios de aceptación** | Al abrir la página: `<option selected>Animales</option>` y "normal (6 fallos)". Categoría `Plantas` y dificultad `x`: "Elige una de las categorías" y "Elige una dificultad". `categoria[]=Frutas`: los mismos errores, sin avisos. |

### RF-02. Elegir la palabra sin enviarla
| | |
|---|---|
| **Origen** | R2, R3 |
| **Descripción** | Al empezar se elige una palabra al azar (semilla opcional en un campo oculto); se guardan la categoría y el índice. |
| **Criterios de aceptación** | Animales, normal y semilla 15: "7 letras", `name="indice" value="2"` y `name="maxfallos" value="6"`; el HTML no contiene CIGÜEÑA ni CIGUEÑA. |

### RF-03. Probar una letra
| | |
|---|---|
| **Origen** | R4, R5 |
| **Descripción** | Cada letra pulsada se añade a las usadas y descubre todas sus apariciones, con tilde o diéresis. |
| **Salida** | Mensajes "¡Bien! La U aparece una vez.", "La Z no está en la palabra.", "Ya habías probado la U.", "Esa letra no es válida.". |
| **Criterios de aceptación** | Con CIGÜEÑA: U → aparece `Ü` y el botón U sale `class="acierto" disabled`; Ñ → "La Ñ aparece una vez"; Z → botón `class="fallo" disabled`; `AB` y `letra[]=A` → "Esa letra no es válida.". |

### RF-04. Fallos y dibujo
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Los fallos se calculan a partir de las letras usadas (y las palabras falladas) y se muestran con el dibujo. |
| **Criterios de aceptación** | Tras U, Ñ y Z en CIGÜEÑA: "Fallos: 1 de 6". |

### RF-05. Fin de la partida
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Ganada sin huecos; perdida al llegar a los fallos permitidos. Después ya no se puede jugar. |
| **Criterios de aceptación** | CIGÜEÑA con U, Ñ, Z, C, I, G, E, A: "¡Has acertado CIGÜEÑA con 1 fallo!" y el botón B deshabilitado; una letra más da "La partida ya ha terminado". Galicia, difícil, semilla 4 con X, K, Q, W: "Te han ahorcado. La palabra era A CORUÑA.", la Ñ con `class="faltaba"` y el espacio con `class="espacio"`. |

### RF-06. Resolver la palabra
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Se compara lo escrito con la palabra sin mayúsculas, tildes ni espacios de los extremos; si falla, son 2 fallos. |
| **Criterios de aceptación** | A CORUÑA y `a coruna`: "«A CORUNA» no es la palabra: cuenta como 2 fallos." y "Fallos: 2 de 4". JABALÍ y ` jabali `: "¡Correcto!" y "¡Has acertado JABALÍ con 2 fallos!" (tras un intento vacío y uno con `<b>x`, que sale como `«&lt;B&gt;X»`). |

### RF-07. Marcador y nueva partida
| | |
|---|---|
| **Origen** | R8, R9 |
| **Descripción** | Victorias y derrotas en campos ocultos; *Nueva partida* vuelve al formulario con la configuración anterior. |
| **Criterios de aceptación** | Tras ganar CIGÜEÑA: "Victorias: 1"; *Nueva partida* propone Animales y normal y lleva `name="victorias" value="1"`. Tras dos derrotas y otra victoria: "Victorias: 2 · Derrotas: 2". |

### RF-08. Validar el estado recibido
| | |
|---|---|
| **Origen** | R10 |
| **Descripción** | Antes de jugar se comprueba que lo recibido es una partida posible; si no, error y vuelta al formulario. |
| **Criterios de aceptación** | `indice=99`, `usadas=AA`, `usadas=1`, `maxfallos=7`, `indice[]=1` con `palabrasfalladas=-1`, `usadas=QWXYZK` (demasiados fallos) o `categoria=<script>…`: "La partida recibida no es válida", sin avisos ni `<script>` en la página. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP o con `php -S`. | Tema 1 |
| RNF-02 | Una única página que se envía a sí misma por **POST**. | Enunciado |
| RNF-03 | Sin `$_SESSION`, cookies, bases de datos ni ficheros de datos: el estado viaja en campos ocultos. | Tema 2 |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-04 | Las palabras están en `palabras.php`; añadir una categoría o una palabra no obliga a tocar otro fichero. | R1 |
| RNF-05 | Se guarda el estado mínimo (categoría, índice, dificultad, letras usadas, palabras falladas); lo demás se calcula. | R6 |
| RNF-06 | La lógica está en funciones con tipos; las constantes (dificultades, abecedario, letras con tilde) no se repiten. | Tema 2 |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-07 | La palabra secreta no aparece en el HTML mientras se juega. | R3 |
| RNF-08 | Todo lo que se escribe en el HTML pasa por `htmlspecialchars`; los campos ocultos se validan siempre. | R10 |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-09 | Las letras usadas se distinguen por color y no se pueden volver a pulsar. | R5 |
| RNF-10 | *Intro* en el campo de la palabra resuelve. | R7 |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-11 | El trabajo se entrega en el repositorio privado de GitHub del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Pistas a cambio de fallos.
- Modo de dos jugadores con palabra escrita por un jugador.
- Puntuación y récord.
- Evitar repetir palabra.
