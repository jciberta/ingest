<?php

/** 
 * Descarrega.php
 *
 * Descàrrega de fitxers.
 *
 * Suporta POST i GET.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

// Report all errors except E_NOTICE i E_DEPRECATED
// https://www.php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibNotes.php');
require_once(ROOT.'/lib/LibProgramacioDidactica.php');
require_once(ROOT.'/lib/LibExpedient.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
//print_h($_POST);
	$Accio = (isset($_POST) && array_key_exists('Accio', $_POST)) ? $_POST['Accio'] : '';
	$Accio = Desencripta($Accio);
	switch ($Accio) {
		case "GeneraActaPDF":
			$CursId = Desencripta($_POST['CursId']);
			$Grup = Desencripta($_POST['Grup']);
			$DataAvaluacio = $_POST['edd_data_avaluacio'];
			$DataImpressio = $_POST['edd_data_impressio'];
//echo "$CursId $Grup $DataAvaluacio $DataImpressio";
			$acta = new Acta($conn, $Usuari);
			$acta = $acta->GeneraPDF($CursId, $Grup, $DataAvaluacio, $DataImpressio);
			break;
	}
}
else if ($_SERVER["REQUEST_METHOD"] == "GET") {
	RecuperaGET($_GET);
	$Accio = (isset($_GET) && array_key_exists('Accio', $_GET)) ? $_GET['Accio'] : '';
//print('<B>Accio</B>: '.$Accio.'<BR>');		
	switch ($Accio) {
		case "ExportaCSV":
			$SQL = $_GET['SQL'];
			$SQL = Desencripta($SQL);
	//print('<B>SQL</B>: '.$SQL.'<BR>');		
			$frm = new FormRecerca($conn, $Usuari);
			$frm->ExportaCSV($SQL);
			break;
		case "ExportaXLSX":
			$SQL = $_GET['SQL'];
			$SQL = Desencripta($SQL);
	//print('<B>SQL</B>: '.$SQL.'<BR>');		
			$frm = new FormRecerca($conn, $Usuari);
			$frm->ExportaXLSX($SQL);
			break;
		case "ExportaNotesCSV":
			$CursId = $_GET['CursId'];
			$Notes = new Notes($conn, $Usuari);
			$Notes->ExportaCSV($CursId, Notes::teULTIMA_CONVOCATORIA);
			//$Notes->ExportaCSV($CursId, Notes::teULTIMA_NOTA);
			break;
		case "ExportaProgramacioDidacticaDOCX":
			$ModulId = $_GET['ModulId'];
	//print('<B>SQL</B>: '.$SQL.'<BR>');
			$PD = new ProgramacioDidactica($conn, $Usuari);
			$PD->ExportaDOCX($ModulId);
			break;
		case "ExportaProgramacioDidacticaODT":
			$ModulId = $_GET['ModulId'];
			$PD = new ProgramacioDidactica($conn, $Usuari);
			$PD->ExportaODT($ModulId);
			break;
	}	
}

$conn->close(); 
 
?>