<?php

/** 
 * LibImporta.php
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibArray.php');
//require_once(ROOT.'/lib/LibHTML.php');

/**
 * Classe Importa.
 *
 * Classe base de la quals descendeixen les importacions.
 */
class Importa {
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
	 * Constructor de l'objecte.
	 * @param object $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	
} 

/**
 * Classe ImportaUsuaris.
 *
 * Classe per a la importació d'usuaris.
 */
class ImportaUsuaris extends Importa {

	// Tipus importació.
	const tiSAGA = 1;

	/**
	* Tipus importació.
	*  - tiSAGA: Importació del CSV generat per SAGA.
	*  - ...
	* @access public
	*/    
    public $Modalitat = self::tiSAGA;

	/**
	* Camps de la capçalera indexats per número.
	* @access protected
	* @var array
	*/    
    protected $Camps = [];	

	/**
	* Camps de la capçalera indexats per nom.
	* @access protected
	* @var array
	*/    
    protected $CampsNom = [];	

	/**
	 * Tracta la primera línia on hi ha la capçalera de les dades.
     * @param array Primera línia.
	 */
	public function TractaPrimeraLinia(array $Linia) {
		switch ($this->Modalitat) {
			case self::tiSAGA:
				for ($i=0; $i < count($Linia); $i++) {
					$nom = utf8_encode($Linia[$i]);
					$this->Camps[$i] = $nom;
					$this->CampsNom[$nom] = $i;
				}
				break;
		}
//print_r($this->CampsNom);
	}

	/**
	 * Obte el NIF/NIE/Passaport a partir del camp CSV del SAGA.
     * @param string $Text Text que conté el identificador.
     * @return string NIF/NIE/Passaport.
	 */
	private function ObteNIF(string $Text): string {
		$Retorn = '';
		if ($Text != '') {
			$aText = explode(" ", $Text);
			if (count($aText)>1)
				$Retorn = Trim($aText[1]);
		}
		return $Retorn;
	}

	/**
	 * Obté el nom a partir del camp CSV del SAGA.
     * @param string $Text Text que conté el nom.
     * @return string Nom.
	 */
	private function ObteNom(string $Text): string {
		$Retorn = '';
		if ($Text != '') {
			$aText = explode(",", $Text);
			if (count($aText)>1)
				$Retorn = Trim($aText[1]);
		}
		return $Retorn;
	}

	/**
	 * Obté els cognoms "normalitzats" (sense preposicions) a partir del camp CSV del SAGA.
     * @param string $Text Text que conté el nom complet.
     * @return array 1r cognom i 2n cognom en un array.
	 */
	private function ObteCognoms(string $Text): array {
		$Retorn = ['', ''];
		if ($Text != '') {
			$aText = explode(",", $Text);
			$Text = $aText[0];
			
			$Text = ' '.$Text;
			$Text = str_replace(' de ', '', $Text);
			$Text = str_replace(' del ', '', $Text);
			$Text = str_replace(' de la ', '', $Text);
			$Text = str_replace(' di ', '', $Text);
			$Text = str_replace(' el ', '', $Text);
			$Text = str_replace(' i ', '', $Text);
			
			$aText = explode(" ", trim($Text));
			$Retorn[0] = $aText[0];
			if (count($aText)>1)
				$Retorn[1] = $aText[1];
		}
		return $Retorn;
	}

	/**
	 * Comprova si existeix un usuari a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @return bool Cert si ja està donat d'alta.
	 */
	private function ExisteixUsuariPerNIF(string $NIF): bool {
		$SQL = "SELECT * FROM USUARI WHERE username='".$NIF."'";
		$ResultSet = $this->Connexio->query($SQL);
		return ($ResultSet->num_rows > 0);
	}

	/**
	 * Actualitza un usuari a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function Actualitza(string $NIF, array $Linia) {
		$SQL = "UPDATE USUARI SET ".
			" nom_complet=".TextAMySQL($Linia[$this->CampsNom['COGNOMS I NOM']]).", ".
			" codi=".$Linia[$this->CampsNom['ID']].", ".
			" sexe=".TextAMySQL($Linia[$this->CampsNom['SEXE']]).", ".
			" tipus_document=".TextAMySQL(substr($Linia[$this->CampsNom['DOC. IDENTITAT']], 0, 1)).", ".
			" document=".TextAMySQL($this->ObteNIF($Linia[$this->CampsNom['DOC. IDENTITAT']])).", ".
			" telefon=".TextAMySQL($Linia[$this->CampsNom['ALTRES TELÈFONS']]).", ".
			" adreca=".TextAMySQL($Linia[$this->CampsNom['ADREÇA']]).", ".
			" codi_postal=".TextAMySQL($Linia[$this->CampsNom['CP']]).", ".
			" poblacio=".TextAMySQL($Linia[$this->CampsNom['LOCALITAT']]).", ".
			" municipi=".TextAMySQL($Linia[$this->CampsNom['MUNICIPI']]).", ".
			" provincia=".TextAMySQL($Linia[$this->CampsNom['PROVÍNCIA']]).", ".
			" data_naixement=".DataAMySQL($Linia[$this->CampsNom['DATA NAIXEMENT']]).", ".
			" municipi_naixement=".TextAMySQL($Linia[$this->CampsNom['NOM MUNICIPI NAIXEMENT']]).", ".
			" nacionalitat=".TextAMySQL($Linia[$this->CampsNom['NACIONALITAT']]).
			" WHERE username='".$NIF."'";
		
//print $SQL . ' <br>';		
		$ResultSet = $this->Connexio->query($SQL);
	}
	
	/**
	 * Inserta un usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function Inserta(array $Linia) {
		// INSERT INTO Taula (...) VALUES (SELECT FROM Taula, ...) -> MySQL no deixa fer-ho
		// Per tant:
		// INSERT INTO Taula (...) SELECT (SELECT FROM Taula) AS ...
		$NIF = $this->ObteNIF($Linia[$this->CampsNom['DOC. IDENTITAT']]); 
		$Nom = $this->ObteNom($Linia[$this->CampsNom['COGNOMS I NOM']]);
		$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['COGNOMS I NOM']]);
		$SQL = "INSERT INTO USUARI (usuari_id, username, password, nom, cognom1, cognom2, nom_complet, codi, sexe, tipus_document, document, telefon, adreca, codi_postal, poblacio, municipi, provincia, data_naixement, municipi_naixement, nacionalitat) ".
			" SELECT ".
			" (SELECT MAX(usuari_id)+1 FROM USUARI) AS usuari_id, ".
			TextAMySQL($NIF).", ".
			TextAMySQL(password_hash($NIF, PASSWORD_DEFAULT)).", ".
			TextAMySQL($Nom).", ".
			TextAMySQL($aCognoms[0]).", ".
			TextAMySQL($aCognoms[1]).", ".
			TextAMySQL($Linia[$this->CampsNom['COGNOMS I NOM']]).", ".
			$Linia[$this->CampsNom['ID']].", ".
			TextAMySQL($Linia[$this->CampsNom['SEXE']]).", ".
			TextAMySQL(substr($Linia[$this->CampsNom['DOC. IDENTITAT']], 0, 1)).", ".
			TextAMySQL($NIF).", ".
			TextAMySQL($Linia[$this->CampsNom['ALTRES TELÈFONS']]).", ".
			TextAMySQL($Linia[$this->CampsNom['ADREÇA']]).", ".
			TextAMySQL($Linia[$this->CampsNom['CP']]).", ".
			TextAMySQL($Linia[$this->CampsNom['LOCALITAT']]).", ".
			TextAMySQL($Linia[$this->CampsNom['MUNICIPI']]).", ".
			TextAMySQL($Linia[$this->CampsNom['PROVÍNCIA']]).", ".
			DataAMySQL($Linia[$this->CampsNom['DATA NAIXEMENT']]).", ".
			TextAMySQL($Linia[$this->CampsNom['NOM MUNICIPI NAIXEMENT']]).", ".
			TextAMySQL($Linia[$this->CampsNom['NACIONALITAT']]);
		
//print $SQL . ' <br>';		
		$ResultSet = $this->Connexio->query($SQL);
	}	
	
	/**
	 * Importa/actualitza l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	public function Importa(array $Linia) {
		$Linia = CodificaArrayUTF8($Linia);
		switch ($this->Modalitat) {
			case self::tiSAGA:
				$NIF = $this->ObteNIF($Linia[$this->CampsNom['DOC. IDENTITAT']]);
				if ($this->ExisteixUsuariPerNIF($NIF)) {
					// Actualitza
					$this->Actualitza($NIF, $Linia);
//print $NIF.'<br>';		
print 'Actualitzat '.trim($Linia[$this->CampsNom['COGNOMS I NOM']]).' <br>';		
				}
				else {
					// Inserta
					$this->Inserta($Linia);
//print $NIF.' - NO <br>';		
print 'Inserit     '.trim($Linia[$this->CampsNom['COGNOMS I NOM']]).' <br>';		
				}
				break;
		}
	}

} 

?>
