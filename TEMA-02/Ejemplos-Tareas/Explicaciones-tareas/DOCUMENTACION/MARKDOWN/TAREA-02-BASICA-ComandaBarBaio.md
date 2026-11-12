# Comanda del Bar Baio (TAREA-02, nivel básico): cómo se llega a la solución paso a paso

**Material de la tarea:** [enunciado](../TAREA-02-BASICA-ComandaBarBaio/ENUNCIADO.md) ·
[requisitos](../TAREA-02-BASICA-ComandaBarBaio/Requisitos.md) ·
[solución](../TAREA-02-BASICA-ComandaBarBaio/SOLUCION-01/) ·
[paso a paso](../TAREA-02-BASICA-ComandaBarBaio/SOLUCION-01/PASO%20A%20PASO/) ·
apuntes de referencia: [El proceso de desarrollo de software](../COMUN/apuntes-proceso-desarrollo-software/Apuntes-Proceso-Desarrollo-Software.html)

> **Caso práctico**
>
> **Juan** le pasa a **María** el encargo del Bar Baio: una página para la tableta de la barra que saque el ticket
> de cada mesa. María ya está calculando mentalmente el 3x2 de los cafés cuando **Carlos** se asoma a su pantalla:
> "Empieza por lo aburrido. En mi primera aplicación el camarero dejaba campos vacíos, escribía `-1` o `abc`, y la
> cuenta salía rota". María toma nota: antes del ticket hay que tener un formulario que **nunca** se fíe de lo que
> llega, porque todo lo que viene de un formulario es texto, aunque parezca un número.

Este documento no repite el código de la solución: explica **por qué** se construye en ese orden, qué decisión se
toma en cada paso y cómo se comprueba antes de pasar al siguiente. Sigue las fases de los apuntes: análisis,
diseño, codificación incremental, pruebas y refactorización.

**Cómo usarlo.** Lee primero el enunciado e intenta hacer tú el análisis (apartados 1 y 2) antes de leer el
nuestro. Después, para cada paso del apartado 3, abre la carpeta del paso y compárala con la anterior:

```bash
git diff --no-index "PASO A PASO/paso1" "PASO A PASO/paso2"
```

(desde la carpeta `SOLUCION-01`; en VS Code: seleccionar los dos ficheros, botón derecho, *Comparar seleccionados*).

## 1.- Análisis: ¿qué hay que hacer?

### 1.1.- Los requisitos, en pocas palabras

El enunciado tiene 8 requisitos (R1…R8) que [Requisitos.md](../TAREA-02-BASICA-ComandaBarBaio/Requisitos.md)
convierte en 8 requisitos funcionales comprobables. Agrupados por lo que significan para el programador:

| Grupo | Requisitos | Qué implica |
|---|---|---|
| La carta | R1 · RF-01, RNF-04 | Un fichero que **devuelve** un array; el formulario y el ticket se generan a partir de él. |
| Recoger la comanda | R2, R3 · RF-02, RF-03 | Un campo `cantidad[codigo]` por producto, formulario **pegajoso** y **todos** los errores a la vez. |
| Calcular el ticket | R4, R5, R6 · RF-04…RF-06 | Líneas agrupadas por categoría, 3x2 en cafés, descuento de socio, total y desglose del IVA. |
| Cerrar y no romperse | R7, R8 · RF-07, RF-08 | Nota al pie, *Nueva comanda*, salida escapada y ningún aviso de PHP con ninguna entrada. |

Los requisitos no funcionales que más condicionan el código son tres: **sin estado** (RNF-03: ni `$_SESSION` ni
ficheros; cada envío trae la comanda completa), **los cálculos en funciones** (RNF-05: el HTML no calcula) y
**los límites como constantes** (RNF-06: mesas, unidades, longitud de la nota, IVA y promociones).

### 1.2.- Lo que el enunciado no dice (y hay que decidir)

Al leer con calma aparecen preguntas. Conviene escribir la respuesta **antes** de programar:

| Pregunta | Decisión tomada en la solución |
|---|---|
| ¿Un campo de cantidad vacío es un error? | No: cuenta como 0 (lo dice R3). Se convierte en `'0'` antes de validarlo. |
| Si hay una cantidad mal escrita y ninguna bien, ¿se dice también "La comanda está vacía"? | No: sobra, el problema es la cantidad mal escrita. Una variable `$hayMal` lo evita. |
| ¿Sobre qué se calcula el 10 % del socio? | Sobre lo que queda **después** del 3x2 (R5). Con la comanda del ejemplo son 1,95 € y no 2,08 €. |
| ¿Cómo se calcula el IVA si los precios ya lo incluyen? | Base = total / 1,10 redondeada, e IVA = total − base. Así base + IVA da **exactamente** el total. |
| ¿Cuándo se redondea? | En cada importe: línea, subtotal, suma, promoción, descuento, total y base (`round(..., 2)`). |
| ¿Qué pasa con `cantidad[inventado]=5`? | Se ignora: se recorre **la carta**, no lo que llega. |
| ¿Qué hace *Nueva comanda*? | Es un enlace a `index.php` (una petición GET): el formulario sale vacío porque no se envía nada. |

> **Recomendación**
>
> Si algo no está claro y no puedes preguntar, **apunta la decisión** (en un comentario o en el README). Así quien
> corrija sabrá que no es un olvido, y tú no cambiarás de criterio a mitad de la tarea.

### 1.3.- Entradas y salidas: los casos de prueba desde el principio

Los criterios de aceptación de `Requisitos.md` son ejemplos concretos de entrada y salida. Escríbelos en una tabla
antes de programar: serán las pruebas de cada paso.

| Caso | Entrada | Salida esperada |
|---|---|---|
| 1 | Abrir la página | Campo `cantidad[cafe]` y el texto "Empanada de zamburiñas (3,20 €)" |
| 2 | *Calcular* sin escribir nada | "La mesa debe ser un número entero entre 1 y 12." y "La comanda está vacía: pide al menos un producto." |
| 3 | Mesa 13, café `x`, zumo 21, nota de 81 caracteres | Cuatro errores (entre ellos "«Café» … (has escrito «x»)" y "(tiene 81)"), **no** sale "La comanda está vacía" y los campos conservan `13` y `x` |
| 4 | `mesa[]=1` y `cantidad=abc` | Errores ("Las cantidades no tienen el formato correcto.") **sin avisos de PHP** |
| 5 | Mesa 7 y café 2, los dos con espacios delante y detrás; zumo 1 | "Ticket de la mesa 7", "Subtotal Bebidas", total 5,10 €, base 4,64 €, IVA 0,46 €; no sale Raciones |
| 6 | Mesa 4, 4 cafés, 2 tostadas, 1 pulpo, socio | Suma 20,80 €; "(1 gratis)" −1,30 €; socio −1,95 €; TOTAL 17,55 €; base 15,95 €; IVA 1,60 €; casilla `checked` |
| 7 | Mesa 12, 6 cafés y 20 pulpos | Pulpo 240,00 €, "(2 gratis)" −2,60 € y total 245,20 € |
| 8 | Nota "Sin sal" | "Nota para la cocina: Sin sal" |
| 9 | Nota `<script>alert(1)</script>` | En el código fuente aparece `&lt;script&gt;` y nunca `<script>alert` |

Los casos 4 y 9 son **maliciosos**: el 4 no se puede provocar con el formulario normal, hay que editar el HTML con
F12 o enviar la petición con `curl`. Que estén en la lista desde el principio obliga a pensar en ellos al escribir
la validación, no después.

### 1.4.- Los datos: qué se guarda y dónde

Como **HTTP no tiene estado**, cada clic en *Calcular el ticket* es una petición nueva y el script empieza desde
cero. En esta tarea eso no es un problema, porque **no hay nada que recordar**: el formulario envía la comanda
completa cada vez, y el formulario pegajoso se limita a devolverla escrita. Es el caso más sencillo posible, y
conviene darse cuenta de ello antes de buscar dónde "guardar" nada.

| Dato | Estructura | Dónde vive | ¿Por qué? |
|---|---|---|---|
| Carta | array `código => [nombre, categoria, precio]` | `carta.php` (`return [...]`) | Datos fijos, separados del código (RNF-04) |
| Mesa | int (o el texto escrito, si no vale) | campo `mesa` | Llega en cada envío |
| Cantidades | array `código => int` (o el texto escrito, si no vale) | campos `cantidad[codigo]` | PHP los recibe todos juntos en `$_POST['cantidad']` |
| Socio | bool | casilla `socio` | Sin marcar **no se envía**: `isset()` |
| Nota | string | campo `nota` | Opcional, como mucho 80 caracteres |
| Líneas, subtotales, promoción, total, IVA | — | **se calculan** | Dependen solo de las cantidades y la carta |
| Errores | array | variable | Solo duran la petición |

La comanda queda como un array asociativo:

```php
['mesa' => 4, 'cantidades' => ['cafe' => 4, 'colacao' => 0, /* … */ 'tostada' => 2, /* … */ 'pulpo' => 1, /* … */], 'socio' => true, 'nota' => '']
```

> **Debes conocer**
>
> **Recorre la carta, no lo que llega.** Si el bucle fuera `foreach ($_POST['cantidad'] as ...)`, un campo inventado
> (`cantidad[inventado]=5`) entraría en la comanda, y un producto cuyo campo se hubiera borrado con F12 faltaría.
> Recorriendo `$carta`, la comanda tiene **siempre una cantidad por cada producto de la carta**, ni una más ni una
> menos. Esa garantía es la que permite, en los pasos 3 y 4, escribir `$cantidades[$codigo]` sin `??` y sin miedo a
> un aviso de PHP.

## 2.- Diseño: ¿cómo se organiza?

### 2.1.- Divide y vencerás: ficheros y funciones

Tres ficheros con una responsabilidad cada uno: `carta.php` (datos), `funciones.php` (lógica, sin HTML) e
`index.php` (decide qué hacer y muestra la página). `index.php` sigue la estructura de siempre de los apuntes:

```text
1. Inicializar          -> cargar funciones y carta; comanda vacía, sin errores y sin ticket
2. Recibir y validar    -> ¿se ha pulsado "Calcular"? validarComanda() devuelve [comanda, errores]
3. Procesar             -> si no hay errores: líneas por categoría, suma y totales
4. Preparar la vista    -> agrupar la carta por categorías para dibujar el formulario
5. Mostrar la página    -> errores, formulario pegajoso y, si lo hay, el ticket (sin lógica, solo variables)
```

Cada tarea del paso 3 es una función pequeña que **recibe** lo que necesita y **devuelve** un resultado (sin
`echo`). El paso 1 ya escribe sus firmas como TODO:

| Función | Responsabilidad | Paso |
|---|---|---|
| `e()` | Escapar para el HTML | 2 |
| `validarComanda()` | `$_POST` y la carta → `[comanda, errores]`, con todos los errores a la vez | 2 |
| `euros()` | `1234.5` → `"1.234,50 €"` | 3 |
| `lineasTicket()` | Cantidades y carta → líneas agrupadas por categoría | 3 |
| `subtotal()` | Suma de los importes de unas líneas | 3 |
| `calcularTotales()` | Promoción 3x2, descuento de socio, total, base e IVA | 4 |

> **Recomendación**
>
> Fíjate en que `validarComanda()` devuelve **dos** cosas a la vez, la comanda y los errores, en un array que se
> desempaqueta así: `[$comanda, $errores] = validarComanda($_POST, $carta);`. Es más claro que devolver los errores
> y modificar la comanda por referencia, y deja la función fácil de probar: entra un array y sale un array.

### 2.2.- Estados de la página

La página es muy sencilla y tiene tres estados (*evento [condición] / acción*):

| Desde | Evento [condición] | Acción | Hasta |
|---|---|---|---|
| (cualquiera) | abrir la página o *Nueva comanda* (GET) | comanda vacía | Formulario vacío |
| Formulario | *Calcular el ticket* [hay errores] | mostrar **todos** los errores; formulario pegajoso | Formulario con errores |
| Formulario | *Calcular el ticket* [sin errores] | líneas, suma y totales | Formulario + ticket |

Dibujarlo deja claro algo que el enunciado da por supuesto: el ticket se muestra **debajo** del formulario (que
sigue relleno), así que el camarero puede corregir una cantidad y volver a calcular sin escribir de nuevo toda la
comanda.

## 3.- Codificación: la solución, paso a paso

Cada carpeta `pasoN` es la aplicación completa al terminar ese paso, y todas se ejecutan. El orden no es casual:
primero lo que permite **recoger bien** los datos, después lo que permite **ver** el ticket y al final los
**cálculos** que dependen de todo lo anterior.

| Paso | Qué se añade | Requisitos |
|---|---|---|
| 1 | Descomposición: TODOs y esqueleto HTML | — |
| 2 | La carta, constantes, `e()`, `validarComanda()` y el formulario pegajoso | R1, R2, R3, R8 |
| 3 | `euros()`, `lineasTicket()`, `subtotal()` y el ticket hasta la suma | R4, R6 (formato) |
| **4** | **Solución completa:** `calcularTotales()`, la nota y *Nueva comanda* | R5, R6, R7 |

### 3.1.- Paso 1: descomponer el problema en TODOs

**Objetivo.** Pasar el diseño del apartado 2 al código, sin programar nada todavía.

**Por qué empezar así.** Aunque la tarea es básica, mezcla validación, agrupación y cálculos de dinero, y es fácil
empezar por el 3x2 (lo más vistoso) y olvidar que antes hace falta una comanda válida. Los TODO son el **plan**
escrito dentro del propio código: en `index.php`, uno por cada parte de la estructura, **anidados** según
dependen unos de otros; en `funciones.php`, la **firma** de cada función con lo que recibe y lo que devuelve:

```php
// TODO: botón "Calcular": validar la comanda
    // TODO: si es correcta, preparar el ticket: líneas por categoría y suma
        // TODO: promoción 3x2, descuento de socio y desglose del IVA
```

```php
// TODO: función calcularTotales(array $comanda, array $carta, float $suma): array -> promoción, descuento, total e IVA
```

La sangría de los TODO de `index.php` ya dice mucho del diseño: los totales solo se calculan si hay ticket, y solo
hay ticket si la comanda es correcta. El HTML también se marca con TODOs (errores, campos, ticket, nota): es la
"maqueta" de esta tarea.

**Decisión clave.** `carta.php` no escribe nada: su único TODO es *devolver la carta*. Y `calcularTotales()` recibe
la `$suma` ya calculada, en lugar de volver a recorrer las líneas: cada función hace **una** cosa.

> **Pruébalo**
>
> Abre la página: sale el título "Bar Baio — Comanda" y el botón *Calcular el ticket*, que todavía no hace nada, y
> **no hay errores de PHP**. Con la extensión *Todo Tree* de VS Code verás la lista de lo que queda por hacer. A
> partir de ahora, cada TODO que se programa se borra.

### 3.2.- Paso 2: la carta, el formulario pegajoso y la validación

**Objetivo.** Generar el formulario a partir de la carta, validar en el servidor todo lo que llega y devolver el
formulario relleno (R1, R2, R3 y la parte de R8 que toca a la entrada).

**Por qué ahora.** Sin una comanda **válida** no hay ticket que calcular, y todos los cálculos de los pasos 3 y 4
darán por hecho que las cantidades son enteros de 0 a 20. Si la validación se dejara para el final, habría que
revisar cada cálculo para ver qué pasa con un `abc`.

**Qué se escribe.**

- `carta.php` **devuelve** el array y `index.php` lo recoge con `$carta = require __DIR__ . '/carta.php';`.
- Las constantes `MESAS`, `MAX_UNIDADES` y `MAX_NOTA`. Las de las promociones y el IVA se dejan para el paso 4:
  cada constante aparece en el paso en que se usa.
- Los valores iniciales de `index.php`: una comanda vacía con `array_fill_keys(array_keys($carta), 0)`, es decir,
  un 0 para cada producto de la carta. Así el formulario se dibuja igual la primera vez que después de enviarlo.
- `validarComanda()`, con el mismo patrón para cada campo: comprobar que es un **string**, quitar los espacios y
  validar con `filter_var(..., FILTER_VALIDATE_INT, ['options' => ['min_range' => ..., 'max_range' => ...]])`:

```php
// validarComanda() (paso 2, funciones.php)
$textoMesa = is_string($datos['mesa'] ?? null) ? trim($datos['mesa']) : '';
$mesa = filter_var($textoMesa, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => MESAS]]);
```

- Para las cantidades, primero se comprueba que `$datos['cantidad']` es un array (si no, "Las cantidades no tienen
  el formato correcto.") y después se recorre **la carta**. Si una cantidad no vale, se guarda **lo escrito** en
  lugar del número (ver el código debajo de esta lista): el formulario pegajoso necesita devolver el `x`, no un 0.
- La nota se mide con `mb_strlen()` y el mensaje dice cuántos caracteres tiene ("(tiene 81)").
- `'socio' => isset($datos['socio'])`: una casilla sin marcar no se envía.
- En la vista, la carta se agrupa por categorías (`$categorias[$producto['categoria']][$codigo] = $producto;`) y
  se dibuja un `<fieldset>` por categoría. Todos los `value` pasan por `e()`, y la casilla lleva `checked` si
  `$comanda['socio']` es verdadero.

```php
// validarComanda() (paso 2, funciones.php)
if ($cantidad === false) {
    $errores[] = "La cantidad de «{$producto['nombre']}» debe ser un entero entre 0 y " . MAX_UNIDADES
        . " (has escrito «{$texto}»).";
    $cantidades[$codigo] = $texto;                   // se guarda lo escrito, para el formulario pegajoso
    $hayMal = true;
}
```

> **Error típico**
>
> Escribir `if (!$cantidad)` en lugar de `if ($cantidad === false)`. `filter_var('0', FILTER_VALIDATE_INT)`
> devuelve el entero `0`, que es *falso* para PHP: con `!` un 0 se tomaría como un error. Lo mismo pasa con
> `empty($texto)`, que da `true` para el texto `'0'`. Por eso se compara con `=== ''` y `=== false`.

> **Para saber más**
>
> ¿Por qué `is_string()` antes de `trim()`? Porque cualquiera puede enviar `mesa[]=1` y entonces `$_POST['mesa']`
> es un **array**. `trim()` de un array lanza un `TypeError` en PHP 8 y la página se rompe. Con
> `is_string($datos['mesa'] ?? null)` el array se trata como un campo vacío y sale el error normal de la mesa.
> El `?? null` evita, además, el aviso *Undefined array key* si el campo no llega.

**Una decisión que el ticket agradecerá.** Si hay errores, `$comanda['cantidades']` puede tener textos (`'x'`)
mezclados con enteros. No es un problema, porque el ticket **solo** se calcula si `$errores === []`, y en ese caso
todas las cantidades son enteros. Por eso, en este paso, una comanda correcta **no muestra nada todavía**: ni
errores ni ticket.

> **Pruébalo**
>
> Casos 1 a 4. Envía el formulario vacío, luego la mesa 13 con un café `x`, un zumo 21 y una nota larga: deben salir
> los cuatro errores y los campos deben conservar lo escrito. Con F12, cambia `name="mesa"` por `name="mesa[]"`:
> error de la mesa, sin avisos. Con el depurador, pon un punto de ruptura en `validarComanda()` y mira cómo llega
> `$datos['cantidad']` y cómo se convierte en `$cantidades`.

### 3.3.- Paso 3: el ticket, agrupado por categorías

**Objetivo.** Con una comanda correcta, mostrar la tabla del ticket: líneas agrupadas por categoría en el orden de
la carta, el subtotal de cada una y la suma (R4), con los importes bien formateados (R6).

**Por qué ahora.** Es lo que se **ve**, y permite comprobar a ojo que las cuentas básicas (unidades × precio) son
correctas antes de añadir descuentos que las complican. Si la suma está mal, el total del paso 4 también lo estará.

**Qué se escribe.**

- `euros()`: `number_format($importe, 2, ',', '.') . ' €'`. Un único sitio para el formato del dinero.
- `lineasTicket()` recorre de nuevo **la carta** (así se respeta su orden) y se salta los productos con cantidad 0.
  Las líneas se agrupan en un array cuya clave es la categoría, de modo que las categorías sin nada pedido
  **ni siquiera existen** en el resultado: no hay que filtrarlas en la vista.
- `subtotal()` suma los importes de un grupo de líneas con `array_sum(array_column($lineas, 'importe'))`.
- En `index.php`, la suma es la de los subtotales; el ticket se guarda en un array `['grupos' => ..., 'suma' => ...]`.

```php
// lineasTicket() (paso 3, funciones.php)
$importe = round($cantidad * $producto['precio'], 2);
$grupos[$producto['categoria']][] = [
```

> **Debes conocer**
>
> **Redondea cada importe a céntimos.** Los `float` no representan exactamente la mayoría de los decimales: 0,1 +
> 0,2 no es exactamente 0,3. En un ticket, cada línea es un importe en céntimos y debe guardarse así; si se suman
> los valores sin redondear, el total puede diferir en un céntimo de la suma de lo que se ve impreso. Por eso hay un
> `round(..., 2)` en el importe de cada línea, en cada subtotal y en la suma. (Una ampliación del enunciado propone
> hacer los cálculos con céntimos enteros, que evita el problema de raíz.)

**Lo que queda pendiente.** La tabla termina en la fila *Suma*; justo debajo queda el TODO de la promoción, el
descuento, el total y el IVA, y en el formulario el de *Nueva comanda*. Es desarrollo incremental: el ticket ya
funciona y se puede probar, aunque todavía no esté completo.

> **Pruébalo**
>
> Caso 5: mesa 7 con espacios alrededor, dos cafés (también con espacios) y un zumo. Debe salir "Ticket de la mesa
> 7", la categoría Bebidas con su subtotal y la suma 5,10 €, y **no** debe salir Raciones. Pide 20 pulpos: la línea
> debe decir 240,00 € (dos decimales y coma). Comprueba que, si añades una línea a `carta.php`, el producto aparece
> en el formulario y en el ticket sin tocar nada más (R1).

### 3.4.- Paso 4 (solución completa): promociones, total e IVA

**Objetivo.** Calcular el 3x2 en cafés, el descuento de socio, el total y el desglose del IVA (R5, R6); añadir la
nota al pie y el enlace *Nueva comanda* (R7).

**Por qué al final.** Todos estos importes se calculan a partir de la **suma** del paso 3, y el orden entre ellos
importa: primero la promoción, después el descuento sobre lo que queda, después el total y, por último, la base y
el IVA a partir del total. Es una cadena, y cada eslabón necesita el anterior.

**Qué se escribe.**

- Las constantes que faltaban: `PROMO_PRODUCTO = 'cafe'`, `PROMO_CADA = 3`, `DESCUENTO_SOCIO = 10` e `IVA = 10`.
- `calcularTotales()`, que devuelve un array con `suma`, `gratis`, `promocion`, `descuento`, `total`, `base` e
  `iva`. Las cuatro líneas clave:

```php
// calcularTotales() (paso 4, funciones.php)
$gratis = intdiv($comanda['cantidades'][PROMO_PRODUCTO], PROMO_CADA);
$descuento = $comanda['socio'] ? round(($suma - $promocion) * DESCUENTO_SOCIO / 100, 2) : 0.0;
$base = round($total / (1 + IVA / 100), 2);
$cuotaIva = round($total - $base, 2);
```

- En la vista, la línea de la promoción solo sale si `$totales['gratis'] > 0`, y la del socio solo si
  `$comanda['socio']`. Las dos se escriben en negativo (−1,30 €).
- La nota se muestra con `e()` solo si no está vacía, y *Nueva comanda* es un `<a href="index.php">`.

> **Debes conocer**
>
> **El IVA "incluido" se calcula hacia atrás.** Los precios de la carta ya llevan el IVA, así que el total
> (17,55 €) es la base **más** el 10 % de la base: base = 17,55 / 1,10 = 15,95 €. El error frecuente es calcular el
> IVA como el 10 % del total (1,76 €), que daría una base de 15,79 €. Y fíjate en que el IVA no se calcula aparte
> con otra división, sino como **total − base**: así los dos importes, redondeados, suman siempre el total del
> ticket, sin un céntimo de diferencia.

> **Error típico**
>
> Aplicar el descuento de socio sobre la suma: 20,80 € × 10 % = 2,08 €, y el total saldría 17,42 € en lugar de
> 17,55 €. R5 dice "sobre lo que queda después de la promoción": 19,50 € × 10 % = 1,95 €. Cuando hay varios
> descuentos, **el orden** es parte del requisito.

**Cada decisión del paso 2 simplifica este.** `calcularTotales()` lee `$comanda['cantidades'][PROMO_PRODUCTO]`
directamente, sin `??`: puede hacerlo porque `validarComanda()` recorre la carta y garantiza que hay una cantidad
(entera) para cada producto, también para el café aunque no se haya pedido ninguno.

> **Pruébalo**
>
> Casos 6 a 9. La comanda del enunciado (mesa 4, 4 cafés, 2 tostadas, 1 pulpo, socio) debe dar exactamente la tabla
> del ejemplo: 20,80; −1,30; −1,95; 17,55; 15,95 y 1,60. Con 6 cafés, "(2 gratis)". Con 2 cafés y sin socio, no
> debe salir ninguna línea de promoción. Escribe `<script>alert(1)</script>` en la nota y busca en el código fuente
> (Ctrl+U) `&lt;script&gt;`. Pulsa *Nueva comanda*: el formulario debe salir vacío.

## 4.- Pruebas finales

Con la solución completa hay que volver a pasar **todos** los casos del apartado 1.3, no solo los del último paso:
el paso 4 añade filas a la tabla del ticket y podría haber estropeado algo que antes funcionaba. Una lista de
comprobación para esta tarea:

- [ ] Casos correctos: el ejemplo del enunciado, una comanda sin cafés, una comanda de socio sin cafés.
- [ ] Casos límite: mesa 1 y mesa 12; cantidad 20 y 21; exactamente 3 cafés (1 gratis) y 2 cafés (ninguno); una
      nota de 80 caracteres con tildes (vale) y otra de 81 (no vale).
- [ ] Formulario pegajoso: tras un error se conservan la mesa, cada cantidad (también las mal escritas), la casilla
      y la nota.
- [ ] Maliciosos: `mesa[]=1`, `cantidad=abc`, `cantidad[cafe][]=1`, `cantidad[inventado]=5`, HTML en la nota y en
      las cantidades (el mensaje "has escrito «…»" también debe salir escapado).
- [ ] Ningún aviso de PHP con `error_reporting(E_ALL)` y base + IVA = total en todos los tickets.

## 5.- Refactorización y mejoras posibles

La solución ya está organizada en funciones cortas, así que la refactorización se hizo sobre la marcha. Aun así,
quedan detalles que se podrían mejorar sin cambiar el comportamiento, buenos para practicar:

- En el formulario, el precio de cada producto se escribe con `number_format($producto['precio'], 2, ',', '.')` y
  un `€` a mano. Tiene una explicación histórica: el formulario se escribió en el paso 2, cuando `euros()` aún no
  existía, y en el paso 3 no se volvió atrás. Ahora se puede sustituir por `euros($producto['precio'])`: es la
  refactorización típica que aparece al **releer** el código terminado.
- Los subtotales se calculan dos veces: en `index.php` para obtener la suma y otra vez en la vista con
  `subtotal($lineas)`. Se podrían guardar en el ticket (`$ticket['subtotales'][$categoria]`), y así la vista no
  calcularía nada, como pide RNF-05.
- `calcularTotales()` supone que el producto de la promoción está en la carta. Si alguien quita el café de
  `carta.php`, `$comanda['cantidades'][PROMO_PRODUCTO]` daría un aviso. Una comprobación con `isset()` (o
  `?? 0`) haría la función robusta frente a cambios de la carta. Del mismo modo, el texto "Promoción 3x2 en
  cafés" de la vista está escrito a mano aunque el producto y el 3 sean constantes.
- Las ampliaciones del enunciado (menú del día, céntimos enteros, forma de pago, ticket de 40 columnas) son la
  siguiente **vuelta** del proceso: cada una empieza otra vez por el análisis. Por ejemplo, el menú del día obliga a
  decidir si se aplica antes o después del 3x2, igual que el descuento de socio.

## 6.- Errores típicos en esta tarea

| Error | Consecuencia | Cómo evitarlo |
|---|---|---|
| Recorrer `$_POST['cantidad']` en lugar de la carta | Entran productos inventados y faltan los borrados | `foreach ($carta as $codigo => $producto)` |
| `trim($_POST['mesa'])` sin comprobar el tipo | `TypeError` con `mesa[]=1` | `is_string($datos['mesa'] ?? null)` |
| `if (!$cantidad)` o `empty($texto)` | El 0 se toma como error | `=== false` y `=== ''` |
| `$_POST['socio'] == 'on'` sin `isset` | Aviso *Undefined array key* si no se marca | `isset($datos['socio'])` |
| `strlen()` para la nota | Una nota con tildes "mide" más de lo que es | `mb_strlen()` |
| Mostrar "La comanda está vacía" junto a una cantidad mal escrita | Mensaje confuso | La variable `$hayMal` |
| Descuento de socio sobre la suma | 17,42 € en lugar de 17,55 € | Primero la promoción, después el descuento |
| IVA = total × 10 % | Base e IVA incorrectos | Base = total / 1,10; IVA = total − base |
| No redondear cada importe | Céntimos que no cuadran | `round(..., 2)` en cada línea y subtotal |
| Escribir los errores sin `e()` | El mensaje "has escrito «…»" ejecuta HTML | **Todo** lo que se escribe en el HTML se escapa |

## 7.- Autoevaluación

> **Autoevaluación**
>
> - ¿Por qué `validarComanda()` recorre la carta y no `$_POST['cantidad']`? **Porque así se ignoran los códigos
>   inventados y la comanda tiene siempre una cantidad para cada producto, lo que permite usar
>   `$cantidades[$codigo]` sin `??` en los pasos siguientes.**
> - Si el camarero escribe `x` en el café, ¿qué se guarda en `$comanda['cantidades']['cafe']`? **El texto `'x'`,
>   para que el formulario pegajoso lo devuelva; el ticket no se calcula porque hay errores.**
> - ¿Por qué no se puede usar `if (!$cantidad)` para detectar un error? **Porque `filter_var()` devuelve `0` para
>   una cantidad válida de 0 unidades, y `0` es falso; hay que comparar con `=== false`.**
> - ¿Cómo se sabe si la casilla de socio estaba marcada? **Con `isset($_POST['socio'])`: una casilla sin marcar no
>   se envía.**
> - Con un total de 17,55 €, ¿cuánto es la base imponible y por qué no es 17,55 × 0,90? **15,95 €: el IVA está
>   incluido, así que base = total / 1,10; e IVA = total − base = 1,60 €.**
> - ¿Por qué esta tarea no necesita campos ocultos ni ninguna otra forma de guardar estado? **Porque cada envío
>   trae la comanda completa: no hay nada que recordar entre una petición y la siguiente.**
