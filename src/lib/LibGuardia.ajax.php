<?php

/** 
 * LibGuardia.ajax.php
 *
 * Accions AJAX per a la llibreria de guàrdies.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibGuardia.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

//print 'AJAX';
//exit;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'GeneraProperDia') {
		$Dia = $_REQUEST['dia'];
		$Guardies = $_REQUEST['guardies'];
		
		$Guardia = new Guardia($conn);
		$Previa = $Guardia->GeneraProperDiaPrevia($Dia, $Guardies);
		$Taula = $Guardia->GeneraTaulaDia($Dia, True, $Previa);

		print $Taula;
	}
	else {
		if ($CFG->Debug)
			print "Acció no suportada. Valor de $_POST: ".json_encode($_POST);
		else
			print "Acció no suportada.";
	}
}
else 
    print "ERROR. No hi ha POST o no hi ha acció.";

?>