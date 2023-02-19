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
require_once(ROOT.'/lib/LibProgramacioDidactica.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

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
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
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
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
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
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Mòduls professionals';
		$frm->SQL = "SELECT MP.modul_professional_id, MP.codi AS codi, MP.nom AS nom, hores, hores_setmana, MP.actiu, es_fct AS FCT, especialitat, cos, CF.codi AS CodiCF, CF.nom AS NomCF, FFP.nom AS NomFFP
		FROM MODUL_PROFESSIONAL MP
		LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id)
		LEFT JOIN FAMILIA_FP FFP ON (FFP.familia_fp_id=CF.familia_fp_id);";
		$frm->Taula = 'MODUL_PROFESSIONAL';
		$frm->ClauPrimaria = 'modul_professional_id';
		$frm->Camps = 'codi, nom, hores, hores_setmana, bool:FCT, especialitat, cos, CodiCF, NomCF, NomFFP, bool:actiu';
		$frm->Descripcions = 'Codi, Nom, Hores, Hores Setmana, FCT, Especialitat, Cos, Codi, Cicle Formatiu, Família, Actiu';
		$frm->PermetEditar = ($Usuari->es_admin);
		$frm->URLEdicio = 'FPFitxa.php?accio=ModulsProfessionals';
		$aCicles = ObteCodiValorDesDeSQL($conn, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$CicleFormatiuId = $aCicles[0][0]; 
		$frm->Filtre->AfegeixLlista('CF.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);
		$frm->EscriuHTML();
        break;
    case "UnitatsFormativesCF":
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
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
		$aCicles = ObteCodiValorDesDeSQL($conn, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$CicleFormatiuId = $aCicles[0][0]; 
		$frm->Filtre->AfegeixLlista('CF.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);
		$frm->Filtre->AfegeixLlista('UF.nivell', 'Nivell', 30, array('', '1', '2'), array('Tots', '1r', '2n'));
		$frm->EscriuHTML();
        break;
    case "PlaEstudisAny":
		$frm = new PlaEstudisAny($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
    case "PlaEstudisCicle":
		$frm = new PlaEstudisCicle($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
    case "PlaEstudisCicleRecerca":
		$frm = new PlaEstudisCicleRecerca($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
    case "PlaEstudisUnitat":
		$frm = new PlaEstudisUnitatRecerca($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
    case "PlaEstudisModul":
		$FamiliaFPId = (array_key_exists('FamiliaFPId', $_GET)) ? $_GET['FamiliaFPId'] : -1; 
		$MostraTot = (array_key_exists('MostraTot', $_GET)) ? $_GET['MostraTot'] : 0; 
		$frm = new PlaEstudisModulRecerca($conn, $Usuari, $Sistema);
		$frm->FamiliaFPId = $FamiliaFPId;
		$frm->MostraTot = $MostraTot;
		$frm->EscriuHTML();
        break;
    case "ProgramacionsDidactiques":
		$frm = new ProgramacioDidacticaRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->EscriuHTML();
        break;
    case "ResultatsAprenentatge":
		$frm = new ResultatsAprenentatge($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
        break;
	case "ContingutsUF":
		$frm = new ContingutsUF($conn, $Usuari, $Sistema);
		$frm->EscriuHTML();
		break;
	case "PreuMatricula":
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Preus matrícula';
		$frm->SQL = "
			SELECT 
				PM.*,
				CF.nom AS NomCF
			FROM PREU_MATRICULA PM
			LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=PM.cicle_formatiu_id)
		";
		$frm->Taula = 'PREU_MATRICULA';
		$frm->ClauPrimaria = 'preu_matricula_id';
		$frm->Camps = 'NomCF, nivell, nom, preu, numero_uf';
		$frm->Descripcions = 'Cicle, Nivell, Nom, Preu, Número UF';
		$frm->URLEdicio = 'FPFitxa.php?accio=PreuMatricula';
		$frm->PermetEditar = ($Usuari->es_admin);
		$frm->PermetAfegir = ($Usuari->es_admin);
		$frm->PermetSuprimir = ($Usuari->es_admin);
		$aAnys = ObteCodiValorDesDeSQL($conn, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		$aCicles = ObteCodiValorDesDeSQL($conn, 'SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU ORDER BY nom', "cicle_formatiu_id", "nom");
		$CicleFormatiuId = $aCicles[0][0]; 
		$frm->Filtre->AfegeixLlista('CF.cicle_formatiu_id', 'Cicle', 100, $aCicles[0], $aCicles[1]);

		$frm->EscriuHTML();
        break;
	case "BonificacioMatricula":
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Bonificacions matrícula';
		$frm->SQL = "
			SELECT 
				BM.*,
				UF.nom AS NomUF
			FROM BONIFICACIO_MATRICULA BM
			LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=BM.unitat_formativa_id)
		";
		$frm->Taula = 'BONIFICACIO_MATRICULA';
		$frm->ClauPrimaria = 'bonificacio_matricula_id';
		$frm->Camps = 'nom, valor, tipus, NomUF';
		$frm->Descripcions = 'Nom, Valor, Tipus, Unitat formativa';
		$frm->URLEdicio = 'FPFitxa.php?accio=BonificacioMatricula';
		$frm->PermetEditar = ($Usuari->es_admin);
		$frm->PermetAfegir = ($Usuari->es_admin);
		$frm->PermetSuprimir = ($Usuari->es_admin);
		$aAnys = ObteCodiValorDesDeSQL($conn, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		$frm->EscriuHTML();
        break;
}

?>
