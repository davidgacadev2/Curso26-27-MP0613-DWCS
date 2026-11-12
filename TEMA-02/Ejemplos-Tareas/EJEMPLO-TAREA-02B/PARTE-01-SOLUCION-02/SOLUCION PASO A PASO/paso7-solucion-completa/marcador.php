<?php 
echo <<<MARCA
    <p>Puntos hasta ahora: {$puntos} </p>    
    <p>Fallos seguidos: {$fallosSeguidos} </p> 

    <input type="hidden" name="puntos" value="{$puntos}"/>
    <input type="hidden" name="fallosSeguidos" value="{$fallosSeguidos}"/>
    <input type="hidden" name="finJuego" value="{$finJuego}"/>
MARCA;
echo "";
?>