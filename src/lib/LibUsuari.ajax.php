<?php

/** 
 * LibUsuari.ajax.php
 *
 * Accions AJAX per a la llibreria d'usuaris.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibUsuari.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'BloquejaUsuari') {
		$Id = $_REQUEST['id'];
		$check = $_REQUEST['check'];
		$cerca = $_REQUEST['cerca'];
		$filtre = $_REQUEST['filtre'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->Filtre = $cerca; 
		$frm->Filtre->JSON = $filtre; 
		// Bloquegem/desbloquegem l'usuari
		$SQL = 'UPDATE USUARI SET usuari_bloquejat='.$check.' WHERE usuari_id='.$Id;
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'BaixaMatricula') {
		$Id = $_REQUEST['id'];
		$cerca = $_REQUEST['cerca'];
		$filtre = $_REQUEST['filtre'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->Filtre = $cerca; 
		$frm->Filtre->JSON = $filtre; 
		// Esborrem el registre
		$SQL = 'UPDATE MATRICULA SET baixa=1 WHERE matricula_id='.$Id;
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'AssignaGrup') {
		$CursId = $_REQUEST['curs'];
		$AlumneId = $_REQUEST['alumne'];
		$Grup = $_REQUEST['grup'];
		$SQL = 'UPDATE MATRICULA SET grup="'.$Grup.'" WHERE curs_id='.$CursId.' AND alumne_id='.$AlumneId;
		$conn->query($SQL);
		print $SQL;
	}
	else if ($_REQUEST['accio'] == 'AssignaGrupTutoria') {
		$CursId = $_REQUEST['curs'];
		$AlumneId = $_REQUEST['alumne'];
		$GrupTutoria = $_REQUEST['grup_tutoria'];
		$SQL = 'UPDATE MATRICULA SET grup_tutoria="'.$GrupTutoria.'" WHERE curs_id='.$CursId.' AND alumne_id='.$AlumneId;
		$conn->query($SQL);
		print $SQL;
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