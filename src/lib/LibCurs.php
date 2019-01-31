<?php

/** 
 * LibCurs.php
 *
 * Llibreria d'utilitats per als cursos.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('LibForms.php');

/**
 * Classe que encapsula les utilitats per al maneig de l'usuari.
 */
class Curs
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
	* Registre carregat amb CarregaRegistre.
	* @access private
	* @var object
	*/    
	private $Registre = NULL;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
	}	

	/**
	 * Carrega el registre especificat de la taula CURS.
	 * @param integer $Id Identificador del registre.
	 */				
	public function CarregaRegistre($Id) {
		$SQL = "SELECT * FROM CURS WHERE curs_id=".$Id;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$this->Registre = $ResultSet->fetch_object();
		}
	}
	
	/**
	 * Obté el codi del cicle formatiu del curs.
	 * @returns integer Identificador del cicle formatiu, sinó -1.
	 */
	function ObteCicleFormatiuId() {
		if ($this->Registre === NULL) 
			return -1;
		else
			return $this->Registre->cicle_formatiu_id;
	}

	/**
	 * Obté el nivell del curs.
	 * @returns integer Nivell del curs, sinó -1.
	 */
	function ObteNivell() {
		if ($this->Registre === NULL) 
			return -1;
		else
			return $this->Registre->nivell;
	}

	/**
	 * Genera el llistat de cursos.
	 */
	function EscriuFormulariRecera() {
		$SQL = ' SELECT C.curs_id, C.codi, C.nom AS NomCurs, C.nivell, AA.any_inici, AA.any_final '.
			' FROM CURS C '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) ';
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Titol = 'Cursos';
		$frm->SQL = $SQL;
		$frm->Taula = 'CURS';
		$frm->ClauPrimaria = 'curs_id';
		$frm->Camps = 'codi, NomCurs, nivell, any_inici, any_final';
		$frm->Descripcions = 'Codi, Nom, Nivell, Any inici, Any final';
		$frm->AfegeixOpcio('Alumnes', 'UsuariRecerca.php?accio=Alumnes&CursId=');
		$frm->AfegeixOpcio('Notes', 'Notes.php?CursId=');
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Curs';
		$frm->EscriuHTML();
	}
}

?>
