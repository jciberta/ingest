<?php

/** 
 * LibPlaEstudis.ajax.php
 *
 * Accions AJAX per a la llibreria del pla d'estudis.
 *
 * Accés:
 *   - Administrador, direcció, cap d'estudis
 * Accions:
 *   - ActualitzaTaulaAny
 *   - ActualitzaTaulaCicle
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibPlaEstudis.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: ../Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaulaAny') {
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new PlaEstudisAny($conn, $Usuari);
		$frm->AnyAcademicId = $AnyAcademicId;
		print $frm->GeneraAcordio();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaCicle') {
		$CicleFormatiuId = $_REQUEST['cicle_formatiu_id'];
		$frm = new PlaEstudisCicle($conn, $Usuari);
		$frm->CicleFormatiuId = $CicleFormatiuId;
		print $frm->GeneraAcordio();
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