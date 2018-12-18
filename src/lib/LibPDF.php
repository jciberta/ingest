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

class DocumentPDF extends TCPDF {

	/**
	 * Títol 1 per als expedients de notes.
	 * @param string $Titol Títol a mostrar.
	 */
	public function Titol1($Titol) {
		$this->SetX($this->original_lMargin);
        $this->SetFont('helvetica', 'B', 14); // Helvetica, Bold, 14
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
		$this->SetX($this->original_lMargin);
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, $Titol, 'B', 0, 'L'); // B: Bottom
		$this->SetY($this->GetY() + 10);
	}

	/**
	 * Títol 2 per als expedients de notes.
	 * @param string $Titol Títol a mostrar.
	 */
	public function Titol2($Titol) {
		$this->SetX($this->original_lMargin);
        $this->SetFont('helvetica', 'B', 12); // Helvetica, Bold, 12
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
		$this->SetX($this->original_lMargin);
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, $Titol, 'B', 0, 'L'); // B: Bottom
		$this->SetY($this->GetY() + 10);
	}
}

?>
