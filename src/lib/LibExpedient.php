<?php

/** 
 * LibExpedient.php
 *
 * Llibreria d'utilitats per a l'expedient.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('vendor/TCPDF/tcpdf.php');

/**
 * Classe que encapsula les utilitats per al maneig de l'expedient. 
 */
class Expedient {
	/**
	 * Genera la SQL per obtenir l'expedient d'un alumne.
	 * @param integer $AlumneId Id de l'alumne.
	 * @return string Sentència SQL.
	 */
	public static function SQL($AlumneId) {
		$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, '.
			' MP.codi AS CodiMP, MP.nom AS NomMP, MP.hores AS HoresMP, '.
			' CF.nom AS NomCF, CF.nom AS NomCF, '.
			' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
			' N.notes_id AS NotaId, N.baixa AS Baixa, '.
			' N.nota1 AS Nota1, N.nota2 AS Nota2, N.nota3 AS Nota3, N.nota4 AS Nota4, N.nota5 AS Nota5, N.convocatoria AS Convocatoria, '.
			' UF.*, MP.*, CF.*, N.* '.
			' FROM UNITAT_FORMATIVA UF '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
			' LEFT JOIN MATRICULA M ON (CF.cicle_formatiu_id=M.cicle_formatiu_id) '.
			' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
			' LEFT JOIN NOTES N ON (UF.unitat_formativa_id=N.uf_id AND N.matricula_id=M.matricula_id) '.
			' WHERE CF.cicle_formatiu_id=M.cicle_formatiu_id AND UF.nivell=M.nivell AND M.alumne_id='.$AlumneId;
		return $SQL;
    }
}

/**
 * DescarregaExpedientPDF
 *
 * Descarrega l'expedient de l'alumne en PDF.
 *
 * @param integer $AlumneId Id de l'alumne.
 * @return void.
 */
/*function DescarregaExpedientPDF($AlumneId)
{
	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Institut de Palamós');
	$pdf->SetTitle('Expedient');
	$pdf->SetSubject('TCPDF Tutorial');
	$pdf->SetKeywords('INS Palamós, Palamós, expedient');

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

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
	$pdf->SetFont('times', 'BI', 12);

	// add a page
	$pdf->AddPage();

	// set some text to print
	$txt = "Custom page header and footer are defined by extending the TCPDF class and overriding the Header() and Footer() methods.";

	// print a block of text using Write()
	$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

	// ---------------------------------------------------------

	//Close and output PDF document
	$pdf->Output('Expedient.pdf', 'I');
}*/
 
?>
