<?php

/** 
 * LibUsuari.php
 *
 * Llibreria d'utilitats per a l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibHTML.php');

/**
 * Classe que encapsula les utilitats per al maneig de l'usuari.
 */
class Usuari extends Objecte
{
	/**
	 * Constructor de l'objecte.
	 * El registre queda carregat inicialment amb l'usuari de l'aplicació.
	 * Si es vol canviar d'usuari, cal usar el mètode Carrega.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 * @param objecte $system Dades de l'aplicació.
	 */
	function __construct($con = null, $user = null, $system = null) {
		parent::__construct($con, $user, $system);
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
	 * Comprova si l'usuari és professor.
	 * @returns boolean Cert si l'usuari és professor.
	 */
	function EsProfessor(): bool {
		return $this->Usuari->es_professor == '1';
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
		$SQL = ' SELECT * FROM USUARI WHERE usuari_id=? AND (pare_id=? OR mare_id=?)';
//		$ResultSet = $this->Connexio->query($SQL);
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("iii", $alumne, $this->Usuari->usuari_id, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
		return ($ResultSet->num_rows > 0);
	}	

	/**
	 * Carrega les dades d'un usuari i les emmagatzema en l'atribut Registre.
     * @param int $UsuariId Identificador de l'usuari.
	 */
	public function Carrega(int $UsuariId) {
		$SQL = " SELECT * FROM USUARI WHERE usuari_id=? ";
//		$ResultSet = $this->Connexio->query($SQL);
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("i", $UsuariId);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
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

		if ($this->Usuari->es_admin) {
			$frm->AfegeixText('usuari_id', 'Id', 20, [FormFitxa::offNOMES_LECTURA]);
			$frm->AfegeixText('data_creacio', 'Data creació', 50, array(FormFitxa::offNOMES_LECTURA, FormFitxa::offAL_COSTAT));
		}
			
		$frm->AfegeixText('username', 'Usuari', 100, [FormFitxa::offREQUERIT]);
		if ($this->Usuari->es_admin) 
			$frm->AfegeixText('data_modificacio', 'Última modificació', 50, array(FormFitxa::offNOMES_LECTURA, FormFitxa::offAL_COSTAT));
		$frm->AfegeixText('nom', 'Nom', 100, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('cognom1', '1r cognom', 100, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('cognom2', '2n cognom', 100, [FormFitxa::offAL_COSTAT]);

		if ($this->Registre->es_alumne && $UsuariId > 0) {
			$frm->Pestanya('Fotografia');
			$frm->AfegeixFotografia('document', '', '.jpg');
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
		$frm->AfegeixText('email_ins', 'Correu electrònic INS', 100);

		//$frm->AfegeixPassword('password', 'Contrasenya', 100, [FormFitxa::offREQUERIT]);
		if (!$this->EsProfessor()) {
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
			$frm->AfegeixEnllac('pare_id', 'Visualitza fitxa', 'UsuariFitxa.php?Id=', [FormFitxa::offAL_COSTAT]);
			$frm->AfegeixLookUp('mare_id', 'Mare', 100, 'UsuariRecerca.php?accio=Pares', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
			$frm->AfegeixEnllac('mare_id', 'Visualitza fitxa', 'UsuariFitxa.php?Id=', [FormFitxa::offAL_COSTAT]);
		}

		if ($this->Registre->es_professor) {
			$frm->Pestanya('Perfil');
			$frm->AfegeixText('titol_angles', 'Títol anglès ', 25, [], 5);
			$frm->AfegeixCheckBox('perfil_aicle', "Perfil AICLE");
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
			$frm->AfegeixCheckBox('es_administratiu', "És administratiu?", [FormFitxa::offAL_COSTAT]);
			//$frm->SaltaColumna();
			$frm->AfegeixCheckBox('es_alumne', "És alumne?");
			$frm->AfegeixCheckBox('es_pare', "És pare?", [FormFitxa::offAL_COSTAT]);
			$frm->AfegeixCheckBox('es_auditor', "És auditor?", [FormFitxa::offAL_COSTAT]);
			//$frm->FinalitzaColumnes();

		}
		if ($this->Registre->es_alumne) {
			$frm->Pestanya('Expedient');
			$frm->AfegeixHTML($this->Matricules($UsuariId), 'Matrícules');
			$frm->AfegeixCheckBox('inscripcio_borsa_treball', "Inscripció borsa treball");
		}
		if ($this->Registre->es_pare) {
			$frm->Pestanya('Fills');
			$frm->AfegeixHTML($this->MatriculesFills($UsuariId), 'Matrícules');
		}
		if ($this->Usuari->es_admin) {
			$frm->Pestanya('Registre');
			$frm->AfegeixHTML($this->RegistreIP($UsuariId), 'IP');
			//$frm->AfegeixHTML($this->MatriculesFills($UsuariId), 'Matrícules');
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
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.			
			' WHERE alumne_id=? AND AA.any_academic_id=? ';
//		$ResultSet = $this->Connexio->query($SQL);
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $AlumneId, $this->Sistema->any_academic_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
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
				$Retorn .= "<A TARGET=_blank HREF='$URL'>".utf8_encodeX($rsMatricula->nom)."</A><br>";
			}
			else
				$Retorn .= utf8_encodeX($rsMatricula->nom).'<br>';
		}		
		return $Retorn.'<br>';;
	}	

	/**
	 * Llista les matrícules dels fills d'un progenitor.
	 * @param $progenitor Identificador del progenitor
	 * @return string Codi HTML de la llista de matrícules.
	 */
	function MatriculesFills(int $progenitor): string {
		$Retorn = '';
		$SQL = " SELECT M.matricula_id, C.nom AS NomCurs, FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomCognom1Cognom2, Edat(data_naixement) AS Edat ".
			" FROM USUARI U ".
			" LEFT JOIN MATRICULA M ON (U.usuari_id=M.alumne_id) ".
			" LEFT JOIN CURS C ON (C.curs_id=M.curs_id) ".
			" WHERE pare_id=$progenitor OR mare_id=$progenitor ".
			" ORDER BY Edat DESC, usuari_id, matricula_id ";
//print_r($SQL);
//exit;
		$NomCognom1Cognom2Anterior = '';
		$ResultSet = $this->Connexio->query($SQL);
		while ($rs = $ResultSet->fetch_object()) {
			$NomCognom1Cognom2 = $rs->NomCognom1Cognom2;
			if ($NomCognom1Cognom2 != $NomCognom1Cognom2Anterior) {
				$Retorn .= $NomCognom1Cognom2." (".$rs->Edat." anys)<br>";
				$NomCognom1Cognom2Anterior = $NomCognom1Cognom2;
			}
			$URL = '';
			if ($this->EsAdmin() || $this->EsDireccio() || $this->EsCapEstudis()) {
				$URL = 'MatriculaAlumne.php?accio=MostraExpedient&MatriculaId='.$rs->matricula_id;
				if (Config::EncriptaURL)
					$URL = GeneraURL($URL);
				$Retorn .= "- <A TARGET=_blank HREF='$URL'>".utf8_encodeX($rs->NomCurs)."</A><br>";
			}
			else
				$Retorn .= utf8_encodeX($rs->NomCurs).'<br>';
		}		
		return $Retorn.'<br>';;
	}	

	/**
	 * Llista les IP des de les que s'ha connectat l'usuari.
	 * @param $UsuariId Identificador de l'usuari.
	 * @return string Codi HTML de les IP des de les que s'ha connectat.
	 */
	function RegistreIP(int $UsuariId): string {
		$Retorn = '';
		$SQL = " SELECT DISTINCT(ip) FROM REGISTRE WHERE usuari_id=$UsuariId ORDER BY INET_ATON(ip) ";
		$ResultSet = $this->Connexio->query($SQL);
		while ($rs = $ResultSet->fetch_object()) {
			$Retorn .= $rs->ip.'<br>';
		}	
		if ($Retorn == '')	
			$Retorn = 'No hi ha dades.<br>';			
		return $Retorn.'<br>';
	}	
}


/**
 * Classe que encapsula les utilitats per al maneig del professor.
 */
class Professor extends Usuari
{
	/**
	* UF Assignades.
	* @var array
	*/    
	public $UFAssignades = [];

	/**
	* És tutor.
	* @var boolean
	*/    
	public $Tutor = False;

	/**
	 * Carrega les UF assignades en un array.
	 */
	function CarregaUFAssignades() {
		$UFAssignades = [];
		$SQL = ' SELECT '.
			' CPE.cicle_formatiu_id, CPE.codi AS CodiCF, CPE.nom AS NomCF, '.
			' MPE.modul_pla_estudi_id, MPE.modul_professional_id, MPE.codi AS CodiMP, MPE.nom AS NomMP, '.
			' UPE.unitat_pla_estudi_id, UPE.unitat_formativa_id, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.nivell '.
			' FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=PUF.uf_id) '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE AA.any_academic_id=? '.
			' AND professor_id=? '.
			' ORDER BY CPE.codi, UPE.nivell ';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Sistema->any_academic_id, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$i = 0;
			while ($obj = $ResultSet->fetch_object()) {
				$this->UFAssignades[$i] = $obj;
				$i++;
			}
		}
		$ResultSet->close();
//print_h($this->UFAssignades);
//exit;
	}

	/**
	 * Comprova si té alguna UF en un determinat cicle.
	 * @param integer $Cicle Identificador del cicle.
	 * @returns boolean Cert si té alguna UF en un determinat cicle.
	 */
	function TeUFEnCicle(int $Cicle): bool {
//print('Cicle: '.$Cicle);
//print('Nivell: '.$Nivell);
//print_h($this->UFAssignades);
//exit;
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades); $i++) {
			if ($this->UFAssignades[$i]->cicle_formatiu_id == $Cicle)
				$bRetorn = True;
		}
		return $bRetorn;
	}

	/**
	 * Comprova si té alguna UF en un determinat cicle i nivell.
	 * @param integer $Cicle Identificador del cicle.
	 * @param integer $Nivell Nivell del cicle.
	 * @returns boolean Cert si té alguna UF en un determinat cicle i nivell.
	 */
	function TeUFEnCicleNivell(int $Cicle, int $Nivell): bool {
//print('Cicle: '.$Cicle);
//print('Nivell: '.$Nivell);
//print_h($this->UFAssignades);
//exit;
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades); $i++) {
			if (($this->UFAssignades[$i]->cicle_formatiu_id == $Cicle) && ($this->UFAssignades[$i]->nivell == $Nivell))
				$bRetorn = True;
		}
		return $bRetorn;
	}

	/**
	 * Comprova si té alguna UF en un determinat curs.
	 * @param integer $CursId Identificador del curs.
	 * @returns boolean Cert si té alguna UF en un determinat curs.
	 */
	function TeUFEnCurs(int $CursId): bool {
		$SQL = ' SELECT COUNT(*) AS UF '.
			' FROM CURS C '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
			' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id) '.
			' WHERE professor_id=? AND curs_id=? 
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Usuari->usuari_id, $CursId);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		$obj = $ResultSet->fetch_object();
		return ($obj->UF > 0);
	}

	/**
	 * Comprova si té alguna UF en un determinat curs (donada una matrícula).
	 * @param integer $MatriculaId Identificador de la matrícula.
	 * @returns boolean Cert si té alguna UF en un determinat curs.
	 */
	function TeUFEnMatricula(int $MatriculaId): bool {
		$SQL = ' SELECT COUNT(*) AS UF '.
			' FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
			' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id) '.
			' WHERE professor_id=? AND matricula_id=? 
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Usuari->usuari_id, $MatriculaId);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		$obj = $ResultSet->fetch_object();
		return ($obj->UF > 0);
	}
	
	/**
	 * Comprova si té assignada una UF.
	 * @param integer $UF Identificador de la UF del pla d'estudis.
	 * @returns boolean Cert si té assignada la UF.
	 */
	function TeUF(int $UF): bool {
//print('Id UF: '.$UF);
//print_h($this->UFAssignades);
//exit;
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades); $i++) {
//			if ($this->UFAssignades[$i]->unitat_formativa_id == $UF) 
			if ($this->UFAssignades[$i]->unitat_pla_estudi_id == $UF) 
				$bRetorn = True;
		}
		return $bRetorn;
	}

	/**
	 * Comprova si té assignat un mòdul professional.
	 * @param integer $MP Identificador del MP.
	 * @returns boolean Cert si té assignat el MP.
	 */
	function TeMP(int $MP): bool {
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades) && !$bRetorn; $i++) {
//			if ($this->UFAssignades[$i]->modul_professional_id == $MP) 
			if ($this->UFAssignades[$i]->modul_pla_estudi_id == $MP) 
				$bRetorn = True;
		}
		return $bRetorn;
	}
	
	/**
	 * Comprova si el professor està a la comissió de qualitat.
	 * @returns boolean Cert si hi participa.
	 */
	function EstaAQualitat(): bool {
		$SQL = ' 
			SELECT COUNT(*) AS Professor
			FROM PROFESSOR_EQUIP PE
			LEFT JOIN EQUIP E ON (E.equip_id=PE.equip_id)
			WHERE professor_id=? AND tipus="CQ" AND any_academic_id=?
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Usuari->usuari_id, $this->Sistema->any_academic_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
		$obj = $ResultSet->fetch_object();
		return ($obj->Professor > 0);
	}
	
	/**
	 * Carrega si és tutor en un curs.
	 * @param integer $CursId Identificador del curs.
	 */
	function CarregaTutor(int $CursId) {
		$SQL = ' SELECT * FROM TUTOR WHERE professor_id=? AND curs_id=?	';
//		$ResultSet = $this->Connexio->query($SQL);
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Usuari->usuari_id, $CursId);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
		$bRetorn = ($ResultSet->num_rows > 0);
		$ResultSet->close();
		$this->Tutor = $bRetorn;
	}

	/**
	 * Obté el identificador del curs del qual un professor és tutor.
	 * @returns int Identificador del curs, -1 si no és tutor.
	 */
	function ObteCursTutorId(): int {
		$iRetorn = -1;
		$SQL = ' SELECT * FROM TUTOR T '.
			' LEFT JOIN CURS C ON (C.curs_id=T.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE AA.any_academic_id=? AND professor_id=? 
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Sistema->any_academic_id, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$obj = $ResultSet->fetch_object();
			$iRetorn = $obj->curs_id;
		}
		$ResultSet->close();
		return $iRetorn;
	}

	/**
	 * Obté el codi dels cicles que imparteix.
	 * @returns array Codi dels cicles que imparteix.
	 */
	function ObteCodiCicles(): array {
		$aRetorn = Array();
		$SQL = ' 
			SELECT DISTINCT CPE.codi 
			FROM PROFESSOR_UF PUF
			LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			WHERE PUF.professor_id=? 
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("i", $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			while ($obj = $ResultSet->fetch_object()) {
				array_push($aRetorn, $obj->codi);
			}
		}
		$ResultSet->close();
		return $aRetorn;
	}	
	
	/**
	 * Comprova si és tutor d'un alumne.
	 * @param integer $AlumneId Identificador de l'alumne.
	 * @returns boolean Cert si és tutor de l'alumne.
	 */
	function EsTutorAlumne(int $AlumneId): bool {
		$bRetorn = False;
		$SQL = ' SELECT * FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (M.curs_id=C.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' LEFT JOIN TUTOR TUT ON (C.curs_id=TUT.curs_id) '.
			' WHERE AA.any_academic_id=? '.
			' AND alumne_id=? AND professor_id=? 
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("iii", $this->Sistema->any_academic_id, $AlumneId, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		$bRetorn = ($ResultSet->num_rows > 0);
		$ResultSet->close();
		return $bRetorn;
	}

	/**
	 * Comprova si és tutor d'un pare d'alumne.
	 * @param integer $PareId Identificador del pare o de la mare.
	 * @returns boolean Cert si és tutor del pare de l'alumne.
	 */
	function EsTutorPare(int $PareId): bool {
		$bRetorn = False;
		$SQL = ' SELECT * FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (M.curs_id=C.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' LEFT JOIN TUTOR TUT ON (C.curs_id=TUT.curs_id) '.
			' WHERE AA.any_academic_id=? '.
			' AND alumne_id IN ('.
			" 	SELECT usuari_id FROM USUARI WHERE pare_id=? OR mare_id=? ".
			' ) '.
			' AND professor_id=? 
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("iiii", $this->Sistema->any_academic_id, $PareId, $PareId, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		$bRetorn = ($ResultSet->num_rows > 0);
		$ResultSet->close();
		return $bRetorn;
	}

	/**
	 * Comprova si és cap de departament.
	 * @param integer $ProfessorId Identificador del professor.
	 * @returns integer Identificador de la família de la qual és cap de departament (sinó -1).
	 */
	function EsCapDepartament(int $ProfessorId): int {
		$Retorn = -1;
		$SQL = ' 
			SELECT * FROM EQUIP E
			LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=E.any_academic_id) 
			WHERE AA.any_academic_id=? AND tipus="DP" AND cap=?
		';
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Sistema->any_academic_id, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();

		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_object();
			if (is_null($row->familia_fp_id)) die("CONFIG. Falta especificar la família per al cap de departament.");
			$Retorn = $row->familia_fp_id;
		}
		$ResultSet->close();
		return $Retorn;
	}

	/**
	 * Comprova si és el gestor de la borsa de treball.
	 * @return boolean Cert si és el gestor de la borsa de treball.
	 */
	public function EsGestorBorsa(): bool {
		if ($this->Usuari === null)
			return false;

		$stmt = $this->Connexio->prepare("SELECT u.usuari_id FROM usuari u INNER JOIN sistema s ON u.usuari_id = s.gestor_borsa_treball_id;");
		$stmt->execute();
		$rs = $stmt->get_result();
		$stmt->close();
		if ($rs->num_rows > 0) {
			$row = $rs->fetch_assoc();
			return $row['usuari_id'] === $this->Usuari->usuari_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Genera i escriu l'escriptori del professor.
	 */
	public function Escriptori() {
		CreaIniciHTML($this->Usuari, '');

		$SQL = ' SELECT DISTINCT CPE.cicle_formatiu_id, UPE.nivell, CPE.codi AS CodiCF, CPE.nom AS NomCF, C.curs_id, C.estat, C.grups_tutoria '.
			' FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (PUF.uf_id=UPE.unitat_pla_estudi_id) '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id AND UPE.nivell=C.nivell) '.
			' WHERE C.estat<>"T" AND AA.any_academic_id=? AND professor_id=? '.
			' ORDER BY CPE.codi, UPE.nivell ';

		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Sistema->any_academic_id, $this->Usuari->usuari_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();

		echo '<h3>Cursos</h3>';
		echo '<div class="row">';
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			while($row) {
				if ($row['estat'] == Curs::Junta) {
					$GrupsTutoria = $row['grups_tutoria'];
					if ($GrupsTutoria == '') {
						// Una sola línia
						$URL = GeneraURL('Fitxa.php?accio=ExpedientSagaAvaluacio&Id='.$row['curs_id']);
						echo CreaTargeta($row['CodiCF'].$row['nivell'], $row['NomCF'], $URL);
					}
					else {
						// Vàries línies
						$aGrupsTutoria = explode(',', $GrupsTutoria);
						foreach($aGrupsTutoria as $Grup) {
							$URL = GeneraURL('Fitxa.php?accio=ExpedientSagaAvaluacio&Id='.$row['curs_id'].','.$Grup);
							echo CreaTargeta($row['CodiCF'].$row['nivell'].' '.$Grup, utf8_encodeX($row['NomCF']), $URL);
						}
					}
				}
				else {
					$URL = GeneraURL('Notes.php?CursId='.$row['curs_id']);
					echo CreaTargeta($row['CodiCF'].$row['nivell'], $row['NomCF'], $URL);
				}
				$row = $ResultSet->fetch_assoc();
			}
		}
		$ResultSet->close();

		if ($this->EstaAQualitat()) {
			$URL = GeneraURL('Recerca.php?accio=Document');
			echo CreaTargeta('Qualitat', 'Documents', $URL);
		}

		echo '</div>';
		echo '<h3>Gestió</h3>';
		echo '<div class="row">';
		//		echo '<div class="card-columns" style="column-count:6">';
		
		// Grups tutoria
		$Professor = new Professor($this->Connexio, $this->Usuari);
		$CursId = $this->ObteCursTutorId();
		if ($CursId > 0) {
			$URL1 = GeneraURL('Grups.php?CursId='.$CursId);
			$URL2 = GeneraURL('UsuariRecerca.php?accio=UltimLogin');
			echo CreaTargeta2('Tutoria', 'Grups', $URL1, 'Darrers accessos', $URL2);
		}
		
		// Les meves UF
		$URL = GeneraURL('FPRecerca.php?accio=PlaEstudisUnitat&ProfId='.$this->Usuari->usuari_id);
		echo CreaTargeta('Unitats formatives', 'Les meves UF', $URL);

		// Els meus mòduls
		$URL = GeneraURL('FPRecerca.php?accio=PlaEstudisModul&ProfId='.$this->Usuari->usuari_id);
		echo CreaTargeta('Mòduls professionals', 'Programacions', $URL);

		// Cap de departament
		$FamiliaFPId = $this->EsCapDepartament($this->Usuari->usuari_id);
		if ($FamiliaFPId != -1) {
			$URL = GeneraURL("FPRecerca.php?accio=PlaEstudisModul&FamiliaFPId=$FamiliaFPId&ProfId=".$this->Usuari->usuari_id);
			echo CreaTargeta('Departament', 'Revisió programacions', $URL);
		}

		// Programacions
		$URL = GeneraURL('FPRecerca.php?accio=PlaEstudisModul&MostraTot=1&ProfId='.$this->Usuari->usuari_id);
		echo CreaTargeta('Programacions', 'Totes les programacions', $URL);

		// Pedaç temporal
		if (str_starts_with($this->Usuari->codi, 'SMX')) {
			// Material
			$URL = GeneraURL('Recerca.php?accio=Material');
			echo CreaTargeta('Material', 'Inventari', $URL);
		}
		
		// Cap de departament
		if ($FamiliaFPId != -1) {
			$URL1 = GeneraURL('AssignaUFs.php?accio=GrupAssignaUF');
			$URL2 = GeneraURL('AssignaUFs.php?accio=ProfessorsUF');
			echo CreaTargeta2('Professorat', 'Assignació UF', $URL1, 'Professors UF', $URL2);
		}

		echo '</div>';
		echo '<h3>Informes</h3>';
		echo '<div class="row">';

		// Històric
		$URL = GeneraURL('Recerca.php?accio=HistoricCurs');
		echo CreaTargeta('Històric', 'Notes FP', $URL);

		// Promoció 1r
		$URL = GeneraURL('UsuariRecerca.php?accio=AlumnesPromocio1r');
		echo CreaTargeta('Promocions 1r', "Alumnes amb 60% d'hores o més", $URL);
		
		// Graduació 2n
		$URL = GeneraURL('UsuariRecerca.php?accio=AlumnesGraduacio2n');
		echo CreaTargeta('Graduacions 2n', "Alumnes amb 100% d'hores", $URL);

		// Estadístiques FP
		$URL = GeneraURL('Estadistiques.php?accio=EstadistiquesNotes');
		echo CreaTargeta('Estadístiques', "Aprovats per UF", $URL);
		
		echo '</div>';
		echo '<h3>Altres</h3>';
		echo '<div class="row">';

		// Orla
		$URL = GeneraURL('UsuariRecerca.php?accio=Orla');
		echo CreaTargeta('Orla', "Orla alumnes", $URL);
		
		if ($this->Usuari->usuari_id == $this->Sistema->gestor_borsa_treball_id) {
			// Borsa treball
			$URL = GeneraURL('BorsaTreball.php');
			echo CreaTargeta('Borsa de treball', "Borsa alumnes", $URL);
		}
	}
}

/**
 * Formulari que mostra els professors per UF.
 */
class ProfessorsUF extends Form
{
	/**
	* Identificador de l'any acadèmic.
	* @var integer
	*/    
    public $AnyAcademicId = -1; 

	/**
	 * Indica si l'usuari és cap de departament.
	 * @var boolean
	 */
    private $EsCapDepartament = false;

	/**
	 * Identificador de la família de FP a la que pertany l'usuari (professor).
	 * @var int
	 */
    private $FamiliaId = -1;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 * @param objecte $system Dades de l'aplicació.
	 */
	function __construct($con = null, $user = null, $system = null) {
		parent::__construct($con, $user, $system);
		$Professor = new Professor($con, $user, $system);
		$this->FamiliaId = $Professor->EsCapDepartament($user->usuari_id);
		$this->EsCapDepartament = $this->FamiliaId > 0;
	}	

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, 'Professors per unitats formatives');
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Professor.js?v1.1" type="text/javascript"></script>';
		echo $this->GeneraFiltre();
		echo '<P>';
		echo $this->GeneraAcordio();
		CreaFinalHTML();
	}	

	/**
	 * Genera el filtre del formulari si n'hi ha.
     * @return string Codi HTML del filtre.
	 */
	protected function GeneraFiltre() {
		$Retorn = '';
		if ($this->EsCapDepartament) {
			$this->AnyAcademicId = $this->Sistema->any_academic_id;
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC WHERE any_academic_id='.$this->AnyAcademicId.' ORDER BY Any DESC', "any_academic_id", "Any");
			$Retorn .= $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], [], $this->AnyAcademicId, 'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');
		}
		else {
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
			$this->AnyAcademicId = $this->Sistema->any_academic_id;
			$Retorn .= $this->CreaLlista('any_academic_id', 'Any', 200, $aAnys[0], $aAnys[1], [], $this->AnyAcademicId, 'onchange="ActualitzaTaulaProfessorsUF(this);"');
		}
		return $Retorn;
	}

	/**
	 * Genera una acordió (component Bootstrap) amb el resultat de la SQL.
     * @return string Acordió amb les dades.
	 */
	public function GeneraAcordio() {
		
		$sRetorn = '';
		$SQL = $this->CreaSQL($this->AnyAcademicId);
//print $SQL;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			// Creem un objecte per administrar els cicles
			$Cicles = new stdClass();
			$i = -1; 
			$j = 0;
			$CiclePlaEstudiId = -1;
			$UnitatFormativaId = -1;
			$row = $ResultSet->fetch_assoc();
			while($row) {
				if ($row["cicle_pla_estudi_id"] != $CiclePlaEstudiId) {
					$CiclePlaEstudiId = $row["cicle_pla_estudi_id"];
					$i++;
					$Cicles->CF[$i] = $row;
					$j = 0; 
				}
				if ($row["UnitatFormativaId"] == $UnitatFormativaId) {
					$Cicles->UF[$i][$j-1]->NomComplet .= utf8_encodeX(', '.$row['Nom'].' '.$row['Cognom1'].' '.$row['Cognom2']);
				}
				else {
					$UnitatFormativaId = $row['UnitatFormativaId'];
					$Cicles->UF[$i][$j] = new stdClass();
					$Cicles->UF[$i][$j]->Dades = $row;
					$Cicles->UF[$i][$j]->NomComplet = utf8_encodeX($row['Nom'].' '.$row['Cognom1'].' '.$row['Cognom2']);
					$j++;
				}
				$row = $ResultSet->fetch_assoc();
			}	
			
			$sRetorn .= '<DIV id=taula>';
			// Creem una llista dels cicles formatius amb les UFs amb col·lapsament.
			// https://getbootstrap.com/docs/4.1/components/collapse/
			$sRetorn .= '<div class="accordion" id="accordionExample">';

			for($i = 0; $i < count($Cicles->CF); $i++) {
				$row = $Cicles->CF[$i];
				$CodiCF = $row['CodiCF'].$row['cicle_pla_estudi_id'];
				$sRetorn .= '  <div class="card">';
				$sRetorn .= '    <div class="card-header" id="'.$CodiCF.'">';
				$sRetorn .= '      <h5 class="mb-0">';
				$sRetorn .= '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$CodiCF.'" aria-expanded="true" aria-controls="collapse'.$CodiCF.'">';
				$sRetorn .= utf8_encodeX($row['NomCF']);
				$sRetorn .= '        </button>';
				$sRetorn .= '      </h5>';
				$sRetorn .= '    </div>';
				$sRetorn .= '    <div id="collapse'.$CodiCF.'" class="collapse" aria-labelledby="'.$CodiCF.'" data-parent="#accordionExample">';
				$sRetorn .= '      <div class="card-body">';

				$sRetorn .= '<TABLE class="table table-striped table-sm table-hover">';
				$sRetorn .= '<thead class="thead-dark">';
				$sRetorn .= "<TH>Mòdul</TH>";
				$sRetorn .= "<TH>Unitat formativa</TH>"; 
				$sRetorn .= "<TH>Professors</TH>"; 
				$sRetorn .= '</thead>';
				$ModulAnterior = '';
				for($j = 0; $j < count($Cicles->UF[$i]); $j++) {
					$NomComplet = $Cicles->UF[$i][$j]->NomComplet;
	//print_r($NomComplet);
					$row = $Cicles->UF[$i][$j]->Dades;
					$sRetorn .= "<TR>";
					if ($row["CodiMP"] != $ModulAnterior)
						$sRetorn .= "<TD>".utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
					else 
						$sRetorn .= "<TD></TD>";
					$ModulAnterior = $row["CodiMP"];
					$sRetorn .= "<TD>".utf8_encodeX($row["NomUF"]).' ('.Ordinal($row["nivell"]).')'."</TD>";
					$sRetorn .= "<TD>".$NomComplet."</TD>";
					$sRetorn .= "</TR>";
				}
				$sRetorn .= "</TABLE>";
				$sRetorn .= '      </div>';
				$sRetorn .= '    </div>';
				$sRetorn .= '  </div>';		
			}
			$sRetorn .= '</div>';	
			$sRetorn .= '</DIV>';	
		};
		return $sRetorn;	
	}

	/**
	 * Crea la sentència SQL.
	 * @param integer $AnyAcademicId Identificador de l'any acadèmic.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $AnyAcademicId): string {
		$SQL = "
			SELECT
				UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.unitat_formativa_id AS UnitatFormativaId, 
				MPE.codi AS CodiMP, MPE.nom AS NomMP, 
				CPE.nom AS NomCF, CPE.cicle_formatiu_id AS CicleFormatiuId, CPE.codi AS CodiCF, 
				U.nom AS Nom, U.cognom1 AS Cognom1, U.cognom2 AS Cognom2, 
				PUF.professor_uf_id AS ProfessorUFId, 
				UPE.*, MPE.*, CPE.* 
			FROM UNITAT_PLA_ESTUDI UPE 
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) 
			LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PUF.professor_id) 
			WHERE any_academic_id=$AnyAcademicId
		";
		if ($this->EsCapDepartament)
			$SQL .= " AND CF.familia_fp_id=".$this->FamiliaId;
		
		$SQL .= " ORDER BY CPE.codi, CPE.cicle_pla_estudi_id, MPE.codi, UPE.codi, U.cognom1, U.cognom2, U.nom ";
		return $SQL;
	}
}

/**
 *  * Classe que encapsula les utilitats per al maneig dels professors per mòdul.
 */
class ProfessorsMP extends Objecte
{
	/**
	* Identificador del curs.
	* @var integer
	*/    
    public $CursId = -1; 

	/**
	* Array .
	* @var array de mòduls i professors d'un curs.
	*/    
    public $Moduls = []; 
	
	/**
	 * Obté els mòduls i professors d'un curs.
	 * @param integer $CursId Identificador del curs.
	 * @return array Array de mòduls i professors d'un curs.
	 */
	public function ObteProfessorsMP(int $CursId) {
		$this->Carrega($CursId);
		return $this->Moduls;
	}
	
	/**
	 * Carrega els mòduls i professors d'un curs en un array.
	 * @param integer $CursId Identificador del curs.
	 */
	private function Carrega(int $CursId) {
		$this->Moduls = []; 
		$SQL = $this->CreaSQL($CursId);
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			while ($obj = $ResultSet->fetch_object())
				array_push($this->Moduls, $obj);
		}
		$ResultSet->close();
	}

	/**
	 * Crea la sentència SQL que retorna els mòduls i professors d'un curs.
	 * @param integer $CursId Identificador del curs.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $CursId): string {
		return "
			SELECT DISTINCT
				MPE.codi AS CodiMP, MPE.nom AS NomMP, 
				UPE.nivell AS Nivell, 
				FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomCognom1Cognom2,
				U.nom AS Nom, U.cognom1 AS Cognom1, U.cognom2 AS Cognom2
			FROM UNITAT_PLA_ESTUDI UPE 
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) 
			LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PUF.professor_id) 
			WHERE curs_id=$CursId
			ORDER BY MPE.codi, U.cognom1, U.cognom2, U.nom
		";
	}
}
/**
 * Formulari que mostra l'assignacio de professors per UF.
 */
class ProfessorsAssignacioUF extends Form
{
	/**
	* Identificador del professor.
	* @var integer
	*/    
    public $ProfessorId = -1; 
	
	/**
	* Identificador de l'any acadèmic.
	* @var integer
	*/    
    public $AnyAcademicId = -1; 

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, "Assignació d'unitats formatives");
		echo '<script language="javascript" src="js/Forms.js?v1.1" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Professor.js?v1.2" type="text/javascript"></script>';
		echo $this->GeneraFiltre();
		echo '<P>';
		echo $this->GeneraAcordio();
		CreaFinalHTML();
	}	

	/**
	 * Genera el filtre del formulari si n'hi ha.
	 */
	protected function GeneraFiltre() {
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
//		$this->AnyAcademicId = $aAnys[0][0]; 
		$this->AnyAcademicId = $this->Sistema->any_academic_id;
		return $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], [], $this->AnyAcademicId, 'onchange="ActualitzaTaulaProfessorsAssignacioUF(this);"');
	}

	/**
	 * Genera una acordió (component Bootstrap) amb el resultat de la SQL.
     * @return string Acordió amb les dades.
	 */
	public function GeneraAcordio() {
		$sRetorn = '';
		$SQL = $this->CreaSQL($this->ProfessorId, $this->AnyAcademicId);
//print $SQL;		
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$sRetorn .= '<DIV id=taula>';
			// Creem un objecte per administrar els cicles
			$Cicles = new stdClass();
			$i = -1; 
			$j = 0;
			$CiclePlaEstudiId = -1;
			$row = $ResultSet->fetch_assoc();
			$NomProfessor = utf8_encodeX($row["NomProfessor"]." ".$row["Cognom1Professor"]." ".$row["Cognom2Professor"]);
			while($row) {
				if ($row["cicle_pla_estudi_id"] != $CiclePlaEstudiId) {
					$CiclePlaEstudiId = $row["cicle_pla_estudi_id"];
					$i++;
					$Cicles->CF[$i] = $row;
					$j = 0; 
				}	
				$Cicles->UF[$i][$j] = $row;
				$j++;
				$row = $ResultSet->fetch_assoc();
			}	
			
			$sRetorn .= '<div class="alert alert-primary" role="alert">Professor: <B>'.$NomProfessor.'</B></div>';

			// Creem una llista dels cicles formatius amb les UFs amb col·lapsament.
			// https://getbootstrap.com/docs/4.1/components/collapse/
			$sRetorn .= '<div class="accordion" id="accordionExample">';

			for($i = 0; $i < count($Cicles->CF); $i++) {
				$row = $Cicles->CF[$i];
				$CodiCF = $row['CodiCF'].$row['cicle_pla_estudi_id'];
				$sRetorn .= '  <div class="card">';
				$sRetorn .= '    <div class="card-header" id="'.$CodiCF.'">';
				$sRetorn .= '      <h5 class="mb-0">';
				$sRetorn .= '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$CodiCF.'" aria-expanded="true" aria-controls="collapse'.$CodiCF.'">';
				$sRetorn .= utf8_encodeX($row['NomCF']);
				$sRetorn .= '        </button>';
				$sRetorn .= '      </h5>';
				$sRetorn .= '    </div>';
				$sRetorn .= '    <div id="collapse'.$CodiCF.'" class="collapse" aria-labelledby="'.$CodiCF.'" data-parent="#accordionExample">';
				$sRetorn .= '      <div class="card-body">';

				$sRetorn .= '<TABLE class="table table-striped table-sm table-hover">';
				$sRetorn .= '<thead class="thead-dark">';
				$sRetorn .= "<TH>Mòdul</TH>";
				$sRetorn .= "<TH>Unitat formativa</TH>"; 
				$sRetorn .= "<TH></TH>"; 
				$sRetorn .= '</thead>';
				$ModulAnterior = '';
				for($j = 0; $j < count($Cicles->UF[$i]); $j++) {
					$row = $Cicles->UF[$i][$j];
					$sRetorn .= "<TR>";

					if ($row["CodiMP"] != $ModulAnterior)
						$sRetorn .= "<TD>".utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
					else 
						$sRetorn .= "<TD></TD>";
					$ModulAnterior = $row["CodiMP"];

					$sRetorn .= "<TD>".utf8_encodeX($row["NomUF"]).' ('.Ordinal($row["nivell"]).')';
					if ($this->Usuari->es_admin)
						$sRetorn .= " [".$row["unitat_pla_estudi_id"]."]";
					$sRetorn .= "</TD>";
					$Checked = ($row["ProfessorUFId"] > 0)? ' checked ' : '';
					$Nom = 'chbUFId_'.$row["unitat_pla_estudi_id"].'_'.$this->ProfessorId;
					$sRetorn .= "<TD><input type=checkbox name=".$Nom.$Checked." onclick='AssignaUF(this);'/></TD>";
					$sRetorn .= "</TR>";
				}
				$sRetorn .= "</TABLE>";
				$sRetorn .= '      </div>';
				$sRetorn .= '    </div>';
				$sRetorn .= '  </div>';		
			}
			$sRetorn .= '</div>';	
			$sRetorn .= '</DIV>';	
		};	
		$sRetorn .= "<input type=hidden id=hdn_professor_id value=".$this->ProfessorId.">";
		
		return $sRetorn;		
	}

	/**
	 * Crea la sentència SQL.
	 * @param integer $ProfessorId Identificador del professor.
	 * @param integer $AnyAcademicId Identificador de l'any acadèmic.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $ProfessorId, int $AnyAcademicId): string {
		return "
			SELECT 
				UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.unitat_formativa_id AS UnitatFormativaId, 
				MPE.codi AS CodiMP, MPE.nom AS NomMP, 
				CPE.nom AS NomCF, CPE.cicle_formatiu_id AS CicleFormatiuId, CPE.codi AS CodiCF, 
				U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, 
				PUF.professor_uf_id AS ProfessorUFId, 
				UPE.*, MPE.*, CPE.* 
			FROM UNITAT_PLA_ESTUDI UPE  
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id AND PUF.professor_id=$ProfessorId) 
			LEFT JOIN USUARI U ON (U.usuari_id=$ProfessorId) 
			WHERE any_academic_id=$AnyAcademicId
			ORDER BY CPE.codi, CPE.cicle_pla_estudi_id, MPE.codi, UPE.codi, U.cognom1, U.cognom2, U.nom
		";
	}
}

/**
 * Formulari que mostra l'assignacio d'un grup de professors per UF.
 */
class GrupProfessorsAssignacioUF extends ProfessorsAssignacioUF
{
	/**
	 * Identificador del cicle del pla d'estudi.
	 * @var integer
	 */    
    public $CiclePlaEstudiId = -1; 

	/**
	 * Array que emmagatzema les dades dels professors.
	 * @var array
	 */
    private $ProfessorUF = [];

	/**
	 * Array associatiu que emmagatzema els professors i les UF que fan.
	 * @var array
	 */
    private $ProfessorUFAssoc = [];

	/**
	 * Indica si l'usuari és cap de departament.
	 * @var boolean
	 */
    private $EsCapDepartament = false;

	/**
	 * Identificador de la família de FP a la que pertany l'usuari (professor).
	 * @var int
	 */
    private $FamiliaId = -1;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param object $user Usuari de l'aplicació.
	 * @param objecte $system Dades de l'aplicació.
	 */
	function __construct($con = null, $user = null, $system = null) {
		parent::__construct($con, $user, $system);
		$Professor = new Professor($con, $user, $system);
		$this->FamiliaId = $Professor->EsCapDepartament($user->usuari_id);
		$this->EsCapDepartament = $this->FamiliaId > 0;
	}	

	/**
	 * Genera el filtre del formulari si n'hi ha.
	 */
	protected function GeneraFiltre() {
		$Retorn = '';
		if ($this->EsCapDepartament) {
			$this->AnyAcademicId = $this->Sistema->any_academic_id;
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC WHERE any_academic_id='.$this->AnyAcademicId.' ORDER BY Any DESC', "any_academic_id", "Any");
			$Retorn .= $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], [], $this->AnyAcademicId, 'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');

			$SQL = '
				SELECT CPE.cicle_pla_estudi_id, CPE.nom 
				FROM CICLE_PLA_ESTUDI CPE 
				LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) 
				WHERE CPE.any_academic_id='.$this->AnyAcademicId.' AND CF.familia_fp_id='.$this->FamiliaId.' 
				ORDER BY nom
			';
			$aCicles = ObteCodiValorDesDeSQL($this->Connexio, $SQL, "cicle_pla_estudi_id", "nom");
			$this->CiclePlaEstudiId = $aCicles[0][0];
			$Retorn .= $this->CreaLlista('CPE.nom', 'Cicle', 600, 
				$aCicles[0], 
				$aCicles[1], 
				[], 
				$aCicles[0][0], 
				'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');
		}
		else {
			$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
			$this->AnyAcademicId = $this->Sistema->any_academic_id;
			$Retorn .= $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], [], $this->AnyAcademicId, 'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');

			$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_pla_estudi_id, nom FROM CICLE_PLA_ESTUDI WHERE any_academic_id='.$this->AnyAcademicId.' ORDER BY nom', "cicle_pla_estudi_id", "nom");
			$this->CiclePlaEstudiId = $aCicles[0][0];
			$Retorn .= $this->CreaLlista('CPE.nom', 'Cicle', 600, 
				$aCicles[0], 
				$aCicles[1], 
				[], 
				$aCicles[0][0], 
				'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');
		}
		return $Retorn;
	}
	
	/**
	 * Crea la sentència SQL.
	 * @param integer $ProfessorId Identificador del professor. No es fa servir (només per compatibilitat amb la sobrecàrrega).
	 * @param integer $AnyAcademicId Identificador de l'any acadèmic.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $ProfessorId, int $AnyAcademicId): string {
		$CiclePlaEstudiId = $this->CiclePlaEstudiId;
		return "
			SELECT 
				UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.unitat_formativa_id AS UnitatFormativaId, UPE.nivell AS Nivell,
				MPE.codi AS CodiMP, MPE.nom AS NomMP, 
				CPE.nom AS NomCF, CPE.cicle_formatiu_id AS CicleFormatiuId, CPE.codi AS CodiCF, 
				UPE.*, MPE.*, CPE.* 
			FROM UNITAT_PLA_ESTUDI UPE  
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			WHERE any_academic_id=$AnyAcademicId
			AND CPE.cicle_pla_estudi_id=$CiclePlaEstudiId
			ORDER BY CPE.codi, MPE.codi, UPE.codi
		";
	}
	
	private function WhereProfessorsCodiCicle($CiclePlaEstudiId): string {
		$SQL = "
			SELECT codi 
			FROM CICLE_FORMATIU 
			WHERE familia_fp_id IN (
				SELECT CF.familia_fp_id
				FROM CICLE_PLA_ESTUDI CPE
				LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id)
				WHERE CPE.cicle_pla_estudi_id=$CiclePlaEstudiId
			)		
		";
		$aCodiProfessor = DB::CarregaConjuntRegistresAss($this->Connexio, $SQL);

		$Retorn = '';
		foreach ($aCodiProfessor as $CodiProfessor) {
			$Retorn .= "LEFT(U.codi, 3)='" . $CodiProfessor['codi'] . "' OR ";
		}
		if ($Retorn != '')
			$Retorn = substr($Retorn, 0, -3);
		
		return $Retorn;
	}	

	/**
	 * Crea la sentència SQL per obtenir els professors.
	 * @return string Sentència SQL.
	 */
	private function CreaSQLProfessor(): string {
		$AnyAcademicId = $this->AnyAcademicId;
		$CiclePlaEstudiId = $this->CiclePlaEstudiId;
		$WhereProfessorsCodiCicle = $this->WhereProfessorsCodiCicle($CiclePlaEstudiId);
		return "
			SELECT 
				U.usuari_id, FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomCognom1Cognom2, U.codi 
			FROM USUARI U
			WHERE ($WhereProfessorsCodiCicle OR LEFT(U.codi, 3)='FOL' OR LEFT(U.codi, 2)='AN')
			AND usuari_bloquejat <> 1
			ORDER BY U.codi;		
		";
	}

	/**
	 * Genera l'array que emmagatzema els professors.
	 * @return void.
	 */
	private function GeneraProfessor() {
		$ProfessorUF = [];
		$SQL = $this->CreaSQLProfessor();
//print $SQL;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$this->ProfessorUF = new stdClass();
			$row = $ResultSet->fetch_assoc();
			while($row) {
				$Professor = new stdClass();
				array_push($ProfessorUF, $Professor);
				$Professor->Id = $row["usuari_id"];
				$Professor->Nom = utf8_encodeX($row["NomCognom1Cognom2"]);
				$Professor->Codi = $row["codi"];
				$row = $ResultSet->fetch_assoc();				
			}
		}			
//print_h($ProfessorUF);	
		$this->ProfessorUF = $ProfessorUF;
	}

	/**
	 * Crea la sentència SQL per obtenir els professors i les UF.
	 * @return string Sentència SQL.
	 */
	private function CreaSQLProfessorUF(): string {
		$AnyAcademicId = $this->AnyAcademicId;
		$CiclePlaEstudiId = $this->CiclePlaEstudiId;
		$WhereProfessorsCodiCicle = $this->WhereProfessorsCodiCicle($CiclePlaEstudiId);
		return "
			SELECT 
				FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomCognom1Cognom2, U.codi, 
				PUF.* 
			FROM PROFESSOR_UF PUF
			LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN USUARI U ON (PUF.professor_id=U.usuari_id) 
			WHERE any_academic_id=$AnyAcademicId
			AND ($WhereProfessorsCodiCicle OR LEFT(U.codi, 3)='FOL' OR LEFT(U.codi, 2)='AN')			
			ORDER BY U.codi;		
		";
	}	

	/**
	 * Genera l'array que emmagatzema els professors i les UF que fan.
	 * @return void.
	 */
	private function GeneraProfessorUF() {
		$ProfessorUFAssoc = [];
		$SQL = $this->CreaSQLProfessorUF();
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			while($row) {
				// Array associatiu
				$ProfessorUFAssoc[$row["professor_id"]][$row["uf_id"]] = True;
				$row = $ResultSet->fetch_assoc();				
			}
		}			
//print_h($ProfessorUFAssoc);	
		$this->ProfessorUFAssoc = $ProfessorUFAssoc;
	}
	
	/**
	 * Genera una acordió (component Bootstrap) amb el resultat de la SQL.
     * @return string Acordió amb les dades.
	 */
	public function GeneraAcordio() {
		$this->GeneraProfessor();
		$this->GeneraProfessorUF();
		$sRetorn = '';
		$SQL = $this->CreaSQL(-1, $this->AnyAcademicId);
//print $SQL;		
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$sRetorn .= '<DIV id=taula>';
			// Creem un objecte per administrar els cicles
			$Cicles = new stdClass();
			$i = -1; 
			$j = 0;
			$CicleFormatiuId = -1;
			$row = $ResultSet->fetch_assoc();
			while($row) {
				if ($row["CicleFormatiuId"] != $CicleFormatiuId) {
					$CicleFormatiuId = $row["CicleFormatiuId"];
					$i++;
					$Cicles->CF[$i] = $row;
					$j = 0; 
				}	
				$Cicles->UF[$i][$j] = $row;
				$j++;
				$row = $ResultSet->fetch_assoc();
			}	
//print_h($Cicles);			
			for($i = 0; $i < count($Cicles->CF); $i++) {
				$row = $Cicles->CF[$i];
				$sRetorn .= '<TABLE class="table table-fixed table-striped table-sm table-hover">';
				$sRetorn .= '<thead class="thead-dark">';
				$sRetorn .= "<TH width=300>Mòdul</TH>";
				$sRetorn .= "<TH width=300>Unitat formativa</TH>"; 
				foreach ($this->ProfessorUF as $PUF) {
					$sRetorn .= '<TH width=40 class="small" style="text-align:center" data-toggle="tooltip" data-placement="top" title="'.$PUF->Nom.'">'.$PUF->Codi.'</TH>'; 
				}
				$sRetorn .= '<TH width=20></TH>'; // Espai per la barra d'scroll
				$sRetorn .= '</thead>';
				$ModulAnterior = '';
				for($j = 0; $j < count($Cicles->UF[$i]); $j++) {
					$row = $Cicles->UF[$i][$j];
					$sRetorn .= "<TR>";

					if ($row["CodiMP"] != $ModulAnterior) {
						$sRetorn .= "<TD width=300>".utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"]);
						if ($this->Usuari->es_admin)
							$sRetorn .= " [".$row["modul_pla_estudi_id"]."]";
						$sRetorn .= "</TD>";
					}
					else 
						$sRetorn .= "<TD width=300></TD>";
					$ModulAnterior = $row["CodiMP"];

					$sRetorn .= "<TD width=300>".utf8_encodeX($row["NomUF"]).' ('.Ordinal($row["Nivell"]).')';
					if ($this->Usuari->es_admin)
						$sRetorn .= " [".$row["unitat_pla_estudi_id"]."]";
					$sRetorn .= "</TD>";

					foreach ($this->ProfessorUF as $PUF) {
						$ProfessorId = $PUF->Id;
						$UFId = $row["unitat_pla_estudi_id"];
						$Checked = '';
						if (array_key_exists($ProfessorId, $this->ProfessorUFAssoc) && array_key_exists($UFId, $this->ProfessorUFAssoc[$ProfessorId]))
							$Checked = ' checked ';
						$Nom = 'chbUFId_'.$UFId.'_'.$ProfessorId;
						$sRetorn .= "<TD width=40 style='text-align:center'><input type=checkbox name=".$Nom.$Checked." onclick='AssignaUF(this);'/></TD>";
					}
					$sRetorn .= '<TH width=20></TH>'; // Espai per la barra d'scroll
					$sRetorn .= "</TR>";
				}
				$sRetorn .= "</TABLE>";
			}
			$sRetorn .= '</DIV>';	
		};	
		return $sRetorn;
	}
}

/**
 * Formulari que mostra els professors per equip.
 */
//class ProfessorsEquip extends Form
class ProfessorsEquip extends Objecte
{
	// Tipus d'equip
	const TIPUS_EQUIP = array(
		'DP' => 'Departament', 
		'ED' => 'Equip docent',
		'CO' => 'Comissió',
		'CQ' => 'Comissió de qualitat',
		'CM' => 'Comissió de mobilitat'
	);
	
	/**
	* Identificador de l'equip.
	* @var array
	*/    
    private $Professors = []; 

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$frm = new FormDetall($this->Connexio, $this->Usuari);
		$Usuari = $this->Usuari;
		$frm->Titol = 'Professors per equip';
		$frm->SubTitol = $this->GeneraSubTitol();
		$frm->SQL = $this->CreaSQLProfessors($this->Id);
		$frm->Taula = 'PROFESSOR_EQUIP';
		$frm->ClauPrimaria = 'professor_equip_id';
		
		$frm->Camps = 'username, NomProfessor, codi, email_ins';
		$frm->Descripcions = 'Usuari, Professor, Codi, Email';
		$frm->PermetSuprimir = True;
		$frm->PermetAfegir = True;

		$frm->CampMestre = 'equip_id';
		$frm->ValorMestre = $this->Id;
		$frm->CampDetall = 'professor_id';

		$frm->LookUp->URL = 'UsuariRecerca.php?accio=Professors';
		$frm->LookUp->Taula = 'USUARI';
		$frm->LookUp->Id = 'usuari_id';
		$frm->LookUp->Camps = 'nom, cognom1, cognom2';

		$frm->EscriuHTML();		
	}	
	
	/**
	 * Crea la sentència SQL.
	 * @param integer $EquipId Identificador de l'equip.
	 * @return string Sentència SQL.
	 */
	private function CreaSQL(int $EquipId): string {
		return "
			SELECT 
				EQ.equip_id, AA.any_academic_id AS any_academic_id, AA.nom AS AnyAcademic, ".
				SQL::CreaCase('EQ.tipus', self::TIPUS_EQUIP)." AS Tipus, 
				EQ.nom AS NomEquip, 
				U.usuari_id, 
				FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomProfessor,
				U.username 
			FROM EQUIP EQ 
			LEFT JOIN ANY_ACADEMIC AA ON (EQ.any_academic_id=AA.any_academic_id) 
			LEFT JOIN USUARI U ON (EQ.cap=U.usuari_id)
			WHERE equip_id=$EquipId
		";
	}

	/**
	 * Crea la sentència SQL que conté els professors de l'equip.
	 * @param integer $EquipId Identificador de l'equip.
	 * @return string Sentència SQL.
	 */
	private function CreaSQLProfessors(int $EquipId): string {
		return "
			SELECT PEQ.professor_equip_id, U.codi, FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomProfessor, U.username, U.email_ins 
			FROM PROFESSOR_EQUIP PEQ
			LEFT JOIN USUARI U ON (PEQ.professor_id=U.usuari_id)
			WHERE equip_id=$EquipId
			ORDER BY U.codi
		";
	}
	
	/**
	 * Carrega les dades d'un usuari i les emmagatzema en l'atribut Registre.
     * @param int $EquipId Identificador de l'equip.
	 */
	private function Carrega(int $EquipId) {
		$SQL = $this->CreaSQL($this->Id);
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$rs = $ResultSet->fetch_object();
			$this->Registre = $rs;
		}
	}

	/**
	 * Carrega els professors d'un equip i els emmagatzema en l'atribut Professors.
     * @param int $EquipId Identificador de l'equip.
	 */
	private function CarregaProfessors(int $EquipId) {
		$SQL = $this->CreaSQLProfessors($this->Id);
		$ResultSet = $this->Connexio->query($SQL);
		while($row = $ResultSet->fetch_object()) 
			array_push($this->Professors, $row);
	}

	/**
	 * Genera el subtítol del formulari.
     * @return string Codi HTML del subtítol.
	 */
	private function GeneraSubTitol() {
		$this->Carrega($this->Id);
		$Dades = array(
			'Any' => $this->Registre->AnyAcademic,
			'Tipus' => $this->Registre->Tipus,
			'Equip' => $this->Registre->NomEquip,
			'Responsable' => $this->Registre->NomProfessor
		);
		return CreaTaula1($Dades);
	}
}

/**
 * Classe que encapsula les utilitats per al maneig de l'alumne.
 */
class Alumne extends Usuari
{
	/**
	 * Genera i escriu l'escriptori de l'alumne.
	 */
	public function Escriptori() {
		CreaIniciHTML($this->Usuari, '');
		echo '<div class="row">';
		$MatriculaId = $this->ObteMatriculaActiva($this->Usuari->usuari_id);
		// Pla de treball. Només es veu a l'avaluació ordinària
		if ($this->EsAvaluacioOrdinariaCursActual($this->Usuari->usuari_id)) {
			echo CreaTargeta('Pla de treball', 'Visualitza el teu pla de treball.', GeneraURL('Fitxa.php?accio=PlaTreball&Id='.$MatriculaId));
		}
		// Expedient. Només es veu quan els butlletins estan oberts
		if ($MatriculaId > 0) {
			echo CreaTargeta('Expedient', 'Visualitza el teu expedient.', GeneraURL('MatriculaAlumne.php?accio=MostraExpedient&MatriculaId='.$MatriculaId));
		}
		echo '</div>';
	}
	
	/**
	 * Indica si la avaluació del curs actual de l'alumne és ordinària.
	 * @param $AlumneId Identificador de l'alumne.
	 * @returns boolean Cert si la avaluació del curs actual de l'alumne és ordinària.
	 */
	private function EsAvaluacioOrdinariaCursActual($AlumneId) {
		$bRetorn = false;
		$SQL = " 
			SELECT *
			FROM MATRICULA M
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
			LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
			WHERE AA.any_academic_id=? AND M.alumne_id=?;		
		";
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("ii", $this->Sistema->any_academic_id, $AlumneId);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();
//		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {		
			$rsMatricula = $ResultSet->fetch_object();
			$bRetorn = (($rsMatricula->estat != 'T') && ($rsMatricula->avaluacio == 'ORD'));
		}		
		return $bRetorn;
	}

	/**
	 * Genera i escriu la fitxa del perfil de l'alumne.
	 */
	public function Perfil() {
		$frm = new FormFitxa($this->Connexio, $this->Usuari);
		$frm->Titol = 'Perfil';
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Id = $this->Usuari->usuari_id;
		$frm->AfegeixText('username', 'Usuari', 100, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('nom', 'Nom', 100, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('cognom1', '1r cognom', 100, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('cognom2', '2n cognom', 100, [FormFitxa::offNOMES_LECTURA]);
		$frm->AfegeixText('email', 'Correu electrònic', 100);
		$frm->AfegeixCheckBox('inscripcio_borsa_treball', "Inscripció borsa treball");
		$frm->EscriuHTML();
	}		
}

/**
 * Classe que encapsula el llistat dels alumnes que han promocionat 1r.
 */
class AlumnesPromocio1r extends Alumne
{
	/**
	 * Escriu el llistat dels alumnes que han promocionat de 1r.
	 */
	public function EscriuHTML() {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Titol = "Promoció d'alumnes de 1r";
		$SQL = ' SELECT '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.username, '.
			' U.data_naixement, Edat(U.data_naixement) AS edat, U.municipi, U.telefon, U.email, U.usuari_bloquejat, '.
			' M.matricula_id, M.grup, '.
			' C.curs_id AS CursId, C.nom AS NomCurs, C.nivell, M.baixa, PercentatgeAprovat(M.matricula_id) AS PercentatgeAprovat '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE es_alumne=1 AND M.matricula_id IS NOT NULL AND (M.baixa=0 or M.baixa is NULL) '.
			' AND C.nivell=1 '.
			' AND PercentatgeAprovat(M.matricula_id)>=60 '.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';
			
//print '<BR><BR><BR>'.$SQL;
		$frm->SQL = $SQL;
		
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'username, NomAlumne, Cognom1Alumne, Cognom2Alumne, data_naixement, edat, municipi, telefon, email, nivell, grup, %2:PercentatgeAprovat';
		$frm->Descripcions = 'Usuari, Nom, 1r cognom, 2n cognom, Data naixement, Edat, Municipi, Telèfon, Correu, Nivell, Grup, Percentatge aprovat';
		
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('CPE.any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1], [], $this->Sistema->any_academic_id);
		if ($this->Usuari->es_professor) {
			$p = new Professor($this->Connexio, $this->Usuari);
			$a = $p->ObteCodiCicles();
			$frm->Filtre->AfegeixLlista('CPE.codi', 'Cicle', 30, $a, $a);
		}
		else
			$frm->Filtre->AfegeixLlista('CPE.codi', 'Cicle', 30, array('APD', 'CAI', 'DAM', 'FIP', 'SMX'), array('APD', 'CAI', 'DAM', 'FIP', 'SMX'));

		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula el llistat dels alumnes que han graduat 2n.
 */
class AlumnesGraduacio2n extends Usuari
{
	/**
	 * Escriu el llistat dels alumnes que han graduat de 2n.
	 */
	public function EscriuHTML() {
		$frm = new FormRecerca($this->Connexio, $this->Usuari);
		$frm->Titol = "Graduació d'alumnes de 2n";
		$SQL = ' SELECT '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.username, '.
			' U.data_naixement, Edat(U.data_naixement) AS edat, U.municipi, U.telefon, U.email, U.usuari_bloquejat, '.
			' M.matricula_id, M.grup, '.
			' C.curs_id AS CursId, C.nom AS NomCurs, C.nivell, M.baixa, PercentatgeAprovat(M.matricula_id) AS PercentatgeAprovat '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE es_alumne=1 AND M.matricula_id IS NOT NULL AND (M.baixa=0 or M.baixa is NULL) '.
			' AND C.nivell=2 '.
			' AND PercentatgeAprovat(M.matricula_id)>=100 '.
			' ORDER BY C.nom, C.nivell, U.cognom1, U.cognom2, U.nom ';
			
//print '<BR><BR><BR>'.$SQL;
		$frm->SQL = $SQL;
		
		$frm->Taula = 'USUARI';
		$frm->ClauPrimaria = 'usuari_id';
		$frm->Camps = 'username, NomAlumne, Cognom1Alumne, Cognom2Alumne, data_naixement, edat, municipi, telefon, email, nivell, grup, %2:PercentatgeAprovat';
		$frm->Descripcions = 'Usuari, Nom, 1r cognom, 2n cognom, Data naixement, Edat, Municipi, Telèfon, Correu, Nivell, Grup, Percentatge aprovat';
		
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('CPE.any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1], [], $this->Sistema->any_academic_id);
			if ($this->Usuari->es_professor) {
			$p = new Professor($this->Connexio, $this->Usuari);
			$a = $p->ObteCodiCicles();
			$frm->Filtre->AfegeixLlista('CPE.codi', 'Cicle', 30, $a, $a);
		}
		else
			$frm->Filtre->AfegeixLlista('CPE.codi', 'Cicle', 30, array('APD', 'CAI', 'DAM', 'FIP', 'SMX'), array('APD', 'CAI', 'DAM', 'FIP', 'SMX'));

		$frm->EscriuHTML();
	}
}

/**
 * Classe que encapsula la orla d'alumnes.
 */
class Orla extends Form
{
	/**
	* Identificador de l'any acadèmic.
	* @var integer
	*/    
    public $AnyAcademicId = -1; 

	/**
	* Identificador del cicle.
	* @var integer
	*/    
    public $CicleFormatiuId = -1; 
	
	/**
	* Nivell: 1 o 2.
	* @var integer
	*/    
    public $Nivell = 1; 

	/**
	* Grup classe.
	* @var string
	*/    
    public $Grup = ''; 	
	
	/**
	 * Escriu la orla d'alumnes.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, "Orla");
		echo '<script language="javascript" src="js/Usuari.js?v1.1" type="text/javascript"></script>';

		echo $this->GeneraFiltre();
		echo '<BR><P>';
		echo $this->GeneraTaula();
		//echo $this->GeneraTaula2();
		CreaFinalHTML();
	}
	
	/**
	 * Genera el filtre del formulari.
	 */
	protected function GeneraFiltre() {
		$Retorn = '';

		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
//		$this->AnyAcademicId = $aAnys[0][0];
		$this->AnyAcademicId = $this->Sistema->any_academic_id;
		$Retorn .= $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], [], $this->AnyAcademicId, 'onchange="ActualitzaTaulaOrla(this);"');		
		
		$aCicles = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$this->CicleFormatiuId = $aCicles[0][0]; 
		$Retorn .= $this->CreaLlista('cicle_formatiu_id', 'Cicle', 500, $aCicles[0], $aCicles[1], [], $this->CicleFormatiuId, 'onchange="ActualitzaTaulaOrla(this);"');
			
		$Retorn .= $this->CreaLlista('nivell', 'Nivell', 75, array('1', '2'), array('1', '2'), [], '1', 'onchange="ActualitzaTaulaOrla(this);"');
		$Retorn .= $this->CreaLlista('grup', 'Grup', 75, array('', 'A', 'B', 'C', 'D', 'E'), array('', 'A', 'B', 'C', 'D', 'E'), [], '', 'onchange="ActualitzaTaulaOrla(this);"');
		
		return $Retorn;
	}

	/**
	 * Genera la taula amb l'orla per a un any i cicle concret.
     * @return string Taula amb les dades.
	 */
	public function GeneraTaula() {
		$Retorn = '<DIV id=taula>';
		$SQL = $this->CreaSQL();
		//$Retorn .= $SQL;

		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$Retorn .= '<TABLE>';
			$Retorn .= '<TR>';
			$i = 1;
			while($row = $ResultSet->fetch_object()) {
				if ($i > 10) {
					$Retorn .= '</TR><TR>';
					$i = 1;
				}
				$Nom = utf8_encodeX(trim($row->nom.'<br>'.$row->cognom1.' '.$row->cognom2));
				$Fitxer = 'img/pix/'.$row->document.'.jpg';
				if (!file_exists(ROOT.'/'.$Fitxer))
					$Fitxer = 'img/nobody.png';
				$Difuminat = ($row->baixa == 1) ? 'opacity:0.3;' : '';
				$Retorn .= '<TD style="'.$Difuminat.'vertical-align:top;text-align:center;">';
				$Retorn .= '<IMG SRC="'.$Fitxer.'">';
				$Retorn .= '<BR>';
				$AlumneId = $row->usuari_id;
				if ($this->Usuari->es_admin) {
					$URL = GeneraURL("UsuariFitxa.php?Id=$AlumneId");
					$Retorn .= "<A target=_blank href='$URL'>$Nom</A>";
					$Retorn .= "<BR>".$row->document;
				}
				else 
					$Retorn .= "$Nom";
				$Retorn .= '</TD>';
				$i++;
			}
			$Retorn .= '</TR>';
			$Retorn .= '</TABLE>';
		}
		else
			$Retorn .= 'No hi ha dades.';
		$Retorn .= '</DIV>';
		return $Retorn;
	}

	/**
	 * Crea la sentència SQL.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(): string {
		$AnyAcademicId = $this->AnyAcademicId;
		$CicleFormatiuId = $this->CicleFormatiuId;
		$Nivell = $this->Nivell;
		$Grup = $this->Grup;
		$SQL = "
			SELECT 
				U.*, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, 
				M.grup as Grup, M.baixa
			FROM MATRICULA M
			LEFT JOIN USUARI U ON (U.usuari_id=M.alumne_id)
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
			WHERE any_academic_id=$AnyAcademicId 
			AND CPE.cicle_formatiu_id=$CicleFormatiuId
			AND nivell=$Nivell
			ORDER BY Cognom1Alumne, Cognom2Alumne, NomAlumne
		";		
		if ($Grup <> '')
			$SQL .= " AND grup='$Grup' ";
		return $SQL;
	}	
}

/**
 * Classe que encapsula les utilitats per al maneig dels pares.
 */
class Progenitor extends Usuari
{
	/**
	 * Genera i escriu l'escriptori del progenitor.
	 */
	public function Escriptori() {
		// Els pares només poden veure dels seus fills:
		// - el PDF de les notes quan estigui disponible
		// - El pla de treball  
		CreaIniciHTML($this->Usuari, '');
		$SQL = ' SELECT '.
			' 	U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
			'   M.matricula_id AS MatriculaId, '.
			'   C.avaluacio '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE (U.pare_id=? OR U.mare_id=?) '.
			' AND (Edat(U.data_naixement)<18 OR U.permet_tutor=1) AND AA.any_academic_id=? ';
//print $SQL;

		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("iii", $this->Usuari->usuari_id, $this->Usuari->usuari_id, $this->Sistema->any_academic_id);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		$stmt->close();

		echo '<div class="row">';
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			while($row) {
				$NomComplet = trim(trim($row['NomAlumne']).' '.trim($row['Cognom1Alumne']).' '.trim($row['Cognom2Alumne']));
				$MatriculaId = $row['MatriculaId'];
				// Pla de treball. Només es veu a l'avaluació ordinària
				if ($row['avaluacio'] == 'ORD') {
					$URL = GeneraURL('Fitxa.php?accio=PlaTreball&Id='.$MatriculaId);
					echo CreaTargeta('Pla de treball', utf8_encodeX($NomComplet), $URL);
				}				
				$URL = GeneraURL('ExpedientPDF.php?MatriculaId='.$row['MatriculaId']);
				echo CreaTargeta('Expedient', utf8_encodeX($NomComplet), $URL);
				$row = $ResultSet->fetch_assoc();
			}
		}
		else
			echo 'No hi ha dades a mostrar.';
		echo '</div>';
		$ResultSet->close();
	}
}

/**
 * Classe que encapsula les utilitats per al maneig dels auditor.
 */
class Auditor extends Objecte
{
	/**
	 * Genera i escriu l'escriptori.
	 */
	public function Escriptori() {
		CreaIniciHTML($this->Usuari, '');
		echo '<div class="row">';
		echo CreaTargeta('Mòduls professionals', 'Programacions', GeneraURL('FPRecerca.php?accio=ProgramacionsDidactiques'));
		echo CreaTargeta('Qualitat', 'Documents', GeneraURL('Recerca.php?accio=Document'));
		echo '</div>';
	}	
}

/**
 * Classe que encapsula les utilitats per al maneig dels membres de la junta.
 */
class MembreJunta extends Objecte
{
	/**
	 * Genera i escriu l'escriptori.
	 */
	public function Escriptori() {
		CreaIniciHTML($this->Usuari, '');
		echo '<div class="row">';
		echo CreaTargeta('Material', 'Llistat de material', GeneraURL('Recerca.php?accio=Material'));
		echo CreaTargeta('Sortida', 'Sortida de material', GeneraURL('Fitxa.php?accio=SortidaMaterial'));
		echo CreaTargeta('Entrada', 'Entrada de material', GeneraURL('Fitxa.php?accio=EntradaMaterial'));
		echo CreaTargeta('Galeria', 'Imatges del material', GeneraURL('Recerca.php?accio=ImatgeMaterial'));
		echo CreaTargeta('Històric', 'Històric de material', GeneraURL('Recerca.php?accio=HistoricPrestecMaterial'));
		echo '</div>';
	}	
}