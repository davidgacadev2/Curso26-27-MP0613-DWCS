# TAREA-02 (nivel MEDIO): Cuaderno de notas del profesor

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

**María** imparte clase en varios grupos y lleva las notas en una hoja de papel. Le pide a **Carlos** una pequeña
aplicación web donde apuntar las notas de las tres evaluaciones de cada alumno y que le calcule automáticamente
las medias y algunas estadísticas del grupo. Como todavía no saben usar sesiones ni bases de datos, Carlos decide
guardar el estado en la propia página, como ya hizo con la agenda de la comunidad de vecinos.

## Enunciado

Programa una aplicación web en PHP, en **una única página** (`index.php`) que se envía a sí misma por el método
**POST**, que gestione un cuaderno de notas.

El cuaderno se guardará en un **array asociativo multidimensional** cuya clave es el nombre del alumno y cuyo valor
es un array con las tres notas:

```php
$cuaderno = [
    'Ana Pérez Lago' => [7.5, 6.0, 8.25],
    'Brais Otero'    => [4.0, 5.5, 3.0],
];
```

### Requisitos

- **R1. Alta y modificación.** Un formulario con el nombre y las notas de la 1ª, 2ª y 3ª evaluación.
  - El nombre se normaliza: se quitan espacios sobrantes y se pone en mayúscula la inicial de cada palabra
    (`"  ana   PÉREZ "` → `"Ana Pérez"`). Usa funciones `mb_` porque hay tildes y eñes.
  - Si el alumno no existe se añade; si existe, se sustituyen sus notas. Se mostrará un mensaje distinto en cada caso.
- **R2. Validación.** Se comprobará en el servidor que:
  - el nombre tiene entre 3 y 40 caracteres y solo contiene letras (con tildes, ñ...), espacios, guiones o apóstrofos;
  - cada nota es un número entre 0 y 10 (se admite coma o punto decimal: `6,5` o `6.5`).

  Se mostrarán **todos** los errores y el formulario conservará lo escrito (formulario pegajoso). Si todo es
  correcto, el formulario se vacía.
- **R3. Tabla de notas.** Para cada alumno se mostrará: nombre, las tres notas, la **media ponderada**
  (30 % - 30 % - 40 %) y la **calificación** textual: *Insuficiente* (< 5), *Suficiente* (< 6), *Bien* (< 7),
  *Notable* (< 9) y *Sobresaliente*. Las filas de alumnos suspensos y las notas suspensas se resaltarán con CSS.
  Las notas se mostrarán con dos decimales y coma decimal (`number_format`).
- **R4. Editar y eliminar.** Cada fila tendrá dos botones:
  - *Editar*: carga el nombre y las notas de ese alumno en el formulario de R1;
  - *Eliminar*: borra al alumno del cuaderno.

  Pista: usa `<button type="submit" name="eliminar" value="Nombre del alumno">`.
- **R5. Ordenación.** Un desplegable permite ordenar la tabla por *nombre* (alfabético) o por *media* (de mayor a
  menor). El criterio elegido se mantiene entre peticiones. Solo se aceptarán los valores conocidos (lista blanca).
- **R6. Estadísticas del grupo.** Número de alumnos, media del grupo, alumno con mejor y con peor media, número y
  porcentaje de aprobados y la **distribución** de calificaciones (cuántos alumnos hay en cada una), dibujada como
  un gráfico de barras horizontales hecho con `<div>` cuyo ancho es proporcional.
- **R7. Carga masiva.** Un `<textarea>` permite pegar varios alumnos de golpe, uno por línea, con el formato:

  ```text
  Nombre Apellidos;nota1;nota2;nota3
  ```

  - Las líneas vacías o que empiezan por `#` se ignoran.
  - Las líneas correctas se añaden (o actualizan) y las incorrectas se informan **indicando el número de línea** y
    el motivo (número de campos incorrecto, nombre no válido, nota no válida...).
  - Se informará de cuántos alumnos se han cargado.
- **R8. Vaciar.** Un botón vacía el cuaderno, pero solo si se ha marcado una casilla de confirmación.
- **R9. Estado en campos ocultos.** El cuaderno viaja de una petición a otra en campos `hidden` con la forma
  `alumnos[Nombre][0]`, `alumnos[Nombre][1]`, `alumnos[Nombre][2]`, de manera que PHP reconstruya el array en
  `$_POST['alumnos']`.
  **Importante:** los campos ocultos los puede modificar cualquiera con las herramientas de desarrollador del
  navegador, así que al recuperarlos **se deben volver a validar** y descartar los datos incorrectos.
- **R10. Organización.** Las funciones irán en `funciones.php`. Como mínimo: `normalizarNombre()`,
  `validarNota()`, `mediaPonderada()`, `calificacion()`, `procesarCargaMasiva()`, `ordenarCuaderno()` y
  `estadisticas()`. Ninguna de ellas hará `echo`; reciben datos y devuelven resultados (o modifican arrays pasados
  **por referencia**). Las constantes (pesos, nombres de las evaluaciones...) se definirán con `const`.

### Restricciones

- No se permite usar `$_SESSION`, cookies, ficheros ni bases de datos.
- Todo texto introducido por el usuario que se escriba en la página pasará por `htmlspecialchars()`.

### Pistas

- Si pones **un único formulario** que envuelva toda la página, todos los botones envían también los campos ocultos.
- Al pulsar *Intro* en un campo de texto, el navegador simula un clic en el **primer** botón *submit* del formulario.
  Piensa qué botón quieres que sea ese.
- `preg_split('/\R/', $texto)` separa un texto en líneas sea cual sea el salto de línea (`\n`, `\r\n`).
- `arsort()` ordena un array por sus valores de mayor a menor **conservando las claves**; `ksort()` ordena por clave.
- `array_fill_keys(['a', 'b'], 0)` crea `['a' => 0, 'b' => 0]`.
- Con `match (true) { $nota < 5 => '...', ... }` se escribe de forma compacta una cadena de `if/elseif`.

### Datos de prueba

En [`datos-prueba.txt`](datos-prueba.txt) tienes un texto para pegar en la carga masiva. Contiene líneas correctas
y líneas con errores a propósito.

### Ampliaciones opcionales (no puntúan, pero se valoran)

- Permitir **renombrar** a un alumno al editarlo (pista: un campo oculto `nombreOriginal`).
- Botón para **exportar** el cuaderno al formato de la carga masiva (rellenando el `<textarea>`).
- Ordenar los nombres correctamente en español (tildes) con la clase `Collator` de la extensión *intl*.

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. Estructura de la página, formulario POST único y uso correcto de los botones (incluido *Intro*). | 0,75 |
| 2. Alta y modificación de alumnos con normalización del nombre y mensajes. | 1,00 |
| 3. Validación de nombre y notas, mostrando todos los errores, y formulario pegajoso. | 1,00 |
| 4. Tabla con medias ponderadas, calificaciones y resaltado de suspensos. | 1,00 |
| 5. Botones *Editar* y *Eliminar* por fila. | 1,00 |
| 6. Ordenación por nombre y por media, con lista blanca y criterio persistente. | 0,75 |
| 7. Estadísticas del grupo y gráfico de distribución. | 1,25 |
| 8. Carga masiva con informe de errores por número de línea. | 1,25 |
| 9. Vaciado con confirmación. | 0,25 |
| 10. Estado en campos `hidden` con array multidimensional **y revalidación** al recuperarlo. | 1,00 |
| 11. Organización en funciones sin `echo`, constantes, paso por referencia, `htmlspecialchars()`. | 0,75 |
| **Total** | **10,00** |

## Solución propuesta

En la carpeta [`SOLUCION-01`](SOLUCION-01/) tienes una propuesta de solución. Te recomendamos:

1. Ejecutarla paso a paso con Xdebug y observar el contenido de `$_POST['alumnos']` en cada petición.
2. Abrir las herramientas de desarrollador, **modificar a mano** un campo oculto (por ejemplo, poner una nota de
   `99`) y comprobar que `recuperarCuaderno()` descarta ese alumno.
