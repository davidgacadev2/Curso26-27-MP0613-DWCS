# Tema 2: explicaciones de las tareas paso a paso

Para cada tarea de `EJEMPLOS TAREA-02` (copiada en `DOCUMENTACION`, una carpeta por tarea), una explicación **motivada** de cómo se
avanza desde el enunciado hasta la solución final: no repite el código, sino que cuenta **por qué** se construye en
ese orden, qué decisión se toma en cada paso, qué se deja provisional y cómo se comprueba antes de seguir.

Todas siguen las fases de los apuntes
[El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html):

1. **Análisis**: requisitos, lo que el enunciado no dice, entradas y salidas (los casos de prueba) y los datos: qué se
   guarda y dónde (HTTP no tiene estado).
2. **Diseño**: ficheros y funciones (divide y vencerás) y estados de la página.
3. **Codificación incremental**: un apartado por cada carpeta `pasoN` (objetivo, por qué ahora, qué se escribe y
   cuadro *Pruébalo*).
4. **Pruebas finales**, **refactorización y mejoras**, **errores típicos** y **autoevaluación**.

Las explicaciones en HTML (`.html`, con el formato de los apuntes del módulo) están en la raíz de esta
carpeta y sus versiones en Markdown (`.md`), en `DOCUMENTACION/MARKDOWN`. Todo lo que citan está copiado
en `DOCUMENTACION`: lo común a todas las tareas (apuntes, método de los autómatas) en `DOCUMENTACION/COMUN`
y lo específico de cada tarea (enunciado, requisitos, soluciones, pasos y diagramas) en
`DOCUMENTACION/<tarea>`. Todos los enlaces son relativos, así que la carpeta se puede mover a cualquier
sitio sin romperlos.

## Tareas de ejemplo del tema (soluciones de referencia)

Explican la descomposición en pasos de las soluciones de referencia de
`Material referencia/tema-02/CODIGO SOLUCIONES/Ejemplos-Tareas`, incluidos sus defectos pendientes (`FIXME`).

| Tarea | Pasos | Explicación |
|---|---|---|
| Agenda de contactos (02A) | 5 | [Markdown](EJEMPLO-TAREA-02A-Agenda.md) · [HTML](../../EJEMPLO-TAREA-02A-Agenda.html) |
| Tablero con letras ocultas (02B), tres soluciones | 7 | [Markdown](EJEMPLO-TAREA-02B-TableroLetras.md) · [HTML](../../EJEMPLO-TAREA-02B-TableroLetras.html) |

## Nuevas propuestas de tarea

| Tarea | Nivel | Pasos | Explicación |
|---|---|---|---|
| Calendario | Básico | 4 | [Markdown](TAREA-02-BASICA-Calendario.md) · [HTML](../../TAREA-02-BASICA-Calendario.html) |
| Comanda del Bar Baio | Básico | 4 | [Markdown](TAREA-02-BASICA-ComandaBarBaio.md) · [HTML](../../TAREA-02-BASICA-ComandaBarBaio.html) |
| Cuaderno de notas | Medio | 4 | [Markdown](TAREA-02-MEDIA-CuadernoNotas.md) · [HTML](../../TAREA-02-MEDIA-CuadernoNotas.html) |
| Memoria de parejas | Medio | 6 | [Markdown](TAREA-02-MEDIA-MemoriaParejas.md) · [HTML](../../TAREA-02-MEDIA-MemoriaParejas.html) |
| El ahorcado | Medio | 6 | [Markdown](TAREA-02-MEDIA-Ahorcado.md) · [HTML](../../TAREA-02-MEDIA-Ahorcado.html) |
| Buscaminas | Avanzado | 5 | [Markdown](TAREA-02-AVANZADA-Buscaminas.md) · [HTML](../../TAREA-02-AVANZADA-Buscaminas.html) |
| Cuatro en raya | Avanzado | 6 | [Markdown](TAREA-02-AVANZADA-CuatroEnRaya.md) · [HTML](../../TAREA-02-AVANZADA-CuatroEnRaya.html) |
| Código secreto | Avanzado (con autómata) | 6 | [Markdown](TAREA-02-AVANZADA-CodigoSecreto.md) · [HTML](../../TAREA-02-AVANZADA-CodigoSecreto.html) |
| Ascensor | Avanzado (con autómata) | 6 | [Markdown](TAREA-02-AVANZADA-Ascensor.md) · [HTML](../../TAREA-02-AVANZADA-Ascensor.html) |
| Siete y media | Avanzado (con autómata) | 6 | [Markdown](TAREA-02-AVANZADA-SieteYMedia.md) · [HTML](../../TAREA-02-AVANZADA-SieteYMedia.html) |

## Cómo usarlas

- **Alumnado**: lee el enunciado e intenta primero el análisis y el diseño por tu cuenta; después compara con los
  apartados 1 y 2 de la explicación. Para cada paso, abre la carpeta del paso y compárala con la anterior mientras
  lees su apartado (`git diff --no-index "paso2" "paso3"` o, en VS Code, *Comparar seleccionados*).
- **Profesorado**: los cuadros *Pruébalo* son los casos que conviene enseñar en clase en cada paso, y los apartados
  de errores típicos y autoevaluación sirven para la corrección.

