<?php

/** 
 * ExpedientPDF.php
 *
 * Impressió de l'expedient en PDF.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('vendor/TCPDF/tcpdf.php');
require_once('lib/LibNotes.php');
require_once('lib/LibExpedient.php');
require_once('lib/LibPDF.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

if (!empty($_GET))
	$alumne = $_GET['AlumneId'];
else
	$alumne = -1;

// Si intenta manipular l'usuari des de la URL -> al carrer!
if (($Usuari->es_alumne) && ($Usuari->usuari_id != $alumne))
	header("Location: Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

/**
 * Classe per a l'informe de qualificacions en PDF.
 */
class QualificacionsPDF extends DocumentPDF 
{
    // Capçalera
    public function Header() {
        // Logo
        $image_file = 'img/logo-gencat.jpg';
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
		$this->Encolumna5("...", "", "...", "", "...");

		$this->Titol2("Dades dels estudis");
		$this->Encolumna5("Cicle formatiu", "", "", "Avaluació", "");
		$this->Encolumna5("...", "", "", "...", "");

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

// create new PDF document
$pdf = new QualificacionsPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Institut de Palamós');
$pdf->SetTitle('Expedient');
$pdf->SetSubject('Expedient');
$pdf->SetKeywords('INS Palamós, Palamós, expedient');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, 120, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set font
$pdf->SetFont('helvetica', '', 10);

// add a page
$pdf->AddPage();

$SQL = Expedient::SQL($alumne);
//print_r($SQL);

$ResultSet = $conn->query($SQL);

// Posem les dades del ResultSet en una estructura de dades pròpia
$Qualificacions = [];
$i = -1;
$j = -1;
if ($ResultSet->num_rows > 0) {
	$row = $ResultSet->fetch_assoc();
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
			$Nota = 'A)'.NumeroANota(UltimaNota($row));
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
$pdf->Output('Expedient.pdf', 'I');
 
?>
