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
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

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
		$frm->FiltreText = utf8_decode($cerca); 
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
		$frm->FiltreText = utf8_decode($cerca); 
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
		$SQL = "DELETE FROM $Taula WHERE $ClauPrimaria='$Valor'";
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'AfegeixDetall') {
		
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
		
/*
		// Esborrem el registre
		$SQL = 'DELETE FROM '.$Taula.' WHERE '.$ClauPrimaria.'='.$Valor;
		$frm->Connexio->query($SQL);*/
		
		
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'DesaFitxa') {
		$jsonForm = $_REQUEST['form'];
//print $jsonForm;		
//exit;
		$frm = new FormFitxa($conn, $Usuari);
//print "Hi";		
//exit;
		print $frm->Desa($jsonForm);
	}
	else if ($_REQUEST['accio'] == 'DesaFitxaDetall') {
		$jsonForm = $_REQUEST['form'];
		$jsonDetalls = $_REQUEST['detalls'];
//print $jsonDetalls;		
//exit;
		$frm = new FormFitxaDetall($conn, $Usuari);
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