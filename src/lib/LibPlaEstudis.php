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
		$sRetorn .= "<TH>Modul</TH>";
		$sRetorn .= "<TH>Hores</TH>";
		$sRetorn .= "<TH>Hores setmana</TH>";
		$sRetorn .= "<TH></TH>";
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
					$sRetorn .= "<TD>".utf8_encode($CodiMP.'. '.$NomMP)."</TD>";
					$sRetorn .= "<TD>".$Modul->Registre->HoresMP."</TD>";
					$sRetorn .= "<TD>".$Modul->Registre->HoresMPSetmana."</TD>";
					if ($this->Usuari->es_admin) {
						$URL = "FPFitxa.php?accio=ModulsProfessionalsPlaEstudis&Id=".$Modul->Registre->modul_pla_estudi_id;
						$sRetorn .= "<TD width=15><A href='".GeneraURL($URL)."'><IMG src=img/edit.svg></A></TD>";
					}
					else 
						$sRetorn .= "<TD></TD>";
					$bPrimer = False;
				}
				else {
					$sRetorn .= "<TD></TD><TD></TD><TD></TD><TD></TD>";
				}
				$sRetorn .= "<TD>".utf8_encode($NomUF)."</TD>";
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
				MPE.nom AS NomMP, MPE.codi AS CodiMP, MPE.hores AS HoresMP, MPE.hores_setmana AS HoresMPSetmana, 
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

?>