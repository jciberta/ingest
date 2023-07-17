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
require_once(ROOT.'/lib/LibClasses.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibDB.php');
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
	 * Array que emmagatzema el contingut d'un ResultSet carregat de la base de dades.
	 * @var object
	 */
    public $RegistreAlumne = null;

	/**
	 * Registre que conté les notes dels mòduls. Es carrega amb CarregaNotesMP.
	 * @var array
	 */
	private $NotesMP = null;

	/**
	 * Indica si el butlletí està visible.
	 * L'alumne i el pare només poden veure les notes quan s'ha activat la visibilitat dels butlletins per a aquell curs.
	 * @var boolean
	 */
	private $ButlletiVisible = true;

	/**
	 * Indica si es pot editar l'expedient.
	 * @var boolean
	 */
	public $ActivaEdicio = false;
 
	// Estadístiques
	private $NumeroUFCicle = 0;
	private $NumeroUFTotals = 0;
	private $NumeroUFAprovades = 0;
	private $HoresTotals = 0;
	private $HoresAprovades = 0;
	private $PercentatgeAprovat = 0.0;
	private $Mitjana = 0.0; // La nota de la FCT no es compta

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 * @param objecte $system Dades de l'aplicació.
	 */
	function __construct($conn, $user = null, $Sistema = null) {
		parent::__construct($conn, $user, $Sistema);
		$this->NotesMP = [];
	}

	/**
	 * Genera la SQL per obtenir l'expedient d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 * @return string Sentència SQL.
	 */
	public static function SQL($MatriculaId): string {
		$SQL = '
			SELECT 
				UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.orientativa, UPE.nivell AS NivellUF, UPE.data_inici AS DataIniciUF, UPE.data_final AS DataFinalUF,
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores AS HoresMP, 
				CPE.cicle_pla_estudi_id AS IdCF, CPE.nom AS NomCF, CPE.nom AS NomCF, CPE.codi AS CodiCF,
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
	 * Carrega les dades de l'expedient al Registre.
	 */
	protected function Carrega() {
		$SQL = self::SQL($this->Id);
//print_r($SQL);
//exit;
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR Carrega</b>. Causa: ".$e->getMessage());
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
	 * @return boolean Cert si el butlletí de notes és visible.
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
	 * Genera el contingut HTML del formulari i el presenta a la sortida.
	 */
	public function EscriuHTML() {
		$this->CreaInici();
		$this->Carrega();
		$this->CalculaEstadistiques();

		$Matricula = new Matricula($this->Connexio, $this->Usuari, $this->Sistema);
		$Matricula->Carrega($this->Id);
		$alumne = $Matricula->ObteAlumne();
		$nivell = $Matricula->ObteNivell();		

		// L'alumne i el pare només poden veure les notes quan s'ha activat la visibilitat dels butlletins per a aquell curs
		if ($this->Usuari->es_alumne || $this->Usuari->es_pare) {
			$Expedient = new Expedient($this->Connexio, $this->Usuari, $this->Sistema);
			$this->ButlletiVisible = $Expedient->EsVisibleButlleti($this->Id);
		}

		if ($this->ButlletiVisible) {
			$SQL = Expedient::SQL($this->Id);
		//print_r($SQL.'<BR>');
			$ResultSet = $this->Connexio->query($SQL);
		
			if ($ResultSet->num_rows > 0) {
				
				$row = $ResultSet->fetch_assoc();
				$NomComplet = trim(utf8_encodeX($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"]));
				if ($this->Usuari->es_admin) {
					$NomComplet = $NomComplet." [".$row["usuari_id"]."]";
					echo "Matrícula: <B>[$this->Id]</B>&nbsp;&nbsp;&nbsp;";
				}
				echo 'Alumne: <B>'.$NomComplet.'</B>&nbsp;&nbsp;&nbsp;';
				echo 'Cicle: <B>'.utf8_encodeX($row["NomCF"]).'</B>';

				echo '<span style="float:right;">';
				if ($nivell == 2) {
					echo '<input type="checkbox" name="chbNivell1" checked onclick="MostraNotes(this, 1);">Notes 1r &nbsp';
					echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraNotes(this, 2);">Notes 2n &nbsp';
				}		

				if (!$this->Usuari->es_admin) {
					$URL = GeneraURL("ExpedientPDF.php?MatriculaId=$this->Id");
					echo '<a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnDescarregaPDF" name="btnDescarregaPDF_'.$alumne.'">Descarrrega PDF</a>';
				}
				else {
					// Descàrregues
					$SQL = bin2hex(Encripta(TrimX($SQL)));
					echo HTML::CreaBotoDesplegable('Descarrega', 
						array(
							'PDF' => GeneraURL("ExpedientPDF.php?MatriculaId=$this->Id"),
							'CSV' => GeneraURL("Descarrega.php?Accio=ExportaCSV&SQL=$SQL"),
							'XLSX' => GeneraURL("Descarrega.php?Accio=ExportaXLSX&SQL=$SQL")
						)
					);
	
					// Pla de treball
					echo '&nbsp';
					$URL = GeneraURL("Fitxa.php?accio=PlaTreball&Id=$this->Id");
					echo '<a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnPlaTreball">Pla de treball</a>';
					
					// Edició de l'expedient
					echo '&nbsp';
					if ($this->ActivaEdicio==1) { 
						$URL = GeneraURL("MatriculaAlumne.php?accio=MostraExpedient&MatriculaId=$this->Id");
						echo '<a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnActivaEdicio">Desactiva edició</a>';
					}
					else {
						$URL = GeneraURL("MatriculaAlumne.php?accio=MostraExpedient&ActivaEdicio=1&MatriculaId=$this->Id");
						echo '<a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnActivaEdicio">Activa edició</a>';
					}
				}
				echo '</span>';
		
				echo '<BR><BR>';
		
				echo '<TABLE class="table table-fixed table-sm table-striped table-hover">';
				echo '<thead class="thead-dark">';
				echo "<TH width=200>Mòdul</TH>";
				echo "<TH width=200>UF</TH>";
				echo "<TH width=50>Nivell</TH>";
				echo "<TH width=50>Hores</TH>";
				echo "<TH width=250 colspan=5>Notes</TH>";
				if ($this->ActivaEdicio==1)
					echo "<TH width=75>Convocatòria</TH>";
				echo '</thead>';
		
				$ModulAnterior = '';
				$j = 1;
				while($row) {
					echo "<TR class='Nivell".$row["NivellUF"]."'>";
					if ($row["CodiMP"] != $ModulAnterior)
						echo "<TD width=200>".utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
					else 
						echo "<TD width=200></TD>";
					$ModulAnterior = $row["CodiMP"];
					echo "<TD width=200>".utf8_encodeX($row["NomUF"])."</TD>";
					echo "<TD width=50>".$row["NivellUF"]."</TD>";
					echo "<TD width=50>".$row["HoresUF"]."</TD>";
					$Baixa = ($row["Baixa"] == True);
					$Convalidat = ($row["Convalidat"] == True);

					for ($i=1; $i<6; $i++) {
						$style = 'width:2em;text-align:center';
						if (($Convalidat) && ($i == 1)) {
							$Deshabilitat = " disabled ";
							$style .= ";background-color:blue;color:white";
						}
						if (($row['convocatoria'] == $i) && (!$Baixa)) {
							// Marquem la convocatòria actual
							$style .= ';border-width:1px;border-color:blue';
							if ($row['orientativa'])
								$style .= ";background-color:yellow";
						}
						$Nota = NumeroANota($row["Nota".$i]);
						$Deshabilitat = ($this->ActivaEdicio==1) ? '' : 'disabled';
						
						// <INPUT>
						// name: conté id i convocatòria
						// id: conté les coordenades x, y. Inici a (0, 0).
						$Id = 'grd_'.$j.'_'.$i;
						echo "<TD width=50><input type=text $Deshabilitat style='$style' name=txtNotaId_".$row["NotaId"]."_".$i.
							" id='$Id' value='$Nota' ".
							" onfocus='EnEntrarCellaNota(this);' onBlur='EnSortirCellaNota(this);' onkeydown='NotaKeyDown(this, event);'>".
							"</TD>";
					}
					if ($this->ActivaEdicio==1) {
						echo "<TD width=75>";
						echo "<A HREF=# onclick='RedueixConvocatoria(".$row["NotaId"].",".$row['convocatoria'].");'><IMG SRC=img/left.svg data-toggle='tooltip' data-placement='top' title='Redueix convocatòria'></A>&nbsp;";
						echo "<A HREF=# onclick='AugmentaConvocatoria(".$row["NotaId"].",".$row['convocatoria'].");'><IMG SRC=img/right.svg data-toggle='tooltip' data-placement='top' title='Augmenta convocatòria'></A>&nbsp;";
						echo "<A HREF=# onclick='ConvocatoriaA0(".$row["NotaId"].");'><IMG SRC=img/check.svg data-toggle='tooltip' data-placement='top' title='Convocatòria a 0 (aprovat)'></A>";
						if ($Convalidat)
							echo "<A HREF=# onclick='Desconvalida(".$row["NotaId"].");'>Desconvalida</A>";
						echo "</TD>";
					}
					
					echo "</TR>";
					$j++;
					$row = $ResultSet->fetch_assoc();
				}
				echo "</TABLE>".PHP_EOL;
				echo "<input type=hidden name=TempNota value=''>".PHP_EOL;

				if ($this->Usuari->es_admin || $this->Usuari->es_direccio || $this->Usuari->es_cap_estudis) {
					echo $this->GeneraTaulaEstadistiques();
				}
									
			};	
		
			$ResultSet->close();
		}
		else
			echo 'El butlletí de notes no està disponible.';	
	}

	/**
	 * Crea l'inici de la pàgina HTML.
	 */
	private function CreaInici() {
		CreaIniciHTML($this->Usuari, 'Visualitza expedient');
		echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Matricula.js?v1.5" type="text/javascript"></script>';
		echo '<script language="javascript" src="js/Notes.js?v1.2" type="text/javascript"></script>';
		echo "<DIV id=debug></DIV>";
	}

	/**
	 * Genera la taula de les estadístiques.
	 * @return string Codi HTML de la taula de les estadístiques.
	 */
	private function GeneraTaulaEstadistiques(): string {
		$Retorn = '<DIV name="Total">'.PHP_EOL;
		$Retorn .= '<BR><BR><BR><BR><BR><BR>'.PHP_EOL;
		$Retorn .= '<H3>Resum</H3>';
		$Dades = array(
			'Número UF cicle' => $this->NumeroUFCicle,
			'Número UF totals' => $this->NumeroUFTotals,
			'Número UF aprovades' => $this->NumeroUFAprovades,
			'Hores totals' => $this->HoresTotals,
			'Hores aprovades' => $this->HoresAprovades,
			'Percentatge aprovat' => number_format($this->PercentatgeAprovat, 2).'%',
			'Mitjana' => number_format($this->Mitjana, 2)
		);
		$Retorn .= CreaTaula1($Dades);
//		$Retorn = CreaTaula1T($Dades);
		$Retorn .= '</DIV>'.PHP_EOL;
		return $Retorn;
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
			$pdf->NomComplet = utf8_encodeX(trim($Cognom1Alumne . ' ' . $Cognom2Alumne) . ', ' . $NomAlumne);
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
					$Qualificacions[$i]->Nom = utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"]);
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
				$Qualificacions[$i]->UF[$j]->Nom = utf8_encodeX($row["NomUF"]);
				$Qualificacions[$i]->UF[$j]->Hores = utf8_encodeX($row["HoresUF"]);
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

//		$pdf->Titol2(utf8_decodeX("Comentaris de l'avaluació"));
		$pdf->Titol2("Comentaris de l'avaluació");
		$pdf->EscriuLinia("Sense comentaris");

		$pdf->Titol2("Llegenda");
//		$pdf->EscriuLinia(utf8_decodeX("L'anotació A) identifica les qualificacions corresponents a avaluacions anteriors"));
		$pdf->EscriuLinia("L'anotació A) identifica les qualificacions corresponents a avaluacions anteriors");
		if ($Llei == 'LO')
//			$pdf->EscriuLinia(utf8_decodeX("L'anotació * identifica les qualificacions orientatives"));
			$pdf->EscriuLinia("L'anotació * identifica les qualificacions orientatives");

		// Close and output PDF document
		$Nom = Normalitza(trim($Cognom1Alumne . ' ' . $Cognom2Alumne . ', ' . $NomAlumne));
		// Clean any content of the output buffer
		ob_end_clean();
		$pdf->Output('Expedient '.$Nom.'.pdf', 'I');
	}

	/**
	 * Retorna l'ordre per executar el PHP des de la línia d'ordres depenent del sistema operatiu.
     * @return string Ordre.
	 */
	private function ComandaPHP(): string {
		$Retorn = '';
		if ($this->SistemaOperatiu === Objecte::soWINDOWS)
			$Retorn = UNITAT_XAMPP.':\xampp\php\php.exe';
		else if ($this->SistemaOperatiu === Objecte::soLINUX)
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
				$Nom = utf8_encodeX($row["codi"])."_".
				$Sufix."_".
				utf8_encodeX($row["cognom1"])."_".
				utf8_encodeX($row["cognom2"])."_".
				utf8_encodeX($row["NomAlumne"]);
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
	public function EscriuScript($Curs, $Sufix) {
		echo $this->GeneraScript($Curs, $Sufix);
	}

	private function TextAvaluacio($Avaluacio, $Trimestre) {
		if ($Avaluacio == 'ORD')
			return utf8_decodeX('Ordinària ').Ordinal($Trimestre).' T';
		else if ($Avaluacio == 'EXT')
			return utf8_decodeX('Extraordinària');
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

	/**
	 * Calcula les estadístiques de l'expediant.
	 */
	protected function CalculaEstadistiques() {
		$NumeroUFCicle = 0;
		$NumeroUFTotals = 0;
		$NumeroUFAprovades = 0;
		$HoresTotals = 0;
		$HoresAprovades = 0;
		$HoresMitjana = 0; // Per calcular la mitjana, no podem comptar les hores de la FCT
		$HoresFCT = 0;
		$Mitjana = 0;

		foreach($this->Registre as $row) {
			$NumeroUFTotals++;
			$HoresTotals += $row['HoresUF'];
			$UltimaNota = UltimaNota($row);

			if ($UltimaNota > 0) {
				if ($UltimaNota>=5) {
					$NumeroUFAprovades++;
					$HoresAprovades += $row['HoresUF'];
				}
				if (!$row['es_fct']) {
					$HoresMitjana += $row['HoresUF'];
					$Mitjana += $UltimaNota*$row['HoresUF'];
				}
				else 
					$HoresFCT += $row['HoresUF'];
			}
		}

		// Número d'UF del cicle (per al pla d'estudis corresponent a la matrícula)
		$SQL = '
			SELECT COUNT(*) AS NumeroUFCicle
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			WHERE MPE.cicle_pla_estudi_id=(
				SELECT C.cicle_formatiu_id
				FROM MATRICULA M
				LEFT JOIN CURS C ON (C.curs_id=M.curs_id)
				WHERE matricula_id='.$this->Id.'				
			)		
		';
		$Registre = DB::CarregaRegistreSQL($this->Connexio, $SQL);

		$this->NumeroUFCicle = $Registre->NumeroUFCicle;
		$this->NumeroUFTotals = $NumeroUFTotals;
		$this->NumeroUFAprovades = $NumeroUFAprovades;
		$this->HoresTotals = $HoresTotals;
		$this->HoresAprovades = $HoresAprovades;
		$this->PercentatgeAprovat = ($HoresTotals == 0) ? 0 : 100*$HoresAprovades/$HoresTotals;
		$this->Mitjana = ($HoresMitjana == 0) ? 0 : $Mitjana/$HoresMitjana;
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
	private $MatriculaId = -1; // Cal eliminar!

	/**
	 * Percentatge aprovat d'UF.
	 * @var float
	 */
	private $PercentatgeAprovat = 0.0;

	/**
	 * Objecte matrícula.
	 * @var object
	 */
	private $Matricula = null;

	/**
	 * Objecte professor.
	 * @var object
	 */
	private $Professor = null;

	/**
	 * Registre que conté les mitjanes dels mòduls per a una matrícula.
	 * És un array associatiu amb els següents valors:
	 *  - Clau: Id del mòdul
	 *  - Valor: Registre de la taula NOTES_MP
	 * @var array
	 */
	private $RegistreMitjanes = null;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 * @param objecte $system Dades de l'aplicació.
	 * @param int $MatriculaId Identificador de la matrícula.
	 */
	function __construct($conn, $user, $Sistema, $MatriculaId) {
		parent::__construct($conn, $user, $Sistema);

		$this->Id = $MatriculaId;
		$this->MatriculaId = $MatriculaId; // Cal eliminar!
		$this->Matricula = new Matricula($conn, $user, $Sistema);
		$this->Matricula->Id = $this->Id;
		$this->Matricula->Carrega();
		$this->RegistreMitjanes = [];

		$this->Professor = new Professor($conn, $user, $Sistema);
		$this->Professor->CarregaUFAssignades();
	}

	/**
	 * Genera la SQL per obtenir les dades d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 * @return string Sentència SQL.
	 */
	public function SQLDadesAlumne($MatriculaId): string {
		$SQL = ' SELECT CPE.codi AS CodiCF, CPE.nom AS NomCF, '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.document AS DNI, '.
			' CONCAT(CPE.codi, C.nivell, M.grup) AS Grup, CONCAT(AA.any_inici, "-", AA.any_final) AS AnyAcademic, '.
			' CPE.*, C.*, M.* '.
			' FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (M.curs_id=C.curs_id) '.
			' LEFT JOIN CICLE_PLA_ESTUDI CPE ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) '.
			' LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id) '.
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
	 * Carrega les dades de l'alumne al Registre.
	 */
	private function CarregaDadesAlumne() {
		$this->RegistreAlumne = null;
		$SQL = self::SQLDadesAlumne($this->Id);
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
	 * @param integer $MatriculaId Identificador de la matrícula.
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
	 * @param integer $MatriculaId Identificador de la matrícula.
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
		
		$this->PercentatgeAprovat = $this->CalculaPercentatgeAprovat();

		// Dades alumne
		$Retorn .= '<TABLE style="color:white;" width="450px">';
		$Retorn .= '<TR>';
		$Retorn .= '<TD><B>Cicle Formatiu</B></TD>';
		$Retorn .= '<TD>'.utf8_encodeX($this->RegistreAlumne->nom).'</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD><B>Grup classe</B></TD>';
		$Retorn .= '<TD>'.$this->RegistreAlumne->codi.' '.$this->RegistreAlumne->grup_tutoria.' ('.$this->RegistreAlumne->codi_xtec.')</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD><B>Alumne</TD>';
		$Retorn .= '<TD>'.utf8_encodeX(trim($this->RegistreAlumne->Cognom1Alumne.' '.$this->RegistreAlumne->Cognom2Alumne).', '.$this->RegistreAlumne->NomAlumne).'</TD>';
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
		$av = new Avaluacio($this->Connexio, $this->Usuari, $this->Sistema);
//print_h($this->Matricula);		
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

			$Retorn .= "<a href='$URL' class='btn btn-primary active' role='button' aria-pressed='true' style='width:70px'>";
			$Retorn .= '&lt;&lt;';
			$Retorn .= "</a>&nbsp;";

//			$Retorn .= '<div class="boto" style="width:70px">';
//			$Retorn .= '<a href="'.$URL.'"><img style="display:inline;" src="img/esquerre_tots.gif"></a>';
//			$Retorn .= '</div>';
		}
		$Retorn .= '</td><td>';
		if ($MatriculaPosterior == -1) {
			$Retorn .= '<div style="width:70px;">';
			$Retorn .= '</div>';
		}
		else {
			$URL = GeneraURL("Fitxa.php?accio=ExpedientSaga&Id=$MatriculaPosterior");

			$Retorn .= "<a href='$URL' class='btn btn-primary active' role='button' aria-pressed='true' style='width:70px'>";
			$Retorn .= '&gt;&gt;';
			$Retorn .= "</a>&nbsp;";

//			$Retorn .= '<div class="boto" style="width:70px">';
//			$Retorn .= '<a href="'.$URL.'"><img style="display:inline;" src="img/dreta_tots.gif"></a>';
//			$Retorn .= '</div>';
		}
		$Retorn .= '</td></tr></table>';

		$Retorn .= '</TD>';
		$Retorn .= '<TD style="color:white;font-weight:bold;font-size:large;width:70px;">';
		$Retorn .= $this->PercentatgeAprovat.'%';
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

		$sRetorn .= '<div class="contingut" style="padding-left: 20px; padding-right: 5px; background-color: rgb(141, 164, 160); overflow: auto; height: 500px; border: solid white 0px;" id="content">';

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
				$sRetorn .= '<TD class="llistat3">'.utf8_encodeX($row["CodiMP"]).'</TD>';
				$sRetorn .= '<TD class="llistat3"><b>'.utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"]).'</b></TD>';
				$sRetorn .= '<TD class="llistat3">'.$row["HoresMP"].'</TD>';
				$sRetorn .= $this->CreaCellaNotaModul($row["IdMP"], $i);
				$i++;
				$sRetorn .= '</TR>';
			}
			$ModulAnterior = $row["CodiMP"];
			// Fila corresponent a la UF
			$sRetorn .= "<TR class='tdContingut_00101 Nivell".$row["NivellUF"]."'>";
			$sRetorn .= "<TD class='llistat1'>"."</TD>";
			$sRetorn .= "<TD class='llistat1' width=200>".utf8_encodeX($row["NomUF"])."</TD>";
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
	 * Genera el comentari de l'avaluació de notes de l'expedient.
	 * @return string HTML del comentari.
	 */
	private function GeneraComentari(): string {
		$MatriculaId = $this->MatriculaId;
		$row = DB::CarregaRegistreObj($this->Connexio, 'MATRICULA', 'matricula_id', $MatriculaId);
		$Valor = htmlspecialchars($row->comentari_matricula_seguent ?? '');
		$Events = "onBlur='EnSortirCellaComentari(this);'";

		$sRetorn = PHP_EOL;
		$sRetorn .= '<div><br>'.PHP_EOL;
		$sRetorn .= "	Comentari per a la matriculació del proper curs:<br>".PHP_EOL;
		$sRetorn .= "	<input type=text name=Comentari_$MatriculaId value='$Valor' size=200 $Events>".PHP_EOL;
		$sRetorn .= '</div>'.PHP_EOL;
		return $sRetorn;
	}

	/**
	 * Carrega les UF de 2n d'un determinat cicle formatiu.
	 * @param int $IdCF Identificador del cicle formatiu.
	 * @return array UF de 2n.
	 */
	private function CarregaUFSegon(int $IdCF): array {
		$Retorn = [];
		$SQL = "
			SELECT 
				UPE.unitat_pla_estudi_id AS IdUF, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.orientativa, UPE.nivell AS NivellUF, UPE.data_inici AS DataIniciUF, UPE.data_final AS DataFinalUF,
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores AS HoresMP, 
				CPE.cicle_pla_estudi_id AS IdCF, CPE.nom AS NomCF, CPE.nom AS NomCF, CPE.codi AS CodiCF,
				CF.llei,
				UPE.*, MPE.*, CPE.*
			FROM UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) 
			WHERE CPE.cicle_pla_estudi_id=? AND UPE.nivell=2
			ORDER BY MPE.codi, UPE.codi
		";
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("i", $IdCF);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		if (!$ResultSet)
			throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		if ($ResultSet->num_rows > 0) {
			while($row = $ResultSet->fetch_object()) {
				array_push($Retorn, $row);
			}
		}
		return $Retorn;
	}

	/**
	 * Carrega els valors de les UF de 2n de la proposta de matrícula.
	 * @param int $MatriculaId MatriculaId de la matrícula.
	 * @return array Valors de les UF de 2n.
	 */
	private function CarregaUFSegonValors(int $MatriculaId): array {
		$Retorn = [];
		$SQL = "
			SELECT proposta_matricula_id, matricula_id, unitat_formativa_id, baixa
			FROM PROPOSTA_MATRICULA PM
			WHERE PM.matricula_id=?
		";
		$stmt = $this->Connexio->prepare($SQL);
		$stmt->bind_param("i", $MatriculaId);
		$stmt->execute();
		$ResultSet = $stmt->get_result();
		if (!$ResultSet)
			throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		if ($ResultSet->num_rows > 0) {
			while($row = $ResultSet->fetch_object()) {
				$Retorn[$row->unitat_formativa_id] = $row;
				//array_push($Retorn, $row);
			}
		}
		return $Retorn;
	}

	/**
	 * Compara les UF de 2n si estan donades d'alta a la proposta de matrícula.
	 * @param int $MatriculaId MatriculaId de la matrícula.
	 * @param array $RegistreUFSegon UF de 2n.
	 * @param array $RegistreUFSegonValors UF de 2n de la proposta.
	 * @param float $PercentatgeAprovat Percentatge aprovat d'UF de 1r.
	 */
	private function ComparaRegistreUFSegon(int $MatriculaId, array $RegistreUFSegon, array $RegistreUFSegonValors, float $PercentatgeAprovat) {
		$Baixa = ($PercentatgeAprovat >= 60) ? 0 : 1;
		foreach($RegistreUFSegon as $row) {
			$IdUF = $row->unitat_formativa_id;
			if (!array_key_exists($IdUF, $RegistreUFSegonValors)) {
				$SQL = " INSERT INTO PROPOSTA_MATRICULA (matricula_id, unitat_formativa_id, baixa) VALUES (?, ?, $Baixa) ";
				$stmt = $this->Connexio->prepare($SQL);
				$stmt->bind_param("ii", $MatriculaId, $row->unitat_formativa_id);
				$stmt->execute();				
			}
		}
	}

	/**
	 * Formata els valors de les UF de 2n per nivell MP/UF.
	 * @param array $RegistreUFSegon UF de 2n.
	 * @return array Valors de les UF de 2n formatats per nivell MP/UF.
	 */
	private function FormataUFsegon(array $RegistreUFSegon): array {
		$Retorn = [];
		$ModulAnterior = -1;
		foreach($RegistreUFSegon as $row) {
			if ($row->IdMP != $ModulAnterior) {
				$IdMP = $row->IdMP;
				$Retorn[$IdMP] = new stdClass();
				$Retorn[$IdMP]->Codi = $row->CodiMP;
				$Retorn[$IdMP]->Nom = $row->NomMP;
				$Retorn[$IdMP]->UF = [];
				$ModulAnterior = $IdMP;
			}
			$Retorn[$IdMP]->UF[$row->unitat_formativa_id] = $row;
		}
		return $Retorn;
	}

	/**
	 * Genera la selecció d'UF de 2n per escollir.
	 * @return string HTML del comentari.
	 */
	private function GeneraSeleccioUFSegon(): string {
		$sRetorn = PHP_EOL;
		$sRetorn .= '<div><br>'.PHP_EOL;
		$sRetorn .= "	Proposta d'unitats formatives per cursar a 2n el proper curs:<br>".PHP_EOL;

		$IdCF = $this->Registre[0]['IdCF'];
		$MatriculaId = $this->MatriculaId;
//print_h($IdCF);		
		$RegistreUFSegon = $this->CarregaUFSegon($IdCF);
		$RegistreUFSegonValors = $this->CarregaUFSegonValors($MatriculaId);
		$this->ComparaRegistreUFSegon($MatriculaId, $RegistreUFSegon, $RegistreUFSegonValors, $this->PercentatgeAprovat);
		$RegistreUFSegonValors = $this->CarregaUFSegonValors($MatriculaId); // Tornem a carregar per si no estaven donades d'alta
		$RegistreUFSegon = $this->FormataUFsegon($RegistreUFSegon);

		foreach($RegistreUFSegon as $MP) {
			$sRetorn .= str_repeat('&nbsp', 4)."<b>".$MP->Codi." ".$MP->Nom."</b><br>".PHP_EOL;
			foreach($MP->UF as $row) {
				$Checked = ($RegistreUFSegonValors[$row->unitat_formativa_id]->baixa == 0) ? 'checked' : '';
				$Events = "onClick='EnSortirCasellaUF2n(this);'";
				$sRetorn .= str_repeat('&nbsp', 8)."<input type=checkbox name=chkUF_".$MatriculaId."_".$row->unitat_formativa_id." $Checked $Events>&nbsp;";
				$sRetorn .= $row->CodiUF.' '.$row->NomUF.'<br>'.PHP_EOL;
			}
		}
		$sRetorn .= '</div>'.PHP_EOL;
		return $sRetorn;
	}

	/**
	 * Genera el botó per imprimir la proposta.
	 * @return string HTML del botó.
	 */
	private function GeneraBotoProposta(): string {
		$URL = GeneraURL("Descarrega.php?Accio=PropostaMatriculaPDF&MatriculaId=$this->MatriculaId");
		return '<div id=BotoProposta><br><a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnDescarregaProposta" name="btnDescarregaProposta">Descarrrega proposta</a></div>';
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
		echo '<script language="javascript" src="js/Expedient.js?v1.1" type="text/javascript"></script>';

		echo '<div style="padding-left: 20px; padding-right: 5px; color: white; background-color: rgb(141, 164, 160); height: 650px;" id="content">';
		echo '<div id="dades" style="display: block;">';

		$this->Carrega();
		$this->CarregaMitjanesModuls();
		$this->CarregaDadesAlumne();

		echo $this->GeneraTitol();
		echo $this->GeneraTaula();

		echo '</div>';
		echo '</div>';

		// Proposta de matrícula només per als 1r cursos i la nota és superior o igual al 60%
		if ($this->Registre[0]['NivellUF']==1 && $this->PercentatgeAprovat>=60.0 && $this->PercentatgeAprovat<100.0) {
			echo $this->GeneraComentari();
			echo $this->GeneraSeleccioUFSegon();
			echo $this->GeneraBotoProposta();
		}
		echo $this->GeneraPeu();

		CreaFinalHTML();
	}

	/**
	 * Genera l'expedient en PDF per a un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 */
	public function GeneraPDF($MatriculaId) {
		// De moment només LOE
		$Llei = 'LO';

		$this->Carrega();
		$this->CarregaMitjanesModuls();
		$this->CarregaDadesAlumne();

		// create new PDF document
		$pdf = new PropostaMatriculaPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetTitle('Proposta matrícula');
		$pdf->SetSubject('Proposta matrícula');

		$RA = $this->RegistreAlumne;
		$NomAlumne = $RA->NomAlumne;
		$Cognom1Alumne = $RA->Cognom1Alumne;
		$Cognom2Alumne = $RA->Cognom2Alumne;
		//$Llei = $RA["llei"];
		$pdf->AnyAcademic = $RA->AnyAcademic;
		$pdf->NomComplet = utf8_encodeX(trim($Cognom1Alumne . ' ' . $Cognom2Alumne) . ', ' . $NomAlumne);
		$pdf->DNI = $RA->DNI;
		$pdf->CicleFormatiu = $RA->NomCF;
		$pdf->Grup = $RA->Grup;
		//$pdf->Avaluacio = $this->TextAvaluacio($RA["avaluacio"], $RA["trimestre"]);
		//$pdf->Llei = $Llei;
		$pdf->AddPage(); // Crida al mètode Header

		// Carreguem les notes de les UF
		// Posem les dades del ResultSet en una estructura de dades pròpia
		$Qualificacions = [];
		$i = -1;
		$j = -1;
			
		$ModulAnterior = '';
		foreach($this->Registre as $row) {
			if ($row["CodiMP"] != $ModulAnterior) {
				$i++;
				$Qualificacions[$i] = new stdClass();
				$Qualificacions[$i]->Nom = utf8_encodeX($row["CodiMP"].'. '.$row["NomMP"]);
				$Qualificacions[$i]->Hores = $row["HoresMP"];
//					if (array_key_exists($row["modul_professional_id"], $this->NotesMP))
//						$Qualificacions[$i]->Qualf = NumeroANotaText($this->NotesMP[$row["modul_pla_estudi_id"]]);
//					else
//						$Qualificacions[$i]->Qualf = '';
				$Qualificacions[$i]->Conv = 'Ord.';
				$Qualificacions[$i]->UF = [];
				$j = -1;
			}
			$ModulAnterior = $row["CodiMP"];
			$j++;
			$Qualificacions[$i]->UF[$j] = new stdClass();
			$Qualificacions[$i]->UF[$j]->Nom = utf8_encodeX($row["NomUF"]);
			$Qualificacions[$i]->UF[$j]->Hores = utf8_encodeX($row["HoresUF"]);
			if ($row["Convocatoria"] == 0)
				$Nota = 'A) '.NumeroANotaText(UltimaNota($row));
			else {
				$Nota = NumeroANotaText($row["nota".$row["Convocatoria"]]);
				if ($row["orientativa"])
					$Nota .= ' *';
			}
			$Qualificacions[$i]->UF[$j]->Qualf = $Nota;
			$Qualificacions[$i]->UF[$j]->Conv = Notes::UltimaConvocatoria($row);
		}

		$HTML = '<TABLE>';
		$HTML .= "<TR>";
		if ($Llei == 'LO') {
			// Mòdul professional
			$HTML .= '<TD style="width:50%">';
			$HTML .= "<TABLE>";
			$HTML .= "<TR>";
			$HTML .= '<TD style="width:55%">Mòdul</TD>';
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
			$HTML .= '<TD style="width:55%">Crèdit</TD>';
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

		$pdf->SetY(110);
		$pdf->writeHTML($HTML, True, True);

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

		// Comentaris de l'avaluació
		$pdf->AddPage(); 
		$pdf->Titol2("Comentaris de l'avaluació");
		$pdf->EscriuLinia($this->RegistreAlumne->comentari_matricula_seguent);
		$pdf->Ln(15);

		// Proposta de matrícula
		$pdf->Titol2("Proposta de matrícula");
		$pdf->EscriuLinia("L'equip docent recomana a l'alumne matricular-se de totes les del 1r curs i les següents de 2n:");

		$IdCF = $this->Registre[0]['IdCF'];
		$MatriculaId = $this->MatriculaId;
		$RegistreUFSegon = $this->CarregaUFSegon($IdCF);
		$RegistreUFSegonValors = $this->CarregaUFSegonValors($MatriculaId);
		$RegistreUFSegon = $this->FormataUFsegon($RegistreUFSegon);

		$HTML = '';
		foreach($RegistreUFSegon as $MP) {
			$pdf->EscriuLinia($MP->Codi." ".$MP->Nom);
			foreach($MP->UF as $row) {
				$pdf->SetX(20);
				$pdf->CheckBox('check'.$row->unitat_formativa_id, 5, $RegistreUFSegonValors[$row->unitat_formativa_id]->baixa == 0);
				$pdf->SetX(30);
				$pdf->Cell(50, 0, $row->CodiUF.' '.$row->NomUF, '', 0, 'L'); 
				$pdf->SetY($pdf->GetY() + 6);
			}
		}
		$pdf->Ln(20);

		// Signatures
		$pdf->Titol2("Acceptació de la proposta");
		$HTML .= "<TABLE>";
		$HTML .= "<TR>";
		$HTML .= "<TD>";
		$HTML .= $pdf->NomComplet.'<BR>'.$pdf->DNI = $RA->DNI.'<BR>Signatura';
		$HTML .= "</TD>";
		$HTML .= "<TD>";
		$HTML .= 'Pare, mare o tutor<BR>(menors de 18 anys)<BR>Signatura';
		$HTML .= "</TD>";
		$HTML .= "</TR>";
		$HTML .= "</TABLE>";
		$pdf->writeHTML($HTML, True);		

		// Close and output PDF document
		$Nom = trim($Cognom1Alumne . ' ' . $Cognom2Alumne . ', ' . $NomAlumne);
		// Clean any content of the output buffer
		ob_end_clean();
		$pdf->Output('Proposta_matricula_'.$RA->Grup.'_'.$Nom.'.pdf', 'I');
	}
}

/**
 * Classe que encapsula les utilitats per al maneig de l'acta.
 */
class Acta extends Form
{
	/**
	 * Nivell del curs (1 o 2).
	 * @var int
	 */
	private $NivellCurs = -1;
	/**
	 * Registre de les dades dels alumnes.
	 * @var array
	 */
	private $RegistreAlumnes = [];	

	/**
	 * Registre de les dades del pla d'estudis.
	 * @var array
	 */
	private $RegistrePlaEstudis = [];	

	/**
	 * Registre de les dades dels professors.
	 * @var array
	 */
	private $RegistreProfessors = [];	

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 * @param objecte $user Usuari.
	 */
	function __construct($con, $user) {
		parent::__construct($con, $user);
		$this->Registre = new stdClass();
	}	

	/**
	 * Genera la SQL per obtenir les dades dels alumnes.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria (si n'hi ha).
	 * @return string Sentència SQL.
	 */
	private function SQLDadesAlumne(int $CursId, string $Grup = ''): string {
		$SQL = "
			SELECT 
				M.alumne_id AS AlumneId,
				U.document, U.tipus_document, U.codi as CodiAlumne, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, FormataCognom1Cognom2Nom(U.nom, U.cognom1, U.cognom2) AS Cognom1Cognom2NomAlumne, 
				UPE.unitat_pla_estudi_id, UPE.unitat_formativa_id AS IdUF, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.orientativa AS Orientativa, UPE.nivell AS NivellUF, UPE.es_fct AS FCT, 
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores AS HoresMP, 
				CF.grau, CF.nom AS NomCicleFormatiu, CF.codi_xtec, CF.llei, 
				AA.any_inici, AA.any_final,
				N.notes_id AS NotaId, N.baixa AS BaixaUF, N.convocatoria AS Convocatoria, N.convalidat AS Convalidat, 
				M.matricula_id, M.grup_tutoria AS GrupTutoria, 
				C.curs_id AS IdCurs, C.nivell AS NivellMAT, C.estat AS EstatCurs, C.avaluacio,
				N.* 
			FROM NOTES N 
			LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) 
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id) 
			LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) 
			LEFT JOIN UNITAT_PLA_ESTUDI UPE ON (UPE.unitat_pla_estudi_id=N.uf_id) 
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id)
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) 
			WHERE (M.baixa IS NULL OR M.baixa=0) AND C.curs_id=$CursId
		";
		if ($Grup != '')
			$SQL .= " AND M.grup_tutoria='$Grup' ";
		$SQL .= " ORDER BY U.cognom1, U.cognom2, U.nom, MPE.codi, UPE.codi ";
		return $SQL;
	}

	/**
	 * Genera la SQL per obtenir les notes del mòdul dels alumnes.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria (si n'hi ha).
	 * @return string Sentència SQL.
	 */
	private function SQLNotesModulAlumne(int $CursId, string $Grup = ''): string {
		$SQL = "
			SELECT 
				M.alumne_id AS AlumneId,
				MPE.codi AS CodiMP,
				NMP.nota
			FROM NOTES_MP NMP
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=NMP.modul_professional_id) 
			LEFT JOIN MATRICULA M ON (M.matricula_id=NMP.matricula_id) 
			LEFT JOIN CURS C ON (C.curs_id=M.curs_id) 
			WHERE C.curs_id=$CursId
		";
		if ($Grup != '')
			$SQL .= " AND M.grup_tutoria='$Grup' ";
		$SQL .= " ORDER BY M.alumne_id, MPE.codi ";
		return $SQL;
	}
	
	/**
	 * Genera la SQL per obtenir les dades del pla d'estudis del cicle (mòduls i UF).
	 * @param integer $CursId Identificador del curs.
	 * @return string Sentència SQL.
	 */
	private function SQLPlaEstudis(int $CursId): string {
		$SQL = "
			SELECT 
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores AS HoresMP,
				UPE.unitat_pla_estudi_id, UPE.unitat_formativa_id AS IdUF, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.orientativa AS Orientativa, UPE.nivell AS NivellUF, UPE.es_fct AS FCT
			FROM  UNITAT_PLA_ESTUDI UPE
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id)
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
			LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id)
			WHERE C.curs_id=$CursId
			ORDER BY MPE.codi, UPE.codi			
		";
		return $SQL;
	}
	
	/**
	 * Genera la SQL per obtenir els professors del curs.
	 * @param integer $CursId Identificador del curs.
	 * @return string Sentència SQL.
	 */
	private function SQLProfessors(int $CursId): string {
		$SQL = "
			SELECT
				C.nivell AS NivellCurs, 
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, 
				UPE.unitat_pla_estudi_id AS IdUF, UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.nivell AS NivellUF, UPE.hores AS HoresUF, 
				FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) AS NomCognom1Cognom2, 
				PUF.professor_uf_id AS ProfessorUFId
			FROM UNITAT_PLA_ESTUDI UPE 
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id)
			LEFT JOIN PROFESSOR_UF PUF ON (UPE.unitat_pla_estudi_id=PUF.uf_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PUF.professor_id) 
			WHERE C.nivell=UPE.nivell AND C.curs_id=$CursId
			ORDER BY CPE.codi, MPE.codi, UPE.codi, U.cognom1, U.cognom2, U.nom
		";
		return $SQL;
	}	
	
	private function Nota($row, int $Convocatoria) {
		switch ($Convocatoria) {
			case 1: $Retorn = $row->nota1; break;
			case 2: $Retorn = $row->nota2; break;
			case 3: $Retorn = $row->nota3; break;
			case 4: $Retorn = $row->nota4; break;
			case 5: $Retorn = $row->nota5; break;
			default: $Retorn = ''; break;
		}
		return $Retorn;
	}
	
	private function NotaConvocatoria($row) {
		$Retorn = '';
		if ($row->Convocatoria == 0) {
			if ($row->nota5 != '') 
				$Retorn = $row->nota5;
			else if ($row->nota4 != '') 
				$Retorn = $row->nota4;
			else if ($row->nota3 != '') 
				$Retorn = $row->nota3;
			else if ($row->nota2 != '') 
				$Retorn = $row->nota2;
			else if ($row->nota1 != '') 
				$Retorn = $row->nota1;
			else
				$Retorn = '';
		}
		else
			$Retorn = $this->Nota($row, $row->Convocatoria);
		return $Retorn;
	}
	
	/**
	 * Carrega les dades de la capçalera.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria (si n'hi ha).
	 */
	private function CarregaDades(int $CursId, string $Grup = '') {
		$SQL = "
			SELECT 
				FormataNomCognom1Cognom2(U1.nom, U1.cognom1, U1.cognom2) AS NomTutor,
				FormataNomCognom1Cognom2(U2.nom, U2.cognom1, U2.cognom2) AS NomDirector
			FROM TUTOR T
			LEFT JOIN USUARI U1 ON (T.professor_id=U1.usuari_id) 
			JOIN SISTEMA S
			LEFT JOIN USUARI U2 ON (S.director_id=U2.usuari_id) 
			WHERE T.curs_id=$CursId	
		";
		if ($Grup != '')
			$SQL .= " AND T.grup_tutoria='$Grup' ";

		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR CarregaDades</b>. Causa: ".$e->getMessage());
		}
		
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_object();
			$this->Registre->NomTutor = utf8_encodeX($row->NomTutor);
			$this->Registre->NomDirector = utf8_encodeX($row->NomDirector);
		}
	}
	
	/**
	 * Carrega les dades de l'alumne al Registre.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria (si n'hi ha).
	 */
	private function CarregaDadesAlumne(int $CursId, string $Grup = '') {
		$this->RegistreAlumnes = [];
		$SQL = $this->SQLDadesAlumne($CursId, $Grup);
//print_r($SQL);
//exit;
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR CarregaDadesAlumne</b>. Causa: ".$e->getMessage());
		}
		
		$AlumneId = -1;
		$ModulId = -1;
		$UnitatId = -1;
		while ($row = $ResultSet->fetch_object()) {
			if ($AlumneId == -1) {
				// Primer cop, agafem les dades de la capçalera
				$this->Registre->CursAcademic = $row->any_inici.'/'.$row->any_final;
				$this->Registre->NivellCurs = $row->NivellMAT;
				$this->Registre->Avaluacio = $row->avaluacio;
				$this->Registre->NomCicleFormatiu = utf8_encodeX($row->NomCicleFormatiu);
				$this->Registre->CodiXTEC = $row->codi_xtec;
				$this->Registre->Grau = $row->grau;
				$this->Registre->Llei = $row->llei;
			}
			if ($row->AlumneId != $AlumneId) {
				$Alumne = new stdClass();
				$Alumne->RALC = $row->CodiAlumne;
				$Alumne->Nom = utf8_encodeX($row->Cognom1Cognom2NomAlumne);
				switch ($row->tipus_document) {
					case 'D': $Alumne->TipusDocument = 'DNI'; break;
					case 'N': $Alumne->TipusDocument = 'NIE'; break;
					case 'P': $Alumne->TipusDocument = 'Passaport'; break;
					default: $Alumne->TipusDocument = ''; break;
				}
				$Alumne->DNI = $row->document;
				$Alumne->Moduls = [];
				//array_push($this->RegistreAlumnes, $Alumne);
				$this->RegistreAlumnes[$row->AlumneId] = $Alumne; // Array associatiu
				$AlumneId = $row->AlumneId;
			}
			if ($row->IdMP != $ModulId) {
				$Modul = new stdClass();
				$Modul->Hores = $row->HoresMP;
				$Modul->Nota = '';
				$Modul->Unitats = [];
				$Alumne->Moduls[$row->CodiMP] = $Modul; // Array associatiu
				$ModulId = $row->IdMP;
			}
			if ($row->IdUF != $UnitatId) {
				$Unitat = new stdClass();
				$Unitat->Nota = $this->NotaConvocatoria($row);
				$Unitat->Convocatoria = $row->Convocatoria;
				$Modul->Unitats[$row->CodiUF] = $Unitat; // Array associatiu
				$UnitatId = $row->IdUF;
			}
		}
		$this->CarregaNotesModulAlumne($CursId, $Grup);
//print_h($this->RegistreAlumnes);
//exit;		
	}

	/**
	 * Carrega les notes del mòdul dels alumnes al Registre.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria (si n'hi ha).
	 */
	private function CarregaNotesModulAlumne(int $CursId, string $Grup = '') {
		$SQL = $this->SQLNotesModulAlumne($CursId, $Grup);
//print_r($SQL);
//exit;
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR CarregaDadesAlumne</b>. Causa: ".$e->getMessage());
		}
		
		while ($row = $ResultSet->fetch_object()) {
			if (array_key_exists($row->AlumneId, $this->RegistreAlumnes)) {
				if (array_key_exists($row->CodiMP, $this->RegistreAlumnes[$row->AlumneId]->Moduls)) {
					$this->RegistreAlumnes[$row->AlumneId]->Moduls[$row->CodiMP]->Nota = $row->nota;
				}
			}
		}
	}

	/**
	 * Carrega les dades del pla d'estudis del cicle (mòduls i UF).
	 * @param integer $CursId Identificador del curs.
	 */
	private function CarregaPlaEstudis(int $CursId) {
		$this->RegistrePlaEstudis = [];
		$SQL = $this->SQLPlaEstudis($CursId);
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR CarregaPlaEstudis</b>. Causa: ".$e->getMessage());
		}
		
		$ModulId = -1;
		$UnitatId = -1;
		while ($row = $ResultSet->fetch_object()) {
			if ($row->IdMP != $ModulId) {
				$Modul = new stdClass();
				$Modul->Nom = utf8_encodeX($row->NomMP);
				
//				$Modul->Nom = str_replace("'", "`", $Modul->Nom);
				
				$Modul->Hores = $row->HoresMP;
				$Modul->Unitats = [];
				$this->RegistrePlaEstudis[$row->CodiMP] = $Modul; // Array associatiu
				$ModulId = $row->IdMP;
			}
			if ($row->IdUF != $UnitatId) {
				$Unitat = new stdClass();
				$Unitat->Nom = utf8_encodeX($row->NomUF);

//				$Unitat->Nom = str_replace("'", "`", $Modul->Nom);

				$Unitat->Hores = $row->HoresUF;
				$Modul->Unitats[$row->CodiUF] = $Unitat; // Array associatiu
				$UnitatId = $row->IdUF;
			}
		}
//print_h($this->RegistrePlaEstudis);
//exit;		
	}	
	
	/**
	 * Carrega les dades dels professors.
	 * @param integer $CursId Identificador del curs.
	 */
	private function CarregaProfessors(int $CursId) {
		$this->RegistreProfessors = [];
		$SQL = $this->SQLProfessors($CursId);
		try {
			$ResultSet = $this->Connexio->query($SQL);
			if (!$ResultSet)
				throw new Exception($this->Connexio->error.'.<br>SQL: '.$SQL);
		} catch (Exception $e) {
			die("<BR><b>ERROR CarregaProfessors</b>. Causa: ".$e->getMessage());
		}

		$ModulId = -1;
		$UnitatId = -1;
		while ($row = $ResultSet->fetch_object()) {
			$this->NivellCurs = $row->NivellCurs;
			if ($row->IdMP != $ModulId) {
				$Modul = new stdClass();
				$Modul->Nom = utf8_encodeX($row->NomMP);
				$Modul->Professors = [];
				$this->RegistreProfessors[$row->CodiMP] = $Modul; // Array associatiu
				$ModulId = $row->IdMP;
			}
			$NomCognom1Cognom2 = utf8_encodeX($row->NomCognom1Cognom2);
			if (!in_array($NomCognom1Cognom2, $Modul->Professors))
				array_push($Modul->Professors, $NomCognom1Cognom2);
		}
//print_h($this->RegistreProfessors);
//exit;		
	}	
	
	private function ObteNotaAlumne($ra, $CodiMP, $CodiUF) {
		$Retorn = '';
		if (array_key_exists($CodiMP, $ra->Moduls))
			if (array_key_exists($CodiUF, $ra->Moduls[$CodiMP]->Unitats))
				$Retorn = $ra->Moduls[$CodiMP]->Unitats[$CodiUF]->Nota;
		return $Retorn;
	}

	private function ObteNotaModulAlumne($ra, $CodiMP) {
		$Retorn = '';
		if (array_key_exists($CodiMP, $ra->Moduls))
				$Retorn = $ra->Moduls[$CodiMP]->Nota;
		return $Retorn;
	}

	/**
	 * Indica si l'alumne finalitza el cicle, és a dir, si té totes les UF superades.
	 * @param object $a Alumne.
     * @return bool Cert si l'alumne finalitza el cicle.
	 */
	private function FinalitzaCicle($a): bool {
		$Retorn = True;
		foreach ($this->RegistrePlaEstudis as $CodiMP => $Modul) {
			foreach ($Modul->Unitats as $CodiUF => $Unitat) {
				$Nota = $this->ObteNotaAlumne($a, $CodiMP, $CodiUF);
				if ($Nota < 5 || $Nota == '') 
					$Retorn = False;
			}
		}
		return $Retorn;
	}

	/**
	 * Genera la part de la taula de notes del PDF.
	 * NOTA: El valor dels atributs HTML ha d'anar entre "", sinó no funciona!
	 */
	private function GeneraTaulaNotes($pdf) {
		$pdf->SetFont('helvetica', '', 7);

		$HTML = '';
		$ra = $this->RegistreAlumnes;
		$Amplada = array(30, 65, 100, 60, 30, 60, 30, 60, 30, 60, 30, 60, 30, 60, 30, 60, 30, 60, 30, 40);
		$Titol = array(
			"Núm", 
			"Identificador de l'alumne", 
			"Cognoms i nom / DNI", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Codi - Hores", 
			"Qual.", 
			"Finalitza el cicle"
		);
		
		$pdf->SetY(65);
		$HTML = '';
		$i = 0;
//print_h($ra);		
//exit;
		foreach ($ra as $AlumneId => $a) {
			// Capçalera
			if ($i % 2 == 0) {
				$HTML .= '<TABLE border="1" style="font-family:helvetica;font-size:7;">';
				$HTML .= "<TR>";
				for($j = 0; $j < 20; ++$j) {
					$HTML .= '<TD width="'.($Amplada[$j]).'">'.$Titol[$j].'</TD>';
				}
				$HTML .= "</TR>";
				$HTML .= "</TABLE>";			
			}
			
			$HTML .= '<TABLE border="1" style="font-family:helvetica;font-size:7;">';
			$HTML .= '<TR>';
			
			$HTML .= '<TD rowspan="4" width="'.($Amplada[0]).'">'.($i+1).'</TD>';
			$HTML .= '<TD rowspan="4" width="'.($Amplada[1]).'">'.$a->RALC.'</TD>';
			$HTML .= '<TD rowspan="4" width="'.($Amplada[2]).'">'.$a->Nom.'<BR><BR>'.$a->TipusDocument.': '.$a->DNI.'</TD>';
			
			// Fem els 8 primers mòduls
			$k = 1;
			foreach ($this->RegistrePlaEstudis as $CodiMP => $Modul) {
				if ($k <= 8) {
					$HTML .= '<TD width="'.($Amplada[2*$k+1]).'">'.$CodiMP.'</TD>';
					$HTML .= '<TD width="'.($Amplada[2*$k+2]).'">'.$this->ObteNotaModulAlumne($a, $CodiMP).'</TD>'; 
				}
				$k++;
			}
			
			// Si no arriba a 8, omplim la resta
			for($j = $k; $j <=8; $j++) {
				$HTML .= '<TD rowspan="3" width="'.($Amplada[2*$k+1]).'"></TD>';
				$HTML .= '<TD rowspan="3" width="'.($Amplada[2*$k+2]).'"></TD>';
			}
			
			// TODO: Finalitza el cicle
			$FinalitzaCicle = ($this->FinalitzaCicle($a)) ? 'Sí' : '';
			$HTML .= '<TD rowspan="4" width="'.($Amplada[19]).'">'.$FinalitzaCicle.'</TD>';
			$HTML .= '</TR>';

			// Fem els 8 primers mòduls/UF
			$k = 1;
			$HTML .= '<TR>';
			foreach ($this->RegistrePlaEstudis as $CodiMP => $Modul) {
				if ($k <= 8) {
					$HTML .= '<TD width="'.($Amplada[2*$k+1]).'">';
					foreach ($Modul->Unitats as $CodiUF => $Unitat) {
						$HTML .= $CodiUF.'<BR>';
					}
					$HTML .= '</TD>';
					
					$HTML .= '<TD width="'.($Amplada[2*$k+2]).'">';
					foreach ($Modul->Unitats as $CodiUF => $Unitat) {
						$HTML .= $this->ObteNotaAlumne($a, $CodiMP, $CodiUF).'<BR>';
					}
					$HTML .= '</TD>'; 
				}
				$k++;
			}
			$HTML .= '</TR>';
			
			
//print_h(count($this->RegistrePlaEstudis));	
//exit;		

			if (count($this->RegistrePlaEstudis) > 8) {

				// Fem els 9+ primers mòduls
				$k = 1;
				$HTML .= '<TR>';
				foreach ($this->RegistrePlaEstudis as $CodiMP => $Modul) {
					if ($k >= 9) {
						$HTML .= '<TD width="'.($Amplada[2*$k+1-16]).'">'.$CodiMP.'</TD>';
						$HTML .= '<TD width="'.($Amplada[2*$k+2-16]).'">'.$this->ObteNotaModulAlumne($a, $CodiMP).'</TD>'; 
					}
					$k++;
				}
				$HTML .= '</TR>';

				// Fem els 9+ primers mòduls/UF
				$k = 1;
				$HTML .= '<TR>';
				foreach ($this->RegistrePlaEstudis as $CodiMP => $Modul) {
					if ($k >= 9) {
						$HTML .= '<TD width="'.($Amplada[2*$k+1-16]).'">';
						foreach ($Modul->Unitats as $CodiUF => $Unitat) {
							$HTML .= $CodiUF.'<BR>';
						}
						$HTML .= '</TD>';
						$HTML .= '<TD width="'.($Amplada[2*$k+2-16]).'">';
						foreach ($Modul->Unitats as $CodiUF => $Unitat) {
							$HTML .= $this->ObteNotaAlumne($a, $CodiMP, $CodiUF).'<BR>';
						}
						$HTML .= '</TD>';
					}
					$k++;
				}
				$HTML .= '</TR>';

			}
			else {
				$HTML .= '<TR>';
				foreach ($this->RegistrePlaEstudis as $CodiMP => $Modul) {
					$HTML .= '<TD width="'.($Amplada[2*$k+1]).'"></TD>';
					$HTML .= '<TD width="'.($Amplada[2*$k+2]).'"></TD>';
				}
				$HTML .= '</TR>';
			}
			$HTML .= '</TABLE>';
			
			if (($i % 2 == 1) || ($i+1 == count($ra))) {
				$pdf->writeHTML($HTML, true, false, true, false, '');
				$HTML = '';
				$pdf->AddPage();
				$pdf->SetY(65);
			}
			$i++;
		}
	}
	
	/**
	 * Extreu el número del mòdul a partir del codi.
	 * @param string $Codi Codi del mòdul.
	 * @return string Número del mòdul.
	 */
	private function NumeroModul($Codi) {
		$Codi = str_replace('MP', '', $Codi);
		$Codi = str_replace('C', '', $Codi);
		return $Codi;
	}

	private function CreaCella($Codi, $Nom, $Professor) {
		$Retorn = '';
		$Retorn .= '<TD height="120" style="border-bottom: 1px solid black">';
		$Retorn .= '<TABLE>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD height="100">';
		if ($Codi != '')
			$Retorn .= 'Mòdul '.$this->NumeroModul($Codi).' '.$Codi.'. '.$Nom;
		$Retorn .= '</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '<TR>';
		$Retorn .= '<TD height="20">';
		$Retorn .= $Professor;
		$Retorn .= '</TD>';
		$Retorn .= '</TR>';
		$Retorn .= '</TABLE>';
		$Retorn .= '</TD>';
		return $Retorn;
	}
	
	/**
	 * Genera la part de la taula de signatures dels professors.
	 * NOTA: El valor dels atributs HTML ha d'anar entre "", sinó no funciona!
	 */
	private function GeneraTaulaSignatures($pdf) {
		$pdf->SetFont('helvetica', '', 7);

		$pdf->SetY(65);
//		$pdf->Titol2(utf8_decodeX("Signatures de l'equip docent dels mòduls"), 9);
		$pdf->Titol2("Signatures de l'equip docent dels mòduls", 9);
		
		$HTML = '';
		$i = 0;
//print_h($this->RegistrePlaEstudis);
//print('<hr>');
//print_h($this->RegistreProfessors);
//exit;		
		foreach ($this->RegistrePlaEstudis as $Codi => $Modul) {
//print_h($Codi);
//print_h($Modul);
			
			$Professors = [];
//print_h($Codi);
//print_h($this->RegistreProfessors);
//exit;
//print('<hr>');
			if (array_key_exists($Codi, $this->RegistreProfessors)) {
				$Professors = $this->RegistreProfessors[$Codi]->Professors;
//print('Professor: '.count($Professors));
//print_h($Professors);
//print('<hr>');
			}
			
			if (count($Professors)>0) {
				foreach ($Professors as $Nom) {
					if ($i % 12 == 0) {$HTML .= '<TABLE style="font-family:helvetica;font-size:9;">';}
					if ($i % 4 == 0) {$HTML .= '<TR>';}
					$HTML .= $this->CreaCella($Codi, $Modul->Nom, $Nom);
					if ($i % 4 == 3) {$HTML .= '</TR>';}
					if ($i % 12 == 11) {
						$HTML .= '</TABLE>';
						
//print_h($HTML);
//exit;

						$pdf->writeHTML($HTML, true, false, true, false, '');
//						$pdf->writeHTML($HTML, true);
						$pdf->AddPage();
						$pdf->SetY(65);
						$pdf->Titol2("Signatures de l'equip docent dels mòduls", 9);
						$HTML = '';
					}
					$i++;
				}
			}
			else {
				if ($i % 12 == 0) {$HTML .= '<TABLE style="font-family:helvetica;font-size:9;">';}
				if ($i % 4 == 0) {$HTML .= '<TR>';}
				$HTML .= $this->CreaCella($Codi, $Modul->Nom, 'Nom i cognoms');
				if ($i % 4 == 3) {$HTML .= '</TR>';}
				if ($i % 12 == 11) {
					$HTML .= '</TABLE>';
//print_h($HTML);
						$pdf->writeHTML($HTML, true, false, true, false, '');
						$pdf->AddPage();
						$pdf->SetY(65);
						$pdf->Titol2("Signatures de l'equip docent dels mòduls", 9);
						$HTML = '';
				}
				$i++;				
			}
		}
		while ($i % 4 != 0) {
			$HTML .= $this->CreaCella('', '', '');
			$i++;
		}
		$HTML .= '</TR>';
		$HTML .= '</TABLE>';
//print_h($HTML);
//exit;
		// Pedaç: depèn la casuística, acaba amb 2 </TR>
		$HTML = str_replace('/TR></TR>', '/TR>', $HTML);
		$pdf->writeHTML($HTML, true, false, true, false, '');
	}	
	
	/**
	 * Genera l'acta en PDF per a un grup tutoria.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria ('' si no n'hi ha).
	 * @param string $DataAvaluacio Data de l'avaluació.
	 * @param string $DataImpressio Data de l'impressió.
	 */
	public function GeneraPDF(int $CursId, string $Grup, $DataAvaluacio, $DataImpressio) {
		$this->CarregaDades($CursId, $Grup);
		$this->CarregaDadesAlumne($CursId, $Grup);
		$this->CarregaPlaEstudis($CursId);
		$this->CarregaProfessors($CursId);

		$pdf = new ActaPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetTitle('Acta');
		$pdf->SetSubject('Acta');

		$pdf->CursAcademic = $this->Registre->CursAcademic;
		$pdf->Avaluacio = $this->Registre->Avaluacio;
		$pdf->NomCicleFormatiu = $this->Registre->NomCicleFormatiu;
		$pdf->NivellCurs = $this->Registre->NivellCurs;
		$pdf->CodiXTEC = $this->Registre->CodiXTEC;
		$pdf->Grau = $this->Registre->Grau;
		$pdf->Llei = $this->Registre->Llei;
		$pdf->NomTutor = $this->Registre->NomTutor;
		$pdf->NomDirector = $this->Registre->NomDirector;
		$pdf->DataAvaluacio = $DataAvaluacio;
		$pdf->DataImpressio = $DataImpressio;
		$pdf->AddPage(); // Crida al mètode Header		
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Institut de Palamós');
		$pdf->SetKeywords('INS Palamós, Palamós');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);		

//print("GeneraTaulaNotes");
		$this->GeneraTaulaNotes($pdf);
//print(GeneraTaulaSignatures);
		$this->GeneraTaulaSignatures($pdf);
		
		// Close and output PDF document
		$Nom = 'Acta '.str_replace('/', '-', $this->Registre->CursAcademic).' '.$this->Registre->Avaluacio.' '.$this->Registre->NomCicleFormatiu.' '.Ordinal($this->Registre->NivellCurs);
		$Nom = Normalitza($Nom);		

		// Clean any content of the output buffer
		ob_end_clean();
		$pdf->Output($Nom.'.pdf', 'I');
	}

	/**
	 * Mostra el diàleg demanant les dates abans de general el PDF.
	 * @param integer $CursId Identificador del curs.
	 * @param string $Grup Grup de tutoria (si n'hi ha).
	 */
	public function EscriuHTML(int $CursId, string $Grup = '') {
		CreaIniciHTML($this->Usuari, 'Acta');
		echo '<script language="javascript" src="js/Avaluacio.js?v1.2" type="text/javascript"></script>';
		echo '<form action="Descarrega.php" method="POST">';
		echo $this->CreaAmagat('Accio', bin2hex(Encripta('GeneraActaPDF')));
		echo $this->CreaAmagat('CursId', bin2hex(Encripta($CursId)));
		echo $this->CreaAmagat('Grup', bin2hex(Encripta($Grup)));
		echo '<table>';
		echo '<tr>'.$this->CreaData('data_avaluacio', 'Data avaluació', [Form::offREQUERIT]).'</tr>';
		echo '<tr>'.$this->CreaData('data_impressio', 'Data impressió', [Form::offREQUERIT], date("d/m/Y")).'</tr>';
		echo '</table>';
		echo '<br>';
		echo '<input class="btn btn-primary" type="submit" value="Genera PDF">';
		echo '</form>';
		CreaFinalHTML();		
	}
}

/**
 * Classe per als informes de l'institut en PDF.
 */
class ActaPDF extends DocumentPDF
{
	/**
	* Curs acadèmic.
	* @var string
	*/
	public $CursAcademic = '';

	/**
	* Avaluació.
	* @var string
	*/
	public $Avaluacio = '';

	/**
	* Nom de cicle formatiu.
	* @var string
	*/
	public $NomCicleFormatiu = '';

	/**
	 * Nivell del curs (1 o 2).
	 * @var int
	 */
	public $NivellCurs = -1;	

	/**
	* Codi XTEC del cicle.
	* @var string
	*/
	public $CodiXTEC = '';				

	/**
	* Grau: Bàsic, Mig, Superior, Especialització (GB, GM, GS, CE).
	* @var string
	*/
	public $Grau = ''; 				

	/**
	* Llei.
	* @var string
	*/
	public $Llei = 'LO'; // Per defecte, LOE
	
	/**
	* Nom del tutor.
	* @var string
	*/
	public $NomTutor = '';

	/**
	* Nom del director.
	* @var string
	*/
	public $NomDirector = '';
	
	/**
	* Data de l'avaluació.
	* @var string
	*/
	public $DataAvaluacio = '';

	/**
	* Data de l'impressió.
	* @var string
	*/
	public $DataImpressio = '';

    // Capçalera
    public function Header() {
        // Logo
        $image_file = ROOT.'/img/logo-gencat.jpg';
        $this->Image($image_file, 10, 8, 7, 8, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', '', 12); // Helvetica, 12
		$this->SetXY(20, 10);
        $this->Cell(0, 15, 'Generalitat de Catalunya', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', 'B', 12); // Helvetica, Bold, 12
		$this->SetXY(20, 15);
        $this->Cell(0, 15, "Departament d'Educació", 0, false, 'L', 0, '', 0, false, 'M', 'M');

		$this->GeneraBlocTitol();
		$this->GeneraBlocCicle();
		$this->GeneraBlocCentre();
    }

	private function GeneraBlocTitol() {
		$this->SetY(20);

        $this->SetFont('helvetica', '', 9);
		$this->SetX($this->original_lMargin);
		switch ($this->Grau) {
			case 'GB':
				$Text = '<B>Acta de qualificacions de mòduls i unitats formatives de cicle formatiu de grau bàsic</B>';
				break;
			case 'GM':
				$Text = '<B>Acta de qualificacions de mòduls i unitats formatives de cicle formatiu de grau mitjà</B>';
				break;
			case 'GS':
				$Text = '<B>Acta de qualificacions de mòduls i unitats formatives de cicle formatiu de grau superior</B>';
				break;
			case 'CE':
				$Text = "<B>Acta de qualificacions de mòduls i unitats formatives de curs d'especialització</B>";
				break;
			default:
				$Text = '';
		}
		$this->writeHTML($Text, False);

		$Avaluacio = ($this->Avaluacio == 'ORD') ? 'Ordinària' : 'Extraordinària';
		$this->SetX($this->original_lMargin+150);
		$this->writeHTML('<B>Avaluació:</B>'.$Avaluacio, False);

		$this->SetX($this->original_lMargin+190);
		$this->writeHTML('<B>Curs acadèmic:</B> '.$this->CursAcademic, False);

		$this->SetX($this->original_lMargin+240);
		$Grup = 'CFP'.$this->Grau[1].' '.$this->CodiXTEC;
		$this->writeHTML('<B>Grup:</B> '.$Grup, False);
		
		$this->Linia(1);
		$this->SetY($this->GetY() + 8);
	}
	
	private function GeneraBlocCicle() {
		$this->Titol2("Dades del cicle", 9, 5);
		
        $this->SetFont('helvetica', '', 9); 

		$this->SetX($this->original_lMargin);
		$this->writeHTML('Descripció', False);
		$this->SetX($this->original_lMargin + 210);
		$this->writeHTML('Codi', False);

		$this->SetY($this->GetY() + 5);		
		
		$this->SetX($this->original_lMargin);
		switch ($this->Llei) {
			case 'LO': $Llei = '(LOE)'; break;
			case 'LG': $Llei = '(LOGSE)'; break;
			default: $Llei = ''; break;
		}
		$Codi = 'CFP'.$this->Grau[1].' '.$this->CodiXTEC;
		$Descripcio = $Codi.' '.$this->NomCicleFormatiu.' '.$Llei.' '.Ordinal($this->NivellCurs);
		$this->writeHTML($Descripcio, False);
		$this->SetX($this->original_lMargin + 210);
		$this->writeHTML($Codi, False);		

		$this->Linia();

		$this->SetY($this->GetY() + 8);		
	}

	private function GeneraBlocCentre() {
		$this->Titol2("Dades del centre", 9, 5);
		
        $this->SetFont('helvetica', '', 9);

		$this->SetX($this->original_lMargin);
		$this->writeHTML('Nom', False);
		$this->SetX($this->original_lMargin + 120);
		$this->writeHTML('Codi', False);
		$this->SetX($this->original_lMargin + 150);
		$this->writeHTML('Municipi', False);
		$this->SetX($this->original_lMargin + 210);
		$this->writeHTML("Data sessió d'avaluació", False);

		$this->SetY($this->GetY() + 5);		
		
		$this->SetX($this->original_lMargin);
		$this->writeHTML('Institut de Palamós', False);
		$this->SetX($this->original_lMargin + 120);
		$this->writeHTML('17005352', False);
		$this->SetX($this->original_lMargin + 150);
		$this->writeHTML('Palamós', False);
		$this->SetX($this->original_lMargin + 210);
		$this->writeHTML($this->DataAvaluacio, False);		

		$this->Linia();

		$this->SetY($this->GetY() + 8);		
	}

    // Peu de pàgina
    public function Footer() {
        $this->SetY(-30); // Position at 30 mm from bottom
        $this->SetFont('helvetica', '', 9);
        $this->SetX($this->original_lMargin);
		$this->Escriu('Signatura del tutor', 9);
        $this->SetX($this->original_lMargin + 100);
		$this->Escriu('Segell del centre', 9);
        $this->SetX($this->original_lMargin + 200);
		$this->Escriu('Vistiplau de la directora', 9);

        $this->SetY(-10);
        $this->SetFont('helvetica', '', 9);
        $this->SetX($this->original_lMargin);
		$this->Escriu($this->NomTutor, 9);
        $this->SetX($this->original_lMargin + 200);
		$this->Escriu($this->NomDirector, 9);

		$this->Linia(0.5);

        $this->SetY(-5);
        $this->SetFont('helvetica', '', 8);
        $this->SetX($this->original_lMargin);
		$this->Escriu('Palamós, '.DataATextCatala($this->DataImpressio), 8);
		
        //$this->SetX($this->original_lMargin + 200);
		//$this->Escriu('Pàgina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 8);
		
		//$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, $Text, '', 0, 'L'); 
        $this->Cell(0, 0, $this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

/**
 * Classe per a l'informe de qualificacions en PDF.
 */
class QualificacionsPDF extends DocumentInstitutPDF
{
	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
		// Marge de dalt
		$this->SetMargins(PDF_MARGIN_LEFT, 120, PDF_MARGIN_RIGHT);
	}

	protected function GeneraBlocTitol() {
		$this->Titol1('Informe de qualificacions del curs escolar '.$this->AnyAcademic);
	}

	protected function GeneraBlocSubtitol() {
		$this->Titol2("Qualificacions");

		$HTML = '<TABLE>';
		$HTML .= "<TR>";
		if ($this->Llei == 'LO') {
			// Mòdul professional
			$HTML .= '<TD style="width:50%">';
			$HTML .= "<TABLE>";
			$HTML .= "<TR>";
			$HTML .= '<TD style="width:55%">Mòdul</TD>';
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
			$HTML .= '<TD style="width:55%">Crèdit</TD>';
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
		$this->writeHTML($HTML, True, True);
	}	
}

/**
 * Classe per a la proposta de matrícula en PDF.
 */
class PropostaMatriculaPDF extends DocumentInstitutPDF
{
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, 'Pàgina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }

	protected function GeneraBlocTitol() {
		$this->Titol1('Informe de qualificacions del curs '.$this->AnyAcademic.' i proposta de matrícula');
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
	public $MatriculaId = -1;

	/**
	 * Identificador del curs.
	 * @var integer
	 */
	public $CursId = -1;
	
	/**
	 * Escriu el pla de treball.
	 */
	public function EscriuHTML() {
		CreaIniciHTML($this->Usuari, "Pla de treball");
		$this->Carrega();
		echo $this->GeneraTitol();
		echo $this->GeneraTaula();
		CreaFinalHTML();
	}

	/**
	 * Crea la SQL per al calendari del curs.
	 * @param integer $CursId Identificador del curs.
     * @return string Sentència SQL.
	 */
	private function CreaSQLCurs(int $CursId) {
		$SQL = " 
			SELECT 
				UPE.codi AS CodiUF, UPE.nom AS NomUF, UPE.hores AS HoresUF, UPE.orientativa, 
				UPE.nivell AS NivellUF, UPE.data_inici AS DataIniciUF, UPE.data_final AS DataFinalUF, 
				MPE.modul_pla_estudi_id AS IdMP, MPE.codi AS CodiMP, MPE.nom AS NomMP, MPE.hores AS HoresMP, 
				CPE.nom AS NomCF, CPE.nom AS NomCF, CF.llei, 
				CONCAT(AA.any_inici, '-', AA.any_final) AS AnyAcademic, 
				0 AS nota1, 0 AS nota2, 0 AS nota3, 0 AS nota4, 0 AS nota5, 1 AS convocatoria, 
				UPE.*, MPE.*, CPE.*, C.* 
			FROM UNITAT_PLA_ESTUDI UPE 
			LEFT JOIN MODUL_PLA_ESTUDI MPE ON (MPE.modul_pla_estudi_id=UPE.modul_pla_estudi_id) 
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id) 
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=CPE.cicle_formatiu_id) 
			LEFT JOIN ANY_ACADEMIC AA ON (CPE.any_academic_id=AA.any_academic_id) 
			LEFT JOIN CURS C ON (C.cicle_formatiu_id=CPE.cicle_pla_estudi_id) 
			WHERE C.curs_id=$CursId AND UPE.nivell<=C.nivell
		";
		return $SQL;
	}
	
	/**
	 * Carrega les dades d'una matrícula i les emmagatzema en l'atribut Registre.
	 */
	protected function Carrega() {
//print($this->CursId);
		if ($this->MatriculaId != -1)
			$SQL = Expedient::SQL($this->MatriculaId);
		else
			$SQL = $this->CreaSQLCurs($this->CursId);
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
				$MP->NomMP = utf8_encodeX($row['NomMP']);
				$MP->CriterisAvaluacio = $row['criteris_avaluacio'];
				$MP->UF = [];
				array_push($this->Registre, $MP);
				
				$ModulAnterior = $row['CodiMP'];
			}
			$UF = new stdClass();
			$UF->CodiUF = $row['CodiUF'];
			$UF->NomUF = utf8_encodeX($row['NomUF']);
			$UF->HoresUF = $row['HoresUF'];
			$UF->Convocatoria = $row['convocatoria'];
			$UF->Orientativa = $row['orientativa'];
			$UF->NivellUF = $row['NivellUF'];
			$UF->DataInici = MySQLAData($row['DataIniciUF']);
			$UF->DataFinal = MySQLAData($row['DataFinalUF']);
			$UF->UltimaNota = UltimaNota($row);
			
			array_push($MP->UF, $UF);
		}

		// Repassem totes les UF per indicar els MP superats en començar el curs
		foreach($this->Registre as &$MP) {
			$MP->aprovat = true;
			foreach($MP->UF as $UF) {
//				if ($UF->UltimaNota < 5)
				if ($UF->Convocatoria > 0)
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
	protected function CreaSQLTitol() {
		if ($this->MatriculaId != -1)
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
		else
			return ' SELECT * FROM CURS WHERE curs_id='.$this->CursId;
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
			die("<BR><b>ERROR GeneraTitol</b>. Causa: ".$e->getMessage());
		}
		$Retorn = '';
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			if ($this->MatriculaId != -1) {
				$NomComplet = trim($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"]);
				if ($this->Usuari->es_admin) {
					$NomComplet = $NomComplet." [".$row["usuari_id"]."]";
				}
				$Dades = array(
					'Alumne' => utf8_encodeX($NomComplet),
					'Cicle Formatiu' => utf8_encodeX($row["NomCF"]),
					'Curs' => $row["any_inici"].'-'.$row["any_final"]
				);
				if ($this->Usuari->es_admin)
					$Dades = array("Id" => $row["matricula_id"]) + $Dades;
				$Retorn .= CreaTaula1($Dades);	
			}
			else {
				$Retorn .= '<b>'.CodificaUTF8($row["nom"]).'</b>';
				$Retorn .= '<BR>';
			}
			$Retorn .= '<BR>';
		}

		// Botons
		echo '<span style="float:right;">';
		if ($this->MatriculaId != -1) {
			$URL = GeneraURL('Fitxa.php?accio=PlaTreballCalendari&Id='.$this->MatriculaId);
			echo '<a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnImprimeix">Calendari</a>&nbsp';
		}
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
	
	/**
	 * Extreu el número del mòdul a partir del codi.
	 * @param string $Codi Codi del mòdul.
	 * @return string Número del mòdul.
	 */
	protected function NumeroModul($Codi) {
		$Codi = str_replace('MP', '', $Codi);
		$Codi = str_replace('C', '', $Codi);
		return $Codi;
	}
}

/**
 * Classe que encapsula les utilitats per al maneig del calendari del pla de treball.
 */
class PlaTreballCalendari extends PlaTreball
{
	/**
	 * Escriu el pla de treball.
	 */
	public function EscriuHTML() {
		CreaIniciHTML_JS_CSS(
			$this->Usuari, 
			"Calendari del pla de treball",
			'<script src="vendor/visjs/moment-with-locales.min.js">
			 </script><script src="vendor/visjs/vis-timeline-graph2d.min.js"></script>',
			'<link href="vendor/visjs/vis-timeline-graph2d.min.css" rel="stylesheet" type="text/css"/>'
		);
		$this->Carrega();
		echo $this->GeneraTitol();
		echo $this->GeneraLiniaTemps();
		CreaFinalHTML();
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
			die("<BR><b>ERROR GeneraTitol</b>. Causa: ".$e->getMessage());
		}
		$Retorn = '';
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			if ($this->MatriculaId != -1) {
				$NomComplet = trim($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"]);
				if ($this->Usuari->es_admin) {
					$NomComplet = $NomComplet." [".$row["usuari_id"]."]";
				}
				$Retorn .= '<b>'.CodificaUTF8($NomComplet).'</b>, '.CodificaUTF8($row["NomCF"]).' ('.$row["any_inici"].'-'.$row["any_final"].')';
			}
			else {
				$Retorn .= '<b>'.CodificaUTF8($row["nom"]).'</b>';
				
				// Botons
				$Retorn .= '<span style="float:right;">';
				$URL = GeneraURL('Fitxa.php?accio=PlaTreball&CursId='.$this->CursId);
				$Retorn .= '<a href="'.$URL.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnImprimeix">Pla de treball</a>';
				$Retorn .= '</span>';				
			}
			$Retorn .= '<BR>';
		}
		return $Retorn;
	}	

	/**
	 * @return string Data en codi JavaScript.
	 */
	private function DataJS($Data) {
		if (ComprovaData($Data)) {
			$aData = explode('/', $Data);
			return 'new Date('.$aData[2].','.($aData[1]-1).','.$aData[0].')';
		}
		else 
			return '';
	}

	/**
	 * Genera el calendari de les UF (línia de temps).
	 * @return string HTML de línia de temps.
	 */
	private function GeneraLiniaTemps(): string {
		$Retorn = '<br><div id="visualization"></div>';

		$Retorn .= '<script>';
		$Retorn .= 'var groups = new vis.DataSet([';
		$Grup = '';
//print_h($this->Registre);		
		foreach($this->Registre as $MP) {
			$Grup .= "{id: ".$this->NumeroModul($MP->CodiMP).", content: '".$MP->CodiMP."',";
			$Grup .= "subgroupStack:{'nostack': false, 'stack': true}},"; // Afegir subgrups per controlar la funció "stack" a cada grup.
		}
		$Grup = substr($Grup, 0, -1); // Treiem la darrera coma		
		$Retorn .= $Grup.']);';

		$i = 0;
		$Retorn .= 'var items = new vis.DataSet([';
		$Items = '';
		foreach($this->Registre as $MP) {
			$GrupId = $this->NumeroModul($MP->CodiMP);
			if (!$MP->aprovat) {
				$stack = false; // Declaració del flag d'stacking, si aquest és true es farà el stacking al MP
				foreach($MP->UF as $UF) {
					// Fem un bucle per totes les UFs i busquem si les dates de les UFs són majors o menors, si es així el flag $stacking sera true
					$DataInicial = strtotime(str_replace("/", "-", $UF->DataInici)); // Primer canviem les barres a guionets per obtenir el format DD-MM-YYYY i tot seguit la convertim a temps UNIX (epoch) per calcular-la.
					$DataFinal = strtotime(str_replace("/", "-", $UF->DataFinal));
					foreach($MP->UF as $subUF) {
						if ((strtotime(str_replace("/", "-", $subUF->DataInici)) > $DataInicial) && (strtotime(str_replace("/", "-", $subUF->DataInici)) < $DataFinal)) {
							$stack = True;
							break;
						}
						if ((strtotime(str_replace("/", "-", $subUF->DataFinal)) > $DataInicial) && (strtotime(str_replace("/", "-", $subUF->DataFinal)) < $DataFinal)) {
							$stack = True;
							break;
						}
					}
				}
				foreach($MP->UF as $UF) {
					if (($UF->Convocatoria != 0) && ($UF->DataInici != '') && ($UF->DataFinal != '')) {
						$Items .= "{id: $i, group: $GrupId, content: '".$UF->CodiUF."', start: ".$this->DataJS($UF->DataInici).", end: ".$this->DataJS($UF->DataFinal);
						if ($stack) {
							$Items .= ", subgroup:'stack'";
						} else {
							$Items .= ", subgroup:'nostack'";
						}
						$Items .= "},";
						$i++;
					}
				}				
			}
		}
		$Items = substr($Items, 0, -1); // Treiem la darrera coma		
		$Retorn .= $Items.']);';
		
		// Opcions
		$Retorn .= "
			var options = {
				locale: 'ca_ES',
				stack: true,
				horizontalScroll: true,
				zoomKey: 'ctrlKey',
				editable: false,
				orientation: 'top'
			};
		";

		// Línia del temps
		$Retorn .= "
			var container = document.getElementById('visualization');
			timeline = new vis.Timeline(container, items, groups, options);		
		";		
		$Retorn .= '</script>';
		return $Retorn;
	}
}

?>
