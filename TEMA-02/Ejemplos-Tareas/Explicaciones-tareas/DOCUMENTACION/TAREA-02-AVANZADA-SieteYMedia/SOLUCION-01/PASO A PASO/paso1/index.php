<?php
/*
 * >>> PASO 1 de 6: descomposición a partir del autómata: estados, eventos (uno interno), tabla de eventos-estados y transiciones, solo con TODOs <<<
 *
 * TAREA-02 AVANZADA: Siete y media
 *  SOLUCIÓN PROGRAMADA A PARTIR DEL AUTÓMATA (ver "DIAGRAMA ESTADO-TRANSICION/Automata-SieteYMedia.pdf")
 *  - 7 estados, 7 eventos entrantes (uno INTERNO) y 7 transiciones; una única página que se envía a sí misma
 *    por POST; la partida viaja en campos ocultos (sin $_SESSION), las cartas no: se calculan;
 *  - ST3, el turno de la banca, es un estado TRANSITORIO: cuando una transición termina en ST3, el programa
 *    atiende enseguida el evento interno (T5) y la banca juega en la misma petición. ST3 nunca llega al navegador.
 */
// TODO: cargar las acciones y los predicados

// TODO: estados (ST) y eventos entrantes (EE) del autómata

// TODO: tabla de eventos-estados ampliada: $automata[estado][evento] = transición (null = "---")

// ----- Eventos salientes (ES) --------------------------------------------------------------------------------

// TODO: mano(string $titulo, array $cartas): las cartas de un jugador y sus puntos

// TODO: resumen(array $partida): el saldo y las manos ganadas y perdidas

// TODO: ES0 / ES1: pedir la apuesta de la mano (ES1, con un aviso)
    // TODO: retirarse con las fichas que se tienen

// TODO: ES2: le toca al jugador: sus cartas y los botones Pedir carta y Plantarse

// TODO: ES3 / ES4: la mano ha terminado (perdida o ganada): las cartas de los dos, el resultado y el saldo
    // TODO: los botones para seguir o retirarse

// TODO: ES5 / ES6: sin fichas o retirado: el resumen de la partida y el botón para empezar otra

// TODO: AC9 guardar(int $estado, array $partida): el estado y la partida en campos ocultos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siete y media</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Siete y media</h1>
<form method="post">
<?php
// TODO: 1) el estado (campo oculto, validado; ST3 no puede llegar del navegador), el evento y la transición

// TODO: 2) antes de cualquier transición salvo T1: AC1 recuperar la partida y comprobarla (PL0)
    // TODO: PL0: ¿son posibles estos datos en el estado en el que dicen estar?

// TODO: 3) la transición; si deja el autómata en ST3, se atiende enseguida el evento interno (T5)
        // TODO: T2: ST1 + Apostar: leer la apuesta y repartir la primera carta
        // TODO: T3: ST2 + Pedir carta; T4: ST2 + Plantarse; T5: ST3 + evento interno (juega la banca)
                // TODO: PL4: sin fichas -> ST5, ES5
                // TODO: PL4: sin fichas -> ST5, ES5
        // TODO: T6: ST4 + Siguiente mano; T7: ST1/ST4 + Retirarse

// TODO: 4) AC9: el nuevo estado y la partida viajan con la página
?>
</form>
</body>
</html>
