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
 * Escriu una variable en format humà.
 * @param mixed $Variable Variable a mostrar.
 */ 
function print_h($Variable)
{
	echo '<pre>';
	print_r($Variable);
	echo '</pre>';
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
 * CodificaUTF8
 *
 * Codifica un text en UTF8. Si ja ho era, el deixa igual (a diferència de utf8_encode).
 *
 * @param string $text text a codificar en UTF8.
 * @return string Text codificat.
 */
function CodificaUTF8(string $text): string
{
	$Codificacio = mb_detect_encoding($text);
	if (!in_array($Codificacio, ['UTF-8', 'ASCII']))
		$text = utf8_encode($text); 
	return $text;
}

/**
 * TextAMySQL
 *
 * Prepara un camp de text per a formar part d'una SQL.
 *
 * @param string $text data a preparar.
 * @return string Text preparat.
 */
function TextAMySQL(string $text)
{
	if ($text == '')
		$Retorn = 'NULL';
	else {
		$text = CodificaUTF8($text);
//print $text." -> CodificaUTF8(text): ".$text.'<BR>';
    	$Retorn = "'".str_replace("'", "''", $text)."'";
	}
	return $Retorn;
}

/**
 * Converteix un CSV (Valors Separats per Comes) en un array.
 * Les funcions explode i str_getcsv no funcionen. La primera no separa bé, i la segona elimina els tancaments.
 * @param string $text CSV a convertir.
 * @param string $delimitador Separador de les diferents cadenes.
 * @param string $tancament En que està en aquest bloc actua com una sola cadena (inclòs si hi ha un delimitador dins).
 * @return string Text preparat.
 */
function CSVAArray(string $text, string $delimitador = ",", $tancament = "'")
{
	$aRetorn = [];
	$s = '';
	$bDinsTancament = False;
	$l = strlen($text);
	for ($i=0; $i<$l; $i++) {
		if (($text[$i] == $delimitador) && !$bDinsTancament) {
			array_push($aRetorn, $s);
			$s = '';
		}
		else if ($text[$i] == $tancament) {
			$bDinsTancament = !$bDinsTancament;
			$s .= $text[$i];
		}
		else 
			$s .= $text[$i];
	}
	if ($l > 0)
		array_push($aRetorn, $s);
	return $aRetorn;
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


/**
 * Treu els accents d'un text.
 * https://www.php.net/strtr
 * @param string $Text Text treure els accents.
 * @return string Text sense els accents.
 */
function Normalitza($Text) {
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
    );
    return strtr($Text, $table);
}

/**
 * Retorna la primera paraula d'un text.
 * @param string $Text Text d'on treure la primera paraula.
 * @return string Primera paraula.
 */
function PrimeraParaula($Text) {
	$Retorn = Trim($Text);
	$asRetorn = explode(' ', $Retorn);
	if (count($asRetorn) > 0)
		$Retorn = $asRetorn[0];
	else
		$Retorn = '';
    return $Retorn;
}

?>