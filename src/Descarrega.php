<?php

/** 
 * Descarrega.php
 *
 * Descàrrega de fitxers.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

// Report all errors except E_NOTICE
// https://www.php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE);

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
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

RecuperaGET($_GET);

$Accio = (isset($_GET) && array_key_exists('Accio', $_GET)) ? $_GET['Accio'] : '';
//print('<B>Accio</B>: '.$Accio.'<BR>');		

switch ($Accio) {
	case "ExportaCSV":
		$SQL = $_GET['SQL'];
		$SQL = Desencripta($SQL);
print('<B>SQL</B>: '.$SQL.'<BR>');		
		$frm = new FormRecerca($conn, $Usuari);
		$frm->ExportaCSV($SQL);
		break;
	case "ExportaNotesCSV":
		$CursId = $_GET['CursId'];
		$Notes = new Notes($conn, $Usuari);
		$Notes->ExportaCSV($CursId, Notes::teULTIMA_CONVOCATORIA);
		//$Notes->ExportaCSV($CursId, Notes::teULTIMA_NOTA);
		break;
}

$conn->close(); 
 
?>