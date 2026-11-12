# EJEMPLO-TAREA-02A: Agenda — solución paso a paso

Descomposición paso a paso de la solución de referencia de la tarea 02A (la de los ejemplos del repositorio, que no se incluye en este
material: el último paso, `paso5-solucion-completa`, es esa solución sin cambios en el código), siguiendo el proceso
de desarrollo de software de los apuntes del tema 2: maqueta, lista de TODOs y desarrollo incremental. Los requisitos
(RF-01…, RNF-01…) que se citan entre corchetes en los comentarios son los del documento de requisitos de la Tarea 02A.

Cada carpeta de `PASO A PASO` contiene la aplicación completa tal como queda al terminar ese paso, con los mismos
nombres de fichero, y **todas se pueden ejecutar**.

| Paso | Qué se hace | Requisitos |
|---|---|---|
| `paso1-maqueta` | Solo HTML y CSS, con ejemplos de todos los estados: el aviso, dos contactos, el botón *Vaciar*. | RF-02, RNF-06, RNF-08 |
| `paso2-todos` | La maqueta con un `TODO` en cada parte que hay que programar, en el orden en que se hará. Muestra el formulario y una agenda vacía. | — |
| `paso3-nuevo-contacto` | Recepción (`trim`) y validación del formulario, guardar el contacto en el array asociativo y mostrar la agenda. | RF-01, RF-03, RF-04 |
| `paso4-estado-hidden` | La agenda viaja en campos `hidden` (`agenda[nombre]`) y se recupera en cada petición. Modificar un teléfono funciona ya sin más código. | RF-05, RF-09, RNF-02 |
| **`paso5-solucion-completa` (último)** | **Solución completa.** Eliminar el contacto si el teléfono llega vacío y enlace *Vaciar* (`?limpiar=1`). | RF-06, RF-07, RF-08 |

## TODO y FIXME

- `// TODO (paso N): …` es una parte **por hacer** e indica en qué paso se resuelve. Al programarla, el TODO se borra.
- `// FIXME (paso N): …` señala algo que **ya funciona, pero mal o a medias**, y el paso en que se arregla. Son los
  defectos provisionales que aparecen al avanzar de lo sencillo a lo completo:

  | Aparece en | FIXME | Se arregla en |
  |---|---|---|
  | paso3 | La agenda empieza vacía en cada petición: solo se ve el último contacto (HTTP no tiene estado). | paso4 |
  | paso3, paso4 | Con el teléfono vacío se guarda un contacto sin teléfono. | paso5 |

- `// FIXME (pendiente): …` marca defectos de la **solución de referencia** que se dejan sin corregir (el último paso
  es esa solución, sin cambios en el código). Sirven como ejercicio de revisión:

  | FIXME (pendiente) | Cómo comprobarlo |
  |---|---|
  | `unset($almacenAgenda)` destruye la variable y el `count()` posterior da un error fatal en PHP 8. No se nota porque el enlace *Vaciar* no envía la agenda (es eso lo que la vacía) y esa línea no llega a ejecutarse. | Abrir `index.php?limpiar=1&agenda[Juan]=555` |
  | El nombre y el teléfono se escriben sin `htmlspecialchars()` (RNF-10), en la lista y en los campos `hidden`. | Añadir el contacto `O'Brien` y después otro: el primero se estropea |
  | Sobra un `</ul>` y hay un `<a>` dentro de un `<button>`. | Validador de HTML |

Con la extensión **Todo Tree** de Visual Studio Code se ven todos en el panel lateral.

## El autómata de la solución

En [`DIAGRAMA ESTADO-TRANSICION/Automata-Agenda.html`](DIAGRAMA%20ESTADO-TRANSICION/Automata-Agenda.html) está el
diagrama de estado-transición ampliado de `paso5-solucion-completa`, con sus tablas (descriptores, eventos-estados,
transiciones y comprobación de predicados). Está hecho del código al autómata, para revisar la solución: explica
los `FIXME (pendiente)` de más abajo y dice qué caminos no recorren los casos de prueba.

## Cómo probar y comparar

`php -S localhost:8000` en la carpeta de un paso y abrir http://localhost:8000 (o copiarla a `htdocs`).

Para ver qué cambia de un paso a otro: en Visual Studio Code, seleccionar los dos ficheros, botón derecho y
*Comparar seleccionados*; o en una terminal abierta en esta carpeta:

    git diff --no-index "PASO A PASO/paso3-nuevo-contacto" "PASO A PASO/paso4-estado-hidden"

## Casos de prueba

| Caso | Entrada | Resultado esperado | Desde |
|---|---|---|---|
| 1 | Nombre vacío (o solo espacios), teléfono `123` | Aviso "el Nombre es obligatorio"; la agenda no cambia | paso3 |
| 2 | `ana perez` / `986111222` | Aparece *Ana Perez — 986111222* | paso3 |
| 3 | Después del caso 2, `brais` / `600` | Están los dos contactos | paso4 |
| 4 | `ana perez` / `111` | Cambia el teléfono de Ana Perez; sigue habiendo dos contactos | paso4 |
| 5 | `brais` y teléfono vacío | Desaparece Brais | paso5 |
| 6 | Pulsar *Vaciar* | Agenda vacía y el botón *Vaciar* desaparece | paso5 |

## Propuesta de uso en clase

1. Entregar `paso2-todos` con el enunciado y pedir que se completen los TODO en orden.
2. A quien se atasque, darle el paso siguiente como pista.
3. Revisar en clase el diff **paso3 → paso4**: son muy pocas líneas y contienen la idea central de la tarea (el
   estado viaja en la página porque HTTP no lo guarda).
4. Proponer como ampliación corregir los `FIXME (pendiente)`.
