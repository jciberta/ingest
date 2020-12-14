<?php

/** 
 * Fitxa.php
 *
 * Formularis de fitxa per a diferents taules:
 *  - Curs
 *  - Any acadèmic
 *  - Equip
 *  - Tutor
 *  - Expedient acadèmic (visió SAGA)
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibExpedient.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

// Paràmetres de la URL
if (!isset($_GET))
	header("Location: Surt.php");
$accio = $_GET['accio'];

// Destruim l'objecte per si estava ja creat.
unset($frm);

switch ($accio) {
    case "Curs":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Curs';
		$frm->Taula = 'CURS';
		$frm->ClauPrimaria = 'curs_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT any_academic_id, nom FROM ANY_ACADEMIC", "any_academic_id", "nom");
		echo $frm->AfegeixLlista('any_academic_id', 'Any acadèmic', 200, $aCurs[0], $aCurs[1]);

		$aCF = ObteCodiValorDesDeSQL($conn, "SELECT cicle_formatiu_id, nom FROM CICLE_FORMATIU", "cicle_formatiu_id", "nom");
		echo $frm->AfegeixLlista('cicle_formatiu_id', 'Cicle formatiu', 200, $aCF[0], $aCF[1]);
		
		$frm->AfegeixEspai();
		$frm->AfegeixText('codi', 'Codi', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nivell', 'Nivell (1 o 2)', 10, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('grups_classe', 'Grups classe', 50);
		$frm->AfegeixText('grups_tutoria', 'Grups tutoria', 50);

		$frm->AfegeixEspai();
		$frm->AfegeixLlista('avaluacio', 'Avaluació', 30, array('ORD', 'EXT'), array('Ordinària', 'Extraordinària'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixEnter('trimestre', 'Trimestre', 10, [FormFitxa::offREQUERIT]);
		$frm->AfegeixCheckBox('butlleti_visible', 'ButlletÃ­ visible', [FormFitxa::offREQUERIT]);
		$frm->AfegeixCheckBox('finalitzat', 'Curs finalitzat', [FormFitxa::offREQUERIT]);
		
//		$frm->AfegeixText('any_inici', 'Any inici', True, 20);
//		$frm->AfegeixText('any_final', 'Any final', True, 20);
		$frm->EscriuHTML();
        break;
    case "Tutor":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Tutor';
		$frm->Taula = 'TUTOR';
		$frm->ClauPrimaria = 'tutor_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;

		$SQL = 'SELECT C.curs_id, C.nom '.
			' FROM CURS C'.
			' LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
			' WHERE actual=1';
		$aCurs = ObteCodiValorDesDeSQL($conn, $SQL, "curs_id", "nom");
		$frm->AfegeixLlista('curs_id', 'Curs', 200, $aCurs[0], $aCurs[1]);
		$frm->AfegeixLookUp('professor_id', 'Professor', 100, 'UsuariRecerca.php?accio=Professors', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
		$frm->AfegeixLlista('grup_tutoria', 'Grup tutoria', 30, array("", "AB", "BC"), array("sense grup", "AB", "BC"));
		
		$frm->EscriuHTML();
        break;
    case "AnyAcademic":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Any acadèmic';
		$frm->Taula = 'ANY_ACADEMIC';
		$frm->ClauPrimaria = 'any_academic_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('any_inici', 'Any inici', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('any_final', 'Any final', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixData('data_inici', 'Data inici');
		$frm->AfegeixData('data_final', 'Data final');
		$frm->AfegeixCheckBox('actual', 'Curs actual');
	
		$frm->EscriuHTML();
        break;
    case "Equip":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Equip';
		$frm->Taula = 'EQUIP';
		$frm->ClauPrimaria = 'equip_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$SQL = 'SELECT AA.any_academic_id, AA.nom '.
			' FROM ANY_ACADEMIC AA ';
		$aCurs = ObteCodiValorDesDeSQL($conn, $SQL, "any_academic_id", "nom");
		$frm->AfegeixLlista('any_academic_id', 'Any', 200, $aCurs[0], $aCurs[1]);
		$frm->AfegeixLlista('tipus', 'Tipus', 30, array("DP", "ED", "CM"), array("Departament", "Equip docent", "Comissió"));
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookUp('cap', 'Professor', 100, 'UsuariRecerca.php?accio=Professors', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
	
		$frm->EscriuHTML();
        break;
    case "ExpedientSaga":
		$MatriculaId = empty($_GET) ? -1 : $_GET['Id'];
		if ($MatriculaId == -1)
			header("Location: Surt.php");

		$frm = new ExpedientSaga($conn, $Usuari, $MatriculaId);
		$frm->Titol = "Avaluació d'alumnes";
		$frm->EscriuHTML();
        break;
    case "Altre":
        break;
}

?>