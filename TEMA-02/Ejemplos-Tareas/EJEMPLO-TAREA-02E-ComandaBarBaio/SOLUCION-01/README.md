# TAREA-02 BÁSICA: Comanda del Bar Baio — SOLUCIÓN-01

Enfoque: `carta.php` solo devuelve el array de la carta (`return [...]`). `funciones.php` tiene la lógica sin
HTML: `validarComanda()` (todos los errores y la comanda con sus tipos), `lineasTicket()` (líneas agrupadas por
categoría), `subtotal()` y `calcularTotales()` (3x2 en cafés, descuento de socio, total y desglose del IVA).
`index.php` llama a esas funciones cuando se pulsa *Calcular el ticket* y dibuja el formulario, generado a partir
de la carta, y el ticket. No hay estado entre peticiones: cada envío trae la comanda completa.

Cómo probarla: `php -S localhost:8000` en esta carpeta y abrir http://localhost:8000 (o copiarla a `htdocs`).

## Construcción paso a paso

| Paso | Qué se añade |
|---|---|
| paso1 | Descomposición en TODOs: el orden de `index.php` y la lista de funciones con su firma. |
| paso2 | La carta con `require`, constantes, `e()`, `validarComanda()` con todos los mensajes y el formulario pegajoso generado desde la carta (agrupado por categorías). |
| paso3 | `euros()`, `lineasTicket()`, `subtotal()` y el ticket: líneas por categoría, subtotales y suma. |
| **paso4 (último)** | **Solución completa.** Añade `calcularTotales()`: 3x2 en cafés, descuento de socio, total, base imponible e IVA; la nota y *Nueva comanda*. |

## Cómo comparar dos pasos

**Visual Studio Code**: selecciona las dos carpetas o ficheros, botón derecho y *Comparar seleccionados* (para
carpetas enteras, la extensión *Compare Folders*).

**Terminal** (desde esta carpeta; las comillas son necesarias por los espacios):

    git diff --no-index "PASO A PASO/paso1" "PASO A PASO/paso2"
    git diff --no-index --stat "PASO A PASO/paso2" "PASO A PASO/paso3"

## Propuesta de uso en clase

1. Entregar el **paso1** con el enunciado y pedir que se completen los TODOs.
2. Si alguien se atasca, darle el paso siguiente como pista.
3. Al corregir, revisar en clase el diff **paso1 → paso2** (cómo `cantidad[codigo]` llega como array y por qué se
   recorre la carta y no `$_POST`) y **paso3 → paso4** (el orden de los descuentos y por qué se redondea cada
   importe).
4. Probar en clase la mesa 4 con 4 cafés, 2 tostadas y 1 pulpo, de un socio: 17,55 € (base 15,95 €, IVA 1,60 €).
