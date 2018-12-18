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

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends DocumentPDF {
//class MYPDF extends TCPDF {

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


/*        $this->SetFont('helvetica', 'B', 12); 
		$this->SetXY(30, 30);
        $this->Cell(0, 15, 'Dades del centre', 0, false, 'L', 0, '', 0, false, 'M', 'M');

		// print an ending header line
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
		$this->SetX($this->original_lMargin);
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, 'PROVA', 'B', 0, 'L');
*/
		$this->SetXY(30, 30);
		$this->Titol1('Informe de qualificacions del curs escolar 2018-2019');

		$this->Titol2('Dades del centre');


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
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set font
$pdf->SetFont('dejavusans', '', 10);


// add a page
$pdf->AddPage();

$SQL = Expedient::SQL($alumne);
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	$row = $ResultSet->fetch_assoc();

	$X = 10;
	$Y = 60;
	$pdf->SetXY($X, $Y);
	$Y += 20;
	$pdf->writeHTML('Alumne: <B>'.utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]).'</B>', True);
	$pdf->writeHTML('Cicle: <B>'.utf8_encode($row["NomCF"]).'</B>', True);

	$pdf->SetY($Y);
	$Y += 20;
	$ModulAnterior = '';
	while($row) {
		if ($row["CodiMP"] != $ModulAnterior) {
			$pdf->SetX(10);
			$pdf->writeHTML(utf8_encode($row["CodiMP"].'. '.$row["NomMP"]), False);
			$X = 125;
			$pdf->SetX($X);
			$pdf->writeHTML($row["HoresMP"], True);
			$Y += 20;
		}
		$ModulAnterior = $row["CodiMP"];
		$pdf->SetX(20);
		$pdf->writeHTML(utf8_encode($row["NomUF"]), False);
		$pdf->SetX(125);
		$pdf->writeHTML(utf8_encode($row["HoresUF"]), False);

		// Notes
		$X = 140;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota1"], False);
		$X += 5;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota2"], False);
		$X += 5;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota3"], False);
		$X += 5;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota4"], False);
		$X += 5;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota5"], False);

		$X += 5;
		$pdf->SetX($X);
		if ($row["Convocatoria"] == 0)
			$pdf->writeHTML('A)', False);

		$X += 5;
		$pdf->SetX($X);
		$pdf->writeHTML(Notes::UltimaConvocatoria($row), False);


		$pdf->writeHTML('', True); // Avancem línia

		$row = $ResultSet->fetch_assoc();
	}
}

// Close and output PDF document
$pdf->Output('Expedient.pdf', 'I');
 
?>
