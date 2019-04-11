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
 * Ordinal
 *
 * Retorna l'ordinal d'un número.
 *
 * @param integer $Numero Número a passar a ordinal (1r, 2n, 3r, etc.).
 * @return string Ordinal d'un número.
 */
function Ordinal($Numero)
{
	if ($Numero < 1)
		return '';
	else if ($Numero == 1)
		return '1r';
	else if ($Numero == 2)
		return '2n';
	else if ($Numero == 3)
		return '3r';
	else if ($Numero == 4)
		return '4t';
	else if ($Numero >4)
		return $Numero.'è';
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

/**
 * TextAMySQL
 *
 * Prepara un camp de text per a formar part d'una SQL.
 *
 * @param string $text data a preparar.
 * @return string Text preparat.
 */
function TextAMySQL($text)
{
	if ($text == '')
		$Retorn = 'NULL';
	else {
		$Codificacio = mb_detect_encoding($text);
		if (!in_array($Codificacio, ['ASCII', 'UTF-8']))
			$text = utf8_decode($text);
    	$Retorn = "'".str_replace("'", "''", $text)."'";
	}
	return $Retorn;
}

/**
 * Elimina si comença per.
 * @param string $Text Text a comprovar.
 * @param string $Prefix Prefix a eliminar.
 * @return string Text sense el prefix.
 */
function EliminaSiComencaPer(string $Text, string $Prefix): string
{
	if (substr($Text, 0, strlen($Prefix)) == $Prefix)
		$Text = substr($Text, strlen($Prefix));
	return $Text;
}

?>