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
	* Fitxers JavaScript.
	* @access protected
	* @var array
	*/    
    protected $FitxerJS = [];	

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	

	/**
	 * Afegeix un fitxer JavaScript.
	 *
	 * @param string $Fitxer Fitxer JavaScript.
	 */
	public function AfegeixJavaScript($Fitxer) {
		$i = count($this->FitxerJS);
		$i++;
		$this->FitxerJS[$i] = $Fitxer;
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
	
	/**
	 * CreaDesplegable
	 *
	 * Crea un desplegable (combobox) HTML com a 2 cel·les d'una taula.
	 * Ús: CreaDesplegable(array(1, 2, 3, 4), array("foo", "bar", "hello", "world"));
	 *
	 * @param string $Nom Nom del desplegable.
	 * @param string $Titol Títol del desplegable.
	 * @param integer $Longitud Longitud del desplegable.
	 * @param array $Codi Codis de la llista.
	 * @param array $Valor Valors de la llista.
	 * @return void
	 */
	public function CreaDesplegable(string $Nom, string $Titol, int $Longitud, array $Codi, array $Valor): string
	{
		$sRetorn = '<TD><label for="cmb_'.$Nom.'">'.$Titol.'</label></TD>';
		$sRetorn .= '<TD>';
		$sRetorn .= '  <select class="custom-select" style="width:'.$Longitud.'px" name="cmb_'.$Nom.'">';
		$LongitudCodi = count($Codi); 
		for ($i = 0; $i < $LongitudCodi; $i++)
		{
			$Selected = ''; // Falta implementar!
			$sRetorn .= '<option value="'.$Codi[$i].'"'.$Selected.'>'.$Valor[$i].'</option>';
		} 	
		$sRetorn .= '  </select>';
		$sRetorn .= '</TD>';
		return $sRetorn;
	}

	/**
	 * Crea un "lookup" (element INPUT + BUTTON per cercar les dades en una altra finestra).
	 * Conté:
	 *  - Camp amagat on hi haurà el identificador (camp lkh_).
	 *  - Camp amagat on hi haurà els camps a mostrar dels retornats (camp lkh_X_camps).
	 *  - Camp text on hi haurà la descripció (camp lkp_).
	 *  - Botó per fer la recerca.	 
	 *
	 * @param string $Nom Nom del lookup.
	 * @param string $Titol Títol del camp.
	 * @param integer $Longitud Longitud màxima.
	 * @param string $URL Pàgina web de recerca.
	 * @param string $Taula Taula associada.
	 * @param string $Id Identificador del registre que es mostra.
	 * @param string $Camps Camps a mostrar al lookup separats per comes.
	 * @param array $off Opcions del formulari.
	 * @return string Codi HTML del lookup.
	 */
	public function CreaLookup(string $Nom, string $Titol, int $Longitud, string $URL, string $Taula, string $Id, string $Camps, array $off = []) {
		$NomesLectura = '';
		$sRetorn = '<TD><label for="lkp_'.$Nom.'">'.$Titol.'</label></TD>';
		$sRetorn .= '<TD>';
		$sRetorn .= '<div class="input-group mb-3">';
		$sRetorn .= "  <input type=hidden name=lkh_".$Nom.">";
		$sRetorn .= "  <input type=hidden name=lkh_".$Nom."_camps value='".$Camps."'>";
//		$Text = $this->ObteCampsTaula($Valor->Lookup->Taula, $Valor->Lookup->Id, $this->Registre[$Valor->Camp], $Valor->Lookup->Camps);
		$sRetorn .= '  <input type="text" class="form-control" style="width:'.$Longitud.'px" name="lkp_'.$Nom.'" value=""'.$NomesLectura.'>';
		$sRetorn .= '  <div class="input-group-append">';
		$onClick = "CercaLookup('lkh_".$Nom."', 'lkp_".$Nom."', '".$URL."', '".$Camps."');";
//		$onClick = ($NomesLectura) ? '': $onClick;
		$sRetorn .= '    <button class="btn btn-outline-secondary" type="button" onclick="'.$onClick.'">Cerca</button>';
		$sRetorn .= '  </div>';
		$sRetorn .= '</div>';
		$sRetorn .= '</TD>';
		return $sRetorn;
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
	
	// Tipus d'opcions
	const toURL = 1;
	const toAJAX = 2;

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
	* Opcions per a cada registre. Estan incloses les opcions AJAX.
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
						$sWhere .= $this->EliminaTipusPredefinit($sCamp) . " LIKE '%" . $sValor . "%' OR ";
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
	 * Elimina el prefix, en el cas de tenir un tipus predefinit (per exemple bool:).
	 * @param string $camp Camp.
     * @return string Camp sense el tipus predefinit.
	 */
	private function EliminaTipusPredefinit(string $camp): string {
		// Mirem si té algun tipus predefinit per mostrar
		$aCamp = explode(':', $camp);
		if (count($aCamp)>1) 
			return $aCamp[1];
		else
			return $aCamp[0];
	}

	/**
	 * Genera una taula amb el resultat de la SQL.
     *
     * @return string Taula amb les dades.
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
					// Mirem si té algun tipus predefinit per mostrar
					$aData = explode(':', $data);
					if (count($aData)>1) {
						if ($aData[0] == 'bool') {
							$sValor = ($row[$aData[1]]) ? 'Sí' : '';
						$sRetorn .= "<TD>".$sValor."</TD>";
						}
					}
					else {
						$sValor = $row[$data];
						$sRetorn .= utf8_encode("<TD>".$sValor."</TD>");
					}
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
					$sRetorn .= $this->GeneraOpcions($row[$this->ClauPrimaria], $row);
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
		$FormSerialitzatEncriptat = Encripta($FormSerialitzat);
//		$FormSerialitzatEncriptat = SaferCrypto::encrypt($FormSerialitzat, hex2bin(Self::Secret));
		$sRetorn .= "<input type=hidden id=frm name=frm value='".bin2hex($FormSerialitzatEncriptat)."'>";
		return $sRetorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, $this->Titol, ($this->Modalitat == self::mfLLISTA));
		echo '<script language="javascript" src="js/Forms.js?v1.4" type="text/javascript"></script>';
		for($i = 1; $i <= count($this->FitxerJS); $i++) {
			echo '<script language="javascript" src="js/'.$this->FitxerJS[$i].'" type="text/javascript"></script>';
		}
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
		$this->Opcions[$i]->Tipus = self::toURL;
		$this->Opcions[$i]->Titol = $Titol;
		$this->Opcions[$i]->URL = $URL;
	}

	/**
	 * Afegeix una opció AJAX per a cada registre. Un cop executada es tornarà a cridar ...
	 * @param string $Titol Títol de l'opció.
	 * @param string $Funcio Funció JavaScript. 
	 * @param string $Camp Camp del registre que serveix com a identificador. 
	 * 		Si no s'especifica, Com a paràmetre es passarà l'identificador del registre. 
	 */
	public function AfegeixOpcioAJAX($Titol, $Funcio, $Camp = '') {
		$i = count($this->Opcions);
		$i++;
		$this->Opcions[$i] = new stdClass();
		$this->Opcions[$i]->Tipus = self::toAJAX;
		$this->Opcions[$i]->Titol = $Titol;
		$this->Opcions[$i]->Funcio = $Funcio;
		$this->Opcions[$i]->Camp = $Camp;
	}
	
	/**
	 * Genera les opcions per a cada registre.
	 * @param integer $Id Identificafdor del registre.
	 * @param array $row Registre.
	 */
	private function GeneraOpcions($Id, $row) {
		$Retorn = '';
		foreach($this->Opcions as $obj) {
			$Retorn .= '<TD>';
			if ($obj->Tipus == self::toURL)
				$Retorn .= '<A HREF="'.$obj->URL.$Id.'">'.$obj->Titol.'<A>';
			else if ($obj->Tipus == self::toAJAX) {
				if ($obj->Tipus == '')
					$Retorn .= '<A HREF="#" onClick="'.$obj->Funcio.'('.$Id.')";>'.$obj->Titol.'<A>';
				else 
					$Retorn .= '<A HREF="#" onClick="'.$obj->Funcio.'('.$row[$obj->Camp].')";>'.$obj->Titol.'<A>';
//				$Retorn .= 'AJAX';
//				echo "<TD width=2><input type=text ".$Deshabilitat." style='".$style."' name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." id='".$Id."' value='".$ValorNota."' size=1 onfocus='ObteNota(this);' onBlur='ActualitzaNota(this);' onkeydown='NotaKeyDown(this, event);'></TD>";
//				$Retorn .= '<A HREF="#" onClick="'.$obj->Funcio.'('.$Id.')";>'.$obj->Titol.'<A>';
			}
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
	const tcPESTANYA = 10;
	const tcCOLUMNA_INICI = 11;
	const tcCOLUMNA_SALT = 12;
	const tcCOLUMNA_FINAL = 13;
	
	// Opcions del FormFitxa.
	const offNOMES_LECTURA = 1;  // Indica si el camp és pot escriure o no.
	const offREQUERIT = 2; // Indica si el camp és obligatori.
	const offAL_COSTAT = 3;      // Indica si el camp es posiciona al costat de l'anterior (per defecte a sota).
	
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
	* Indica si el formulari té pestanyes.
	* @access private
	* @var boolean
	*/    
    private $HiHaPestanyes = False;	

	/**
	 * Afegeix un camp del tipus especificat al formulari.
	 *
	 * @param string $tipus Tipus de camp.
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	private function Afegeix(string $tipus, string $camp, string $titol, int $longitud, array $off = []) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = $tipus;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Longitud = 5*$longitud;
		$this->Camps[$i]->Opcions = $off;
	}

	/**
	 * Afegeix un camp de tipus text al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixText(string $camp, string $titol, int $longitud, array $off = []) {
		$this->Afegeix(self::tcTEXT, $camp, $titol, $longitud, $off);
	}

	/**
	 * Afegeix un camp de tipus enter al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixEnter(string $camp, string $titol, $longitud, array $off = []) {
		$this->Afegeix(self::tcENTER, $camp, $titol, $longitud, $off);
	}

	/**
	 * Afegeix un camp de tipus real al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixReal(string $camp, string $titol, int $longitud, array $off = []) {
		$this->Afegeix(self::tcREAL, $camp, $titol, $longitud, $off);
	}

	/**
	 * Afegeix un camp de tipus password al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixPassword(string $camp, string $titol, int $longitud, array $off = []) {
		$this->Afegeix(self::tcPASSWORD, $camp, $titol, $longitud, $off);
	}

	/**
	 * Afegeix un camp de tipus checkbox al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixCheckBox(string $camp, string $titol, array $off = []) {
		$this->Afegeix(self::tcCHECKBOX, $camp, $titol, 0, $off);
	}

	/**
	 * Afegeix un camp de tipus data al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixData(string $camp, string $titol, array $off = []) {
		$this->Afegeix(self::tcDATA, $camp, $titol, 0, $off);
	}
	
	/**
	 * Afegeix un ComboBox (desplegable) per triar un valor d'una llista.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $aCodis Codis de la llista. Per exemple: array(1, 2, 3, 4)
	 * @param array $aValors Valors de la llista. Per exemple: array("foo", "bar", "hello", "world")
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixLlista(string $camp, string $titol, int $longitud, array $aCodis, array $aValors, array $off = []) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcSELECCIO;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Longitud = 5*$longitud;
		$this->Camps[$i]->Opcions = $off;
		$this->Camps[$i]->Llista = new stdClass();
		$this->Camps[$i]->Llista->Codis = $aCodis;
		$this->Camps[$i]->Llista->Valors = $aValors;
	}
	
	/**
	 * Afegeix un camp de tipus lookup al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param string $URL Pàgina web de recerca.
	 * @param string $Taula Taula associada.
	 * @param string $Id Identificador del registre que es mostra.
	 * @param string $Camps Camps a mostrar al lookup separats per comes.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixLookup(string $camp, string $titol, int $longitud, string $URL, string $Taula, string $Id, string $Camps, array $off = []) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcLOOKUP;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Longitud = 5*$longitud;
		$this->Camps[$i]->Opcions = $off;
		$this->Camps[$i]->Lookup = new stdClass();
		$this->Camps[$i]->Lookup->URL = $URL;
		$this->Camps[$i]->Lookup->Taula = $Taula;
		$this->Camps[$i]->Lookup->Id = $Id;
		$this->Camps[$i]->Lookup->Camps = $Camps;
	}

	/**
	 * Marca l'inici d'una pestanya.
	 * @param string $titol Títol de la pestanya.
	 */
	public function Pestanya(string $titol) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcPESTANYA;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Opcions = [];
	}

	/**
	 * Marca l'inici de l'encolumnat.
	 */
	public function IniciaColumnes() {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcCOLUMNA_INICI;
		$this->Camps[$i]->Opcions = [];
	}

	/**
	 * Passa a la següent columna.
	 */
	public function SaltaColumna() {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcCOLUMNA_SALT;
		$this->Camps[$i]->Opcions = [];
	}

	/**
	 * Marca el final de l'encolumnat.
	 */
	public function FinalitzaColumnes() {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcCOLUMNA_FINAL;
		$this->Camps[$i]->Opcions = [];
	}

	/**
	 * Retorna el valor d'un camp de tipus text que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampText(string $camp) {
		return ' value="'.utf8_encode($this->Registre[$camp]).'" ';
	}

	/**
	 * Retorna el valor d'un camp de tipus data que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampData(string $camp) {
		return ' value="'.MySQLAData($this->Registre[$camp]).'" ';
	}
	
	/**
	 * Retorna el valor d'un camp de tipus password que prèviament ha estat carregat de la base de dades.
	 *
	 * @param string $camp Camp de la taula.
	 * @return string Valor que conté.
	 */
	private function ValorCampPassword(string $camp) {
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
	private function ValorCampCheckBox(string $camp) {
		return ($this->Registre[$camp]) ? ' value=1 checked ' : ' value=0 ';
	}

	/**
	 * Genera la capçalera (navegador) de les pestanyes (si n'hi ha).
	 * @return string Codi HTML per generar el navegador de les pestanyes.
	 */
	private function GeneraNavegadorPestanya() {
		$sRetorn = '';
		$Active = 'active';
		foreach($this->Camps as $Valor) {
			switch ($Valor->Tipus) {
				case self::tcPESTANYA:
					$this->HiHaPestanyes = True;
					$Titol = $Valor->Titol;
					$sRetorn .= '<a class="nav-item nav-link '.$Active.'" id="nav-'.$Titol.'-tab" data-toggle="tab" href="#nav-'.$Titol.'" role="tab" aria-controls="nav-'.$Titol.'" aria-selected="true">'.$Titol.'</a>';
					$Active = '';
					break;
			}
		}
		if ($sRetorn != '')
			$sRetorn = '<nav style="padding-top:20px;padding-bottom:20px"><div class="nav nav-tabs" id="nav-tab" role="tablist">'.$sRetorn.'</div></nav>';
		return $sRetorn;
	}
	
	/**
	 * Genera la fitxa per l'edició.
	 */
	private function GeneraFitxa() {
		$sRetorn = '<DIV id=Fitxa>';
		$sRetorn .= '<FORM class="form-inline my-2 my-lg-0" id="frmFitxa" method="post" action="LibForms.ajax.php">';
//		$sRetorn .= '<FORM class="form-horizontal" id="frmFitxa" method="post" action="LibForms.ajax.php">';
		$sRetorn .= "<input type=hidden name=hid_Taula value='".$this->Taula."'>";
		$sRetorn .= "<input type=hidden name=hid_ClauPrimaria value='".$this->ClauPrimaria."'>";
		$sRetorn .= "<input type=hidden name=hid_AutoIncrement value='".$this->AutoIncrement."'>";
		$sRetorn .= "<input type=hidden name=hid_Id value='".$this->Id."'>";
		$bAlCostat = False;
		$bPrimeraPestanya = True;
		$sRetorn .= '<TABLE>';
		$sRetorn .= '<TR>';
		foreach($this->Camps as $Valor) {
			$Requerit = (in_array(self::offREQUERIT, $Valor->Opcions) ? ' required' : '');
			$NomesLectura = (in_array(self::offNOMES_LECTURA, $Valor->Opcions) ? ' readonly' : '');
			$bAlCostat = in_array(self::offAL_COSTAT, $Valor->Opcions);
			switch ($Valor->Tipus) {
				case self::tcTEXT:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Valor->Longitud.'px" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.$NomesLectura.'></TD>';
					break;
				case self::tcENTER:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="ede_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Valor->Longitud.'px" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.$NomesLectura.' onkeydown="FormFitxaKeyDown(this, event, 0);"></TD>';
					break;
				case self::tcREAL:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="edr_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Valor->Longitud.'px" name="edt_'.$Valor->Camp.'" '.$this->ValorCampText($Valor->Camp).$Requerit.$NomesLectura.' onkeydown="FormFitxaKeyDown(this, event, 1);"></TD>';
					break;
				case self::tcPASSWORD:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="password" style="width:'.$Valor->Longitud.'px" name="pwd_'.$Valor->Camp.'" '.$this->ValorCampPassword($Valor->Camp).$Requerit.'></TD>';
					break;
				case self::tcCHECKBOX:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="edt_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="checkbox" name="chb_'.$Valor->Camp.'" '.$this->ValorCampCheckBox($Valor->Camp).$Requerit.'></TD>';
					break;
				case self::tcDATA:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sNom = 'edd_' . $Valor->Camp;
					$sRetorn .= '<TD><label for='.$sNom.'>'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD>';
					$sRetorn .= '<div id='.$sNom.' class="input-group date" style="width:150px">';
					$sRetorn .= '  <input type="text" class="form-control" name="'.$sNom.'" '.$this->ValorCampData($Valor->Camp).$Requerit.'>';
					$sRetorn .= '  <div class="input-group-append"><button class="btn btn-outline-secondary" type="button"><img src="img/calendar.svg"></button></div>';
					$sRetorn .= '</div>';
					$sRetorn .= '<script>$("#'.$sNom.'").datepicker({format: "dd/mm/yyyy", language: "ca"});</script>';
					$sRetorn .= '</TD>';
					break;
				case self::tcSELECCIO:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="cmb_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD>';
					$sRetorn .= '  <select class="custom-select" style="width:'.$Valor->Longitud.'px" name="cmb_'.$Valor->Camp.'">';
						$LongitudCodi = count($Valor->Llista->Codis); 
						for ($i = 0; $i < $LongitudCodi; $i++)
						{
							$Selected = ($Valor->Llista->Codis[$i] == $this->Registre[$Valor->Camp])? ' selected ' : '';
							$sRetorn .= '<option value="'.$Valor->Llista->Codis[$i].'"'.$Selected.'>'.$Valor->Llista->Valors[$i].'</option>';
						} 	
					$sRetorn .= '  </select>';
					$sRetorn .= '</TD>';
					break;
				case self::tcLOOKUP:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= '<TD><label for="lkp_'.$Valor->Camp.'">'.$Valor->Titol.'</label></TD>';
					$sRetorn .= '<TD>';
					$sRetorn .= '<div class="input-group mb-3">';
					$sRetorn .= "  <input type=hidden name=lkh_".$Valor->Camp." value=".$this->Registre[$Valor->Camp].">";
					$sRetorn .= "  <input type=hidden name=lkh_".$Valor->Camp."_camps value='".$Valor->Lookup->Camps."'>";
					$Text = $this->ObteCampsTaula($Valor->Lookup->Taula, $Valor->Lookup->Id, $this->Registre[$Valor->Camp], $Valor->Lookup->Camps);
					$sRetorn .= '  <input type="text" class="form-control" style="width:'.$Valor->Longitud.'px" name="lkp_'.$Valor->Camp.'" value="'.$Text.'"'.$NomesLectura.'>';
					$sRetorn .= '  <div class="input-group-append">';
					$onClick = "CercaLookup('lkh_".$Valor->Camp."', 'lkp_".$Valor->Camp."', '".$Valor->Lookup->URL."', '".$Valor->Lookup->Camps."');";
					$onClick = ($NomesLectura) ? '': $onClick;
					$sRetorn .= '    <button class="btn btn-outline-secondary" type="button" onclick="'.$onClick.'">Cerca</button>';
					$sRetorn .= '  </div>';
					$sRetorn .= '</div>';
					$sRetorn .= '</TD>';
					break;
				case self::tcPESTANYA:
					$Titol = $Valor->Titol;
					if ($bPrimeraPestanya) {
						$sRetorn .= '</TR><TR>';
						$sRetorn .= '<TD colspan=10>';
						$sRetorn .= '<DIV>';
						$sRetorn .= $this->GeneraNavegadorPestanya();
						$sRetorn .= '<div class="tab-content" id="nav-tabContent">';
						$sRetorn .= '<div class="tab-pane fade show active" id="nav-'.$Titol.'" role="tabpanel" aria-labelledby="nav-'.$Titol.'-tab">';
						$bPrimeraPestanya = False;
						$sRetorn .= '<TABLE>';
						$sRetorn .= '<TR>';
					}
					else {
						$sRetorn .= '</TR></TABLE>';
						$sRetorn .= '</div>';
						$sRetorn .= '<div class="tab-pane fade" id="nav-'.$Titol.'" role="tabpanel" aria-labelledby="nav-'.$Titol.'-tab">';
						$sRetorn .= '<TABLE>';
						$sRetorn .= '<TR>';
					}
					break;
				case self::tcCOLUMNA_INICI:
					$sRetorn .= '<TR><TD>';
					$sRetorn .= '</TD><TD>';
					$sRetorn .= '<TABLE>';
					$sRetorn .= '<TR><TD>';
					$sRetorn .= '<TABLE>';
					break;
				case self::tcCOLUMNA_SALT:
					$sRetorn .= '</TABLE>';
					$sRetorn .= '</TD>';
					$sRetorn .= '<TD>';
					$sRetorn .= '<TABLE>';
					break;
				case self::tcCOLUMNA_FINAL:
					$sRetorn .= '</TABLE>';
					$sRetorn .= '</TD></TR>';
					$sRetorn .= '</TABLE>';
					$sRetorn .= '</TD></TR>';
					break;
			}
		}
		if ($this->HiHaPestanyes)
			$sRetorn .= '</TD></TR></TABLE></DIV></DIV></DIV></DIV>';
		$sRetorn .= '</TR>';
		$sRetorn .= '<TR><TD><a class="btn btn-primary active" role="button" aria-pressed="true" id="btnDesa" name="btnDesa" onclick="DesaFitxa(this.form);">Desa</a></TD></TR>';
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
		$sRetorn .= "Les dades s'han desat correctament.";
		$sRetorn .= '</div>';
		$sRetorn .= '<div class="alert alert-danger collapse" id="MissatgeError" role="alert">';
		$sRetorn .= "Hi ha hagut un error en desar les dades.";
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
		echo $this->GeneraMissatges();
		echo $this->GeneraFitxa();
		CreaFinalHTML();
	}
} 

?>