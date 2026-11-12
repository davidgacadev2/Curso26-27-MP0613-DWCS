<?php
/*
 * >>> PASO 1 de 6: descomposición a partir del autómata: estados, eventos, tabla de eventos-estados y transiciones, solo con TODOs <<<
 *
 * TAREA-02 AVANZADA: Código secreto
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "../../../DIAGRAMA ESTADO-TRANSICION/Automata-CodigoSecreto.html")
 *  - 5 estados, 7 eventos entrantes y 7 transiciones; una única página que se envía a sí misma por POST;
 *  - el estado y la partida viajan en campos ocultos (sin $_SESSION); el código secreto no: se calcula;
 *  - cada transición es un "case" del switch; cada evento saliente (ES), una función que escribe la página.
 */
// TODO: cargar las acciones y los predicados

// TODO: estados (ST) y eventos entrantes (EE) del autómata

// TODO: tabla de eventos-estados ampliada: $automata[estado][evento] = transición (null = "---")

// ----- Eventos salientes (ES): cada uno escribe una variante de la página ------------------------------------

// TODO: marcador(array $partida): las partidas ganadas y perdidas

// TODO: ES0 / ES1: el formulario de configuración (ES1, con los errores o un aviso)

// TODO: ficha(int $color, string $clase): una ficha de color

// TODO: ES2: la partida en juego: intentos anteriores, pista y el formulario del intento (con un aviso opcional)
    // TODO: el historial de intentos y los intentos que quedan
    // TODO: la pista, si se ha pedido
    // TODO: el formulario del intento: un desplegable por posición, que recuerda el último intento
    // TODO: los botones de pista y de rendirse

// TODO: historial(array $partida, array $secreto): una fila por intento con sus fichas, las negras y las blancas

// TODO: ES3 / ES4: partida ganada o perdida: el historial, el código y el marcador
    // TODO: botones para jugar otra vez o cambiar la configuración

// TODO: AC10 guardar(int $estado, array $partida): el estado y la partida en campos ocultos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código secreto</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Código secreto</h1>
<form method="post">
<?php
// TODO: 1) el estado (campo oculto, validado) y el evento entrante (el botón pulsado; sin botón, Carga)

// TODO: 2) la transición que dice la tabla (un evento imposible, "---", se trata como T1)
    // TODO: T2: ST1 + Empezar: validar la configuración y empezar la partida
            // TODO: para poder probar, el formulario puede mandar la semilla (si no, una al azar)
    // TODO: T3: ST2 + Probar
        // TODO: PL1: ¿es una partida posible? (si no: la página se ha manipulado)
    // TODO: T4: ST2 + Pista y T5: ST2 + Rendirse
        // TODO: PL1: ¿es una partida posible?
    // TODO: T6: ST3/ST4 + Nueva partida y T7: ST3/ST4 + Cambiar la configuración

// TODO: 3) AC10: el nuevo estado y la partida viajan con la página
?>
</form>
</body>
</html>
