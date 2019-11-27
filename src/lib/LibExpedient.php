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
require_once(ROOT.'/lib/LibPDF.php');
require_once(ROOT.'/lib/LibNotes.php');

/**
 * Classe que encapsula les utilitats per al maneig de l'expedient. 
 */
class Expedient 
{
	/**
	* Connexió a la base de dades.
	* @access public 
	* @var object
	*/    
	public $Connexio;

	/**
	 * Constructor de l'objecte.
	 * @param objecte $conn Connexió a la base de dades.
	 */
	function __construct($con) {
		$this->Connexio = $con;
	}	

	/**
	 * Genera la SQL per obtenir l'expedient d'un alumne.
	 * @param integer $MatriculaId Id de la matrícula de l'alumne.
	 * @return string Sentència SQL.
	 */
	public static function SQL($MatriculaId): string {
		$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, UF.orientativa, UF.nivell AS NivellUF, '.
			' MP.codi AS CodiMP, MP.nom AS NomMP, MP.hores AS HoresMP, '.
			' CF.nom AS NomCF, CF.nom AS NomCF, '.
			' U.usuari_id, U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, U.document AS DNI, '.
			' N.notes_id AS NotaId, N.baixa AS Baixa, N.convalidat AS Convalidat, '.
			' N.nota1 AS Nota1, N.nota2 AS Nota2, N.nota3 AS Nota3, N.nota4 AS Nota4, N.nota5 AS Nota5, N.convocatoria AS Convocatoria, '.
			' CONCAT(CF.codi, C.nivell, M.grup) AS Grup, '.
			' UF.*, MP.*, CF.*, N.*, C.* '.
			' FROM UNITAT_FORMATIVA UF '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
			' LEFT JOIN CURS C ON (CF.cicle_formatiu_id=C.cicle_formatiu_id) '.
			' LEFT JOIN MATRICULA M ON (M.curs_id=C.curs_id) '.
			' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN NOTES N ON (UF.unitat_formativa_id=N.uf_id AND N.matricula_id=M.matricula_id) '.
			' WHERE CF.cicle_formatiu_id=C.cicle_formatiu_id AND UF.nivell<=C.nivell AND M.matricula_id='.$MatriculaId;
		return $SQL;
    }

	/**
	 * Indica si el butlletí de notes és visible o no.
	 * @param integer $MatriculaId Id de la matrícula.
	 * @return boolena Cert si el butlletí de notes és visible.
	 */
	public function EsVisibleButlleti(int $MatriculaId): bool {
		$SQL = ' SELECT * FROM MATRICULA M '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' WHERE C.finalitzat=0 '.
			' AND M.matricula_id='.$MatriculaId;
print "<hr>".$SQL."<hr>";
		$bRetorn = False;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$bRetorn = ($row['butlleti_visible'] == 1);
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

		// Posem les dades del ResultSet en una estructura de dades pròpia
		$Qualificacions = [];
		$i = -1;
		$j = -1;
		if ($ResultSet->num_rows > 0) {
			$row = $ResultSet->fetch_assoc();
			$NomAlumne = $row["NomAlumne"];
			$Cognom1Alumne = $row["Cognom1Alumne"];
			$Cognom2Alumne = $row["Cognom2Alumne"];
			$pdf->NomComplet = trim($NomAlumne . ' ' . $Cognom1Alumne . ', ' . $Cognom2Alumne);
			$pdf->DNI = $row["DNI"];
			$pdf->CicleFormatiu = $row["NomCF"];
			$pdf->Grup = $row["Grup"];
//			$pdf->Avaluacio = "?";
			$pdf->Avaluacio = $this->TextAvaluacio($row["avaluacio"], $row["trimestre"]);
			$pdf->AddPage(); // Crida al mètode Header
			$ModulAnterior = '';
			while($row) {
				if ($row["CodiMP"] != $ModulAnterior) {
					$i++;
					$Qualificacions[$i] = new stdClass();
					$Qualificacions[$i]->Nom = utf8_encode($row["CodiMP"].'. '.$row["NomMP"]);
					$Qualificacions[$i]->Hores = $row["HoresMP"];
					$Qualificacions[$i]->Qualf = '*';
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
					$Nota = 'A) '.NumeroANota(UltimaNota($row));
				else {
					$Nota = NumeroANota($row["nota".$row["Convocatoria"]]);
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

		$pdf->Titol2("Comentaris de l'avaluació");
		$pdf->Escriu("Sense comentaris");

		$pdf->Titol2("Llegenda");
		$pdf->Escriu("L'anotació A) identifica les qualificacions corresponents a avaluacions anteriors");
		$pdf->Escriu("L'anotació * identifica les qualificacions orientatives");

		// Close and output PDF document
		$Nom = trim($Cognom1Alumne . ' ' . $Cognom2Alumne . ', ' . $NomAlumne);
		// Clean any content of the output buffer
		ob_end_clean();
		$pdf->Output('Expedient '.$Nom.'.pdf', 'I');
	}

	/**
	 * Genera l'script per a poder generar tots els expedients en PDF d'un curs.
	 * @param integer $Curs Identificador del curs.
	 */
	public function GeneraScript($Curs, $Sufix) {
		$SQL = ' SELECT U.nom AS NomAlumne, U.*, C.* '.
			' FROM USUARI U '.
			' LEFT JOIN MATRICULA M ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN CURS C ON (C.curs_id=M.curs_id) '.
			' WHERE C.curs_id='.$Curs;
		$ResultSet = $this->Connexio->query($SQL);
		if ($ResultSet->num_rows > 0) {
			while ($row = $ResultSet->fetch_array()) {
				echo "php ../ExpedientPDF.php ".$row["usuari_id"]." >pdf/Expedient_".
					utf8_encode($row["codi"])."_".
					$Sufix."_".
					utf8_encode($row["cognom1"])."_".
					utf8_encode($row["cognom2"])."_".
					utf8_encode($row["NomAlumne"]).
					".pdf\n";
			}
		}
		$ResultSet->close();
	}
	
	private function TextAvaluacio($Avaluacio, $Trimestre) {
		if ($Avaluacio == 'ORD')
			return 'Ordinària '.Ordinal($Trimestre).' T';
		else if ($Avaluacio == 'EXT')
			return 'Extraordinària';
		else
			return '';
	}
}

/**
 * Classe per a l'informe de qualificacions en PDF.
 */
class QualificacionsPDF extends DocumentPDF 
{
	/**
	* Nom complet de l'alumne.
	* @access public 
	* @var string
	*/    
	public $NomComplet = '';

	/**
	* DNI l'alumne.
	* @access public 
	* @var string
	*/    
	public $DNI = '';

	/**
	* Nom del cicle formatiu.
	* @access public 
	* @var string
	*/    
	public $CicleFormatiu = '';

	/**
	* Grup del curs de l'alumne.
	* @access public 
	* @var string
	*/    
	public $Grup = '';

	/**
	* Avaluació.
	* @access public 
	* @var string
	*/    
	public $Avaluacio = '';
	
    // Capçalera
    public function Header() {
        // Logo
        $image_file = ROOT.'/img/logo-gencat.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 14); // Helvetica, Bold, 14
		$this->SetXY(30, 15);
        $this->Cell(0, 15, 'Generalitat de Catalunya', 0, false, 'L', 0, '', 0, false, 'M', 'M');
		$this->SetXY(30, 20);
        $this->Cell(0, 15, "Departament d'Ensenyament", 0, false, 'L', 0, '', 0, false, 'M', 'M');

		$this->SetXY(30, 30);
		$this->Titol1('Informe de qualificacions del curs escolar 2018-2019');

		$this->Titol2("Dades del centre");
		$this->Encolumna5("Nom", "", "", "Codi", "Municipi");
		$this->Encolumna5("Institut de Palamós", "", "", "17005352", "Palamós");

		$this->Titol2("Dades de l'alumne");
		$this->Encolumna5("Alumne", "", "DNI", "", "Grup");
		$this->Encolumna5($this->NomComplet, "", $this->DNI, "", $this->Grup);

		$this->Titol2("Dades dels estudis");
		$this->Encolumna5("Cicle formatiu", "", "", "Avaluació", "");
		$this->Encolumna5($this->CicleFormatiu, "", "", $this->Avaluacio, "");

		$this->Titol2("Qualificacions");

		$HTML = '<TABLE>';
		$HTML .= "<TR>";
	
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
        $this->Cell(0, 10, utf8_encode('Pàgina ').$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
 
?>