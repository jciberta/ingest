<?php

/** 
 * LibUsuari.php
 *
 * Llibreria d'utilitats per a l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibForms.php');

/**
 * Classe que encapsula les utilitats per al maneig de l'usuari.
 * No es creen les subclasses Professor, Alumne... ja que un usuari pot ser diversos rols a la vegada.
 */
class Usuari
{
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
	* Registre de la base de dades que conté les dades d'una matrícula.
	* @var object
	*/    
    private $Registre = null;

	/**
	 * Constructor de l'objecte.
	 * El registre queda carregat inicialment amb l'usuari de l'aplicació.
	 * Si es vol canviar d'usuari, cal usar el mètode Carrega.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 */
	function __construct($con, $user) {
		$this->Connexio = $con;
		$this->Usuari = $user;
		$this->Registre = $user;
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
	 * Comprova si l'usuari és alumne.
	 * @returns boolean Cert si l'usuari és alumne.
	 */
	function EsAlumne(): bool {
		return $this->Usuari->es_alumne == '1';
	}	

	/**
	 * Comprova si l'usuari és pare/mare d'un alumne.
	 * @param $alumne Identificador de l'alumne.
	 * @returns boolean Cert si l'usuari és pare/mare de l'alumne.
	 */
	function EsProgenitor(int $alumne): bool {
		$SQL = ' SELECT * FROM USUARI WHERE usuari_id='.$alumne.' AND (pare_id='.$this->Usuari->usuari_id.' OR mare_id='.$this->Usuari->usuari_id.')';
		$ResultSet = $this->Connexio->query($SQL);
		return ($ResultSet->num_rows > 0);
	}	

	/**
	 * Carrega les dades d'un usuari i les emmagatzema en l'atribut Registre.
     * @param int $UsuariId Identificador de l'usuari.
	 */
	public function Carrega(int $UsuariId) {
		$SQL = " SELECT * FROM USUARI WHERE usuari_id=$UsuariId ";
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$rs = $ResultSet->fetch_object();
			$this->Registre = $rs;
		}
	}
	
	/**
	 * Genera i escriu el formulari de la fitxa.
	 * @param $UsuariId Identificador de l'usuari.
	 */
	public function EscriuFormulariFitxa(int $UsuariId) {
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Fitxa usuari';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Id = $UsuariId;
		// Carreguem les dades de l'usuari al registre
		$this->Carrega($UsuariId);
		
		$ProfessorSenseCarrecDirectiu = ($this->Usuari->es_professor) && (!$this->Usuari->es_direccio) && (!$this->Usuari->es_cap_estudis);
		$frm->NomesLectura = $ProfessorSenseCarrecDirectiu;

		$frm->AfegeixText('username', 'Usuari', 100, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nom', 'Nom', 100, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('cognom1', '1r cognom', 100, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('cognom2', '2n cognom', 100, [FormFitxa::offAL_COSTAT]);

		if ($this->Registre->es_alumne) {
			$frm->Pestanya('Fotografia');
			$frm->AfegeixFotografia('document', '.jpg');
		}

		$frm->Pestanya('Dades');
		$frm->AfegeixText('codi', 'Codi (codi professor, IDALU per alumne)', 100);
		$frm->AfegeixLlista('sexe', 'Sexe', 30, array('H', 'D', 'N'), array('Home', 'Dona', 'Neutre'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixLlista('tipus_document', 'Tipus document', 30, array('D', 'N', 'P'), array('Dni', 'Nie', 'Passaport'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('document', 'Document', 100, [FormFitxa::offAL_COSTAT]);

		$frm->AfegeixData('data_naixement', 'Data naixement');
		$frm->AfegeixCalculat(Form::tccEDAT, 'data_naixement', 'Edat', 100, [FormFitxa::offAL_COSTAT]);
		$frm->AfegeixText('municipi_naixement', 'Municipi naixement', 100);
		$frm->AfegeixText('nacionalitat', 'Nacionalitat', 100);
		$frm->AfegeixText('email', 'Correu electrònic', 100);

		//$frm->AfegeixPassword('password', 'Contrasenya', 100, [FormFitxa::offREQUERIT]);
		if (!$Usuari->es_professor) {
			$frm->AfegeixCheckBox('imposa_canvi_password', 'Imposa nova contrasenya?');
			$frm->AfegeixCheckBox('usuari_bloquejat', "Bloqueja l'usuari?");
		}

		$frm->Pestanya('Contacte');
		$frm->AfegeixText('telefon', 'Telèfons', 100);
		$frm->AfegeixText('adreca', 'Adreça', 100);
		$frm->AfegeixText('codi_postal', 'Codi postal', 100);
		$frm->AfegeixText('poblacio', 'Població', 100);
		$frm->AfegeixText('municipi', 'Municipi', 100);
		$frm->AfegeixText('provincia', 'Província', 100);
		$frm->AfegeixCheckBox('permet_tutor', "Permet tutor? (vàlid pels >=18 anys)");

		if ($this->Registre->es_alumne) {
			$frm->Pestanya('Pares');
			$frm->AfegeixLookUp('pare_id', 'Pare', 100, 'UsuariRecerca.php?accio=Pares', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
			$frm->AfegeixLookUp('mare_id', 'Mare', 100, 'UsuariRecerca.php?accio=Pares', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
		}

		if (!$this->Usuari->es_professor) {
			$frm->Pestanya('Rols');
			//$frm->IniciaColumnes();
			$frm->AfegeixCheckBox('es_direccio', 'És direcció?');
			$frm->AfegeixCheckBox('es_cap_estudis', "És cap d'estudis?", [FormFitxa::offAL_COSTAT]);
			$frm->AfegeixCheckBox('es_cap_departament', "És cap de departament?", [FormFitxa::offAL_COSTAT]);
			//$frm->SaltaColumna();
			$frm->AfegeixCheckBox('es_tutor', "És tutor?");
			$frm->AfegeixCheckBox('es_professor', "És professor?", [FormFitxa::offAL_COSTAT]);
			$frm->AfegeixCheckBox('es_alumne', "És alumne?", [FormFitxa::offAL_COSTAT]);
			//$frm->SaltaColumna();
			$frm->AfegeixCheckBox('es_pare', "És pare?");
			//$frm->FinalitzaColumnes();

		}
		if ($this->Registre->es_alumne) {
			$frm->Pestanya('Expedient');
			$frm->AfegeixHTML($this->Matricules($UsuariId), 'Matrícules');
		}
		$frm->EscriuHTML();
	}
	
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

/**
 * Classe que encapsula les utilitats per al maneig dels alumnes.
 */
class Alumne extends Usuari
{
	
}

?>