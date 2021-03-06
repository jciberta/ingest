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
 */

require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibSQL.php');
require_once(ROOT.'/lib/LibHTML.php');

/**
 * Classe Form.
 *
 * Classe base de la quals descendeixen els formularis.
 * Conté els mètodes per crear els components bàsics.
 */
class Form {
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
	const tcCALCULAT = 10;
	const tcFOTOGRAFIA = 11;
	const tcTEXT_RIC = 12;
	const tcHTML = 13;
	const tcPESTANYA = 20;
	const tcCOLUMNA_INICI = 21;
	const tcCOLUMNA_SALT = 22;
	const tcCOLUMNA_FINAL = 23;
	const tcESPAI = 24;

	// Tipus de camps calculat.
	const tccEDAT = 1;
	
	// Opcions del FormFitxa.
	const offNOMES_LECTURA = 1; // Indica si el camp és pot escriure o no.
	const offREQUERIT = 2; 		// Indica si el camp és obligatori.
	const offAL_COSTAT = 3;     // Indica si el camp es posiciona al costat de l'anterior (per defecte a sota).

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
	* Títol del formulari.
	* @var string
	*/    
    public $Titol = '';

	/**
	* Taula principal.
	* @var string
	*/    
    public $Taula = '';	

	/**
	* Clau primària de la taula. Es permet que sigui múltiple.
	* @var string
	*/    
    public $ClauPrimaria = '';	

	/**
	* Objecte que emmagatzema el contingut d'un ResultSet carregat de la base de dades.
	* @var object
	*/    
//    public $Registre = NULL;

	/**
	* Fitxers JavaScript.
	* @var array
	*/    
    protected $FitxerJS = [];	

	/**
	* Indica si un formulari s'hi permet realitzar canvis o no.
	* @var boolean
	*/    
    public $NomesLectura = False; 

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 */
	function __construct($con = NULL, $user = NULL) {
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
	 * Crea un clicable per a l'ajuda.
	 * @param string $Titol Títol de l'ajuda.
	 * @param string $Contingut Contingut de l'ajuda.
	 * @return string Codi HTML de l'ajuda.
	 */
	public function CreaAjuda(string $Titol, string $Contingut): string {
		$sRetorn = '<span class="text-nowrap">';
		$sRetorn .= '<a class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" '.
			"title='$Titol'";
		$sRetorn .= 'data-placement="right" data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;'.
			"$Contingut".
			'&lt;/p&gt;&lt;/div&gt; "';
		$sRetorn .= 'data-html="true" tabindex="0" data-trigger="focus">';
		$sRetorn .= '<img src="img/help.svg">';
		$sRetorn .= '</a>';
		$sRetorn .= '</span>&nbsp;&nbsp;';
		return $sRetorn;
	}	

	/**
	 * Crea un botó.
	 * @param string $Nom Nom del botó.
	 * @param string $Titol Títol de l'ajuda.
	 * @param string $URL URL de l'opció. Se li afegirà l'identificador del registre.
	 * @return string Codi HTML del botó.
	 */
	public function CreaBoto(string $Nom, string $Titol, string $URL): string {
		$sRetorn = "<a href='$URL' class='btn btn-primary active' role='button' aria-pressed='true' ".
			" name='$Nom'>$Titol</a>&nbsp;";
		return $sRetorn;
	}	
	
	/**
	 * Crea un botó que executa una funció JavaScript.
	 * @param string $Nom Nom del botó.
	 * @param string $Titol Títol de l'ajuda.
	 * @param string $Funcio Funció JavaScript.
	 * @param boolean $Deshabilitat Botó deshabilitat.
	 * @return string Codi HTML del botó.
	 */
	public function CreaBotoJS(string $Nom, string $Titol, string $Funcio, $Deshabilitat = False): string {
		$Deshabilitat = $Deshabilitat ? ' disabled ' : '';
		$sRetorn = "<a href=# class='btn btn-primary active $Deshabilitat' role='button' aria-pressed='true' ".
			" name='$Nom'".
			" onClick='$Funcio'>$Titol</a>&nbsp;";
		return $sRetorn;
	}	
	
	/**
	 * Crea un camp de tipus checkbox.
	 *
	 * @param string $Nom Nom del element.
	 * @param string $Titol Títol del camp.
	 * @param boolean $Valor Valor per defecte de l'element.
	 * @param array $off Opcions del formulari.
	 * @param string $onChange Funció que crida l'event onChange (opcional).
	 * @return string Codi HTML del checkbox.
	 */
	public function CreaCheckBox(string $Nom, string $Titol, bool $Valor, array $off = [], $onChange = '') {
		$Requerit = (in_array(self::offREQUERIT, $off) ? ' required' : '');
		$NomesLectura = (in_array(self::offNOMES_LECTURA, $off) || $this->NomesLectura) ? ' disabled' : '';
		$TextValor = $Valor ? ' value=1 checked ' : ' value=0 ';
		if (get_class($this) == 'FormRecerca')
			$onChange = ($onChange = '') ? '' : 'onchange="ActualitzaTaula(this);"';

		$sNom = 'chb_' . $Nom;
		$sRetorn = '<TD><label for='.$sNom.'>'.$Titol.'</label></TD>';
//		$sRetorn .= '<TD><input class="form-control mr-sm-2" type="checkbox" name="chb_'.$sNom.'" '.$TextValor.$Requerit.'></TD>';
		$sRetorn .= '<TD><input type="checkbox" name="'.$sNom.'" '.$TextValor.$Requerit.$NomesLectura.$onChange.'></TD>';
		return $sRetorn;
	}	

	/**
	 * Crea un element "data" (element INPUT + BUTTON per cercar les dates).
	 *
	 * @param string $Nom Nom del element.
	 * @param string $Titol Títol del camp.
	 * @param array $off Opcions del formulari.
	 * @param mixed $DataSeleccionada Valor de la data per defecte de l'element.
	 * @return string Codi HTML del lookup.
	 */
	public function CreaData(string $Nom, string $Titol, array $off = [], $DataSeleccionada = NULL) {
		$Requerit = (in_array(self::offREQUERIT, $off) ? ' required' : '');
		$NomesLectura = (in_array(self::offNOMES_LECTURA, $off) || $this->NomesLectura) ? ' readonly' : '';

		$sNom = 'edd_' . $Nom;
		$sRetorn = '<TD><label for='.$sNom.'>'.$Titol.'</label></TD>';
		$sRetorn .= '<TD>';
		$sRetorn .= '<div id='.$sNom.' class="input-group date" style="width:150px">';
		$sRetorn .= '  <input type="text" class="form-control" name="'.$sNom.'" '.$DataSeleccionada.$Requerit.$NomesLectura.'>';
		if (!$NomesLectura)
			$sRetorn .= '  <div class="input-group-append"><button class="btn btn-outline-secondary" type="button"><img src="img/calendar.svg"></button></div>';
		$sRetorn .= '</div>';
		if (!$NomesLectura)
			$sRetorn .= '<script>$("#'.$sNom.'").datepicker({format: "dd/mm/yyyy", language: "ca"});</script>';
		$sRetorn .= '</TD>';
		return $sRetorn;
	}
	
	/**
	 * CreaLlista
	 *
	 * Crea una llista desplegable (combobox) HTML com a 2 cel·les d'una taula.
	 * Ús: CreaLlista(array(1, 2, 3, 4), array("foo", "bar", "hello", "world"));
	 *
	 * @param string $Nom Nom del desplegable.
	 * @param string $Titol Títol del desplegable.
	 * @param integer $Longitud Longitud del desplegable.
	 * @param array $Codi Codis de la llista.
	 * @param array $Valor Valors de la llista.
	 * @param mixed $CodiSeleccionat Codi de la llista seleccionat per defecte.
	 * @param string $onChange Funció que crida l'event onChange (opcional).
	 * @return void
	 */
	public function CreaLlista(string $Nom, string $Titol, int $Longitud, array $Codi, array $Valor, $CodiSeleccionat = NULL, $onChange = ''): string {
		$NomesLectura = ($this->NomesLectura) ? ' disabled' : '';
		$sRetorn = '<TD><label for="cmb_'.$Nom.'">'.$Titol.'</label></TD>';
		$sRetorn .= '<TD>';
		if (get_class($this) == 'FormRecerca')
			$onChange = ($onChange = '') ? '' : 'onchange="ActualitzaTaula(this);"';
		$sRetorn .= "  <select class='custom-select' $NomesLectura style='width:".$Longitud."px' id='cmb_$Nom' name='cmb_$Nom' $onChange>";
		$LongitudCodi = count($Codi); 
		for ($i = 0; $i < $LongitudCodi; $i++) {
			$Selected = (($CodiSeleccionat != '') && ($Codi[$i] == $CodiSeleccionat)) ? ' selected ': '';
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
	 * @param string $CodiSeleccionat Valor del codi per defecte del lookup.
	 * @param string $onChange Funció que crida l'event onChange (opcional).
	 * @return string Codi HTML del lookup.
	 */
	public function CreaLookup(string $Nom, string $Titol, int $Longitud, string $URL, string $Taula, string $Id, string $Camps, array $off = [], $CodiSeleccionat = '', $onChange = '') {
		
		$Connector = (strpos($URL, '?') === False) ? '?' : '&';
		$URL .= $Connector . 'Modalitat=mfBusca';

		// Només en els formularis de recerca
//echo '<hr>'.get_class($this);		
//echo '<hr>'.get_class();		
//echo '<hr>'.get_called_class();
		if (get_class($this) == 'FormRecerca')
			$onChange = ($onChange = '') ? '' : 'onchange="ActualitzaTaula(this);"';
		
		if (Config::EncriptaURL)
			$URL = GeneraURL($URL);
		
		// $NomesLectura = (in_array(self::offNOMES_LECTURA, $off) || $this->NomesLectura) ? ' readonly' : '';
		$NomesLectura = (in_array(self::offNOMES_LECTURA, $off)) ? ' readonly' : '';
		$sRetorn = '<TD><label for="lkp_'.$Nom.'">'.$Titol.'</label></TD>';
		$sRetorn .= '<TD>';
		$sRetorn .= '<div class="input-group mb-3">';
		$sRetorn .= "  <input type=hidden name=lkh_".$Nom." value='".$CodiSeleccionat."' $onChange $NomesLectura>";
		$sRetorn .= "  <input type=hidden name=lkh_".$Nom."_camps value='".$Camps."' $NomesLectura>";
		if ($CodiSeleccionat == '')
			$Text = '';
		else
			$Text = $this->ObteCampsTaula($Taula, $Id, $CodiSeleccionat, $Camps);
		$onkeydown = ($NomesLectura) ? '':' onkeydown="FormFitxaKeyDown(this, event, 2);" ';
		$sRetorn .= '  <input type="text" class="form-control" style="width:'.$Longitud.'px" name="lkp_'.$Nom.'" value="'.$Text.'"'.$NomesLectura.$onkeydown.'>';
		$sRetorn .= '  <div class="input-group-append">';
		$onClick = " onclick=".'"'."CercaLookup('lkh_".$Nom."', 'lkp_".$Nom."', '".$URL."', '".$Camps."');".'"';
		$onClick = ($NomesLectura) ? '': $onClick;
		$sRetorn .= '    <button class="btn btn-outline-secondary" type="button" '.$onClick.'>Cerca</button>';
		$sRetorn .= '  </div>';
		$sRetorn .= '</div>';
		$sRetorn .= '</TD>';
		return $sRetorn;
	}
	
	/**
	 * Crea un camp de tipus camp calculat.
	 *
	 * @param string $Calcul Tipus de camp calculat.
	 * @param string $Nom Nom del element.
	 * @param string $Titol Títol del camp.
	 * @param integer $Longitud Longitud del desplegable.
	 * @param boolean $Valor Valor per defecte de l'element.
	 * @param array $off Opcions del formulari.
	 * @return string Codi HTML del checkbox.
	 */
	public function CreaCalculat(int $Calcul, string $Nom, string $Titol, int $Longitud, $Valor, array $off = []) {
		$bAlCostat = in_array(self::offAL_COSTAT, $off);
		$TextValor = '';
		switch ($Calcul) {
			case Form::tccEDAT:
				$diff = date_diff(date_create("now"), date_create($Valor));
				$TextValor = $diff->format("%y");
//print("Edat: $TextValor<hr>");
				break;
		}

		$sNom = 'cfd_' . $Nom;
		$sRetorn = (!$bAlCostat) ? '</TR><TR>' : '';
		$sRetorn .= '<TD><label for='.$sNom.'>'.$Titol.'</label></TD>';
		$sRetorn .= '<TD><input class="form-control mr-sm-2" type="text" style="width:'.$Longitud.'px" name="'.$sNom.'" value="'.$TextValor.'" disabled></TD>';
		return $sRetorn;
	}	

	/**
	 * Crea un camp de tipus fotografia.
	 * @param string $Valor Valor que identifica la fotografia.
	 * @param string $Sufix Sufix que s'afegeix al valor per completar el fitxer de la fotografia.
	 * @return string Codi HTML del checkbox.
	 */
	public function CreaFotografia(string $Valor, string $Sufix): string {
		//$bAlCostat = in_array(self::offAL_COSTAT, $off);
		$Fitxer = 'img/pix/'.$Valor.$Sufix;
		if (!file_exists($Fitxer))
			$Fitxer = 'img/nobody.png';
		//$sRetorn = (!$bAlCostat) ? '</TR><TR>' : '';
		$sRetorn = '<TD><IMG SRC="'.$Fitxer.'"></TD>';
		return $sRetorn;
	}	

	/**
	 * Crea un text amb format (RichEdit o RichMemo).
	 * @param string $Nom Nom del element.
	 * @param string $Titol Títol del control.
	 * @param integer $Longitud Longitud del text.
	 * @param integer $Altura Altura del text.
	 * @param array $off Opcions del formulari.
	 * @param mixed $Contingut Valor del text per defecte.
	 * @return string Codi HTML del text enriquit.
	 */
	public function CreaTextRic(string $Nom, string $Titol, int $Longitud, int $Altura, string $Contingut = '', array $off = []) {
		$Requerit = (in_array(self::offREQUERIT, $off) ? ' required' : '');
		$NomesLectura = (in_array(self::offNOMES_LECTURA, $off) || $this->NomesLectura) ? ' readonly' : '';
		$sNom = 'red__' . $Nom;
		$sRetorn = '<TD valign=top><label for='.$sNom.'>'.$Titol.'</label></TD>';
		$sRetorn .= '<TD>';
		$sRetorn .= "<span class='summernote'>$Contingut</span>";
		$sRetorn .= '</TD>';
		return $sRetorn;
	}

	/**
	 * Crea un text amb contingut HTML.
	 * @param string $Text Camp de la taula.
	 * @param string $Titol Títol del camp.
	 * @return string Codi HTML del checkbox.
	 */
	public function CreaHTML(string $Text, string $Titol): string {
		//$bAlCostat = in_array(self::offAL_COSTAT, $off);
		//$sRetorn = (!$bAlCostat) ? '</TR><TR>' : '';
		$sRetorn = '<TD valign=top><label>'.$Titol.'&nbsp</label></TD>';
		$sRetorn .= "<TD>$Text</TD>";
		return $sRetorn;
	}	
	
	/**
	 * Genera els missatges de succés i error per quan es desen les dades.
	 */
	protected function GeneraMissatges() {
		$sRetorn = '<div class="alert alert-success collapse" id="MissatgeCorrecte" role="alert">';
		$sRetorn .= "L'acció s'ha realitzat correctament.";
		$sRetorn .= '</div>';
		$sRetorn .= '<div class="alert alert-danger collapse" id="MissatgeError" role="alert">';
		$sRetorn .= "Hi ha hagut un error en realitzar l'acció.";
		$sRetorn .= '</div>';
		return $sRetorn;
	}
} 

/**
 * Classe FormRecerca.
 *
 * Classe per als formularis de recerca.
 */
class Filtre {
	/**
	* Camps del filtre amb les seves característiques. S'usa per generar els components visuals.
	* @access private
	* @var array
	*/    
    private $Camps = [];	

	/**
	* Objecte formulari per tal d'usar els mètodes de generar els components visuals.
	* @access private
	* @var object
	*/    
    private $Form;	

	/**
	* Cadena JSON amb la llista dels elements a filtrar.
	* @access public
	* @var string
	*/    
	public $JSON = '';

	/**
	 * Constructor de l'objecte.
	 * @param object $frm Formulari que crea el filtre.
	 */
	function __construct($frm) {
		$this->Form = $frm;
	}	
/*	function __construct() {
		$this->Form = new Form();
	}	*/

	/**
	 * Afegeix un camp del tipus especificat al filtre.
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
	 * Afegeix un camp de tipus checkbox al formulari.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param boolean $Valor Valor per defecte de l'element.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixCheckBox(string $camp, string $titol, bool $Valor, array $off = []) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = Form::tcCHECKBOX;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Valor = $Valor; // Passa a ser enter (0, 1)!
		$this->Camps[$i]->Opcions = $off;
	}
	
	/**
	 * Afegeix un camp de tipus data al filtre.
	 *
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixData(string $camp, string $titol, array $off = []) {
		$this->Afegeix(Form::tcDATA, $camp, $titol, 0, $off);
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
		$this->Camps[$i]->Tipus = Form::tcSELECCIO;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Longitud = 5*$longitud;
		$this->Camps[$i]->Opcions = $off;
		$this->Camps[$i]->Llista = new stdClass();
		$this->Camps[$i]->Llista->Codis = $aCodis;
		$this->Camps[$i]->Llista->Valors = $aValors;
	}
	
	/**
	 * Afegeix un "lookup" (element INPUT + BUTTON per cercar les dades en una altra finestra).
	 *
	 * @param string $Nom Nom del lookup.
	 * @param string $Titol Títol del camp.
	 * @param integer $Longitud Longitud màxima.
	 * @param string $URL Pàgina web de recerca.
	 * @param string $Taula Taula associada.
	 * @param string $Id Identificador del registre que es mostra.
	 * @param string $Camps Camps a mostrar al lookup separats per comes.
	 * @param array $off Opcions del formulari.
	 * @param string $CodiSeleccionat Valor del codi per defecte del lookup.
	 * @return void
	 */
	public function AfegeixLookup(string $Nom, string $Titol, int $Longitud, string $URL, string $Taula, string $Id, string $Camps, array $off = [], $CodiSeleccionat = '') {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = Form::tcLOOKUP;
		$this->Camps[$i]->Camp = $Nom;
		$this->Camps[$i]->Titol = $Titol;
		$this->Camps[$i]->Longitud = 5*$Longitud;
		$this->Camps[$i]->Opcions = $off;
		$this->Camps[$i]->Lookup = new stdClass();
		$this->Camps[$i]->Lookup->URL = $URL;
		$this->Camps[$i]->Lookup->Taula = $Taula;
		$this->Camps[$i]->Lookup->Id = $Id;
		$this->Camps[$i]->Lookup->Camps = $Camps;		
	}
	
	
//echo $frmMatricula->CreaLookUp('alumne', 'Alumne', 100, 'UsuariRecerca.php?accio=Alumnes', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
	
	
	
	/**
	 * Crea el filtre del formulari.
	 * @return string HTML del filtre.
	 */
	public function CreaFiltre(): string {
		$Retorn = '<DIV id=filtre>';
		foreach($this->Camps as $Valor) {
			switch ($Valor->Tipus) {
				case Form::tcESPAI:
					break;
				case Form::tcTEXT:
					break;
				case Form::tcENTER:
					break;
				case Form::tcREAL:
					break;
				case Form::tcPASSWORD:
					break;
				case Form::tcCHECKBOX:
//					$ValorDefecte = ($Valor->Valor == 1);
/*print_r($Valor);	
echo "<p>".$Valor->Valor."<p>";
echo "<p>".(bool)$ValorDefecte."<p>";

exit;*/
					$Retorn .= $this->Form->CreaCheckBox($Valor->Camp, $Valor->Titol, $Valor->Valor);
					break;
				case Form::tcDATA:
//					$sRetorn .= $this->CreaData($Valor->Camp, $Valor->Titol, $Valor->Opcions, $this->ValorCampData($Valor->Camp));
					break;
				case Form::tcSELECCIO:
//					$Retorn .= '<BR>EI<BR>';
//					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
//					$CodiSeleccionat = $this->Registre[$Valor->Camp];
//					$Retorn .= $this->Form->CreaLlista($Valor->Camp, $Valor->Titol, $Valor->Longitud, $Valor->Llista->Codis, $Valor->Llista->Valors, $this->Registre[$Valor->Camp]);
					$Retorn .= $this->Form->CreaLlista($Valor->Camp, $Valor->Titol, $Valor->Longitud, $Valor->Llista->Codis, $Valor->Llista->Valors);
					break;
				case Form::tcLOOKUP:
					//$CodiSeleccionat = ($this->Registre == NULL) ? '' : $this->Registre[$Valor->Camp];
					$CodiSeleccionat = '';
//print_r($this->Registre);	
//exit;			
					//$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$Retorn .= $this->Form->CreaLookup(
						$Valor->Camp, 
						$Valor->Titol, 
						$Valor->Longitud, 
						$Valor->Lookup->URL, 
						$Valor->Lookup->Taula, 
						$Valor->Lookup->Id, 
						$Valor->Lookup->Camps, 
						$Valor->Opcions, 
						$CodiSeleccionat);
					break;
			}			
		}
		$Retorn .= '</DIV><P/>';
		$this->CreaFiltreJSON();
		return $Retorn;
	}
	
	/**
	 * Crea el filtre JSON per a la primera vegada que s'executa el formulari de recerca.
	 * @return void.
	 */
	private function CreaFiltreJSON() {
		$sFiltre = '{';
		foreach($this->Camps as $Valor) {
			switch ($Valor->Tipus) {
				case Form::tcESPAI:
					break;
				case Form::tcTEXT:
					break;
				case Form::tcENTER:
					break;
				case Form::tcREAL:
					break;
				case Form::tcPASSWORD:
					break;
				case Form::tcCHECKBOX:
					break;
				case Form::tcDATA:
					break;
				case Form::tcSELECCIO:
//					$Retorn .= '<BR>EI<BR>';
//					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
//					$CodiSeleccionat = $this->Registre[$Valor->Camp];
//					$Retorn .= $this->Form->CreaLlista($Valor->Camp, $Valor->Titol, $Valor->Longitud, $Valor->Llista->Codis, $Valor->Llista->Valors, $this->Registre[$Valor->Camp]);
					$sFiltre .= '"'.$Valor->Camp.'": "'.$Valor->Llista->Codis[0].'", ';
					break;
				case Form::tcLOOKUP:
//print_r($Valor);
					$sFiltre .= '"'.$Valor->Camp.'": "", ';
					break;
			}					
		}
		$sFiltre = substr($sFiltre, 0, -2); // Treiem la darrera coma
		$sFiltre = trim($sFiltre);
		if (strlen($sFiltre)>0)
			$sFiltre .= '}';
//echo '<p>'.$sFiltre.'<p>';		
		$this->JSON = $sFiltre;		
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
	const toImatge = 3;
	
	// Opcions del FormRecerca.
	const ofrCHECK = 1; 		// Indica si l'opció és booleana i es farà amb un checkbox.
	const ofrNOMES_CHECK = 2; 	// Indica que només es podrà seleccionar el checkbox (i no desseleccionar). 
	const ofrNOMES_LECTURA = 3; // Indica que l'opció és de només lectura.

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
    public $FiltreText = ''; 

	/**
	* Llista de components (dates, combos) que permeten filtrar de forma específica.
	* @access public
	* @var array
	*/    
    public $Filtre = []; 
	
	/**
	* Camp per realitzar l'ordenació.
	* @access public
	* @var string
	*/    
    public $Ordre = ''; 
	
	/**
	* Permet ordenar la recerca.
	* @access public
	* @var boolean
	*/    
    public $PermetOrdenar = True; 
	
	/**
	* Permet editar un registre.
	* @access public
	* @var boolean
	*/    
    public $PermetEditar = False; 
	
	/**
	* URL per a l'edició d'un registre.
	* @access public
	* @var string
	*/    
    public $URLEdicio = ''; 
	
	/**
	* Permet afegir un registre. Usa la URLEdicio per indicar la fitxa.
	* @access public
	* @var boolean
	*/    
    public $PermetAfegir = False; 
	
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
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		parent::__construct($con, $user);
		$this->Filtre = new Filtre($this);
	}	

	/**
	 * Crea la nova SQL a partir de les propietats SQL i FiltreText.
     * @return string Sentència SQL.
	 */
	public function CreaSQL() {
		$sRetorn = $this->SQL;
		
		// Filtre de components visuals
		if ($this->Filtre->JSON != '') {
//print 'Filtre: '.$this->Filtre->JSON;
			$Filtre = $this->CreaSQLFiltre();
			if ($Filtre != '') {
				$obj = new SQL($this->SQL);
				if (strlen($obj->Where) > 0)
					$obj->Where .= ' AND '.$Filtre;
				else
					$obj->Where = $Filtre;
				$sRetorn = $obj->GeneraSQL();
			}
		}
		
		// Filtre de paraules clau
		if ($this->FiltreText != '') {
			$obj = new SQL($sRetorn);
//print_r('CampAlies: ');
//print_r($obj->CampAlies);
//print_r('<hr>');
			$sWhere = '';
			$aFiltreText = explode(" ", TrimX($this->FiltreText));
			$aCamps = explode(",", TrimXX($this->Camps));
			foreach ($aFiltreText as $sValor) {
				$sWhere .= '(';
				foreach ($aCamps as $sCamp) {
					$sCamp = $this->EliminaTipusPredefinit($sCamp);
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
		
		// Ordenació de les columnes
		if ($this->Ordre != '') {
			$iOrder = strrpos($sRetorn, ' ORDER ');
			if ($iOrder != 0) {
				$sRetorn = trim(substr($sRetorn, 0, $iOrder));
			}
			$sRetorn .= ' ORDER BY '.$this->EliminaTipusPredefinit($this->Ordre);
		}
//print $sRetorn;		
		return $sRetorn;
	}

	/**
	 * Crea la part SQL del filtre del formulari.
	 * @return string SQL del filtre.
	 */
	private function CreaSQLFiltre(): string {
		$Retorn = '';
		if ($this->Filtre->JSON != '') {
			// Convertim el JSON en un array associatiu
			// https://www.php.net/manual/en/function.json-decode.php
//print_r("this->Filtre->JSON:<BR>");
//print_r($this->Filtre->JSON);
			$aFiltre = json_decode($this->Filtre->JSON, true);
			$aRetorn = [];
			$obj = new SQL($this->SQL);
//print_r('aFiltre: '.$aFiltre);
//var_dump($aFiltre);
			foreach ($aFiltre as $key => $value) {
				if ($value != '')
					array_push($aRetorn, $obj->ObteCampDesDeAlies($key)."='".$value."'");
			}
			if (count($aRetorn)>0)
				$Retorn = implode(' AND ', $aRetorn); 
		}
//print_r($Retorn);
		return trim($Retorn);
	}
	
	/**
	 * Crea les fletxes per a l'ordenació dels diferents camps de la recerca.
	 * @param string $camp Camp per a l'ordenació.
     * @return string HTML amb les imatges de les fletxes.
	 */
	private function CreaFletxaOrdenacio(string $camp): string {
		$Retorn = '';
		if ($this->PermetOrdenar) {
			$camp = $this->EliminaTipusPredefinit($camp);
			$FuncioAvall = 'OrdenaColumna("'.$camp.'", "")';
			$FuncioAmunt = 'OrdenaColumna("'.$camp.'", "DESC")';
			$Retorn .= "<span id='FletxaAvall_".$camp."'><img src=img/down.svg style='cursor:pointer' onclick='$FuncioAvall'></span>";
			$Retorn .= "<span id='FletxaAmunt_".$camp."' style='display:none'><img src=img/up.svg style='cursor:pointer' onclick='$FuncioAmunt'></span>";
		}
		return $Retorn;
	}
	
	/**
	 * Crea el botó per a la descàrrega en CSV.
	 * @param string $URL URL que realitza l'acció de la descàrrega.
	 * @return string Codi HTML del botó.
	 */
	private function CreaBotoDescarrega(string $URL): string {
		$sRetorn = '<div class="btn-group" role="group">';
		$sRetorn .= '    <button id="btnGroupDrop1" type="button" class="btn btn-primary active dropdown-toggle" data-toggle="dropdown">';
		//$sRetorn .= '    <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
		$sRetorn .= '      Descarrega';
		$sRetorn .= '    </button>';
		$sRetorn .= '    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
		$sRetorn .= '      <a class="dropdown-item" href="'.$URL.'">CSV</a>';
		$sRetorn .= '    </div>';
		$sRetorn .= '  </div>';		
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
     * @return string Taula amb les dades.
	 */
	public function GeneraTaula() {
		$sRetorn = '<DIV id=taula>';
		$SQL = $this->CreaSQL();
//print $SQL;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$sRetorn .= '<TABLE class="table table-striped table-sm table-hover">';

			$aDescripcions = explode(",", TrimX($this->Descripcions));
			$aCamps = explode(",", TrimXX($this->Camps));

			// Capçalera
			$sRetorn .= '<THEAD class="thead-dark">';
			for ($i=0; $i<count($aDescripcions); $i++) {
				$sValor = $aDescripcions[$i];
				$Ordenacio = $this->CreaFletxaOrdenacio($aCamps[$i]);
				$sRetorn .= "<TH>".$sValor."&nbsp;".$Ordenacio."</TH>";
			}
			$sRetorn .= '<TH></TH>';
			if ($this->Modalitat == self::mfLLISTA) 
				foreach($this->Opcions as $obj) { 
					if (in_array(self::ofrCHECK, $obj->Opcions) || in_array(self::ofrNOMES_CHECK, $obj->Opcions) || ($obj->Tipus == self::toImatge)) {
						$sRetorn .= '<TH>'.$obj->Titol;
						if (isset($obj->Llegenda) && $obj->Llegenda != '')
							$sRetorn .= ' '.$this->CreaAjuda($obj->Titol, $obj->Llegenda);
						$sRetorn .= '</TH>';
					}
					else
						$sRetorn .= '<TH></TH>';
				}
			$sRetorn .= '</THEAD>';

			// Dades
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
							$sValor = ($row[$aData[1]]) ? '<input type="checkbox" checked disabled>' : '';
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
					$URL = $this->URLEdicio.$Concatena."Id=".$row[$this->ClauPrimaria];
					$sRetorn .= "<A href='".GeneraURL($URL)."'><IMG src=img/edit.svg></A>&nbsp&nbsp";
				}
				if ($this->Modalitat == self::mfLLISTA && $this->PermetSuprimir) {
					$Funcio = 'SuprimeixRegistre("'.$this->Taula.'", "'.$this->ClauPrimaria.'", '.$row[$this->ClauPrimaria].');';
					$sRetorn .= "<A href=# onClick='".$Funcio."' data-toggle='modal' data-target='#confirm-delete'><IMG src=img/delete.svg></A>&nbsp&nbsp";
//					$sRetorn .= "<IMG src=img/delete.svg>&nbsp&nbsp";
				}
				$sRetorn .= "</TD>";
				if ($this->Modalitat == self::mfLLISTA && $this->ClauPrimaria != '')
//print_h($row);					
					$sRetorn .= $this->GeneraOpcions($this->ValorClauPrimaria($row, $this->ClauPrimaria), $row);
//					$sRetorn .= $this->GeneraOpcions($row[$this->ClauPrimaria], $row);
				$sRetorn .= "</TR>";
			}
			$sRetorn .= "</TABLE>";
		}
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Obté el valor de la clau primària. Permet que la clau sigui múltiple.
     * @param mixed $row Registre.
     * @param string $cp Clau primària.
     * @return string Retorna el valor de la clau primària. Si és múltiple, retorna els valors separats per coma.
	 */
	private function ValorClauPrimaria($row, $cp) {
		$Retorn = NULL;
		if ((strpos($cp, ',') === False)) {
			$Retorn = $row[$this->ClauPrimaria];
		}
		else {
			// La clau és múltiple
			$acp = explode(',', TrimXX($cp));
			
			$Retorn = '';
			for($i=0; $i < count($acp); $i++) {
				$Retorn .= utf8_encode($row[$acp[$i]]).',';
			}
			$Retorn = substr($Retorn, 0, -1); // Treiem la darrera coma
			
		}
		return $Retorn;	
	}

	/**
	 * Genera el formulari per fer recerques.
     * @return string Codi HTML amb el formulari per fer recerques.
	 */
	private function GeneraCerca() {
		$sRetorn = '<DIV id=Recerca style="padding:10px">';
		$sRetorn .= '  <FORM class="form-inline my-2 my-lg-0" id=form method="post" action="">';
		$sRetorn .= '    <TABLE style="width:100%">';
		$sRetorn .= '    <TR>';
		$sRetorn .= '    <TD>';
		$sRetorn .= '    <input class="form-control mr-sm-2" type="text" style="width:500px" name="edtRecerca" placeholder="Text a cercar" aria-label="Search" autofocus onkeypress="RecercaKeyPress(event);">';

		// *** No pot ser un botó, ja que el submit del form fa recarregar la pàgina! (multitud d'hores perdudes!) ***
		//$sRetorn .= '    <button class="btn btn-outline-primary my-2 my-sm-0" name="btnRecerca" onclick="ActualitzaTaula(this);">Cerca</button>';
		$sRetorn .= '    <a class="btn btn-primary active" role="button" aria-pressed="true" id="btnRecerca" name="btnRecerca" onclick="ActualitzaTaula(this);">Cerca</a>';

		$sRetorn .= '    </TD>';
		
		$sRetorn .= '<TD style="align:right">';
		$sRetorn .= '<span style="float:right;">';
		// De moment només admin
		if ($this->Modalitat == self::mfLLISTA && $this->Usuari->es_admin) {
//			$sRetorn .= '<TD style="align:right">';
//			$sRetorn .= '<span style="float:right;">';

			$SQL = bin2hex(Encripta(TrimX($this->CreaSQL())));
//print('<B>SQL</B>: '.$SQL.'<BR>');
			$URL = GeneraURL("Descarrega.php?Accio=ExportaCSV&SQL=$SQL");
//print('<B>URL</B>: '.$URL.'<BR>');

			$sRetorn .= $this->CreaBotoDescarrega($URL).'&nbsp';
//			$sRetorn .= '</span>';
//			$sRetorn .= '</TD>';		
			
		}
		
		if ($this->Modalitat == self::mfLLISTA && $this->PermetAfegir) { 
//			$sRetorn .= '<TD style="align:right">';
//			$sRetorn .= '<span style="float:right;">';
			$URL = GeneraURL($this->URLEdicio);
			$sRetorn .= '  <a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnNou" name="btnNou">Nou</a>';
//			$sRetorn .= '</span>';
//			$sRetorn .= '</TD>';
		}
		$sRetorn .= '</span>';
		$sRetorn .= '</TD>';		
		
		$sRetorn .= '    </TR>';
		$sRetorn .= '    </TABLE>';
		$sRetorn .= $this->GeneraPartOculta();
		$sRetorn .= '  </FORM>';
		$sRetorn .= '</DIV>';
		return $sRetorn;
	}

	/**
	 * Genera el filtre del formulari si n'hi ha.
	 */
	private function GeneraFiltre() {
		return $this->Filtre->CreaFiltre();
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
		echo '<script language="javascript" src="js/Forms.js?v1.14" type="text/javascript"></script>';
		for($i = 1; $i <= count($this->FitxerJS); $i++) {
			echo '<script language="javascript" src="js/'.$this->FitxerJS[$i].'" type="text/javascript"></script>';
		}
		// Inicialització de l'ajuda
		// https://getbootstrap.com/docs/4.0/components/popovers/
		echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

		echo $this->GeneraMissatges();
		echo $this->GeneraCerca();
		echo $this->GeneraFiltre();
		echo $this->GeneraTaula();
		CreaFinalHTML();
	}
	
	/**
	 * Afegeix una opció per a cada registre.
	 * @param string $Titol Títol de l'opció.
	 * @param string $URL URL de l'opció. Se li afegirà l'identificador del registre.
	 * @param string $CampClau Camp del registre que serveix com a identificador. 
	 * 		Si no s'especifica, com a paràmetre es passarà l'identificador del registre. 
	 * @param string $Imatge Imatge a posar en comptes del títol. 
	 */
	public function AfegeixOpcio(string $Titol, string $URL, string $CampClau = '', string $Imatge = '') {
		$i = count($this->Opcions);
		$i++;
		$this->Opcions[$i] = new stdClass();
		$this->Opcions[$i]->Tipus = self::toURL;
		$this->Opcions[$i]->Titol = $Titol;
		$this->Opcions[$i]->URL = $URL;
		$this->Opcions[$i]->Camp = $CampClau;
		$this->Opcions[$i]->Imatge = $Imatge;
		$this->Opcions[$i]->Opcions = [];
	}

	/**
	 * Afegeix una opció AJAX per a cada registre. Un cop executada es tornarà a cridar ...
	 * @param string $Titol Títol de l'opció.
	 * @param string $Funcio Funció JavaScript. 
	 * @param string $CampClau Camp del registre que serveix com a identificador. 
	 * 		Si no s'especifica, com a paràmetre es passarà l'identificador del registre. 
	 * @param array $ofr Opcions. 
	 * @param string $CampValor Camp del registre que analitzem el seu valor (opció ofrBOOLEA ). 
	 */
	public function AfegeixOpcioAJAX(string $Titol, string $Funcio, string $CampClau = '', array $ofr = [], string $CampValor = '') {
		$i = count($this->Opcions);
		$i++;
		$this->Opcions[$i] = new stdClass();
		$this->Opcions[$i]->Tipus = self::toAJAX;
		$this->Opcions[$i]->Titol = $Titol;
		$this->Opcions[$i]->Funcio = $Funcio;
		$this->Opcions[$i]->Camp = $CampClau;
		$this->Opcions[$i]->Opcions = $ofr;
		$this->Opcions[$i]->CampValor = $CampValor;
	}
	
	/**
	 * Afegeix un color depenent d'un camp.
	 * @param string $Titol Títol de l'opció.
	 * @param string $CampClau Camp del registre que serveix com a identificador. 
	 * @param string $Prefix Prefix de la imatge a posar en comptes del títol. 
	 * @param string $Extensio Extensió de la imatge a posar en comptes del títol. 
	 * @param string $Llegenda Llegenda del codi de colors. 
	 */
	public function AfegeixOpcioColor(string $Titol, string $CampClau, string $Prefix, string $Extensio, string $Llegenda = '') {
		$i = count($this->Opcions);
		$i++;
		$this->Opcions[$i] = new stdClass();
		$this->Opcions[$i]->Tipus = self::toImatge;
		$this->Opcions[$i]->Titol = $Titol;
		$this->Opcions[$i]->Camp = $CampClau;
		$this->Opcions[$i]->Prefix = $Prefix;
		$this->Opcions[$i]->Extensio = $Extensio;
		$this->Opcions[$i]->Llegenda = $Llegenda;
		$this->Opcions[$i]->Opcions = [];
	}
	
	/**
	 * Genera les opcions per a cada registre.
	 * @param integer $Id Identificafdor del registre.
	 * @param array $row Registre.
	 */
	private function GeneraOpcions($Id, $row) {
		$Retorn = '';
		foreach($this->Opcions as $obj) {
			//$Retorn .= '<TD>';
			$NomesLectura = (in_array(self::ofrNOMES_LECTURA, $obj->Opcions)) ? ' disabled ' : '';
			
			if ($obj->Tipus == self::toURL) {
				// AfegeixOpcio
//				if ($obj->Camp == '')
//					$Retorn .= '<TD><A HREF="'.$obj->URL.$Id.'">'.$obj->Titol.'<A>';
//				else 
//					$Retorn .= '<TD><A HREF="'.$obj->URL.$row[$obj->Camp].'">'.$obj->Titol.'<A>';
				$URL = ($obj->Camp == '') ? $obj->URL.$Id : $obj->URL.$row[$obj->Camp];
//				$Retorn .= '<TD><A HREF="'.$URL.'">'.$obj->Titol.'<A>';
				$ToolTip = ' data-toggle="tooltip" data-placement="top" title="'.$obj->Titol.'" ';
				$Text = ($obj->Imatge == '') ? $obj->Titol : '<IMG SRC="img/'.$obj->Imatge.'" '.$ToolTip.'>';
				$Retorn .= '<TD><A HREF="'.GeneraURL($URL).'">'.$Text.'<A>';
				
			}
			else if ($obj->Tipus == self::toAJAX) {
				// AfegeixOpcioAJAX
				if (in_array(self::ofrCHECK, $obj->Opcions) || in_array(self::ofrNOMES_CHECK, $obj->Opcions)) {
//print_r($row['usuari_bloquejat']);
//print_r($row);
					$NoMostrisCheckBox = False;
					$Checked = ($row[$obj->CampValor] == 1) ? ' checked ' : '';
					if ($obj->Camp == '')
						$Funcio = $obj->Funcio.'(this, '.$Id.')';
					else {
//echo '$row[$obj->Camp]:'.$row[$obj->Camp].'<BR>';
						// Si el camp és extern (no la clau primària) pot ser que valgui NULL o res
						if ($row[$obj->Camp] == '')
							$NoMostrisCheckBox = True;
						$Funcio = $obj->Funcio.'(this, '.$row[$obj->Camp].')';
					}
					$Nom = $obj->Funcio.'_'.$Id;

					$Retorn .= '<TD style="text-align:center">';
					
					if (!(in_array(self::ofrNOMES_CHECK, $obj->Opcions) && $row[$obj->CampValor] == 1) && !$NoMostrisCheckBox)
						$Retorn .= '<input type="checkbox" '.$Checked.$NomesLectura.' id='.$Nom.' name='.$Nom.' onClick="'.$Funcio.'">';
//					$Retorn .= '<input class="form-control mr-sm-2" type="checkbox" name="chb_'.$Valor->Camp.'" '.$this->ValorCampCheckBox($Valor->Camp).$Requerit.'>';
					
				}
				else {
					if ($obj->Camp == '')
						$Retorn .= '<TD><A HREF="#" onClick="'.$obj->Funcio.'('.$Id.')";>'.$obj->Titol.'<A>';
					else 
						$Retorn .= '<TD><A HREF="#" onClick="'.$obj->Funcio.'('.$row[$obj->Camp].')";>'.$obj->Titol.'<A>';
//				$Retorn .= 'AJAX';
//				echo "<TD width=2><input type=text ".$Deshabilitat." style='".$style."' name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." id='".$Id."' value='".$ValorNota."' size=1 onfocus='ObteNota(this);' onBlur='ActualitzaNota(this);' onkeydown='NotaKeyDown(this, event);'></TD>";
//				$Retorn .= '<A HREF="#" onClick="'.$obj->Funcio.'('.$Id.')";>'.$obj->Titol.'<A>';
				}
			}
			else if ($obj->Tipus == self::toImatge) {
				$Retorn .= '<TD ALIGN=center VALIGN=bottom>';
				$Retorn .= '<IMG SRC="img/'.$obj->Prefix.$row[$obj->Camp].'.'.$obj->Extensio.'">';
			}			
			$Retorn .= '</TD>';
		}
		return $Retorn;
	}
	
		/**
	 * Exporta el contingut d'una SQL a un fitxer CSV.
 	 * @param string $SQL Sentència SQL a exportar.
	 * @param string $filename Nom del fitxer.
	 * @param string $delimiter Separador.
	 */
	 public function ExportaCSV(string $SQL, string $filename="export.csv", string $delimiter=";") {
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'. $filename .'";');

		// Clean output buffer
		ob_end_clean();

		$handle = fopen('php://output', 'w');
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$bPrimerRegistre = True;
			while($row = $ResultSet->fetch_assoc()) {
				$aExport = [];
				if ($bPrimerRegistre) {
					foreach ($row as $key => $value) {
						array_push($aExport, utf8_encode($key));
					}
					fputcsv($handle, $aExport, $delimiter);
					$bPrimerRegistre = False;
					$aExport = [];
				}					
				foreach ($row as $key => $value) {
					array_push($aExport, utf8_encode($value));
				}
				fputcsv($handle, $aExport, $delimiter);
			}
		}
		fclose($handle);

		// Flush buffer
		ob_flush();

		// Use exit to get rid of unexpected output afterward
		exit();		
	}	
} 

/**
 * Classe FormFitxa.
 *
 * Classe per als formularis de fitxa.
 */
class FormFitxa extends Form {
	/**
	* Indica si la clau primària de la taula és autoincrementable o no.
	* @var boolean
	*/    
    public $AutoIncrement = False;	

	/**
	* Camps del formulari amb les seves característiques. S'usa per generar els components visuals.
	* @var array
	*/    
    private $Camps = [];	

	/**
	* Títol del formulari de recerca.
	* @var string
	*/    
    public $Titol = '';

	/**
	* Permet editar un registre.
	* @var boolean
	*/    
    public $PermetEditar = False; 

	/**
	* Permet suprimir un registre.
	* @var boolean
	*/    
    public $PermetSuprimir = False; 

	/**
	* En el cas que s'estigui editant un registre, carrega les dades de la base de dades.
	* @var object
	*/    
    public $Registre = null;

	/**
	* Indica si el formulari té pestanyes.
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
	 * Afegeix un espai (horitzontal) formulari.
	 *
	 * @param integer $altura Altura.
	 * @return void
	 */
	public function AfegeixEspai(int $altura = 2) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcESPAI;
//		$this->Camps[$i]->Camp = $camp;
//		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Longitud = $altura;
		$this->Camps[$i]->Opcions = [];
	}
	
	/**
	 * Afegeix un camp calculat del tipus especificat al formulari.
	 *
	 * @param string $calcul Tipus de camp calculat.
	 * @param string $camp Camp de la taula.
	 * @param string $titol Títol del camp.
	 * @param integer $longitud Longitud màxima.
	 * @param array $off Opcions del formulari.
	 * @return void
	 */
	public function AfegeixCalculat(int $calcul, string $camp, string $titol, int $longitud, array $off = []) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcCALCULAT;
		$this->Camps[$i]->Camp = $camp;
		$this->Camps[$i]->Calcul = $calcul;
		$this->Camps[$i]->Titol = $titol;
		$this->Camps[$i]->Longitud = 5*$longitud;
		$this->Camps[$i]->Opcions = $off;
	}

	/**
	 * Afegeix una fotografia.
	 * @param string $Camp Camp de la taula.
	 * @param string $Sufix Sufix del fitxer.
	 * @return void
	 */
	public function AfegeixFotografia(string $Camp, string $Sufix) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcFOTOGRAFIA;
		$this->Camps[$i]->Camp = $Camp;
		$this->Camps[$i]->Sufix = $Sufix;
		$this->Camps[$i]->Opcions = [];
	}
	
	/**
	 * Afegeix un text amb format (RichEdit o RichMemo).
	 * @param string $Camp Camp de la taula.
	 * @param string $Titol Títol del control.
	 * @param integer $Longitud Longitud del text.
	 * @param integer $Altura Altura del text.
	 * @param array $off Opcions del formulari.
	 * @param mixed $Contingut Valor del text per defecte.
	 * @return string Codi HTML del text enriquit.
	 */
	public function AfegeixTextRic(string $Camp, string $Titol, int $Longitud, int $Altura, array $off = []) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcTEXT_RIC;
		$this->Camps[$i]->Camp = $Camp;
		$this->Camps[$i]->Titol = $Titol;
		$this->Camps[$i]->Longitud = 5*$Longitud;
//		$this->Camps[$i]->Altura = $Altura;
		$this->Camps[$i]->Opcions = $off;		
	}	

	/**
	 * Afegeix un text fix en HTML.
	 * @param string $Text Camp de la taula.
	 * @param string $Titol Títol del camp.
	 * @return void
	 */
	public function AfegeixHTML(string $Text, string $Titol) {
		$i = count($this->Camps);
		$i++;
		$this->Camps[$i] = new stdClass();
		$this->Camps[$i]->Tipus = self::tcHTML;
		$this->Camps[$i]->Text = $Text;
		$this->Camps[$i]->Titol = $Titol;
		$this->Camps[$i]->Opcions = [];
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
		if ($this->Registre == NULL)
			$Retorn = '';
		else 
			$Retorn = ' value="'.utf8_encode($this->Registre[$camp]).'" ';
		return $Retorn;
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
			$NomesLectura = (in_array(self::offNOMES_LECTURA, $Valor->Opcions) || $this->NomesLectura) ? ' readonly' : '';
			$bAlCostat = in_array(self::offAL_COSTAT, $Valor->Opcions);
			switch ($Valor->Tipus) {
				case self::tcESPAI:
					$sRetorn .= '</TR><TR style="padding:'.$Valor->Longitud.'px"><TD>&nbsp</TD>';
					break;
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
					$NomesLecturaCB = ($NomesLectura == '') ? '' : ' disabled';
					$sRetorn .= '<TD><input class="form-control mr-sm-2" type="checkbox" name="chb_'.$Valor->Camp.'" '.$this->ValorCampCheckBox($Valor->Camp).$Requerit.$NomesLecturaCB.'></TD>';
					break;
				case self::tcDATA:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= $this->CreaData($Valor->Camp, $Valor->Titol, $Valor->Opcions, $this->ValorCampData($Valor->Camp));
					break;
				case self::tcSELECCIO:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
//exit;
					$CodiSeleccionat = ($this->Registre == NULL) ? '' : $this->Registre[$Valor->Camp];
//					$CodiSeleccionat = $this->Registre[$Valor->Camp];
					$sRetorn .= $this->CreaLlista($Valor->Camp, $Valor->Titol, $Valor->Longitud, $Valor->Llista->Codis, $Valor->Llista->Valors, $CodiSeleccionat);
//					$sRetorn .= $this->CreaLlista($Valor->Camp, $Valor->Titol, $Valor->Longitud, $Valor->Llista->Codis, $Valor->Llista->Valors, $this->Registre[$Valor->Camp]);
					break;
				case self::tcLOOKUP:
					$CodiSeleccionat = ($this->Registre == NULL) ? '' : $this->Registre[$Valor->Camp];
//print_r($this->Registre);	
//exit;			
					if ($this->NomesLectura)
						array_push($Valor->Opcions, self::offNOMES_LECTURA);
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= $this->CreaLookup(
						$Valor->Camp, 
						$Valor->Titol, 
						$Valor->Longitud, 
						$Valor->Lookup->URL, 
						$Valor->Lookup->Taula, 
						$Valor->Lookup->Id, 
						$Valor->Lookup->Camps, 
						$Valor->Opcions, 
						$CodiSeleccionat);
					break;
				case self::tcCALCULAT:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= $this->CreaCalculat($Valor->Calcul, $Valor->Camp, $Valor->Titol, $Valor->Longitud, $this->Registre[$Valor->Camp], $Valor->Opcions);
					break;
				case self::tcFOTOGRAFIA:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
//echo '<hr>'.$Valor->Camp;
//echo '<hr>'.$this->ValorCampText($Valor->Camp);
//echo '<hr>'.$this->Registre[$Valor->Camp];
					$sRetorn .= $this->CreaFotografia($this->Registre[$Valor->Camp], $Valor->Sufix);
					break;
				case self::tcTEXT_RIC:
					$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= $this->CreaTextRic($Valor->Camp, $Valor->Titol, $Valor->Longitud, $Valor->Longitud, $this->Registre[$Valor->Camp], $Valor->Opcions);
//print_r($sRetorn);					
//					$sRetorn .= $this->CreaTextRic($Valor->Text, $Valor->Titol);
					break;
					
					
					
				case self::tcHTML:
					//$sRetorn .= (!$bAlCostat) ? '</TR><TR>' : '';
					$sRetorn .= $this->CreaHTML($Valor->Text, $Valor->Titol);
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
		if (!$this->NomesLectura)
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
			$aClauPrimaria = explode(',', $this->ClauPrimaria);
			$aId = explode(',', $this->Id);
			for ($i=0; $i<count($aClauPrimaria); $i++) 
				$aClauPrimaria[$i] .= '='.$aId[$i];
			$Where = implode(' AND ', $aClauPrimaria);
//echo '<br>'.$Where.'<br>';			
			$SQL = 'SELECT * FROM '.$this->Taula.' WHERE '.$Where;
//echo '<br>'.$SQL.'<br>';			
			$ResultSet = $this->Connexio->query($SQL);
			if ($ResultSet->num_rows > 0) {
				$this->Registre = $ResultSet->fetch_assoc();
			}
		}
	}

	/**
	 * Genera el botó i l'acció de tornar enrera (cap a la llista).
	 */
	private function GeneraTorna() {
		$sRetorn = '<div class="collapse" id="MissatgeTorna">';
		$sRetorn .= '<a class="btn btn-primary active" role="button" aria-pressed="true" id="btnTorna" name="btnTorna" onclick="window.history.go(-1); return false;">Torna</a>';
		$sRetorn .= '</div>';
		return $sRetorn;
	}
	
	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, $this->Titol);
		echo '<script language="javascript" src="js/Forms.js?v1.7" type="text/javascript"></script>';
		if ($this->Id > 0)
			$this->CarregaDades();
		echo $this->GeneraFitxa();
		echo $this->GeneraMissatges();
		echo $this->GeneraTorna();
		CreaFinalHTML();
	}
	
	/**
	 * Desa la fitxa a la base de dades.
	 * @param string $jsonForm Fitxa a desar en format JSON.
	 * @return string Missatge informatiu.
	 */
	public function Desa(string $jsonForm): string {
		$Retorn = '';
		$data = json_decode($jsonForm);
		$sCamps = '';
		$sValues = '';
		foreach($data as $Valor) {
			if ($Valor->name == 'hid_Taula') 
				$Taula = $Valor->value;
			else if ($Valor->name == 'hid_ClauPrimaria') 
				$ClauPrimaria = $Valor->value;
			else if ($Valor->name == 'hid_AutoIncrement') 
				$AutoIncrement = $Valor->value;
			else if ($Valor->name == 'hid_Id') 
				$Id = $Valor->value;
			else {
				$Tipus = substr($Valor->name, 0, 3);
				switch ($Tipus) {
					case 'edt':
						// Camp text
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= TextAMySQL($Valor->value).', ';
//						if ($Valor->value == '')
//							$sValues .= "NULL, ";
//						else
//							$sValues .= "'".$Valor->value."', ";
						break;
					case 'edd':
						// Camp data
						$sCamps .= substr($Valor->name, 4).", ";
//print '<br>Data: '.$Valor->value;
//							if ComprovaData($Valor->value) 
								$sValues .= DataAMySQL($Valor->value).", ";
//							else
//								throw new Exception('Data no vàlida.');
						break;
					case 'chb':
						// Camp checkbox
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= (($Valor->value == '') || ($Valor->value == 0)) ? '0, ' : '1, ';
						break;
					case 'cmb':
						// Camp combobox (desplegable)
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= TextAMySQL($Valor->value).', ';
//print '<BR>Camp: '.$Valor->name . ' <BR> Value: '.$Valor->value . '<BR>';
//print_r($Valor);
//exit;
						break;
					case 'lkh':
						if (substr($Valor->name, -6) != '_camps') {
							// Camp lookup
							$sCamps .= substr($Valor->name, 4).", ";
							$sValues .= ($Valor->value == '') ? "NULL, " : $Valor->value.", ";
							//if ($Valor->value == '')
								//$sValues .= "NULL, ";
							//else
								//$sValues .= "'".$Valor->value."', ";
//print '<BR>Camp: '.$Valor->name . ' <BR> Value: '.$Valor->value . '<BR>';
//print_r($Valor);
						}
						break;
				}
			}
		}
		$sCamps = substr($sCamps, 0, -2);
		$sValues = substr($sValues, 0, -2);
//print '<hr>Camps: '.$sCamps . ' <BR> Values: '.$sValues.'<hr>';

		if ($Id > 0) {
			// UPDATE
			$SQL = "UPDATE ".$Taula." SET ";
			$aCamps = explode(",", TrimXX($sCamps));
//			$aValues = explode(",", Trim($sValues));
			$aValues = CSVAArray(Trim($sValues));
//print_r($aValues);
			for($i=0; $i < count($aCamps); $i++) {
				$SQL .= $aCamps[$i].'='.trim($aValues[$i]).', ';
			}
			$SQL = substr($SQL, 0, -2);
			$SQL .= ' WHERE '.$ClauPrimaria.'='.$Id;
		}
		else {
			// INSERT
			if ($AutoIncrement) {
				$SQL = "INSERT INTO ".$Taula." (".$sCamps.") VALUES (".$sValues.")";
			}
			else {
				$sCamps = $ClauPrimaria.', '.$sCamps;
				$sValues = '(SELECT MAX('.$ClauPrimaria.')+1 FROM '. $Taula.'), '.$sValues;
				$SQL = "INSERT INTO ".$Taula." (".$sCamps.") SELECT ".$sValues;
			}
		}

		$SQL = utf8_decode($SQL);
		if (Config::Debug)		
			$Retorn .= '<BR><b>SQL</b>: '.$SQL;
		
		try {
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			$Retorn .= "<BR><b>ERROR DesaFitxa</b>. Causa: ".$e->getMessage();
		}		
		return $Retorn;
	}	
} 

?>