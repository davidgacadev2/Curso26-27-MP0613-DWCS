# Requisitos — TAREA-02 (MEDIA): Cuaderno de notas del profesor

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R10) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

## Glosario

| Término | Significado |
|---|---|
| Cuaderno | Conjunto de alumnos con sus notas. Clave: nombre del alumno; valor: sus 3 notas. |
| Media ponderada | 30 % de la 1ª evaluación + 30 % de la 2ª + 40 % de la 3ª. |
| Calificación | Insuficiente (< 5), Suficiente (< 6), Bien (< 7), Notable (< 9), Sobresaliente (≥ 9). |
| Aprobado | Alumno con media ponderada ≥ 5. |

---

## 1. Requisitos funcionales

### RF-01. Añadir un alumno
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Un formulario permite introducir el nombre y apellidos y las notas de la 1ª, 2ª y 3ª evaluación. Si el alumno no existe en el cuaderno, se añade. |
| **Reglas** | Antes de guardarlo, el nombre se **normaliza**: se quitan espacios al principio, al final y repetidos, y se pone en mayúscula la inicial de cada palabra (`"  ana   PÉREZ "` → `"Ana Pérez"`). |
| **Salida** | Mensaje *Se ha añadido a «Nombre»*. El formulario queda vacío. |
| **Criterios de aceptación** | Al añadir `brais   OTERO` con notas `4`, `5,5`, `3` aparece en la tabla como *Brais Otero*. |

### RF-02. Modificar las notas de un alumno
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | Si el nombre (ya normalizado) existe en el cuaderno, se sustituyen sus notas. |
| **Salida** | Mensaje *Se han actualizado las notas de «Nombre»*. |
| **Criterios de aceptación** | Guardar `ana pérez` cuando existe *Ana Pérez* no crea un segundo alumno. |

### RF-03. Validar los datos del alumno
| | |
|---|---|
| **Origen** | R2 |
| **Descripción** | El servidor comprueba que: el nombre tiene entre 3 y 40 caracteres y solo contiene letras (incluidas tildes y ñ), espacios, guiones o apóstrofos; cada una de las 3 notas es obligatoria, numérica y está entre 0 y 10. Se admite coma o punto decimal. |
| **Salida** | Si hay errores, se muestran **todos** y el formulario conserva lo escrito (formulario pegajoso). No se modifica el cuaderno. |
| **Criterios de aceptación** | Nombre `X` y notas `5`, vacío, `abc` producen 3 errores. `6,5` y `6.5` se aceptan como la misma nota. |

### RF-04. Mostrar la tabla de notas
| | |
|---|---|
| **Origen** | R3 |
| **Descripción** | Se muestra una tabla con una fila por alumno: nombre, las 3 notas, media ponderada y calificación. Si el cuaderno está vacío, se muestra un mensaje en su lugar. |
| **Reglas** | Las notas y medias se muestran con 2 decimales y coma decimal. Las filas de alumnos suspensos y las notas inferiores a 5 se resaltan. La cabecera de cada evaluación indica su peso. |
| **Criterios de aceptación** | Notas 6,75 / 7 / 6 → media 6,53, calificación *Bien*. |

### RF-05. Editar un alumno
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Cada fila tiene un botón *Editar* que carga el nombre y las notas del alumno en el formulario de RF-01 para modificarlas. |
| **Criterios de aceptación** | Tras pulsar *Editar* en *Ana Pérez*, el formulario muestra su nombre y sus notas con coma decimal. |

### RF-06. Eliminar un alumno
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Cada fila tiene un botón *Eliminar* que borra al alumno del cuaderno. |
| **Salida** | Mensaje *Se ha eliminado a «Nombre»*. |

### RF-07. Ordenar la tabla
| | |
|---|---|
| **Origen** | R5 |
| **Descripción** | Un desplegable y un botón *Ordenar* permiten ordenar por **nombre** (alfabético) o por **media** (de mayor a menor). |
| **Reglas** | El criterio elegido se mantiene en las siguientes peticiones. Cualquier valor distinto de los dos permitidos se trata como *nombre*. |

### RF-08. Mostrar las estadísticas del grupo
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Si hay alumnos, se muestran: número de alumnos, media del grupo, alumno con mejor media y con peor media, número y porcentaje de aprobados, y la distribución de alumnos por calificación como gráfico de barras horizontales proporcionales. |
| **Criterios de aceptación** | Con los 5 alumnos correctos de `datos-prueba.txt`: media del grupo 6,46, mejor *Carmen Núñez Vázquez* (9,55), peor *Brais Otero* (4,05), 3 aprobados (60 %). |

### RF-09. Carga masiva de alumnos
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | Un área de texto permite pegar varios alumnos, uno por línea, con el formato `Nombre;nota1;nota2;nota3`. Al pulsar *Cargar*, cada línea correcta se añade o actualiza (RF-01, RF-02). |
| **Reglas** | Las líneas vacías y las que empiezan por `#` se ignoran. Cada línea se valida como en RF-03, y además debe tener exactamente 4 campos. |
| **Salida** | Número de alumnos cargados y lista de errores con el **número de línea** y el motivo. Las líneas erróneas no impiden cargar las correctas. |
| **Criterios de aceptación** | `datos-prueba.txt` carga 5 alumnos e informa de 5 errores (líneas 9 a 13). |

### RF-10. Vaciar el cuaderno
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Si hay alumnos, un botón *Vaciar* borra todo el cuaderno, pero solo si se ha marcado la casilla de confirmación. |
| **Salida** | Con la casilla marcada: mensaje *Se ha vaciado el cuaderno*. Sin marcar: error que lo indica, y el cuaderno no cambia. |

### RF-11. Conservar el cuaderno entre peticiones
| | |
|---|---|
| **Origen** | R9 |
| **Descripción** | Después de cualquier acción (guardar, editar, eliminar, ordenar, cargar…) el cuaderno conserva todos sus alumnos y notas. |
| **Criterios de aceptación** | Se pueden añadir 3 alumnos uno tras otro y los 3 siguen en la tabla. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP o con `php -S`. | Tema 1 |
| RNF-02 | Es una **única página** (`index.php`) que se envía a sí misma por el método **POST**. | Enunciado |
| RNF-03 | El estado (el cuaderno) se guarda **en la propia página**, en campos `hidden` con la forma `alumnos[Nombre][0..2]`, para que PHP reconstruya el array multidimensional en `$_POST['alumnos']`. | R9 |
| RNF-04 | No se usa `$_SESSION`, cookies, ficheros, bases de datos ni JavaScript. | Restricciones |
| RNF-05 | El cuaderno es un **array asociativo multidimensional** (nombre → array de 3 notas). | Enunciado |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-06 | Las funciones están en `funciones.php`, como mínimo: `normalizarNombre()`, `validarNota()`, `mediaPonderada()`, `calificacion()`, `procesarCargaMasiva()`, `ordenarCuaderno()` y `estadisticas()`. | R10 |
| RNF-07 | Ninguna de esas funciones hace `echo`: reciben datos y devuelven resultados, o modifican arrays recibidos **por referencia**. | R10 |
| RNF-08 | Los pesos, los nombres de las evaluaciones, las calificaciones y la nota de aprobado se definen con `const`. Cambiar un peso solo exige tocar una línea. | R10 |
| RNF-09 | El código está comentado y con nombres significativos. | Rúbrica |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-10 | Toda la validación se hace **en el servidor**. | R2 |
| RNF-11 | **No se confía en los campos ocultos**: al recuperar el cuaderno se vuelve a validar cada alumno y se descartan los datos incorrectos (por ejemplo, una nota de 99 escrita con las herramientas de desarrollador). | R9 |
| RNF-12 | Los valores de selección (criterio de ordenación) se comprueban contra una **lista blanca**. | R5 |
| RNF-13 | Todo texto del usuario que se escriba en la página (en contenido o en atributos `value`) pasa por `htmlspecialchars()`. | Restricciones |
| RNF-14 | El formato del nombre (RF-03) garantiza que se puede usar como clave en los nombres de los campos ocultos sin romper el HTML. | R2, R9 |
| RNF-15 | La aplicación no produce avisos ni errores de PHP con ninguna entrada. | Calidad |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-16 | Un único formulario envuelve toda la página para que cualquier botón envíe también el estado. | Pistas |
| RNF-17 | Al pulsar *Intro* en un campo de texto se ejecuta la acción *Guardar* y no otra (el primer botón *submit* del formulario es el de guardar). | Pistas, rúbrica 1 |
| RNF-18 | Las notas se escriben y se muestran con coma decimal, como es habitual en español. | R2, R3 |
| RNF-19 | Los mensajes de error son claros e indican el campo y el motivo; en la carga masiva, también la línea. | R2, R7 |
| RNF-20 | El aspecto se define en una hoja de estilos externa (`estilos.css`). | Rúbrica |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-21 | El trabajo se entrega en el repositorio privado de GitHub del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Renombrar a un alumno al editarlo (campo oculto `nombreOriginal`).
- Exportar el cuaderno al formato de la carga masiva.
- Ordenación alfabética correcta en español (tildes) con `Collator` (extensión *intl*).
