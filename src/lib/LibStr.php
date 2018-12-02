<?php

/** 
 * LibStr.php
 *
 * Llibreria d'utilitats per a cadenes de caràcters (strings).
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

 /**
 * TrimX
 *
 * Elimina els espais innecessaris, és a dir, els espais inicials, finals, i més de dos espais seguits.
 *
 * @param string $Text Text a suprimir els espais.
 * @return string Text amb els espais suprimits.
 */
function TrimX($Text)
{
	$sRetorn = trim($Text);
	while (strpos($sRetorn, '  ') > 0)
		$sRetorn = str_replace('  ', ' ', $sRetorn);
	return $sRetorn;
}

 /**
 * TrimXX
 *
 * Elimina tots els espais.
 *
 * @param string $Text Text a suprimir els espais.
 * @return string Text amb els espais suprimits.
 */
function TrimXX($Text)
{
	$sRetorn = str_replace(' ', '', $Text);
	return $sRetorn;
}
 
 /**
 * Ocurrencies
 *
 * Compta les ocurrències d'un array ordenat i ho retorna en forma de 2 arrays.
 *
 * @param array $Connexio Connexió a la base de dades.
 * @param string $SQL Sentència SQL.
 * @param array $CampCodi Nom del camp del codi.
 * @param array $CampValor Nom del camp del valor.
 * @return void Array que conté 2 arrays (parell codi-valor).
 */
function Ocurrencies($array)
{
	$aRetorn = array();
	$TextAnterior = '';
	$j = -1;
	for($i = 0; $i < count($array); $i++) {
		if ($array[$i] == $TextAnterior) {
			$aRetorn[$j][1]++;
		}
		else {
			$TextAnterior = $array[$i];
			$j++;
			$aRetorn[$j][0] = $array[$i];
			$aRetorn[$j][1] = 1;
		}
	}
	return $aRetorn;
}
 
/**
 * JSONEncodeUTF8Especial
 *
 * La funció de PHP json_encode no funciona si té caràcters que no són UTF8 i no retorna res.
 * Error: "Malformed UTF-8 characters, possibly incorrectly encoded".
 * https://stackoverflow.com/questions/41972084/php-json-encode-not-working
 *
 * @param array $row Fila retornada per un ResultSet d'una query.
 * @return string Fila del ResultSet en format JSON. // especial (sense cometes dobles).
 */
function JSONEncodeUTF8($row)
{
	$sRetorn = '{';
	foreach($row as $clau => $valor) {
		$sRetorn .= '"'.$clau.'":"'.utf8_encode($valor).'",';
//		$sRetorn .= ''.$clau.':'.utf8_encode($valor).',';
	}
	$sRetorn = substr($sRetorn, 0, -1);
	$sRetorn .= '}';
	return $sRetorn;
}

?>
