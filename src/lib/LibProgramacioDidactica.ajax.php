<?php

/** 
 * LibProgramacioDidactica.ajax.php
 *
 * Accions AJAX per a la programació didàctica.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibProgramacioDidactica.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaulaResultatsAprenentatge') {
		$sCicleFormatiuId = $_REQUEST['cicle_formatiu_id'];
		$frm = new ResultatsAprenentatge($conn, $Usuari);
		$frm->CicleFormatiuId = $sCicleFormatiuId;
		print $frm->GeneraTaula();
	} else if ($_REQUEST['accio'] == 'ActualitzaTaulaContingutsUF') {
		$sCicleFormatiuId = $_REQUEST['cicle_formatiu_id'];
		$frm = new ContingutsUF($conn, $Usuari);
		$frm->CicleFormatiuId = $sCicleFormatiuId;
		print $frm->GeneraTaula();
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