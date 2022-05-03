<?php

/**
 * LibMaterial.php
 *
 * Llibreria d'utilitats per al material.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibForms.php');

/**
 * Classe que encapsula les utilitats per al maneig del tipus de material.
 */
class TipusMaterial extends Objecte
{
	/**
	 * Genera el formulari de la recerca de material.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->AfegeixJavaScript('Matricula.js?v1.2');
		$frm->Titol = 'Tipus de material';
		$frm->SQL = $this->CreaSQL();
		$frm->Taula = 'TIPUS_MATERIAL';
		$frm->ClauPrimaria = 'tipus_material_id';
		$frm->Camps = 'tipus_material_id, nom';
		$frm->Descripcions = 'Id, Nom';
		if ($this->Usuari->es_admin) {
			$frm->PermetEditar = true;
			$frm->URLEdicio = 'Fitxa.php?accio=TipusMaterial';
			$frm->PermetAfegir = true;
		}
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT *
			FROM TIPUS_MATERIAL
			WHERE (0=0)
		";
		return $SQL;
	}	
	
	public function EscriuFormulariFitxa() {
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Tipus de material';
		$frm->Taula = 'TIPUS_MATERIAL';
		$frm->ClauPrimaria = 'tipus_material_id';
		$frm->AutoIncrement = true;
		$frm->Id = $this->Id;
		$frm->AfegeixText('tipus_material_id', 'Id', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->EscriuHTML();
	}

}

/**
 * Classe que encapsula les utilitats per al maneig del material.
 */
class Material extends Objecte
{
	/**
	 * Genera el formulari de la recerca de material.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->AfegeixJavaScript('Matricula.js?v1.2');
		$frm->Titol = 'Material';
		$frm->SQL = $this->CreaSQL();
		$frm->Taula = 'MATERIAL';
		$frm->ClauPrimaria = 'material_id';
		$frm->Camps = 'material_id, Tipus, codi, nom, data_compra, bool:es_obsolet';
		$frm->Descripcions = 'Id, Tipus, Codi, Nom, Data compra, Obsolet';
		if ($this->Usuari->es_admin) {
			$frm->PermetEditar = true;
			$frm->URLEdicio = 'Fitxa.php?accio=Material';
			$frm->PermetAfegir = true;
		}
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT M.*, TM.nom AS Tipus
			FROM MATERIAL M 
			LEFT JOIN TIPUS_MATERIAL TM ON (TM.tipus_material_id=M.tipus_material_id) 
			WHERE (0=0) 
		";
		return $SQL;
	}	
	
	public function EscriuFormulariFitxa() {
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Material';
		$frm->Taula = 'MATERIAL';
		$frm->ClauPrimaria = 'material_id';
		$frm->AutoIncrement = false;
		$frm->Id = $this->Id;
		$frm->AfegeixText('codi', 'Codi', 50, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookUp('tipus_material_id', 'Classificació', 100, 'Recerca.php?accio=TipusMaterial', 'TIPUS_MATERIAL', 'tipus_material_id', 'nom');
		$frm->AfegeixData('data_compra', 'Data compra');
		$frm->AfegeixCheckBox('es_obsolet', 'Obsolet');
		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula les utilitats per al maneig de les reserves de material.
 */
class ReservaMaterial extends Objecte
{
	/**
	 * Genera el formulari de la recerca de material.
	 * @param integer $Modalitat Modalitat del formulari.
	 */
	public function EscriuFormulariRecerca($Modalitat = FormRecerca::mfLLISTA) {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->AfegeixJavaScript('Matricula.js?v1.2');
		$frm->Titol = 'Reserva de material';
		$frm->SQL = $this->CreaSQL();
		$frm->Taula = 'RESERVA_MATERIAL';
		$frm->ClauPrimaria = 'reserva_material_id';
		$frm->Camps = 'reserva_material_id, Tipus, CodiMaterial, NomMaterial, Cognom1Cognom2NomUsuari, data_sortida, data_entrada';
		$frm->Descripcions = 'Id, Tipus, Codi, Nom, Usuari, Data sortida, Data entrada';
		if ($this->Usuari->es_admin) {
			$frm->PermetEditar = true;
			$frm->URLEdicio = 'Fitxa.php?accio=ReservaMaterial';
			$frm->PermetAfegir = true;
		}
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT RM.*, 
				M.codi AS CodiMaterial, M.nom AS NomMaterial, 
				U.usuari_id as IdUsuari, U.nom AS NomUsuari, U.cognom1 AS Cognom1Usuari, U.cognom2 AS Cognom2Usuari, 
				FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS Cognom1Cognom2NomUsuari, 
				TM.nom AS Tipus
			FROM RESERVA_MATERIAL RM 
			LEFT JOIN MATERIAL M ON (M.material_id=RM.material_id) 
			LEFT JOIN TIPUS_MATERIAL TM ON (TM.tipus_material_id=M.tipus_material_id) 
			LEFT JOIN USUARI U ON (U.usuari_id=RM.usuari_id) 
			WHERE (0=0) 
		";
		return $SQL;
	}	
	
	public function EscriuFormulariFitxa() {
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Reserva de material';
		$frm->Taula = 'RESERVA_MATERIAL';
		$frm->ClauPrimaria = 'reserva_material_id';
		$frm->AutoIncrement = false;
		$frm->Id = $this->Id;
		$frm->AfegeixLookUp('material_id', 'Material', 100, 'Recerca.php?accio=Material', 'MATERIAL', 'material_id', 'codi, nom');
		$frm->AfegeixLookUp('usuari_id', 'Usuari', 100, 'UsuariRecerca.php', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
		$frm->AfegeixData('data_sortida', 'Data sortida');
		$frm->AfegeixData('data_entrada', 'Data entrada');
		$frm->EscriuHTML();
	}
}

?>
