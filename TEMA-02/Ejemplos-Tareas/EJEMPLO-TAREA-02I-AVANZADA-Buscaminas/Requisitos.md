# Requisitos — TAREA-02 (AVANZADA): Buscaminas

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R10) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

## Glosario

| Término | Significado |
|---|---|
| Casilla vecina | Cada una de las (hasta) 8 casillas que rodean a otra, en horizontal, vertical y diagonal. |
| Número de una casilla | Cantidad de minas que hay en sus casillas vecinas (0 a 8). |
| Casilla oculta / descubierta / con bandera | Estado de una casilla para el jugador. |
| Jugada | Cada clic sobre el tablero que cambia algo (descubrir, poner o quitar bandera). |

---

## 1. Requisitos funcionales

### Configuración

#### RF-01. Elegir el nivel de dificultad
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Un formulario permite elegir un nivel predefinido o uno personalizado y empezar una partida nueva con el botón *Empezar*. |
| **Datos** | Principiante 9 × 9 con 10 minas; Intermedio 16 × 16 con 40; Experto 16 × 30 con 99; Personalizado: filas, columnas y minas a elección. |
| **Criterios de aceptación** | Al elegir *Experto* y pulsar *Empezar* aparece un tablero de 16 filas y 30 columnas, todo oculto, con el marcador a 99 minas. |

#### RF-02. Validar la configuración personalizada
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | El servidor comprueba que las filas son un entero entre 5 y 16, las columnas un entero entre 5 y 30 y las minas un entero entre 1 y el máximo permitido. |
| **Reglas** | Máximo de minas = el menor entre el 35 % de las casillas y (casillas − 9), para que siempre quepa la zona segura del primer clic (RF-04). |
| **Salida** | Si hay errores se muestran **todos**, no se crea partida y el formulario conserva lo escrito. |
| **Criterios de aceptación** | 5 × 5 con 9 minas: error "entre 1 y 8". Filas 3, columnas 40, minas `abc`: 3 errores. |

#### RF-03. Mostrar la configuración de la partida en juego
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Mientras se juega, el formulario de configuración muestra el nivel (o los valores personalizados) de la partida actual, para poder volver a empezar con la misma configuración. |

### Juego

#### RF-04. Primer clic seguro
| | |
|---|---|
| **Origen** | R3 |
| **Descripción** | Las minas no existen hasta el primer clic de *descubrir*. Entonces se reparten al azar evitando la casilla pulsada y todas sus vecinas, y se calcula el número de cada casilla. |
| **Criterios de aceptación** | El primer clic nunca pierde y siempre descubre una zona vacía (un 0) con su entorno. |

#### RF-05. Descubrir una casilla
| | |
|---|---|
| **Origen** | R2, R4 |
| **Descripción** | En modo *Descubrir*, al pulsar una casilla oculta: si tiene mina, se pierde (RF-10); si su número es mayor que 0, se muestra el número con su color; si es 0, se muestra vacía. |
| **Reglas** | Una casilla con bandera no se puede descubrir: se muestra un aviso. Una casilla ya descubierta no cambia. |

#### RF-06. Descubrir automáticamente las zonas vacías
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Al descubrir una casilla con 0, se descubren también todas sus vecinas; si alguna es también un 0, se descubren las suyas, y así sucesivamente. Las casillas con bandera no se descubren. |
| **Criterios de aceptación** | En un tablero 16 × 30 con 1 mina, el primer clic descubre todo el tablero y se gana. |

#### RF-07. Poner y quitar banderas
| | |
|---|---|
| **Origen** | R5 |
| **Descripción** | Unos botones de radio cambian el modo del clic entre *Descubrir* y *Poner/quitar bandera*. En modo bandera, un clic en una casilla oculta le pone bandera y en una con bandera se la quita. |
| **Reglas** | El modo elegido se mantiene entre clics. Al empezar una partida nueva el modo vuelve a *Descubrir*. Se pueden poner banderas antes del primer clic. |

#### RF-08. Marcador
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Encima del tablero se muestran las minas restantes (minas − banderas, puede ser negativo), el número de jugadas y el tiempo de juego en formato `mm:ss`. |
| **Reglas** | El tiempo empieza a contar en el primer clic de *descubrir* y se detiene al terminar la partida. |

#### RF-09. Ganar la partida
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Se gana cuando todas las casillas sin mina están descubiertas. Todas las minas se marcan con bandera y se muestra un mensaje de enhorabuena con el tiempo y las jugadas. |

#### RF-10. Perder la partida
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Se pierde al descubrir una mina. Se muestran todas las minas, la que ha explotado resaltada en rojo, y se marcan las banderas que estaban mal puestas. Un mensaje indica la fila y columna de la mina. |

#### RF-11. Bloquear el tablero al terminar
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Una vez ganada o perdida la partida, el tablero ya no tiene botones ni selector de modo; solo se puede empezar una nueva. |

#### RF-12. Conservar la partida entre clics
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Cada clic es una petición al servidor; tras cada una, la partida continúa exactamente donde estaba (tablero, casillas descubiertas, banderas, jugadas, hora de inicio). |

#### RF-13. Rechazar datos manipulados
| | |
|---|---|
| **Origen** | R9 |
| **Descripción** | Si el estado de la partida recibido no es coherente, se muestra un error y se pide empezar una nueva partida. Si la casilla pulsada no existe, se muestra un error y la partida no cambia. |
| **Criterios de aceptación** | Cambiar en el código de la página el número de minas, o enviar `celda=99-99`, produce el error correspondiente. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP o con `php -S`. | Tema 1 |
| RNF-02 | **Sin JavaScript**: cada clic es un envío de formulario POST. Cada casilla oculta es un `<button type="submit" name="celda" value="fila-columna">`. | Caso práctico, R2 |
| RNF-03 | No se usa `$_SESSION`, cookies, ficheros, bases de datos ni clases. | Restricciones |
| RNF-04 | El estado de la partida viaja en campos `hidden`. Para no generar cientos de campos, **cada fila se guarda como una cadena** (tablero `"01*1000"`, estado `"DDOOBOO"`) y se reconstruye con `str_split()`. | R8 |
| RNF-05 | La partida se representa con **un único array asociativo** (dimensiones, matrices de tablero y de estado, jugadas, tiempos, resultado). | Pistas |
| RNF-06 | El descubrimiento de zonas vacías (RF-06) se implementa con una **función recursiva**. | R4 |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-07 | El código se reparte, como mínimo, en `config.php` (constantes), `logica.php` (reglas, sin HTML), `vista.php` (funciones que **devuelven** HTML) e `index.php` (recibe la petición, llama a la lógica y muestra la vista). | R10 |
| RNF-08 | Los niveles se definen en un **array constante** (`const NIVELES`), y los límites, estados y resultados también como constantes. Añadir un nivel solo exige tocar `config.php`. | R1 |
| RNF-09 | Las funciones que modifican la partida o las matrices la reciben **por referencia**; las que solo consultan, por valor. | Restricciones |
| RNF-10 | Una función `vecinos()` se reutiliza para calcular los números, para el relleno recursivo y para la zona segura del primer clic. | Pistas, rúbrica 4 |
| RNF-11 | El código está comentado. Al principio de `index.php` se responden las tres preguntas de reflexión del enunciado. | Rúbrica 12 |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-12 | **No se confía en el cliente**: al recuperar la partida se valida todo (números enteros, dimensiones dentro de los límites, número de minas válido, longitud y caracteres de cada fila, minas contadas = minas declaradas, resultado dentro de una lista blanca). | R9 |
| RNF-13 | El `value` del botón pulsado se valida con un formato estricto (`fila-columna`) y se comprueba que está dentro del tablero. | R9 |
| RNF-14 | El modo de clic y el nivel se comprueban contra una **lista blanca**. | R1, R5 |
| RNF-15 | Los textos que se escriben en la página pasan por `htmlspecialchars()` cuando corresponde. | Restricciones |
| RNF-16 | La aplicación no produce avisos ni errores de PHP con ninguna entrada. | Calidad |
| RNF-17 | Limitación conocida y aceptada: con *Ver código fuente* se puede ver dónde están las minas, y con *Atrás* se puede volver a un estado anterior (preguntas de reflexión 1 y 2). | Reflexión |

### Rendimiento
| Id | Requisito | Origen |
|---|---|---|
| RNF-18 | El tablero más grande (16 × 30) se genera, se recorre recursivamente y se envía sin retardos perceptibles. | Pistas |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-19 | Cada número (1 a 8) tiene su color, como en el juego original; las casillas ocultas, descubiertas, con mina y con bandera se distinguen claramente. | R4, R7 |
| RNF-20 | Los mensajes (errores, avisos, victoria, derrota) son claros. | R1, R7 |
| RNF-21 | El aspecto se define en una hoja de estilos externa (`estilos.css`). | Rúbrica 12 |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-22 | El trabajo se entrega en el repositorio privado de GitHub del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Ampliaciones opcionales (para nota)

| Id | Requisito | Tipo |
|---|---|---|
| RF-A1 | **Acorde**: al pulsar un número descubierto que tiene alrededor tantas banderas como indica, se descubren todas sus vecinas sin bandera. Si alguna bandera estaba mal puesta, se pierde. Si no hay suficientes banderas, se muestra un aviso. | Funcional |
| RNF-A1 | **Estado protegido**: la partida viaja en **un único** campo oculto cifrado y autenticado (`json_encode()` + `openssl_encrypt()` con `aes-256-gcm` + `base64_encode()`). El jugador no puede ver las minas ni modificar la partida sin que se detecte. La clave secreta solo existe en el servidor. | No funcional (seguridad) |
