# Requisitos — TAREA-02 (MEDIA): Memoria de parejas

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R8) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

## Glosario

| Término | Significado |
|---|---|
| Carta | Casilla del tablero con una letra. Boca abajo se ve como un botón `?`; boca arriba, con su letra. |
| Pareja | Dos cartas con la misma letra. Cada letra aparece exactamente dos veces en el tablero. |
| Turno | Girar dos cartas seguidas. |
| Carta pendiente | La primera carta girada del turno, que espera a la segunda. |
| Posición | Identificador de una carta: `"fila-columna"`, empezando en 0 (`"1-2"` = fila 1, columna 2). |
| Estado | Todo lo que hay que recordar entre clics: configuración, tablero, encontradas, pendiente y marcador. |

---

## 1. Requisitos funcionales

### RF-01. Formulario de configuración
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | La página pide filas, columnas, letras y fallos permitidos, con los botones *Empezar* y *Borrar*. |
| **Salida** | La primera vez, los campos valen 4, 4, `ABCDEFGH` y 8. |
| **Criterios de aceptación** | Al abrir la página, el campo `letras` contiene `ABCDEFGH`. |

### RF-02. Validar la configuración
| | |
|---|---|
| **Origen** | R2 |
| **Descripción** | Se validan los cuatro datos en el servidor y se muestran todos los errores a la vez. |
| **Reglas** | Filas y columnas: enteros de 2 a 6. `filas × columnas` par. Letras: solo A-Z tras pasar a mayúsculas y quitar espacios de los extremos; sin repetidas; al menos tantas como parejas (si sobran, se usan las primeras). Fallos: entero de 1 a 30. |
| **Salida** | Mensajes que dicen cuál es el error; el formulario conserva lo escrito. |
| **Criterios de aceptación** | 3 × 3: "hay 9 cartas: el número de cartas debe ser par". Filas 7, columnas `x`, letras `aab`, fallos 0: cuatro errores, uno de ellos "Repetidas: A". 2 × 3 con `AB`: "Para 3 parejas hacen falta al menos 3 letras". Letras `<b>x`: error y el campo muestra `&lt;B&gt;X`. |

### RF-03. Crear el tablero
| | |
|---|---|
| **Origen** | R3 |
| **Descripción** | Con datos correctos se crea un tablero de `filas × columnas` cartas: cada letra, dos veces, al azar, todas boca abajo. |
| **Reglas** | Se usan las primeras `filas × columnas / 2` letras. |
| **Criterios de aceptación** | 2 × 3 con ` abcd `: el tablero tiene 6 botones (`0-0` a `1-2`) y usa las letras `ABC`. |

### RF-04. Primera carta del turno
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Al pulsar una carta boca abajo sin carta pendiente, se gira y queda pendiente. |
| **Salida** | "Has girado una A. Elige la segunda carta." |
| **Criterios de aceptación** | Pulsar otra vez la misma carta da "Esa carta no se puede elegir". |

### RF-05. Pareja
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Si la segunda carta tiene la misma letra que la pendiente, las dos quedan encontradas y se suman 10 puntos. |
| **Criterios de aceptación** | Tras encontrar la pareja de A: "¡Pareja de A! +10 puntos." y "Parejas: 1 de 3". |

### RF-06. Fallo
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Si las letras son distintas, se restan 2 puntos y se suma un fallo. Las dos cartas se ven solo en esa respuesta. |
| **Criterios de aceptación** | C y B: "No son pareja (C y B)", 8 puntos (10 − 2) y 1 fallo; en la jugada siguiente la B vuelve a estar boca abajo. |

### RF-07. Marcador
| | |
|---|---|
| **Origen** | R5 |
| **Descripción** | Desde la primera carta girada se muestran los puntos, las parejas (`n de total`) y los fallos. |
| **Criterios de aceptación** | Antes del primer clic no aparece "Puntos:"; tras el primero, "Puntos: 0". |

### RF-08. Partida ganada
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Al encontrar todas las parejas termina la partida con un mensaje de enhorabuena. |
| **Criterios de aceptación** | 2 × 3 sin fallos: "Has encontrado las 3 parejas con 30 puntos". Un clic posterior no cambia el marcador. |

### RF-09. Partida perdida
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Al superar los fallos permitidos termina la partida y se enseñan todas las cartas. |
| **Criterios de aceptación** | 2 × 2 con 1 fallo permitido y dos fallos: "Has superado los 1 fallos permitidos" y ningún botón de carta. |

### RF-10. Nueva partida
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | El botón *Nueva partida* vuelve al formulario con la configuración de la partida anterior. |
| **Criterios de aceptación** | Tras una partida 2 × 3 con `ABC` y 1 fallo, el formulario muestra esos valores. |

### RF-11. Estado en campos ocultos
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | La configuración, el tablero (`cartas[f][c]`), las encontradas, la pendiente y el marcador viajan en campos `hidden`. |

### RF-12. Validar el estado recibido
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Antes de jugar se comprueba que el estado es una partida posible; si no, error y formulario. |
| **Criterios de aceptación** | `cartas[0][0]=Z`, `encontradas=0-0` (media pareja), `filas=9` o `cartas[1][1]=<script>`: "La partida recibida no es válida". `carta[]=0-0` o `carta=9-9`: "Esa carta no se puede elegir", sin avisos. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP o con `php -S`. | Tema 1 |
| RNF-02 | Una única página que se envía a sí misma por **POST**; sin `$_SESSION`, bases de datos ni ficheros. | Enunciado |
| RNF-03 | El tablero se guarda como **array en campos ocultos** (`cartas[f][c]`) y se recupera en la petición siguiente. | R7 |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-04 | La lógica está en funciones cortas (validar, crear el tablero, leer el estado, jugar, estado de la partida) separadas del HTML. | Buenas prácticas |
| RNF-05 | Se usan funciones de la biblioteca estándar (`str_split`, `shuffle`, `array_chunk`, `count_chars`, `implode` / `explode`, `filter_var`). | Tema 2 |
| RNF-06 | El reparto se puede reproducir con una semilla (`mt_srand`) para probar. | Pistas |
| RNF-07 | El código está comentado. | Buenas prácticas |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-08 | Toda salida se escapa con `htmlspecialchars`. | R8 |
| RNF-09 | Se validan todas las entradas, también los campos ocultos; ninguna entrada produce avisos de PHP. | R8 |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-10 | El tablero es una tabla HTML con estilos en una hoja CSS externa; las cartas encontradas y las giradas se distinguen por el color. | Enunciado |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-11 | El trabajo se entrega en el repositorio privado de GitHub `Trabajos-DWCS` del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Contador de turnos y bonificación por terminar en pocos.
- Cartas con emojis o imágenes.
- Todo el estado en un único campo oculto (JSON en Base64).
- Modo de dos jugadores.
