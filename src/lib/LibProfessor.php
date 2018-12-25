<?php

/** 
 * LibProfessor.php
 *
 * Llibreria d'utilitats per al professor.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('LibUsuari.php');

/**
 * Classe que encapsula les utilitats per al maneig del professor.
 */
class Professor extends Usuari
{
	/**
	* UF Assignades.
	* @access public 
	* @var array
	*/    
	public $UFAssignades = [];

	/**
	 * Carrega les UF assignades en un array.
	 */
	function CarregaUFAssignades() {
		$UFAssignades = [];
		$SQL = ' SELECT '.
			' CF.cicle_formatiu_id, CF.codi AS CodiCF, CF.nom AS NomCF, '.
			' UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.nivell '.
			' FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=PUF.uf_id) '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
			' WHERE professor_id='.$this->Usuari->usuari_id .
			' ORDER BY CF.codi, UF.nivell ';
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$i = 0;
			while ($obj = $ResultSet->fetch_object()) {
				$this->UFAssignades[$i] = $obj;
				$i++;
			}
		}
		$ResultSet->close();
	}

	/**
	 * Comprova si té alguna UF en un determinat cicle i nivell.
	 * @param integer $Cicle Identificador del cicle.
	 * @param integer $Nivell Nivell del cicle.
	 * @returns boolean Cert si té alguna UF en un determinat cicle i nivell.
	 */
	function TeUFEnCicleNivell($Cicle, $Nivell) {
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades); $i++) {
			if (($this->UFAssignades[$i]->cicle_formatiu_id == $Cicle) && ($this->UFAssignades[$i]->nivell == $Nivell))
				$bRetorn = True;
		}
		return $bRetorn;
	}

	/**
	 * Comprova si té assignada una UF.
	 * @param integer $UF Identificador de la UF.
	 * @returns boolean Cert si té assignada la UF.
	 */
	function TeUF($UF) {
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades); $i++) {
			if ($this->UFAssignades[$i]->unitat_formativa_id == $UF) 
				$bRetorn = True;
		}
		return $bRetorn;
	}
}

?>
