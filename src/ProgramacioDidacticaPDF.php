<?php

/** 
 * ProgramacioDidacticaPDF.php
 *
 * Impressió de la programació didàctica en PDF per a un mòdul.
 * Es crida des de la línia de comandes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibProgramacioDidactica.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (defined('STDIN')) {
	// Via CLI
	if ($argc == 2) {
		$ModulId = $argv[1];
	}
	else {
		die("Ús: ProgramacioDidacticaPDF.php ModulId\n");
	}
    $PDE = ProgramacioDidacticaPDFFactory::Crea($conn, null, null, $ModulId);
    $PDE->EscriuPDF($ModulId);
}
else {
    die('ERROR: Accés incorrecta');
}

?>