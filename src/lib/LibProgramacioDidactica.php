<?php

/** 
 * LibProgramacioDidactica.php
 *
 * Llibreria d'utilitats per a la programació didàctica.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

//require_once(ROOT.'/lib/LibUsuari.php');
//require_once(ROOT.'/lib/LibURL.php');
//require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
//require_once(ROOT.'/lib/LibHTML.php');


/**
 * Classe que encapsula les utilitats per al maneig del professor.
 */
class ProgramacioDidactica extends Objecte
{
}

/**
 * Formulari que mostra l'assignacio de professors per UF.
 */
class ResultatsAprenentatge extends Form
{
	/**
	* Identificador del cicle formatiu.
	* @var integer
	*/    
    public $CicleFormatiuId = -1; 
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, "Resultats d'aprenentatge");
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/ProgramacioDidactica.js?v1.1" type="text/javascript"></script>';

		// Inicialització de l'ajuda
		// https://getbootstrap.com/docs/4.0/components/popovers/
//		echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

		echo $this->GeneraFiltre();
		echo '<BR><BR>';
		echo $this->GeneraTaula();
		CreaFinalHTML();
	}	

	/**
	 * Crea la sentència SQL.
	 * @param integer $CicleFormatiuId Identificador del cicle.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $CicleFormatiuId): string {
		return "
			SELECT 
				MP.modul_professional_id, MP.codi AS CodiMP, MP.nom AS NomMP,
				UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.nivell, UF.hores AS HoresUF, UF.activa, UF.es_fct AS FCT, 
				RA.resultat_aprenentatge_id, RA.descripcio AS ResultatAprenentatge,
				CAV.descripcio AS CriteriAvaluacio
			FROM MODUL_PROFESSIONAL MP 
			LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id)
			LEFT JOIN RESULTAT_APRENENTATGE RA ON (RA.unitat_formativa_id=UF.unitat_formativa_id)
			LEFT JOIN CRITERI_AVALUACIO CAV ON (CAV.resultat_aprenentatge_id=RA.resultat_aprenentatge_id)
			WHERE cicle_formatiu_id=$CicleFormatiuId
		";		
	}

	/**
	 * Genera el filtre del formulari si n'hi ha.
	 */
	protected function GeneraFiltre() {
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$this->CicleFormatiuId = $aCicles[0][0]; 
		return $this->CreaLlista('cicle_formatiu_id', 'Cicle', 800, $aCicles[0], $aCicles[1], $this->CicleFormatiuId, 'onchange="ActualitzaTaulaResultatsAprenentatge(this);"');
	}

	/**
	 * Genera la taula amb el resultat de la SQL.
     * @return string Acordió amb les dades.
	 */
	public function GeneraTaula() {
		$sRetorn = '<DIV id=taula>';
		$ModulProfessionalId = -1;
		$UnitatFormativaId = -1;
		$ResultatAprenentatgeId = -1;
		$SQL = $this->CreaSQL($this->CicleFormatiuId);
//print_r($SQL);		
		$ResultSet = $this->Connexio->query($SQL);
//print_r($ResultSet->num_rows);		
		if ($ResultSet->num_rows > 0) {
//print_r('-');		
			while($row = $ResultSet->fetch_object()) {
				if ($row->resultat_aprenentatge_id !== $ResultatAprenentatgeId) {
					// RA nou
					if ($ResultatAprenentatgeId != -1)
						$sRetorn .= '</ul>';
					if ($row->unitat_formativa_id !== $UnitatFormativaId) {
						// UF nova
						if ($UnitatFormativaId != -1)
							$sRetorn .= '</ul>';
						if ($row->modul_professional_id !== $ModulProfessionalId) {
							// Mòdul nou
							if ($ModulProfessionalId != -1)
								$sRetorn .= '</ul>';
							$sRetorn .= '<li><b>'.$row->CodiMP.'. '.utf8_encode($row->NomMP).'</b>';
							$sRetorn .= '<ul>';
							$ModulProfessionalId = $row->modul_professional_id;
						}
						$sRetorn .= '<li><u>'.utf8_encode($row->NomUF).'</u>';
						$sRetorn .= '<ul>';
						$UnitatFormativaId = $row->unitat_formativa_id;
					}
					$sRetorn .= '<li>RA'.utf8_encode($row->ResultatAprenentatge);
					$sRetorn .= '<ul>';
					$ResultatAprenentatgeId = $row->resultat_aprenentatge_id;
				}
				if ($row->CriteriAvaluacio != '')
					$sRetorn .= '<li>'.utf8_encode($row->CriteriAvaluacio);
			}
		}
		else
			$sRetorn .= 'No hi ha dades.';
		$sRetorn .= '</DIV>';
		return $sRetorn;			
	}
}

