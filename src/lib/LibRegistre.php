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
 require_once(ROOT.'/lib/LibClasses.php');

/**
 * Classe Registre.
 *
 * Classe per al registre de log.
 */
class Registre extends Objecte
{
	const AUTH = 'Autenticació';
	const AVAL = 'Avaluació';
	const MATR = 'Matrículació';

	/**
	 * Registra a la taula de log un missatge.
	 * @param string $Seccio Secció del missatge.
	 * @param string $Missatge Missatge a registrar.
	 */
	public function Escriu($Seccio, $Missatge) {
		$SQL = "INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge) VALUES (".
			$this->Usuari->usuari_id.", ".
			"'".trim($this->Usuari->nom." ".$this->Usuari->cognom1." ".$this->Usuari->cognom2)."', ".
			"'".date('Y-m-d H:i:s')."', ".
			"'".getUserIP()."', ".
			"'".utf8_decodeX($Seccio)."', ".
			"'".utf8_decodeX($Missatge)."'".
			")";
		$this->Connexio->query($SQL);
	}
}

?>