<?php

/** 
 * FPRecerca.php
 *
 * Formularis de recerques per les taules de FP:
 *  - Famílies
 *  - Cicles formatius
 *  - Mòduls professionals
 *  - Unitats formatives
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

if (empty($_GET))
	$accio = 'Families';
else
	$accio = $_GET['accio'];

// Destruim l'objecte per si estava ja creat.
unset($frm);

switch ($accio) {
    case "Families":
		$frm = new FormRecerca($conn);
		$frm->Titol = 'Famílies';
		$frm->SQL = 'SELECT * FROM FAMILIA_FP';
		$frm->Camps = 'nom';
		$frm->Descripcions = 'Nom';
		$frm->GeneraHTML();
        break;
    case "CiclesFormatius":
		$frm = new FormRecerca($conn);
		$frm->Titol = 'Cicles formatius';
		$frm->SQL = 'SELECT * FROM CICLE_FORMATIU';
		$frm->Camps = 'nom, grau, codi, codi_xtec, familia_id';
		$frm->Descripcions = 'Nom, Grau, Codi, Codi XTEC, Família';
		$frm->GeneraHTML();
        break;
    case "ModulsProfessionals":
		$frm = new FormRecerca($conn);
		$frm->Titol = 'Mòduls professionals';
		$frm->SQL = 'SELECT * FROM MODUL_PROFESSIONAL';
		$frm->Camps = 'nom, codi, hores, hores_setmana, especialitat, cos, cicle_formatiu_id';
		$frm->Descripcions = 'Nom, Codi, Hores, Hores Setmana, Especialitat, Cos, Cicle Formatiu';
		$frm->GeneraHTML();
        break;
    case "UnitatsFormatives":
		$frm = new FormRecerca($conn);
		$frm->Titol = 'Unitats formatives';
		$frm->SQL = 'SELECT * FROM UNITAT_FORMATIVA';
		$frm->Camps = 'nom, codi, hores, modul_professional_id';
		$frm->Descripcions = 'Nom, Codi, Hores, Mòdul professional';
		$frm->GeneraHTML();
        break;
}

?>
