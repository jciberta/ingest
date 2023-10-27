<?php

/** 
 * LibDocument.php
 *
 * Llibreria d'utilitats per a l'administració dels documents.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibUsuari.php');

/**
 * Classe que encapsula les utilitats per a l'administració.
 */
class Document extends Objecte
{
	// Estudis
	const ESTUDI = array(
		'GEN' => 'General', 
		'ESO' => 'ESO',
		'BAT' => 'Batxillerat',
		'CF0' => 'Cicle formatiu',
		'CFB' => 'Cicle formatiu de grau bàsic',
		'CFM' => 'Cicle formatiu de grau mig',
		'CFS' => 'Cicle formatiu de grau superior'
	);

	// Subestudis
	const SUBESTUDI = array(
		'FPB' => 'FPB',
		'APD' => 'APD',
		'CAI' => 'CAI',
		'FIP' => 'FIP',
		'SMX' => 'SMX',
		'DAM' => 'DAM',
		'HBD' => 'HBD'
	);

	// Categoria
	const CATEGORIA = array(
		'D' => 'Document de centre',
		'I' => 'Imprès de funcionament'
	);

	// Sol·licitant
	const SOLICITANT = array(
		'T' => 'Tutor',
		'A' => 'Alumne'
	);

	// Liurament/Custòdia
	const LLIURAMENT_CUSTODIA = array(
		'TU' => "Tutor",
		'TF' => "Tutor FCT",
		'TD' => "Tutor Dual",
		'SE' => "Secretaria",
		'CE' => "Cap d'estudis",
		'CF' => "Coordinador FP",
		'CD' => "Coordinador Dual"
	);

	/**
	 * Genera el formulari de la recerca.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		$Professor = new Professor($this->Connexio, $this->Usuari, $this->Sistema);

		$frm = new FormRecerca($this->Connexio, $this->Usuari, $this->Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Impresos de funcionament';
		$frm->SQL = $this->CreaSQL();
		$frm->Taula = 'DOCUMENT';
		$frm->ClauPrimaria = 'document_id';
		$frm->Camps = 'nom, estudi, subestudi, categoria, solicitant, lliurament, custodia';
		$frm->Descripcions = 'Nom, Estudi, Nivell, Categoria, Sol·licitant, Lliurament, Custòdia';
		if ($this->Usuari !== null) {
			if ($this->Usuari->es_admin || $this->Usuari->es_direccio ||$this->Usuari->es_cap_estudis || $Professor->EstaAQualitat()) {
				$frm->PermetEditar = true;
				$frm->URLEdicio = 'Fitxa.php?accio=Document';
				$frm->PermetAfegir = true;
			}
		}
		$frm->AfegeixSuggeriment('observacions');
		$frm->AfegeixEnllacImatge('enllac', FormRecerca::tiPDF);

		$aClaus = array_keys(self::ESTUDI); array_unshift($aClaus, '');
		$aValors = array_values(self::ESTUDI); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('estudi', 'Estudi', 60, $aClaus, $aValors);

		$aClaus = array_keys(self::SUBESTUDI); array_unshift($aClaus, '');
		$aValors = array_values(self::SUBESTUDI); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('subestudi', 'Nivell', 30, $aClaus, $aValors);

		$aClaus = array_keys(self::CATEGORIA); array_unshift($aClaus, '');
		$aValors = array_values(self::CATEGORIA); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('categoria', 'Categoria', 50, $aClaus, $aValors);

		$aClaus = array_keys(self::SOLICITANT); array_unshift($aClaus, '');
		$aValors = array_values(self::SOLICITANT); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('solicitant', 'Sol·licitant', 30, $aClaus, $aValors);

		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('lliurament', 'Lliurament', 40, $aClaus, $aValors);

		$aClaus = array_keys(self::LLIURAMENT_CUSTODIA); array_unshift($aClaus, '');
		$aValors = array_values(self::LLIURAMENT_CUSTODIA); array_unshift($aValors, 'Tots');
		$frm->Filtre->AfegeixLlista('custodia', 'Custòdia', 40, $aClaus, $aValors);
		
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT 
				DV.*, 
				D.*, ".
				SQL::CreaCase('estudi', self::ESTUDI)." AS estudi, ".
				SQL::CreaCase('subestudi', self::SUBESTUDI)." AS subestudi, ".
				SQL::CreaCase('categoria', self::CATEGORIA)." AS categoria, ".
				SQL::CreaCase('solicitant', self::SOLICITANT)." AS solicitant, ".
				SQL::CreaCase('lliurament', self::LLIURAMENT_CUSTODIA)." AS lliurament, ".
				SQL::CreaCase('custodia', self::LLIURAMENT_CUSTODIA)." AS custodia ".
				"
			FROM DOCUMENT_VERSIO DV
			LEFT JOIN DOCUMENT D ON (D.document_id=DV.document_id)
			WHERE estat='A' AND versio=(SELECT MAX(versio) FROM DOCUMENT_VERSIO DV2 WHERE DV.document_id=DV2.document_id)
			GROUP BY DV.document_id
		";
		return $SQL;
	}	
	
	public function EscriuFormulariFitxa() {
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Impresos de funcionament';
		$frm->Taula = 'DOCUMENT';
		$frm->ClauPrimaria = 'document_id';
		$frm->AutoIncrement = true;
		$frm->Id = $this->Id;
		$frm->AfegeixText('document_id', 'Id', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('document', 'Document', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('solicitant', 'Sol.licitant', 50);
		$frm->AfegeixText('lliurament', 'Lliurament', 50);
		$frm->AfegeixText('custodia', 'Custòdia', 50);
		$frm->AfegeixText('filtre', 'Filtre', 50);
		$frm->AfegeixTextArea('observacions', 'Observacions', 200, 5);
		$frm->EscriuHTML();
	}
}

 ?>