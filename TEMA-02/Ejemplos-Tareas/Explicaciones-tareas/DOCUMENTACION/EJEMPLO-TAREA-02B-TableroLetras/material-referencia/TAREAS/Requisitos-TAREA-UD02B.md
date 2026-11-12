# Requisitos — TAREA-02B: Juego de tablero con letras ocultas

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en
[MP0613-DWCS-EJEMPLO-RESUELTO-TAREA-UD02B.pdf](MP0613-DWCS-EJEMPLO-RESUELTO-TAREA-UD02B.pdf) (apartado
*ENUNCIADO*). La columna **Origen** indica de qué parte del enunciado procede cada requisito.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, organización del código…).

Los requisitos marcados como **(implícito)** no aparecen escritos en el enunciado, pero se deducen de él, de la
captura de pantalla o de las tres soluciones de ejemplo (`CODIGO SOLUCIONES/Ejemplos-Tareas/EJEMPLO-TAREA-02B`).

## Glosario

| Término | Significado |
|---|---|
| Tablero | Cuadrícula de casillas. Las del borde muestran la letra `o` y sobre ellas se dibuja un **rombo** de asteriscos `*`. |
| Rombo | Figura de asteriscos centrada: 1 asterisco en la primera fila, 3 en la segunda… hasta la fila central (completa), y luego vuelve a disminuir. |
| Letra oculta | Letra de la palabra escondida debajo de un asterisco. |
| Marcador | Puntos acumulados por el jugador (y fallos seguidos). |

---

## 1. Requisitos funcionales

### Configuración de la partida

#### RF-01. Introducir el tamaño del tablero y la palabra
| | |
|---|---|
| **Origen** | Enunciado, punto 1; captura |
| **Descripción** | La página solicita la longitud del lado del tablero y una palabra, con un botón *Enviar* y un botón *Borrar*. |
| **Nota** | El enunciado habla de "número de filas y columnas" pero la captura y las soluciones usan un único valor, el **lado** de un tablero cuadrado (ver apartado 3). |

#### RF-02. Validar los datos de entrada
| | |
|---|---|
| **Origen** | Enunciado, punto 1 |
| **Descripción** | Se comprueba que el lado es un número **impar entre 5 y 30** y que la palabra tiene **5 letras como mínimo**. |
| **Salida** | Si hay errores, se muestra un mensaje informativo que indica **cuál es el error** (lado fuera de rango, lado par, palabra demasiado corta…) y no se dibuja el tablero. |
| **Criterios de aceptación** | Lado 6: error de número impar. Lado 3: error de rango. Palabra `sol`: error de longitud. |

#### RF-03. Dibujar el tablero
| | |
|---|---|
| **Origen** | Enunciado, punto 2; captura |
| **Descripción** | Al pulsar *Enviar* con datos correctos aparece el tablero: las casillas fuera del rombo muestran `o` y las del rombo muestran `*`. |
| **Criterios de aceptación** | Lado 7: la fila 1 tiene un asterisco en el centro, la fila 4 tiene 7 asteriscos y la fila 7 vuelve a tener uno. |

#### RF-04. Esconder las letras al azar
| | |
|---|---|
| **Origen** | Enunciado, punto 2 |
| **Descripción** | Las letras de la palabra se reparten **al azar** debajo de los asteriscos (una letra por asterisco). Los demás asteriscos no esconden nada. |
| **Reglas** | Si la palabra tiene más letras que asteriscos, solo se esconden las que caben y la palabra se recorta (implícito, solución 03). |

### Juego

#### RF-05. Cada asterisco es un botón
| | |
|---|---|
| **Origen** | Enunciado, punto 2 |
| **Descripción** | Cada asterisco del tablero es un botón que el jugador puede pulsar. Las `o` y las letras ya descubiertas no se pueden pulsar. |

#### RF-06. Acierto
| | |
|---|---|
| **Origen** | Enunciado, punto 2 |
| **Descripción** | Si el asterisco pulsado esconde una letra: el jugador **suma 5 puntos**, el asterisco se cambia por la **letra** acertada y esa letra **desaparece** de la caja de texto de la palabra. |
| **Reglas** | Un acierto pone a cero el contador de fallos seguidos (implícito). |

#### RF-07. Fallo
| | |
|---|---|
| **Origen** | Enunciado, punto 2 |
| **Descripción** | Si el asterisco pulsado no esconde nada, el jugador **resta 2 puntos** y suma un fallo seguido. |

#### RF-08. Marcador
| | |
|---|---|
| **Origen** | Enunciado, punto 2 |
| **Descripción** | El marcador de puntos aparece **en cuanto se hace clic por primera vez** en un asterisco (antes no se muestra) y se actualiza en cada jugada. |
| **Reglas** | Las soluciones muestran también el número de fallos seguidos (implícito). |

#### RF-09. Fin del juego
| | |
|---|---|
| **Origen** | Enunciado, punto 3 |
| **Descripción** | El juego termina cuando el jugador **acierta todas las letras** o cuando tiene **más de 3 fallos seguidos** (es decir, al 4.º fallo consecutivo). |
| **Salida** | Se muestra un mensaje de fin de juego y los botones del tablero quedan deshabilitados (implícito, soluciones). |

#### RF-10. Conservar la partida entre jugadas
| | |
|---|---|
| **Origen** | Texto previo al enunciado |
| **Descripción** | Tras cada clic, el tablero conserva las letras escondidas, las letras ya descubiertas, la palabra restante, los puntos y los fallos seguidos. |

#### RF-11. Empezar de nuevo
| | |
|---|---|
| **Origen** | Captura (botón *Borrar*) |
| **Descripción** | El botón *Borrar* limpia el formulario para empezar una partida nueva. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está programada en **PHP** y se ejecuta en el servidor; cada clic es una petición al servidor. | Enunciado |
| RNF-02 | El **estado de la página** se almacena en la versión de la página que se sirve al cliente, con campos `hidden`. En particular, el **tablero (un array) se guarda en campos hidden** y se recupera en la siguiente petición. | Texto previo al enunciado |
| RNF-03 | No se usa `$_SESSION`, bases de datos ni ficheros (implícito, coherente con la tarea 02A). | Tarea 02A |
| RNF-04 | Se usan funciones de la **biblioteca estándar** de PHP cuando sea posible (por ejemplo `str_split`, `shuffle`, `array_pad`, `substr_replace`, `preg_match`). | Texto previo al enunciado |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-05 | El código se organiza en funciones (generar la información del tablero, dibujarlo…) y, si se quiere, en varios ficheros incluidos con `include` (como las soluciones: `entrada.php`, `marcador.php`). | Soluciones |
| RNF-06 | Se aprovecha el algoritmo del rombo de asteriscos visto en los boletines de bucles. | Soluciones |
| RNF-07 | El código está comentado. | Buenas prácticas |

### Validación y robustez
| Id | Requisito | Origen |
|---|---|---|
| RNF-08 | Se validan **todas** las entradas en el servidor. | Enunciado, punto 1 |
| RNF-09 | La palabra solo admite letras sin tilde ni ñ (`/^[A-Za-z]+$/`), porque `str_split` y `strlen` trabajan con bytes (implícito, solución 03). | Soluciones |

### Depuración y estudio
| Id | Requisito | Origen |
|---|---|---|
| RNF-10 | Las soluciones se estudian ejecutándolas **paso a paso con el depurador** (Xdebug). | Texto previo al enunciado |
| RNF-11 | Para ver cómo se construye la página paso a paso en el navegador se recomienda usar `ob_end_flush()` y `ob_implicit_flush()`. | Texto previo al enunciado |

### Presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-12 | El tablero se dibuja como una tabla HTML con estilos en una hoja CSS externa, con un aspecto similar al de la captura. | Captura |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-13 | El trabajo se entrega en el repositorio **privado** de GitHub `Trabajos-DWCS`, asociado a la cuenta 1 @iesrodeira.gal del alumno, compartido con el profesor y organizado en carpetas por unidad. La URL se indica en la entrega. | Cabecera del enunciado |

---

## 3. Puntos que el enunciado no concreta

Conviene aclararlos con el alumnado:

- **Filas y columnas o lado**: el texto pide "número de filas y columnas (cada uno impar)", pero la captura y las
  tres soluciones piden un único lado y dibujan un tablero cuadrado. Para un rombo simétrico el tablero cuadrado es
  lo natural.
- **Rango del lado**: "entre 5 y 30" con número impar significa en la práctica de 5 a 29.
- **"Más de 3 fallos seguidos"**: el juego termina en el 4.º fallo consecutivo, no en el 3.º.
- **Letras repetidas**: si la palabra tiene letras repetidas (`caracola`), cada aparición se esconde en un
  asterisco distinto y al acertar se quita **una** aparición de la caja de texto.
- **Palabra más larga que el número de asteriscos**: no se indica qué hacer; la solución 03 la recorta.
- **Puntuación negativa**: no se indica un mínimo; los puntos pueden ser negativos.
