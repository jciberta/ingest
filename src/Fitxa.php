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
require_once(ROOT.'/lib/LibMatricula.php');
require_once(ROOT.'/lib/LibExpedient.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibMaterial.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

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

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Curs';
		$frm->Taula = 'CURS';
		$frm->ClauPrimaria = 'curs_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$aPE = ObteCodiValorDesDeSQL($conn, "SELECT cicle_pla_estudi_id, nom FROM CICLE_PLA_ESTUDI", "cicle_pla_estudi_id", "nom");
		echo $frm->AfegeixLlista('cicle_formatiu_id', "Pla d'estudis", 200, $aPE[0], $aPE[1]);
		$frm->AfegeixEspai();
		$frm->AfegeixText('codi', 'Codi', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nivell', 'Nivell (1 o 2)', 10, [FormFitxa::offREQUERIT]);
		$frm->AfegeixData('data_inici', 'Data inici', [FormFitxa::offREQUERIT]);
		$frm->AfegeixData('data_final', 'Data final', [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('grups_classe', 'Grups classe', 50);
		$frm->AfegeixText('grups_tutoria', 'Grups tutoria', 50);

		$frm->AfegeixEspai();
		$frm->AfegeixLlista('estat', 'Estat', 30, array('A', 'J', 'I', 'O', 'T'), array('Actiu', 'Junta', 'Inactiu', 'Obertura', 'Tancat'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixLlista('avaluacio', 'Avaluació', 30, array('ORD', 'EXT'), array('Ordinària', 'Extraordinària'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixEnter('trimestre', 'Trimestre', 10, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('data_tancament', 'Tancament curs', 40, [FormFitxa::offNOMES_LECTURA]);
		$frm->EscriuHTML();
        break;
    case "Tutor":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");

		// Obtenció de l'identificador, sinó registre nou.
		$Id = !array_key_exists("Id", $_GET) ? -1 : $_GET['Id'];
//		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Tutor';
		$frm->Taula = 'TUTOR';
		$frm->ClauPrimaria = 'tutor_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$SQL = "SELECT C.curs_id, C.nom 
			FROM CURS C
			LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
			LEFT JOIN ANY_ACADEMIC AA ON (AA.any_academic_id=CPE.any_academic_id) 
			WHERE actual=1;
			";
		$aCurs = ObteCodiValorDesDeSQL($conn, $SQL, "curs_id", "nom");
		$frm->AfegeixLlista('curs_id', 'Curs', 200, $aCurs[0], $aCurs[1]);
		$frm->AfegeixLookUp('professor_id', 'Professor', 100, 'UsuariRecerca.php?accio=Professors', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
		$gt = new GrupTutoria($conn, $Usuari);
		$aGrups = $gt->ObteGrupsAnyActual();
		array_unshift($aGrups, ""); // afegim al principi
		$frm->AfegeixLlista('grup_tutoria', 'Grup tutoria', 30, $aGrups, $aGrups);
		
		$frm->EscriuHTML();
        break;
    case "AnyAcademic":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
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

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Equip';
		$frm->Taula = 'EQUIP';
		$frm->ClauPrimaria = 'equip_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$SQL = "SELECT AA.any_academic_id, AA.nom FROM ANY_ACADEMIC AA ORDER BY AA.nom DESC;";
		$aCurs = ObteCodiValorDesDeSQL($conn, $SQL, "any_academic_id", "nom");
		$frm->AfegeixLlista('any_academic_id', 'Any', 200, $aCurs[0], $aCurs[1]);
		$frm->AfegeixLlista('tipus', 'Tipus', 30, array("DP", "ED", "CM"), array("Departament", "Equip docent", "Comissió"));
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookUp('cap', 'Professor', 100, 'UsuariRecerca.php?accio=Professors', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
		$frm->AfegeixLookUp('familia_fp_id', 'Família', 100, 'FPRecerca.php?accio=Families', 'FAMILIA_FP', 'familia_fp_id', 'nom');
	
		$frm->EscriuHTML();
        break;
    case "EquipProfessors":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		if ($Id == -1)
			header("Location: Surt.php");

		// Comprovem que l'usuari té accés a aquesta pàgina
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");		

		$frm = new ProfessorsEquip($conn, $Usuari, $Sistema);
		$frm->Id = $Id;
		$frm->EscriuHTML();
        break;
    case "Festiu":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
	
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Festiu';
		$frm->Taula = 'FESTIU';
		$frm->ClauPrimaria = 'festiu_id';
		$frm->AutoIncrement = True;
		$frm->Id = $Id;
		
		$frm->AfegeixData('data', 'Data');
		$frm->AfegeixText('motiu', 'Motiu', 100, [FormFitxa::offREQUERIT]);
	
		$frm->EscriuHTML();
        break;
	case "ExpedientSaga":
		$MatriculaId = empty($_GET) ? -1 : $_GET['Id'];
		if ($MatriculaId == -1)
			header("Location: Surt.php");

		// Comprovem que l'usuari té accés a aquesta pàgina
		$Professor = new Professor($conn, $Usuari);
		if (!$Professor->TeUFEnMatricula($MatriculaId) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");		

		$frm = new ExpedientSaga($conn, $Usuari, $MatriculaId);
		$frm->Titol = "Avaluació d'alumnes";
		$frm->EscriuHTML();
        break;
    case "ExpedientSagaAvaluacio":
		// Entrada inicial per a avaluació. Comença per la primera matrícula del <curs,grup>.
		$CursIdGrup = empty($_GET) ? -1 : $_GET['Id'];
		if ($CursIdGrup == -1)
			header("Location: Surt.php");

		$CursId = explode(',', $CursIdGrup)[0];
//echo "CursId: $CursId<br>";
		// Comprovem que l'usuari té accés a aquesta pàgina
		$Professor = new Professor($conn, $Usuari);
		if (!$Professor->TeUFEnCurs($CursId) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");		
		
		$av = new Avaluacio($conn, $Usuari, $Sistema);
		$aMatricules = $av->LlistaMatricules($CursIdGrup);
//print_h($aMatricules);
//exit;
		if (count($aMatricules) > 0) {
			$frm = new ExpedientSaga($conn, $Usuari, $aMatricules[0]);
			$frm->Titol = "Avaluació d'alumnes";
			$frm->EscriuHTML();
		}
        break;
    case "Acta":
		$CursIdGrup = empty($_GET) ? -1 : $_GET['Id'];
		if ($CursIdGrup == -1)
			header("Location: Surt.php");

		$aCursId = explode(',', $CursIdGrup);
		$CursId = $aCursId[0];
		$Grup = (count($aCursId)>1) ? $aCursId[1] : '';

		// Comprovem que l'usuari té accés a aquesta pàgina
//		$Professor = new Professor($conn, $Usuari);
//		if (!$Professor->TeUFEnCurs($CursId) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
//			header("Location: Surt.php");		
		
		$acta = new Acta($conn, $Usuari);
//		$acta = $acta->GeneraPDF($CursId, $Grup);
		$acta = $acta->EscriuHTML($CursId, $Grup);
		
        break;
    case "PlaTreball":
		$MatriculaId = (array_key_exists('Id', $_GET)) ? $_GET['Id'] : -1; 
		$CursId = (array_key_exists('CursId', $_GET)) ? $_GET['CursId'] : -1; 
		if ($MatriculaId == -1 && $CursId == -1)
			header("Location: Surt.php");

		$mat = new Matricula($conn, $Usuari);
		$mat->Carrega($MatriculaId);
		$AlumneId = $mat->ObteAlumne();
		$objUsuari = new Usuari($conn, $Usuari);

		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis || ($Usuari->usuari_id == $AlumneId) || ($objUsuari->EsProgenitor($AlumneId))) {
			$frm = new PlaTreball($conn, $Usuari, $Sistema);
			$frm->MatriculaId = $MatriculaId;
			$frm->CursId = $CursId;
			$frm->EscriuHTML();
		}
		else
			header("Location: Surt.php");		
        break;
    case "PlaTreballCalendari":
		$MatriculaId = (array_key_exists('Id', $_GET)) ? $_GET['Id'] : -1; 
		$CursId = (array_key_exists('CursId', $_GET)) ? $_GET['CursId'] : -1; 
		if ($MatriculaId == -1 && $CursId == -1)
			header("Location: Surt.php");

		$mat = new Matricula($conn, $Usuari);
		$mat->Carrega($MatriculaId);
		$AlumneId = $mat->ObteAlumne();
		$objUsuari = new Usuari($conn, $Usuari);
		if ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis || ($Usuari->usuari_id == $AlumneId) || ($objUsuari->EsProgenitor($AlumneId))) {
			$frm = new PlaTreballCalendari($conn, $Usuari, $Sistema);
			$frm->MatriculaId = $MatriculaId;
			$frm->CursId = $CursId;
			$frm->EscriuHTML();
		}
		else
			header("Location: Surt.php");		
        break;
    case "Material":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		// Obtenció de l'identificador, sinó registre nou.
		$Id = (array_key_exists('Id', $_GET)) ? $_GET['Id'] : -1; 
		$Material = new Material($conn, $Usuari, $Sistema);
		$Material->Id = $Id;
		$Material->EscriuFormulariFitxa();
        break;
    case "TipusMaterial":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		// Obtenció de l'identificador, sinó registre nou.
		$Id = (array_key_exists('Id', $_GET)) ? $_GET['Id'] : -1; 
		$TipusMaterial = new TipusMaterial($conn, $Usuari, $Sistema);
		$TipusMaterial->Id = $Id;
		$TipusMaterial->EscriuFormulariFitxa();
        break;
    case "ReservaMaterial":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		// Obtenció de l'identificador, sinó registre nou.
		$Id = (array_key_exists('Id', $_GET)) ? $_GET['Id'] : -1; 
		$ReservaMaterial = new ReservaMaterial($conn, $Usuari, $Sistema);
		$ReservaMaterial->Id = $Id;
		$ReservaMaterial->EscriuFormulariFitxa();
        break;
    case "PerfilAlumne":
		if (!$Usuari->es_alumne)
			header("Location: Surt.php");
		$Alumne = new Alumne($conn, $Usuari, $Sistema);
		$Alumne->Perfil();
        break;
	case "Altre":
        break;
}
