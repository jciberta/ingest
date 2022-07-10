<?php

/** 
 * Importa.php
 *
 * Importació de fitxers i d'altres tecnologies (REST, ...).
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibImporta.php');
require_once(ROOT.'/lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

// Paràmetres de la URL
if (!isset($_GET))
	header("Location: Surt.php");
RecuperaGET($_GET);
$accio = $_GET['accio'];

switch ($accio) {
    case "ImportaNotesMoodleServeiWeb":
		CreaIniciHTML($Usuari, "Importació de notes");
		$ImportaNotes = new ImportaNotesMoodleServeiWeb($conn, $Usuari, $Sistema);
		$ImportaNotes->UnitatPlaEstudiId = $_GET['UnitatPlaEstudiId'];
		$ImportaNotes->Importa();
		break;
}

$conn->close(); 

?>
