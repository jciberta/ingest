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
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibAvaluacio.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

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
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		$frm = new FormRecerca($conn, $Usuari);
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
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		$frm = new FormRecerca($conn, $Usuari);
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Equips';
		$SQL = ' SELECT EQ.equip_id, AA.any_academic_id AS any_academic_id, AA.nom AS AnyAcademic, '.
			' CASE EQ.tipus '.
			'     WHEN "DP" THEN "Departament" '.
			'     WHEN "ED" THEN "Equip docent" '.
			'     WHEN "CM" THEN "Comissió" '.
			' END AS Tipus, '.
			' EQ.nom AS NomEquip, '.
			' U.usuari_id, U.nom AS NomProfessor, U.cognom1 AS Cognom1Professor, U.cognom2 AS Cognom2Professor, U.username '.
			' FROM EQUIP EQ '.
			' LEFT JOIN ANY_ACADEMIC AA ON (EQ.any_academic_id=AA.any_academic_id) '.
			' LEFT JOIN USUARI U ON (EQ.cap=U.usuari_id) ';
		$frm->SQL = $SQL;
//print('<br><br><br>'.$SQL);		
		$frm->Taula = 'EQUIP';
		$frm->ClauPrimaria = 'equip_id';
		$frm->Camps = 'AnyAcademic, Tipus, NomEquip, NomProfessor, Cognom1Professor, Cognom2Professor';
		$frm->Descripcions = 'Any, Tipus, Equip, Nom, 1r cognom, 2n cognom';
		$frm->PermetEditar = True;
		$frm->URLEdicio = 'Fitxa.php?accio=Equip';
		$frm->PermetAfegir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		$frm->PermetSuprimir = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);

		$frm->AfegeixOpcio('Membres', 'Fitxa.php?accio=EquipProfessors&Id=', '', 'enrolusers.svg');		
		
		$aAnys = ObteCodiValorDesDeSQL($conn, 'SELECT any_academic_id, CONCAT(any_inici,"-",any_final) AS Any FROM ANY_ACADEMIC ORDER BY Any DESC', "any_academic_id", "Any");
		$frm->Filtre->AfegeixLlista('any_academic_id', 'Any', 30, $aAnys[0], $aAnys[1]);
		
		$frm->EscriuHTML();
        break;
    case "HistoricCurs":
		$curs = new Curs($conn, $Usuari);
		$curs->EscriuFormulariRecera();
        break;		
    case "Avaluacio":
		$avaluacio = new Avaluacio($conn, $Usuari);
		$avaluacio->EscriuFormulariRecera();
        break;		
    case "Registre":
		if (!$Usuari->es_admin)
				header("Location: Surt.php");
		$frm = new FormRecerca($conn, $Usuari);
		$frm->AfegeixJavaScript('Inet.js?v1.0');
		$frm->Modalitat = $Modalitat;
		$frm->Titol = 'Registres';
		$SQL = ' SELECT registre_id, usuari_id, nom_usuari, data, ip, seccio, missatge FROM REGISTRE ';
		$frm->SQL = $SQL;
		$frm->Taula = 'REGISTRE';		
		$frm->ClauPrimaria = 'registre_id';
		$frm->Camps = 'usuari_id, nom_usuari, data, ip, seccio, missatge';
		$frm->Descripcions = 'usuari_id, Usuari, Data, IP, Secció, Missatge';

		$frm->AfegeixOpcioAJAX('Mostra dades IP', 'MostraDadesIP', 'ip', [], '', 'help.svg');

		$frm->Filtre->AfegeixLookup('usuari_id', 'Usuari', 100, 'UsuariRecerca.php', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2', [], '', '*');
		$frm->EscriuHTML();
        break;		
}

?>
