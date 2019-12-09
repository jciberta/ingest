<?php

/** 
 * LibPassword.ajax.php
 *
 * Accions AJAX per a la llibreria de contrasenyes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
//require_once(ROOT.'/lib/LibForms.php');
//require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibDate.php');
require_once(ROOT.'/lib/LibPassword.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'RecuperaPasswordTutor') {
		$dni = $_REQUEST['dni'];
		$dni_tutor = $_REQUEST['dni_tutor'];
		$data_naixement = $_REQUEST['data_naixement'];
		
		// Netegem els caràcters estranys
		$dni = preg_replace('/[^a-zA-Z0-9]/', '', $dni);
		$dni_tutor = preg_replace('/[^a-zA-Z0-9]/', '', $dni_tutor);
		$data_naixement = preg_replace('/[^\/0-9]/', '', $data_naixement);
		
		$Retorn = '';
		if (ComprovaData($data_naixement)) {
			$rc = new RecuperaPasswordTutor($conn);
			$Retorn = $rc->Recupera($dni, $dni_tutor, $data_naixement);
		}
		print $Retorn;
	}
	else if ($_REQUEST['accio'] == 'RecuperaPasswordAlumne') {
		$dni = $_REQUEST['dni'];
		$data_naixement = $_REQUEST['data_naixement'];
		$telefon = $_REQUEST['telefon'];
		$municipi_naixement = $_REQUEST['municipi_naixement'];
		
		// Netegem els caràcters estranys
		$dni = preg_replace('/[^a-zA-Z0-9]/', '', $dni);
		$data_naixement = preg_replace('/[^\/0-9]/', '', $data_naixement);
		$telefon = preg_replace('/[^0-9]/', '', $telefon);
		$municipi_naixement = preg_replace('/[^a-zA-Z0-9]/', '', $municipi_naixement);
		
		$Retorn = '';
		if (ComprovaData($data_naixement) && strlen($telefon)==9) {
			$rc = new RecuperaPasswordAlumne($conn);
			$Retorn = $rc->Recupera($dni, $data_naixement, $telefon, $municipi_naixement);
		}
		print $Retorn;
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