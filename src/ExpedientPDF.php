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

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

if (!empty($_GET))
	$alumne = $_GET['AlumneId'];
else
	$alumne = -1;

// Si intenta manipular l'usuari des de la URL -> al carrer!
if (($Usuari->es_alumne) && ($Usuari->usuari_id != $alumne))
	header("Location: Surt.php");

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

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
        $this->SetFont('helvetica', '', 14); // Helvetica, 14
		$this->SetXY(30, 25);
        $this->Cell(0, 15, utf8_encode("Institut de Palamós"), 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    // Peu de pàgina
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, utf8_encode('Pàgina ').$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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


$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, MP.codi AS CodiMP, MP.nom AS NomMP, CF.nom AS NomCF, '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
	' N.notes_id AS NotaId, N.baixa AS Baixa, '.
	' N.nota1 AS Nota1, N.nota2 AS Nota2, N.nota3 AS Nota3, N.nota4 AS Nota4, N.nota5 AS Nota5, '.
	' UF.*, MP.*, CF.*, N.* '.
	' FROM UNITAT_FORMATIVA UF '.
	' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
	' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
	' LEFT JOIN MATRICULA M ON (CF.cicle_formatiu_id=M.cicle_formatiu_id) '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' LEFT JOIN NOTES N ON (UF.unitat_formativa_id=N.uf_id AND N.matricula_id=M.matricula_id) '.
	' WHERE CF.cicle_formatiu_id=M.cicle_formatiu_id AND UF.nivell=M.nivell AND M.alumne_id='.$alumne;
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	$row = $ResultSet->fetch_assoc();

	$X = 10;
	$Y = 40;
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
			$pdf->writeHTML(utf8_encode($row["CodiMP"].'. '.$row["NomMP"]), True);
			$Y += 20;
		}
		$ModulAnterior = $row["CodiMP"];
		$pdf->SetX(20);
		$pdf->writeHTML(utf8_encode($row["NomUF"]), False);

		// Notes
		$X = 150;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota1"], False);
		$X += 10;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota2"], False);
		$X += 10;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota3"], False);
		$X += 10;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota4"], False);
		$X += 10;
		$pdf->SetX($X);
		$pdf->writeHTML($row["Nota5"], True);

		$row = $ResultSet->fetch_assoc();
	}
}

// Close and output PDF document
$pdf->Output('Expedient.pdf', 'I');
 
?>
