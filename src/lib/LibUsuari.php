<?php

/** 
 * LibUsuari.php
 *
 * Llibreria d'utilitats per a l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Classe que encapsula les utilitats per al maneig de l'usuari.
 */
class Usuari
{
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
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	

	/**
	 * Comprova si l'usuari és administrador.
	 * @returns boolean Cert si l'usuari és administrador.
	 */
	function EsAdmin(): bool {
		return $this->Usuari->es_admin == '1';
	}	

	/**
	 * Comprova si l'usuari és direcció.
	 * @returns boolean Cert si l'usuari és direcció.
	 */
	function EsDireccio(): bool {
		return $this->Usuari->es_direccio == '1';
	}	

	/**
	 * Comprova si l'usuari és cap d'estudis.
	 * @returns boolean Cert si l'usuari és cap d'estudis.
	 */
	function EsCapEstudis(): bool {
		return $this->Usuari->es_cap_estudis == '1';
	}	

	/**
	 * Comprova si l'usuari és pare/mare d'un alumne.
	 * @param $alumne Identificador de l'alumne
	 * @returns boolean Cert si l'usuari és pare/mare de l'alumne.
	 */
	function EsProgenitor(int $alumne): bool {
		$SQL = ' SELECT * FROM USUARI WHERE usuari_id='.$alumne.' AND (pare_id='.$this->Usuari->usuari_id.' OR mare_id='.$this->Usuari->usuari_id.')';
		$ResultSet = $this->Connexio->query($SQL);
		return ($ResultSet->num_rows > 0);
	}	
}

/**
 * Classe que encapsula les utilitats per al maneig dels alumnes.
 */
class Alumne extends Usuari
{
	/**
	 * Obté l'identificador de la matrícula activa d'un alumne.
	 * @param $AlumneId Identificador de l'alumne
	 * @return integer Identificador de la matrícula.
	 */
	public function ObteMatriculaActiva(int $AlumneId): int {
		$Retorn = -1;
		$SQL = ' SELECT matricula_id FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) '.
			' WHERE alumne_id='.$AlumneId.' AND actual=1 ';
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_object();
			$Retorn = $row->matricula_id;
		}
		return $Retorn;
	}
	
	/**
	 * Llista les matrícules d'un alumne.
	 * @param $alumne Identificador de l'alumne
	 * @return string Codi HTML de la llista de matrícules.
	 */
	function Matricules(int $alumne): string {
		$Retorn = '';
		$SQL = ' SELECT M.matricula_id, C.nom FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' WHERE alumne_id='.$alumne;
//print_r($this);
//exit;
		$ResultSet = $this->Connexio->query($SQL);
		while ($rsMatricula = $ResultSet->fetch_object()) {
			$URL = '';
			if ($this->EsAdmin() || $this->EsDireccio() || $this->EsCapEstudis()) {
				$URL = 'MatriculaAlumne.php?accio=MostraExpedient&MatriculaId='.$rsMatricula->matricula_id;
				if (Config::EncriptaURL)
					$URL = GeneraURL($URL);
				$Retorn .= "<A HREF='$URL'>".utf8_encode($rsMatricula->nom)."</A><br>";
			}
			else
				$Retorn .= utf8_encode($rsMatricula->nom).'<br>';
		}		
		return $Retorn;
	}	
}

?>