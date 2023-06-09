<?php

/** 
 * Recerca.php
 *
 * Formularis de recerques per les taules generals:
 *  - Any acadèmic
 *  - Equips
 *  - Històric cursos
 *  - Avaluacions
 *  - Registres
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibAvaluacio.php');
require_once(ROOT.'/lib/LibMatricula.php');
require_once(ROOT.'/lib/LibMaterial.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR', 'AD']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

if (empty($_GET))
	header("Location: Surt.php");

$accio = (array_key_exists('accio', $_GET)) ? $_GET['accio'] : ''; 

// Obtenció de la modalitat del formulari
$Modalitat = FormRecerca::mfLLISTA;
if (isset($_GET) && array_key_exists('Modalitat', $_GET) && $_GET['Modalitat']=='mfBusca') 
	$Modalitat = FormRecerca::mfBUSCA;

// Destruim l'objecte per si estava ja creat
unset($frm);

switch ($accio) {
    case "AnyAcademic":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Any acadèmic';
		$frm->SQL = 'SELECT * FROM ANY_ACADEMIC';
		$frm->Taula = 'ANY_ACADEMIC';
		$frm->ClauPrimaria = 'any_academic_id';
		$frm->Camps = 'nom, any_inici, any_final, data_inici, data_final, bool:actual';
		$frm->Descripcions = 'Nom, Any inicial, Any final, Data inici, Data final, Actual';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=AnyAcademic';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->EscriuHTML();
        break;
    case "Equip":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Equips';
		$SQL = ' SELECT EQ.equip_id, AA.any_academic_id AS any_academic_id, AA.nom AS AnyAcademic, '.
			' CASE EQ.tipus '.
			'     WHEN "DP" THEN "Departament" '.
			'     WHEN "ED" THEN "Equip docent" '.
			'     WHEN "CM" THEN "Comissió" '.
			' END AS Tipus, '.
			' EQ.nom AS NomEquip, '.
			' U.usuari_id, U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, U.username, U.email_ins '.
			' FROM EQUIP EQ '.
			' LEFT JOIN ANY_ACADEMIC AA ON (EQ.any_academic_id=AA.any_academic_id) '.
			' LEFT JOIN USUARI U ON (EQ.cap=U.usuari_id) ';
		$frm->SQL = $SQL;
//print('<br><br><br>'.$SQL);		
		$frm->Taula = 'EQUIP';
		$frm->ClauPrimaria = 'equip_id';
		$frm->Camps = 'AnyAcademic, Tipus, NomEquip, NomProfessor, Cognom1Professor, Cognom2Professor, email_ins';
		$frm->Descripcions = 'Any, Tipus, Equip, Nom, 1r cognom, 2n cognom, Email';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Equip';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);

		$frm->AfegeixOpcio('Membres', 'Fitxa.php?accio=EquipProfessors&Id=', '', 'enrolusers.svg');		
		
		$aAnys = ObteCodiValorDesDeSQL($conn, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1], [], $Sistema->any_academic_id);
		
		$frm->EscriuHTML();
        break;
    case "HistoricCurs":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
		$curs = new Curs($conn, $Usuari, $Sistema);
		$curs->EscriuFormulariRecerca();
        break;		
    case "Avaluacio":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
		$avaluacio = new Avaluacio($conn, $Usuari, $Sistema);
		$avaluacio->EscriuFormulariRecerca();
        break;		
    case "Registre":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->AfegeixJavaScript('Inet.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Registres';
		$SQL = ' SELECT registre_id, usuari_id, nom_usuari, data, ip, seccio, missatge FROM REGISTRE ORDER BY registre_id DESC ';
		$frm->SQL = $SQL;
		$frm->Taula = 'REGISTRE';		
		$frm->ClauPrimaria = 'registre_id';
		$frm->Camps = 'usuari_id, nom_usuari, data, ip, seccio, missatge';
		$frm->Descripcions = 'usuari_id, Usuari, Data, IP, Secció, Missatge';
		$frm->MaximRegistres = 20;
		$frm->AfegeixOpcioAJAX('Mostra dades IP', 'MostraDadesIP', 'ip', [], '', 'help.svg');
		$frm->Filtre->AfegeixLookup('usuari_id', 'Usuari', 100, 'UsuariRecerca.php', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2', [], '', '*');
		$frm->EscriuHTML();
        break;		
    case "Festiu":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Festius';
		$SQL = ' SELECT festiu_id, data, motiu FROM FESTIU ';
		$frm->SQL = $SQL;
		$frm->Taula = 'FESTIU';		
		$frm->ClauPrimaria = 'festiu_id';
		$frm->Camps = 'data, motiu';
		$frm->Descripcions = 'Data, Motiu';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Festiu';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->EscriuHTML();
        break;		
    case "Material":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'PR']);
		$Material = new Material($conn, $Usuari, $Sistema);
		$Material->EscriuFormulariRecerca($Modalitat);
        break;		
    case "TipusMaterial":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$TipusMaterial = new TipusMaterial($conn, $Usuari, $Sistema);
		$TipusMaterial->EscriuFormulariRecerca($Modalitat);
        break;		
    case "ReservaMaterial":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$ReservaMaterial = new ReservaMaterial($conn, $Usuari, $Sistema);
		$ReservaMaterial->EscriuFormulariRecerca($Modalitat);
        break;
	case "HistoricPrestecMaterial":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE']);
		$frm = new FormRecerca($conn, $Usuari, $Sistema);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Històric de préstecs de material';
		$SQL = '
			SELECT 
				PM.*, DATEDIFF(data_entrada, data_sortida) AS Dies, 
				FormataNomCognom1Cognom2(U.nom, U.cognom1, U.cognom2) As Usuari, 
				TM.nom AS TipusMaterial, 
				M.codi AS CodiMaterial, M.nom AS NomMaterial
			FROM PRESTEC_MATERIAL PM
			LEFT JOIN MATERIAL M ON (M.material_id=PM.material_id)
			LEFT JOIN TIPUS_MATERIAL TM ON (TM.tipus_material_id=M.tipus_material_id)
			LEFT JOIN USUARI U ON (U.usuari_id=PM.usuari_id)
			ORDER BY data_entrada, data_sortida, M.tipus_material_id, usuari_id		
		';
		$frm->SQL = $SQL;
		$frm->Taula = 'PRESTEC_MATERIAL';		
		$frm->ClauPrimaria = 'prestec_material_id';
		$frm->Camps = 'data_sortida, data_entrada, Dies, TipusMaterial, CodiMaterial, NomMaterial, Usuari';
		$frm->Descripcions = 'Data sortida, Data entrada, Dies, Tipus material, Codi, Material, Usuari';
		$frm->EscriuHTML();
		break;
	case "PropostaMatricula":
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE', 'AD']);
		$frm = new PropostaMatricula($conn, $Usuari, $Sistema);
		$frm->EscriuFormulariRecerca();
		break;
}

?>
