<?php

/** 
 * FPFitxa.php
 *
 * Formularis de fitxa per les taules de FP:
 *  - Famílies
 *  - Cicles formatius
 *  - Mòduls professionals
 *  - Unitats formatives
 *  - Programació didàctica
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibUsuari.php');
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
	$accio = 'Families';
else
	$accio = $_GET['accio'];
//echo "Accio: $accio";

// Destruim l'objecte per si estava ja creat.
unset($frm);

switch ($accio) {
    case "Families":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		if (!$Usuari->es_admin)
			header("Location: Surt.php");

		$Opcions = [FormFitxa::offREQUERIT];
		$NomesLectura = !($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if ($NomesLectura)
			array_push($Opcions, FormFitxa::offNOMES_LECTURA);

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Edició famílies';
		$frm->Taula = 'FAMILIA_FP';
		$frm->ClauPrimaria = 'familia_fp_id';
		$frm->Id = $Id;
		$frm->AfegeixText('nom', 'Nom', 200, $Opcions);
		$frm->EscriuHTML();	
        break;
    case "CiclesFormatius":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		if (!$Usuari->es_admin)
			header("Location: Surt.php");

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Edició CF';
		$frm->Taula = 'CICLE_FORMATIU';
		$frm->ClauPrimaria = 'cicle_formatiu_id';
		$frm->Id = $Id;
		$frm->AfegeixText('codi', 'Codi', 20);
		$frm->AfegeixText('nom', 'Nom', 200);
		$frm->AfegeixText('grau', 'Grau', 20);
		$frm->AfegeixText('codi_xtec', 'Codi XTEC', 20);
		$frm->AfegeixLookup('familia_fp_id', 'Família', 200, 'FPRecerca.php?accio=Families', 'FAMILIA_FP', 'familia_fp_id', 'nom');
		$frm->AfegeixLlista('llei', 'Llei', 30, array('LO', 'LG'), array('LOE', 'LOGSE'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixCheckBox('actiu', 'Actiu');
		$frm->EscriuHTML();	
        break;
    case "ModulsProfessionals":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		if (!$Usuari->es_admin)
			header("Location: Surt.php");

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Edició MP';
		$frm->Taula = 'MODUL_PROFESSIONAL';
		$frm->ClauPrimaria = 'modul_professional_id';
		$frm->Id = $Id;
		$frm->AfegeixText('codi', 'Codi', 20);
		$frm->AfegeixText('nom', 'Nom', 200);
		$frm->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixEnter('hores_setmana', 'Hores setmana', 20);
		$frm->AfegeixLookup('cicle_formatiu_id', 'Cicle formatiu', 200, 'FPRecerca.php?accio=CiclesFormatius', 'CICLE_FORMATIU', 'cicle_formatiu_id', 'codi, nom');
		$frm->AfegeixCheckBox('es_fct', 'És FCT?');
		$frm->AfegeixText('especialitat', 'Especialitat', 40);
		$frm->AfegeixText('cos', 'Cos', 20);
		$frm->AfegeixCheckBox('actiu', 'Actiu');
		$frm->EscriuHTML();
        break;
    case "ModulsProfessionalsPlaEstudis":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		if (!$Usuari->es_admin)
			header("Location: Surt.php");

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = "Edició MP Pla d'estudis";
		$frm->Taula = 'MODUL_PLA_ESTUDI';
		$frm->ClauPrimaria = 'modul_pla_estudi_id';
		$frm->Id = $Id;
		$frm->AfegeixText('codi', 'Codi', 20);
		$frm->AfegeixText('nom', 'Nom', 200);
		$frm->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixEnter('hores_setmana', 'Hores setmana', 20);
//		$frm->AfegeixLookup('cicle_formatiu_id', 'Cicle formatiu', 200, 'FPRecerca.php?accio=CiclesFormatius', 'CICLE_FORMATIU', 'cicle_formatiu_id', 'codi, nom');
		$frm->AfegeixCheckBox('es_fct', 'És FCT?');
		$frm->AfegeixText('especialitat', 'Especialitat', 40);
		$frm->AfegeixText('cos', 'Cos', 20);
		$frm->AfegeixTextRic('metodologia', 'Metodologia', 200, 100);
		$frm->AfegeixTextRic('criteris_avaluacio', "Criteris d'avaluació", 200, 100);
		$frm->AfegeixTextRic('recursos', 'Recursos', 200, 100);
		$frm->EscriuHTML();
        break;
    case "UnitatsFormatives":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		$Professor = new Professor($conn, $Usuari, $Sistema);
		$Professor->CarregaUFAssignades();
		if (!$Professor->TeUF($Id) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		
		$Opcions = [FormFitxa::offREQUERIT];
		$NomesLectura = !($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if ($NomesLectura)
			array_push($Opcions, FormFitxa::offNOMES_LECTURA);

		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Edició UF';
		$frm->Taula = 'UNITAT_FORMATIVA';
		$frm->ClauPrimaria = 'unitat_formativa_id';
		$frm->Id = $Id;
		$frm->AfegeixText('codi', 'Codi', 20, $Opcions);
		$frm->AfegeixText('nom', 'Nom', 200, $Opcions);
		$frm->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookup('modul_professional_id', 'Mòdul professional', 200, 'FPRecerca.php?accio=ModulsProfessionals', 'MODUL_PROFESSIONAL', 'modul_professional_id', 'codi, nom', $Opcions);
		$frm->AfegeixEnter('nivell', 'Nivell (1 o 2)', 10, $Opcions);
		$frm->AfegeixCheckBox('es_fct', 'És FCT?', $Opcions);
		$frm->AfegeixCheckBox('activa', 'Activa');		
		$frm->EscriuHTML();
        break;
    case "UnitatsFormativesPlaEstudis":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$Professor = new Professor($conn, $Usuari, $Sistema);
		$Professor->CarregaUFAssignades();
		if (!$Professor->TeUF($Id) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		
		$frm = new PlaEstudisUnitatFitxa($conn, $Usuari, $Sistema);
		$frm->Id = $Id;
		$frm->EscriuHTML();
        break;
    case "ProgramacioDidactica":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$Professor = new Professor($conn, $Usuari, $Sistema);
		$Professor->CarregaUFAssignades();
		$FamiliaFPId = $Professor->EsCapDepartament($Usuari->usuari_id); // Si és cap de departament

		if ($Professor->TeMP($Id) || $Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis
			|| $FamiliaFPId>0) {} // ToDo: Comprovar que la programació (mòdul) és de la família
		else
			header("Location: Surt.php");
		
//		$frm = new ProgramacioDidacticaFitxa($conn, $Usuari, $Sistema);
//		$frm->Id = $Id;
		$frm = ProgramacioDidacticaFitxaFactory::Crea($conn, $Usuari, $Sistema, $Id);
		$frm->EscriuHTML();
        break;
    case "ProgramacioDidacticaLectura":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];

		$PermisLectura = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis || $Usuari->es_professor);
		if (!$PermisLectura)
			header("Location: Surt.php");

//		$frm = new ProgramacioDidactica($conn, $Usuari, $Sistema);
//		$frm->Id = $Id;

		$frm = ProgramacioDidacticaFactory::Crea($conn, $Usuari, $Sistema, $Id);

		$frm->EscriuHTML();
        break;
    case "PlaEstudisCicleFitxa":
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		$Permis = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if (!$Permis)
			header("Location: Surt.php");
		$frm = new PlaEstudisCicleFitxa($conn, $Usuari, $Sistema);
		$frm->Id = $Id;
		$frm->EscriuHTML();
        break;
	case "PreuMatricula":
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		$Permis = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if (!$Permis)
			header("Location: Surt.php");
		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Edició preus matrícula';
		$frm->Taula = 'PREU_MATRICULA';
		$frm->ClauPrimaria = 'preu_matricula_id';
		$frm->Id = $Id;
		$frm->AfegeixLookup('any_academic_id', 'Any acadèmic', 200, 'Recerca.php?accio=AnyAcademic', 'ANY_ACADEMIC', 'any_academic_id', 'any_inici, any_final', [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookup('cicle_formatiu_id', 'Cicle formatiu', 200, 'FPRecerca.php?accio=CiclesFormatius', 'CICLE_FORMATIU', 'cicle_formatiu_id', 'nom', [FormFitxa::offREQUERIT]);
		$frm->AfegeixEnter('nivell', 'Nivell', 10, [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixReal('preu', 'Preu', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixEnter('numero_uf', 'Número UF', 20, [FormFitxa::offREQUERIT]);
		$frm->EscriuHTML();
		break;
	case "BonificacioMatricula":
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		$Permis = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if (!$Permis)
			header("Location: Surt.php");
		$frm = new FormFitxa($conn, $Usuari, $Sistema);
		$frm->Titol = 'Edició bonificacions matrícula';
		$frm->Taula = 'BONIFICACIO_MATRICULA';
		$frm->ClauPrimaria = 'bonificacio_matricula_id';
		$frm->Id = $Id;
		$frm->AfegeixLookup('any_academic_id', 'Any acadèmic', 200, 'Recerca.php?accio=AnyAcademic', 'ANY_ACADEMIC', 'any_academic_id', 'any_inici, any_final', [FormFitxa::offREQUERIT]);
		$frm->AfegeixText('nom', 'Nom', 200, [FormFitxa::offREQUERIT]);
		$frm->AfegeixReal('valor', 'Valor', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixLlista('tipus', 'Tipus', 30, array('P', 'E'), array('Percentatge', 'Euro'), [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookup('unitat_formativa_id', 'Unitat formativa', 200, 'FPRecerca.php?accio=UnitatsFormativesCF', 'UNITAT_FORMATIVA', 'unitat_formativa_id', 'nom', [FormFitxa::offREQUERIT]);
		$frm->EscriuHTML();
		break;
}

?>
