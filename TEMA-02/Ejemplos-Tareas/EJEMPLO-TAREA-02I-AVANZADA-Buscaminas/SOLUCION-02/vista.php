<?php
/*
 * vista.php
 *  Funciones que GENERAN el HTML (lo devuelven como string; el echo lo hace index.php).
 */
require_once 'logica.php';

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Deduce los valores del formulario de configuración a partir de una partida
 * (para que el formulario muestre la configuración con la que se está jugando).
 */
function configuracionDe(array $partida): array
{
    foreach (NIVELES as $clave => $nivel) {
        if ($nivel['filas'] == $partida['filas'] && $nivel['columnas'] == $partida['columnas']
            && $nivel['minas'] == $partida['minas']) {
            return ['nivel' => $clave] + $nivel;
        }
    }
    return ['nivel' => 'personalizado', 'filas' => $partida['filas'], 'columnas' => $partida['columnas'], 'minas' => $partida['minas']];
}

/**
 * Formulario de nueva partida. $config: ['nivel' => ..., 'filas' => ..., 'columnas' => ..., 'minas' => ...]
 */
function pintarConfiguracion(array $config): string
{
    $accion = e($_SERVER['PHP_SELF']);
    $html   = "<form action='{$accion}' method='post' class='configuracion'>\n<fieldset>\n<legend>Nueva partida</legend>\n";

    $opciones = array_map(fn($nivel) => $nivel['nombre'], NIVELES);
    $opciones['personalizado'] = 'Personalizado:';
    foreach ($opciones as $clave => $texto) {
        $marcado = $config['nivel'] === $clave ? ' checked' : '';
        $html .= "<label><input type='radio' name='nivel' value='{$clave}'{$marcado}> {$texto}</label>\n";
    }

    $html .= "<span class='personalizado'>";
    $campos = [
        'filas'    => ['Filas', FILAS_MIN, FILAS_MAX],
        'columnas' => ['Columnas', COLUMNAS_MIN, COLUMNAS_MAX],
        'minas'    => ['Minas', 1, maximoMinas(FILAS_MAX, COLUMNAS_MAX)],
    ];
    foreach ($campos as $nombre => [$etiqueta, $min, $max]) {
        $valor = e((string) ($config[$nombre] ?? ''));
        $html .= "<label>{$etiqueta} <input type='number' name='{$nombre}' min='{$min}' max='{$max}' value='{$valor}'></label>\n";
    }
    $html .= "</span>\n<button type='submit' name='nueva' value='1'>Empezar</button>\n</fieldset>\n</form>\n";
    return $html;
}

/**
 * Marcador: minas restantes, jugadas, tiempo y mensaje de final de partida.
 */
function pintarMarcador(array $partida): string
{
    $restantes = $partida['minas'] - contarEstado($partida['estado'], BANDERA);
    $segundos  = segundosJugados($partida);
    $tiempo    = sprintf('%02d:%02d', intdiv($segundos, 60), $segundos % 60);

    $html  = "<div class='marcador'>";
    $html .= "<span>💣 Minas restantes: <strong>{$restantes}</strong></span>";
    $html .= "<span>🖱️ Jugadas: <strong>{$partida['jugadas']}</strong></span>";
    $html .= "<span>⏱️ Tiempo: <strong>{$tiempo}</strong></span>";
    $html .= "</div>\n";

    if ($partida['resultado'] == GANADA) {
        $html .= "<p class='final ganada'>🎉 ¡Enhorabuena! Has despejado el campo en {$tiempo} y {$partida['jugadas']} jugadas.</p>\n";
    } elseif ($partida['resultado'] == PERDIDA) {
        [$f, $c] = explode('-', $partida['explotada']);
        $html .= "<p class='final perdida'>💥 ¡BOOM! Has pisado la mina de la fila " . ($f + 1) . ", columna " . ($c + 1) . ".</p>\n";
    }
    return $html;
}

/**
 * HTML de una casilla del tablero.
 */
function pintarCasilla(array $partida, int $f, int $c): string
{
    $valor     = $partida['tablero'][$f][$c];
    $estado    = $partida['estado'][$f][$c];
    $terminada = $partida['resultado'] != EN_JUEGO;

    if ($estado == DESCUBIERTA) {
        if ($valor == MINA) {   // solo puede ser la mina que ha explotado
            return "<td class='mina explotada'>💣</td>";
        }
        if ($valor > 0 && !$terminada) {
            // SOLUCIÓN 02: los números son botones para poder hacer "acorde"
            return "<td class='descubierta'><button type='submit' name='celda' value='{$f}-{$c}' class='numero n{$valor}'"
                . " title='Despejar alrededor'>{$valor}</button></td>";
        }
        return "<td class='descubierta n{$valor}'>" . ($valor > 0 ? $valor : '') . "</td>";
    }

    if ($terminada) {
        // Partida acabada: enseñamos las minas y las banderas mal puestas
        if ($estado == BANDERA) {
            return $valor == MINA ? "<td class='bandera'>🚩</td>" : "<td class='error' title='Aquí no había mina'>❌</td>";
        }
        return $valor == MINA ? "<td class='mina'>💣</td>" : "<td class='oculta'></td>";
    }

    // Partida en juego: casilla oculta o con bandera -> botón
    $clase = $estado == BANDERA ? 'bandera' : 'oculta';
    $texto = $estado == BANDERA ? '🚩' : '';
    return "<td><button type='submit' name='celda' value='{$f}-{$c}' class='{$clase}'>{$texto}</button></td>";
}

/**
 * Tablero completo dentro de su formulario, con el selector de modo y el estado oculto.
 *  $camposOcultos: HTML con el estado de la partida (lo genera estado.php)
 */
function pintarTablero(array $partida, string $modo, string $camposOcultos): string
{
    $accion = e($_SERVER['PHP_SELF']);
    $html   = "<form action='{$accion}' method='post' class='juego'>\n";

    if ($partida['resultado'] == EN_JUEGO) {
        $html .= "<p class='modo'>Al hacer clic: ";
        foreach (['descubrir' => '⛏️ Descubrir', 'bandera' => '🚩 Poner/quitar bandera'] as $valor => $texto) {
            $marcado = $modo == $valor ? ' checked' : '';
            $html .= "<label><input type='radio' name='modo' value='{$valor}'{$marcado}> {$texto}</label> ";
        }
        $html .= "</p>\n";
    }

    $html .= "<table class='tablero'>\n";
    for ($f = 0; $f < $partida['filas']; $f++) {
        $html .= "<tr>";
        for ($c = 0; $c < $partida['columnas']; $c++) {
            $html .= pintarCasilla($partida, $f, $c);
        }
        $html .= "</tr>\n";
    }
    $html .= "</table>\n";

    $html .= $camposOcultos;
    $html .= "</form>\n";
    return $html;
}
