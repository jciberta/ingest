<?php

/** 
 * LibForms.ajax.php
 *
 * Accions AJAX per a la llibreria de formularis.
 *
 * Accés:
 *   - Tothom?
 * Accions:
 *   - ActualitzaTaula
 *   - FiltraQBE
 *   - OrdenaColumna
 *   - SuprimeixRegistre
 *   - DuplicaRegistre
 *   - AfegeixDetall
 *   - DesaFitxa
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibDate.php');

session_start();
if (!isset($_SESSION['usuari_id'])) {
	$Usuari = null;
	$Sistema = null;
//	header("Location: ../Surt.php");
}
else {
	$Usuari = unserialize($_SESSION['USUARI']);
	$Sistema = unserialize($_SESSION['SISTEMA']);
}

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
}

//print 'AJAX';
//exit;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaula') {
		$cerca = $_REQUEST['cerca'];
//print 'Cerca [AJAX]: '.$cerca;
		$filtre = $_REQUEST['filtre'];
//print 'Filtre [AJAX]: '.$filtre;
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->FiltreText = utf8_decodeX($cerca); 
		$frm->Filtre->JSON = $filtre; 
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'FiltraQBE') {
		$params = $_REQUEST['params'];
		parse_str($params, $aParams);
//print_r($aParams);
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		print $frm->GeneraTaula($aParams);
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
		$frm->FiltreText = utf8_decodeX($cerca); 
		$frm->Filtre->JSON = $filtre; 
		$frm->Ordre = $camp.' '.$sentit; 
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'SuprimeixRegistre') {
		if ($Usuari === null)
			header("Location: ../Surt.php");
		$Taula = $_REQUEST['taula'];
		$ClauPrimaria = $_REQUEST['clau_primaria'];
		$Valor = $_REQUEST['valor'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé

		// Esborrem el registre
		$SQL = "DELETE FROM $Taula WHERE $ClauPrimaria='$Valor'";
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'DuplicaRegistre') {
		if ($Usuari === null)
			header("Location: ../Surt.php");
		$Taula = $_REQUEST['taula'];
		$ClauPrimaria = $_REQUEST['clau_primaria'];
		$Valor = $_REQUEST['valor'];
		$CampCopia = $_REQUEST['camp_copia'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		// Dupliquem el registre
		DB::DuplicaRegistre($conn, $Taula, $ClauPrimaria, $Valor, $CampCopia);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'AfegeixDetall') {
		if ($Usuari === null)
			header("Location: ../Surt.php");
		$Taula = $_REQUEST['taula'];
		$ClauPrimaria = $_REQUEST['clau_primaria'];
		
		$CampMestre = $_REQUEST['camp_mestre'];
		$ValorMestre = $_REQUEST['valor_mestre'];
		$CampDetall = $_REQUEST['camp_detall'];
		$ValorDetal = $_REQUEST['valor_detall'];
		
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		
		// Afegim el registre
		// ! Falta: si la clau no és autoincrement
		$SQL = "INSERT INTO $Taula ($CampMestre, $CampDetall) VALUES ($ValorMestre, $ValorDetal)";
		$frm->Connexio->query($SQL);
		
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'DesaFitxa') {
		if ($Usuari === null)
			header("Location: ../Surt.php");
		$jsonForm = $_REQUEST['form'];
//print $jsonForm;		
//exit;

// No funciona a producció! Necessari per a events
//		$FormSerialitzatEncriptat = $_REQUEST['frm'];
//		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
//		$frm = unserialize($FormSerialitzat);
//		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		
		print $frm->Desa($jsonForm);
	}
	else if ($_REQUEST['accio'] == 'DesaFitxaDetall') {
		$jsonForm = $_REQUEST['form'];
		$jsonDetalls = $_REQUEST['detalls'];
//print $jsonDetalls;		
//exit;
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
//		$frm = new FormFitxaDetall($conn, $Usuari, $Sistema);
		print $frm->Desa($jsonForm, $jsonDetalls);
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