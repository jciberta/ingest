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
require_once('LibHTML.php');

/**
 * Classe Form.
 *
 * Classe base de la quals descendeixen els formularis.
 */
class Form {
} 

/**
 * Classe FormRecerca.
 *
 * Classe per als formularis de recerca.
 */
class FormRecerca extends Form {
	/**
	* Connexió a la base de dades.
	* @access private
	* @var object
	*/    
	private $Connexio;
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
	 * Constructor de l'objecte.
	 *
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con) {
		$this->Connexio = $con;
	}

	/**
	 * Crea la nova SQL a partir de les propietats {@link $SQL} i {@link $Filtre}.
     *
     * @return string Sentència SQL.
	 */
	public function CreaSQL() {
		$sRetorn = $this->SQL;
		if ($this->Filtre != '') {
			$sWhere = '';
			$aFiltre = explode(" ", TrimX($this->Filtre));
			$aCamps = explode(",", TrimXX($this->Camps));
			foreach ($aCamps as $sCamp) {
				foreach ($aFiltre as $sValor) {
					$sWhere .= $sCamp . " LIKE '%" . $sValor . "%' OR ";
				}
			}
			$sRetorn .= ' WHERE ' . substr($sWhere, 0, -4);
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
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {

			
			$sRetorn .= '<TABLE class="table table-striped">';
			// Capçalera
			$sRetorn .= '<THEAD class="thead-dark">';
			$aDescripcions = explode(",", TrimX($this->Descripcions));
			foreach ($aDescripcions as $sValor) {
				$sRetorn .= "<TH>" . utf8_encode($sValor) . "</TH>";
			}
			$sRetorn .= '</THEAD>';

			$aCamps = explode(",", TrimXX($this->Camps));

			while($row = $ResultSet->fetch_assoc()) {
				$sRetorn .= "<TR>";

				foreach($aCamps as $data) {
					$sValor = $row[$data];
					$sRetorn .= utf8_encode("<TD>".$sValor."</TD>");
				}

//				foreach($row as $data) {
//					$sRetorn .= utf8_encode("<TD>".$data."</TD>");
//				}

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
		CreaIniciHTML($this->Titol);
		echo '<script language="javascript" src="js/Forms.js" type="text/javascript"></script>';
		echo $this->GeneraCerca();
		echo $this->GeneraTaula();
		CreaFinalHTML();
	}
} 

?>
