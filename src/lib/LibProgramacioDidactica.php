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
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibForms.php');
//require_once(ROOT.'/lib/LibHTML.php');


/**
 * Classe que encapsula el formulari de la programació didàctica.
 */
class ProgramacioDidactica extends Form
{
	// Seccions de la programació didàctica.
	const pdESTRATEGIES = 1;
	const pdCRITERIS = 2;
	const pdRECURSOS = 3;
	const pdSEQUENCIACIO = 4;
	const pdUNITATS = 5;

	/**
	* Identificador del modul del pla d'estudi.
	* @var integer
	*/    
    public $Id = -1; 
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		if ($this->Id < 0)
			die('Cal indicar una programació.');
		
		CreaIniciHTML($this->Usuari, "Programació didàctica");
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/ProgramacioDidactica.js?v1.1" type="text/javascript"></script>';

		// Inicialització de l'ajuda
		// https://getbootstrap.com/docs/4.0/components/popovers/
//		echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

		$this->Carrega();
//		echo '<ARTICLE class="sheet text-normal text-left medium" style="padding-bottom: 170px;" lang="ca">';
		echo $this->GeneraTitol();
		echo '<ARTICLE class="sheet" lang="ca">';
		echo $this->GeneraSeccio(self::pdESTRATEGIES);
		echo $this->GeneraSeccio(self::pdCRITERIS);
		echo $this->GeneraSeccio(self::pdRECURSOS);
		echo $this->GeneraSeccio(self::pdSEQUENCIACIO);
		echo $this->GeneraSeccio(self::pdUNITATS);
		echo '</ARTICLE>';
		CreaFinalHTML();
	}	

	/**
	 * Crea la sentència SQL.
	 * @param integer $ModulPlaEstudiId Identificador del mòdul del pla d'estudis.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $ModulPlaEstudiId): string {
		return "
			SELECT 
				MPE.nom AS NomMP, CPE.nom AS NomCF, AA.nom AS NomAA,
				MPE.codi AS CodiMP, CPE.codi AS CodiCF,
				MPE.*, CPE.*, AA.* 
			FROM MODUL_PLA_ESTUDI MPE
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
			WHERE MPE.modul_pla_estudi_id=$ModulPlaEstudiId	
		";
	}

	/**
	 * Carrega els registres especificat a la SQL i els posa en un objecte.
	 * @return void.
	 */				
	private function Carrega() {
		$SQL = $this->CreaSQL($this->Id);
//print_r($SQL);		
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$this->Registre = $ResultSet->fetch_object();
//print_h($this->Registre);
		}
	}
	
	/**
	 * Obté la llista de professors que imparteixen un mòdul.
	 * @param integer $ModulPlaEstudiId Identificador del mòdul del pla d'estudis.
	 * @return string Llista de professors.
	 */
	private function ObteProfessorsModul(int $ModulPlaEstudiId): string {
		$sRetorn = '';
		$SQL = "
			SELECT DISTINCT(professor_id), U.cognom1, U.cognom2, U.nom, FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS Nom
			FROM PROFESSOR_UF PUF
			LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PUF.professor_id)
			WHERE MPE.modul_pla_estudi_id=$ModulPlaEstudiId	
			ORDER BY U.cognom1, U.cognom2, U.nom
		";
		$ResultSet = $this->Connexio->query($SQL);
		while($row = $ResultSet->fetch_object()) {
			$sRetorn .= $row->Nom.', ';
		}
		$sRetorn = substr($sRetorn, 0, -2); // Treiem la darrera coma
		return $sRetorn;
	}		
	
	/**
	 * Genera el títol de la programació didàctica.
	 * @return string Codi HTML amb el títol de la programació didàctica.
	 */
	private function GeneraTitol() {
		$sRetorn = '<DIV id=titol>';
		$Dades = array(
			'Nom del Cicle Formatiu' => $this->Registre->NomCF,
			'Curs' => $this->Registre->any_inici.'-'.$this->Registre->any_final,
			'Codi del Mòdul Professional' => $this->Registre->CodiMP,
			'Títol del Mòdul Professional' => $this->Registre->NomMP,
			'Professors' => $this->ObteProfessorsModul($this->Id)
		);
		if ($this->Usuari->es_admin)
			$Dades = array("Id" => $this->Id) + $Dades;
		$sRetorn .= CreaTaula1($Dades);		
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}	
	
	/**
	 * Genera la secció especificada de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	private function GeneraSeccio($SeccioId) {
		switch ($SeccioId) {
			case self::pdESTRATEGIES:
				return $this->GeneraSeccioEstrategies($SeccioId);
				break;
			case self::pdCRITERIS:
				return $this->GeneraSeccioCriteris($SeccioId);
				break;
			case self::pdRECURSOS:
				return $this->GeneraSeccioRecursos($SeccioId);
				break;
			case self::pdSEQUENCIACIO:
				return $this->GeneraSeccioSequenciacio($SeccioId);
				break;
			case self::pdUNITATS:
				return $this->GeneraSeccioUnitats($SeccioId);
				break;
		}
	}
	
	/**
	 * Genera la secció d'estratègies de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	private function GeneraSeccioEstrategies($SeccioId){
		$sRetorn = "<DIV id=seccio$SeccioId>";
		$sRetorn .= "<H2>".$SeccioId.". Estratègies metodològiques</H2>";
		$sRetorn .= $this->Registre->metodologia;
		$sRetorn .= "</DIV>";
		return $sRetorn;		
	}

	/**
	 * Genera la secció de criteris de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	private function GeneraSeccioCriteris($SeccioId){
		$sRetorn = "<DIV id=seccio$SeccioId>";
		$sRetorn .= "<H2>".$SeccioId.". Criteris d’avaluació, qualificació i recuperació</H2>";
		$sRetorn .= $this->Registre->criteris_avaluacio;
		$sRetorn .= "</DIV>";
		return $sRetorn;		
	}

	/**
	 * Genera la secció de recursos de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	private function GeneraSeccioRecursos($SeccioId){
		$sRetorn = "<DIV id=seccio$SeccioId>";
		$sRetorn .= "<H2>".$SeccioId.". Recursos i material utilitzat</H2>";
		$sRetorn .= $this->Registre->recursos;
		$sRetorn .= "</DIV>";
		return $sRetorn;		
	}

	/**
	 * Genera la secció de la sequenciació i temporització de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	private function GeneraSeccioSequenciacio($SeccioId){
		$ModulPlaEstudiId = $this->Id;
		
		$sRetorn = "<DIV id=seccio$SeccioId>";
		$sRetorn .= "<H2>".$SeccioId.". Seqüenciació i temporització de les unitats formatives</H2>";
		$sRetorn .= "<BR>";

		$sRetorn .= "<TABLE BORDER=1>";
		$sRetorn .= "<TR STYLE='background-color:lightgrey;'>";
		$sRetorn .= "<TH>Unitat formativa</TH>";
		$sRetorn .= "<TH>Hores</TH>";
		$sRetorn .= "<TH>Data inici</TH>";
		$sRetorn .= "<TH>Data fi</TH>";
		$sRetorn .= "</TR>";
		
		$SQL = "
			SELECT UPE.* 
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			WHERE MPE.modul_pla_estudi_id=$ModulPlaEstudiId	
		";
		$ResultSet = $this->Connexio->query($SQL);
		while($row = $ResultSet->fetch_object()) {
			$sRetorn .= "<TR>";
			$sRetorn .= "<TD>".utf8_encode($row->nom)."</TD>";
			$sRetorn .= "<TD style='text-align:center;'>".$row->hores."</TD>";
			$sRetorn .= "<TD>".MySQLAData($row->data_inici)."</TD>";
			$sRetorn .= "<TD>".MySQLAData($row->data_final)."</TD>";
			$sRetorn .= "</TR>";
		}

		$sRetorn .= "</TABLE>";
		$sRetorn .= "<BR>";

		$sRetorn .= "</DIV>";
		return $sRetorn;		
	}

	/**
	 * Genera la secció d'unitats formatives de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	private function GeneraSeccioUnitats($SeccioId){
		$ModulId = $this->Registre->modul_professional_id;
		
		$sRetorn = "<DIV id=seccio$SeccioId>";
		$sRetorn .= "<H2>".$SeccioId.". Unitats formatives</H2>";
		
		$RA = new ResultatsAprenentatge($this->Connexio, $this->Usuari);
		$sRetorn .= $RA->GeneraTaulaModul($ModulId);

		$sRetorn .= "</DIV>";
		return $sRetorn;		
	}
}

/**
 * Classe que encapsula el formulari de recerca de les programacions didàctiques.
 */
class ProgramacioDidacticaRecerca extends FormRecerca
{
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$Usuari = $this->Usuari;
		$frm->Modalitat = $this->Modalitat;
		$frm->Titol = 'Programacions didàctiques';
		$frm->SQL = 'SELECT '.
			' 	MPE.modul_pla_estudi_id, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores, '.
			'	CPE.codi AS CodiCF '. 
			' FROM MODUL_PLA_ESTUDI MPE '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) ';
		$frm->Taula = 'UNITAT_PLA_ESTUDI';
		$frm->ClauPrimaria = 'modul_pla_estudi_id';
		$frm->Camps = 'CodiCF, CodiMP, NomMP, hores';
		$frm->Descripcions = 'Cicle, Codi, Mòdul professional, Hores';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'FPFitxa.php?accio=ProgramacioDidactica';
		$frm->AfegeixOpcio('Programació didàctica', 'FPFitxa.php?accio=ProgramacioDidacticaLectura&Id=', '', 'report.svg');

		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis) {
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
			$AnyAcademicId = $aAnys[0][0]; 
			$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		}
		$frm->Filtre->AfegeixLlista('CPE.codi', 'Cicle', 30, array('', 'APD', 'CAI', 'DAM', 'FIP', 'SMX', 'HBD', 'FPB'), array('Tots', 'APD', 'CAI', 'DAM', 'FIP', 'SMX', 'HBD', 'FPB'));
		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula el formulari de fitxa de les programacions didàctiques.
 */
class ProgramacioDidacticaFitxa extends FormRecerca
{
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		
/*		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		if (!$Usuari->es_admin)
			header("Location: Surt.php"); */

		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = "Programació didàctica";
		$frm->Taula = 'MODUL_PLA_ESTUDI';
		$frm->ClauPrimaria = 'modul_pla_estudi_id';
		$frm->Id = $this->Id;
		$frm->AfegeixText('codi', 'Codi', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixTextRic('metodologia', 'Metodologia', 200, 100);
		$frm->AfegeixTextRic('criteris_avaluacio', "Criteris d'avaluació", 200, 100);
		$frm->AfegeixTextRic('recursos', 'Recursos', 200, 100);
		$frm->EscriuHTML();		
		
	}
}

/**
 * Formulari que encapsula els resultats d'aprenentatge.
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
	 * Genera la llista amb els RA d'un cicle.
     * @return string Llista amb les dades.
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

	/**
	 * Crea la sentència SQL.
	 * @param integer $Modul Identificador del mòdul.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQLModul(int $ModulId): string {
		// Es suposa que no hi ha més de 9 RA (pel LEFT de l'ORDER)
		return "
			SELECT * 
			FROM (
				SELECT 'R' AS Tipus, 
					MP.modul_professional_id, MP.codi AS CodiMP, MP.nom AS NomMP,
					UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.nivell, UF.hores AS HoresUF, UF.activa, UF.es_fct AS FCT, 
					RA.resultat_aprenentatge_id AS DescripcioId, RA.descripcio AS Descripcio,
					CAV.criteri_avaluacio_id AS Descripcio2Id, CAV.descripcio AS Descripcio2
				FROM MODUL_PROFESSIONAL MP 
				LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id)
				LEFT JOIN RESULTAT_APRENENTATGE RA ON (RA.unitat_formativa_id=UF.unitat_formativa_id)
				LEFT JOIN CRITERI_AVALUACIO CAV ON (CAV.resultat_aprenentatge_id=RA.resultat_aprenentatge_id)
				WHERE MP.modul_professional_id=$ModulId            
				UNION
				SELECT 'C' AS Tipus, 
					MP.modul_professional_id, MP.codi AS CodiMP, MP.nom AS NomMP,
					UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.nivell, UF.hores AS HoresUF, UF.activa, UF.es_fct AS FCT, 
					CUF.contingut_uf_id AS DescripcioId, CUF.descripcio AS Descripcio,
					SCUF.subcontingut_uf_id AS Descripcio2Id, SCUF.descripcio AS Descripcio2
				FROM MODUL_PROFESSIONAL MP 
				LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id)
				LEFT JOIN CONTINGUT_UF CUF ON (CUF.unitat_formativa_id=UF.unitat_formativa_id)
				LEFT JOIN SUBCONTINGUT_UF SCUF ON (SCUF.contingut_uf_id=CUF.contingut_uf_id)
				WHERE MP.modul_professional_id=$ModulId
			) AS T
			ORDER BY modul_professional_id, unitat_formativa_id, left(Descripcio, 1), Tipus DESC, Descripcio2Id
		";		
	}
	
	/**
	 * Crea un registre amb els resultats d’aprenentatge, criteris d’avaluació i continguts d'un mòdul.
	 * @param integer $ModulId Identificador del mòdul.
	 */
	public function CreaRegistreModul(int $ModulId) {
		$this->Registre = [];
		$SQL = $this->CreaSQLModul($ModulId);
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$UnitatFormativaId = -1;
			$DescripcioId = -1;
			$Tipus = '';

			while($row = $ResultSet->fetch_object()) {
				if ($row->unitat_formativa_id !== $UnitatFormativaId) {
					// UF nova
					$UF = new stdClass();
					array_push($this->Registre, $UF);
					$UF->Id = $row->unitat_formativa_id;
					$UF->Nom = utf8_encode($row->NomUF);
					$UF->Dades = [];
					
					$UnitatFormativaId = $row->unitat_formativa_id;
				}
				
				if (($row->DescripcioId !== $DescripcioId) || ($row->Tipus !== $Tipus)) {
					// RA o contingut nou	
					$Dades = new stdClass();
					array_push($UF->Dades, $Dades);
					$Dades->Id = $row->DescripcioId;
					$Dades->Nom = utf8_encode($row->Descripcio);
					$Dades->Tipus = $row->Tipus;
					$Dades->Dades = [];

					$DescripcioId = $row->DescripcioId;							
					$Tipus = $row->Tipus;							
				}
				
				array_push($Dades->Dades, utf8_encode($row->Descripcio2));
			}
		}
//print_h($this->Registre);
	}	
	
	/**
	 * Genera la taula amb els RA d'un mòdul.
	 * @param integer $ModulId Identificador del mòdul.
     * @return string Taula amb les dades.
	 */
	public function GeneraTaulaModul(int $ModulId): string {
		$sRetorn = '';
		$this->CreaRegistreModul($ModulId);
		foreach ($this->Registre as $UF) {
			$sRetorn .= '<table border=1>';
			$sRetorn .= "<tr style='background-color:grey;'>";
			$sRetorn .= '<th>'.$UF->Nom.'</th>';
			$sRetorn .= '</tr>';
			foreach ($UF->Dades as $Dades) {
				if ($Dades->Tipus == 'R') {
					$sRetorn .= "<tr style='background-color:grey;'>";
					$sRetorn .= '<th>RA'.$Dades->Nom.'</th>';
					$sRetorn .= '</tr>';
					$sRetorn .= "<tr style='background-color:lightgrey;'>";
					$sRetorn .= '<th>Resultats d’aprenentatge i criteris d’avaluació</th>';
					$sRetorn .= '</tr>';
				}
				else if ($Dades->Tipus == 'C') {
					$sRetorn .= "<tr style='background-color:lightgrey;'>";
					$sRetorn .= '<th>Continguts</th>';
					$sRetorn .= '</tr>';
					$sRetorn .= "<tr>";
					$sRetorn .= '<td>'.$Dades->Nom.'</td>';
					$sRetorn .= '</tr>';
				}
				foreach ($Dades->Dades as $Dades2) {
					$sRetorn .= "<tr>";
					$sRetorn .= '<td>'.$Dades2.'</td>';
					$sRetorn .= '</tr>';
				}
			}
			$sRetorn .= '</table>';
			$sRetorn .= '<br>';
		}
		return $sRetorn;
	}	
}

?>