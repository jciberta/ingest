<?php

/** 
 * LibDate.php
 *
 * Llibreria d'utilitats per a les dates.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * ComprovaData
 *
 * Comprova una data si és correcta.
 * https://stackoverflow.com/questions/12322824/php-preg-match-with-working-regex
 * https://stackoverflow.com/questions/2086598/validate-date-format-in-php
 *
 * @param string $date data a comprovar.
 * @param string $format Format de la data.
 * @return boolean Si la data és correcta o no.
 */
function ComprovaData($date, $format = 'd/m/Y') {
	// El cas dd/mm/yy cal passar-lo a dd/mm/yyyy
	if (substr_count($date, '/') == 2) {
        list($d, $m, $y) = explode('/', $date);
		if ($y < 100)
			$y = ($y < 50) ? (2000+$y) : (1900+$y);
        $date = $d.'/'.$m.'/'.$y;
    }	
	
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * DataAMySQL
 *
 * Transforma una data al format MySQL (yyyy-mm-dd).
 *
 * @param string $date data a transformar.
 * @return string Data transformada.
 */
function DataAMySQL($date)
{
	if ($date == '')
		$Retorn = 'NULL';
	else if (ComprovaData($date)) {
		$aTemp = explode('/', $date);
    	$Retorn = "'".$aTemp[2].'-'.$aTemp[1].'-'.$aTemp[0]."'";
	}
	else
		throw new Exception('Data no vàlida');
	return $Retorn;
}

/**
 * MySQLAData
 *
 * Transforma una data al format de l'aplicació (dd/mm/yyyy).
 *
 * @param string $date data a transformar.
 * @return string Data transformada.
 */
function MySQLAData($date)
{
	$date = substr($date, 0, 10); // Per si és DateTime
	if ($date == '')
		$Retorn = '';
	else if (ComprovaData($date, 'Y-m-d')) {
			$aTemp = explode('-', $date);
			$Retorn = $aTemp[2].'/'.$aTemp[1].'/'.$aTemp[0];
	}
	else
		throw new Exception('Data no vàlida');
	return $Retorn;
}

/**
 * DiaSetmana
 *
 * Calcula el dia de la setmana a partir d'una data.
 *
 * @param string $date Data.
 * @return string Dia de la setmana (en català).
 */
function DiaSetmana($date)
{
	$Retorn = '';
	if ($date != '') {
		$unixTimestamp = strtotime($date);
		$dayOfWeek = date("w", $unixTimestamp);
		$dowMap = array('Diumenge', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte');
		$Retorn = $dowMap[$dayOfWeek];
	}
	return $Retorn;
}

/**
 * ProperDia
 *
 * Calcula el proper dia a partir d'una data.
 * http://php.net/manual/en/datetime.add.php
 *
 * @param string $date Data.
 * @param integer $dies Número de dies a sumar.
 * @return string Nova data.
 */
function ProperDia($date, $dies)
{
	$Retorn = '';
	if ($date != '') {
		$dt = DateTime::createFromFormat('d/m/Y', $date);
		$dt->add(new DateInterval('P'.$dies.'D'));
		$Retorn = $dt->format('d/m/Y');
	}
	return $Retorn;
}

function DataATextCatala($Data) {
	$TextMes = ['de gener', 'de febrer', 'de març', "d'abril", 'de maig', 'de juny', 
		'de juliol', "d'agost", 'de setembre', "d'octubre", 'de novembre', 'de desembre'];
	list($Dia, $Mes, $Any) = explode('/', $Data);
	return intval($Dia).' '.$TextMes[intval($Mes)-1].' de '.$Any;
}

?>