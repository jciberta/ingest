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
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

if (empty($_GET))
	$accio = 'Families';
else
	$accio = $_GET['accio'];

// Obtenció de la modalitat del formulari.
$Modalitat = FormRecerca::mfLLISTA;
if (isset($_GET) && array_key_exists('Modalitat', $_GET) && $_GET['Modalitat']=='mfBusca') 
	$Modalitat = FormRecerca::mfBUSCA;

// Destruim l'objecte per si estava ja creat.
unset($frm);

switch ($accio) {
    case "Families":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Famílies';
		$frm->SQL = 'SELECT * FROM FAMILIA_FP';
		$frm->Camps = 'nom';
		$frm->Descripcions = 'Nom';
		$frm->EscriuHTML();
        break;
    case "CiclesFormatius":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Cicles formatius';
		$frm->SQL = ' SELECT CF.cicle_formatiu_id, CF.nom AS NomCF, CF.*, FFP.nom AS NomFFP '.
			' FROM CICLE_FORMATIU CF '.
			' LEFT JOIN FAMILIA_FP FFP ON (FFP.familia_fp_id=CF.familia_fp_id) ';
		$frm->Camps = 'NomCF, grau, codi, codi_xtec, NomFFP';
		$frm->Descripcions = 'Nom, Grau, Codi, Codi XTEC, Família';
		$frm->EscriuHTML();
        break;
    case "ModulsProfessionals":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Mòduls professionals';
		$frm->SQL = 'SELECT MP.modul_professional_id, MP.codi AS codi, MP.nom AS nom, hores, hores_setmana, especialitat, cos, CF.codi AS CodiCF, CF.nom AS NomCF, FFP.nom AS NomFFP '.
			' FROM MODUL_PROFESSIONAL MP '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
			' LEFT JOIN FAMILIA_FP FFP ON (FFP.familia_fp_id=CF.familia_fp_id) ';
		$frm->Camps = 'codi, nom, hores, hores_setmana, especialitat, cos, CodiCF, NomCF, NomFFP';
		$frm->Descripcions = 'Codi, Nom, Hores, Hores Setmana, Especialitat, Cos, Codi, Cicle Formatiu, Família';
		$frm->EscriuHTML();
        break;
    case "UnitatsFormatives":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Unitats formatives';
		$frm->SQL = 'SELECT UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.hores AS HoresUF, MP.codi AS CodiMP, MP.nom AS NomMP, CF.codi AS CodiCF, CF.nom AS NomCF'. 
			' FROM UNITAT_FORMATIVA UF '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) ';
		$frm->Camps = 'CodiUF, NomUF, HoresUF, CodiMP, NomMP, CodiCF, NomCF ';
		$frm->Descripcions = 'Codi, Nom, Hores, Codi, Mòdul professional, Codi, Cicle Formatiu';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'FPFitxa.php?accio=UnitatsFormatives';
		$frm->ClauPrimaria = 'unitat_formativa_id';
		$frm->EscriuHTML();
        break;
}

?>
