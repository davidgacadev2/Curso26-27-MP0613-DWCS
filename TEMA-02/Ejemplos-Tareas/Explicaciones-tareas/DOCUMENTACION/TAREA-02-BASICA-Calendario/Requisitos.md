# Requisitos — TAREA-02 (BÁSICA): Calendario mensual con festivos

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R10) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

---

## 1. Requisitos funcionales

### RF-01. Mostrar el mes actual por defecto
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Cuando la página se carga sin parámetros en la URL, muestra el calendario del mes y año actuales. |
| **Entrada** | Ninguna (petición GET sin `mes` ni `anio`). |
| **Salida** | Calendario del mes actual con el día de hoy resaltado. |
| **Criterios de aceptación** | Al abrir `index.php` sin parámetros se ve el mes en curso; el formulario muestra ese mes y año seleccionados. |

### RF-02. Seleccionar mes y año
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Un formulario enviado por **GET** permite elegir el mes en una lista desplegable (se ve el nombre y se envía el número 1–12) y escribir el año en un campo numérico. Un botón *Mostrar* envía el formulario. |
| **Criterios de aceptación** | Al pulsar *Mostrar* la URL contiene `?mes=…&anio=…` y se muestra el calendario solicitado. |

### RF-03. Volver al mes actual
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Un enlace *Hoy* carga de nuevo la página sin parámetros. |
| **Criterios de aceptación** | Desde cualquier mes, el enlace *Hoy* muestra el mes actual. |

### RF-04. Validar los datos recibidos
| | |
|---|---|
| **Origen** | R2 |
| **Descripción** | El servidor comprueba que el mes es un entero entre 1 y 12 y que el año es un entero entre 1900 y 2100. Ambos son obligatorios si se envía alguno de ellos. |
| **Salida** | Si hay errores, se muestra una lista con **todos** los errores encontrados y **no** se dibuja el calendario. |
| **Criterios de aceptación** | `?mes=13&anio=hola` muestra dos errores (mes y año). `?mes=5` sin año muestra el error del año. `?mes=5&anio=1899` muestra el error del año. |

### RF-05. Conservar los datos del formulario
| | |
|---|---|
| **Origen** | R3 |
| **Descripción** | Tras cada envío, el formulario muestra el mes y el año que se enviaron, incluso si eran incorrectos ("formulario pegajoso"). |
| **Criterios de aceptación** | Después de pedir mayo de 2027, el desplegable muestra *Mayo* y el campo año *2027*. |

### RF-06. Dibujar el calendario del mes
| | |
|---|---|
| **Origen** | R4, R5 |
| **Descripción** | Se muestra una tabla HTML con el título *Mes de Año*, una cabecera con los días de la semana empezando en **lunes** y los días del mes colocados en su día de la semana. Las celdas anteriores al día 1 y posteriores al último día (hasta completar la semana) quedan vacías. |
| **Reglas** | El número de días del mes depende del mes y de si el año es bisiesto (divisible por 4 y no por 100, o divisible por 400). |
| **Criterios de aceptación** | Mayo de 2027 empieza en sábado (5 celdas vacías antes). Febrero de 2024 tiene 29 días; febrero de 2100, 28. |

### RF-07. Resaltar fines de semana, festivos y el día de hoy
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Los sábados y domingos, los festivos y el día de hoy se distinguen visualmente. Al pasar el ratón por un festivo se muestra su nombre. |
| **Criterios de aceptación** | El 17 de mayo aparece resaltado como festivo con el texto *Día das Letras Galegas* al pasar el ratón. |

### RF-08. Gestionar los festivos de fecha fija
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | La aplicación conoce los festivos de fecha fija de Galicia (Año Nuevo, Reyes, 1 de mayo, Letras Galegas, 25 de julio, 15 de agosto, 12 de octubre, 1 de noviembre, 6, 8 y 25 de diciembre). |
| **Criterios de aceptación** | Todos ellos aparecen resaltados en su mes, sea cual sea el año. |

### RF-09. Navegar al mes anterior y al siguiente
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Encima del calendario hay dos enlaces, *« mes anterior* y *mes siguiente »*, con el nombre del mes y el año de destino. |
| **Reglas** | De enero se pasa a diciembre del año anterior y de diciembre a enero del año siguiente. No se muestran enlaces que lleven fuera del rango 1900–2100. |
| **Criterios de aceptación** | En diciembre de 2026 el enlace siguiente lleva a enero de 2027. En enero de 1900 no hay enlace al mes anterior. |

### RF-10. Mostrar el resumen del mes
| | |
|---|---|
| **Origen** | R9 |
| **Descripción** | Debajo del calendario se muestran el número de días del mes, el número de **días laborables** (lunes a viernes que no son festivo) y la lista de festivos del mes ordenada por día, o un mensaje si no hay ninguno. |
| **Criterios de aceptación** | Mayo de 2027: 31 días, 20 laborables y dos festivos (1 y 17 de mayo). Marzo: mensaje "sin festivos de fecha fija". |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP (Apache) o con el servidor integrado `php -S`. | Tema 1 |
| RNF-02 | Es una **única página** (`index.php`) que se llama a sí misma por el método **GET**. | Enunciado |
| RNF-03 | No se usa `$_SESSION`, cookies, ficheros de datos ni bases de datos. Tampoco JavaScript. | Restricciones |
| RNF-04 | El número de días del mes se calcula con funciones propias `esBisiesto()` y `diasEnMes()` (esta última con `switch`). **Prohibido** `date('t')` y `cal_days_in_month()`. Para el día de la semana del día 1 sí se puede usar `date('N', mktime(...))`. | R5 |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-05 | Todas las funciones están en `funciones.php`, incluido con `require_once`. | R10 |
| RNF-06 | Los festivos se guardan en un **array asociativo** con claves `'mm-dd'` en `festivos.php`, que hace `return` del array; se carga con `$festivos = require 'festivos.php';`. | R7 |
| RNF-07 | Las funciones **reciben parámetros y devuelven valores**; no se usa `global`. Las funciones que generan HTML lo devuelven como cadena. | R10 |
| RNF-08 | Los valores fijos (años mínimo y máximo, nombres de meses y días) se definen como **constantes**. | Buenas prácticas |
| RNF-09 | El código está comentado y con nombres de variables y funciones significativos. | Rúbrica 11 |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-10 | Toda la validación se hace **en el servidor**, aunque el formulario use atributos HTML5 (`min`, `max`). | R2 |
| RNF-11 | Cualquier dato del usuario que se escriba en la página pasa por `htmlspecialchars()` (prevención de XSS). | Restricciones |
| RNF-12 | La aplicación no produce avisos ni errores de PHP con ninguna entrada, incluidas las manipuladas a mano en la URL. | R2 |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-13 | Los mensajes de error son claros e indican qué campo es incorrecto y qué valores se admiten. | R2 |
| RNF-14 | El aspecto se define en una hoja de estilos externa (`estilos.css`) mediante clases CSS (fin de semana, festivo, hoy…). | R6, rúbrica 11 |
| RNF-15 | La página se ve correctamente en un navegador de escritorio actual. | Rúbrica 11 |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-16 | El trabajo se entrega en el repositorio privado de GitHub del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Festivos móviles (Jueves y Viernes Santo con `easter_date()`).
- Número de semana ISO en una primera columna.
