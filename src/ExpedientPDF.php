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
require_once('lib/LibExpedient.php');

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

$Expedient = new Expedient($conn);
$Expedient->GeneraPDF($alumne);
 
?>
