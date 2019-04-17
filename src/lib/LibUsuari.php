<?php

/** 
 * LibUsuari.php
 *
 * Llibreria d'utilitats per a l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Classe que encapsula les utilitats per al maneig de l'usuari.
 */
class Usuari
{
	/**
	* Connexió a la base de dades.
	* @access public 
	* @var object
	*/    
	public $Connexio;

	/**
	* Usuari autenticat.
	* @access public 
	* @var object
	*/    
	public $Usuari;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	

	/**
	 * Comprova si l'usuari és administrador.
	 * @returns boolean Cert si l'usuari és administrador.
	 */
	function EsAdmin(): bool {
		return $this->Usuari->es_admin == '1';
	}	

	/**
	 * Comprova si l'usuari és direcció.
	 * @returns boolean Cert si l'usuari és direcció.
	 */
	function EsDireccio(): bool {
		return $this->Usuari->es_direccio == '1';
	}	

	/**
	 * Comprova si l'usuari és cap d'estudis.
	 * @returns boolean Cert si l'usuari és cap d'estudis.
	 */
	function EsCapEstudis(): bool {
		return $this->Usuari->es_cap_estudis == '1';
	}	

	/**
	 * Comprova si l'usuari és pare/mare d'un alumne.
	 * @param $alumne Identificador de l'alumne
	 * @returns boolean Cert si l'usuari és pare/mare de l'alumne.
	 */
	function EsProgenitor(int $alumne): bool {
		$SQL = ' SELECT * FROM USUARI WHERE usuari_id='.$alumne.' AND (pare_id='.$this->Usuari->usuari_id.' OR mare_id='.$this->Usuari->usuari_id.')';
		$ResultSet = $this->Connexio->query($SQL);
		return ($ResultSet->num_rows > 0);
	}	
}

?>