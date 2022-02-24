<?php

/**
 * LibExpedient.php
 *
 * Llibreria d'utilitats per a l'expedient.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once(ROOT.'/vendor/TCPDF/tcpdf.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibPDF.php');
require_once(ROOT.'/lib/LibNotes.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibMatricula.php');
require_once(ROOT.'/lib/LibAvaluacio.php');


/**
 * Classe que encapsula les utilitats per al maneig de l'expedient.
 */
class Expedient extends Form
{
	/**
	* Connexió a la base de dades.
	* @var object
	*/
	//public $Connexio;

	/**
	* Sistema operatiu (Windows, Linux).
	* @var string
	*/
	private $SistemaOperatiu = '';

	/**
	* Array que emmagatzema el contingut d'un ResultSet carregat de la base de dades.
	* @var array
	*/
    public $Registre = [];

	/**
	* Array que emmagatzema el contingut d'un ResultSet carregat de la base de dades.
	* @var object
	*/
    public $RegistreAlumne = NULL;

	/**
	* Registre que conté les notes dels mòduls. Es carrega amb CarregaNotesMP.
	* @var array
	*/
	private $NotesMP = NULL;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 */
	function __construct($conn, $user = NULL) {
		parent::__construct($conn, $user);
		//$this->Connexio = $con;
		$this->NotesMP = [];
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$this->SistemaOperatiu = 'Windows';
		else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN')
			$this->SistemaOperatiu = 'Linux';
	}

	/**
	 * Genera la SQL per obtenir l'expedient d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 * @return string Sentència SQL.
	 */
	public static function SQL($MatriculaId): string {
		$SQL = '
			SELECT 
				UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.orientativa, UPE.nivell AS NivellUF, 
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores AS HoresMP, 
				CPE.nom AS NomCF, CPE.nom AS NomCF, 
				CF.llei,
				U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.document AS DNI, 
				N.notes_id AS NotaId, N.baixa AS Baixa, N.convalidat AS Convalidat, N.nota1 AS Nota1, N.nota2 AS Nota2, N.nota3 AS Nota3, N.nota4 AS Nota4, N.nota5 AS Nota5, N.convocatoria AS Convocatoria, 
				CONCAT(CPE.codi, C.nivell, M.grup) AS Grup, CONCAT(AA.any_inici, "-", AA.any_final) AS AnyAcademic, 
				UPE.*, MPE.*, CPE.*, N.*, C.* 
			FROM NOTES N
			LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=N.uf_id)
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) 
			LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id)
			LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) 
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id) 
			LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id)
			WHERE M.matricula_id='.$MatriculaId.' AND UPE.nivell<=C.nivell
		';
//print $SQL;
		return $SQL;
    }

	/**
	 * Genera la SQL per obtenir les notes del mòduls professionals d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 * @return string Sentència SQL.
	 */
	private function SQLNotesMP(int $MatriculaId): string {
		$SQL = ' SELECT * '.
			' FROM NOTES_MP '.
			' WHERE matricula_id='.$MatriculaId;
		return $SQL;
    }

	/**
	 * Carrega les notes del mòduls professionals d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 */
	private function CarregaNotesMP(int $MatriculaId) {
		$SQL = $this->SQLNotesMP($MatriculaId);
		$ResultSet = $this->Connexio->query($SQL);
		$row = $ResultSet->fetch_assoc();
		while($row) {
			$this->NotesMP[$row["modul_professional_id"]] = $row["nota"];
			$row = $ResultSet->fetch_assoc();
		}
		$ResultSet->close();
    }

	/**
	 * Indica si el butlletí de notes és visible o no.
	 * @param integer $MatriculaId Id de la matrícula.
	 * @return boolena Cert si el butlletí de notes és visible.
	 */
	public function EsVisibleButlleti(int $MatriculaId): bool {
		$SQL = ' SELECT * FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' WHERE C.estat<>"T" '.
			' AND M.matricula_id='.$MatriculaId;
//print "<hr>".$SQL."<hr>";
		$bRetorn = False;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$bRetorn = ($row['estat'] == 'O');
		}
		return $bRetorn;
	}

	/**
	 * Genera l'expedient en PDF per a un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 */
	public function GeneraPDF($MatriculaId) {
		// create new PDF document
		$pdf = new QualificacionsPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetTitle('Expedient');
		$pdf->SetSubject('Expedient');

		$SQL = self::SQL($MatriculaId);
//print_r($SQL);

		$ResultSet = $this->Connexio->query($SQL);

		// Carreguem les notes dels MP
		$this->CarregaNotesMP($MatriculaId);

		// Carreguem les notes de les UF
		// Posem les dades del ResultSet en una estructura de dades pròpia
		$Qualificacions = [];
		$i = -1;
		$j = -1;
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$NomAlumne = $row["NomAlumne"];
			$Cognom1Alumne = $row["Cognom1Alumne"];
			$Cognom2Alumne = $row["Cognom2Alumne"];
			$Llei = $row["llei"];
			$pdf->AnyAcademic = $row["AnyAcademic"];
			$pdf->NomComplet = trim($Cognom1Alumne . ' ' . $Cognom2Alumne) . ', ' . $NomAlumne;
			$pdf->DNI = $row["DNI"];
			$pdf->CicleFormatiu = $row["NomCF"];
			$pdf->Grup = $row["Grup"];
			$pdf->Avaluacio = $this->TextAvaluacio($row["avaluacio"], $row["trimestre"]);
			$pdf->Llei = $Llei;
			$pdf->AddPage(); // Crida al mètode Header
			$ModulAnterior = '';
			while($row) {
				if ($row["CodiMP"] != $ModulAnterior) {
					$i++;
					$Qualificacions[$i] = new stdClass();
					$Qualificacions[$i]->Nom = utf8_encode($row["CodiMP"].'. '.$row["NomMP"]);
					$Qualificacions[$i]->Hores = $row["HoresMP"];
					if (array_key_exists($row["modul_professional_id"], $this->NotesMP))
						$Qualificacions[$i]->Qualf = NumeroANotaText($this->NotesMP[$row["modul_pla_estudi_id"]]);
					else
						$Qualificacions[$i]->Qualf = '';
					$Qualificacions[$i]->Conv = 'Ord.';
					$Qualificacions[$i]->UF = [];
					$j = -1;
				}
				$ModulAnterior = $row["CodiMP"];
				$j++;
				$Qualificacions[$i]->UF[$j] = new stdClass();
				$Qualificacions[$i]->UF[$j]->Nom = utf8_encode($row["NomUF"]);
				$Qualificacions[$i]->UF[$j]->Hores = utf8_encode($row["HoresUF"]);
				if ($row["Convocatoria"] == 0)
					$Nota = 'A) '.NumeroANotaText(UltimaNota($row));
				else {
					$Nota = NumeroANotaText($row["nota".$row["Convocatoria"]]);
					if ($row["orientativa"])
						$Nota .= ' *';
				}
				$Qualificacions[$i]->UF[$j]->Qualf = $Nota;
				$Qualificacions[$i]->UF[$j]->Conv = Notes::UltimaConvocatoria($row);
				$row = $ResultSet->fetch_assoc();
			}
		}
		$ResultSet->close();

		// Realitzem el layout
		if ($Llei == 'LO') {
			// LOE
			for($i = 0; $i < count($Qualificacions); $i++) {
				$HTML = '<TABLE>';
				$HTML .= "<TR>";
				$HTML .= '<TD style="width:50%">';

				// Mòdul professional
				$HTML .= "<TABLE>";
				$HTML .= "<TR>";
				$HTML .= '<TD style="width:55%">'.$Qualificacions[$i]->Nom."</TD>";
				$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->Hores."</TD>";
				$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->Qualf."</TD>";
				$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->Conv."</TD>";
				$HTML .= "</TR>";
				$HTML .= "</TABLE>";

				$HTML .= "</TD>";
				$HTML .= '<TD style="width:50%">';

				// Unitats formatives
				$HTML .= "<TABLE>";
				for($j = 0; $j < count($Qualificacions[$i]->UF); $j++) {
					$HTML .= "<TR>";
					$HTML .= '<TD style="width:55%">'.$Qualificacions[$i]->UF[$j]->Nom."</TD>";
					$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->UF[$j]->Hores."</TD>";
					$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->UF[$j]->Qualf."</TD>";
					$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->UF[$j]->Conv."</TD>";
					$HTML .= "</TR>";
				}
				$HTML .= "</TABLE>";

				$HTML .= "</TD>";
				$HTML .= "</TR>";
				$HTML .= "</TABLE>";
				$HTML .= "<HR>";
				$pdf->writeHTML($HTML, True);
			}
		} else {
			// LOGSE
			for($i = 0; $i < count($Qualificacions); $i++) {
				$HTML = '<TABLE>';
				$HTML .= "<TR>";
				$HTML .= '<TD style="width:100%">';

				// Crèdits
				$HTML .= "<TABLE>";
				for($j = 0; $j < count($Qualificacions[$i]->UF); $j++) {
					$HTML .= "<TR>";
					$HTML .= '<TD style="width:55%">'.$Qualificacions[$i]->UF[$j]->Nom."</TD>";
					$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->UF[$j]->Hores."</TD>";
					$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->UF[$j]->Qualf."</TD>";
					$HTML .= '<TD style="width:15%;text-align:center">'.$Qualificacions[$i]->UF[$j]->Conv."</TD>";
					$HTML .= "</TR>";
				}
				$HTML .= "</TABLE>";

				$HTML .= "</TD>";
				$HTML .= "</TR>";
				$HTML .= "</TABLE>";
				$HTML .= "<HR>";
				$pdf->writeHTML($HTML, True);		
			}
		}

		$pdf->Titol2(utf8_decode("Comentaris de l'avaluació"));
		$pdf->Escriu("Sense comentaris");

		$pdf->Titol2("Llegenda");
		$pdf->Escriu(utf8_decode("L'anotació A) identifica les qualificacions corresponents a avaluacions anteriors"));
		if ($Llei == 'LO')
			$pdf->Escriu(utf8_decode("L'anotació * identifica les qualificacions orientatives"));

		// Close and output PDF document
		$Nom = trim($Cognom1Alumne . ' ' . $Cognom2Alumne . ', ' . $NomAlumne);
		// Clean any content of the output buffer
		ob_end_clean();
		$pdf->Output('Expedient '.$Nom.'.pdf', 'I');
	}

	/**
	 * Genera l'script per a poder generar tots els expedients en PDF d'un curs.
	 * @param integer $Curs Identificador del curs.
	 * @param integer $Sufix Per posar l'estat de l'avaluació (1r trimestre, etc.).
	 */
	private function ComandaPHP(): string {
		$Retorn = '';
		if ($this->SistemaOperatiu === 'Windows')
			$Retorn = UNITAT_XAMPP.':\xampp\php\php.exe';
		else if ($this->SistemaOperatiu === 'Linux')
			$Retorn = 'php';
		return $Retorn;
	}

	/**
	 * Genera l'script per a poder generar tots els expedients en PDF d'un curs.
	 * @param integer $Curs Identificador del curs.
	 * @param integer $Sufix Per posar l'estat de l'avaluació (1r trimestre, etc.).
	 */
	public function GeneraScript($Curs, $Sufix): string {
		$Comanda = $this->ComandaPHP();
		$Retorn = '';
		$SQL = ' SELECT M.matricula_id AS MatriculaId, U.nom AS NomAlumne, U.*, C.* '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' WHERE C.curs_id='.$Curs;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			while ($row = $ResultSet->fetch_array()) {
				$Nom = utf8_encode($row["codi"])."_".
				$Sufix."_".
				utf8_encode($row["cognom1"])."_".
				utf8_encode($row["cognom2"])."_".
				utf8_encode($row["NomAlumne"]);
				$Nom = Normalitza($Nom);
				$Nom = str_replace(" ", "_", $Nom);
				$Retorn .= "$Comanda ".ROOT."/ExpedientPDF.php ".$row["MatriculaId"]." >".INGEST_DATA."/pdf/Expedient_".$Nom.".pdf\r\n";
			}
		}
		$ResultSet->close();
		return $Retorn;
	}

	/**
	 * Escriu l'script per a poder generar tots els expedients en PDF d'un curs.
	 * @param integer $Curs Identificador del curs.
	 * @param integer $Sufix Per posar l'estat de l'avaluació (1r trimestre, etc.).
	 */
	public function EscriuScript($Curs, $Sufix): string {
		echo GeneraScript($Curs, $Sufix);
	}

	private function TextAvaluacio($Avaluacio, $Trimestre) {
		if ($Avaluacio == 'ORD')
			return utf8_decode('Ordinària ').Ordinal($Trimestre).' T';
		else if ($Avaluacio == 'EXT')
			return utf8_decode('Extraordinària');
		else
			return '';
	}

	public static function CarregaNotesExpedient($ResultSet) {
		$Retorn = [];
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_object();
			$ModulAnterior = '';
			while($row) {
				if ($row->CodiMP != $ModulAnterior) {
					$MP = new stdClass();
					array_push($Retorn, $MP);
					$MP->IdMP = $row->IdMP;
					$MP->CodiMP = $row->CodiMP;
					$MP->NomMP = $row->NomMP;
					$MP->HoresMP = $row->HoresMP;
					$MP->UF = [];
					$ModulAnterior = $row->CodiMP;
				}
				array_push($MP->UF, $row);
				$row = $ResultSet->fetch_object();
			}
		};
		return $Retorn;
	}
}

/**
 * Classe per a l'expedient de SAGA.
 */
class ExpedientSaga extends Expedient
{
	/**
	* Identificador de la matrícula.
	* @var integer
	*/
	private $MatriculaId = -1;

	/**
	* Objecte matrícula.
	* @var object
	*/
	private $Matricula = NULL;

	/**
	* Objecte professor.
	* @var object
	*/
	private $Professor = NULL;
	/**
	* Registre que conté les mitjanes dels mòduls per a una matrícula.
	* És un array associatiu amb els següents valors:
	*  - Clau: Id del mòdul
	*  - Valor: Registre de la taula NOTES_MP
	* @var array
	*/
	private $RegistreMitjanes = NULL;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 * @param int $MatriculaId Identificador de la matrícula.
	 */
	function __construct($conn, $user, $MatriculaId) {
		parent::__construct($conn);

		$this->Connexio = $conn;
		$this->Usuari = $user;
		$this->MatriculaId = $MatriculaId;

		$this->Matricula = new Matricula($conn, $user);
		$this->Matricula->Carrega($this->MatriculaId);
		$this->RegistreMitjanes = [];

		$this->Professor = new Professor($conn, $user);
		$this->Professor->CarregaUFAssignades();
	}

	/**
	 * Genera la SQL per obtenir les dades d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 * @return string Sentència SQL.
	 */
	public function SQLDadesAlumne($MatriculaId): string {
		$SQL = ' SELECT CPE.nom AS NomCF, CPE.nom AS NomCF, '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.document AS DNI, '.
			' CONCAT(CPE.codi, C.nivell, M.grup) AS Grup, CONCAT(AA.any_inici, "-", AA.any_final) AS AnyAcademic, '.
			' CPE.*, C.*, M.* '.
			' FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (M.curs_id=C.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id) '.
//			' LEFT JOIN CICLE_FORMATIU CF ON (C.cicle_formatiu_id=CF.cicle_formatiu_id) '.
//			' LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
			' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
			' WHERE M.matricula_id='.$MatriculaId;
		return $SQL;
}

	/**
	 * Genera la sentència SQL per recuperar les notes mitjanes dels mòduls d'una matrícula.
	 * @return string Sentència SQL.
	 */
	private function SQLMitjanesModuls() {
		$sRetorn = 'SELECT * FROM NOTES_MP WHERE matricula_id='.$this->MatriculaId;
		return $sRetorn;
	}


	/**
	 * Carrega les dades de l'expedient al Registre.
	 */
	private function Carrega() {
		$SQL = self::SQL($this->MatriculaId);
//print_r($SQL);
//exit;
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR GeneraTaula</b>. Causa: ".$e->getMessage());
		}
		//$this->Registre = $ResultSet;
		
		$this->Registre = [];
		
		//$ResultSet = $this->Registre;

		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			while($row) {
				array_push($this->Registre, $row); 
				$row = $ResultSet->fetch_assoc();
			}
		};		
//print_h($this->Registre);
//exit;
	}

	/**
	 * Carrega les dades de l'alumne al Registre.
	 */
	private function CarregaDadesAlumne() {
		$this->RegistreAlumne = NULL;
		$SQL = self::SQLDadesAlumne($this->MatriculaId);
//print_r($SQL);
//exit;
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR GeneraTaula</b>. Causa: ".$e->getMessage());
		}
		if ($ResultSet->num_rows > 0)
			$this->RegistreAlumne = $ResultSet->fetch_object();
//print_r($this->RegistreAlumne);
//exit;
	}

	/**
	 * Carrega el registre de mitjanesdels mòduls.
	 */
	private function CarregaMitjanesModuls() {
		$SQL = $this->SQLMitjanesModuls();
//print_h($SQL);
		$ResultSet = $this->Connexio->query($SQL);

		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			while($row) {
				$this->RegistreMitjanes[$row["modul_professional_id"]] = $row;
				$row = $ResultSet->fetch_assoc();
			}
		}
//print_r($this->RegistreMitjanes); print('<HR>');
	}

	/**
	 * Crea una cel·la de la taula de notes amb tota la seva casuística i la següent que indica la convocatòria.
	 * @param object $row Registre que correspon a la nota.
	 * @param integer $i Fila.
	 * @return string Codi HTML de la cel·la.
	 */
	private function CreaCellaNota($row, int $i): string {
		$sRetorn = '';

		// <INPUT>
		// name: conté id i convocatòria
		// id: conté les coordenades x, y. Inici a (0, 0). Y sempre 0 per compatibilitat amb LibNotes.
		$Id = 'grd_'.$i.'_0';
		$Name = "txtNotaId_".$row["NotaId"]."_".$row['convocatoria'];

		if ($row['Convalidat'] == True) {
			// UF convalidada
			$Nota = NumeroANota(Notes::UltimaNotaAvaluada($row));
			$Convocatoria = Notes::UltimaConvocatoria($row);

			$style = "width:50px;text-align:center;background-color:blue;color:white;";
			$sRetorn .= '<td class="llistat1" style="text-align:center;">';
			$sRetorn .= "<input type='text' style='$style' name='notaJunta' id='$Id' disabled value='A) $Nota'>";
			$sRetorn .= '</td>';
			$sRetorn .= "<td class='llistat1' style='text-align:center;' width=50></td>";
			$sRetorn .= "<td class='llistat1' width=50>Convalidada</td>";
		}
		else if ($row['Baixa'] == True) {
			// Baixa UF
			$style = "background-color:grey;width:50px;text-align:center;";
			$sRetorn .= '<td class="llistat1" style="text-align:center;">';
			$sRetorn .= "<input type='text' class='micro numero no-editable' style='$style' id='$Id' disabled value=''>";
			$sRetorn .= '</td>';
			$sRetorn .= "<td class='llistat1' style='text-align:center;' width=50></td>";
			$sRetorn .= "<td class='llistat1' width=50>Baixa</td>";
		}
		else if ($row['convocatoria'] == 0) {
			// UF aprovada
			$Nota = NumeroANota(Notes::UltimaNotaAvaluada($row));
			$Convocatoria = Notes::UltimaConvocatoria($row);

			$style = "width:50px;text-align:center;";
			$sRetorn .= '<td class="llistat1" style="text-align:center;">';
			$sRetorn .= "<input type='text' class='micro numero no-editable' style='$style' name='notaJunta' id='$Id' disabled value='A) $Nota'>";
			$sRetorn .= '</td>';
			$sRetorn .= "<td class='llistat1' style='text-align:center;' width=50>".$Convocatoria."</td>";
			$sRetorn .= "<td class='llistat1' width=50></td>";
		}
		else {
			// UF actual
			$Nota = $row["nota".$row["Convocatoria"]];
			$Convocatoria = $row['convocatoria'];

			$Deshabilitat = '';
			if (!$this->Professor->TeUF($row["unitat_pla_estudi_id"]) && !$this->Professor->EsAdmin() && !$this->Professor->EsDireccio() && !$this->Professor->EsCapEstudis())
				$Deshabilitat = ' disabled ';
			$BackgroundColor = $Deshabilitat ? 'background-color:lightgrey;' : 'background-color:white;';

			$style = "width:50px;text-align:center;";
			
			if ($row["orientativa"] && !$row['Baixa']) 
				// Nota orientativa
				$BackgroundColor .= 'background-color:yellow;';
			
			$style .= $BackgroundColor;
			$ClassInput = 'nota';
			//$ClassInput = 'micro numero';
			$sRetorn .= '<td class="llistat1" style="text-align:center;">';
			$sRetorn .= "<input type=text class='$ClassInput' style='$style' name='$Name' id='$Id' $Deshabilitat value='$Nota' ".
				" onfocus='EnEntrarCellaNota(this);' onBlur='EnSortirCellaNota(this);' onkeydown='NotaKeyDown(this, event);' >";
			$sRetorn .= '</td>';
			$sRetorn .= "<td class='llistat1' style='text-align:center;' width=50>".$Convocatoria."</td>";
			$sRetorn .= "<td class='llistat1' width=50></td>";
		}
		return $sRetorn;
	}

	/**
	 * Crea una cel·la de la taula par a la nota del mòdul.
	 * @param string $IdMP Identificador del mòdul.
	 * @param integer $i Fila.
	 * @return string Codi HTML de la cel·la.
	 */
	private function CreaCellaNotaModul($IdMP, int $i): string {
		$NotaId = 0;
		$Nota = '';
		if (array_key_exists($IdMP, $this->RegistreMitjanes)) {
//print_h($this->RegistreMitjanes);
//print_h($this->RegistreMitjanes[$IdMP]);
//exit;
			$NotaId = $this->RegistreMitjanes[$IdMP]['notes_mp_id'];
			$Nota = $this->RegistreMitjanes[$IdMP]['nota'];
		}

//              $style .= "text-align:center;text-transform:uppercase;";
//              $Baixa = (($row["BaixaUF"] == 1) || ($row["BaixaMatricula"] == 1));
//              $Convalidat = ($row["Convalidat"] == True);
//              $Deshabilitat = '';

		$Deshabilitat = '';
		if (!$this->Professor->TeMP($IdMP) && !$this->Professor->EsAdmin() && !$this->Professor->EsDireccio() && !$this->Professor->EsCapEstudis())
			$Deshabilitat = ' disabled ';
		$BackgroundColor = $Deshabilitat ? 'background-color:lightgrey;' : 'background-color:white;';

		$sRetorn = '';

		// <INPUT>
		// name: conté identificadors de la nota, matrícula i mòdul.
		// id: conté les coordenades x, y. Inici a (0, 0). Y sempre 0 per compatibilitat amb LibNotes.
		$Id = 'grd_'.$i.'_0';
		$Name = "txtNotaModulId_".$NotaId."_".$this->MatriculaId."_".$IdMP;

		$style = "width:50px;text-align:center;text-transform:uppercase;";
		$style .= $BackgroundColor;
		
		$sRetorn .= '<td class="llistat3" style="text-align:center;">';
		$sRetorn .= "<input type=text class='nota' style='$style' name='$Name' id='$Id' $Deshabilitat value='$Nota' ".
			" onfocus='EnEntrarCellaNotaModul(this);' onBlur='EnSortirCellaNotaModul(this);' onkeydown='NotaKeyDown(this, event);' >";
		$sRetorn .= '</td>';
		$sRetorn .= "<td class='llistat3' width=50></td>";
		$sRetorn .= "<td class='llistat3' width=50></td>";

		return $sRetorn;
	}

	/**
	 * Cerca la matrícula anterior d'una matrícula dins un array ordenat per nom de l'alumne.
	 * @param array $aMatricules Array de matrícules.
	 * @param integer $$MatriculaId Identificador de la matrícula.
	 * @return integer Identificador de la matrícula anterior o -1 si no trobat.
	 */
	private function MatriculaAnterior(array $aMatricules, int $MatriculaId): int {
		$iRetorn = -1;
		for($i = 0; $i < count($aMatricules); $i++) {
			if ($i > 0 && $aMatricules[$i] == $MatriculaId)
				$iRetorn = $aMatricules[$i-1];
		}
		return $iRetorn;
	}

	/**
	 * Cerca la matrícula posterior d'una matrícula dins un array ordenat per nom de l'alumne.
	 * @param array $aMatricules Array de matrícules.
	 * @param integer $$MatriculaId Identificador de la matrícula.
	 * @return integer Identificador de la matrícula posterior o -1 si no trobat.
	 */
	private function MatriculaPosterior(array $aMatricules, int $MatriculaId): int {
		$iRetorn = -1;
		$c = count($aMatricules);
		for($i = 0; $i < $c; $i++) {
			if ($i < ($c-1) && $aMatricules[$i] == $MatriculaId)
				$iRetorn = $aMatricules[$i+1];
		}
		return $iRetorn;
	}
	
	/**
	 * Calcula el percentatge que té aprovat.
	 * @return string Percentatge aprovat amb 2 decimals.
	 */
	private function CalculaPercentatgeAprovat(): string {
		$HoresTotal = 0.0;
		$HoresAprovat = 0.0;
		$Percentatge = 0.0;
		foreach ($this->Registre as $row) {
			$UltimaNota = UltimaNota($row);
			if ($UltimaNota != '') {
				if ($row['es_fct'] && $UltimaNota='A') {
					$HoresAprovat += $row['HoresUF'];
				}
				else if ($UltimaNota >= 5)
					$HoresAprovat += $row['HoresUF'];
			}			
			$HoresTotal += $row['HoresUF'];
//print_h($row);			
		}
		if ($HoresTotal > 0)
			$Percentatge = $HoresAprovat/$HoresTotal*100;
//print_h('HoresAprovat: '.$HoresAprovat);			
//print_h('HoresTotal: '.$HoresTotal);			
		return number_format($Percentatge, 2);
	}	

	/**
	 * Genera la capçalera de l'expedient.
	 * @return string HTML amb la capçalera de l'expedient.
	 */
	private function GeneraTitol(): string {
		$Retorn = '<BR>';
		$Retorn .= '<TABLE width="740px"><TR><TD>';
		
		$PercentatgeAprovat = $this->CalculaPercentatgeAprovat();

		// Dades alumne
		$Retorn .= '<TABLE style="color:white;" width="450px">';
		$Retorn .= '<TR>';
		$Retorn .= '<TD><B>Cicle Formatiu</B></TD>';
		$Retorn .= '<TD>'.utf8_encode($this->RegistreAlumne->nom).'</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD><B>Grup classe</B></TD>';
		$Retorn .= '<TD>'.$this->RegistreAlumne->codi.' '.$this->RegistreAlumne->grup_tutoria.' ('.$this->RegistreAlumne->codi_xtec.')</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD><B>Alumne</TD>';
		$Retorn .= '<TD>'.utf8_encode(trim($this->RegistreAlumne->Cognom1Alumne.' '.$this->RegistreAlumne->Cognom2Alumne).', '.$this->RegistreAlumne->NomAlumne).'</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TR>';
		$Retorn .= "<TD><B>Sessió d'avaluació</B></TD>";
		$Retorn .= '<TD>';
		$Retorn .= ($this->RegistreAlumne->avaluacio == 'ORD') ? Ordinal($this->RegistreAlumne->trimestre).' trimestre' : 'Extraordinària';
		$Retorn .= '</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '</TABLE>';

		$Retorn .= '</TD><TD>';

		// Botons navegació
		$av = new Avaluacio($this->Connexio, $this->Usuari);
		$CursId = $this->Matricula->ObteCurs();
		$Grup = $this->Matricula->ObteGrupTutoria();
		$CursIdGrup = $CursId.','.$Grup;
		$aMatricules = $av->LlistaMatricules($CursIdGrup);
//print_h($aMatricules);
		$MatriculaAnterior = $this->MatriculaAnterior($aMatricules, $this->MatriculaId);
		$MatriculaPosterior = $this->MatriculaPosterior($aMatricules, $this->MatriculaId);
//echo "MatriculaAnterior: $MatriculaAnterior<br>";
//echo "MatriculaPosterior: $MatriculaPosterior<br>";
		$Retorn .= '<table><tr><td>';
		if ($MatriculaAnterior == -1) {
			$Retorn .= '<div style="width:70px;">';
			$Retorn .= '</div>';
		}
		else {
			$URL = GeneraURL("Fitxa.php?accio=ExpedientSaga&Id=$MatriculaAnterior");
			$Retorn .= '<div class="boto" style="width:70px">';
			$Retorn .= '<a href="'.$URL.'"><img style="display:inline;" src="img/esquerre_tots.gif"></a>';
			$Retorn .= '</div>';
		}
		$Retorn .= '</td><td>';
		if ($MatriculaPosterior == -1) {
			$Retorn .= '<div style="width:70px;">';
			$Retorn .= '</div>';
		}
		else {
			$URL = GeneraURL("Fitxa.php?accio=ExpedientSaga&Id=$MatriculaPosterior");
			$Retorn .= '<div class="boto" style="width:70px">';
			$Retorn .= '<a href="'.$URL.'"><img style="display:inline;" src="img/dreta_tots.gif"></a>';
			$Retorn .= '</div>';
		}
		$Retorn .= '</td></tr></table>';

		$Retorn .= '</TD>';
		$Retorn .= '<TD style="color:white;font-weight:bold;font-size:large;">';
		$Retorn .= $this->CalculaPercentatgeAprovat().'%';
		$Retorn .= '</TD>';
		$Retorn .='</TR></TABLE>';

		$Retorn .= '<BR>';
		return $Retorn;
	}

	/**
	 * Genera la llista de notes de l'expedient.
	 * @return string Taula amb les notes de l'expedient.
	 */
	private function GeneraTaula(): string {
		$i = 0; // Comptador de files
		$sRetorn = '<input type=hidden id=Formulari value=ExpedientSaga>';

		$alumne = $this->Matricula->ObteAlumne();
		$nivell = $this->Matricula->ObteNivell();

		//$sRetorn .= '<div class="contingut" style="padding-left: 20px; padding-right: 5px; background-color: rgb(141, 164, 160); overflow: auto; height: 696px;" id="content">';
		$sRetorn .= '<div class="contingut" style="padding-left: 20px; padding-right: 5px; background-color: rgb(141, 164, 160); overflow: auto; height: 650px; border: solid white 0px;" id="content">';

		//$sRetorn .= '<table border=2 cellpadding=0 cellspacing=10 width="740px" style="padding:0px;border-color=yellow;" id="taula_43419445926">';
		$sRetorn .= '<table width="740px" style="padding:0px;border-collapse:separate;">';
		$sRetorn .= '<tbody>';
		//$sRetorn .= '<tr><th colspan="2"></th></tr>';
		$sRetorn .= '<tr><th class="contingut">Codi</th>';
		$sRetorn .= '<th class="contingut">Mòdul</th>';
		$sRetorn .= '<th class="contingut">Hores</th>';
		$sRetorn .= '<th class="contingut" id="cell_43419445928">Qualif.</th>';
		$sRetorn .= '<th class="contingut">Conv.</th>';
		$sRetorn .= '<th class="contingut">Coment.</th>';
		$sRetorn .= '</tr>';

		$ModulAnterior = '';
		foreach ($this->Registre as $row) {
			if (($row["CodiMP"] != $ModulAnterior) && ($row["llei"])!='LG') {
				// Fila corresponent al mòdul
				$sRetorn .= '<TR class="tdContingut_001">';
				$sRetorn .= '<TD class="llistat3">'.utf8_encode($row["CodiMP"]).'</TD>';
				$sRetorn .= '<TD class="llistat3"><b>'.utf8_encode($row["CodiMP"].'. '.$row["NomMP"]).'</b></TD>';
				$sRetorn .= '<TD class="llistat3">'.$row["HoresMP"].'</TD>';
				$sRetorn .= $this->CreaCellaNotaModul($row["IdMP"], $i);
				$i++;
				$sRetorn .= '</TR>';
			}
			$ModulAnterior = $row["CodiMP"];
			// Fila corresponent a la UF
			$sRetorn .= "<TR class='tdContingut_00101 Nivell".$row["NivellUF"]."'>";
			$sRetorn .= "<TD class='llistat1'>"."</TD>";
			$sRetorn .= "<TD class='llistat1' width=200>".utf8_encode($row["NomUF"])."</TD>";
			$sRetorn .= "<TD class='llistat1' width=50>".$row["HoresUF"]."</TD>";
			$sRetorn .= $this->CreaCellaNota($row, $i);
			$i++;
			$sRetorn .= "</TR>";
		
		}
		
		$sRetorn .= '</tbody>';
		$sRetorn .= '</table>';
		$sRetorn .= "<input type=hidden name=TempNota value=''>";
		$sRetorn .= '</div>';

		return $sRetorn;
	}

	/**
	 * Genera el peu de l'expedient.
	 * @return string HTML amb el peu l'expedient.
	 */
	private function GeneraPeu(): string {
		return "";
	}

	/**
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, $this->Titol, True, True);
		echo '<script language="javascript" src="js/Forms.js?v1.0" type="text/javascript"></script>';
		echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Notes.js?v1.7" type="text/javascript"></script>';

		echo '<div style="padding-left: 20px; padding-right: 5px; color: white; background-color: rgb(141, 164, 160); height: 750px;" id="content">';
		echo '<div id="dades" style="display: block;">';

		$this->Carrega();
		$this->CarregaMitjanesModuls();
		$this->CarregaDadesAlumne();

		echo $this->GeneraTitol();
		echo $this->GeneraTaula();
		echo $this->GeneraPeu();

		echo '</div>';
		echo '</div>';
		CreaFinalHTML();
	}
}

/**
 * Classe per a l'informe de qualificacions en PDF.
 */
class QualificacionsPDF extends DocumentPDF
{
	/**
	* Any acadèmic.
	* @var string
	*/
	public $AnyAcademic = '';

	/**
	* Nom complet de l'alumne.
	* @var string
	*/
	public $NomComplet = '';

	/**
	* DNI l'alumne.
	* @var string
	*/
	public $DNI = '';

	/**
	* Nom del cicle formatiu.
	* @var string
	*/
	public $CicleFormatiu = '';

	/**
	* Grup del curs de l'alumne.
	* @var string
	*/
	public $Grup = '';

	/**
	* Avaluació.
	* @var string
	*/
	public $Avaluacio = '';

	/**
	* Llei.
	* @var string
	*/
	public $Llei = 'LO'; // Per defecte, LOE
	
    // Capçalera
    public function Header() {
        // Logo
        $image_file = ROOT.'/img/logo-gencat.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 14); // Helvetica, Bold, 14
		$this->SetXY(30, 15);
        $this->Cell(0, 15, 'Generalitat de Catalunya', 0, false, 'L', 0, '', 0, false, 'M', 'M');
		$this->SetXY(30, 20);
        $this->Cell(0, 15, "Departament d'Educació", 0, false, 'L', 0, '', 0, false, 'M', 'M');

		$this->SetXY(30, 30);
		$this->Titol1('Informe de qualificacions del curs escolar '.$this->AnyAcademic);

		$this->Titol2("Dades del centre");
		$this->Encolumna5("Nom", "", "", "Codi", "Municipi");
		$this->Encolumna5(utf8_decode("Institut de Palamós"), "", "", "17005352", utf8_decode("Palamós"));

		$this->Titol2("Dades de l'alumne");
		$this->Encolumna5("Alumne", "", "DNI", "", "Grup");
		$this->Encolumna5($this->NomComplet, "", $this->DNI, "", $this->Grup);

		$this->Titol2("Dades dels estudis");
		$this->Encolumna5("Cicle formatiu", "", "", utf8_decode("Avaluació"), "");
		$this->Encolumna5($this->CicleFormatiu, "", "", $this->Avaluacio, "");

		$this->Titol2("Qualificacions");

		$HTML = '<TABLE>';
		$HTML .= "<TR>";
		if ($this->Llei == 'LO') {
			// Mòdul professional
			$HTML .= '<TD style="width:50%">';
			$HTML .= "<TABLE>";
			$HTML .= "<TR>";
			$HTML .= utf8_decode('<TD style="width:55%">Mòdul</TD>');
			$HTML .= '<TD style="width:15%;text-align:center">Hores</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Qualf.</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Conv.</TD>';
			$HTML .= "</TR>";
			$HTML .= "</TABLE>";
			$HTML .= "</TD>";

			// Unitats formatives
			$HTML .= '<TD style="width:50%">';
			$HTML .= "<TABLE>";
			$HTML .= "<TR>";
			$HTML .= '<TD style="width:55%">Unitat formativa</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Hores</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Qualf.</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Conv.</TD>';
			$HTML .= "</TR>";
			$HTML .= "</TABLE>";
			$HTML .= "</TD>";
		} else {
			// Crèdits
			$HTML .= '<TD style="width:100%">';
			$HTML .= "<TABLE>";
			$HTML .= "<TR>";
			$HTML .= utf8_decode('<TD style="width:55%">Crèdit</TD>');
			$HTML .= '<TD style="width:15%;text-align:center">Hores</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Qualf.</TD>';
			$HTML .= '<TD style="width:15%;text-align:center">Conv.</TD>';
			$HTML .= "</TR>";
			$HTML .= "</TABLE>";
			$HTML .= "</TD>";			
		}
		$HTML .= "</TR>";
		$HTML .= "</TABLE>";
		$HTML .= "<HR>";

		$this->SetY(110);
		$this->writeHTML(utf8_encode($HTML), True, True);
    }

    // Peu de pàgina
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, 'Segell del centre', 0, false, 'L', 0, '', 0, false, 'T', 'M');
//        $this->Cell(0, 10, utf8_encode('Pàgina ').$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 10, 'Pàgina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

/**
 * Classe que encapsula les utilitats per al maneig del pla de treball.
 */
class PlaTreball extends Objecte
{
	/**
	* Identificador de la matrícula.
	* @var integer
	*/
	private $MatriculaId = -1;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 * @param int $MatriculaId Identificador de la matrícula.
	 */
	function __construct($conn, $user, $MatriculaId) {
		parent::__construct($conn);

		$this->Connexio = $conn;
		$this->Usuari = $user;
		$this->MatriculaId = $MatriculaId;
	}
	
	/**
	 * Escriu el pla de treball.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, "Pla de treball");
		$this->Carrega();
		echo $this->GeneraTitol();
		echo $this->GeneraTaula();
		//echo $this->GeneraModal();
		CreaFinalHTML();
	}

	/**
	 * Carrega les dades d'una matrícula i les emmagatzema en l'atribut Registre.
	 */
	private function Carrega() {
		$SQL = Expedient::SQL($this->MatriculaId);
//print($SQL);
		$RecordSet = [];
		$ResultSet = $this->Connexio->query($SQL);
		while ($row = $ResultSet->fetch_array())
			array_push($RecordSet, $row);
//print_h($this->RecordSet);
		$ResultSet->close();
		
		// Passem el RecordSet a un objecte estructurat: mòduls/UF
		$ModulAnterior = '';
		$this->Registre = [];
		
		foreach($RecordSet as $row) {
			if ($row['CodiMP'] != $ModulAnterior) {
				$MP = new stdClass();
				$MP->IdMP = $row['IdMP'];
				$MP->CodiMP = $row['CodiMP'];
				$MP->NomMP = utf8_encode($row['NomMP']);
				$MP->CriterisAvaluacio = $row['criteris_avaluacio'];
				$MP->UF = [];
				array_push($this->Registre, $MP);
				
				$ModulAnterior = $row['CodiMP'];
			}
			$UF = new stdClass();
			$UF->NomUF = utf8_encode($row['NomUF']);
			$UF->HoresUF = $row['HoresUF'];
			$UF->Convocatoria = $row['convocatoria'];
			$UF->Orientativa = $row['orientativa'];
			$UF->NivellUF = $row['NivellUF'];
			$UF->DataInici = MySQLAData($row['data_inici']);
			$UF->DataFinal = MySQLAData($row['data_final']);
			$UF->UltimaNota = UltimaNota($row);
			
			array_push($MP->UF, $UF);
		}

		// Repassem totes les UF per indicar els MP superats
		foreach($this->Registre as &$MP) {
			$MP->aprovat = true;
			foreach($MP->UF as $UF) {
				if ($UF->UltimaNota < 5)
					$MP->aprovat = false;
			}
		}
//print_h($this->Registre);
//exit;
	}

	/**
	 * Crea la sentència SQL per recuperar les dades de la capçalera.
	 * @return string Sentència SQL.
	 */
	private function CreaSQLTitol() {
		return '
			SELECT 
				M.matricula_id,
				CPE.nom AS NomCF, CPE.nom AS NomCF, 
				U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.document AS DNI, 
				CPE.*, C.*, AA.* 
			FROM MATRICULA M
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id) 
			LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id)
			LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id)
			WHERE M.matricula_id='.$this->MatriculaId;
	}

	/**
	 * Genera la capçalera del pla de treball.
	 * @return string HTML amb la capçalera del pla de treball.
	 */
	private function GeneraTitol(): string {
		$SQL = $this->CreaSQLTitol();
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR GeneraTaula</b>. Causa: ".$e->getMessage());
		}
		$Retorn = '';
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$NomComplet = trim($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"]);
			if ($this->Usuari->es_admin) {
				$NomComplet = $NomComplet." [".$row["usuari_id"]."]";
			}
			$Dades = array(
				'Alumne' => utf8_encode($NomComplet),
				'Cicle Formatiu' => utf8_encode($row["NomCF"]),
				'Curs' => $row["any_inici"].'-'.$row["any_final"]
			);
			if ($this->Usuari->es_admin)
				$Dades = array("Id" => $row["matricula_id"]) + $Dades;
			$Retorn .= CreaTaula1($Dades);		
			$Retorn .= '<BR>';
		}
		
		// Botó d'impressió
		echo '<span style="float:right;">';
		echo '<a href="#" onclick="print();" class="btn btn-primary active" role="button" aria-pressed="true" id="btnImprimeix">Imprimeix</a>';
		echo '</span>';
		
		return $Retorn;
	}

	/**
	 * Genera el pla de treball.
	 * @return string Taula amb el pla de treball.
	 */
	private function GeneraTaula(): string {
		$ModalCriterisAvaluacio = '';
		$Retorn = '<TABLE border=1>';
		foreach($this->Registre as $MP) {
			$color = ($MP->aprovat) ? 'color:grey' : '';
			$Retorn .= "<TR STYLE='$color;background-color:lightgrey;'>";
			$Retorn .= "<TD><B>".$MP->CodiMP.'. '.$MP->NomMP."</B></TD>";
			if (!$MP->aprovat) {
				$Retorn .= "<TD width=250 STYLE='text-align:center' width=200><a role='button' href='#' data-toggle='modal' data-target='#Modal".$MP->IdMP."'>Criteris d'avaluació</a></TD>";
				$ModalCriterisAvaluacio .= $this->GeneraModal($MP->IdMP, $MP->CodiMP, $MP->CriterisAvaluacio);
			}
			else
				$Retorn .= "<TD width=250></TD>";
			$Retorn .= '</TR>';
			foreach($MP->UF as $UF) {
				$color = ($UF->Convocatoria == 0) ? 'color:grey' : '';
				$Retorn .= "<TR STYLE='$color'>";
				$Retorn .= "<TD STYLE='padding-left:1cm;padding-right:1cm'>".$UF->NomUF."</TD>";
				if ($UF->Convocatoria == 0)
					$Retorn .= "<TD STYLE='text-align:center'>Aprovada (".$UF->UltimaNota.")</TD>";
				else
					$Retorn .= "<TD STYLE='padding-left:1cm;padding-right:1cm;text-align:center'>".$UF->DataInici."-".$UF->DataFinal."</TD>";
				$Retorn .= '</TR>';
			}
		}
		$Retorn .= '</TABLE>';
		$Retorn .= $ModalCriterisAvaluacio;
		return $Retorn;
	}		
	
	private function GeneraModal(int $Id, string $Nom, $Text): string {
		return '
			<div class="modal fade" id="Modal'.$Id.'" tabindex="-1" role="dialog" aria-labelledby="Modal'.$Id.'Label" aria-hidden="true">
			  <div class="modal-dialog  modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="Modal'.$Id.'Label">Criteris d'."'".'avaluació</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					  <span aria-hidden="true">&times;</span>
					</button>
				  </div>
				  <div class="modal-body">
					'.$Text.'
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Tanca</button>
				  </div>
				</div>
			  </div>
			</div>	';	
	}
}

?>
