<?php

/** 
 * LibForms.php
 *
 * Llibreria de formularis:
 *  - {@link FormRecerca}
 *  - {@link FormFitxa} -> PENDENT!
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */

require_once('LibStr.php');
require_once('LibSQL.php');
require_once('LibHTML.php');

/**
 * Classe Form.
 *
 * Classe base de la quals descendeixen els formularis.
 */
class Form {
	/**
	* Connexió a la base de dades.
	* @access protected 
	* @var object
	*/    
	protected $Connexio;

	/**
	 * Constructor de l'objecte.
	 *
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con) {
		$this->Connexio = $con;
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
	 * Crea la nova SQL a partir de les propietats {@link $SQL} i {@link $Filtre}.
     *
     * @return string Sentència SQL.
	 */
	public function CreaSQL() {
		$sRetorn = $this->SQL;
		if ($this->Filtre != '') {
			$obj = new SQL($this->SQL);

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
			$sRetorn .= ' WHERE ' . substr($sWhere, 0, -5);
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
//				$sRetorn .= "<TH>" . utf8_encode($sValor) . "</TH>";
			}
//			if ($this->Modalitat == self::mfLLISTA && ($this->PermetEditar || $this->PermetSuprimir))
				$sRetorn .= '<TH></TH>';
			$sRetorn .= '</THEAD>';

			// Dades
			$aCamps = explode(",", TrimXX($this->Camps));
			while($row = $ResultSet->fetch_assoc()) {
//print_r($row);
				$ParametreJS = JSONEncodeUTF8($row); 
				$ParametreJS = "'".str_replace('"', '~', $ParametreJS)."'"; 
				if ($this->Modalitat == self::mfBUSCA)
					$sRetorn .= '<TR style="cursor: pointer;" onClick="returnYourChoice('.$ParametreJS.')">';
				else
					$sRetorn .= "<TR>";
				foreach($aCamps as $data) {
					$sValor = $row[$data];

//					if ($this->Modalitat == self::mfBUSCA) {
//						$sRetorn .= utf8_encode('<TD><A href=# onclick="returnYourChoice('.$ParametreJS.')">'.$sValor.'</A></TD>');
//					}
//					else
						$sRetorn .= utf8_encode("<TD>".$sValor."</TD>");

				}
				$sRetorn .= "<TD>";
				$Concatena = (strpos($this->URLEdicio, '?') > 0) ? '&' : '?';
				if ($this->Modalitat == self::mfLLISTA && $this->PermetEditar) {
					$sRetorn .= "<A href='".$this->URLEdicio.$Concatena."Id=".$row[$this->ClauPrimaria]."'><IMG src=../img/edit.svg></A>&nbsp&nbsp";
				}
				if ($this->Modalitat == self::mfLLISTA && $this->PermetSuprimir) {
					$sRetorn .= "<IMG src=../img/delete.svg>&nbsp&nbsp";
				}
				$sRetorn .= "</TD>";
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
		$sRetorn = '<DIV id=Recerca>';
		$sRetorn .= '  <FORM class="form-inline my-2 my-lg-0" id=form method="post" action="">';
		$sRetorn .= '    <input class="form-control mr-sm-2" type="text" name="edtRecerca" placeholder="Text a cercar" aria-label="Search" autofocus onkeypress="RecercaKeyPress(event);">';

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
		$sRetorn = "<input type=hidden name=edtSQL value='".$this->SQL."'>";
		$sRetorn .= "<input type=hidden name=edtCamps value='".$this->Camps."'>";
		$sRetorn .= "<input type=hidden name=edtDescripcions value='".$this->Descripcions."'>";
		return $sRetorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function GeneraHTML() {
		CreaIniciHTML($this->Titol, ($this->Modalitat == self::mfLLISTA));
		echo '<script language="javascript" src="js/Forms.js" type="text/javascript"></script>';
		echo $this->GeneraCerca();
		echo $this->GeneraTaula();
		CreaFinalHTML();
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
	
	/**
	* Taula de la base de dades de la que es fa la fitxa.
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
	 * Constructor de l'objecte.
	 *
	 * @param objecte $conn Connexió a la base de dades.
	 */
/*	function __construct($con) {
		$this->Connexio = $con;
	}*/

	/**
	 * Afegeix un camp del tipus especificat al formulari.
	 *
	 * @param string $tipus Tipus de camp.
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param array $longitud Longitud màxima.
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
		$this->Camps[$i]->Longitud = $longitud;
	}

	/**
	 * Afegeix un camp de tipus text al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param array $longitud Longitud màxima.
	 * @return void
	 */
	public function AfegeixText($camp, $titol, $requerit, $longitud) {
		$this->Afegeix(self::tcTEXT, $camp, $titol, $requerit, $longitud);
	}

	/**
	 * Afegeix un camp de tipus password al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $requerit Indica si el camp és obligatori.
	 * @param array $longitud Longitud màxima.
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
	 * Retorna el valor d'un camp de tipus text que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampText($camp) {
		return ' value="'.utf8_encode($this->Registre[$camp]).'" ';
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
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.'></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcPASSWORD:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="password" name="pwd_'.$Valor->Camp.'" '.$this->ValorCampPassword($Valor->Camp).$Requerit.'></TD>';
					$sRetorn .= '</TR>';
					break;
				case self::tcCHECKBOX:
					$sRetorn .= '<TR>';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="checkbox" name="chb_'.$Valor->Camp.'" '.$this->ValorCampCheckBox($Valor->Camp).$Requerit.'></TD>';
					$sRetorn .= '</TR>';
					break;
			}
		}
		$sRetorn .= '<TR><a class="btn btn-primary active" role="button" aria-pressed="true" id="btnDesa" name="btnDesa" onclick="DesaFitxa(this.form);">Desa</a></TR>';
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
		$sRetorn .= "Hi ha hagut un error en desat el registre.";
		$sRetorn .= '</div>';
		return $sRetorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Titol);
		echo '<script language="javascript" src="js/Forms.js" type="text/javascript"></script>';
		if ($this->Id > 0)
			$this->CarregaDades();
		echo $this->GeneraFitxa();
		echo $this->GeneraMissatges();
		CreaFinalHTML();
	}
} 

?>
