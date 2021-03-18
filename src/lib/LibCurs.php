<?php

/** 
 * LibCurs.php
 *
 * Llibreria d'utilitats per als cursos.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibHTML.php');


/**
 * Classe que encapsula les utilitats per al maneig del curs.
 */
class Curs
{
	// Estats del curs
	const Actiu = 'A'; 		// Entrada de notes.
	const Junta = 'J'; 		// Realització de l’avaluació (pantalla especial).
	const Inactiu = 'I'; 	// Bloquejat.
	const Obert = 'O'; 		// Visualització de butlletins.
	const Tancat = 'T'; 	// Curs tancat.
	// Flux:
	//  - Actiu: el professors entren notes.
	//  - Junta: es realitza l’avaluació.
	//  - Inactiu: un cop acabada l’avaluació, es bloqueja la modificació de notes.
	//  - Obertura: es mostren els butlletins.
	//  - ...
	//  - Tancat: es tanca el curs.
	
	// Colors dels estats del curs
	const COLOR_ACTIU = '#00FF00';
	const COLOR_JUNTA = '#CC00CC';
	const COLOR_INACTIU = '#CCCCCC';
	const COLOR_OBERTURA = '#FF9900';
	const COLOR_TANCAT = '#FF0000';
	
	/**
	* Connexió a la base de dades.
	* @access public 
	* @var object
	*/    
	public $Connexio;

	/**
	* Usuari autenticat.
	* @access public 
	* @var object
	*/    
	public $Usuari;

	/**
	* Registre carregat amb CarregaRegistre.
	* @access private
	* @var object
	*/    
	private $Registre = NULL;

	/**
	* Indica que només és professor i no admin, ni cap d'estudis...
	* @var boolean
	*/    
	private $NomesProfessor = True;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
		$this->NomesProfessor = ($this->Usuari->es_professor && !$this->Usuari->es_admin && !$this->Usuari->es_direccio && !$this->Usuari->es_cap_estudis);			
	}	

	/**
	 * Carrega el registre especificat de la taula CURS.
	 * @param integer $Id Identificador del registre.
	 */				
	public function CarregaRegistre($Id) {
		$SQL = "SELECT * FROM CURS WHERE curs_id=".$Id;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$this->Registre = $ResultSet->fetch_object();
		}
	}
	
	/**
	 * Retorna l'estat del curs.
     * @return string estat del curs.
	 */
	public function Estat(): string {
		$sRetorn = '';
		if ($this->Registre != NULL)
			$sRetorn = $this->Registre->estat;
		return $sRetorn;
	}
	
	/**
	 * Obté el codi del cicle formatiu del curs.
	 * @returns integer Identificador del cicle formatiu, sinó -1.
	 */
	function ObteCicleFormatiuId() {
		if ($this->Registre === NULL) 
			return -1;
		else
			return $this->Registre->cicle_formatiu_id;
	}

	/**
	 * Obté el nivell del curs.
	 * @returns integer Nivell del curs, sinó -1.
	 */
	function ObteNivell() {
		if ($this->Registre === NULL) 
			return -1;
		else
			return $this->Registre->nivell;
	}

	/**
	 * Crea la SQL pel llistat de cursos.
	 * @param integer $CursId Identificador del curs (opcional).
     * @return string Sentència SQL.
	 */
	private function CreaSQL(int $CursId = -1) {
		$SQL = ' SELECT C.cicle_formatiu_id, C.curs_id, C.codi, C.nom AS NomCurs, C.estat, C.nivell, '.
			' CONCAT(AA.any_inici,"-",AA.any_final) AS Any, '.
			' CASE '.
			'     WHEN C.estat = "T" THEN "Tancada" '.
			'     WHEN C.avaluacio = "ORD" THEN "Ordinària" '.
			'     WHEN C.avaluacio = "EXT" THEN "Extraordinària" '.
			' END AS avaluacio, '.
			' CASE '.
			'     WHEN C.avaluacio = "ORD" THEN C.trimestre '.
			'     WHEN C.avaluacio = "EXT" THEN NULL '.
			' END AS trimestre '.
			' FROM CURS C '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE (0=0) ';
		if ($this->NomesProfessor)			
			$SQL .= ' AND C.curs_id IN ( '.
			' SELECT DISTINCT C.curs_id FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_FORMATIVA UF ON (PUF.uf_id=UF.unitat_formativa_id) '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (UF.modul_professional_id=MP.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (MP.cicle_formatiu_id=CF.cicle_formatiu_id) '.
			' LEFT JOIN CURS C ON (C.cicle_formatiu_id=CF.cicle_formatiu_id AND UF.nivell=C.nivell) '.
			' WHERE professor_id='.$this->Usuari->usuari_id.		
			' ) ';
		if ($CursId != -1)
			$SQL .= ' AND C.curs_id='.$CursId;
		return $SQL;
	}

	/**
	 * Crea la SQL pel llistat de cursos actuals.
     * @return string Sentència SQL.
	 */
	private function CreaSQLCursosActuals() {
		$SQL = ' SELECT C.curs_id, C.codi, C.nom AS NomCurs, C.nivell, C.estat, '.
			' CONCAT(AA.any_inici,"-",AA.any_final) AS Any, '.
			' CASE '.
			'     WHEN C.estat = "T" THEN "Tancada" '.
			'     WHEN C.avaluacio = "ORD" THEN "Ordinària" '.
			'     WHEN C.avaluacio = "EXT" THEN "Extraordinària" '.
			' END AS avaluacio, '.
			' CASE '.
			'     WHEN C.avaluacio = "ORD" THEN C.trimestre '.
			'     WHEN C.avaluacio = "EXT" THEN NULL '.
			' END AS trimestre '.
			' FROM CURS C '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE AA.actual=1 ';
		return $SQL;
	}

	/**
	 * Retorna el text de l'estat.
	 * @param string $sEstat Codi de l'estat.
     * @return string Text de l'estat.
	 */
	static public function TextEstat(string $sEstat): string {
		switch ($sEstat) {
			case "A":
				return 'Actiu';
				break;
			case "J":
				return 'Junta';
				break;
			case "I":
				return 'Inactiu';
				break;
			case "O":
				return 'Obert';
				break;
			case "T":
				return 'Tancat';
				break;
			default:
				return '';
		}
	}

	/**
	 * Retorna el text de l'estat (inclosa la imatge).
	 * @param string $sEstat Codi de l'estat.
     * @return string Text de l'estat en format HTML.
	 */
	static public function TextEstatColor(string $sEstat): string {
		switch ($sEstat) {
			case "A":
				return '<img src=img/colorA.png> Actiu. Entrada de notes.';
				break;
			case "J":
				return '<img src=img/colorJ.png> Junta. Realització de l’avaluació (pantalla especial).';
				break;
			case "I":
				return '<img src=img/colorI.png> Inactiu. Bloquejat per als professors i alumnes.';
				break;
			case "O":
				return '<img src=img/colorO.png> Obert. Visualització de butlletins.';
				break;
			case "T":
				return '<img src=img/colorT.png> Tancat. Els curs està tancat.';
				break;
			default:
				return '';
		}
	}

	/**
	 * Retorna la llegenda dels estats del curs.
     * @return string Llegenda en format HTML.
	 */
	static public function LlegendaEstat(): string {
		$Retorn = 'Els diferents estats en que pot estar un curs són els següents:<br>'.
			Self::TextEstatColor('A').'<br>'.
			Self::TextEstatColor('J').'<br>'.
			Self::TextEstatColor('I').'<br>'.
			Self::TextEstatColor('O').'<br>'.
			Self::TextEstatColor('T').'<br>';
		return $Retorn;
	}
	
	/**
	 * Genera el llistat de cursos.
	 */
	public function EscriuFormulariRecera() {
		$SQL = $this->CreaSQL();
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->AfegeixJavaScript('Matricula.js?v1.2');
		$frm->Titol = 'Cursos';
		$frm->SQL = utf8_decode($SQL);
		$frm->Taula = 'CURS';
		$frm->ClauPrimaria = 'curs_id';
		$frm->Camps = 'codi, NomCurs, nivell, Any, avaluacio, trimestre';
		$frm->Descripcions = 'Codi, Nom, Nivell, Any, Avaluació, Trimestre';
		$frm->AfegeixOpcioColor('Estat', 'estat', 'color', 'png', Curs::LlegendaEstat());
		if (!$this->NomesProfessor) {
			$frm->AfegeixOpcio('Alumnes', 'UsuariRecerca.php?accio=Matricules&CursId=');
			$frm->AfegeixOpcio('Grups', 'Grups.php?CursId=');
		}
		$frm->AfegeixOpcio('Notes', 'Notes.php?CursId=');
		if (!$this->NomesProfessor) {
			$frm->AfegeixOpcio('Avaluació', 'Avaluacio.php?CursId=');
			$frm->AfegeixOpcio('Butlletins en PDF', 'GeneraExpedientsPDF.php?CursId=', '', 'pdf.png');
			$frm->AfegeixOpcio('Estadístiques', 'Estadistiques.php?accio=EstadistiquesNotesCurs&CursId=', '', 'pie.svg');
		}
		if ($this->Usuari->es_admin) {
			$frm->AfegeixOpcioAJAX('[EliminaMatricula]', 'EliminaMatriculaCurs');
		}
		if (!$this->NomesProfessor) {
			$frm->PermetEditar = True;
			$frm->URLEdicio = 'Fitxa.php?accio=Curs';
			$frm->PermetAfegir = ($this->Usuari->es_admin || $this->Usuari->es_direccio || $this->Usuari->es_cap_estudis);
		}

		// Filtre
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('C.any_academic_id', 'Any', 100, $aAnys[0], $aAnys[1]);
		$frm->EscriuHTML();
	}

	/**
	 * Genera una pàgina amb les estadístiques d'un o més cursos indicats per una SQL.
	 * @param string $SQL Sentència SQL amb els cursos.
	 * @return string Codi HTML de la pàgina.
	 */				
	private function GeneraEstadistiques(string $SQL): string
	{
		$Retorn = GeneraIniciHTML($this->Usuari, 'Estadístiques cursos');
		$Retorn .= '<script language="javascript" src="vendor/Chart.min.js" type="text/javascript"></script>';
		
		//$bColumna1 = true;
		$Retorn .= '<TABLE>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD width=600px>';
		$ResultSet = $this->Connexio->query($SQL);
		while ($objCurs = $ResultSet->fetch_object()) {
			$Nivell = $objCurs->nivell;
			$Notes = new Notes($this->Connexio, $this->Usuari);
//print_r($objCurs);			
//exit;
			$Notes->CarregaRegistre($objCurs->curs_id, $Nivell, $objCurs->avaluacio);
			$Retorn .= $Notes->GeneraEstadistiquesCurs($objCurs, $Nivell);
			$Retorn .= '<BR>';
			$Retorn .= '</TD>';
			$Retorn .= '<TD width=500px>';
			$Retorn .= $Notes->GeneraPastisEstadistiquesCurs($objCurs, $Nivell);
			$Retorn .= '</TD>';
			//if (!$bColumna1)
				$Retorn .= '</TR><TR>';
			$Retorn .= '<TD width=600px>';
			//$bColumna1 = !$bColumna1;
		}
		$ResultSet->close();		
		$Retorn .= '</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TABLE>';
		
		$Retorn .= '';
		return $Retorn;
	}
	
	/**
	 * Genera una pàgina amb les estadístiques de les notes dels cursos actuals.
	 * @return string Codi HTML de la pàgina.
	 */				
	public function Estadistiques()
	{
		$SQL = $this->CreaSQLCursosActuals();
		return $this->GeneraEstadistiques($SQL);
	}
	
	/**
	 * Genera una pàgina amb les estadístiques de les notes d'un curs.
	 * @param integer $CursId Identificador del curs.
	 * @return string Codi HTML de la pàgina.
	 */				
	public function EstadistiquesCurs(int $CursId)
	{
		$SQL = $this->CreaSQL($CursId);
		return $this->GeneraEstadistiques($SQL);
	}
}

/**
 * Classe que encapsula les utilitats per al maneig dels grups-classe.
 */
class GrupClasse 
{
	/**
	* Connexió a la base de dades.
	* @var object
	*/    
	public $Connexio;

	/**
	* Usuari autenticat.
	* @var object
	*/    
	public $Usuari;
	
	/**
	* Registre de la base de dades que conté les dades d'una matrícula.
	* @var array
	*/    
    private $Registre = [];

	/**
	 * Constructor de l'objecte.
	 * @param object $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}

	/**
	 * Carrega el registre especificat de la taula CURS.
	 * @param integer $Id Identificador del registre.
	 */				
	public function Carrega(int $CursId) {
		$SQL = " SELECT * FROM CURS WHERE curs_id=$CursId ";
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$this->Registre = $ResultSet->fetch_object();
		}
	}
	
	/**
	 * Carrega els diferents grups-classe d'un curs i els emmagatzema en l'atribut Registre.
     * @param int $CursId Identificador del curs.
	 */
	/*public function Carrega(int $CursId) {
		$SQL = " SELECT DISTINCT grup ".
			" FROM MATRICULA M ".
			" WHERE curs_id=$CursId ".
			" ORDER BY grup ";	
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$row = $ResultSet->fetch_object();
			while($row) {
				if ($row->grup != '')
					array_push($this->Registre, $row->grup);
				$row = $ResultSet->fetch_object();
			}
		}
	}*/
	
	/**
	 * Genera un array amb els grups d'un curs.
     * @param int $CursId Identificador del curs.
	 * @return array Grups dels curs.
	 */
	public function ObteGrups(int $CursId): array {
		$this->Carrega($CursId);
		return explode(',', $this->Registre->grups_classe);
	}
	
	/**
	 * Genera els checkboxs per filtrar per grup.
     * @param int $CursId Identificador del curs.
	 * @return string Codi HTML del filtre.
	 */
	public function GeneraMostraGrup(int $CursId): string {
		$Retorn = '';
		$aGrups = $this->ObteGrups($CursId);
		foreach ($aGrups as $Grup) {
			$Valor = '"'.$Grup.'"';	
			$Retorn .= "<input type='checkbox' name='chbGrup$Grup' checked onclick='MostraGrup(this, $Valor);'>Grup $Grup &nbsp";
		}
		return $Retorn;
	}
}

/**
 * Classe que encapsula les utilitats per al maneig dels grups de tutoria.
 */
class GrupTutoria 
{
	/**
	* Connexió a la base de dades.
	* @var object
	*/    
	public $Connexio;

	/**
	* Usuari autenticat.
	* @var object
	*/    
	public $Usuari;
	
	/**
	* Registre de la base de dades que conté les dades d'una matrícula.
	* @var array
	*/    
    private $Registre = [];

	/**
	 * Constructor de l'objecte.
	 * @param object $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}

	/**
	 * Carrega el registre especificat de la taula CURS.
	 * @param integer $Id Identificador del registre.
	 */				
	public function Carrega(int $CursId) {
		$SQL = " SELECT * FROM CURS WHERE curs_id=$CursId ";
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$this->Registre = $ResultSet->fetch_object();
		}
	}

	/**
	 * Genera un array amb els grups d'un curs.
     * @param int $CursId Identificador del curs.
	 * @return array Grups dels curs.
	 */
	public function ObteGrups(int $CursId): array {
		$this->Carrega($CursId);
		return explode(',', $this->Registre->grups_tutoria);
	}

	/**
	 * Genera els checkboxs per filtrar per grup.
     * @param int $CursId Identificador del curs.
	 * @return string Codi HTML del filtre.
	 */
	public function GeneraMostraGrup(int $CursId): string {
		$Retorn = '';
		$aGrups = $this->ObteGrups($CursId);
		foreach ($aGrups as $GrupTutoria) {
			$Valor = '"'.$GrupTutoria.'"';	
			$Retorn .= "<input type='checkbox' name='chbGrup$GrupTutoria' checked onclick='MostraTutoria(this, $Valor);'>Tutoria $GrupTutoria &nbsp";
		}
		return $Retorn;
	}
}

?>