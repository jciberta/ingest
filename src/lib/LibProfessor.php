<?php

/** 
 * LibProfessor.php
 *
 * Llibreria d'utilitats per al professor.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibHTML.php');


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
			' MPE.modul_professional_id, MPE.codi AS CodiMP, MPE.nom AS NomMP, '.
			' UPE.unitat_pla_estudi_id, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.nivell '.
			' FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=PUF.uf_id) '.
			' LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) '.
			' WHERE professor_id='.$this->Usuari->usuari_id.
			' AND AA.actual=1 '.
			' ORDER BY CPE.codi, UPE.nivell ';
			
		/*$SQL = ' SELECT '.
			' CF.cicle_formatiu_id, CF.codi AS CodiCF, CF.nom AS NomCF, '.
			' MP.modul_professional_id, MP.codi AS CodiMP, MP.nom AS NomMP, '.
			' UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.nivell '.
			' FROM PROFESSOR_UF PUF '.
			' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=PUF.uf_id) '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
			' WHERE professor_id='.$this->Usuari->usuari_id .
			' ORDER BY CF.codi, UF.nivell ';*/
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$i = 0;
			while ($obj = $ResultSet->fetch_object()) {
				$this->UFAssignades[$i] = $obj;
				$i++;
			}
		}
		$ResultSet->close();
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
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=C.cicle_formatiu_id) '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.cicle_formatiu_id=CF.cicle_formatiu_id) '.
			' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id) '.
			' LEFT JOIN PROFESSOR_UF PUF ON (UF.unitat_formativa_id=PUF.uf_id) '.
			' WHERE professor_id='.$this->Usuari->usuari_id.
			' AND curs_id='.$CursId;
		$ResultSet = $this->Connexio->query($SQL);
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
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=C.cicle_formatiu_id) '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.cicle_formatiu_id=CF.cicle_formatiu_id) '.
			' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.modul_professional_id=MP.modul_professional_id) '.
			' LEFT JOIN PROFESSOR_UF PUF ON (UF.unitat_formativa_id=PUF.uf_id) '.
			' WHERE professor_id='.$this->Usuari->usuari_id.
			' AND matricula_id='.$MatriculaId;
		$ResultSet = $this->Connexio->query($SQL);
		$obj = $ResultSet->fetch_object();
		return ($obj->UF > 0);
	}
	
	/**
	 * Comprova si té assignada una UF.
	 * @param integer $UF Identificador de la UF.
	 * @returns boolean Cert si té assignada la UF.
	 */
	function TeUF(int $UF): bool {
//print('Id UF: '.$UF);
//print_h($this->UFAssignades);
//exit;
		$bRetorn = False;
		for($i = 0; $i < count($this->UFAssignades); $i++) {
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
			if ($this->UFAssignades[$i]->modul_professional_id == $MP) 
				$bRetorn = True;
		}
		return $bRetorn;
	}
	
	/**
	 * Carrega si és tutor en un curs.
	 * @param integer $CursId Identificador del curs.
	 */
	function CarregaTutor(int $CursId) {
		$SQL = ' SELECT * FROM TUTOR '.
			' WHERE professor_id='.$this->Usuari->usuari_id.
			' AND curs_id='.$CursId;
		$ResultSet = $this->Connexio->query($SQL);
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
			' WHERE actual=1 AND professor_id='.$this->Usuari->usuari_id;
		$ResultSet = $this->Connexio->query($SQL);
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
			WHERE PUF.professor_id='.$this->Usuari->usuari_id;
		$ResultSet = $this->Connexio->query($SQL);
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
			' WHERE AA.actual=1 '.
			' AND alumne_id='.$AlumneId.
			' AND professor_id='.$this->Usuari->usuari_id;
		$ResultSet = $this->Connexio->query($SQL);
		$bRetorn = ($ResultSet->num_rows > 0);
		$ResultSet->close();
		return $bRetorn;
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
	 */
	protected function GeneraFiltre() {
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$this->AnyAcademicId = $aAnys[0][0]; 
		return $this->CreaLlista('any_academic_id', 'Any', 200, $aAnys[0], $aAnys[1], $this->AnyAcademicId, 'onchange="ActualitzaTaulaProfessorsUF(this);"');
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
			$CicleFormatiuIdAnterior = -1;
			$UnitatFormativaId = -1;
			$row = $ResultSet->fetch_assoc();
			while($row) {
				if ($row["CicleFormatiuId"] != $CicleFormatiuIdAnterior) {
					$CicleFormatiuIdAnterior = $row["CicleFormatiuId"];
					$i++;
					$Cicles->CF[$i] = $row;
					$j = 0; 
				}
				if ($row["UnitatFormativaId"] == $UnitatFormativaId) {
					$Cicles->UF[$i][$j-1]->NomComplet .= utf8_encode(', '.$row['Nom'].' '.$row['Cognom1'].' '.$row['Cognom2']);
				}
				else {
					$UnitatFormativaId = $row['UnitatFormativaId'];
					$Cicles->UF[$i][$j] = new stdClass();
					$Cicles->UF[$i][$j]->Dades = $row;
					$Cicles->UF[$i][$j]->NomComplet = utf8_encode($row['Nom'].' '.$row['Cognom1'].' '.$row['Cognom2']);
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
				$sRetorn .= '  <div class="card">';
				$sRetorn .= '    <div class="card-header" id="'.$row['CodiCF'].'">';
				$sRetorn .= '      <h5 class="mb-0">';
				$sRetorn .= '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$row['CodiCF'].'" aria-expanded="true" aria-controls="collapse'.$row['CodiCF'].'">';
				$sRetorn .= utf8_encode($row['NomCF']);
				$sRetorn .= '        </button>';
				$sRetorn .= '      </h5>';
				$sRetorn .= '    </div>';
				$sRetorn .= '    <div id="collapse'.$row['CodiCF'].'" class="collapse" aria-labelledby="'.$row['CodiCF'].'" data-parent="#accordionExample">';
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
						$sRetorn .= "<TD>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
					else 
						$sRetorn .= "<TD></TD>";
					$ModulAnterior = $row["CodiMP"];
					$sRetorn .= "<TD>".utf8_encode($row["NomUF"])."</TD>";
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
		return "
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
			LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PUF.professor_id) 
			WHERE any_academic_id=$AnyAcademicId
			ORDER BY CPE.codi, MPE.codi, UPE.codi, U.cognom1, U.cognom2, U.nom
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
		echo '<script language="javascript" src="js/Professor.js?v1.1" type="text/javascript"></script>';
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
		$this->AnyAcademicId = $aAnys[0][0]; 
		return $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], $this->AnyAcademicId, 'onchange="ActualitzaTaulaProfessorsAssignacioUF(this);"');
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
			$CicleFormatiuId = -1;
			$row = $ResultSet->fetch_assoc();
			$NomProfessor = utf8_encode($row["NomProfessor"]." ".$row["Cognom1Professor"]." ".$row["Cognom2Professor"]);
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
			
			$sRetorn .= '<div class="alert alert-primary" role="alert">Professor: <B>'.$NomProfessor.'</B></div>';

			// Creem una llista dels cicles formatius amb les UFs amb col·lapsament.
			// https://getbootstrap.com/docs/4.1/components/collapse/
			$sRetorn .= '<div class="accordion" id="accordionExample">';

			for($i = 0; $i < count($Cicles->CF); $i++) {
				$row = $Cicles->CF[$i];
				$sRetorn .= '  <div class="card">';
				$sRetorn .= '    <div class="card-header" id="'.$row['CodiCF'].'">';
				$sRetorn .= '      <h5 class="mb-0">';
				$sRetorn .= '        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$row['CodiCF'].'" aria-expanded="true" aria-controls="collapse'.$row['CodiCF'].'">';
				$sRetorn .= utf8_encode($row['NomCF']);
				$sRetorn .= '        </button>';
				$sRetorn .= '      </h5>';
				$sRetorn .= '    </div>';
				$sRetorn .= '    <div id="collapse'.$row['CodiCF'].'" class="collapse" aria-labelledby="'.$row['CodiCF'].'" data-parent="#accordionExample">';
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
						$sRetorn .= "<TD>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
					else 
						$sRetorn .= "<TD></TD>";
					$ModulAnterior = $row["CodiMP"];

					$sRetorn .= "<TD>".utf8_encode($row["NomUF"]);
					if ($this->Usuari->es_admin)
						$sRetorn .= " (".$row["unitat_pla_estudi_id"].")";
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
			ORDER BY CPE.codi, MPE.codi, UPE.codi, U.cognom1, U.cognom2, U.nom
		";
	}
}

/**
 * Formulari que mostra l'assignacio d'un grup de professors per UF.
 */
class GrupProfessorsAssignacioUF extends ProfessorsAssignacioUF
{
	/**
	* Codi del cicle del pla d'estudi.
	* @var integer
	*/    
    public $CodiCiclePlaEstudi = 'APD'; 
	
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
	 * Genera el filtre del formulari si n'hi ha.
	 */
	protected function GeneraFiltre() {
		$Retorn = '';
		
		$aAnys = ObteCodiValorDesDeSQL($this->Connexio, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$this->AnyAcademicId = $aAnys[0][0]; 
		$Retorn .= $this->CreaLlista('any_academic_id', 'Any', 150, $aAnys[0], $aAnys[1], $this->AnyAcademicId, 'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');
		
		$Retorn .= $this->CreaLlista('CPE.codi', 'Cicle', 100, 
			array('APD', 'CAI', 'DAM', 'FIP', 'SMX', 'FPB', 'HBD'), 
			array('APD', 'CAI', 'DAM', 'FIP', 'SMX', 'FPB', 'HBD'),
			$this->CodiCiclePlaEstudi, 
			'onchange="ActualitzaTaulaGrupProfessorsAssignacioUF(this);"');
		return $Retorn;
	}
	
	/**
	 * Crea la sentència SQL.
	 * @param integer $ProfessorId Identificador del professor. No es fa servir (només per compatibilitat amb la sobrecàrrega).
	 * @param integer $AnyAcademicId Identificador de l'any acadèmic.
	 * @return string Sentència SQL.
	 */
	protected function CreaSQL(int $ProfessorId, int $AnyAcademicId): string {
		$CodiCiclePlaEstudi = $this->CodiCiclePlaEstudi;
		return "
			SELECT 
				UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.unitat_formativa_id AS UnitatFormativaId, 
				MPE.codi AS CodiMP, MPE.nom AS NomMP, 
				CPE.nom AS NomCF, CPE.cicle_formatiu_id AS CicleFormatiuId, CPE.codi AS CodiCF, 
				UPE.*, MPE.*, CPE.* 
			FROM UNITAT_PLA_ESTUDI UPE  
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			WHERE any_academic_id=$AnyAcademicId
			AND LEFT(CPE.codi, 3)='$CodiCiclePlaEstudi'
			ORDER BY CPE.codi, MPE.codi, UPE.codi
		";
	}

	/**
	 * Crea la sentència SQL per obtenir els professors.
	 * @return string Sentència SQL.
	 */
	private function CreaSQLProfessor(): string {
		$AnyAcademicId = $this->AnyAcademicId;
		$CodiCiclePlaEstudi = $this->CodiCiclePlaEstudi;
		// Pedaç
		if (($CodiCiclePlaEstudi == 'DAM') || ($CodiCiclePlaEstudi == 'FPB'))
			$CodiCiclePlaEstudi = 'SMX';
		if ($CodiCiclePlaEstudi == 'FIP')
			$CodiCiclePlaEstudi = 'CAI';
		return "
			SELECT 
				U.usuari_id, FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomCognom1Cognom2, U.codi 
			FROM USUARI U
			WHERE LEFT(U.codi, 3)='$CodiCiclePlaEstudi' AND usuari_bloquejat <> 1
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
				$Professor->Nom = utf8_encode($row["NomCognom1Cognom2"]);
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
		$CodiCiclePlaEstudi = $this->CodiCiclePlaEstudi;
		// Pedaç
		if (($CodiCiclePlaEstudi == 'DAM') || ($CodiCiclePlaEstudi == 'FPB'))
			$CodiCiclePlaEstudi = 'SMX';
		if ($CodiCiclePlaEstudi == 'FIP')
			$CodiCiclePlaEstudi = 'CAI';
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
			AND LEFT(U.codi, 3)='$CodiCiclePlaEstudi'
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
					$sRetorn .= '<TH width=40 style="text-align:center" data-toggle="tooltip" data-placement="top" title="'.$PUF->Nom.'">'.$PUF->Codi.'</TH>'; 
				}
				$sRetorn .= '</thead>';
				$ModulAnterior = '';
				for($j = 0; $j < count($Cicles->UF[$i]); $j++) {
					$row = $Cicles->UF[$i][$j];
					$sRetorn .= "<TR>";

					if ($row["CodiMP"] != $ModulAnterior)
						$sRetorn .= "<TD width=300>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
					else 
						$sRetorn .= "<TD width=300></TD>";
					$ModulAnterior = $row["CodiMP"];

					$sRetorn .= "<TD width=300>".utf8_encode($row["NomUF"]);
					if ($this->Usuari->es_admin)
						$sRetorn .= " (".$row["unitat_pla_estudi_id"].")";
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
					$sRetorn .= "</TR>";
				}
				$sRetorn .= "</TABLE>";
			}
			$sRetorn .= '</DIV>';	
		};	
		return $sRetorn;
	}
}

?>