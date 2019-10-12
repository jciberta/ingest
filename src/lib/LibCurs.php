<?php

/** 
 * LibCurs.php
 *
 * Llibreria d'utilitats per als cursos.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibForms.php');


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
		$SQL = ' SELECT C.curs_id, C.codi, C.nom AS NomCurs, C.nivell, C.finalitzat, AA.any_inici, AA.any_final, '.
			' CASE '.
			'     WHEN C.finalitzat = 1 THEN "Tancada" '.
			'     WHEN C.avaluacio = "ORD" THEN "Ordinària" '.
			'     WHEN C.avaluacio = "EXT" THEN "Extraordinària" '.
			' END AS avaluacio, '.
			' CASE '.
			'     WHEN C.avaluacio = "ORD" THEN C.trimestre '.
			'     WHEN C.avaluacio = "EXT" THEN NULL '.
			' END AS trimestre '.
			' FROM CURS C '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=C.any_academic_id) ';
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Titol = 'Cursos';
		$frm->SQL = utf8_decode($SQL);
		$frm->Taula = 'CURS';
		$frm->ClauPrimaria = 'curs_id';
		$frm->Camps = 'codi, NomCurs, nivell, any_inici, any_final, avaluacio, trimestre';
		$frm->Descripcions = 'Codi, Nom, Nivell, Any inici, Any final, Avaluació, Trimestre';
		$frm->AfegeixOpcio('Alumnes', 'UsuariRecerca.php?accio=Matricules&CursId=');
		$frm->AfegeixOpcio('Grups', 'Grups.php?CursId=');
		$frm->AfegeixOpcio('Notes', 'Notes.php?CursId=');
		$frm->AfegeixOpcio('Avaluació', 'Avaluacio.php?CursId=');
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Curs';
		$frm->PermetAfegir = ($this->Usuari->es_admin || $this->Usuari->es_direccio || $this->Usuari->es_cap_estudis);

		// Filtre
//		$frm->Filtre->AfegeixCheckBox('finalitzat', 'Avaluacions tancades', False); -> Funciona, però la casuística és estranya
		$frm->Filtre->AfegeixLlista('finalitzat', 'Avaluació', 30, array('0', '1', ''), array('Oberta', 'Tancada', 'Totes'));

		$frm->EscriuHTML();
	}
}

?>