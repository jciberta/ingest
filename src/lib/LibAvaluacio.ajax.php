<?php

/** 
 * LibAvaluacio.ajax.php
 *
 * Accions AJAX per a la llibreria d'avaluació.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibAvaluacio.php');
//require_once(ROOT.'/lib/LibCripto.php');
//require_once(ROOT.'/lib/LibUsuari.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'PosaEstatCurs') {
		$Id = $_REQUEST['curs_id'];
		$Estat = $_REQUEST['estat'];
		// Canviem l'estat del curs
		$SQL = 'UPDATE CURS SET estat="'.$Estat.'" WHERE curs_id='.$Id;
		/* PENDENT !!!
		if ($Estat == 'J') {
			// Calculem les notes del mòduls que no estan calculades
			$Avaluacio = new Avaluacio($conn, $Usuari);
			$Avaluacio->CalculaNotesModul($Id);
		}*/
		$conn->query($SQL);
		//print $SQL;
	}
	else if ($_REQUEST['accio'] == 'PosaEstatTrimestre') {
		$Id = $_REQUEST['curs_id'];
		$Trimestre = $_REQUEST['trimestre'];
		// Canviem l'estat del curs
		$SQL = 'UPDATE CURS SET trimestre="'.$Trimestre.'" WHERE curs_id='.$Id;
		$conn->query($SQL);
		//print $SQL;
	}
	else if ($_REQUEST['accio'] == 'TancaAvaluacio') {
//print_r($_REQUEST);
		$Id = $_REQUEST['curs_id'];
		$Avaluacio = new Avaluacio($conn, $Usuari);
		$Avaluacio->TancaAvaluacio($Id);
		print $Avaluacio->CreaTaula($Id);
	}
	else if ($_REQUEST['accio'] == 'TancaCurs') {
		$Id = $_REQUEST['curs_id'];
		$Avaluacio = new Avaluacio($conn, $Usuari);
		$Avaluacio->TancaCurs($Id);
		print $Avaluacio->CreaTaula($Id);
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