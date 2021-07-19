<?php

/** 
 * LibProfessor.ajax.php
 *
 * Accions AJAX per a la llibreria del professor.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibProfessor.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'ActualitzaTaulaProfessorsUF') {
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new ProfessorsUF($conn, $Usuari);
		$frm->AnyAcademicId = $AnyAcademicId;
		print $frm->GeneraAcordio();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaProfessorsAssignacioUF') {
		$ProfessorId = $_REQUEST['professor_id'];
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new ProfessorsAssignacioUF($conn, $Usuari);
		$frm->ProfessorId = $ProfessorId;
		$frm->AnyAcademicId = $AnyAcademicId;
		print $frm->GeneraAcordio();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaGrupProfessorsAssignacioUF') {
		$sCodiCiclePlaEstudi = $_REQUEST['codi_cicle_pla_estudi'];
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new GrupProfessorsAssignacioUF($conn, $Usuari);
		$frm->CodiCiclePlaEstudi = $sCodiCiclePlaEstudi;
		$frm->AnyAcademicId = $AnyAcademicId;
		print $frm->GeneraAcordio();
	}
	else if ($_REQUEST['accio'] == 'AssignaUF') {
		$nom = $_REQUEST['nom'];
		$check = ($_REQUEST['check']=='true');
		$data = explode("_", $nom);
		if ($check) {
			// Assignem UF
			$SQL = 'INSERT INTO PROFESSOR_UF (professor_id, uf_id) VALUES ('.$data[2].', '.$data[1].')';	
			$conn->query($SQL);
			print $SQL;
		}
		else {
			// Desassignem UF
			$SQL = 'DELETE FROM PROFESSOR_UF WHERE professor_id='.$data[2].' AND uf_id='.$data[1];	
			$conn->query($SQL);
			print $SQL;
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