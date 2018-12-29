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
 *
 * @param string $date data a comprovar.
 * @param string $format Format de la data.
 * @return boolean Si la data és correcta o no.
 */
function ComprovaData($date, $format = 'd/m/Y')
{
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
    	$Retorn = $aTemp[2].'-'.$aTemp[1].'-'.$aTemp[0];
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

?>
