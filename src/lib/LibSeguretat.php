<?php

/** 
 * LibSeguretat.php
 *
 * Llibreria d'utilitats per a la seguretat.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Classe que encapsula les utilitats per a la seguretat.
 */
class Seguretat
{
	 /**
	  * Comprova si l'usuari té els permisos necessaris per a l'accés al punt on es crida el mètode.
	  * @param object $Usuari Usuari de l'aplicació.
	  * @param array $Array Array de 2 caràcters d'usuaris permesos. Codificació usuaris:
	  *   - SU: administrador
	  *   - DI: direcció
	  *   - CE: cap d'estudis
	  *   - PR: professor
	  *   - AL: alumne
	  *   - PM: pare o mare
	  *   - AD: administratiu
	  */
	public static function ComprovaAccessUsuari($Usuari, $Array) {
		$Access = false;
		foreach($Array as $Valor) 
			$Valor = strtoupper($Valor);
		if (in_array('SU', $Array) && $Usuari->es_admin)
			$Access = true;
		if (in_array('DI', $Array) && $Usuari->es_direccio)
			$Access = true;
		if (in_array('CE', $Array) && $Usuari->es_cap_estudis)
			$Access = true;
		if (in_array('PR', $Array) && $Usuari->es_professor)
			$Access = true;
		if (in_array('AL', $Array) && $Usuari->es_alumne)
			$Access = true;
		if (in_array('PM', $Array) && $Usuari->es_pare)
			$Access = true;
		if (in_array('AD', $Array) && $Usuari->es_administratiu)
			$Access = true;
		
		if (!$Access) {
			if (isset($_SESSION)) {
				session_unset();
				session_destroy();
			}
			die('Usuari no autoritzat.');
		}
	}
}

?>