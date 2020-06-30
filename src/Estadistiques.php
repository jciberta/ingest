<?php

/** 
 * Estadistiques.php
 *
 * Estadístiques vàries.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibNotes.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

$Accio = (isset($_GET) && array_key_exists('accio', $_GET)) ? $_GET['accio'] : '';

switch ($Accio) {
	case "EstadistiquesNotes":
		$Curs = new Curs($conn, $Usuari);
		echo $Curs->Estadistiques();
		break;
	case "EstadistiquesNotesCurs":
		$CursId = $_GET['CursId'];
		$Curs = new Curs($conn, $Usuari);
		echo $Curs->EstadistiquesCurs($CursId);
		break;
}

$conn->close(); 
 
?>