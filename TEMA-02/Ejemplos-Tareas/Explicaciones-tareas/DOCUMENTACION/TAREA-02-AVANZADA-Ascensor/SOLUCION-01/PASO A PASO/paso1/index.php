<?php
/*
 * >>> PASO 1 de 6: descomposición a partir del autómata: estados, eventos, tabla de eventos-estados y transiciones, solo con TODOs <<<
 *
 * TAREA-02 AVANZADA: El ascensor del instituto
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "DIAGRAMA ESTADO-TRANSICION/Automata-Ascensor.pdf")
 *  - 7 estados, 5 eventos entrantes y 10 transiciones; una única página que se envía a sí misma por POST;
 *  - aquí el autómata de la página ES el del ascensor: parado, subiendo, bajando, puertas abiertas, emergencia...
 *  - el estado y los datos del ascensor viajan en campos ocultos (sin $_SESSION) y se comprueban con los
 *    invariantes de cada estado (PL0) antes de cualquier transición.
 */
// TODO: cargar las acciones y los predicados

// TODO: estados (ST) y eventos entrantes (EE) del autómata

// TODO: tabla de eventos-estados ampliada: $automata[estado][evento] = transición (null = "---")

// TODO: evento(array $datos): int -> el evento entrante según el botón pulsado ("llamar:3" es Llamar)

// TODO: ES0...ES4: el edificio con el ascensor, los botones de llamada, el mensaje y los mandos
    // TODO: los botones de emergencia y de rearmar

// TODO: AC8 guardar(int $estado, array $ascensor): el estado y el ascensor en campos ocultos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El ascensor del instituto</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>El ascensor del instituto</h1>
<form method="post">
<?php
// TODO: 1) el estado (campo oculto, validado), el evento entrante y la transición que dice la tabla

// TODO: 2) antes de cualquier transición salvo T1: AC1 recuperar el ascensor y comprobarlo (PL0)
    // TODO: PL0: ¿cumplen los datos los invariantes del estado en el que dicen estar?

// TODO: 3) la transición
    // TODO: T2: ST1 + Llamar: la llamada con el ascensor parado
    // TODO: T3: ST2/ST3 + Llamar y T4: ST4 + Llamar (llamadas mientras se mueve o con las puertas abiertas)
    // TODO: T9: ST1-ST4 + Emergencia y T10: ST5 + Rearmar

// TODO: 4) el evento saliente (la página) y AC8: el nuevo estado y el ascensor viajan con ella
?>
</form>
</body>
</html>
