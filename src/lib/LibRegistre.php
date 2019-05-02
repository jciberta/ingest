<?php

/** 
 * LibRegistre.php
 *
 * Llibreria per al registre de log.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */

require_once(ROOT.'/lib/LibInet.php');

/**
 * Classe Registre.
 *
 * Classe per al registre de log.
 */
class Registre {
	const AUTH = 'Autenticació';
	const AVAL = 'Avaluació';

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
	 * Registra a la taula de log un missatge.
	 * @param string $Seccio Secció del missatge.
	 * @param string $Missatge Missatge a registrar.
	 */
	public function Escriu($Seccio, $Missatge) {
		$SQL = "INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge) VALUES (".
			$this->Usuari->usuari_id.", ".
			"'".trim(utf8_decode($this->Usuari->nom." ".$this->Usuari->cognom1." ".$this->Usuari->cognom2))."', ".
			"'".date('Y-m-d H:i:s')."', ".
			"'".getUserIP()."', ".
			"'".utf8_decode($Seccio)."', ".
			"'".utf8_decode($Missatge)."'".
			")";
		$this->Connexio->query($SQL);
	}
}

?>