# Requisitos — TAREA-02 (BÁSICA): Comanda del Bar Baio

**CFGS Desarrollo de Aplicaciones Web — MP0613 Desarrollo web en entorno servidor — Tema 2**

Este documento detalla los requisitos de la tarea descrita en [ENUNCIADO.md](ENUNCIADO.md). La columna
**Origen** indica el requisito del enunciado (R1…R8) del que procede cada uno.

- **Requisitos funcionales (RF)**: *qué* debe hacer la aplicación.
- **Requisitos no funcionales (RNF)**: *cómo* debe hacerlo (tecnología, calidad, seguridad, organización del código…).

## Glosario

| Término | Significado |
|---|---|
| Carta | Array con los productos del bar: código => nombre, categoría y precio (IVA incluido). |
| Comanda | Lo que pide una mesa: número de mesa, cantidad de cada producto, si es socio y la nota. |
| Línea | Un producto pedido en el ticket: unidades, nombre, precio e importe (unidades × precio). |
| Suma | Total de las líneas, antes de las promociones. |
| Base imponible | Parte del total sin IVA: total / 1,10. |

---

## 1. Requisitos funcionales

### RF-01. Carta en un fichero aparte
| | |
|---|---|
| **Origen** | R1 |
| **Descripción** | `carta.php` devuelve con `return` el array de la carta; `index.php` lo recoge con `require`. |
| **Criterios de aceptación** | Al abrir la página aparece el campo `cantidad[cafe]` y el texto "Empanada de zamburiñas (3,20 €)". |

### RF-02. Formulario de la comanda
| | |
|---|---|
| **Origen** | R2 |
| **Descripción** | Mesa, un campo de cantidad por producto (agrupados por categoría), casilla de socio y nota. |
| **Salida** | Formulario pegajoso: tras enviarlo conserva los valores, también la casilla marcada. |
| **Criterios de aceptación** | Al enviar la mesa 13 y el café `x`, los campos muestran `13` y `x`. Al enviar con socio, la casilla sale `checked`. |

### RF-03. Validar la comanda
| | |
|---|---|
| **Origen** | R3 |
| **Descripción** | Se validan todos los campos y se muestran todos los errores a la vez. |
| **Reglas** | Mesa: entero 1-12. Cantidades: entero 0-20, vacío = 0. Al menos un producto (si las cantidades son correctas). Nota: máximo 80 caracteres con `mb_strlen`. Espacios de los extremos fuera. Códigos que no son de la carta, ignorados. |
| **Criterios de aceptación** | Enviar sin nada: "La mesa debe ser un número entero entre 1 y 12" y "La comanda está vacía". Mesa 13, café `x`, zumo 21 y nota de 81 caracteres: cuatro errores, entre ellos "«Café» debe ser un entero entre 0 y 20 (has escrito «x»)" y "(tiene 81)", y **no** sale "La comanda está vacía". `mesa[]=1` y `cantidad=abc`: errores sin avisos de PHP ("no tienen el formato correcto"). |

### RF-04. Líneas del ticket
| | |
|---|---|
| **Origen** | R4 |
| **Descripción** | Con la comanda correcta se muestra el ticket con las líneas agrupadas por categoría, subtotales y suma. |
| **Reglas** | Orden de la carta; solo productos con cantidad > 0; solo categorías con algo pedido. |
| **Criterios de aceptación** | Mesa ` 7 `, café ` 2 ` y zumo 1: "Ticket de la mesa 7", "Subtotal Bebidas" y 5,10 €; no sale la categoría Raciones. |

### RF-05. Promociones
| | |
|---|---|
| **Origen** | R5 |
| **Descripción** | 3x2 en cafés (uno gratis de cada tres) y 10 % para socios sobre lo que queda tras la promoción. |
| **Criterios de aceptación** | Mesa 4, 4 cafés, 2 tostadas, 1 pulpo, socio: "(1 gratis)" −1,30 € y descuento −1,95 €. Mesa 12, 6 cafés y 20 pulpos: 240,00 € de pulpo, "(2 gratis)", −2,60 € y total 245,20 €. Sin cafés suficientes ni socio no sale ninguna línea de promoción. |

### RF-06. Total y desglose del IVA
| | |
|---|---|
| **Origen** | R6 |
| **Descripción** | Total con IVA incluido; base imponible = total / 1,10 y IVA = total − base, redondeados a céntimos. |
| **Salida** | Importes con dos decimales, coma decimal, punto de miles y `€`. |
| **Criterios de aceptación** | El ejemplo de RF-05 da TOTAL 17,55 €, base 15,95 € e IVA 1,60 €. Dos cafés y un zumo: total 5,10 €, base 4,64 €, IVA 0,46 €. |

### RF-07. Nota y nueva comanda
| | |
|---|---|
| **Origen** | R7 |
| **Descripción** | La nota sale al pie del ticket; el enlace *Nueva comanda* vuelve al formulario vacío. |
| **Criterios de aceptación** | Con la nota "Sin sal": "Nota para la cocina: Sin sal". |

### RF-08. Entradas maliciosas
| | |
|---|---|
| **Origen** | R8 |
| **Descripción** | Toda la salida se escapa y ninguna entrada produce avisos. |
| **Criterios de aceptación** | Nota `<script>alert(1)</script>`: aparece `&lt;script&gt;` y nunca `<script>alert`. |

---

## 2. Requisitos no funcionales

### Tecnología y arquitectura
| Id | Requisito | Origen |
|---|---|---|
| RNF-01 | La aplicación está escrita en **PHP 8** y funciona en XAMPP o con `php -S`. | Tema 1 |
| RNF-02 | Una única página que se envía a sí misma por **POST**. | Enunciado |
| RNF-03 | Sin `$_SESSION`, bases de datos ni ficheros de datos. | Tema 2 |

### Organización y mantenibilidad del código
| Id | Requisito | Origen |
|---|---|---|
| RNF-04 | La carta está en `carta.php`, que devuelve un array; añadir un producto no obliga a tocar otro fichero. | R1 |
| RNF-05 | Los cálculos están en funciones con parámetros y tipos; el HTML no calcula. | Tema 2 |
| RNF-06 | Los límites (mesas, unidades, longitud de la nota, IVA, promociones) son constantes. | Buenas prácticas |

### Seguridad
| Id | Requisito | Origen |
|---|---|---|
| RNF-07 | Todo lo que se escribe en el HTML pasa por `htmlspecialchars`. | R8 |
| RNF-08 | Se valida el tipo de cada dato recibido (`is_string`, `is_array`) antes de usarlo. | R8 |

### Usabilidad y presentación
| Id | Requisito | Origen |
|---|---|---|
| RNF-09 | Los errores se muestran juntos, encima del formulario, con mensajes que dicen qué corregir. | R3 |
| RNF-10 | El ticket se lee como uno de papel: importes alineados a la derecha y totales destacados. | R4 |

### Entrega
| Id | Requisito | Origen |
|---|---|---|
| RNF-11 | El trabajo se entrega en el repositorio privado de GitHub del alumno, en la carpeta `TEMA-02`. | Normas del módulo |

---

## 3. Fuera de alcance (ampliaciones opcionales)

- Menú del día a precio cerrado.
- Cálculos con céntimos enteros.
- Forma de pago y cambio.
- Ticket de impresora en 40 columnas.
