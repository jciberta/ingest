<?php

/** 
 * LibUsuari.ajax.php
 *
 * Accions AJAX per a la llibreria d'usuaris.
 *
 * Accés:
 *   - Administrador, direcció, cap d'estudis, professor*
 * Accions:
 *   - BloquejaUsuari
 *   - BaixaMatricula
 *   - ActualitzaTaulaProfessorsUF
 *   - ActualitzaTaulaProfessorsAssignacioUF
 *   - ActualitzaTaulaGrupProfessorsAssignacioUF
 *   - AssignaUF
 *   - AssignaGrup
 *   - AssignaGrupTutoria
 *   - CanviPassword
 *   - ActualitzaTaulaOrla
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCripto.php');
require_once(ROOT.'/lib/LibUsuari.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: ../Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
//if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
//	header("Location: ../Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_REQUEST['accio']))) {
	if ($_REQUEST['accio'] == 'BloquejaUsuari') {
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$Id = $_REQUEST['id'];
		$check = $_REQUEST['check'];
		$cerca = $_REQUEST['cerca'];
		$filtre = $_REQUEST['filtre'];
//print_r('cerca: '.$cerca.'<hr>');		
//print_r('filtre: '.$filtre.'<hr>');		
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->FiltreText = $cerca; 
		$frm->Filtre->JSON = $filtre; 
		// Bloquegem/desbloquegem l'usuari
		$SQL = 'UPDATE USUARI SET usuari_bloquejat='.$check.' WHERE usuari_id='.$Id;
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'BaixaMatricula') {
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$Id = $_REQUEST['id'];
		$cerca = $_REQUEST['cerca'];
		$filtre = $_REQUEST['filtre'];
		$FormSerialitzatEncriptat = $_REQUEST['frm'];
		$FormSerialitzat = Desencripta($FormSerialitzatEncriptat);
		$frm = unserialize($FormSerialitzat);
		$frm->Connexio = $conn; // La connexió MySQL no es serialitza/deserialitza bé
		$frm->FiltreText = $cerca; 
		$frm->Filtre->JSON = $filtre; 
		// Esborrem el registre
		$SQL = 'UPDATE MATRICULA SET baixa=1 WHERE matricula_id='.$Id;
		$frm->Connexio->query($SQL);
		print $frm->GeneraTaula();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaProfessorsUF') {
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new ProfessorsUF($conn, $Usuari, $Sistema);
		$frm->AnyAcademicId = $AnyAcademicId;
		print $frm->GeneraAcordio();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaProfessorsAssignacioUF') {
		$ProfessorId = $_REQUEST['professor_id'];
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new ProfessorsAssignacioUF($conn, $Usuari, $Sistema);
		$frm->ProfessorId = $ProfessorId;
		$frm->AnyAcademicId = $AnyAcademicId;
		print $frm->GeneraAcordio();
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaGrupProfessorsAssignacioUF') {
		$CiclePlaEstudiId = $_REQUEST['codi_cicle_pla_estudi'];
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$frm = new GrupProfessorsAssignacioUF($conn, $Usuari, $Sistema);
		$frm->CiclePlaEstudiId = $CiclePlaEstudiId;
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
	else if ($_REQUEST['accio'] == 'AssignaGrup') {
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$CursId = $_REQUEST['curs'];
		$AlumneId = $_REQUEST['alumne'];
		$Grup = $_REQUEST['grup'];
		$SQL = 'UPDATE MATRICULA SET grup="'.$Grup.'" WHERE curs_id='.$CursId.' AND alumne_id='.$AlumneId;
		$conn->query($SQL);
		print $SQL;
	}
	else if ($_REQUEST['accio'] == 'AssignaGrupTutoria') {
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$CursId = $_REQUEST['curs'];
		$AlumneId = $_REQUEST['alumne'];
		$GrupTutoria = $_REQUEST['grup_tutoria'];
		$SQL = 'UPDATE MATRICULA SET grup_tutoria="'.$GrupTutoria.'" WHERE curs_id='.$CursId.' AND alumne_id='.$AlumneId;
		$conn->query($SQL);
		print $SQL;
	}
	else if ($_REQUEST['accio'] == 'CanviPassword') {
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$UsuariId = $_REQUEST['usuari_id'];
		$Password = $_REQUEST['password'];
		$SQL = "UPDATE USUARI SET password='".password_hash($Password, PASSWORD_DEFAULT)."', imposa_canvi_password=1 WHERE usuari_id=". $UsuariId;
		$conn->query($SQL);
		print 'Contrasenya canviada correctament.';
	}
	else if ($_REQUEST['accio'] == 'ActualitzaTaulaOrla') {
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$AnyAcademicId = $_REQUEST['any_academic_id'];
		$CicleFormatiuId = $_REQUEST['cicle_formatiu_id'];
		$Nivell = $_REQUEST['nivell'];
		$Grup = $_REQUEST['grup'];

		$frm = new Orla($conn, $Usuari, $Sistema);
		$frm->AnyAcademicId = $AnyAcademicId;
		$frm->CicleFormatiuId = $CicleFormatiuId;
		$frm->Nivell = $Nivell;
		$frm->Grup = $Grup;
		//print $AnyAcademicId.', '.$CicleFormatiuId.', '.$Nivell.', '.$Grup;
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