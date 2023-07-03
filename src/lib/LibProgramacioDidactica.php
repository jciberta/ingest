<?php

/** 
 * LibProgramacioDidactica.php
 *
 * Llibreria d'utilitats per a la programació didàctica.
 *
 * @author Josep Ciberta, Jordi Planelles
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibPDF.php');
require_once(ROOT.'/vendor/autoload.php');

use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\TablePosition;

/**
 * Factoria que genera l'objecte per a la programació didàctica depenent de la llei.
 */
class ProgramacioDidacticaFactory
{
	public static function Crea($conn, $user, $system, $Id) {
		$Llei = self::ObteLlei($conn, $Id);
		switch($Llei) {
			case "LO":
				$obj = new ProgramacioDidacticaLOE($conn, $user, $system);
				break;
			case "LG":
				$obj = new ProgramacioDidacticaLOGSE($conn, $user, $system);
				break;
			default:
				throw new Exception("ProgramacioDidacticaFactory: Llei no implementada");
				break;
		}
		$obj->Id = $Id;
		return $obj;		
	}

	public static function ObteLlei($conn, $Id) {
		$SQL = "
			SELECT CF.llei
			FROM MODUL_PLA_ESTUDI MPE
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id)
			WHERE modul_pla_estudi_id=$Id;
		";
		$aConjuntRegistres = DB::CarregaConjuntRegistres($conn, $SQL);
		return $aConjuntRegistres[0]->llei;
	}	
}

/**
 * Factoria que genera l'objecte de la fitxa de la programació didàctica en DOCX depenent de la llei.
 */
class ProgramacioDidacticaFitxaFactory extends ProgramacioDidacticaFactory
{
	public static function Crea($conn, $user, $system, $Id) {
		$Llei = self::ObteLlei($conn, $Id);
		switch($Llei) {
			case "LO":
				$obj = new ProgramacioDidacticaFitxaLOE($conn, $user, $system);
				break;
			case "LG":
				$obj = new ProgramacioDidacticaFitxaLOGSE($conn, $user, $system);
				break;
			default:
				throw new Exception("ProgramacioDidacticaFitxaFactory: Llei no implementada");
				break;
		}
		$obj->Id = $Id;
		return $obj;		
	}
}

/**
 * Factoria que genera l'objecte per a l'exportació de la programació didàctica en DOCX depenent de la llei.
 */
class ProgramacioDidacticaDOCXFactory extends ProgramacioDidacticaFactory
{
	public static function Crea($conn, $user, $system, $Id) {
		$Llei = self::ObteLlei($conn, $Id);
		switch($Llei) {
			case "LO":
				$obj = new ProgramacioDidacticaDOCXLOE($conn, $user, $system);
				break;
			case "LG":
				$obj = new ProgramacioDidacticaDOCXLOGSE($conn, $user, $system);
				break;
			default:
				throw new Exception("ProgramacioDidacticaDOCXFactory: Llei no implementada");
				break;
		}
		$obj->Id = $Id;
		return $obj;		
	}
}

/**
 * Factoria que genera l'objecte per a l'exportació de la programació didàctica en DOCX depenent de la llei.
 */
class ProgramacioDidacticaPDFFactory extends ProgramacioDidacticaFactory
{
	public static function Crea($conn, $user, $system, $Id) {
		$Llei = self::ObteLlei($conn, $Id);
		switch($Llei) {
			case "LO":
				$obj = new ProgramacioDidacticaPDFLOE($conn, $user, $system);
				break;
			case "LG":
				$obj = new ProgramacioDidacticaPDFLOGSE($conn, $user, $system);
				break;
			default:
				throw new Exception("ProgramacioDidacticaPDFFactory: Llei no implementada");
				break;
		}
		$obj->Id = $Id;
		return $obj;		
	}
}

/**
 * Classe que encapsula el formulari de la programació didàctica.
 */
abstract class ProgramacioDidactica extends Form
{
	// Estats de la programació didàctica.
	const epELABORACIO = 'E'; 		// Elaboració
	const epCAP_DEPARTAMENT = 'D'; 	// Revisió cap departament
	const epCAP_ESTUDIS = 'T'; 		// Revisió cap d'estudis
	const epACCEPTADA = 'A'; 		// Acceptada (tancada)

	// Seccions de la programació didàctica.
	const pdESTRATEGIES = 1;
	const pdCRITERIS = 2;
	const pdRECURSOS = 3;
//	const pdSEQUENCIACIO = 4;
	const pdSEQUENCIACIO_LOE = 4;
	const pdSEQUENCIACIO_LOGSE = 5;
	const pdUNITATS = 6;
	const pdOBJECTIUS_CONTINGUTS = 7;

	// Títol de les seccions de la programació didàctica.
	const SECCIO = array(
		self::pdESTRATEGIES => 'Estratègies metodològiques',
		self::pdCRITERIS => 'Criteris d’avaluació, qualificació i recuperació',
		self::pdRECURSOS => 'Recursos i material utilitzat',
		self::pdSEQUENCIACIO_LOE => 'Seqüenciació i temporització de les unitats formatives',
		self::pdSEQUENCIACIO_LOGSE => 'Seqüenciació i temporització de les unitats didàctiques',
		self::pdUNITATS => 'Unitats formatives',
		self::pdOBJECTIUS_CONTINGUTS => 'Objectius i continguts'
	);

	/**
	 * Identificador del modul del pla d'estudi.
	 * @var integer
	 */    
    public $Id = -1; 

	abstract protected function GeneraSeccions();
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		if ($this->Id < 0)
			die('Cal indicar una programació.');
		
		CreaIniciHTML($this->Usuari, "Programació didàctica");
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/ProgramacioDidactica.js?v1.1" type="text/javascript"></script>';

		// Botons
		echo '<span style="float:right;">';
		if ($this->Usuari->es_admin)
			echo $this->CreaBotoEdita($this->Id).'&nbsp';
		echo $this->CreaBotoDescarrega($this->Id);
		echo '</span>';

		$this->Carrega();
		echo $this->GeneraTitol();
		echo '<ARTICLE class="sheet" lang="ca">';
		echo $this->GeneraSeccions();
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
	protected function Carrega() {
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
	protected function ObteProfessorsModul(int $ModulPlaEstudiId): string {
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
	 * Genera el nom del fitxer  de la programació didàctica.
	 * @return string Nom del fitxer.
	 */
	protected function GeneraNomFitxer() {
		$Nom = substr($this->Registre->any_inici, -2).''.substr($this->Registre->any_final, -2).'_'.
			$this->Registre->CodiCF.'_'.$this->Registre->CodiMP;
		return 'Programacio_didactica_'.$Nom;
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
	 * @param integer $Comptador Comptador de número de secció.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccio(int $SeccioId, int &$Comptador) {
		$sRetorn = "<DIV id=seccio$SeccioId>";
//		$sRetorn .= "<H2>".$SeccioId.". ".self::SECCIO[$SeccioId]."</H2>";
		$sRetorn .= "<H2>".$Comptador.". ".self::SECCIO[$SeccioId]."</H2>";
		$Comptador++;
		switch ($SeccioId) {
			case self::pdESTRATEGIES:
				$sRetorn .= $this->GeneraSeccioEstrategies();
				break;
			case self::pdCRITERIS:
				$sRetorn .= $this->GeneraSeccioCriteris();
				break;
			case self::pdRECURSOS:
				$sRetorn .= $this->GeneraSeccioRecursos();
				break;
			case self::pdSEQUENCIACIO_LOE:
			case self::pdSEQUENCIACIO_LOGSE:
				$sRetorn .= $this->GeneraSeccioSequenciacio();
				break;
			case self::pdUNITATS:
				$sRetorn .= $this->GeneraSeccioUnitats();
				break;
			case self::pdOBJECTIUS_CONTINGUTS:
				$sRetorn .= $this->GeneraSeccioObjectiusContinguts();
				break;
		}
		$sRetorn .= "</DIV>";
		return $sRetorn;
	}

	/**
	 * Dona format a les taules d'un HTML.
	 * @param string $html Codi HTML amb les taules a tractar (si n'hi ha).
	 * @return string Codi HTML amb les taules amb el format adequat.
	 */
	protected function TractaTaules($html) {
		// https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
		$html = $html ?? '';
		
		$Retorn = '';
		$i = 0;
		$HTML = strtoupper($html);
		$j = strpos($HTML, '<TABLE', $i);

		if ($j !== false) {
			while ($j > 0) {
				$Retorn .= substr($html, $i, $j-$i);
				$i = $j + 1;
				
				$j1 = strpos($HTML, '<TABLE', $i);
				$j2 = strpos($HTML, '</TABLE>', $i);
				
//				if ($j1 < $j2)
//					throw new Exception("No es permeten taules aniuades (taules dins de taules).");
				
				$Taula = substr($html, $i-1, $j2-$i+9);
//print_h($Taula);
				$Retorn .= $this->FormataTaula($Taula);
				$i = $j2 + 1;
				$j = strpos($HTML, '<TABLE', $i);
			}
			$Retorn .= substr($html, $i+7, strlen($html)-$i-7);
		}
		else 
			// No hi ha cap taula
			$Retorn = $html; 
		return $Retorn;		
	}
	
	private function FormataTaula($Taula) {
		$sRetorn = "";
		//$dom = new domDocument;
		$dom = new DOMDocument('1.0', 'UTF-8');

		// Some versions of the DOMDocument parser that PHP uses are super-strict about HTML compliance, and will whine 
		// and regularly do wrong things when confronted with spec violations.
		// This completely depends on whether the version of libxml2 you are using has support for this part of HTML5. 
		// https://bugs.php.net/bug.php?id=63477
		// https://stackoverflow.com/questions/5645536/issue-with-using-domnode-attributes-with-attributes-that-have-multiple-words-in
		$Taula = str_replace('class=""""', '', $Taula);
//print htmlspecialchars($taula);
//exit;		
//print_h($Taula);
		$dom->loadHTML($Taula); 
		$dom->preserveWhiteSpace = false; 
   
		$tables = $dom->getElementsByTagName('table'); 

		// Es suposa una única taula
		$rows = $tables->item(0)->getElementsByTagName('tr'); 

		$sRetorn .= '<TABLE cellspacing="0" cellpadding="2" border="1">';
		$PrimeraFila = true;
		$aFiles = [];
		foreach ($rows as $row) {
			if ($PrimeraFila) {
				$sRetorn .= '<THEAD>';
				$sRetorn .= '<TR STYLE="background-color:lightgrey;">';
				$cols = $row->getElementsByTagName('td'); 
				for($i=0; $i<count($cols); $i++) {
//					$Valor = utf8_decodeX($cols->item($i)->nodeValue);
					$Valor = $cols->item($i)->nodeValue;
					$Valor = utf8_decode($Valor);
					$sRetorn .= "<TH>$Valor</TH>";
				}
				$sRetorn .= "</TR>";
				$sRetorn .= "</THEAD>";
				$PrimeraFila = false;
			}
			else {
				$sRetorn .= "<TR>";
				$cols = $row->getElementsByTagName('td'); 
				for($i=0; $i<count($cols); $i++) {
//					$Valor = utf8_decodeX($cols->item($i)->nodeValue);
					$Valor = $cols->item($i)->nodeValue;
					$Valor = utf8_decode($Valor);
					$sRetorn .= "<TD>$Valor</TD>";
				}
				$sRetorn .= "</TR>";
			}
		}
		$sRetorn .= "</TABLE>";
		$sRetorn .= "<BR>";
		return $sRetorn;
	}

	/**
	 * Genera la secció d'estratègies de la programació didàctica.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioEstrategies() {
		$sRetorn = $this->Registre->metodologia;
		$sRetorn = $this->TractaTaules($sRetorn);
		return $sRetorn;		
	}

	/**
	 * Genera la secció de criteris de la programació didàctica.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioCriteris() {
		$sRetorn = $this->Registre->criteris_avaluacio;
		$sRetorn = $this->TractaTaules($sRetorn);
		return $sRetorn;		
	}

	/**
	 * Genera la secció de recursos de la programació didàctica.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioRecursos() {
		$sRetorn = $this->Registre->recursos;
		$sRetorn = $this->TractaTaules($sRetorn);
		return $sRetorn;		
	}

	abstract protected function GeneraSeccioSequenciacio(&$section = null);

	/**
	 * Genera la secció d'unitats formatives de la programació didàctica (LOE).
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioUnitats(&$section = null) {
		$ModulId = $this->Registre->modul_professional_id;
		$RA = new ResultatsAprenentatge($this->Connexio, $this->Usuari);
		$sRetorn = $RA->GeneraTaulaModul($ModulId);
		return $sRetorn;		
	}
	
	/**
	 * Genera la secció d'objectius i continguts de la programació didàctica (LOGSE).
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioObjectiusContinguts(&$section = null) {
		$ModulId = $this->Registre->modul_professional_id;
		$OC = new ObjectiusContinguts($this->Connexio, $this->Usuari);
		$sRetorn = $OC->GeneraTaulaModul($ModulId);
		return $sRetorn;		
	}
	
	/**
	 * Crea el botó per a la edició.
	 * @param string $ModulId Identificador del mòdul del cicle formatiu.
	 * @return string Codi HTML del botó.
	 */
	public function CreaBotoEdita(string $ModulId): string {
		$URL = GeneraURL("FPFitxa.php?accio=ProgramacioDidactica&Id=$ModulId");
		return $this->CreaBoto('btnEdita', 'Edita', $URL);
 	}
	
	/**
	 * Crea el botó per a la descàrrega en DOCX i ODT.
	 * @param string $ModulId Identificador del mòdul del cicle formatiu.
	 * @return string Codi HTML del botó.
	 */
	public function CreaBotoDescarrega(string $ModulId): string {
		$sRetorn = '<div class="btn-group" role="group">';
		$sRetorn .= '    <button id="btnGroupDrop1" type="button" class="btn btn-primary active dropdown-toggle" data-toggle="dropdown">';
		$sRetorn .= '      Descarrega';
		$sRetorn .= '    </button>';
		$sRetorn .= '    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
		$URL = GeneraURL("Descarrega.php?Accio=ExportaProgramacioDidacticaDOCX&ModulId=$ModulId");
		$sRetorn .= '      <a id="btnDescarregaDOCX" class="dropdown-item" href="'.$URL.'">DOCX</a>';
		$URL = GeneraURL("Descarrega.php?Accio=ExportaProgramacioDidacticaPDF&ModulId=$ModulId");
		$sRetorn .= '      <a id="btnDescarregaPDF" class="dropdown-item" href="'.$URL.'">PDF</a>';
		$sRetorn .= '    </div>';
		$sRetorn .= '  </div>';				
		return $sRetorn;
 	}
	
	/**
	 * Exporta la programació didàctica en format DOCX.
	 * @param int $ModulId Identificador del modul del pla d'estudi.
	 */				
	public function ExportaDOCX(int $ModulId) {
		$PDE = ProgramacioDidacticaDOCXFactory::Crea($this->Connexio, $this->Usuari, $this->Sistema, $ModulId);
		$PDE->EscriuDOCX($ModulId);
	}	

	/**
	 * Exporta la programació didàctica en format PDF.
	 * @param int $ModulId Identificador del modul del pla d'estudi.
	 */				
	public function ExportaPDF(int $ModulId) {
		$PDE = ProgramacioDidacticaPDFFactory::Crea($this->Connexio, $this->Usuari, $this->Sistema, $ModulId);
		$PDE->EscriuPDF($ModulId);
	}	
	
	/**
	 * Retorna la llegenda dels estats de la programació.
     * @return string Llegenda en format HTML.
	 */
	static public function LlegendaEstat(): string {
		$Retorn = 'Els diferents estats en que pot estar una programació són els següents:<br>'.
			Self::TextEstatColor('E').'<br>'.
			Self::TextEstatColor('D').'<br>'.
			Self::TextEstatColor('A').'<br>';
		return $Retorn;
	}

	/**
	 * Retorna el text de l'estat (inclosa la imatge).
	 * @param string $sEstat Codi de l'estat.
     * @return string Text de l'estat en format HTML.
	 */
	static public function TextEstatColor(string $sEstat): string {
		switch ($sEstat) {
			case "E":
				return '<img src=img/programacio/colorE.png> Elaboració';
				break;
			case "D":
				return '<img src=img/programacio/colorD.png> Revisió departament';
				break;
			case "A":
				return '<img src=img/programacio/colorA.png> Acceptada';
				break;
			default:
				return '';
		}
	}	
}

/**
 * Classe que encapsula el formulari de la programació didàctica LOE.
 */
class ProgramacioDidacticaLOE extends ProgramacioDidactica
{
	protected function GeneraSeccions() {
		$Comptador = 1;
		echo $this->GeneraSeccio(self::pdESTRATEGIES, $Comptador);
		echo $this->GeneraSeccio(self::pdCRITERIS, $Comptador);
		echo $this->GeneraSeccio(self::pdRECURSOS, $Comptador);
		echo $this->GeneraSeccio(self::pdSEQUENCIACIO_LOE, $Comptador);
		echo $this->GeneraSeccio(self::pdUNITATS, $Comptador);	
	}

	/**
	 * Genera la secció de la sequenciació i temporització de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	public function GeneraSeccioSequenciacio(&$section = null): string {
		$ModulPlaEstudiId = $this->Id;
		$sRetorn = '<BR>';
		$sRetorn .= '<TABLE BORDER="1">';
		$sRetorn .= '<thead>';
		$sRetorn .= '<TR STYLE="background-color:lightgrey;">';
		$sRetorn .= '<TH>Unitat formativa</TH>';
		$sRetorn .= '<TH STYLE="text-align:center">Hores</TH>';
		$sRetorn .= '<TH>Data inici</TH>';
		$sRetorn .= '<TH>Data fi</TH>';
		$sRetorn .= '</TR>';
		$sRetorn .= '</thead>';
		$SQL = "
			SELECT UPE.* 
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			WHERE MPE.modul_pla_estudi_id=$ModulPlaEstudiId	
		";
		$ResultSet = $this->Connexio->query($SQL);
		$sRetorn .= '<tbody>';
		while($row = $ResultSet->fetch_object()) {
			$sRetorn .= '<TR>';
			$sRetorn .= '<TD>'.CodificaUTF8($row->nom).'</TD>';
			$sRetorn .= '<TD style="text-align:center;">'.$row->hores.'</TD>';
			$sRetorn .= '<TD>'.MySQLAData($row->data_inici).'</TD>';
			$sRetorn .= '<TD>'.MySQLAData($row->data_final).'</TD>';
			$sRetorn .= '</TR>';
		}
		$sRetorn .= '</tbody>';
		$sRetorn .= '</TABLE>';
		$sRetorn .= '<BR>';
		return $sRetorn;		
	}
}

/**
 * Classe que encapsula el formulari de la programació didàctica LOGSE.
 */
class ProgramacioDidacticaLOGSE extends ProgramacioDidactica
{
	protected function GeneraSeccions() {
		$Comptador = 1;
		echo $this->GeneraSeccio(self::pdESTRATEGIES, $Comptador);
		echo $this->GeneraSeccio(self::pdCRITERIS, $Comptador);
		echo $this->GeneraSeccio(self::pdRECURSOS, $Comptador);
		echo $this->GeneraSeccio(self::pdSEQUENCIACIO_LOGSE, $Comptador);
		echo $this->GeneraSeccio(self::pdOBJECTIUS_CONTINGUTS, $Comptador);
	}

	/**
	 * Genera la secció de la sequenciació i temporització de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	public function GeneraSeccioSequenciacio(&$section = null): string {
		$sRetorn = $this->Registre->unitats_didactiques;
print_h($sRetorn);
echo $sRetorn;
		$sRetorn = $this->TractaTaules($sRetorn);
print_h($sRetorn);
echo $sRetorn;
exit;
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
		$frm->AfegeixJavaScript('Forms.js?v1.0');
		$frm->AfegeixJavaScript('ProgramacioDidactica.js?v1.4');
		$Usuari = $this->Usuari;
		$frm->Modalitat = $this->Modalitat;
		$frm->Titol = 'Programacions didàctiques';
		$frm->SQL = 'SELECT '.
			' 	MPE.modul_pla_estudi_id, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores, MPE.estat, '.
			' 	CASE MPE.estat '.
			'   	WHEN "E" THEN "Elaboració" '.
			'   	WHEN "D" THEN "Revisió cap departament" '.
			'   	WHEN "T" THEN "Revisió cap d\'estudis" '.
			'   	WHEN "A" THEN "Acceptada" '.
			' 	END AS NomEstat, '.
			'	CPE.codi AS CodiCF '. 
			' FROM MODUL_PLA_ESTUDI MPE '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) ';
		$frm->Taula = 'MODUL_PLA_ESTUDI';
		$frm->ClauPrimaria = 'modul_pla_estudi_id';
		$frm->Camps = 'CodiCF, CodiMP, NomMP, hores';
		$frm->Descripcions = 'Cicle, Codi, Mòdul professional, Hores';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'FPFitxa.php?accio=ProgramacioDidactica';
		$frm->AfegeixOpcio('Programació didàctica', 'FPFitxa.php?accio=ProgramacioDidacticaLectura&Id=', '', 'report.svg');

		$frm->AfegeixOpcioColor('Estat', 'estat', 'programacio/color', 'png', ProgramacioDidactica::LlegendaEstat());

		if ($Usuari->es_admin) {
			$frm->AfegeixOpcioAJAX('Elaboració', 'EnviaElaboracio');
			$frm->AfegeixOpcioAJAX('Departament', 'EnviaDepartament');
			$frm->AfegeixOpcioAJAX('Accepta', 'EnviaAcceptada');
//			$frm->AfegeixOpcioAJAX('Elaboració', 'EnviaElaboracio', '', [], '', '', ['estat' => 'D']);
//			$frm->AfegeixOpcioAJAX('Departament', 'EnviaDepartament', '', [], '', '', ['estat' => 'D']);
//			$frm->AfegeixOpcioAJAX('Accepta', 'EnviaAcceptada', '', [], '', '', ['estat' => 'D']);
		}
		
		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis) {
			//$frm->AfegeixOpcioAJAX('Accepta', 'EnviaAcceptada', '', [], '', '', ['estat' => 'T']);
			//$frm->AfegeixOpcioAJAX('Torna a departament', 'EnviaDepartament', '', [], '', '', ['estat' => 'T']);
			
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
			$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1], [], $this->Sistema->any_academic_id);
		}
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$CicleFormatiuId = $aCicles[0][0]; 
		$frm->Filtre->AfegeixLlista('CPE.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);
		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula el formulari de fitxa de les programacions didàctiques.
 */
abstract class ProgramacioDidacticaFitxa extends FormRecerca
{
	/**
	* Registre de l'any acadèmic de la programació didàctica.
	* @var object
	*/    
    protected $AnyAcademic = null;

	/**
	* Array dels dies festius.
	* @var array
	*/    
    protected $DiesFestius = [];

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$Registre = DB::CarregaRegistreObj($this->Connexio, 'MODUL_PLA_ESTUDI', 'modul_pla_estudi_id', $this->Id);
		$this->CarregaAnyAcademic($this->Id);
		$this->CarregaDiesFestius();

		$frm = new FormFitxaDetall($this->Connexio, $this->Usuari);
		$frm->AfegeixJavaScript('DateUtils.js?v1.0');
		$frm->AfegeixJavaScript('ProgramacioDidactica.js?v1.0');
		$frm->Titol = "Programació didàctica";
		$frm->Taula = 'MODUL_PLA_ESTUDI';
		$frm->ClauPrimaria = 'modul_pla_estudi_id';
		$frm->Id = $this->Id;
		$frm->AfegeixText('codi', 'Codi', 20, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offNOMES_LECTURA]);
		$this->GeneraPestanyes($frm, $Registre);
		$frm->EscriuHTML();		
	}

	/**
	 * Genera les pestanyes de la programació didàctica.
	 * @param object $frm Formulari fitxa.
	 * @param object $Registre Registre amb les dades del mòdul.
	 */
	abstract protected function GeneraPestanyes(object $frm, object $Registre);
		
	private function CarregaAnyAcademic(string $ModulPlaEstudiId) {
		$SQL = "
			SELECT C.*
			FROM MODUL_PLA_ESTUDI MPE
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id)
			WHERE MPE.modul_pla_estudi_id=$ModulPlaEstudiId				
		";
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) 
			$this->AnyAcademic = $ResultSet->fetch_object();
	}
	
	private function CarregaDiesFestius() {
		$this->DiesFestius = [];
		$SQL = "
			SELECT data
			FROM FESTIU F
			WHERE data >= '".$this->AnyAcademic->data_inici."'
			AND data <= '".$this->AnyAcademic->data_final."'
			ORDER BY data			
		";
		$ResultSet = $this->Connexio->query($SQL);
		while ($row = $ResultSet->fetch_object()) 
			array_push($this->DiesFestius, MySQLAData($row->data));
	}
}

/**
 * Classe que encapsula el formulari de fitxa de les programacions didàctiques LOE.
 */
class ProgramacioDidacticaFitxaLOE extends ProgramacioDidacticaFitxa
{
	/**
	 * Genera les pestanyes de la programació didàctica.
	 * @param object $frm Formulari fitxa.
	 * @param object $Registre Registre amb les dades del mòdul.
	 */
	protected function GeneraPestanyes(object $frm, object $Registre) {
		$frm->Pestanya('Metodologia');
		$frm->AfegeixTextRic('metodologia', '', 500, 300);

		$frm->Pestanya("Criteris d'avaluació");
		$frm->AfegeixTextRic('criteris_avaluacio', '', 500, 300);

		$frm->Pestanya("Recursos");
		$frm->AfegeixTextRic('recursos', '', 500, 300);

		if ($Registre->es_fct) {
			$frm->Pestanya("Planificació");
			$frm->AfegeixTextRic('planificacio', '', 500, 300);
		}
		
		$frm->DetallsEnPestanyes = true;
		$frm->Pestanya("Unitats formatives", true);
		$frm->AfegeixAmagat('data_inici', MySQLAData($this->AnyAcademic->data_inici));
		$frm->AfegeixAmagat('data_final', MySQLAData($this->AnyAcademic->data_final));
		$frm->AfegeixAmagat('festius', json_encode($this->DiesFestius));
		$frm->AfegeixDetall('Unitats formatives', 'UNITAT_PLA_ESTUDI', 'unitat_pla_estudi_id', 'modul_pla_estudi_id', '
			nom:Nom:text:400:r, 
			hores:Hores:int:60:w,
			nivell:Nivell:int:60:r,
			data_inici:Data inici:date:0:w,
			data_final:Data final:date:0:w
		');
		$Ajuda = "
			La proposta de dates es fa de manera <b>seqüencial</b> al llarg del curs, 
			de forma <b>proporcional</b> al número d'hores, 
			tenint en compte els <b>dies festius</b> 
			i acostant-se al <b>cap de setmana</b> més proper.<p>
			Qualsevol altre seqüenciació s'ha de fer a mà.
		";
		
		$frm->AfegeixBotoJSDetall('Proposa dates UF', 'ProposaDatesUF', $Ajuda);
		$frm->AfegeixBotoJSDetall('Esborra dates UF', 'EsborraDatesUF');
	}
}

/**
 * Classe que encapsula el formulari de fitxa de les programacions didàctiques LOGSE.
 */
class ProgramacioDidacticaFitxaLOGSE extends ProgramacioDidacticaFitxa
{
	/**
	 * Genera les pestanyes de la programació didàctica.
	 * @param object $frm Formulari fitxa.
	 * @param object $Registre Registre amb les dades del mòdul.
	 */
	protected function GeneraPestanyes(object $frm, object $Registre) {
		$frm->Pestanya('Metodologia');
		$frm->AfegeixTextRic('metodologia', '', 500, 300);

		$frm->Pestanya("Criteris d'avaluació");
		$frm->AfegeixTextRic('criteris_avaluacio', '', 500, 300);

		$frm->Pestanya("Recursos");
		$frm->AfegeixTextRic('recursos', '', 500, 300);

		$frm->Pestanya("Unitats didàctiques");
		$frm->AfegeixTextRic('unitats_didactiques', '', 500, 300);

		if ($Registre->es_fct) {
			$frm->Pestanya("Planificació");
			$frm->AfegeixTextRic('planificacio', '', 500, 300);
		}
	}
}

/**
 * Classe que encapsula l'exportació de la programació didàctica en DOCX.
 * https://github.com/PHPOffice/PHPWord
 * https://phpword.readthedocs.io/en/latest/
 */
abstract class ProgramacioDidacticaDOCX extends ProgramacioDidactica
{
	abstract protected function GeneraSeccionsDOCX($section);
	protected function GeneraSeccions() {} // No cal implementació.

	/**
	 * Genera el contingut DOCX de la programació didàctica.
	 * @param int $ModulId Identificador del modul del pla d'estudi.
	 */
	public function EscriuDOCX(int $ModulId) {
		$this->Id = $ModulId;
		$this->Carrega();

		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$phpWord->getSettings()->setUpdateFields(true);

		$phpWord->setDefaultParagraphStyle(
			array(
				'alignment'  => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
				'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(12),
			)
		);

		$phpWord->addFontStyle('Negreta', array('bold' => true));

		$phpWord->addParagraphStyle('Centrat', array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
		$phpWord->addParagraphStyle('Dreta', array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT));
		$phpWord->addParagraphStyle('Esquerra', array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT));
		$phpWord->addParagraphStyle('Justificat', array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH));
		$phpWord->addParagraphStyle('Interliniat0', array('spaceAfter' => 0));

		$Prova = array('spaceAfter' => 0);
		$Estil = array('borderSize' => 1, 'borderColor' => '000000');
		$phpWord->addTableStyle('TaulaSimple', $Estil, $Estil);

		$section = $phpWord->addSection();

		$this->GeneraCapcalera($section);
		$this->GeneraPeu($section);
		$this->GeneraPortada($phpWord, $section);
		$this->GeneraIndex($phpWord, $section);

		$this->GeneraSeccionsDOCX($section);

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="'.$this->GeneraNomFitxer().'.docx"');		
		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		$objWriter->save('php://output');
	}
	
	/**
	 * Elimina les etiquetes no suportades per PHPWord a la secció HTML.
	 * https://stackoverflow.com/questions/17622350/recognize-html-tags-with-phpword
	 */
	private function TractaEtiquetes($Text): string {
		if ($Text !== null) {
			$Text = str_replace('<BR>', '', $Text);
			$Text = str_replace('<br>', '', $Text);
			$Text = str_replace('"""', '"', $Text);
			$Text = str_replace('<colgroup>', '', $Text);
			$Text = str_replace('</colgroup>', '', $Text);
			$Text = str_replace('<col />', '', $Text);

			//$Text = str_replace('<table>', '<table style="border:100%">', $Text);
			//$Text = str_replace('<TABLE>', '<table style="border:100%">', $Text);

	//		$Text = str_replace('<BR />', '', $Text);
			$Text = str_replace('<br /><br />', '<br />', $Text);
		
//print_h($Text);
//exit;
		}
		else
			$Text = '';
		return $Text;
	}

	/**
	 * El mètode \PhpOffice\PhpWord\Shared\Html::addHtml no funciona gaire bé quan hi ha taules.
	 * Idea: separa les taules HTML i fer-les amb els mètodes natius.
	 * @param object $section Secció del document de PHPWord.
	 * @param string $html Fragment HTML per tractar.
	 */
	protected function AfegeixHTML(&$section, $html) {
		// https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
		$html = $html ?? '';
		
		$aHTML = [];
		$aTable = [];
		$i = 0;
		$HTML = strtoupper($html);
		$j = strpos($HTML, '<TABLE', $i);

		if ($j === false) {
			$html = $this->TractaEtiquetes($html);
//print_h($html);
//exit;			
			\PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
		}
		else
		{
			while ($j > 0) {
				array_push($aHTML, substr($html, $i, $j-$i));
				$i = $j + 1;
				
				$j1 = strpos($HTML, '<TABLE', $i);
				$j2 = strpos($HTML, '</TABLE>', $i);
				
				//if ($j1 < $j2)
	//				throw new Exception("No es permeten taules aniuades (taules dins de taules).");
				
				array_push($aTable, substr($html, $i-1, $j2-$i+9));
				$i = $j2 + 1;
				
				$j = strpos($HTML, '<TABLE', $i);
			}
			array_push($aHTML, substr($html, $i+7, strlen($html)-$i-7));
			
			for($i=0; $i<count($aHTML); $i++) {
				// HTML
				$html = $this->TractaEtiquetes($aHTML[$i]);
				\PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
				// Taula
				if ($i < count($aTable))
					$this->AfegeixTaulaHTML($section, $aTable[$i]);
			}
		}
	}
	
	/**
	 * Afegeix una taula HTML a la forma nativa (i no amb el mètode addHtml).
	 * https://www.tutorialspoint.com/php/php_dom_parser_example.htm   
	 * @param object $section Secció del document de PHPWord.
	 * @param string $html Fragment HTML per tractar.
	 */
	private function AfegeixTaulaHTML(&$section, $taula) {
		$dom = new domDocument;

		// Some versions of the DOMDocument parser that PHP uses are super-strict about HTML compliance, and will whine 
		// and regularly do wrong things when confronted with spec violations.
		// This completely depends on whether the version of libxml2 you are using has support for this part of HTML5. 
		// https://bugs.php.net/bug.php?id=63477
		// https://stackoverflow.com/questions/5645536/issue-with-using-domnode-attributes-with-attributes-that-have-multiple-words-in
		$taula = str_replace('class=""""', '', $taula);
//print htmlspecialchars($taula);
//exit;	

		// Si hi ha TH, cal passar-los a TR
		$taula = str_replace('<TH>', '<TD>', $taula);
		$taula = str_replace('</TH>', '</TD>', $taula);
	
		$dom->loadHTML($taula); 
		$dom->preserveWhiteSpace = false; 
		$tables = $dom->getElementsByTagName('table'); 
//print_h($tables);
//exit;

		// Es suposa una única taula
		$rows = $tables->item(0)->getElementsByTagName('tr'); 

		// Posem la taula en un array 2D
		$aFiles = [];
		foreach ($rows as $row) {
			$aColumnes = [];
			$cols = $row->getElementsByTagName('td'); 
			for($i=0; $i<count($cols); $i++) {
				$Valor = utf8_decodeX($cols->item($i)->nodeValue);
				array_push($aColumnes, $Valor);
			}
			array_push($aFiles, $aColumnes);
		}
//print_h($aFiles);
//exit;

		// Calculem les mides màximes de cada columna
		$aMax = [];
		for($j=0; $j<count($aFiles[0]); $j++) {
//		for($j=0; $j<$rows->length; $j++) {
			$Max = 0;
			for($i=0; $i<count($aFiles); $i++) {
				$bbox = imagettfbbox(16, 0, FONT_FILENAME_ARIAL, $aFiles[$i][$j]);
				$width = abs($bbox[0]) + abs($bbox[2]); // distance from left to right
				$Max = max($Max, $width);				
			}
			array_push($aMax, $Max);
		}
//print_h($aMax);
//exit;

		$UnCm = \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1); // 1 cm 
		//$Negreta = array('bold' => true);
//		$Centrat = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
		$Estil = array('borderSize' => 1, 'borderColor' => '000000', 'spaceAfter' => 0);
		$EstilGrisClar = array('bgColor' => 'D3D3D3', 'borderSize' => 1, 'borderColor' => '000000', 'spaceAfter' => 0);

		//$table = $section->addTable('TaulaHTML', 'TaulaSimple');
		$table = $section->addTable('TaulaHTML', $Estil);
		for($j=0; $j<count($aFiles); $j++) {
			$table->addRow();
			for($i=0; $i<count($aFiles[$j]); $i++) {
				$Valor = $aFiles[$j][$i];
				//$cell = $table->addCell(15*$aMax[$i])->addText($Valor);
				if ($j == 0)
					$cell = $table->addCell(15*$aMax[$i], $EstilGrisClar)->addText($Valor, 'Negreta', 'Interliniat0');
				else 
					$cell = $table->addCell(15*$aMax[$i], $Estil)->addText($Valor, null, 'Interliniat0');
			}
		}
	}
	
	private function GeneraCapcalera(&$section) {
		// Capçalera
		// https://github.com/PHPOffice/PHPWord/blob/develop/samples/Sample_12_HeaderFooter.php
		$header = $section->addHeader();
		$table = $header->addTable();
		$table->addRow();

		$cell = $table->addCell(800);
		$textrun = $cell->addTextRun();
		$textrun->addImage(ROOT.'/img/logo-gencat.jpg', array('width' => 35, 'height' => 35, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT));

		$cell = $table->addCell(3000);
		$textrun = $cell->addTextRun('Esquerra');
		$textrun->addText("Generalitat de Catalunya\n", 'Negreta');
		$textrun->addText("Departament d'Educació\n", 'Negreta');
		$textrun->addText("Institut de Palamós", 'Negreta');

		$table->addCell(1250)->addText("");
		$table->addCell(3250)->addImage(ROOT.'/img/logo-inspalamos.png', array('width' => 150, 'height' => 35));
		$table->addCell(1750)->addImage(ROOT.'/img/logo-ue.png', array('width' => 50, 'height' => 35));
	}

	private function GeneraPeu(&$section) {
		$footer = $section->addFooter();
		$footer->addPreserveText('Pàgina {PAGE} de {NUMPAGES}', null, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT));
	}
	
	private function GeneraPortada(&$phpWord, &$section) {
		$tableStyle = array(
			'borderColor' => 'black',
			'borderSize' => 1,
			'cellMargin' => 50
		);
//		$firstRowStyle = array('bgColor' => '66BBFF');
//		$phpWord->addTableStyle('TaulaPortada', $tableStyle, $firstRowStyle);
		$phpWord->addTableStyle('TaulaPortada', $tableStyle);


		$fontPrimeraPagina = array('name' => 'Arial', 'size' => 13, 'bold' => true);
		$fontPrimeraPaginaEsquerra = array('name' => 'Arial', 'size' => 13, 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT);
		$fontPrimeraPaginaDreta = array('name' => 'Arial', 'size' => 13, 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT);
		$fontPrimeraPaginaCentre = array('name' => 'Arial', 'size' => 13, 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
		$EsquerraInterliniat0 = array('spaceAfter' => 0, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT);
		$DretaInterliniat0 = array('spaceAfter' => 0, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT);

		$section->addTextBreak(7);
		$section->addTextRun($fontPrimeraPaginaCentre)->addText("PROGRAMACIONS DE CICLES FORMATIUS", $fontPrimeraPagina);
		$section->addTextBreak(1);

		$Cm1 = \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1); // 1 cm 

		$table = $section->addTable('TaulaPortada');

		$table->addRow();
		$table->addCell(8*$Cm1)->addText("Nom del Cicle Formatiu:", $fontPrimeraPaginaEsquerra, $DretaInterliniat0);
		$table->addCell(8*$Cm1)->addText(utf8_encodeX($this->Registre->NomCF), $fontPrimeraPaginaEsquerra, $EsquerraInterliniat0);

		$table->addRow();
		$table->addCell()->addText("Curs:", $fontPrimeraPaginaEsquerra, $DretaInterliniat0);
		$table->addCell()->addText(utf8_encodeX($this->Registre->NomAA), $fontPrimeraPaginaEsquerra, $EsquerraInterliniat0);

		$table->addRow();
		$table->addCell()->addText("Codi del Mòdul Professional:", $fontPrimeraPaginaEsquerra, $DretaInterliniat0);
		$table->addCell()->addText(utf8_encodeX($this->Registre->CodiMP), $fontPrimeraPaginaEsquerra, $EsquerraInterliniat0);

		$table->addRow();
		$table->addCell()->addText("Títol del Mòdul Professional:", $fontPrimeraPaginaEsquerra, $DretaInterliniat0);
		$table->addCell()->addText(utf8_encodeX($this->Registre->NomMP), $fontPrimeraPaginaEsquerra, $EsquerraInterliniat0);

		$table->addRow();
		$table->addCell()->addText("Professors:", $fontPrimeraPaginaEsquerra, $DretaInterliniat0);
		$table->addCell()->addText($this->ObteProfessorsModul($this->Id), $fontPrimeraPaginaEsquerra, $EsquerraInterliniat0);
	}
	
	private function GeneraIndex(&$phpWord, &$section) {
		// Definició d'estils
		$fontStyle12 = array('spaceAfter' => 60, 'size' => 12);
		$fontStyle10 = array('size' => 10);
		$phpWord->addTitleStyle(null, array('size' => 22, 'bold' => true));
		$phpWord->addTitleStyle(1, array('size' => 18, 'bold' => true));
		$phpWord->addTitleStyle(2, array('size' => 16, 'bold' => true));
		$phpWord->addTitleStyle(3, array('size' => 14, 'bold' => true));

		$section->addPageBreak();
//		$section->addTextBreak(1);

		// Índex de continguts
		$section->addTitle('Índex de continguts', 0);
		$section->addTextBreak(1);
		$toc = $section->addTOC($fontStyle12, 'Interliniat0');
	}

	abstract protected function GeneraSeccioSequenciacio(&$section = null);

	protected function GeneraSeccioUnitats(&$section = null) {
		$ModulId = $this->Registre->modul_professional_id;
		$RA = new ResultatsAprenentatge($this->Connexio, $this->Usuari);
		$RA->CreaRegistreModul($ModulId);
//print_h($RA->Registre);
		
		$width = \PhpOffice\PhpWord\Shared\Converter::cmToTwip(17); // 17 cm 
		//$Negreta = array('bold' => true);
		$Estil = array('borderSize' => 1, 'borderColor' => '000000');
		$EstilGris = array('bgColor' => '808080', 'borderSize' => 1, 'borderColor' => '000000');
		$EstilGrisClar = array('bgColor' => 'D3D3D3', 'borderSize' => 1, 'borderColor' => '000000');
		//$CentratInterliniat0 = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0 );

		foreach ($RA->Registre as $UF) {
			$table = $section->addTable('TaulaSeccioUnitats'.$UF->Id);
			$table->addRow();
			$cell = $table->addCell($width, $EstilGris)->addText($UF->Nom, 'Negreta', 'Interliniat0');
			foreach ($UF->Dades as $Dades) {
				if ($Dades->Tipus == 'R') {
					$table->addRow();
					$cell = $table->addCell($width, $EstilGris)->addText('RA'.$Dades->Nom, 'Negreta', 'Interliniat0');
					$table->addRow();
					$cell = $table->addCell($width, $EstilGrisClar)->addText('Resultats d’aprenentatge i criteris d’avaluació', 'Negreta', 'Interliniat0');
				}
				else if ($Dades->Tipus == 'C') {
					$table->addRow();
					$cell = $table->addCell($width, $EstilGrisClar)->addText('Continguts', 'Negreta', 'Interliniat0');
					$table->addRow();
					$cell = $table->addCell($width, $Estil)->addText($Dades->Nom, null, 'Interliniat0');
				}
				foreach ($Dades->Dades as $Dades2) {
					$table->addRow();
					$cell = $table->addCell($width, $Estil)->addText($Dades2, null, 'Interliniat0');
				}
			}
			$section->addTextBreak(2);
		}
	}
	
	/**
	 * Genera la secció d'objectius i continguts de la programació didàctica (LOGSE).
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioObjectiusContinguts(&$section = null) {
		$ModulId = $this->Registre->modul_professional_id;
		$OC = new ObjectiusContinguts($this->Connexio, $this->Usuari);
		$OC->CreaRegistreModul($ModulId);

		$width = \PhpOffice\PhpWord\Shared\Converter::cmToTwip(17); // 17 cm 
		$Estil = array('borderSize' => 1, 'borderColor' => '000000');
		$EstilGris = array('bgColor' => '808080', 'borderSize' => 1, 'borderColor' => '000000');
		$EstilGrisClar = array('bgColor' => 'D3D3D3', 'borderSize' => 1, 'borderColor' => '000000');
		
		foreach ($OC->Registre as $MP) {
			$table = $section->addTable('TaulaSeccioObjectiusContinguts'.$MP->Id);
			$table->addRow();
			$cell = $table->addCell($width, $EstilGris)->addText($MP->Nom, 'Negreta', 'Interliniat0');
				
			$table->addRow();
			$cell = $table->addCell($width, $EstilGrisClar)->addText($OC->TipusText('O'), 'Negreta', 'Interliniat0');
			foreach ($MP->MP->Objectius as $Dades) {
				$table->addRow();
				$cell = $table->addCell($width, $Estil)->addText($Dades, null, 'Interliniat0');
			}

			$table->addRow();
			$cell = $table->addCell($width, $EstilGrisClar)->addText($OC->TipusText('F'), 'Negreta', 'Interliniat0');
			foreach ($MP->MP->Fets as $Dades) {
				$table->addRow();
				$cell = $table->addCell($width, $Estil)->addText($Dades, null, 'Interliniat0');
			}

			$table->addRow();
			$cell = $table->addCell($width, $EstilGrisClar)->addText($OC->TipusText('P'), 'Negreta', 'Interliniat0');
			foreach ($MP->MP->Procediments as $Dades) {
				$table->addRow();
				$cell = $table->addCell($width, $Estil)->addText($Dades, null, 'Interliniat0');
			}

			$table->addRow();
			$cell = $table->addCell($width, $EstilGrisClar)->addText($OC->TipusText('A'), 'Negreta', 'Interliniat0');
			foreach ($MP->MP->Actituds as $Dades) {
				$table->addRow();
				$cell = $table->addCell($width, $Estil)->addText($Dades, null, 'Interliniat0');
			}
				
			$section->addTextBreak(2);
		}
		
	}	
}

/**
 * Classe que encapsula l'exportació de la programació didàctica LOE en DOCX.
 */
class ProgramacioDidacticaDOCXLOE extends ProgramacioDidacticaDOCX
{
	protected function GeneraSeccionsDOCX($section) {
		$Comptador = 1;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdESTRATEGIES], 1);
		$html = $this->GeneraSeccioEstrategies();
		$this->AfegeixHTML($section, $html);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdCRITERIS], 1);
		$html = $this->GeneraSeccioCriteris();
		$this->AfegeixHTML($section, $html);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdRECURSOS], 1);
		$html = $this->GeneraSeccioRecursos();
		$this->AfegeixHTML($section, $html);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdSEQUENCIACIO_LOE], 1);
		$this->GeneraSeccioSequenciacio($section);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdUNITATS], 1);
		$this->GeneraSeccioUnitats($section);
	}
	
	protected function GeneraSeccioSequenciacio(&$section = null) {
		$ModulPlaEstudiId = $this->Id;
		$aUF = [];
		$SQL = "
			SELECT UPE.* 
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			WHERE MPE.modul_pla_estudi_id=$ModulPlaEstudiId	
		";
		$ResultSet = $this->Connexio->query($SQL);
		while($row = $ResultSet->fetch_object())
			array_push($aUF, $row);
		
		$Max = 0;
		// https://www.php.net/manual/en/function.imagettfbbox.php
		// Hi ha també imageftbbox
		foreach ($aUF as $row) {
			$NomUF = utf8_encodeX($row->nom);
			$bbox = imagettfbbox(16, 0, FONT_FILENAME_ARIAL, $NomUF);
			$width = abs($bbox[0]) + abs($bbox[2]); // distance from left to right
			$Max = max($Max, $width);
		}
		
		$UnCm = \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1); // 1 cm 
		//$Negreta = array('bold' => true);
		//$Centrat = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
		$Estil = array('borderSize' => 1, 'borderColor' => '000000');
		$EstilGrisClar = array('bgColor' => 'D3D3D3', 'borderSize' => 1, 'borderColor' => '000000');
		$CentratInterliniat0 = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0 );

//		$table = $section->addTable('TaulaSeccioSequenciacio');
		$table = $section->addTable('TaulaSeccioSequenciacio', 'TaulaSimple');
		$table->addRow();
		$cell = $table->addCell(10*$Max, $EstilGrisClar)->addText('Unitat formativa', 'Negreta', 'Interliniat0');
		$cell = $table->addCell(2*$UnCm, $EstilGrisClar)->addText('Hores', 'Negreta', $CentratInterliniat0);
		$cell = $table->addCell(2*$UnCm, $EstilGrisClar)->addText('Data inici', 'Negreta', 'Interliniat0');
		$cell = $table->addCell(2*$UnCm, $EstilGrisClar)->addText('Data fi', 'Negreta', 'Interliniat0');
		foreach ($aUF as $row) {
			$table->addRow();
			$cell = $table->addCell(10*$Max, $Estil)->addText(utf8_encodeX($row->nom), null, 'Interliniat0');
			$cell = $table->addCell(2*$UnCm, $Estil)->addText($row->hores, null, $CentratInterliniat0);
			$cell = $table->addCell(2*$UnCm, $Estil)->addText(MySQLAData($row->data_inici), null, 'Interliniat0');
			$cell = $table->addCell(2*$UnCm, $Estil)->addText(MySQLAData($row->data_final), null, 'Interliniat0');
		}
	}
}

/**
 * Classe que encapsula l'exportació de la programació didàctica LOGSE en DOCX.
 */
class ProgramacioDidacticaDOCXLOGSE extends ProgramacioDidacticaDOCX
{
	protected function GeneraSeccionsDOCX($section) {
		$Comptador = 1;
		
//		throw new Exception("ProgramacioDidacticaLOGSE->GeneraSeccions. No implementat");
		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdESTRATEGIES], 1);
		$html = $this->GeneraSeccioEstrategies();
		$this->AfegeixHTML($section, $html);
		$Comptador++;
		
		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdCRITERIS], 1);
		$html = $this->GeneraSeccioCriteris();
		$this->AfegeixHTML($section, $html);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdRECURSOS], 1);
		$html = $this->GeneraSeccioRecursos();
		$this->AfegeixHTML($section, $html);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdSEQUENCIACIO_LOGSE], 1);
		$html = $this->GeneraSeccioSequenciacio();
		$this->AfegeixHTML($section, $html);
		$Comptador++;

		$section->addPageBreak();
		$section->addTextBreak(1);
		$section->addTitle($Comptador.'. '.self::SECCIO[self::pdOBJECTIUS_CONTINGUTS], 1);
		$this->GeneraSeccioObjectiusContinguts($section);
	}

	/**
	 * Genera la secció de la sequenciació i temporització de la programació didàctica.
	 * @param integer $SeccioId Identificador de la secció.
	 * @return string Codi HTML amb la secció.
	 */
	protected function GeneraSeccioSequenciacio(&$section = null) {
		$sRetorn = $this->Registre->unitats_didactiques;
		$sRetorn = $this->TractaTaules($sRetorn);
		return $sRetorn;		
	}
}

/**
 * Classe que encapsula l'exportació de la programació didàctica en PDF.
 */
abstract class ProgramacioDidacticaPDF extends ProgramacioDidactica
{
	/**
	 * Objecte PDF.
	 * @var object
	 */
	protected $PDF = null;

	abstract protected function GeneraSeccions();
	abstract function GeneraSeccioSequenciacio(&$section = null);

	/**
	 * Genera el contingut PDF de la programació didàctica.
	 * @param int $ModulId Identificador del modul del pla d'estudi.
	 */
	public function EscriuPDF(int $ModulId) {
		$this->Id = $ModulId;
		$this->Carrega();

		$pdf = new DocumentPDFProgramacioDidactica('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Institut de Palamós');
		$pdf->SetKeywords('INS Palamós, Palamós');
		$pdf->SetTitle('ProgramacioDidacticaPDF');
		$pdf->SetSubject('ProgramacioDidacticaPDF');

		// Portada
		$pdf->AddPage(); 
		$this->GeneraPortada($pdf);

		$pdf->AddPage(); 
		$this->PDF = $pdf;
		$this->GeneraSeccions();

		// Taula de continguts (índex)
		// https://tcpdf.org/examples/example_045/
		$pdf->addTOCPage();
		// write the TOC title
		$pdf->SetFont('helvetica', 'B');
		$pdf->MultiCell(0, 0, 'Índex', 0, 'L', 0, 1, '', '', true, 0);
		$pdf->Ln();
		$pdf->SetFont('helvetica', '');
		// add a simple Table Of Content at first page
		$pdf->addTOC(2, 'helvetica', '.', 'Índex', '');
		// end of TOC page
		$pdf->endTOCPage();

		// Close and output PDF document
		// Clean any content of the output buffer
		ob_end_clean();
		$pdf->Output($this->GeneraNomFitxer().'.pdf', 'I');
	}

	private function GeneraPortada($pdf) {
		$pdf->Ln(70);
		$pdf->SetFont('helvetica', 'B', 13); 
		$pdf->MultiCell(0, 0, 'PROGRAMACIONS DE CICLES FORMATIUS', 0, 'C', 0, 1, '', '', true, 0);
		$pdf->SetFont('helvetica', '', 12); 
		$pdf->Ln(20);

		$HTML = '
			<table cellspacing="0" cellpadding="2" border="1">
				<tr>
					<td align="right">Nom del Cicle Formatiu:</td>
					<td><b>'.$this->Registre->NomCF.'</b></td>
				</tr>
				<tr>
					<td align="right">Curs:</td>
					<td><b>'.$this->Registre->NomAA.'</b></td>
				</tr>
				<tr>
					<td align="right">Codi del Mòdul Professional:</td>
					<td><b>'.$this->Registre->CodiMP.'</b></td>
				</tr>
				<tr>
					<td align="right">Títol del Mòdul Professional:</td>
					<td><b>'.$this->Registre->NomMP.'</b></td>
				</tr>
				<tr>
					<td align="right">Professors:</td>
					<td><b>'.$this->ObteProfessorsModul($this->Id).'</b></td>
				</tr>
			</table>
		';
		$pdf->writeHTML($HTML, True);
	}
}

/**
 * Classe que encapsula l'exportació de la programació didàctica LOE en PDF.
 */
class ProgramacioDidacticaPDFLOE extends ProgramacioDidacticaPDF
{
	protected function GeneraSeccions() {
		$pdf = $this->PDF;
		$Comptador = 1;
		$SeccioId = 1;

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdESTRATEGIES];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioEstrategies($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdCRITERIS];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioCriteris($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdRECURSOS];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioRecursos($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdSEQUENCIACIO_LOE];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioSequenciacio($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdUNITATS];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioUnitats($SeccioId);
		$pdf->writeHTML($HTML, True);
	}

	public function GeneraSeccioSequenciacio(&$section = null): string {
		$pd = new ProgramacioDidacticaLOE($this->Connexio, $this->Usuari, $this->Sistema);
		$pd->Id = $this->Id;
		return $pd->GeneraSeccioSequenciacio($section);
	}
}

/**
 * Classe que encapsula l'exportació de la programació didàctica LOE en PDF.
 */
class ProgramacioDidacticaPDFLOGSE extends ProgramacioDidacticaPDF
{
	protected function GeneraSeccions() {
		$pdf = $this->PDF;
		$Comptador = 1;
		$SeccioId = 1;

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdESTRATEGIES];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioEstrategies($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdCRITERIS];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioCriteris($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdRECURSOS];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioRecursos($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdSEQUENCIACIO_LOGSE];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioSequenciacio($SeccioId);
		$pdf->writeHTML($HTML, True);

		$Apartat = $Comptador++.'. '.self::SECCIO[self::pdOBJECTIUS_CONTINGUTS];
		$pdf->Bookmark($Apartat, 0, 0, '', '', array(0,64,128));
		$pdf->Titol2($Apartat);
		$HTML = $this->GeneraSeccioObjectiusContinguts($SeccioId);
		$pdf->writeHTML($HTML, True);
	}

	public function GeneraSeccioSequenciacio(&$section = null): string {
		$pd = new ProgramacioDidacticaLOGSE($this->Connexio, $this->Usuari, $this->Sistema);
		$pd->Registre = $this->Registre;
		return $pd->GeneraSeccioSequenciacio($section);
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
				CAV.criteri_avaluacio_id, CAV.descripcio AS CriteriAvaluacio
			FROM MODUL_PROFESSIONAL MP 
			LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id)
			LEFT JOIN RESULTAT_APRENENTATGE RA ON (RA.unitat_formativa_id=UF.unitat_formativa_id)
			LEFT JOIN CRITERI_AVALUACIO CAV ON (CAV.resultat_aprenentatge_id=RA.resultat_aprenentatge_id)
			WHERE cicle_formatiu_id=$CicleFormatiuId
		";		
	}

	/**
	 * Genera el filtre del formulari si n'hi ha.
     * @return string Codi HTML del filtre.
	 */
	protected function GeneraFiltre(): string {
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, "SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU WHERE llei='LO' ORDER BY nom", "cicle_formatiu_id", "nom");
		$this->CicleFormatiuId = $aCicles[0][0]; 
		return $this->CreaLlista('cicle_formatiu_id', 'Cicle', 800, $aCicles[0], $aCicles[1], $this->CicleFormatiuId, 'onchange="ActualitzaTaulaResultatsAprenentatge(this);"');
	}

	/**
	 * Genera la llista amb els RA d'un cicle.
     * @return string Llista amb les dades.
	 */
	public function GeneraTaula(): string {
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
							$Id = ($this->Usuari->es_admin) ? ' ['.$row->modul_professional_id.']' : '';
//							$sRetorn .= '<li><b>'.$row->CodiMP.'. '.utf8_encodeX($row->NomMP).'</b>';
							$sRetorn .= '<li><b>'.$row->CodiMP.'. '.CodificaUTF8($row->NomMP).'</b>'.$Id;
							$sRetorn .= '<ul>';
							$ModulProfessionalId = $row->modul_professional_id;
						}
						$Id = ($this->Usuari->es_admin) ? ' ['.$row->unitat_formativa_id.']' : '';
//						$sRetorn .= '<li><u>'.utf8_encodeX($row->NomUF).'</u>';
						$sRetorn .= '<li><u>'.CodificaUTF8($row->NomUF).'</u>'.$Id;
						$sRetorn .= '<ul>';
						$UnitatFormativaId = $row->unitat_formativa_id;
					}
					$Id = ($this->Usuari->es_admin) ? ' ['.$row->resultat_aprenentatge_id.']' : '';
//					$sRetorn .= '<li>RA'.utf8_encodeX($row->ResultatAprenentatge);
					$sRetorn .= '<li>RA'.CodificaUTF8($row->ResultatAprenentatge).$Id;
					$sRetorn .= '<ul>';
					$ResultatAprenentatgeId = $row->resultat_aprenentatge_id;
				}
				if ($row->CriteriAvaluacio != '')
					$Id = ($this->Usuari->es_admin) ? ' ['.$row->criteri_avaluacio_id.']' : '';
//					$sRetorn .= '<li>'.utf8_encodeX($row->CriteriAvaluacio);
					$sRetorn .= '<li>'.CodificaUTF8($row->CriteriAvaluacio).$Id;
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
					$UF->Nom = CodificaUTF8($row->NomUF);
					$UF->Dades = [];
					
					$UnitatFormativaId = $row->unitat_formativa_id;
				}
				
				if (($row->DescripcioId !== $DescripcioId) || ($row->Tipus !== $Tipus)) {
					// RA o contingut nou	
					$Dades = new stdClass();
					array_push($UF->Dades, $Dades);
					$Dades->Id = $row->DescripcioId;
					$Dades->Nom = CodificaUTF8($row->Descripcio);
					$Dades->Tipus = $row->Tipus;
					$Dades->Dades = [];

					$DescripcioId = $row->DescripcioId;							
					$Tipus = $row->Tipus;							
				}
				
				array_push($Dades->Dades, CodificaUTF8($row->Descripcio2));
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
			$sRetorn .= '<table border="1">';
			$sRetorn .= '<tr style="background-color:grey;">';
			$sRetorn .= '<th>'.$UF->Nom.'</th>';
			$sRetorn .= '</tr>';
			foreach ($UF->Dades as $Dades) {
				if ($Dades->Tipus == 'R') {
					$sRetorn .= '<tr style="background-color:grey;">';
					$sRetorn .= '<th>RA'.$Dades->Nom.'</th>';
					$sRetorn .= '</tr>';
					$sRetorn .= '<tr style="background-color:lightgrey;">';
					$sRetorn .= '<th>Resultats d’aprenentatge i criteris d’avaluació</th>';
					$sRetorn .= '</tr>';
				}
				else if ($Dades->Tipus == 'C') {
					$sRetorn .= '<tr style="background-color:lightgrey;">';
					$sRetorn .= '<th>Continguts</th>';
					$sRetorn .= '</tr>';
					$sRetorn .= '<tr>';
					$sRetorn .= '<td>'.$Dades->Nom.'</td>';
					$sRetorn .= '</tr>';
				}
				foreach ($Dades->Dades as $Dades2) {
					$sRetorn .= '<tr>';
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

/**
 * Formulari que encapsula els continguts de les unitats formatives.
 */
class ContingutsUF extends Form
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
		CreaIniciHTML($this->Usuari, "Continguts UF");
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/ProgramacioDidactica.js?v1.4" type="text/javascript"></script>';

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
				CUF.contingut_uf_id, CUF.descripcio AS ContingutUF,
				SCUF.subcontingut_uf_id, SCUF.descripcio AS SubContingutUF
			FROM MODUL_PROFESSIONAL MP 
			LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id)
			LEFT JOIN CONTINGUT_UF CUF ON (CUF.unitat_formativa_id=UF.unitat_formativa_id)
			LEFT JOIN SUBCONTINGUT_UF SCUF ON (SCUF.contingut_uf_id=CUF.contingut_uf_id)
			WHERE cicle_formatiu_id=$CicleFormatiuId;
		";		
	}

	/**
	 * Genera el filtre del formulari si n'hi ha.
     * @return string Codi HTML del filtre.
	 */
	protected function GeneraFiltre(): string {
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, "SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU WHERE llei='LO' ORDER BY nom", "cicle_formatiu_id", "nom");
		$this->CicleFormatiuId = $aCicles[0][0]; 
		return $this->CreaLlista('cicle_formatiu_id', 'Cicle', 800, $aCicles[0], $aCicles[1], $this->CicleFormatiuId, 'onchange="ActualitzaTaulaContingutsUF(this);"');
	}

	/**
	 * Genera la llista amb els Continguts d'un cicle.
     * @return string Llista amb les dades.
	 */
	public function GeneraTaula(): string {
		$sRetorn = '<DIV id=taula>';
		$ModulProfessionalId = -1;
		$UnitatFormativaId = -1;
		$ContingutUFId = -1;
		$SQL = $this->CreaSQL($this->CicleFormatiuId);
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			while($row = $ResultSet->fetch_object()) {
				if ($row->contingut_uf_id !== $ContingutUFId) {
					// Contingut nou
					if ($ContingutUFId != -1)
						$sRetorn .= '</ul>';
					if ($row->unitat_formativa_id !== $UnitatFormativaId) {
						// UF nova
						if ($UnitatFormativaId != -1)
							$sRetorn .= '</ul>';
						if ($row->modul_professional_id !== $ModulProfessionalId) {
							// Mòdul nou
							if ($ModulProfessionalId != -1)
								$sRetorn .= '</ul>';
							$Id = ($this->Usuari->es_admin) ? ' ['.$row->modul_professional_id.']' : '';
							$sRetorn .= '<li><b>'.$row->CodiMP.'. '.CodificaUTF8($row->NomMP).'</b>'.$Id;
							$sRetorn .= '<ul>';
							$ModulProfessionalId = $row->modul_professional_id;
						}
						$Id = ($this->Usuari->es_admin) ? ' ['.$row->unitat_formativa_id.']' : '';
						$sRetorn .= '<li><u>'.CodificaUTF8($row->NomUF).'</u>'.$Id;
						$sRetorn .= '<ul>';
						$UnitatFormativaId = $row->unitat_formativa_id;
					}
					$Id = ($this->Usuari->es_admin) ? ' ['.$row->contingut_uf_id.']' : '';
					$sRetorn .= '<li>'.CodificaUTF8($row->ContingutUF).$Id;
					$sRetorn .= '<ul>';
					$ContingutUFId = $row->contingut_uf_id;
				}
				if ($row->SubContingutUF != '') {
					$Id = ($this->Usuari->es_admin) ? ' ['.$row->subcontingut_uf_id.']' : '';
					$sRetorn .= '<li>'.CodificaUTF8($row->SubContingutUF).$Id;
				}
			}
		}
		else
			$sRetorn .= 'No hi ha dades.';
		$sRetorn .= '</DIV>';
		return $sRetorn;			
	}	
}

/**
 * Formulari que encapsula els objectius i continguts.
 */
class ObjectiusContinguts extends Form
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
		CreaIniciHTML($this->Usuari, "Objectius i continguts");
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
				OC.*
			FROM MODUL_PROFESSIONAL MP 
			LEFT JOIN OBJECTIU_CONTINGUT OC ON (OC.modul_professional_id=MP.modul_professional_id)
			WHERE cicle_formatiu_id=$CicleFormatiuId
			ORDER BY MP.codi
		";		
	}

	/**
	 * Genera el filtre del formulari si n'hi ha.
     * @return string Codi HTML del filtre.
	 */
	protected function GeneraFiltre(): string {
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, "SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU WHERE llei='LG' ORDER BY nom", "cicle_formatiu_id", "nom");
		$this->CicleFormatiuId = $aCicles[0][0]; 
		return $this->CreaLlista('cicle_formatiu_id', 'Cicle', 800, $aCicles[0], $aCicles[1], $this->CicleFormatiuId, 'onchange="ActualitzaTaulaResultatsAprenentatge(this);"');
	}
	
	public function TipusText(string $Tipus): string {
		switch ($Tipus) {
			case 'O':
				return 'Objectius terminals';
				break;
			case 'F':
				return 'Continguts de fets, conceptes i sistemes conceptuals';
				break;
			case 'P':
				return 'Continguts de procediments';
				break;
			case 'A':
				return 'Continguts d’actituds';
				break;
			default:
				return '';
				break;
		}
	}
	
	/**
	 * Carrega els registres especificat a la SQL i els posa en un objecte.
	 * @return void.
	 */				
	protected function Carrega() {
		$this->Registre = [];
		$ModulProfessionalId = -1;
		$Tipus = '';
		$SQL = $this->CreaSQL($this->CicleFormatiuId);
//print_r($SQL);		
		$ResultSet = $this->Connexio->query($SQL);
//print_r($ResultSet->num_rows);		
		if ($ResultSet->num_rows > 0) {
//print_r('-');		
			while($row = $ResultSet->fetch_object()) {
				if ($row->modul_professional_id !== $ModulProfessionalId) {
					$this->Registre[$row->modul_professional_id] = new StdClass();
					$this->Registre[$row->modul_professional_id]->Id = $row->modul_professional_id;
					$this->Registre[$row->modul_professional_id]->Codi = $row->CodiMP;
					$this->Registre[$row->modul_professional_id]->Nom = $row->NomMP;
					$MP = new StdClass();
					$MP->Objectius = [];
					$MP->Fets = [];
					$MP->Procediments = [];
					$MP->Actituds = [];
					$this->Registre[$row->modul_professional_id]->MP = $MP;
					$ModulProfessionalId = $row->modul_professional_id;
				}
				switch ($row->tipus) {
					case 'O':
						array_push($MP->Objectius, $row->descripcio);
						break;
					case 'F':
						array_push($MP->Fets, $row->descripcio);
						break;
					case 'P':
						array_push($MP->Procediments, $row->descripcio);
						break;
					case 'A':
						array_push($MP->Actituds, $row->descripcio);
						break;
				}
			}
		}
//print_h($this->Registre);
//exit;
	}
	

	/**
	 * Genera la llista amb els RA d'un cicle.
     * @return string Llista amb les dades.
	 */
	public function GeneraTaula(): string {
		$this->Carrega();
		$sRetorn = '<DIV id=taula>'.PHP_EOL;
		foreach($this->Registre as $MP) {
			$sRetorn .= '<ul>'.PHP_EOL;
			$Id = ($this->Usuari->es_admin) ? ' ['.$MP->Id.']' : '';
			$sRetorn .= '    <li><b>'.$MP->Codi.'. '.$MP->Nom.'</b>'.$Id.'</li>'.PHP_EOL;
			$sRetorn .= '    <ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('O').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Objectius as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('F').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Fets as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('P').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Procediments as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('A').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Actituds as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '    </ul>'.PHP_EOL;
			
			$sRetorn .= '</ul>'.PHP_EOL;
		}
		$sRetorn .= '</DIV>';
		return $sRetorn;			
	}

	/**
	 * Crea la sentència SQL.
	 * @param integer $Modul Identificador del mòdul.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQLModul(int $ModulId): string {
		return "
			SELECT 
				MP.modul_professional_id, MP.codi AS CodiMP, MP.nom AS NomMP,
				OC.*
			FROM MODUL_PROFESSIONAL MP 
			LEFT JOIN OBJECTIU_CONTINGUT OC ON (OC.modul_professional_id=MP.modul_professional_id)
			WHERE MP.modul_professional_id=$ModulId
			ORDER BY MP.codi
		";			
	}
	
	/**
	 * Crea un registre amb els resultats d’aprenentatge, criteris d’avaluació i continguts d'un mòdul.
	 * @param integer $ModulId Identificador del mòdul.
	 */
	public function CreaRegistreModul(int $ModulId) {
		$this->Registre = [];
		$ModulProfessionalId = -1;
		$Tipus = '';
		$SQL = $this->CreaSQLModul($ModulId);
//print_r($SQL);		
		$ResultSet = $this->Connexio->query($SQL);
//print_r($ResultSet->num_rows);		
		if ($ResultSet->num_rows > 0) {
//print_r('-');		
			while($row = $ResultSet->fetch_object()) {
				if ($row->modul_professional_id !== $ModulProfessionalId) {
					$this->Registre[$row->modul_professional_id] = new StdClass();
					$this->Registre[$row->modul_professional_id]->Id = $row->modul_professional_id;
					$this->Registre[$row->modul_professional_id]->Codi = $row->CodiMP;
					$this->Registre[$row->modul_professional_id]->Nom = $row->NomMP;
					$MP = new StdClass();
					$MP->Objectius = [];
					$MP->Fets = [];
					$MP->Procediments = [];
					$MP->Actituds = [];
					$this->Registre[$row->modul_professional_id]->MP = $MP;
					$ModulProfessionalId = $row->modul_professional_id;
				}
				switch ($row->tipus) {
					case 'O':
						array_push($MP->Objectius, $row->descripcio);
						break;
					case 'F':
						array_push($MP->Fets, $row->descripcio);
						break;
					case 'P':
						array_push($MP->Procediments, $row->descripcio);
						break;
					case 'A':
						array_push($MP->Actituds, $row->descripcio);
						break;
				}
			}
		}
	}	
	
	/**
	 * Genera la taula amb els RA d'un mòdul.
	 * @param integer $ModulId Identificador del mòdul.
     * @return string Taula amb les dades.
	 */
	public function GeneraTaulaModul(int $ModulId): string {
		$sRetorn = '';
		$this->CreaRegistreModul($ModulId);
		foreach($this->Registre as $MP) {
			$Id = ($this->Usuari->es_admin) ? ' ['.$MP->Id.']' : '';
			$sRetorn .= '    <ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('O').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Objectius as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('F').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Fets as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('P').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Procediments as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '        <li>'.$this->TipusText('A').'</li>'.PHP_EOL;
			$sRetorn .= '        <ul>'.PHP_EOL;
			foreach($MP->MP->Actituds as $descripcio)
				$sRetorn .= '            <li>'.$descripcio.'</li>'.PHP_EOL;
			$sRetorn .= '        </ul>'.PHP_EOL;
			$sRetorn .= '    </ul>'.PHP_EOL;
		}
		return $sRetorn;
	}	
}

?>