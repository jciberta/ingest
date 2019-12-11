<?php

/** 
 * LibPassword.php
 *
 * Llibreria d'utilitats per a les contrasenyes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');

/**
 * Classe que encapsula les utilitats per a la recuperació de contrasenyes.
 */
class RecuperaPassword
{
	/**
	* Connexió a la base de dades.
	* @var object
	*/    
	public $Connexio;

	/**
	* Objecte que defineix la pàgina web.
	* @var object
	*/    
	private $Portal;
	
	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con) {
		$this->Connexio = $con;
		$this->Portal = new Portal();
		$this->Portal->JavaScript = 
			'<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>'.
			'<script language="javascript" src="js/Password.js?v1.4" type="text/javascript"></script>';
	}	

	/**
	 * Escriu la capçalera de la pàgina web.
	 */				
	public function EscriuCapcalera() {
		$this->Portal->EscriuCapcalera();
	}

	/**
	 * Escriu el peu de la pàgina web.
	 */				
	public function EscriuPeu(bool $bRecuperaContrasenya = True) {
		$this->Portal->EscriuPeu($bRecuperaContrasenya);
	}

	// https://stackoverflow.com/questions/4356289/php-random-string-generator
	protected function GeneraPassword(int $length = 10): string {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}			
	
	/**
	 * Genera els missatges de succés i error.
	 */
	public function GeneraMissatges() {
		$sRetorn = '<div class="alert alert-success collapse" id="MissatgeCorrecte" role="alert">';
		$sRetorn .= "La nova contrasenya és ...";
		$sRetorn .= '</div>';
		$sRetorn .= '<div class="alert alert-danger collapse" id="MissatgeError" role="alert">';
		$sRetorn .= "Les dades introduïdes no coincideixen amb les de l'alumne.";
		$sRetorn .= '</div>';
		$sRetorn .= '<div class="alert alert-danger collapse" id="MissatgeFaltenDades" role="alert">';
		$sRetorn .= "Falten dades. Reviseu els camps.";
		$sRetorn .= '</div>';
		return $sRetorn;
	}

	/**
	 * Escriu els missatges de succés i error.
	 */
	public function EscriuMissatges() {
		echo $this->GeneraMissatges();
	}	
}

/**
 * Classe que encapsula les utilitats per a la recuperació de la contrasenya del tutor.
 */
class RecuperaPasswordTutor extends RecuperaPassword
{
	/**
	 * Crea la SQL per recuperar un fill.
	 * @param string $DNITutor DNI del tutor.
     * @return string Sentència SQL.
	 */
	public function CreaSQL($DNITutor) {
		$SQL = " SELECT * FROM USUARI ".
			" WHERE pare_id IN (SELECT usuari_id FROM USUARI WHERE document='$DNITutor' AND es_pare=1)".
			" OR mare_id IN (SELECT usuari_id FROM USUARI WHERE document='$DNITutor' AND es_pare=1)";
		return $SQL;
	}	
	
	/**
	 * Escriu el formulari.
	 * @param string $Nom Nom del fill/a.
	 * @param string $DNITutor DNI del tutor.
	 */
	public function EscriuFormulari(string $Nom, string $DNITutor) {
		echo '		<div class="d-flex justify-content-center">';
		echo '			<form id="RecuperaPassword">';
		echo '				<h3 class="d-flex justify-content-center">Recupera contrasenya</h3>';
		echo '				<br><br>';
		echo '				<div>';
		echo '				Ompliu les següents dades del vostre fill/a '.$Nom.':';
		echo '				</div>';
		echo '				<br><br>';
		echo '				<table>';
		echo '					<tr>';
		echo '						<td><div style="width:200px">DNI</div></td>';
		echo '						<td><input type="text" class="form-control" id="dni" required></td>';
		echo '					</tr>';
		echo '					<tr>';
		echo '						<td>Data de naixement</td>';
		echo '						<td><input type="text" class="form-control" id="data_naixement" required></td>';
		echo '					</tr>';
		echo '				</table>';
		echo '				<br>';
		echo '				<div class="small">';
		echo "				La data ha d'estar en (dd/mm/aaaa).";
		echo '				</div>';
		echo '				<br>';
		echo '              <a class="btn btn-primary active" role="button" onclick="RecuperaPasswordTutor();">Recupera</a>';
		echo '				<br>';
		echo '				<br>';
		echo "				<input type='hidden' id='dni_tutor' value='$DNITutor'>";
		echo '			</form>';
		echo '		</div>';
	}
		
	/**
	 * Comprova si existeix un tutor amb un fill amb DNI i data de naixement, i genera una nova contrasenya.
	 * @param string $DNI DNI de l'alumne.
	 * @param string $DNITutor DNI del tutor.
	 * @param string $DataNaixement data de naixement de l'alumne.
	 * @return string Nova contrasenya, '' si no coincideixen els camps.
	 */
	public function Recupera(string $DNI, string $DNITutor, string $DataNaixement): string {
		$Retorn = '';
		$SQL = " SELECT * FROM USUARI ".
			" WHERE es_alumne=1 AND document='$DNI' AND data_naixement=".DataAMySQL($DataNaixement).
			" AND (pare_id IN (SELECT usuari_id FROM USUARI WHERE document='$DNITutor' AND es_pare=1)".
			" 	OR mare_id IN (SELECT usuari_id FROM USUARI WHERE document='$DNITutor' AND es_pare=1))";
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$Retorn = $this->GeneraPassword();
			$SQL = ' UPDATE USUARI SET '.
				'   password='.TextAMySQL(password_hash($Retorn, PASSWORD_DEFAULT)).', '.
				'   usuari_bloquejat=0, '.
				'   imposa_canvi_password=1 '.
				' WHERE document='.TextAMySQL($DNITutor).' AND es_pare=1';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
		}		
		//return $SQL;
		return $Retorn;
	}
}

/**
 * Classe que encapsula les utilitats per a la recuperació de la contrasenya de l'alumne.
 */
class RecuperaPasswordAlumne extends RecuperaPassword
{
	/**
	 * Escriu el formulari.
	 * @param string $DNI DNI de l'alumne.
	 */
	public function EscriuFormulari(string $DNI) {
		echo '		<div class="d-flex justify-content-center">';
		echo '			<form id="RecuperaPassword">';
		echo '				<h3 class="d-flex justify-content-center">Recupera contrasenya</h3>';
		echo '				<br><br>';
		echo '				<div>';
		echo '				Ompliu les següents dades personals:';
		echo '				</div>';
		echo '				<br><br>';
		echo '				<table>';
		echo '					<tr>';
		echo '						<td><div style="width:200px">DNI</div></td>';
		echo "						<td><input type='text' class='form-control' id='dni' value='$DNI' readonly></td>";
		echo '					</tr>';
		echo '					<tr>';
		echo '						<td>Data de naixement</td>';
		echo '						<td><input type="text" class="form-control" id="data_naixement" required></td>';
		echo '					</tr>';
		echo '					<tr>';
		echo '						<td>Telèfon</td>';
		echo '						<td><input type="text" class="form-control" id="telefon" required></td>';
		echo '					</tr>';
		echo '					<tr>';
		echo '						<td>Municipi de naixement</td>';
		echo '						<td><input type="text" class="form-control" id="municipi_naixement" required></td>';
		echo '					</tr>';
		echo '				</table>';
		echo '				<br>';
		echo '				<div class="small">';
		echo "				La data ha d'estar en (dd/mm/aaaa).<br>";
		echo "				El telèfon ha de ser el que consta a la matrícula.<br>";
		echo '				</div>';
		echo '				<br>';
		echo '              <a class="btn btn-primary active" role="button" onclick="RecuperaPasswordAlumne();">Recupera</a>';
		echo '				<br>';
		echo '				<br>';
		echo '			</form>';
		echo '		</div>';
	}	

	/**
	 * Comprova si existeix un alumne amb dni, data de naixement, telèfon i municipi de naixement, i genera una nova contrasenya.
	 * @param string $DNI DNI de l'alumne.
	 * @param string $DataNaixement Data de naixement de l'alumne.
	 * @param string $Telefon Telèfon de l'alumne (1r telèfon de l'alumne de la matrícula).
	 * @param string $MunicipiNaixement Municipi de naixement de l'alumne.
	 * @return string Nova contrasenya, '' si no coincideixen els camps.
	 */
	public function Recupera(string $DNI, string $DataNaixement, string $Telefon, string $MunicipiNaixement): string {
		$Retorn = '';
		$DNI = strtoupper($DNI);
		$MunicipiNaixement = strtoupper($MunicipiNaixement);
		$SQL = " SELECT * FROM USUARI ".
			" WHERE es_alumne=1 ".
			" AND UPPER(document)='$DNI' ".
			" AND data_naixement=".DataAMySQL($DataNaixement).
			" AND telefon LIKE '%$Telefon%' ";
//			" AND UPPER(municipi_naixement)=".TextAMySQL($MunicipiNaixement); // Hi ha un problema amb la codificació (UTF-8)
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$Retorn = $this->GeneraPassword();
			$SQL = ' UPDATE USUARI SET '.
				'   password='.TextAMySQL(password_hash($Retorn, PASSWORD_DEFAULT)).', '.
				'   usuari_bloquejat=0, '.
				'   imposa_canvi_password=1 '.
				' WHERE document='.TextAMySQL($DNI).' AND es_alumne=1';
			if (!$this->Connexio->query($SQL))
				throw new Exception($this->Connexio->error.'. SQL: '.$SQL);
		}		
		//return $SQL;
		return $Retorn;
	}
}

?>