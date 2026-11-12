# TAREA-02 (nivel BÁSICO): Comanda del Bar Baio

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

## Caso práctico

El **Bar Baio** quiere dejar la libreta de las comandas y usar una tableta en la barra. Le pide a **BK
Programación** una página sencilla: el camarero escribe el número de la mesa y cuántas unidades se piden de cada
producto, y la página saca el ticket con el total, las promociones del bar y el desglose del IVA que exige
Hacienda.

**Juan** se lo encarga a **María**. **Carlos** le avisa de lo que más le costó en su primera aplicación: los
datos del formulario llegan siempre como texto, y un campo vacío, un `-1` o un `abc` no pueden romper la cuenta.

## Enunciado

Programa en PHP una aplicación web en **una única página** (`index.php`) que se envía a sí misma por **POST**. La
carta del bar estará en un fichero aparte, `carta.php`, que **devuelve un array** con `return`, y los cálculos en
funciones (en `funciones.php` si quieres). No hace falta recordar nada entre peticiones: **no uses `$_SESSION`**,
bases de datos ni ficheros.

### Parte 1: la comanda

- **R1. La carta.** `carta.php` devuelve un array asociativo cuya clave es el código del producto y cuyo valor
  es otro array con el `nombre`, la `categoria` y el `precio` (con el IVA incluido). Como mínimo:

  | Código | Producto | Categoría | Precio |
  |---|---|---|---:|
  | `cafe` | Café | Bebidas | 1,30 € |
  | `colacao` | Cola Cao | Bebidas | 1,60 € |
  | `zumo` | Zumo de naranja | Bebidas | 2,50 € |
  | `agua` | Agua (50 cl) | Bebidas | 1,20 € |
  | `tostada` | Tostada con tomate | Desayunos | 1,80 € |
  | `croissant` | Croissant | Desayunos | 1,60 € |
  | `tortilla` | Pincho de tortilla | Desayunos | 2,50 € |
  | `pulpo` | Ración de pulpo | Raciones | 12,00 € |
  | `zorza` | Zorza con patatas | Raciones | 8,50 € |
  | `empanada` | Empanada de zamburiñas | Raciones | 3,20 € |

  Si se añade un producto a la carta, debe aparecer en el formulario y en el ticket **sin tocar nada más**.
- **R2. Formulario.** Pide el número de **mesa**, la **cantidad** de cada producto (un campo por producto,
  agrupados por categoría y con el precio a la vista), una casilla **Socio del club Baio** y una **nota para la
  cocina** opcional. Los campos de cantidad se llaman `cantidad[codigo]`, para recibirlos todos en un array. El
  formulario es **pegajoso**: después de enviarlo conserva todo lo escrito, también la casilla.
- **R3. Validación.** Se comprueba en el servidor y se muestran **todos** los errores a la vez:
  - la mesa es un entero de 1 a 12;
  - cada cantidad es un entero de 0 a 20 (un campo vacío cuenta como 0); el mensaje dice qué producto está mal y
    qué se ha escrito: *La cantidad de «Café» debe ser un entero entre 0 y 20 (has escrito «x»).*;
  - la comanda tiene al menos un producto (*La comanda está vacía*), salvo que ya haya cantidades mal escritas;
  - la nota tiene como mucho 80 caracteres (contando bien las tildes).

  Se quitan los espacios de los extremos de todos los campos. Lo que no sea de la carta (`cantidad[inventado]`)
  se ignora.

### Parte 2: el ticket

- **R4. Líneas del ticket.** Con una comanda correcta aparece, debajo del formulario, el ticket de la mesa: una
  tabla con las unidades, el producto, el precio y el importe de cada producto pedido, **agrupados por
  categoría** en el orden de la carta, con el **subtotal** de cada categoría y la **suma**. Las categorías sin
  nada pedido no salen.
- **R5. Promociones.**
  - **3x2 en cafés**: por cada 3 cafés, uno es gratis (con 4 cafés, 1 gratis; con 6, 2).
  - **Socios**: un 10 % de descuento sobre lo que queda después de la promoción.

  Cada promoción aplicada sale en su propia línea, en negativo.
- **R6. Total e IVA.** El total lleva el IVA incluido (10 % en hostelería). Debajo se desglosa: **base
  imponible** = total / 1,10 y **IVA** = total − base, redondeados a céntimos. Todos los importes se escriben con
  dos decimales, coma decimal y el símbolo del euro (`1.234,50 €`).
- **R7. Nota y nueva comanda.** Si hay nota, sale al pie del ticket. Un enlace *Nueva comanda* vuelve al
  formulario vacío.
- **R8. Seguridad.** Nada de lo que escriba el usuario se muestra sin escapar, y ninguna entrada (`mesa[]=1`,
  `cantidad=abc`, una nota con `<script>`) produce avisos de PHP.

Con la mesa 4, 4 cafés, 2 tostadas y 1 ración de pulpo, de un socio, el ticket es:

| | |
|---|---:|
| Suma | 20,80 € |
| Promoción 3x2 en cafés (1 gratis) | −1,30 € |
| Descuento de socio (10 %) | −1,95 € |
| **TOTAL (IVA incluido)** | **17,55 €** |
| Base imponible | 15,95 € |
| IVA (10 %) | 1,60 € |

### Pistas

- `$carta = require 'carta.php';` guarda en `$carta` lo que devuelve el `return` del fichero.
- `name="cantidad[cafe]"` hace que `$_POST['cantidad']` sea un array `['cafe' => '4', ...]`. Recorre **la carta**,
  no lo que llega: así lo que no es de la carta se ignora solo.
- `filter_var($texto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 20]])` devuelve el
  entero o `false`. Comprueba antes con `is_string()` que no te han enviado un array.
- Una casilla sin marcar **no se envía**: `isset($_POST['socio'])`.
- `intdiv(4, 3)` es 1. `round($x, 2)` redondea a céntimos y `number_format($x, 2, ',', '.')` escribe `1.234,50`.
- `mb_strlen('Sin cebolla, ¡gracias!')` cuenta caracteres; `strlen()` cuenta bytes (las tildes ocupan dos).

### Ampliaciones opcionales

- Un menú del día (bebida + desayuno a precio cerrado) que se aplique automáticamente.
- Calcular con **céntimos enteros** en lugar de `float` y comparar los resultados.
- Pedir la forma de pago y, en efectivo, lo entregado y el cambio.
- Mostrar el ticket con un formato de "impresora" de 40 columnas dentro de un `<pre>` (`str_pad`).

## Ejemplo de criterios de puntuación

| Criterio | Puntos |
|---|---:|
| 1. La carta en un fichero que devuelve un array y se recoge con `require` (R1) | 0,75 |
| 2. Formulario generado a partir de la carta, agrupado por categorías y pegajoso (R2) | 1,50 |
| 3. Validación completa con todos los mensajes a la vez (R3) | 2,00 |
| 4. Líneas del ticket agrupadas por categoría con subtotales y suma (R4) | 1,75 |
| 5. Promoción 3x2 y descuento de socio (R5) | 1,25 |
| 6. Total, base imponible e IVA, con los importes bien formateados (R6) | 1,00 |
| 7. Nota y nueva comanda (R7) | 0,25 |
| 8. Salida escapada y sin avisos con ninguna entrada (R8) | 0,75 |
| 9. Código organizado en funciones, comentado y sin repeticiones | 0,75 |
| **Total** | **10,00** |

## Solución propuesta

En [`SOLUCION-01`](SOLUCION-01/): `carta.php` devuelve el array de la carta, `funciones.php` valida y calcula
(`validarComanda()`, `lineasTicket()`, `subtotal()`, `calcularTotales()`) e `index.php` lo une y dibuja el
formulario y el ticket. Con el depurador, pon un punto de ruptura en `validarComanda()` y mira cómo llega
`$datos['cantidad']` y cómo se convierte en `$cantidades`. Construcción paso a paso en
[`SOLUCION-01/PASO A PASO`](SOLUCION-01/PASO%20A%20PASO/).
