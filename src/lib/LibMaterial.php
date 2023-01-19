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
		$frm->Camps = 'codi, nom, Tipus, Familia, Responsable, ambit, ubicacio, data_compra, bool:es_obsolet';
		$frm->Descripcions = 'Codi, Nom, Tipus, Familia, Responsable, Àmbit, Ubicació, Data compra, Obsolet';
		
		$aTMaterials = ObteCodiValorDesDeSQL($this->Connexio,'SELECT tms.tipus_material_id as tmaterial_id, tms.nom as tipus from MATERIAL as m INNER JOIN FAMILIA_FP as ffp ON m.familia_fp_id = ffp.familia_fp_id INNER JOIN TIPUS_MATERIAL as tms ON m.tipus_material_id = tms.tipus_material_id INNER JOIN USUARI as u ON m.responsable_id = u.usuari_id GROUP BY tipus, tmaterial_id', "tmaterial_id", "tipus");
		array_unshift($aTMaterials[0] , '');
		array_unshift($aTMaterials[1] , 'Tots');
		$frm->Filtre->AfegeixLlista('TM.tipus_material_id', 'Tipus', 32, $aTMaterials[0], $aTMaterials[1]);
		$aFamilia = ObteCodiValorDesDeSQL($this->Connexio,'SELECT ffp.familia_fp_id as familiafp_id, ffp.nom as familia from MATERIAL as m INNER JOIN FAMILIA_FP as ffp ON m.familia_fp_id = ffp.familia_fp_id INNER JOIN TIPUS_MATERIAL as tm ON m.tipus_material_id = tm.tipus_material_id INNER JOIN USUARI as u ON m.responsable_id = u.usuari_id GROUP BY familia, familiafp_id', "familiafp_id", "familia");
		array_unshift($aFamilia[0] , '');
		array_unshift($aFamilia[1] , 'Tots');
		$frm->Filtre->AfegeixLlista('FFP.familia_fp_id', 'Familia', 50, $aFamilia[0], $aFamilia[1]);
		$aResponsable = ObteCodiValorDesDeSQL($this->Connexio,'SELECT u.usuari_id as responsable_id, concat(u.nom," ",u.cognom1," ",u.cognom2) as responsable from MATERIAL as m INNER JOIN FAMILIA_FP as ffp ON m.familia_fp_id = ffp.familia_fp_id INNER JOIN TIPUS_MATERIAL as tm ON m.tipus_material_id = tm.tipus_material_id INNER JOIN USUARI as u ON m.responsable_id = u.usuari_id GROUP BY responsable, responsable_id', "responsable_id", "responsable");
		array_unshift($aResponsable[0] , '');
		array_unshift($aResponsable[1] , 'Tots');
		$frm->Filtre->AfegeixLlista('U.usuari_id', 'Responsable', 60, $aResponsable[0], $aResponsable[1]);
		$frm->Filtre->AfegeixLlista('M.es_obsolet', 'Obsolet', 15, array('', '0', '1'), array('Tots', 'No', 'Si'));

		if ($this->Usuari->es_admin) {
			$frm->PermetEditar = true;
			$frm->URLEdicio = 'Fitxa.php?accio=Material';
			$frm->PermetAfegir = true;
			$frm->PermetSuprimir = true;
			$frm->PermetDuplicar = true;
		}
		$frm->EscriuHTML();
	}
	
	/**
	 * Crea la SQL pel formulari de la recerca de material.
     * @return string Sentència SQL.
	 */
	private function CreaSQL() {
		$SQL = "
			SELECT 
				M.material_id, M.codi AS codi, M.nom AS nom, M.ambit, M.ubicacio, M.data_compra, M.es_obsolet, 
				TM.nom AS Tipus, FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS Responsable, FFP.nom AS Familia
			FROM MATERIAL M 
			LEFT JOIN TIPUS_MATERIAL TM ON (TM.tipus_material_id=M.tipus_material_id) 
			LEFT JOIN FAMILIA_FP FFP ON (FFP.familia_fp_id=M.familia_fp_id) 
			LEFT JOIN USUARI U ON (U.usuari_id=M.responsable_id) 
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
		$frm->AfegeixLookUp('familia_fp_id', 'Família FP', 100, 'FPRecerca.php?accio=Families', 'FAMILIA_FP', 'familia_fp_id', 'nom');
		$frm->AfegeixLookUp('responsable_id', 'Responsable', 100, 'UsuariRecerca.php?accio=Professors', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
		$frm->AfegeixText('ambit', 'Àmbit', 100);
		$frm->AfegeixText('ubicacio', 'Ubicació', 100);
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
