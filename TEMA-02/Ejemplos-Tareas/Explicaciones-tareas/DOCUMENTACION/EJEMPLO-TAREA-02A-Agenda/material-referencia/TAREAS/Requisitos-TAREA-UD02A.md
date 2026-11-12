# Requisitos — TAREA-02A: Agenda de la comunidad de vecinos

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en
[MP0613-DWCS-EJEMPLO-RESUELTO-TAREA-UD02A.pdf](MP0613-DWCS-EJEMPLO-RESUELTO-TAREA-UD02A.pdf). La columna
**Origen** indica de qué parte del enunciado procede cada requisito: R1…R4, la *Pista* o la tabla de criterios de
puntuación (C1…C12).

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, organización del código…).

Los requisitos marcados como **(implícito)** no aparecen escritos en el enunciado, pero se deducen de él o de las
capturas de pantalla.

## Glosario

| Término | Significado |
|---|---|
| Agenda | Lista de contactos. Cada contacto tiene un nombre y un número de teléfono. |
| Contacto | Par *nombre → teléfono*. No puede haber dos contactos con el mismo nombre. |

---

## 1. Requisitos funcionales

### RF-01. Mostrar el contenido de la agenda
| | |
|---|---|
| **Origen** | Enunciado, C2 |
| **Descripción** | En la parte superior de la página se muestran todos los contactos de la agenda (nombre y teléfono). |
| **Reglas** | Si la agenda está vacía, no se muestra la sección de datos (implícito, ver capturas). |

### RF-02. Formulario de nuevo contacto
| | |
|---|---|
| **Origen** | Enunciado, C3 |
| **Descripción** | En la parte inferior hay un formulario con dos cuadros de texto (nombre y teléfono), un botón para enviar (*Añadir Contacto*) y otro para limpiar los campos (*Limpiar Campos*). |

### RF-03. Nombre obligatorio
| | |
|---|---|
| **Origen** | C5, captura 2 |
| **Descripción** | Si se envía el formulario sin nombre (o solo con espacios), no se modifica la agenda y se muestra una advertencia (*"El Nombre es obligatorio"*). |

### RF-04. Añadir un contacto
| | |
|---|---|
| **Origen** | R1, C6 |
| **Descripción** | Si el nombre introducido **no existe** en la agenda y el teléfono **no está vacío**, se añade el contacto. |

### RF-05. Modificar el teléfono de un contacto
| | |
|---|---|
| **Origen** | R2, C7 |
| **Descripción** | Si el nombre introducido **ya existe** en la agenda y se indica un teléfono, se sustituye el teléfono anterior por el nuevo. |

### RF-06. Eliminar un contacto
| | |
|---|---|
| **Origen** | R3, C8 |
| **Descripción** | Si el nombre introducido **ya existe** en la agenda y **no** se indica teléfono, se elimina ese contacto. |
| **Reglas** | Si el nombre no existe y el teléfono está vacío, no ocurre nada (implícito: R1 solo añade con teléfono). |

### RF-07. Mostrar la opción de vaciar la agenda
| | |
|---|---|
| **Origen** | R4, C9 |
| **Descripción** | En cuanto la agenda tiene al menos un contacto, aparece un formulario o enlace *Vaciar*. Si la agenda está vacía, no aparece. |

### RF-08. Vaciar la agenda
| | |
|---|---|
| **Origen** | R4, C10 |
| **Descripción** | Al pulsar *Vaciar* se eliminan todos los contactos. Para ello se envía a la misma página una variable en la URL (por ejemplo `?limpiar=1`), y el script comprueba con `$_GET` si ha llegado. |

### RF-09. Conservar la agenda entre peticiones
| | |
|---|---|
| **Origen** | Pista, C4 |
| **Descripción** | Después de añadir, modificar o eliminar un contacto, el resto de contactos sigue en la agenda. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está programada en **PHP** en **una única página web**, que se llama a sí misma. | Enunciado, C1 |
| RNF-02 | El estado de la agenda se guarda **en la propia página**, como campos `hidden` del formulario. | Pista, C4 |
| RNF-03 | **Prohibido usar `$_SESSION`** (se verá en temas posteriores). Tampoco se usan bases de datos ni ficheros (implícito). | Pista |
| RNF-04 | La agenda se representa con un **array asociativo** cuya clave es el nombre y cuyo valor es el teléfono; así se garantiza que no hay nombres repetidos. | C11, enunciado |
| RNF-05 | El vaciado de la agenda usa una variable enviada por **GET** en la URL y se comprueba con `$_GET`. | R4, C10 |

### Calidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-06 | La página PHP tiene una estructura HTML correcta (DOCTYPE, `head`, `body`…). | C1 |
| RNF-07 | El código está comentado. | C12 |
| RNF-08 | El diseño de la página está cuidado (por ejemplo, con hoja de estilos y `fieldset` para cada zona, como en las capturas). | C12 |
| RNF-09 | Los datos del usuario se limpian de espacios al principio y al final antes de procesarlos (implícito, para que un nombre de solo espacios cuente como vacío). | C5 |
| RNF-10 | Los datos que se escriben en la página (contactos y campos ocultos) se escapan con `htmlspecialchars()` para que un nombre con comillas o `<` no rompa el HTML (implícito, buena práctica). | Buenas prácticas |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-11 | El trabajo se entrega en un repositorio **privado** de GitHub llamado `Trabajos-DWCS`, asociado a la cuenta 1 @iesrodeira.gal del alumno, compartido con el profesor y organizado en carpetas por unidad (`TEMA-01`, `TEMA-02`…). La URL del repositorio se indica en la entrega. | Cabecera del enunciado |

---

## 3. Puntos que el enunciado no concreta

Conviene aclararlos con el alumnado o decidirlos en la solución:

- **Método del formulario de contacto**: el enunciado solo exige GET para el vaciado; el formulario puede usar GET
  o POST. La solución de ejemplo usa GET para todo.
- **Formato del teléfono**: no se pide validarlo (solo que no esté vacío).
- **Normalización del nombre**: no se exige; la solución de ejemplo pone en mayúscula la inicial de cada palabra
  (`ucwords`), con lo que *juan* y *Juan* son el mismo contacto.
