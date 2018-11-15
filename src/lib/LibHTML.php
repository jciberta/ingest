<?php

/** 
 * LibHTML.php
 *
 * Llibreria d'HTML.
 */

 /**
 * CreaIniciHTML
 *
 * Crea l'inici del document HTML.
 * Ús: 
 *
 * @param string $Titol Títol de la pàgina.
 */
function CreaIniciHTML($Titol)
{
	echo "<HTML>";
	echo "<HEAD>";
	echo "	<META charset=UTF8>";
	echo "</HEAD>";
	echo '<BODY>';
	echo '<H1>'.utf8_encode($Titol).'</H1>';
}
 
/**
 * CreaDesplegable
 *
 * Crea un desplegable (combobox) HTML.
 * Ús: CreaDesplegable(array(1, 2, 3, 4), array("foo", "bar", "hello", "world"));
 *
 * @param string $Titol Títol del desplegable.
 * @param string $Nom Nom del desplegable.
 * @param array $Codi Codis de la llista.
 * @param array $Valor Valors de la llista.
 * @return void
 */
function CreaDesplegable($Titol, $Nom, $Codi, $Valor)
{
	echo $Titol.':';
	echo '<select name="'.$Nom.'">';
	
//  <option value="" selected disabled hidden>Escull...</option>	
	
	$LongitudCodi = count($Codi); 
	for ($i = 0; $i < $LongitudCodi; $i++)
	{
    echo '<option value="'.$Codi[$i].'">'.utf8_encode($Valor[$i]).'</option>';
	} 	
	echo "</select>";
	echo '<BR>';
}
 
 ?>
 
 