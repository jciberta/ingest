<?php

/** 
 * LibForms.php
 *
 * Llibreria de formularis:
 *  - {@link FormRecerca}
 *  - {@link FormFitxa} 
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */

require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibSQL.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibHTML.php');

/**
 * Classe Form.
 *
 * Classe base de la quals descendeixen els formularis.
 */
class Form {
	const Secret = '736563726574'; // Clau per a les funcions d'encriptació (hexadecimal). -> Cal passar-la a Config.php

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
	* Taula principal.
	* @access public
	* @var string
	*/    
    public $Taula = '';	

	/**
	* Clau primària de la taula.
	* @access public
	* @var string
	*/    
    public $ClauPrimaria = '';	

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	

	/**
	 * Obté el valor de diversos camp d'un registre donada una taula.
	 *
	 * @param string $Taula Taula de la base de dades.
	 * @param string $CampClau Clau primària de taula.
	 * @param string $ValorClau Valor de la clau primària de taula.
	 * @param string $Camps Nom del camps separats per comes del qual es volen obtenir els valors.
	 * @param string $Separador Separador entre els diferents camps en cas que n'hi haguessin.
	 * @return string Valor del camp o '' si no existeix.
	 */
	public function ObteCampsTaula($Taula, $CampClau, $ValorClau, $Camps, $Separador = ' ') {
		$Retorn = '';
		$SQL = 'SELECT '.$Camps.' FROM '.$Taula.' WHERE '.$CampClau.'='.$ValorClau;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			
			$aCamps = explode(",", TrimXX($Camps));
			for($i=0; $i < count($aCamps); $i++) {
				$Retorn .= utf8_encode($row[$aCamps[$i]]).$Separador;
			}
			$Retorn = substr($Retorn, 0, -strlen($Separador));
		}
		$ResultSet->close();
		return $Retorn;
	}	
} 

/**
 * Classe FormRecerca.
 *
 * Classe per als formularis de recerca.
 */
class FormRecerca extends Form {
	// Modalitats del formulari.
	const mfLLISTA = 1;
	const mfBUSCA = 2;

	/**
	* Modalitat del formulari.
	*  - mfLLISTA: Formulari de recerca on es poden visualitzar cada registre individualment (amb FormFitxa).
	*  - mfBUSCA: Formulari de recerca que serveix d'ajuda per a les seleccions de registres. 
	* @access public
	*/    
    public $Modalitat = self::mfLLISTA;
	/**
	* Sentència SQL per obtenir els registres a mostrar.
	* @access public
	* @var string
	*/    
    public $SQL = '';
	/**
	* Títol del formulari de recerca.
	* @access public
	* @var string
	*/    
    public $Titol = '';
	/**
	* Camps a visualitzar separats per comes.
	* @access public
	* @var string
	*/    
    public $Camps = '';
	/**
	* Títols de columnes separats per comes.
	* @access public
	* @var string
	*/    
    public $Descripcions = ''; 
	/**
	* Paraules a filtrar separades per espai (formaran part del WHERE).
	* @access public
	* @var string
	*/    
    public $Filtre = ''; 
	/**
	* Permet editar un registre.
	* @access public
	* @var boolean
	*/    
    public $PermetEditar = False; 
	/**
	* URL per a l'edició d'un registre.
	* @access public
	* @var boolean
	*/    
    public $URLEdicio = ''; 
	/**
	* Permet suprimir un registre.
	* @access public
	* @var boolean
	*/    
    public $PermetSuprimir = False; 
	
	/**
	* Opcions per a cada registre.
	* @access private
	* @var array
	*/    
    private $Opcions = [];	

	/**
	 * Crea la nova SQL a partir de les propietats SQL i Filtre.
     * @return string Sentència SQL.
	 */
	public function CreaSQL() {
		$sRetorn = $this->SQL;
		if ($this->Filtre != '') {
			$obj = new SQL($this->SQL);
//print_r($obj->CampAlies);
			$sWhere = '';
			$aFiltre = explode(" ", TrimX($this->Filtre));
			$aCamps = explode(",", TrimXX($this->Camps));
			foreach ($aFiltre as $sValor) {
				$sWhere .= '(';
				foreach ($aCamps as $sCamp) {
					if (array_key_exists($sCamp, $obj->CampAlies) && ($obj->CampAlies[$sCamp] != ''))
						$sWhere .= $obj->CampAlies[$sCamp] . " LIKE '%" . $sValor . "%' OR ";
					else
						$sWhere .= $sCamp . " LIKE '%" . $sValor . "%' OR ";
				}
				$sWhere = substr($sWhere, 0, -4) . ') AND ';
			}
			$sWhere = trim(substr($sWhere, 0, -5));
			if ($sWhere != '') {
				if (strlen($obj->Where) > 0)
					$obj->Where .= ' AND ' . $sWhere;
				else
					$obj->Where = $sWhere;
			}

			$sRetorn = $obj->GeneraSQL();
			
/*			// L'avaluació de ser estricta
			// http://php.net/manual/en/function.strpos.php
			if (strpos(strtoupper($this->SQL), ' WHERE ') !== false)
				$sRetorn .= ' AND ' . substr($sWhere, 0, -5);
			else
				$sRetorn .= ' WHERE ' . substr($sWhere, 0, -5);*/
		}
		return $sRetorn;
	}

	/**
	 * Genera una taula amb el resultat de la SQL.
     *
     * @return string Sentència SQL.
	 */
	public function GeneraTaula() {
		$sRetorn = '<DIV id=taula>';
		$SQL = $this->CreaSQL();
//print $SQL;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$sRetorn .= '<TABLE class="table table-striped">';
			// Capçalera
			$sRetorn .= '<THEAD class="thead-dark">';
			$aDescripcions = explode(",", TrimX($this->Descripcions));
			foreach ($aDescripcions as $sValor) {
				$sRetorn .= "<TH>" . $sValor . "</TH>";
			}
			$sRetorn .= '<TH></TH>';
			if ($this->Modalitat == self::mfLLISTA) 
				foreach($this->Opcions as $obj) 
					$sRetorn .= '<TH></TH>';
						
			$sRetorn .= '</THEAD>';

			// Dades
			$aCamps = explode(",", TrimXX($this->Camps));
			while($row = $ResultSet->fetch_assoc()) {
//print_r($row);
				$ParametreJS = JSONEncodeUTF8($row); 
				$ParametreJS = "'".str_replace('"', '~', $ParametreJS)."'"; 
//print_r($this->Modalitat);
				if ($this->Modalitat == self::mfBUSCA)
					$sRetorn .= '<TR style="cursor: pointer;" onClick="returnYourChoice('.$ParametreJS.')">';
				else
					$sRetorn .= "<TR>";
				foreach($aCamps as $data) {
					$sValor = $row[$data];
					$sRetorn .= utf8_encode("<TD>".$sValor."</TD>");
				}
				$sRetorn .= "<TD>";
				$Concatena = (strpos($this->URLEdicio, '?') > 0) ? '&' : '?';
				if ($this->Modalitat == self::mfLLISTA && $this->PermetEditar) {
					$sRetorn .= "<A href='".$this->URLEdicio.$Concatena."Id=".$row[$this->ClauPrimaria]."'><IMG src=img/edit.svg></A>&nbsp&nbsp";
				}
				if ($this->Modalitat == self::mfLLISTA && $this->PermetSuprimir) {
					$Funcio = 'SuprimeixRegistre("'.$this->Taula.'", "'.$this->ClauPrimaria.'", '.$row[$this->ClauPrimaria].');';
					$sRetorn .= "<A href=# onClick='".$Funcio."' data-toggle='modal' data-target='#confirm-delete'><IMG src=img/delete.svg></A>&nbsp&nbsp";
//					$sRetorn .= "<IMG src=img/delete.svg>&nbsp&nbsp";
				}
				$sRetorn .= "</TD>";
				if ($this->Modalitat == self::mfLLISTA) 
					$sRetorn .= $this->GeneraOpcions($row[$this->ClauPrimaria]);
				$sRetorn .= "</TR>";
			}
			$sRetorn .= "</TABLE>";
		}
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Genera el formulari per fer recerques.
	 */
	private function GeneraCerca() {
		$sRetorn = '<DIV id=Recerca style="padding:10px">';
		$sRetorn .= '  <FORM class="form-inline my-2 my-lg-0" id=form method="post" action="">';
		$sRetorn .= '    <input class="form-control mr-sm-2" type="text" style="width:500px" name="edtRecerca" placeholder="Text a cercar" aria-label="Search" autofocus onkeypress="RecercaKeyPress(event);">';

		// *** No pot ser un botó, ja que el submit del form fa recarregar la pàgina! (multitud d'hores perdudes!) ***
		//$sRetorn .= '    <button class="btn btn-outline-primary my-2 my-sm-0" name="btnRecerca" onclick="ActualitzaTaula(this);">Cerca</button>';
		$sRetorn .= '    <a class="btn btn-primary active" role="button" aria-pressed="true" id="btnRecerca" name="btnRecerca" onclick="ActualitzaTaula(this);">Cerca</a>';

		$sRetorn .= $this->GeneraPartOculta();
		$sRetorn .= '  </FORM>';
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Genera la part oculta per emmagatzemar valors.
	 */
	private function GeneraPartOculta() {
		$sRetorn = "";
		$FormSerialitzat = serialize($this);
		$FormSerialitzatEncriptat = SaferCrypto::encrypt($FormSerialitzat, hex2bin(Self::Secret));
		$sRetorn .= "<input type=hidden id=frm name=frm value='".bin2hex($FormSerialitzatEncriptat)."'>";
		return $sRetorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, $this->Titol, ($this->Modalitat == self::mfLLISTA));
		echo '<script language="javascript" src="js/Forms.js?v1.3" type="text/javascript"></script>';
		echo $this->GeneraCerca();
		echo $this->GeneraTaula();
		CreaFinalHTML();
	}
	
	/**
	 * Afegeix una opció per a cada registre.
	 * @param string $Titol Títol de l'opció.
	 * @param string $URL URL de l'opció. Se li afegirà l'identificador del registre.
	 */
	public function AfegeixOpcio($Titol, $URL) {
		$i = count($this->Opcions);
		$i++;
		$this->Opcions[$i] = new stdClass();
		$this->Opcions[$i]->Titol = $Titol;
		$this->Opcions[$i]->URL = $URL;
	}
	
	/**
	 * Genera les opcions per a cada registre.
	 * @param integer $Id Identificafdor del registre.
	 */
	private function GeneraOpcions($Id) {
		$Retorn = '';
		foreach($this->Opcions as $obj) {
			$Retorn .= '<TD>';
			$Retorn .= '<A HREF="'.$obj->URL.$Id.'">'.$obj->Titol.'<A>';
			$Retorn .= '</TD>';
		}
		return $Retorn;
	}
} 

/**
 * Classe FormFitxa.
 *
 * Classe per als formularis de fitxa.
 */
class FormFitxa extends Form {
	// Tipus de camps per al formulari.
	const tcTEXT = 1;
	const tcENTER = 2;
	const tcREAL = 3;
	const tcPASSWORD = 4;
	const tcMEMO = 5;
	const tcDATA = 6;
	const tcSELECCIO = 7;
	const tcCHECKBOX = 8;
	const tcLOOKUP = 9;
	
	/**
	* Indica si la clau primària de la taula és autoincrementable o no.
	* @access public
	* @var boolean
	*/    
    public $AutoIncrement = False;	
	/**
	* Camps del formulari amb les seves característiques. S'usa per generar els components visuals.
	* @access private
	* @var array
	*/    
    private $Camps = [];	
	/**
	* Títol del formulari de recerca.
	* @access public
	* @var string
	*/    
    public $Titol = '';
	/**
	* Permet editar un registre.
	* @access public
	* @var boolean
	*/    
    public $PermetEditar = False; 
	/**
	* Permet suprimir un registre.
	* @access public
	* @var boolean
	*/    
    public $PermetSuprimir = False; 
	/**
	* En el cas que s'estigui editant un registre, carrega les dades de la base de dades.
	* @access public
	* @var object
	*/    
    public $Registre = null;

	/**
	 * Afegeix un camp del tipus especificat al formulari.
	 *
	 * @param string $tipus Tipus de camp.
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param integer $longitud Longitud màxima.
	 * @return void
	 */
	private function Afegeix($tipus, $camp, $titol, $requerit, $longitud) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = $tipus;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Requerit = $requerit;
		$this->Camps[$i]->Longitud = 5*$longitud;
	}

	/**
	 * Afegeix un camp de tipus text al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param integer $longitud Longitud màxima.
	 * @return void
	 */
	public function AfegeixText($camp, $titol, $requerit, $longitud) {
		$this->Afegeix(self::tcTEXT, $camp, $titol, $requerit, $longitud);
	}

	/**
	 * Afegeix un camp de tipus enter al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param integer $longitud Longitud màxima.
	 * @return void
	 */
	public function AfegeixEnter($camp, $titol, $requerit, $longitud) {
		$this->Afegeix(self::tcENTER, $camp, $titol, $requerit, $longitud);
	}

	/**
	 * Afegeix un camp de tipus real al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param integer $longitud Longitud màxima.
	 * @return void
	 */
	public function AfegeixReal($camp, $titol, $requerit, $longitud) {
		$this->Afegeix(self::tcREAL, $camp, $titol, $requerit, $longitud);
	}

	/**
	 * Afegeix un camp de tipus password al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param integer $longitud Longitud màxima.
	 * @return void
	 */
	public function AfegeixPassword($camp, $titol, $requerit, $longitud) {
		$this->Afegeix(self::tcPASSWORD, $camp, $titol, $requerit, $longitud);
	}

	/**
	 * Afegeix un camp de tipus checkbox al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @return void
	 */
	public function AfegeixCheckBox($camp, $titol, $requerit) {
		$this->Afegeix(self::tcCHECKBOX, $camp, $titol, $requerit, 0);
	}

	/**
	 * Afegeix un camp de tipus data al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @return void
	 */
	public function AfegeixData($camp, $titol, $requerit) {
		$this->Afegeix(self::tcDATA, $camp, $titol, $requerit, 0);
	}
	
	/**
	 * Afegeix un ComboBox (desplegable) per triar un valor d'una llista.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param array $aCodis Codis de la llista. Per exemple: array(1, 2, 3, 4)
	 * @param array $aValors Valors de la llista. Per exemple: array("foo", "bar", "hello", "world")
	 * @return void
	 */
	public function AfegeixLlista($camp, $titol, $requerit, $aCodis, $aValors) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcSELECCIO;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Requerit = $requerit;
		$this->Camps[$i]->Llista = new stdClass();
		$this->Camps[$i]->Llista->Codis = $aCodis;
		$this->Camps[$i]->Llista->Valors = $aValors;
	}
	
	/**
	 * Afegeix un camp de tipus lookup al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param integer $longitud Longitud màxima.
	 * @param string $URL Pàgina web de recerca.
	 * @param string $Taula Taula associada.
	 * @param string $Id Identificador del registre que es mostra.
	 * @param string $Camps Camps a mostrar al lookup separats per comes.
	 * @return void
	 */
	public function AfegeixLookup($camp, $titol, $requerit, $longitud, $URL, $Taula, $Id, $Camps) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcLOOKUP;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Requerit = $requerit;
		$this->Camps[$i]->Longitud = 5*$longitud;
		$this->Camps[$i]->Lookup = new stdClass();
		$this->Camps[$i]->Lookup->URL = $URL;
		$this->Camps[$i]->Lookup->Taula = $Taula;
		$this->Camps[$i]->Lookup->Id = $Id;
		$this->Camps[$i]->Lookup->Camps = $Camps;
	}

	/**
	 * Retorna el valor d'un camp de tipus text que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampText($camp) {
		return ' value="'.utf8_encode($this->Registre[$camp]).'" ';
	}

	/**
	 * Retorna el valor d'un camp de tipus data que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampData($camp) {
		return ' value="'.MySQLAData($this->Registre[$camp]).'" ';
	}
	
	/**
	 * Retorna el valor d'un camp de tipus password que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampPassword($camp) {
		// Falta implementar!
		return '*****';
		//return ' value="'.utf8_encode($this->Registre[$camp]).'" ';
	}

	/**
	 * Retorna el valor d'un camp de tipus password que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampCheckBox($camp) {
		return ($this->Registre[$camp]) ? ' value=1 checked ' : ' value=0 ';
	}
	
	/**
	 * Genera la fitxa per l'edició.
	 */
	private function GeneraFitxa() {
		$sRetorn = '<DIV id=Fitxa>';
		$sRetorn .= '<FORM class="form-inline my-2 my-lg-0" id="frmFitxa" method="post" action="LibFormsAJAX.php">';
		$sRetorn .= "<input type=hidden name=hid_Taula value='".$this->Taula."'>";
		$sRetorn .= "<input type=hidden name=hid_ClauPrimaria value='".$this->ClauPrimaria."'>";
		$sRetorn .= "<input type=hidden name=hid_AutoIncrement value='".$this->AutoIncrement."'>";
		$sRetorn .= "<input type=hidden name=hid_Id value='".$this->Id."'>";
		$sRetorn .= '<TABLE>';
		foreach($this->Camps as $Valor) {
			$Requerit = ($Valor->Requerit ? ' required' : '');
			switch ($Valor->Tipus) {
				case self::tcTEXT:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Valor->Longitud.'px" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.'></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcENTER:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="ede_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Valor->Longitud.'px" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.' onkeydown="FormFitxaKeyDown(this, event, 0);"></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcREAL:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="edr_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Valor->Longitud.'px" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.' onkeydown="FormFitxaKeyDown(this, event, 1);"></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcPASSWORD:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="password" style="width:'.$Valor->Longitud.'px" name="pwd_'.$Valor->Camp.'" '.$this->ValorCampPassword($Valor->Camp).$Requerit.'></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcCHECKBOX:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="checkbox" name="chb_'.$Valor->Camp.'" '.$this->ValorCampCheckBox($Valor->Camp).$Requerit.'></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcDATA:
					$sRetorn .= '<TR>';
					$sNom = 'edd_' . $Valor->Camp;
					$sRetorn .= '<TD><label for='.$sNom.'>'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD>';
//					$sRetorn .= '<div id='.$sNom.' name='.$sNom.' class="input-group date" style="width:150px">';
					$sRetorn .= '<div id='.$sNom.' class="input-group date" style="width:150px">';
					$sRetorn .= '  <input type="text" class="form-control" name="'.$sNom.'" '.$this->ValorCampData($Valor->Camp).$Requerit.'>';
					$sRetorn .= '  <div class="input-group-append"><button class="btn btn-outline-secondary" type="button"><img src="img/calendar.svg"></button></div>';
					$sRetorn .= '</div>';
					$sRetorn .= '<script>$("#'.$sNom.'").datepicker({format: "dd/mm/yyyy", language: "ca"});</script>';
					$sRetorn .= '</TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcSELECCIO:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="cmb_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD>';
/*
	$sRetorn .= '<select name="'.$Nom.'">';
//  <option value="" selected disabled hidden>Escull...</option>	
	$LongitudCodi = count($Valor->Llista->Codis); 
	for ($i = 0; $i < $LongitudCodi; $i++)
	{
    	$sRetorn .= '<option value="'.$Valor->Llista->Codis[$i].'">'.utf8_encode($Valor->Llista->Valors[$i]).'</option>';
	} 	
	$sRetorn .= "</select>";
*/

$sRetorn .= '<div class="input-group mb-3">';
$sRetorn .= '  <select class="custom-select" name="cmb_'.$Valor->Camp.'">';
	$LongitudCodi = count($Valor->Llista->Codis); 
	for ($i = 0; $i < $LongitudCodi; $i++)
	{
		$Selected = ($Valor->Llista->Codis[$i] == $this->Registre[$Valor->Camp])? ' selected ' : '';
    	$sRetorn .= '<option value="'.$Valor->Llista->Codis[$i].'"'.$Selected.'>'.$Valor->Llista->Valors[$i].'</option>';
	} 	
$sRetorn .= '  </select>';
$sRetorn .= '</div>';


					$sRetorn .= '</TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcLOOKUP:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="lkp_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD>';
					$sRetorn .= '<div class="input-group mb-3">';
					$sRetorn .= "  <input type=hidden name=lkh_".$Valor->Camp." value=".$this->Registre[$Valor->Camp].">";
					$sRetorn .= "  <input type=hidden name=lkh_".$Valor->Camp."_camps value='".$Valor->Lookup->Camps."'>";
					$Text = $this->ObteCampsTaula($Valor->Lookup->Taula, $Valor->Lookup->Id, $this->Registre[$Valor->Camp], $Valor->Lookup->Camps);
					$sRetorn .= '  <input type="text" class="form-control" style="width:'.$Valor->Longitud.'px" name="lkp_'.$Valor->Camp.'" value="'.$Text.'">';
					$sRetorn .= '  <div class="input-group-append">';
					$onClick = "CercaLookup('lkh_".$Valor->Camp."', 'lkp_".$Valor->Camp."', '".$Valor->Lookup->URL."', '".$Valor->Lookup->Camps."');";
					$sRetorn .= '    <button class="btn btn-outline-secondary" type="button" onclick="'.$onClick.'">Cerca</button>';
					$sRetorn .= '  </div>';
					$sRetorn .= '</div>';
					$sRetorn .= '</TD>';
					$sRetorn .= '</TR>';
					break;
			}
		}
		$sRetorn .= '<TR><TD><a class="btn btn-primary active" role="button" aria-pressed="true" id="btnDesa" name="btnDesa" onclick="DesaFitxa(this.form);">Desa</a></TDR></TR>';
		$sRetorn .= '</TABLE>';
		$sRetorn .= '</FORM>';
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Carrega les dades de la base de dades en el cas s'editi un registre existent.
	 */
	private function CarregaDades() {
		if ($this->Id > 0) {
			$SQL = 'SELECT * FROM '.$this->Taula.' WHERE '.$this->ClauPrimaria.'='.$this->Id;
			$ResultSet = $this->Connexio->query($SQL);
			if ($ResultSet->num_rows > 0) {
				$this->Registre = $ResultSet->fetch_assoc();
			}
		}
	}

	/**
	 * Genera els missatges de succés i error per quan es desen les dades.
	 */
	private function GeneraMissatges() {
		$sRetorn = '<div class="alert alert-success collapse" id="MissatgeCorrecte" role="alert">';
		$sRetorn .= "El registre s'ha desat correctament.";
		$sRetorn .= '</div>';
		$sRetorn = '<div class="alert alert-danger collapse" id="MissatgeError" role="alert">';
		$sRetorn .= "Hi ha hagut un error en desar el registre.";
		$sRetorn .= '</div>';
		return $sRetorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, $this->Titol);
		echo '<script language="javascript" src="js/Forms.js?v1.3" type="text/javascript"></script>';
		if ($this->Id > 0)
			$this->CarregaDades();
		echo $this->GeneraFitxa();
		echo $this->GeneraMissatges();
		CreaFinalHTML();
	}
} 

?>
