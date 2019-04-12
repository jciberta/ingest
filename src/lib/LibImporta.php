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
 * Formats suportats:
 * 	- SAGA: per encaixar amb la base de dades, el pare és el responsable 1 i la mare el responsable 2
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
	 * Obté la clau primària de l'usuari a partit del NIF.
	 * @param string $NIF NIF de l'usuari
	 * @return string Clau primària o NULL si no existeix.
	 */
	private function ObteIDPerNIF(string $NIF): string {
		$Retorn = 'NULL';
		if ($NIF != '') {
			$SQL = 'SELECT usuari_id FROM USUARI WHERE document="'.$NIF.'"';
			$ResultSet = $this->Connexio->query($SQL);
			if ($ResultSet->num_rows > 0) {
				$row = $ResultSet->fetch_assoc();
				$Retorn = $row["usuari_id"];
			}
			$ResultSet->close();
		}
		return $Retorn;
	}

	/**
	 * Actualitza un usuari a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function Actualitza(string $NIF, array $Linia, string $NIFPare, string $NIFMare) {
		$IdPare = 'NULL';
		if ($NIFPare != $NIF)
			$IdPare = $this->ObteIDPerNIF($NIFPare);

		$IdMare = 'NULL';
		if ($NIFMare != $NIF)
			$IdMare = $this->ObteIDPerNIF($NIFMare);

		$Nom = $this->ObteNom($Linia[$this->CampsNom['COGNOMS I NOM']]);
		$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['COGNOMS I NOM']]);

		$SQL = "UPDATE USUARI SET ".
			" nom=".TextAMySQL($Nom).", ".
			" cognom1=".TextAMySQL($aCognoms[0]).", ".
			" cognom2=".TextAMySQL($aCognoms[1]).", ".				
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
			" nacionalitat=".TextAMySQL($Linia[$this->CampsNom['NACIONALITAT']]).", ".
			" pare_id=".$IdPare.", ".
			" mare_id=".$IdMare.
			" WHERE username='".$NIF."'";
		$ResultSet = $this->Connexio->query($SQL);
	}

	/**
	 * Actualitza un pare a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function ActualitzaPareSAGA(string $NIF, array $Linia) {
		$NIF = trim($NIF);
//print $NIF . ' <br>';		
		if ($NIF != '') {
			$Nom = $this->ObteNom($Linia[$this->CampsNom['RESPONSABLE 1']]);
			$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['RESPONSABLE 1']]);
			$SQL = "UPDATE USUARI SET ".
				" es_pare=1, ".
				" nom=".TextAMySQL($Nom).", ".
				" cognom1=".TextAMySQL($aCognoms[0]).", ".
				" cognom2=".TextAMySQL($aCognoms[1]).", ".				
				" nom_complet=".TextAMySQL($Linia[$this->CampsNom['RESPONSABLE 1']]).", ".
				" adreca=".TextAMySQL($Linia[$this->CampsNom['ADREÇA RESP. 1']]).", ".
				" poblacio=".TextAMySQL($Linia[$this->CampsNom['LOCALITAT RESP. 1']]).", ".
				" municipi=".TextAMySQL($Linia[$this->CampsNom['MUNICIPI RESP. 1']]).", ".
				" provincia=".TextAMySQL($Linia[$this->CampsNom['PROVÍNCIA RESP. 1']]).
				" WHERE username='".$NIF."'";
//print $SQL . ' <br>';		
			$ResultSet = $this->Connexio->query($SQL);
		}
	}

	/**
	 * Actualitza una mare a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function ActualitzaMareSAGA(string $NIF, array $Linia) {
		$NIF = trim($NIF);
//print $NIF . ' <br>';		
		if ($NIF != '') {
			$Nom = $this->ObteNom($Linia[$this->CampsNom['RESPONSABLE 2']]);
			$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['RESPONSABLE 2']]);
			$SQL = "UPDATE USUARI SET ".
				" es_pare=1, ".
				" nom=".TextAMySQL($Nom).", ".
				" cognom1=".TextAMySQL($aCognoms[0]).", ".
				" cognom2=".TextAMySQL($aCognoms[1]).", ".				
				" nom_complet=".TextAMySQL($Linia[$this->CampsNom['RESPONSABLE 2']]).", ".
				" codi_postal=".TextAMySQL($Linia[$this->CampsNom['CP RESP. 2']]).", ".
				" adreca=".TextAMySQL($Linia[$this->CampsNom['ADREÇA RESP. 2']]).", ".
				" poblacio=".TextAMySQL($Linia[$this->CampsNom['LOCALITAT RESP. 2']]).", ".
				" municipi=".TextAMySQL($Linia[$this->CampsNom['MUNICIPI RESP. 2']]).", ".
				" provincia=".TextAMySQL($Linia[$this->CampsNom['PROVÍNCIA RESP. 2']]).
				" WHERE username='".$NIF."'";
//print $SQL . ' <br>';		
			$ResultSet = $this->Connexio->query($SQL);
		}
	}
	
	/**
	 * Inserta un usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function Inserta(array $Linia, string $NIFPare, string $NIFMare) {
		// INSERT INTO Taula (...) VALUES (SELECT FROM Taula, ...) -> MySQL no deixa fer-ho
		// Per tant:
		// INSERT INTO Taula (...) SELECT (SELECT FROM Taula) AS ...
		$NIF = $this->ObteNIF($Linia[$this->CampsNom['DOC. IDENTITAT']]); 
		$NIF = trim($NIF);
		if ($NIF != '') {
			$IdPare = 'NULL';
			if ($NIFPare != $NIF)
				$IdPare = $this->ObteIDPerNIF($NIFPare);

			$IdMare = 'NULL';
			if ($NIFMare != $NIF)
				$IdMare = $this->ObteIDPerNIF($NIFMare);

			$Nom = $this->ObteNom($Linia[$this->CampsNom['COGNOMS I NOM']]);
			$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['COGNOMS I NOM']]);
			$SQL = "INSERT INTO USUARI (usuari_id, username, password, nom, cognom1, cognom2, nom_complet, codi, sexe, tipus_document, document, telefon, adreca, codi_postal, poblacio, municipi, provincia, data_naixement, municipi_naixement, nacionalitat, pare_id, mare_id) ".
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
				TextAMySQL($Linia[$this->CampsNom['NACIONALITAT']]).", ".
				$IdPare.", ".
				$IdMare;
//print $SQL . ' <br>';		
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	

	/**
	 * Inserta un pare.
     * @param array $Linia Línia CSV a importar.
	 */
	private function InsertaPareSAGA(array $Linia) {
		// INSERT INTO Taula (...) VALUES (SELECT FROM Taula, ...) -> MySQL no deixa fer-ho
		// Per tant:
		// INSERT INTO Taula (...) SELECT (SELECT FROM Taula) AS ...
		$NIF = $Linia[$this->CampsNom['D.N.I. RESP. 1']]; 
		$NIF = trim($NIF);
//print $NIF . ' <br>';		
		if ($NIF != '') {
			$Nom = $this->ObteNom($Linia[$this->CampsNom['RESPONSABLE 1']]);
			$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['RESPONSABLE 1']]);
			$SQL = "INSERT INTO USUARI (usuari_id, es_pare, username, password, nom, cognom1, cognom2, nom_complet, document, adreca, poblacio, municipi, provincia) ".
				" SELECT ".
				" (SELECT MAX(usuari_id)+1 FROM USUARI) AS usuari_id, ".
				"1, ".
				TextAMySQL($NIF).", ".
				TextAMySQL(password_hash($NIF, PASSWORD_DEFAULT)).", ".
				TextAMySQL($Nom).", ".
				TextAMySQL($aCognoms[0]).", ".
				TextAMySQL($aCognoms[1]).", ".
				TextAMySQL($Linia[$this->CampsNom['RESPONSABLE 1']]).", ".
				TextAMySQL($NIF).", ".
				TextAMySQL($Linia[$this->CampsNom['ADREÇA RESP. 1']]).", ".
				TextAMySQL($Linia[$this->CampsNom['LOCALITAT RESP. 1']]).", ".
				TextAMySQL($Linia[$this->CampsNom['MUNICIPI RESP. 1']]).", ".
				TextAMySQL($Linia[$this->CampsNom['PROVÍNCIA RESP. 1']]);
//print $SQL . ' <br>';		
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	

	/**
	 * Inserta una mare.
     * @param array $Linia Línia CSV a importar.
	 */
	private function InsertaMareSAGA(array $Linia) {
		// INSERT INTO Taula (...) VALUES (SELECT FROM Taula, ...) -> MySQL no deixa fer-ho
		// Per tant:
		// INSERT INTO Taula (...) SELECT (SELECT FROM Taula) AS ...
		$NIF = $Linia[$this->CampsNom['D.N.I. RESP. 2']]; 
		$NIF = trim($NIF);
//print $NIF . ' <br>';		
		if ($NIF != '') {
			$Nom = $this->ObteNom($Linia[$this->CampsNom['RESPONSABLE 2']]);
			$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['RESPONSABLE 2']]);
			$SQL = "INSERT INTO USUARI (usuari_id, es_pare, username, password, nom, cognom1, cognom2, nom_complet, document, codi_postal, adreca, poblacio, municipi, provincia) ".
				" SELECT ".
				" (SELECT MAX(usuari_id)+1 FROM USUARI) AS usuari_id, ".
				"1, ".
				TextAMySQL($NIF).", ".
				TextAMySQL(password_hash($NIF, PASSWORD_DEFAULT)).", ".
				TextAMySQL($Nom).", ".
				TextAMySQL($aCognoms[0]).", ".
				TextAMySQL($aCognoms[1]).", ".
				TextAMySQL($Linia[$this->CampsNom['RESPONSABLE 2']]).", ".
				TextAMySQL($NIF).", ".
				TextAMySQL($Linia[$this->CampsNom['CP RESP. 2']]).", ".
				TextAMySQL($Linia[$this->CampsNom['ADREÇA RESP. 2']]).", ".
				TextAMySQL($Linia[$this->CampsNom['LOCALITAT RESP. 2']]).", ".
				TextAMySQL($Linia[$this->CampsNom['MUNICIPI RESP. 2']]).", ".
				TextAMySQL($Linia[$this->CampsNom['PROVÍNCIA RESP. 2']]);
//print $SQL . ' <br>';		
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	
	
	/**
	 * Importa/actualitza el pare de l'usuari (importació SAGA).
     * @param array $Linia Línia CSV a importar.
	 */
	public function ImportaPareSAGA(array $Linia) {
//print_r($Linia);
//print_r($this->CampsNom);

		$NIF = trim($Linia[$this->CampsNom['D.N.I. RESP. 1']]);
//print('<br>'.$NIF.'<br>');
		if ($this->ExisteixUsuariPerNIF($NIF)) {
			// Actualitza
			$this->ActualitzaPareSAGA($NIF, $Linia);
			print 'Actualitzat  [resp1]  '.trim($Linia[$this->CampsNom['RESPONSABLE 1']]).' <br>';		
		}
		else {
			// Inserta
			$this->InsertaPareSAGA($Linia);
			print 'Inserit      [resp1]  '.trim($Linia[$this->CampsNom['RESPONSABLE 1']]).' <br>';		
		}
	}

	/**
	 * Importa/actualitza la mare de l'usuari (importació SAGA).
     * @param array $Linia Línia CSV a importar.
	 */
	public function ImportaMareSAGA(array $Linia) {
		$NIF = trim($Linia[$this->CampsNom['D.N.I. RESP. 2']]);
		if ($this->ExisteixUsuariPerNIF($NIF)) {
			// Actualitza
			$this->ActualitzaMareSAGA($NIF, $Linia);
			print 'Actualitzat  [resp2]  '.trim($Linia[$this->CampsNom['RESPONSABLE 2']]).' <br>';		
		}
		else {
			// Inserta
			$this->InsertaMareSAGA($Linia);
			print 'Inserit      [resp2]  '.trim($Linia[$this->CampsNom['RESPONSABLE 2']]).' <br>';		
		}
	}
	
	/**
	 * Importa/actualitza l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	public function Importa(array $Linia) {
		echo '<pre>';
		$Linia = CodificaArrayUTF8($Linia);
		switch ($this->Modalitat) {
			case self::tiSAGA:
				$NIF = $this->ObteNIF($Linia[$this->CampsNom['DOC. IDENTITAT']]);
				$NIFPare = trim($Linia[$this->CampsNom['D.N.I. RESP. 1']]);
				$NIFMare = trim($Linia[$this->CampsNom['D.N.I. RESP. 2']]);
//echo $NIF.'  '.$NIFPare.'<br>';

				if ($NIF != $NIFPare) {
					// L'alumne està repetit quan és ell mateix el responsable
					if ($NIFPare != '')
						$this->ImportaPareSAGA($Linia);
					if ($NIFMare != '')
						$this->ImportaMareSAGA($Linia);
				}

				if ($this->ExisteixUsuariPerNIF($NIF)) {
					// Actualitza
					$this->Actualitza($NIF, $Linia, $NIFPare, $NIFMare);
//print $NIF.'<br>';		
					print 'Actualitzat  [alumne] '.trim($Linia[$this->CampsNom['COGNOMS I NOM']]).' <br>';		
				}
				else {
					// Inserta
					$this->Inserta($Linia, $NIFPare, $NIFMare);
//print $NIF.' - NO <br>';		
					print 'Inserit      [alumne] '.trim($Linia[$this->CampsNom['COGNOMS I NOM']]).' <br>';		
				}
				break;
		}
		echo '</pre>';
	}

} 

?>
