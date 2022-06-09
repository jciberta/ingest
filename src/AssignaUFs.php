<?php

/** 
 * AssignaUFs.php
 *
 * Assignació d'unitats formatives a professors.
 *
 * GET:
 * - accio: {AssignaUF, ProfessorsUF}.
 * - ProfessorId: Id del professor per a l'acció AssignaUF.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibUsuari.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

$Accio = $_GET['accio'];

if ($Accio == 'AssignaUF') {
	// Assigna diferents UF a un professor.
	$ProfessorId = $_GET['ProfessorId'];
//print_r($ProfessorId);
	$frm = new ProfessorsAssignacioUF($conn, $Usuari, $Sistema);
	$frm->ProfessorId = $ProfessorId;
	$frm->EscriuHTML();
}
else if ($Accio == 'GrupAssignaUF') {
	$frm = new GrupProfessorsAssignacioUF($conn, $Usuari, $Sistema);
	$frm->EscriuHTML();
}
else if ($Accio == 'ProfessorsUF') {
	$frm = new ProfessorsUF($conn, $Usuari, $Sistema);
	$frm->EscriuHTML();
}

echo "<DIV id=debug></DIV>";

?>
