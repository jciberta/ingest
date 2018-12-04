<?php

/** 
 * LibFormsAJAX.php
 *
 * Accions AJAX per a la llibreria de formularis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 * @version 1.0
 */
 

require_once('../Config.php');
require_once('LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
}


// print 'AJAX';


if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaula') {
		$SQL = $_REQUEST['sql'];
		$cerca = $_REQUEST['cerca'];
		$camps = $_REQUEST['camps'];
		$descripcions = $_REQUEST['descripcions'];

		//print_r($_REQUEST);
		//exit(1);

		$frm = new FormRecerca($conn);
		$frm->Modalitat = FormRecerca::mfBUSCA;
		$frm->SQL = $SQL;
		$frm->Camps = $camps;
		$frm->Filtre = $cerca;
		$frm->Descripcions = $descripcions;
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'DesaFitxa') {
		$jsonForm = $_REQUEST['form'];
//print 'DesaFitxa.jsonForm: '.$jsonForm;

		$data = json_decode($jsonForm);
//print 'DesaFitxa.data: '.$data;
		$sCamps = '';
		$sValues = '';
		foreach($data as $Valor) {
			if ($Valor->name == 'hid_Taula') 
				$Taula = $Valor->value;
			else if ($Valor->name == 'hid_ClauPrimaria') 
				$ClauPrimaria = $Valor->value;
			else if ($Valor->name == 'hid_AutoIncrement') 
				$AutoIncrement = $Valor->value;
			else if ($Valor->name == 'hid_Id') 
				$Id = $Valor->value;
			else {
				$Tipus = substr($Valor->name, 0, 3);
				switch ($Tipus) {
					case 'edt':
						// Camp de tipus text <INPUT type="text">
						$sCamps .= substr($Valor->name, 4).", ";
						if ($Valor->value == '')
							$sValues .= "NULL, ";
						else
							$sValues .= "'".$Valor->value."', ";
						break;
					case 'chb':
						// Camp de tipus text <INPUT type="checkbox">
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= ($Valor->value == '') ? '0, ' : '1, ';
						break;
				}
			}
		}
		$sCamps = substr($sCamps, 0, -2);
		$sValues = substr($sValues, 0, -2);
//print 'Camps: '.$sCamps . ' <BR> Values: '.$sValues;
		//print 'Id: '.$Id;
		//print 'Id: '.$Id;
		if ($Id == 0) {
			// INSERT
			if ($AutoIncrement) {
				$SQL = "INSERT INTO ".$Taula." (".$sCamps.") VALUES (".$sValues.")";
			}
			else {
				$sCamps = $ClauPrimaria.', '.$sCamps;
				$sValues = '(SELECT MAX('.$ClauPrimaria.')+1 FROM '. $Taula.'), '.$sValues;
				$SQL = "INSERT INTO ".$Taula." (".$sCamps.") SELECT ".$sValues;
			}
		}
		else {
			// UPDATE
			$SQL = "UPDATE ".$Taula." SET ";
			$aCamps = explode(",", TrimXX($sCamps));
			$aValues = explode(",", Trim($sValues));
			for($i=0; $i < count($aCamps); $i++) {
				$SQL .= $aCamps[$i].'='.trim($aValues[$i]).', ';
			}
			$SQL = substr($SQL, 0, -2);
			$SQL .= ' WHERE '.$ClauPrimaria.'='.$Id;
			
		}
		$conn->query($SQL);
print 'SQL: '.$SQL;
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
