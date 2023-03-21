<?php

/** 
 * LibURL.php
 *
 * Llibreria d'utilitats per a la URL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibCripto.php');

/**
 * Encripta la URL.
 * @param string $URL URL a encriptar.
 * @return URL encriptada.
 */
function EncriptaURL(string $URL): string {
	$aURL = explode('?', $URL);
	$Retorn = (count($aURL) > 1) ? $aURL[0].'?clau='.bin2hex(Encripta($aURL[1])) : $aURL[0];
	return $Retorn;
}

/**
 * Desencripta el paràmetre clau de GET en funció de si cal encriptar els paràmetres o no.
 * @param array $GET Array de paràmetres.
 */
function RecuperaGET(&$GET) {
	if (Config::EncriptaURL && isset($GET) && !empty($GET)) {
		$Clau = array_key_exists('clau', $GET) ? $GET['clau'] : '';
		if ($Clau == '') 
			throw new Exception('Error recuperant la clau.');		
		$Clau = Desencripta($Clau);
		$aClau = explode('&', $Clau);
		$GET = [];
		foreach($aClau as $Valor) {
			$aPart = explode('=', $Valor);
			$GET[$aPart[0]] = $aPart[1];
		}
	}
//echo "<BR><BR><BR>Clau: $Clau";
//echo "<BR>Valor de GET$:";
//print_r($GET);
}

/**
 * Genera la URL en funció de si cal encriptar els paràmetres o no.
 * @param string $URL URL a generar.
 * @return string URL generada.
 */
function GeneraURL(string $URL): string {
	return (Config::EncriptaURL) ? EncriptaURL($URL) : $URL;
}
