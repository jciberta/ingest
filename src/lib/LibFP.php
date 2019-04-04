<?php

/** 
 * LibFP.php
 *
 * Llibreria d'utilitats per a les taules de FP.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Classe que encapsula les utilitats per al maneig de l'usuari.
 */
class CicleFormatiu
{
	/**
	* Connexió a la base de dades.
	* @access public 
	* @var object
	*/    
	public $Connexio;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con) {
		$this->Connexio = $con;
	}	

	/**
	 * Obté el nom d'un cicle formatiu a partir del seu identificador.
	 * @param integer $Id identificador del cicle formatiu.
	 */
	function ObteNom($Id) {
		$Retorn = "";
		$SQL = "SELECT * FROM CICLE_FORMATIU WHERE cicle_formatiu_id=".$Id;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$cf = $ResultSet->fetch_object();
			$Retorn = utf8_encode($cf->nom);
		}
		return $Retorn;
	}

	/**
	 * Obté el codi d'un cicle formatiu a partir del seu identificador.
	 * @param integer $Id identificador del cicle formatiu.
	 */
	function ObteCodi($Id) {
		$Retorn = "";
		$SQL = "SELECT * FROM CICLE_FORMATIU WHERE cicle_formatiu_id=".$Id;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$cf = $ResultSet->fetch_object();
			$Retorn = utf8_encode($cf->codi);
		}
		return $Retorn;
	}
}

?>