<?php

/** 
 * LibPlaEstudis.php
 *
 * Llibreria d'utilitats per al pla d'estudis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibHTML.php');


/**
 * Classe que encapsula les utilitats per al maneig del pla d'estudis.
 */
abstract class PlaEstudis extends Form
{
	/**
	* Registre carregat amb Carrega.
	* @var object
	*/    
	protected $Registre = NULL;
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, $this->Titol);
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/PlaEstudis.js?v1.2" type="text/javascript"></script>';

		// Inicialització de l'ajuda
		// https://getbootstrap.com/docs/4.0/components/popovers/
		echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

		echo $this->GeneraFiltre();
		echo '<BR><BR>';
		echo $this->GeneraAcordio();
		CreaFinalHTML();
	}	

	abstract protected function GeneraFiltre();
	abstract protected function GeneraAcordio();
	abstract protected function CreaSQL(int $id): string;
	abstract protected function Carrega(int $id);
	
	/**
	 * Genera la taula per a un any i cicle concret.
     * @return string Taula amb les dades.
	 */
	protected function GeneraTaula($Nivell1): string {
		$sRetorn = '<TABLE class="table table-striped table-sm table-hover">';
		$sRetorn .= '<thead class="thead-dark">';
		$sRetorn .= "<TH>Mòdul</TH>";
		$sRetorn .= "<TH>Hores</TH>";
		$sRetorn .= "<TH>Hores setmana</TH>";
		$sRetorn .= "<TH></TH><TH></TH>";
		$sRetorn .= "<TH>Unitat formativa</TH>"; 
		$sRetorn .= "<TH>Hores</TH>";
		$sRetorn .= "<TH></TH>";
		$sRetorn .= '</thead>';			
		for($j = 0; $j < count($Nivell1->Modul); $j++) {
			$Modul = $Nivell1->Modul[$j];
			$CodiMP = $Modul->Registre->CodiMP;
			$NomMP = $Modul->Registre->NomMP;
			$bPrimer = True;
			for($k = 0; $k < count($Modul->Unitat); $k++) {				
				$Unitat = $Modul->Unitat[$k];
				$CodiUF = $Unitat->Registre->CodiUF;
				$NomUF = $Unitat->Registre->NomUF;
				$sRetorn .= "<TR>";
				if ($bPrimer) {
					$Id = ($this->Usuari->es_admin	) ? "[".$Modul->Registre->modul_pla_estudi_id."]" : "";
					$sRetorn .= "<TD>".utf8_encode($CodiMP.'. '.$NomMP)." $Id</TD>";
					$sRetorn .= "<TD>".$Modul->Registre->HoresMP."</TD>";
					$sRetorn .= "<TD>".$Modul->Registre->HoresMPSetmana."</TD>";
					if ($this->Usuari->es_admin) {
						$URL = "FPFitxa.php?accio=ModulsProfessionalsPlaEstudis&Id=".$Modul->Registre->modul_pla_estudi_id;
						$sRetorn .= "<TD width=15><A href='".GeneraURL($URL)."'><IMG src=img/edit.svg></A></TD>";
						$URL = "FPFitxa.php?accio=ProgramacioDidacticaLectura&Id=".$Modul->Registre->modul_pla_estudi_id;
						$sRetorn .= "<TD width=15><A href='".GeneraURL($URL)."'><IMG src=img/report.svg></A></TD>";
					}
					else 
						$sRetorn .= "<TD></TD><TD></TD>";
					$bPrimer = False;
				}
				else {
					$sRetorn .= "<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>";
				}
				$Id = ($this->Usuari->es_admin	) ? "[".$Unitat->Registre->unitat_pla_estudi_id."]" : "";
				$sRetorn .= "<TD>".utf8_encode($NomUF)." $Id</TD>";
				$sRetorn .= "<TD>".$Unitat->Registre->HoresUF."</TD>";
				if ($this->Usuari->es_admin) {
					$URL = "FPFitxa.php?accio=UnitatsFormativesPlaEstudis&Id=".$Unitat->Registre->unitat_pla_estudi_id;
					$sRetorn .= "<TD width=15 align=left><A href='".GeneraURL($URL)."'><IMG src=img/edit.svg></A></TD>";
				}
				else 
					$sRetorn .= "<TD></TD>";
				$sRetorn .= "</TR>";
			}
		}
		$sRetorn .= "</TABLE>";
		return $sRetorn;			
	}	
	
	/**
	 * Genera un bloc per a l'acordió.
	 * @param string $Codi Codi del bloc.
	 * @param string $Nom Nom del bloc.
	 * @param string $Taula Contingut del bloc.
     * @return string HTML del bloc de l'acordió.
	 */
	protected function GeneraBlocAcordio($Codi, $Nom, $Taula): string {
		$sRetorn = '  <div class="card">';
		$sRetorn .= '    <div class="card-header" id="'.$Codi.'">';
		$sRetorn .= '      <h5 class="mb-0">';
		$sRetorn .= '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$Codi.'" aria-expanded="true" aria-controls="collapse'.$Codi.'">';
		$sRetorn .= utf8_encode($Nom);
		$sRetorn .= '        </button>';
		$sRetorn .= '      </h5>';
		$sRetorn .= '    </div>';
		$sRetorn .= '    <div id="collapse'.$Codi.'" class="collapse" aria-labelledby="'.$Codi.'" data-parent="#accordionExample">';
		$sRetorn .= '      <div class="card-body">';		
		$sRetorn .= $Taula;
		$sRetorn .= '      </div>';
		$sRetorn .= '    </div>';
		$sRetorn .= '  </div>';				
		return $sRetorn;
	}	
}

/**
 * Classe que encapsula les utilitats per al maneig del pla d'estudis.
 */
class PlaEstudisAny extends PlaEstudis
{
	/**
	* Identificador de l'any acadèmic.
	* @var integer
	*/    
    public $AnyAcademicId = -1; 

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 */
	function __construct($con, $user) {
		parent::__construct($con, $user);
		$this->Titol = "Pla d'estudis per any";
	}

	/**
	 * Crea la sentència SQL.
	 * @param integer $AnyAcademicId Identificador de l'any acadèmic.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $AnyAcademicId): string {
		return "
			SELECT 
				UPE.nom AS NomUF, UPE.codi AS CodiUF, UPE.hores AS HoresUF,
				MPE.modul_pla_estudi_id, MPE.nom AS NomMP, MPE.codi AS CodiMP, MPE.hores AS HoresMP, MPE.hores_setmana AS HoresMPSetmana, 
				CPE.nom AS NomCF, CPE.codi AS CodiCF, 
				CPE.*, MPE.*, UPE.*
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			WHERE any_academic_id=$AnyAcademicId
			ORDER BY CPE.codi, MPE.codi, UPE.codi
		";
	}

	/**
	 * Genera el filtre del formulari si n'hi ha.
	 */
	protected function GeneraFiltre() {
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$this->AnyAcademicId = $aAnys[0][0]; 
		return $this->CreaLlista('any_academic_id', 'Any', 200, $aAnys[0], $aAnys[1], $this->AnyAcademicId, 'onchange="ActualitzaTaulaPlaEstudisAny(this);"');
	}
	
	/**
	 * Carrega els registres especificat a la SQL i els posa en un objecte.
	 * Nivell1 és el cicle formatiu.
	 * @param integer $AnyAcademicId Identificador de l'any acadèmic.
	 */				
	protected function Carrega(int $AnyAcademicId) {
		$CicleFormatiuId = -1;
		$ModulProfessionalId = -1;
		$SQL = $this->CreaSQL($AnyAcademicId);
//print_r($SQL);		
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			// Creem un objecte per administrar els cicles
			$obj = new stdClass();
			$i = -1; 
			$j = -1;
			$k = -1;
			while($row = $ResultSet->fetch_object()) {
				if ($row->cicle_formatiu_id !== $CicleFormatiuId) {
					// Cicle nou
					$CicleFormatiuId = $row->cicle_formatiu_id;
					$i++;
					$obj->Nivell1[$i] = new stdClass();
					$obj->Nivell1[$i]->Registre = $row;
					$j = -1; 
				}
				if ($row->modul_professional_id !== $ModulProfessionalId) {
					// Mòdul nou
					$ModulProfessionalId = $row->modul_professional_id;
					$j++;
					$obj->Nivell1[$i]->Modul[$j] = new stdClass();
					$obj->Nivell1[$i]->Modul[$j]->Registre = $row;
					$k = -1;
				}
				$k++;
				$obj->Nivell1[$i]->Modul[$j]->Unitat[$k] = new stdClass();
				$obj->Nivell1[$i]->Modul[$j]->Unitat[$k]->Registre = $row;
			}
//print_h($obj);			
			$this->Registre = $obj;
		}
	}		

	/**
	 * Genera una acordió (component Bootstrap) amb el resultat de la SQL.
     * @return string Acordió amb les dades.
	 */
	public function GeneraAcordio() {
		$this->Carrega($this->AnyAcademicId);
		$obj = $this->Registre;
		$sRetorn = '<DIV id=taula>';
		$sRetorn .= '<div class="accordion" id="accordionExample">';
		for($i = 0; $i < count($obj->Nivell1); $i++) {
			$Cicle = $obj->Nivell1[$i];
			$CodiCF = $Cicle->Registre->CodiCF;
			$NomCF = $Cicle->Registre->NomCF;
			$Taula = $this->GeneraTaula($Cicle);
			$sRetorn .= $this->GeneraBlocAcordio($CodiCF, $NomCF, $Taula);
		}
		$sRetorn .= '</div>';
		$sRetorn .= '</DIV>';
		return $sRetorn;	
	}
}

/**
 * Classe que encapsula les utilitats per al maneig del pla d'estudis.
 */
class PlaEstudisCicle extends PlaEstudis
{
	/**
	* Identificador del cicle.
	* @var integer
	*/    
    public $CicleFormatiuId = -1; 

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 */
	function __construct($con, $user) {
		parent::__construct($con, $user);
		$this->Titol = "Pla d'estudis per cicle";
	}	

	/**
	 * Crea la sentència SQL.
	 * @param integer $CicleFormatiuId Identificador del cicle.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $CicleFormatiuId): string {
		return "
			SELECT 
				UPE.nom AS NomUF, UPE.codi AS CodiUF, UPE.hores AS HoresUF,
				MPE.nom AS NomMP, MPE.codi AS CodiMP, MPE.hores AS HoresMP, MPE.hores_setmana AS HoresMPSetmana, 
				CPE.nom AS NomCF, CPE.codi AS CodiCF, 
				AA.nom AS NomCurs,
				CPE.*, MPE.*, UPE.*, AA.*
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
			WHERE cicle_formatiu_id=$CicleFormatiuId
			ORDER BY AA.any_inici DESC, CPE.codi, MPE.codi, UPE.codi
		";		
	}
	
	/**
	 * Genera el filtre del formulari si n'hi ha.
	 */
	protected function GeneraFiltre() {
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$this->CicleFormatiuId = $aCicles[0][0]; 
		return $this->CreaLlista('cicle_formatiu_id', 'Cicle', 800, $aCicles[0], $aCicles[1], $this->CicleFormatiuId, 'onchange="ActualitzaTaulaPlaEstudisCicle(this);"');
	}

	/**
	 * Carrega els registres especificat a la SQL i els posa en un objecte.
	 * Nivell1 és l'any acadèmic.
	 * @param integer $CicleFormatiuId Identificador del cicle.
	 */				
	protected function Carrega(int $CicleFormatiuId) {
		$AnyAcademicId = -1;
		$ModulProfessionalId = -1;
		$SQL = $this->CreaSQL($CicleFormatiuId);
//print_r($SQL);		
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			// Creem un objecte per administrar els cicles
			$obj = new stdClass();
			$i = -1; 
			$j = -1;
			$k = -1;
			while($row = $ResultSet->fetch_object()) {
				if ($row->any_academic_id !== $AnyAcademicId) {
					// Cicle nou
					$AnyAcademicId = $row->any_academic_id;
					$i++;
					$obj->Nivell1[$i] = new stdClass();
					$obj->Nivell1[$i]->Registre = $row;
					$j = -1; 
				}
				if ($row->modul_professional_id !== $ModulProfessionalId) {
					// Mòdul nou
					$ModulProfessionalId = $row->modul_professional_id;
					$j++;
					$obj->Nivell1[$i]->Modul[$j] = new stdClass();
					$obj->Nivell1[$i]->Modul[$j]->Registre = $row;
					$k = -1;
				}
				$k++;
				$obj->Nivell1[$i]->Modul[$j]->Unitat[$k] = new stdClass();
				$obj->Nivell1[$i]->Modul[$j]->Unitat[$k]->Registre = $row;
			}
//print_h($obj);			
			$this->Registre = $obj;
		}
	}

	/**
	 * Genera una acordió (component Bootstrap) amb el resultat de la SQL.
     * @return string Acordió amb les dades.
	 */
	public function GeneraAcordio() {
		$this->Carrega($this->CicleFormatiuId);
		$obj = $this->Registre;
		$sRetorn = '<DIV id=taula>';
		$sRetorn .= '<div class="accordion" id="accordionExample">';
		for($i = 0; $i < count($obj->Nivell1); $i++) {
			$AnyAcademic = $obj->Nivell1[$i];
			$CodiCurs = $AnyAcademic->Registre->any_inici.'-'.$AnyAcademic->Registre->any_final;
			$NomCurs = $AnyAcademic->Registre->NomCurs;
			$Taula = $this->GeneraTaula($AnyAcademic);
			$sRetorn .= $this->GeneraBlocAcordio($CodiCurs, $NomCurs, $Taula);
		}
		$sRetorn .= '</div>';
		$sRetorn .= '</DIV>';
		return $sRetorn;	
	}
}

/**
 * Classe que encapsula el formulari de recerca dels MP del pla d'estudis.
 */
class PlaEstudisModulRecerca extends FormRecerca
{
	/**
	 * Crea la sentència SQL.
	 * @return string Sentència SQL.
	 */
	public function CreaSQL(): string {
		$Usuari = $this->Usuari;
		$SubSQL = 'SELECT '.
			' 	UPE.unitat_pla_estudi_id, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.nivell, UPE.orientativa, '.
			' 	MPE.modul_pla_estudi_id AS modul_pla_estudi_id, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores, MPE.estat, '.
			' 	CASE MPE.estat '.
			'   	WHEN "E" THEN "Elaboració" '.
			'   	WHEN "D" THEN "Revisió cap departament" '.
			'   	WHEN "T" THEN "Revisió cap d\'estudis" '.
			'   	WHEN "A" THEN "Acceptada" '.
			' 	END AS NomEstat, '.
			'	CPE.codi AS CodiCF, FormataData(UPE.data_inici) AS data_inici, FormataData(UPE.data_final) AS data_final '. 
			' FROM UNITAT_PLA_ESTUDI UPE '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) ';
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			// És professor
			if ($Usuari->es_professor)
				$SubSQL .= ' LEFT JOIN PROFESSOR_UF PUF ON (PUF.uf_id=UPE.unitat_pla_estudi_id) '.
					' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
					' WHERE PUF.professor_id='.$Usuari->usuari_id.
					' AND AA.actual=1 ';		
		$SQL = "
			SELECT 
				modul_pla_estudi_id, CodiCF, CodiMP, NomMP, hores, estat, NomEstat
			FROM ($SubSQL) AS M
			GROUP BY modul_pla_estudi_id, CodiCF, CodiMP, NomMP
		";			
		return $SQL;
	}

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->AfegeixJavaScript('ProgramacioDidactica.js?v1.3');
		$Usuari = $this->Usuari;
		$frm->Modalitat = $this->Modalitat;
		$frm->Titol = 'Mòduls professionals';
		$frm->SQL = $this->CreaSQL();
//print '<br><br><br>'.$frm->SQL;
		$frm->Taula = 'MODUL_PLA_ESTUDI';
		$frm->ClauPrimaria = 'modul_pla_estudi_id';
		$frm->Camps = 'CodiCF, CodiMP, NomMP, hores, NomEstat';
		$frm->Descripcions = 'Cicle, Codi, Mòdul professional, Hores, Estat';

		//$frm->PermetEditar = True;
		$frm->PermetEditarCondicional(['estat' => 'E']);

		$frm->URLEdicio = 'FPFitxa.php?accio=ProgramacioDidactica';
		$frm->AfegeixOpcio('Programació didàctica', 'FPFitxa.php?accio=ProgramacioDidacticaLectura&Id=', '', 'report.svg');

		$frm->AfegeixOpcioAJAX('Envia a departament', 'EnviaDepartament', '', [], '', '', ['estat' => 'E']);
	 
		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis) {
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
			$AnyAcademicId = $aAnys[0][0]; 
			$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		}
		//$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		//$CicleFormatiuId = $aCicles[0][0]; 
		//$frm->Filtre->AfegeixLlista('CPE.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);
		//$frm->Filtre->AfegeixLlista('UPE.nivell', 'Nivell', 30, array('', '1', '2'), array('Tots', '1r', '2n'));
		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula el formulari de recerca de les UF del pla d'estudis.
 */
class PlaEstudisUnitatRecerca extends FormRecerca
{
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$Usuari = $this->Usuari;
		$frm->Modalitat = $this->Modalitat;
		$frm->Titol = 'Unitats formatives';
		$frm->SQL = 'SELECT '.
			' 	UPE.unitat_pla_estudi_id, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.nivell, UPE.orientativa, '.
			' 	MPE.codi AS CodiMP, MPE.nom AS NomMP, '.
			'	CPE.codi AS CodiCF, FormataData(UPE.data_inici) AS data_inici, FormataData(UPE.data_final) AS data_final '. 
			' FROM UNITAT_PLA_ESTUDI UPE '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) ';
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			// És professor
			if ($Usuari->es_professor)
				$frm->SQL .= ' LEFT JOIN PROFESSOR_UF PUF ON (PUF.uf_id=UPE.unitat_pla_estudi_id) '.
					' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
					' WHERE PUF.professor_id='.$Usuari->usuari_id.
					' AND AA.actual=1 ';
//print '<br><br><br>'.$frm->SQL;
		$frm->Taula = 'UNITAT_PLA_ESTUDI';
		$frm->ClauPrimaria = 'unitat_pla_estudi_id';
		$frm->Camps = 'CodiCF, nivell, CodiMP, NomMP, CodiUF, NomUF, HoresUF, data_inici, data_final, bool:orientativa';
		$frm->Descripcions = 'Cicle, Nivell, Codi, Mòdul professional, Codi, Nom, Hores, Data inici, Data final, Orientativa';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'FPFitxa.php?accio=UnitatsFormativesPlaEstudis';
		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis) {
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
			$AnyAcademicId = $aAnys[0][0]; 
			$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		}
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		
		array_unshift($aCicles[0] , '');
		array_unshift($aCicles[1] , 'Tots');
		//$CicleFormatiuId = $aCicles[0][0];  ??

		$frm->Filtre->AfegeixLlista('CPE.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);
		$frm->Filtre->AfegeixLlista('UPE.nivell', 'Nivell', 30, array('', '1', '2'), array('Tots', '1r', '2n'));
		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula la fitxa de les UF del pla d'estudis.
 */
class PlaEstudisUnitatFitxa extends FormFitxa
{
	/**
	 * Comprova si la unitat formativa és LOGSE, és a dir, un crèdit.
	 */
	private function EsLOGSE() {
		$Retorn = False;
		$SQL = '
			SELECT CF.llei
			FROM UNITAT_PLA_ESTUDI UPE 
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 			
			LEFT JOIN CICLE_FORMATIU CF ON (CPE.cicle_formatiu_id=CF.cicle_formatiu_id)
			WHERE unitat_pla_estudi_id='.$this->Id;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_object();
			$Retorn = ($row->llei == 'LG');
		}
		return $Retorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$Opcions = [FormFitxa::offREQUERIT];
		$NomesLectura = !($this->Usuari->es_admin || $this->Usuari->es_direccio || $this->Usuari->es_cap_estudis);
		if ($NomesLectura)
			array_push($Opcions, FormFitxa::offNOMES_LECTURA);

		$this->Titol = "Edició UF Pla d'estudis";
		$this->Taula = 'UNITAT_PLA_ESTUDI';
		$this->ClauPrimaria = 'unitat_pla_estudi_id';
		$this->AfegeixText('codi', 'Codi', 20, $Opcions);
		$this->AfegeixText('nom', 'Nom', 200, $Opcions);
		$this->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offREQUERIT]);
		$this->AfegeixEnter('nivell', 'Nivell (1 o 2)', 10, $Opcions);
		$this->AfegeixData('data_inici', 'Data inici');
		$this->AfegeixData('data_final', 'Data final');
		$this->AfegeixCheckBox('es_fct', 'És FCT?', $Opcions);
		if (!$this->EsLOGSE())
			$this->AfegeixCheckBox('orientativa', 'És orientativa?');

		$this->Pestanya('Importació notes');
		$this->AfegeixLlista('lms', 'LMS', 30, array('M', 'C'), array('Moodle', 'Clasroom'), [FormFitxa::offREQUERIT]);
		$this->AfegeixLlista('metode_importacio_notes', 'Mètode importació', 30, array('F', 'W'), array('Fitxer', 'Servei web'), [FormFitxa::offREQUERIT]);
		$this->AfegeixEnter('nota_maxima', 'Nota màxima', 20, [FormFitxa::offREQUERIT]);
		$this->AfegeixLlista('nota_inferior_5', 'Nota inferior a 5', 30, array('A', 'T'), array('Arrodoneix', 'Trunca'), [FormFitxa::offREQUERIT]);
		$this->AfegeixLlista('nota_superior_5', 'Nota superior a 5', 30, array('A', 'T'), array('Arrodoneix', 'Trunca'), [FormFitxa::offREQUERIT]);
		$this->AfegeixText('categoria_moodle_importacio_notes', "Categoria Moodle per a la importació", 100);
		
		parent::EscriuHTML();		
	}
}

?>