<?php 
echo <<<MARCA
            <div id="entrada">
                <p>
                    <label for="lado">Introduzca la longitud del lado del tablero (impar de 5-29):</label>
                    <input type="number" id="lado" name="lado" value="{$lado}"/>
                </p>
                <p>
                    <label for="palabra">Introduzca una palabra (no usar acentos ni ñ):</label>
                    <input type="text" id="palabra" name="palabra" value="{$palabra}"/>
                </p>            
                <button type="submit" >Enviar</button>
                <button type="reset" ><a href="{$_SERVER['PHP_SELF']}" style="text-decoration: none;">Borrar</a></button>
            </div>

    MARCA;

?>