<?php

/** 
 * LibImporta.php
 *
 * Llibreria d'utilitats per a les importacions.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibArray.php');
require_once(ROOT.'/lib/LibMatricula.php');

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
 * 	- SAGA2: format SAGA de la secretària (d'on va sortir?)f
 */
class ImportaUsuaris extends Importa {

	// Tipus importació.
	const tiSAGA = 1;
	const tiSAGA2 = 2; 

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
//    protected $Camps = [];	

	/**
	* Camps de la capçalera indexats per nom.
	* @access protected
	* @var array
	*/    
//    protected $CampsNom = [];	

	/**
	 * Tracta la primera línia on hi ha la capçalera de les dades.
     * @param array Primera línia.
	 */
	public function TractaPrimeraLinia(array $Linia) {
		switch ($this->Modalitat) {
			case self::tiSAGA:
			case self::tiSAGA2:
				for ($i=0; $i < count($Linia); $i++) {
					$nom = CodificaUTF8($Linia[$i]);
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
	 * Retorna el nom complet a partir d'una línia del fitxer CSV.
     * @param array $Linia Línia del fitxer CSV.
     * @param string $Prefix Per indicar el pare o la mare (T1 o T2).
     * @return string Nom complet.
	 */
	private function NomComplet(array $Linia, string $Prefix = ''): string {
		$NomComplet = trim($Linia[$this->CampsNom[$Prefix.'NOM']].' '.$Linia[$this->CampsNom[$Prefix.'COGNOM1']].' '.$Linia[$this->CampsNom[$Prefix.'COGNOM2']]);
		return $NomComplet;
	}
	/**
	 * Comprova si existeix un usuari a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @return bool Cert si ja està donat d'alta.
	 */
	private function ExisteixUsuariPerNIF(string $NIF): bool {
		$NIF = trim($NIF);
		if ($NIF!='' && $NIF!='-') {
			$SQL = "SELECT * FROM USUARI WHERE username='".$NIF."'";
			$ResultSet = $this->Connexio->query($SQL);
			return ($ResultSet->num_rows > 0);
		}
		else
			return False;
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

		$Nom = $Linia[$this->CampsNom['NOM']];
		$aCognom1 = $Linia[$this->CampsNom['COGNOM1']];
		$aCognom2 = $Linia[$this->CampsNom['COGNOM2']];

		$SQL = "UPDATE USUARI SET ".
			" es_alumne=1, ".
			" nom=".TextAMySQL($Nom).", ".
			" cognom1=".TextAMySQL($aCognom1).", ".
			" cognom2=".TextAMySQL($aCognom2).", ".				
			" nom_complet=".TextAMySQL($this->NomComplet($Linia)).", ".
			" codi=".$Linia[$this->CampsNom['ID']].", ".
			" sexe=".TextAMySQL($Linia[$this->CampsNom['SEXE']]).", ".
			" tipus_document=".TextAMySQL($Linia[$this->CampsNom['TP DNI']]).", ".
			" document=".TextAMySQL($Linia[$this->CampsNom['DNI']]).", ".
			" telefon=".TextAMySQL($Linia[$this->CampsNom['TELS']]).", ".
			" adreca=".TextAMySQL($Linia[$this->CampsNom['ADRECA']]).", ".
			" codi_postal=".TextAMySQL($Linia[$this->CampsNom['CP']]).", ".
			" poblacio=".TextAMySQL($Linia[$this->CampsNom['POBLACIO']]).", ".
			" municipi=".TextAMySQL($Linia[$this->CampsNom['MUNICIPI']]).", ".
			" provincia=".TextAMySQL($Linia[$this->CampsNom['PROVINCIA']]).", ".
			" data_naixement=".DataAMySQL($Linia[$this->CampsNom['DATA_NAIXEMENT']]).", ".
			" municipi_naixement=".TextAMySQL($Linia[$this->CampsNom['MUNIC_NAIXEMENT']]).", ".
			" nacionalitat=".TextAMySQL($Linia[$this->CampsNom['NACIONALITAT']]).", ".
			" pare_id=".$IdPare.", ".
			" mare_id=".$IdMare.
			" WHERE username='".$NIF."'";
//print $SQL . ' <br>';		
			$SQL = utf8_decode($SQL);
		$ResultSet = $this->Connexio->query($SQL);
	}

	/**
	 * Actualitza un usuari a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function Actualitza2(string $NIF, array $Linia, string $NIFPare, string $NIFMare) {
		$IdPare = 'NULL';
		if ($NIFPare != $NIF)
			$IdPare = $this->ObteIDPerNIF($NIFPare);

		$IdMare = 'NULL';
		if ($NIFMare != $NIF)
			$IdMare = $this->ObteIDPerNIF($NIFMare);

		$Nom = $this->ObteNom($Linia[$this->CampsNom['COGNOMS I NOM']]);
		$aCognoms = $this->ObteCognoms($Linia[$this->CampsNom['COGNOMS I NOM']]);

		$SQL = "UPDATE USUARI SET ".
			" es_alumne=1, ".
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
//print $SQL . ' <br>';		
			$SQL = utf8_decode($SQL);
		$ResultSet = $this->Connexio->query($SQL);
	}

	/**
	 * Actualitza un responsable a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
     * @param int $Responsable Responsable 1 o 2.
	 */
	private function ActualitzaResponsableSAGA(string $NIF, array $Linia, int $Responsable) {
		$NIF = trim($NIF);
//print $NIF . ' <br>';		
		if ($NIF!='' && $NIF!='-') {
			$Nom = $Linia[$this->CampsNom['T'.$Responsable.' NOM']];
			$Cognom1 = $Linia[$this->CampsNom['T'.$Responsable.' COGNOM1']];
			$Cognom2 = $Linia[$this->CampsNom['T'.$Responsable.' COGNOM2']];
			
			$SQL = "UPDATE USUARI SET ".
				" es_pare=1, ".
				" nom=".TextAMySQL($Nom).", ".
				" cognom1=".TextAMySQL($Cognom1).", ".
				" cognom2=".TextAMySQL($Cognom2).", ".				
				" nom_complet=".TextAMySQL($this->NomComplet($Linia, 'T'.$Responsable.' ')).", ".
				" adreca=".TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' ADRECA']]).", ".
				" poblacio=".TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' LOCALITAT']]).", ".
				" municipi=".TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' MUNICIPI']]).", ".
				" provincia=".TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' PROVINCIA']]).
				" WHERE username='".$NIF."'";
//print $SQL . ' <br>';		
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}

	/**
	 * Actualitza un pare a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function ActualitzaPareSAGA2(string $NIF, array $Linia) {
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
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}

	/**
	 * Actualitza una mare a través del seu NIF.
     * @param string $NIF NIF de l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function ActualitzaMareSAGA2(string $NIF, array $Linia) {
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
			$SQL = utf8_decode($SQL);
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
		$NIF = $Linia[$this->CampsNom['DNI']]; 
		$NIF = trim($NIF);
		if ($NIF != '') {
			$IdPare = 'NULL';
			if ($NIFPare != $NIF)
				$IdPare = $this->ObteIDPerNIF($NIFPare);

			$IdMare = 'NULL';
			if ($NIFMare != $NIF)
				$IdMare = $this->ObteIDPerNIF($NIFMare);

			$Nom = $Linia[$this->CampsNom['NOM']];
			$aCognom1 = $Linia[$this->CampsNom['COGNOM1']];
			$aCognom2 = $Linia[$this->CampsNom['COGNOM2']];
			$SQL = "INSERT INTO USUARI (usuari_id, es_alumne, username, password, nom, cognom1, cognom2, nom_complet, codi, sexe, tipus_document, document, telefon, adreca, codi_postal, poblacio, municipi, provincia, data_naixement, municipi_naixement, nacionalitat, pare_id, mare_id) ".
				" SELECT ".
				" (SELECT MAX(usuari_id)+1 FROM USUARI) AS usuari_id, ".
				"1, ".
				TextAMySQL($NIF).", ".
				TextAMySQL(password_hash($NIF, PASSWORD_DEFAULT)).", ".
				TextAMySQL($Nom).", ".
				TextAMySQL($aCognom1).", ".
				TextAMySQL($aCognom2).", ".
				TextAMySQL($this->NomComplet($Linia)).", ".
				$Linia[$this->CampsNom['ID']].", ".
				TextAMySQL($Linia[$this->CampsNom['SEXE']]).", ".
				TextAMySQL($Linia[$this->CampsNom['TP DNI']]).", ".
				TextAMySQL($NIF).", ".
				TextAMySQL($Linia[$this->CampsNom['TELS']]).", ".
				TextAMySQL($Linia[$this->CampsNom['ADRECA']]).", ".
				TextAMySQL($Linia[$this->CampsNom['CP']]).", ".
				TextAMySQL($Linia[$this->CampsNom['POBLACIO']]).", ".
				TextAMySQL($Linia[$this->CampsNom['MUNICIPI']]).", ".
				TextAMySQL($Linia[$this->CampsNom['PROVINCIA']]).", ".
				DataAMySQL($Linia[$this->CampsNom['DATA_NAIXEMENT']]).", ".
				TextAMySQL($Linia[$this->CampsNom['MUNIC_NAIXEMENT']]).", ".
				TextAMySQL($Linia[$this->CampsNom['NACIONALITAT']]).", ".
				$IdPare.", ".
				$IdMare;
//print $SQL . ' <br>';		
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	

	/**
	 * Inserta un usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	private function Inserta2(array $Linia, string $NIFPare, string $NIFMare) {
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
			$SQL = "INSERT INTO USUARI (usuari_id, es_alumne, username, password, nom, cognom1, cognom2, nom_complet, codi, sexe, tipus_document, document, telefon, adreca, codi_postal, poblacio, municipi, provincia, data_naixement, municipi_naixement, nacionalitat, pare_id, mare_id) ".
				" SELECT ".
				" (SELECT MAX(usuari_id)+1 FROM USUARI) AS usuari_id, ".
				"1, ".
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
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	

	/**
	 * Inserta un responsable.
     * @param array $Linia Línia CSV a importar.
     * @param int $Responsable Responsable 1 o 2.
	 */
	private function InsertaResponsableSAGA(array $Linia, int $Responsable) {
		// INSERT INTO Taula (...) VALUES (SELECT FROM Taula, ...) -> MySQL no deixa fer-ho
		// Per tant:
		// INSERT INTO Taula (...) SELECT (SELECT FROM Taula) AS ...
		$NIF = $Linia[$this->CampsNom['T'.$Responsable.' DNI']]; 
		$NIF = trim($NIF);
//print $NIF . ' <br>';		
		if ($NIF!='' && $NIF!='-') {
			$Nom = $Linia[$this->CampsNom['T'.$Responsable.' NOM']];
			$aCognom1 = $Linia[$this->CampsNom['T'.$Responsable.' COGNOM1']];
			$aCognom2 = $Linia[$this->CampsNom['T'.$Responsable.' COGNOM2']];
			
			$SQL = "INSERT INTO USUARI (usuari_id, es_pare, username, password, nom, cognom1, cognom2, nom_complet, document, adreca, poblacio, municipi, provincia) ".
				" SELECT ".
				" (SELECT MAX(usuari_id)+1 FROM USUARI) AS usuari_id, ".
				"1, ".
				TextAMySQL($NIF).", ".
				TextAMySQL(password_hash($NIF, PASSWORD_DEFAULT)).", ".
				TextAMySQL($Nom).", ".
				TextAMySQL($aCognom1).", ".
				TextAMySQL($aCognom2).", ".
				TextAMySQL($this->NomComplet($Linia, 'T'.$Responsable.' ')).", ".
				TextAMySQL($NIF).", ".
				TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' ADRECA']]).", ".
				TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' LOCALITAT']]).", ".
				TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' MUNICIPI']]).", ".
				TextAMySQL($Linia[$this->CampsNom['T'.$Responsable.' PROVINCIA']]);
//print $SQL . ' <br>';		
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	

	/**
	 * Inserta un pare.
     * @param array $Linia Línia CSV a importar.
	 */
	private function InsertaPareSAGA2(array $Linia) {
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
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	

	/**
	 * Inserta una mare.
     * @param array $Linia Línia CSV a importar.
	 */
	private function InsertaMareSAGA2(array $Linia) {
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
			$SQL = utf8_decode($SQL);
			$ResultSet = $this->Connexio->query($SQL);
		}
	}	
	
	/**
	 * Importa/actualitza el responsable de l'usuari (importació SAGA).
     * @param array $Linia Línia CSV a importar.
     * @param int $Responsable Responsable 1 o 2.
	 */
	public function ImportaResponsableSAGA(array $Linia, int $Responsable) {
		$NIF = trim($Linia[$this->CampsNom['T'.$Responsable.' DNI']]);
		if ($NIF!='' && $NIF!='-') {
			if ($this->ExisteixUsuariPerNIF($NIF)) {
				$this->ActualitzaResponsableSAGA($NIF, $Linia, $Responsable);
				print 'Actualitzat  [resp'.$Responsable.']  '.$this->NomComplet($Linia, 'T'.$Responsable.' ').' <br>';	
			}
			else {
				$this->InsertaResponsableSAGA($Linia, $Responsable);
				print 'Inserit      [resp'.$Responsable.']  '.$this->NomComplet($Linia, 'T'.$Responsable.' ').' <br>';	
			}
		}
	}

	/**
	 * Importa/actualitza el pare de l'usuari (importació SAGA).
     * @param array $Linia Línia CSV a importar.
	 */
	public function ImportaPareSAGA2(array $Linia) {
//print_r($Linia);
//print_r($this->CampsNom);

		$NIF = trim($Linia[$this->CampsNom['D.N.I. RESP. 1']]);
//print('<br>'.$NIF.'<br>');
		if ($this->ExisteixUsuariPerNIF($NIF)) {
			// Actualitza
			$this->ActualitzaPareSAGA2($NIF, $Linia);
			print 'Actualitzat  [resp1]  '.trim($Linia[$this->CampsNom['RESPONSABLE 1']]).' <br>';		
		}
		else {
			// Inserta
			$this->InsertaPareSAGA2($Linia);
			print 'Inserit      [resp1]  '.trim($Linia[$this->CampsNom['RESPONSABLE 1']]).' <br>';		
		}
	}

	/**
	 * Importa/actualitza la mare de l'usuari (importació SAGA).
     * @param array $Linia Línia CSV a importar.
	 */
	public function ImportaMareSAGA2(array $Linia) {
		$NIF = trim($Linia[$this->CampsNom['D.N.I. RESP. 2']]);
		if ($this->ExisteixUsuariPerNIF($NIF)) {
			// Actualitza
			$this->ActualitzaMareSAGA2($NIF, $Linia);
			print 'Actualitzat  [resp2]  '.trim($Linia[$this->CampsNom['RESPONSABLE 2']]).' <br>';		
		}
		else {
			// Inserta
			$this->InsertaMareSAGA2($Linia);
			print 'Inserit      [resp2]  '.trim($Linia[$this->CampsNom['RESPONSABLE 2']]).' <br>';		
		}
	}
	
	/**
	 * Importa/actualitza l'usuari.
     * @param array $Linia Línia CSV a importar.
	 */
	public function Importa(array $Linia) {
		echo '<pre>';
		//$Linia = CodificaArrayUTF8($Linia);
		switch ($this->Modalitat) {
			case self::tiSAGA:
				$NIF = trim($Linia[$this->CampsNom['DNI']]);
				$NIFPare = trim($Linia[$this->CampsNom['T1 DNI']]);
				$NIFMare = trim($Linia[$this->CampsNom['T2 DNI']]);

				if ($NIF != $NIFPare) {
					// L'alumne està repetit quan és ell mateix el responsable
					if ($NIFPare != '')
						$this->ImportaResponsableSAGA($Linia, 1);
					if ($NIFMare != '')
						$this->ImportaResponsableSAGA($Linia, 2);
				}

				if ($this->ExisteixUsuariPerNIF($NIF)) {
					$this->Actualitza($NIF, $Linia, $NIFPare, $NIFMare);
					print 'Actualitzat  [alumne] '.$this->NomComplet($Linia).' <br>';		
				}
				else {
					$this->Inserta($Linia, $NIFPare, $NIFMare);
					print 'Inserit      [alumne] '.$this->NomComplet($Linia).' <br>';		
				}
			
				break;
			case self::tiSAGA2:
				$NIF = $this->ObteNIF($Linia[$this->CampsNom['DOC. IDENTITAT']]);
				$NIFPare = trim($Linia[$this->CampsNom['D.N.I. RESP. 1']]);
				$NIFMare = trim($Linia[$this->CampsNom['D.N.I. RESP. 2']]);
//echo $NIF.'  '.$NIFPare.'<br>';

				if ($NIF != $NIFPare) {
					// L'alumne està repetit quan és ell mateix el responsable
					if ($NIFPare != '')
						$this->ImportaPareSAGA2($Linia);
					if ($NIFMare != '')
						$this->ImportaMareSAGA2($Linia);
				}

				if ($this->ExisteixUsuariPerNIF($NIF)) {
					// Actualitza
					$this->Actualitza2($NIF, $Linia, $NIFPare, $NIFMare);
//print $NIF.'<br>';		
					print 'Actualitzat  [alumne] '.trim($Linia[$this->CampsNom['COGNOMS I NOM']]).' <br>';		
				}
				else {
					// Inserta
					$this->Inserta2($Linia, $NIFPare, $NIFMare);
//print $NIF.' - NO <br>';		
					print 'Inserit      [alumne] '.trim($Linia[$this->CampsNom['COGNOMS I NOM']]).' <br>';		
				}
				break;
		}
		echo '</pre>';
	}

} 

/**
 * Classe ImportaPasswords.
 *
 * Classe per a la importació de les contrasenyes d'iEduca.
 * Només s'importen les contrasenyes dels alumnes i dels pares.
 */
class ImportaPasswords extends Importa {
	/**
	* Número de línia.
	* @var int
	*/    
	private $NumeroLinia = 1;

	/**
	 * Importa una línia la matrícula.
     * @param array $Linia Línia CSV a importar.
	 */
	private function ImportaLinia(array $Linia) {
		$aLinia = explode(",", $Linia[0]);
		$n = count($aLinia);
		if ($n < 4) return;

		$sDNI = CodificaUTF8($aLinia[0]);
		$sNom = CodificaUTF8($aLinia[1]);
		$sPwd = CodificaUTF8($aLinia[4]);
		
		$SQL = ' UPDATE USUARI SET '.
			'   password='.TextAMySQL(password_hash($sPwd, PASSWORD_DEFAULT)).', '.
			'   usuari_bloquejat=0, '.
			'   imposa_canvi_password=1 '.
			' WHERE document='.TextAMySQL($sDNI).' AND (es_alumne=1 OR es_pare=1)';


		// El document és molt llarg, s'ha de copiar les SQL directament al MySQL.
		//if (!$this->Connexio->query($SQL))
		//	throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
		//$mar = mysqli_affected_rows($this->Connexio);
		//if ($mar > 0) {
		//	print $sDNI.' '.$sNom.": contrasenya actualitzada ($sPwd).<br>";	
		//}
		//print $this->NumeroLinia++.':  '.$SQL.'<BR>';
		print ' '.$SQL.'<BR>';
	}
	
	/**
	 * Importa la matrícula.
     * @param string $Fitxer Fitxer CSV a importar.
	 */
	public function Importa(string $Fitxer) {
		if (($handle = fopen($Fitxer, "r")) !== FALSE) {
			echo '<pre>';
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				$this->ImportaLinia($data);
			}
			fclose($handle);
			echo '</pre>';
		}
		echo "Importació realitzada amb èxit.";		
	}
}


/**
 * Classe ImportaMatricula.
 *
 * Classe per a la importació de les matrícules.
 * Usa el mateix document de la importació d'alumnes.
 * Formats suportats:
 * 	- SAGA
 */
class ImportaMatricula extends Importa {
	/**
	* Array amb els cursos. S'hauria de poder configurar!
	* @var array
	*/    
	private static $CURSOS = array(
		'CFPM 1601 A' => 13, // CAI1
		'CFPM 1601 B' => 14, // CAI2
		'CFPM IC10 A' => 5,  // SMX1 AB
		'CFPM IC10 B' => 5,  // SMX1 BC
		'CFPM IC10 C' => 6,  // SMX2
		'CFPM SA20 A' => 9,  // FIP1
		'CFPM SA20 B' => 10, // FIP1
		'CFPM SC10 A' => 11, // APD1
		'CFPM SC10 B' => 12, // APD2
		'CFPS ICB0 A' => 7,  // DAM1
		'CFPS ICB0 B' => 8   // DAM2
	);

	/**
	* Objete per a fer les matriculacions.
	* @var object
	*/    
	private $Mat;

	/**
	 * Tracta la primera línia on hi ha la capçalera de les dades.
     * @param array Primera línia.
	 */
	private function TractaPrimeraLinia(array $Linia) {
		for ($i=0; $i < count($Linia); $i++) {
			$nom = CodificaUTF8($Linia[$i]);
			$this->Camps[$i] = $nom;
			$this->CampsNom[$nom] = $i;
		}
	}

	/**
	 * Importa una línia la matrícula.
     * @param array $Linia Línia CSV a importar.
	 */
	private function ImportaLinia(array $Linia) {
		echo '<pre>';

		$DNI = trim($Linia[$this->CampsNom['DNI']]);
		$Curs = trim($Linia[$this->CampsNom['CODI']]);
		
		$CursId = self::$CURSOS[$Curs];
//print_r($this->CampsNom);				
//print_r($Linia);				
//print '<p>'.$Curs.' '.$CursId.'<p>';				
//print 'DNI: '.$DNI.'<p>';				

		$Resultat = $this->Mat->CreaMatriculaDNI($CursId, $DNI, '', '');
		switch ($Resultat) {
			case 0:
				print 'Alumne '.$DNI.': matrícula creada.<br>';	
				break;
			case -1:
				print 'Alumne '.$DNI.': matrícula no creada. Alumne ja matriculat.<br>';	
				break;
			case -2:
				print 'Alumne '.$DNI.': matrícula no creada. DNI inexistent.<br>';	
				break;
			case -99:
				print 'Alumne '.$DNI.': matrícula no creada. Error base de dades.<br>';	
				break;
		}
		echo '</pre>';
	}
	
	/**
	 * Importa la matrícula.
     * @param string $Fitxer Fitxer CSV a importar.
	 */
	public function Importa(string $Fitxer) {
		$this->Mat = new Matricula($this->Connexio, $this->Usuari);
		$row = 1;
		if (($handle = fopen($Fitxer, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				if ($row == 1)
					$this->TractaPrimeraLinia($data);
				else 
					$this->ImportaLinia($data);
				$row++;
			}
			fclose($handle);
		}
		echo "Importació realitzada amb èxit.";		
	}
}

?>
