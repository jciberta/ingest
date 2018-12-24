<?php

/** 
 * LibPDF.php
 *
 * Llibreria d'utilitats per a la impresió en PDF.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('vendor/TCPDF/tcpdf.php');

/**
 * Classe base per a la generació de documents PDF amb mètodes bàsics.
 */
class DocumentPDF extends TCPDF 
{
	/**
	 * FontFamily desada.
	 * @private
	 */
	private $FontFamilyDesada = 'helvetica';

	/**
	 * FontStyle desat.
	 * @private
	 */
	private $FontStyleDesat = '';

	/**
	 * FontSizePt desat.
	 * @private
	 */
	private $FontSizePtDesat = 12;

	/**
	 * Desa els valors de la font actual.
	 */
	private function DesaFont() {
		$this->FontFamilyDesada = $this->FontFamily;
		$this->FontStyleDesat = $this->FontStyle;
		$this->FontSizePtDesat = $this->FontSizePt;
	}

	/**
	 * Restaura els valors desats per a la font.
	 */
	private function RestauraFont() {
		$this->SetFont($this->FontFamilyDesada, $this->FontStyleDesat, $this->FontSizePtDesat);
	}

	/**
	 * Títol 1 per als expedients de notes.
	 * @param string $Titol Títol a mostrar.
	 */
	public function Titol1($Titol) {
		$this->DesaFont();
		$this->SetX($this->original_lMargin);
        $this->SetFont('helvetica', 'B', 14); // Helvetica, Bold, 14
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
		$this->SetX($this->original_lMargin);
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, $Titol, 'B', 0, 'L'); // B: Bottom
		$this->SetY($this->GetY() + 10);
		$this->RestauraFont();
	}

	/**
	 * Títol 2 per als expedients de notes.
	 * @param string $Titol Títol a mostrar.
	 */
	public function Titol2($Titol) {
		$this->DesaFont();
		$this->SetX($this->original_lMargin);
        $this->SetFont('helvetica', 'B', 12); // Helvetica, Bold, 12
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
		$this->SetX($this->original_lMargin);
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, $Titol, 'B', 0, 'L'); // B: Bottom
		$this->SetY($this->GetY() + 8);
		$this->RestauraFont();
	}

	/**
	 * Escriu text per a 5 columnes.
	 * @param string $Text1 Text per a la 1a columna.
	 * @param string $Text2 Text per a la 2a columna.
	 * @param string $Text3 Text per a la 3a columna.
	 * @param string $Text4 Text per a la 4a columna.
	 * @param string $Text5 Text per a la 5a columna.
	 */
	public function Encolumna5($Text1, $Text2 = '', $Text3 = '', $Text4 = '', $Text5 = '') {
//		$Pas = intval(($this->original_rMargin - $this->original_lMargin) / 5);
		$Pas = 38;
		$this->SetX($this->original_lMargin);
        $this->SetFont('helvetica', '', 12); // Helvetica, 12

		$this->SetX($this->original_lMargin);
		$this->writeHTML(utf8_encode($Text1), False);
		$this->SetX($this->original_lMargin + 1*$Pas);
		$this->writeHTML(utf8_encode($Text2), False);
		$this->SetX($this->original_lMargin + 2*$Pas);
		$this->writeHTML(utf8_encode($Text3), False);
		$this->SetX($this->original_lMargin + 3*$Pas);
		$this->writeHTML(utf8_encode($Text4), False);
		$this->SetX($this->original_lMargin + 4*$Pas);
		$this->writeHTML(utf8_encode($Text5), False);

		$this->SetY($this->GetY() + 6);
	}
}

?>
