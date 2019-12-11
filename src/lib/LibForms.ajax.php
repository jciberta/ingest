<?php

/** 
 * LibForms.ajax.php
 *
 * Accions AJAX per a la llibreria de formularis.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
}

//print 'AJAX';
//exit;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaula') {
		$cerca = $_REQUEST['cerca'];
		$filtre = $_REQUEST['filtre'];
//print 'Filtre [AJAX]: '.$filtre;
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->FiltreText = $cerca; 
		$frm->Filtre->JSON = $filtre; 
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'OrdenaColumna') {
		$camp = $_REQUEST['camp'];
		$sentit = $_REQUEST['sentit'];
		$cerca = $_REQUEST['cerca'];
		$filtre = $_REQUEST['filtre'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->FiltreText = $cerca; 
		$frm->Filtre->JSON = $filtre; 
		$frm->Ordre = $camp.' '.$sentit; 
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'SuprimeixRegistre') {
		$Taula = $_REQUEST['taula'];
		$ClauPrimaria = $_REQUEST['clau_primaria'];
		$Valor = $_REQUEST['valor'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé

		// Esborrem el registre
		$SQL = 'DELETE FROM '.$Taula.' WHERE '.$ClauPrimaria.'='.$Valor;
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'DesaFitxa') {
		$jsonForm = $_REQUEST['form'];
		if (Config::Debug)		
			print '<br><b>DesaFitxa.jsonForm</b>: '.$jsonForm;
		$data = json_decode($jsonForm);
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
						// Camp text
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= TextAMySQL($Valor->value).', ';
//						if ($Valor->value == '')
//							$sValues .= "NULL, ";
//						else
//							$sValues .= "'".$Valor->value."', ";
						break;
					case 'edd':
						// Camp data
						$sCamps .= substr($Valor->name, 4).", ";
//print '<br>Data: '.$Valor->value;
//							if ComprovaData($Valor->value) 
								$sValues .= DataAMySQL($Valor->value).", ";
//							else
//								throw new Exception('Data no vàlida.');
						break;
					case 'chb':
						// Camp checkbox
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= (($Valor->value == '') || ($Valor->value == 0)) ? '0, ' : '1, ';
						break;
					case 'cmb':
						// Camp combobox (desplegable)
						$sCamps .= substr($Valor->name, 4).", ";
						$sValues .= TextAMySQL($Valor->value).', ';
//print '<BR>Camp: '.$Valor->name . ' <BR> Value: '.$Valor->value . '<BR>';
//print_r($Valor);
//exit;
						break;
					case 'lkh':
						if (substr($Valor->name, -6) != '_camps') {
							// Camp lookup
							$sCamps .= substr($Valor->name, 4).", ";
							$sValues .= ($Valor->value == '') ? "NULL, " : $Valor->value.", ";
							//if ($Valor->value == '')
								//$sValues .= "NULL, ";
							//else
								//$sValues .= "'".$Valor->value."', ";
//print '<BR>Camp: '.$Valor->name . ' <BR> Value: '.$Valor->value . '<BR>';
//print_r($Valor);
						}
						break;
				}
			}
		}
		$sCamps = substr($sCamps, 0, -2);
		$sValues = substr($sValues, 0, -2);
//print '<hr>Camps: '.$sCamps . ' <BR> Values: '.$sValues.'<hr>';
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
//			$aValues = explode(",", Trim($sValues));
			$aValues = CSVAArray(Trim($sValues));
//print_r($aValues);
			for($i=0; $i < count($aCamps); $i++) {
				$SQL .= $aCamps[$i].'='.trim($aValues[$i]).', ';
			}
			$SQL = substr($SQL, 0, -2);
			$SQL .= ' WHERE '.$ClauPrimaria.'='.$Id;
			
		}
		$SQL = utf8_decode($SQL);
		if (Config::Debug)		
			print '<BR><b>SQL</b>: '.$SQL;
		
		try {
			if ($conn->query($SQL)) {
				//throw new Exception($conn->error.'. SQL: '.$SQL);
				print $conn->error.'. SQL: '.$SQL;
			}
		} catch (Exception $e) {
			print "ERROR DesaFitxa. Causa: ".$e->getMessage();
//			exit;
		}		
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