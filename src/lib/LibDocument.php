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
/*	function __construct($conn = null, $user = null, $system = null) {
		// Usuaris que poden instanciar aquesta classe
		$Professor = new Professor($conn, $user, $system);
		Seguretat::ComprovaAccessUsuari($user, ['SU', 'DI', 'CE'], $Professor->EstaAQualitat());
		parent::__construct($conn, $user, $system);
	}*/

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
		$frm->Camps = 'nom, solicitant, lliurament, custodia, filtre';
		$frm->Descripcions = 'Nom, Sol·licitant, Lliurament, Custòdia, Filtre';
		if ($this->Usuari !== null) {
			if ($this->Usuari->es_admin || $this->Usuari->es_direccio ||$this->Usuari->es_cap_estudis || $Professor->EstaAQualitat()) {
				$frm->PermetEditar = true;
				$frm->URLEdicio = 'Fitxa.php?accio=Document';
				$frm->PermetAfegir = true;
			}
		}
		$frm->AfegeixSuggeriment('observacions');
		$frm->AfegeixEnllacImatge('document', FormRecerca::tiPDF);
		$frm->Filtre->AfegeixLlista('solicitant', 'Sol·licitant', 30, array('', 'Tutor', 'Alumne'), array('Tots', 'Tutor', 'Alumne'));
		$frm->Filtre->AfegeixLlista('lliurament', 'Lliurament', 30, array('', 'Tutor', 'Tutor FCT', 'Secretaria', "Cap d'estudis"), array('Tots', 'Tutor', 'Tutor FCT', 'Secretaria', "Cap d'estudis"));
		$frm->Filtre->AfegeixLlista('filtre', 'Filtre', 30, array('', 'Tutor', 'Tutor FCT'), array('Tots', 'Tutor', 'Tutor FCT'));
		
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT *
			FROM DOCUMENT
			WHERE (0=0)
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