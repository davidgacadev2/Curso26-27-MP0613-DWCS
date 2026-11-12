# Incoherencias detectadas en el material de las tareas (sin corregir)

Encontradas al redactar las explicaciones, leyendo el código y los diffs entre pasos. **No se ha ejecutado PHP**:
las marcadas con (*) son deducciones de la lectura del código y conviene comprobarlas.

## EJEMPLO-TAREA-02A-Agenda
- El README describe mal el FIXME del apóstrofo: con `O'Brien` la lista ya muestra solo "O" desde el principio
  (el apóstrofo cierra `value='...'`); el `hidden` sobrevive. Lo que rompe el `hidden` es un apóstrofo en el teléfono.
- El autómata (docs/Automata) no coincide con R3 ni con el código: condición de error `!PL0 || !PL1`, falta la
  transición de eliminar y la vuelta a "sin datos"; erratas "paginaIncicial", "arrangar".
- `ob_end_flush()` / `ob_implicit_flush(true)` activos aunque el comentario dice que son "solo para" depurar.
- Defectos no marcados como FIXME (*): `?agenda=hola` (error fatal en `count()`), `?enviar=1` sin campos (avisos),
  `$_SERVER['PHP_SELF']` sin escapar, `ucwords()` no unifica *JUAN*/*Juan* ni pone mayúscula a `ángel`.

## EJEMPLO-TAREA-02B-TableroLetras
- Validación del lado: además del FIXME (`$lado < 1` frente a "mayor o igual a 5"), con lado 31 sale el mismo
  mensaje; la etiqueta dice "5-29" y la condición `$lado > 30`.
- Errata "mínmo" en el código. Palabra con ñ o tildes: se convierte en `""` y sale el error de longitud.
- Comentario falso sobre `primeraVez` en el fin del juego. HTML no válido: `<a>` dentro de `<button type="reset">`
  y `hidden` dentro de `<tr>`. Casillas `o` con "0" en una función y "1" en los campos ocultos (sin efecto).
- Requisitos RF-04 atribuye el recorte de la palabra solo a la solución 03 (lo hacen las tres; la 02 mal).
- No se usa `ob_end_flush()`/`ob_implicit_flush()` (RNF-11); el estado no es mínimo (`finJuego`, lado).
- *Enviar* a mitad de partida empieza otra y pone el marcador a cero (no documentado).
- (*) `botonJugada=9x9` parece contarse como acierto (`null != "0"`).

## TAREA-02-BASICA-Calendario
- RNF-12: `?mes[]=1&anio=2027` lanza un `TypeError` (`validarEntero()` recibe `?string`) (*).
- Sin README; carpeta de pasos fuera de `SOLUCION-01` y sin sufijos; los TODO del paso 1 no indican el paso.
- RF-05: con `mes=13` no queda ninguna opción seleccionada (sale Enero).
- RF-10 cita "sin festivos de fecha fija" y el código dice "Este mes no tiene festivos de fecha fija."; el
  comentario dice que los enlaces se "deshabilitan" y en realidad se omiten.

## TAREA-02-BASICA-ComandaBarBaio
- RF-03: `mesa[]=1` da el error normal de la mesa, no "no tienen el formato correcto" (solo `cantidad=abc` lo da).
- RNF-05: la vista llama a `subtotal()` (se calcula dos veces). El precio del formulario no usa `euros()`.
- `calcularTotales()` lee la cantidad del café sin comprobar que exista en la carta; texto "3x2 en cafés" escrito
  a mano aunque producto y cantidad son constantes.

## TAREA-02-MEDIA-CuadernoNotas
- Sin README; la carpeta de pasos no está enlazada desde el enunciado.
- RNF-15 (*): `nombre[]=x`, `notas[0][]=5`, `csv[]=` → `TypeError`; `eliminar[]=x` → *Array to string conversion*.
- RNF-08: `calificacion()` no usa `NOTA_APROBADO` ni `CALIFICACIONES`. RF-08 dice "60 %" y se muestra "60,0 %".
- `funciones.php` no lleva la cabecera "PASO N de 4". Con `6,5x` el mensaje muestra «6.5x».

## TAREA-02-MEDIA-MemoriaParejas
- *Nueva partida* visible durante toda la partida (R6: al terminar).
- (*) `filas[]=1` da *Array to string conversion* en `validarEntrada()`.
- `estadoValido()` no comprueba letras repetidas (`letras=AA`) ni cartas por fila (`cartas[0][9]=Z` se acepta).
- `puntos` y `giradas` se guardan sin validar (`puntos=1000` se acepta).

## TAREA-02-AVANZADA-CuatroEnRaya
- RF-09: `jugadas[]` se convierte en `''` y se acepta como partida vacía.
- RNF-05: `[0-6]` escrito a mano en `reconstruir()`, máximo 10 columnas, el 4 no es constante.
- (*) Semilla sin límite superior: cerca de `PHP_INT_MAX` daría un `TypeError`.

## TAREA-02-AVANZADA-Buscaminas
- Ninguna solución responde las 3 preguntas de reflexión (RNF-11, criterio 12).
- SOLUCION-02 rellena de forma iterativa y R4/RNF-06 exigen recursividad.
- (*) Avisos/`TypeError` con arrays (`celda`, `nivel[]=x`, filas de `partida[tablero]`).
- `recuperarPartida()` no comprueba que los números cuadren con las minas; RF-13 solo se cumple tras el primer clic.
- Paso 3: `pintarMarcador()` usa `$tiempo` antes de definirlo. Sin README. "1 jugadas".

## TAREA-02-AVANZADA-SieteYMedia
- PL4 es `saldo === 0`, pero se puede quedar con 1–4 fichas: "Apuesta (5-3 fichas)" y solo queda *Retirarse*.
- PL0 no impide subir la `apuesta` con F12 en ST2 tras ver la carta.
- El `default:` provisional del paso 2 sigue en la solución. Errata "cartas cartas" en el autómata.

## TAREA-02-AVANZADA-Ascensor
- `paso1/index.php` no tiene TODO para T1 ni para T5–T8.
- El autómata cita "52 peticiones de pruebas.json", que no está. T5 dice "se cierran las puertas" (ya lo están).
- (*) Paso 5: con `estado=2`, `piso=4` y sin llamadas, *Avanzar* da *Undefined array key 5*.

## TAREA-02-AVANZADA-CodigoSecreto
- Requisitos RF-07: `intentos[]` no da "partida no válida" (*): se convierte en `''` y se borra el historial.
- R4 dice usar `hash_hmac()` como semilla; el código usa `crc32(hash_hmac(...))` (necesario: `mt_srand()` pide int).
- Las pistas dicen que el formulario puede enviar la semilla, pero no hay campo `semilla`.
- El autómata cita "51 peticiones de pruebas.json", que no están.
- (*) Paso 5: `pista=7` → *Undefined array key 7* y `TypeError` en `ficha(int)`.
