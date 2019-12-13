<?php

/** 
 * FPFitxa.php
 *
 * Formularis de fitxa per les taules de FP:
 *  - Famílies
 *  - Cicles formatius
 *  - Mòduls professionals
 *  - Unitats formatives
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibProfessor.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

if (empty($_GET))
	$accio = 'Families';
else
	$accio = $_GET['accio'];

// Destruim l'objecte per si estava ja creat.
unset($frm);

switch ($accio) {
    case "Families":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		$Opcions = [FormFitxa::offREQUERIT];
		$NomesLectura = !($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if ($NomesLectura)
			array_push($Opcions, FormFitxa::offNOMES_LECTURA);

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Edició famílies';
		$frm->Taula = 'FAMILIA_FP';
		$frm->ClauPrimaria = 'familia_fp_id';
		$frm->Id = $Id;
		$frm->AfegeixText('nom', 'Nom', 200, $Opcions);
		$frm->EscriuHTML();	
        break;
    case "CiclesFormatius":
        break;
    case "ModulsProfessionals":
        break;
    case "UnitatsFormatives":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		$Professor = new Professor($conn, $Usuari);
		$Professor->CarregaUFAssignades();
		if (!$Professor->TeUF($Id) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
			header("Location: Surt.php");
		
		$Opcions = [FormFitxa::offREQUERIT];
		$NomesLectura = !($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);
		if ($NomesLectura)
			array_push($Opcions, FormFitxa::offNOMES_LECTURA);

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Edició UF';
		$frm->Taula = 'UNITAT_FORMATIVA';
		$frm->ClauPrimaria = 'unitat_formativa_id';
		$frm->Id = $Id;
		$frm->AfegeixText('nom', 'Nom', 200, $Opcions);
		$frm->AfegeixText('codi', 'Codi', 20, $Opcions);
		$frm->AfegeixEnter('hores', 'Hores', 20, [FormFitxa::offREQUERIT]);
		$frm->AfegeixLookup('modul_professional_id', 'Mòdul professional', 200, 'FPRecerca.php?accio=ModulsProfessionals', 'MODUL_PROFESSIONAL', 'modul_professional_id', 'codi, nom', $Opcions);
		$frm->AfegeixEnter('nivell', 'Nivell (1 o 2)', 10, $Opcions);
		$frm->AfegeixData('data_inici', 'Data inici');
		$frm->AfegeixData('data_final', 'Data final');

		$frm->AfegeixCheckBox('orientativa', 'És orientativa?');
		$frm->EscriuHTML();
        break;
}

?>
