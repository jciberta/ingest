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
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibPlaEstudis.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

if (empty($_GET))
	header("Location: Surt.php");

$accio = (array_key_exists('accio', $_GET)) ? $_GET['accio'] : ''; 

// Comprovem que el professor coincideix amb l'usuari de la sessió
$ProfId = (array_key_exists('ProfId', $_GET)) ? $_GET['ProfId'] : '-1'; 
if ($ProfId != $Usuari->usuari_id && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

// Obtenció de la modalitat del formulari
$Modalitat = FormRecerca::mfLLISTA;
if (isset($_GET) && array_key_exists('Modalitat', $_GET) && $_GET['Modalitat']=='mfBusca') 
	$Modalitat = FormRecerca::mfBUSCA;

// Destruim l'objecte per si estava ja creat
unset($frm);

switch ($accio) {
    case "Families":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Famílies';
		$frm->SQL = 'SELECT * FROM FAMILIA_FP';
		$frm->Taula = 'FAMILIA_FP';
		$frm->ClauPrimaria = 'familia_fp_id';
		$frm->Camps = 'nom';
		$frm->Descripcions = 'Nom';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'FPFitxa.php?accio=Families';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->EscriuHTML();
        break;
    case "CiclesFormatius":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Cicles formatius';
		$frm->SQL = ' SELECT CF.cicle_formatiu_id, CF.nom AS NomCF, CF.*, '.
			'   CASE CF.llei '.
			'     WHEN "LO" THEN "LOE" '.
			'     WHEN "LG" THEN "LOGSE" '.
			'   END AS Llei, '.
			' 	FFP.nom AS NomFFP '.
			' FROM CICLE_FORMATIU CF '.
			' LEFT JOIN FAMILIA_FP FFP ON (FFP.familia_fp_id=CF.familia_fp_id) ';
		$frm->Taula = 'CICLE_FORMATIU';
		$frm->ClauPrimaria = 'cicle_formatiu_id';
		$frm->Camps = 'NomCF, grau, codi, codi_xtec, NomFFP, Llei, bool:actiu';
		$frm->Descripcions = 'Nom, Grau, Codi, Codi XTEC, Família, Llei, Actiu';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'FPFitxa.php?accio=CiclesFormatius';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		//$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->EscriuHTML();
        break;
    case "ModulsProfessionals":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Mòduls professionals';
		$frm->SQL = 'SELECT MP.modul_professional_id, MP.codi AS codi, MP.nom AS nom, hores, hores_setmana, MP.actiu, es_fct AS FCT, especialitat, cos, CF.codi AS CodiCF, CF.nom AS NomCF, FFP.nom AS NomFFP '.
			' FROM MODUL_PROFESSIONAL MP '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
			' LEFT JOIN FAMILIA_FP FFP ON (FFP.familia_fp_id=CF.familia_fp_id) ';
		$frm->Taula = 'MODUL_PROFESSIONAL';
		$frm->ClauPrimaria = 'modul_professional_id';
		$frm->Camps = 'codi, nom, hores, hores_setmana, bool:FCT, especialitat, cos, CodiCF, NomCF, NomFFP, bool:actiu';
		$frm->Descripcions = 'Codi, Nom, Hores, Hores Setmana, FCT, Especialitat, Cos, Codi, Cicle Formatiu, Família, Actiu';
		$frm->PermetEditar = ($Usuari->es_admin);
		$frm->URLEdicio = 'FPFitxa.php?accio=ModulsProfessionals';
		$frm->Filtre->AfegeixLlista('CF.codi', 'Cicle', 30, 
			array('', 'APD', 'CAI', 'DAM', 'FIP', 'SMX', 'FPB', 'HBU'), 
			array('Tots', 'APD', 'CAI', 'DAM', 'FIP', 'SMX', 'FPB', 'HBU')
		);
		$frm->EscriuHTML();
        break;
    case "UnitatsFormativesCF":
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Unitats formatives';
		$frm->SQL = 'SELECT UF.unitat_formativa_id, UF.codi AS CodiUF, UF.nom AS NomUF, UF.nivell, UF.hores AS HoresUF, UF.activa, UF.es_fct AS FCT, MP.codi AS CodiMP, MP.nom AS NomMP, CF.codi AS CodiCF, CF.nom AS NomCF'. 
			' FROM UNITAT_FORMATIVA UF '.
			' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
			' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) ';
		$frm->Taula = 'UNITAT_FORMATIVA';
		$frm->ClauPrimaria = 'unitat_formativa_id';
		$frm->Camps = 'CodiUF, NomUF, nivell, HoresUF, bool:FCT, CodiMP, NomMP, CodiCF, NomCF, bool:activa';
		$frm->Descripcions = 'Codi, Nom, Nivell, Hores, FCT, Codi, Mòdul professional, Codi, Cicle Formatiu, Activa';
		$frm->PermetEditar = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->URLEdicio = 'FPFitxa.php?accio=UnitatsFormatives';
		$frm->Filtre->AfegeixLlista('CF.codi', 'Cicle', 30, 
			array('', 'APD', 'CAI', 'DAM', 'FIP', 'SMX', 'FPB', 'HBU'), 
			array('Tots', 'APD', 'CAI', 'DAM', 'FIP', 'SMX', 'FPB', 'HBU')
		);
		$frm->Filtre->AfegeixLlista('UF.nivell', 'Nivell', 30, array('', '1', '2'), array('Tots', '1r', '2n'));
		$frm->EscriuHTML();
        break;
    case "PlaEstudisAny":
		$frm = new PlaEstudisAny($conn, $Usuari);
		$frm->EscriuHTML();
        break;
    case "PlaEstudisCicle":
		$frm = new PlaEstudisCicle($conn, $Usuari);
		$frm->EscriuHTML();
        break;
    case "PlaEstudisUnitat":
		$frm = new PlaEstudisUnitatRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->EscriuHTML();
        break;
}

?>
