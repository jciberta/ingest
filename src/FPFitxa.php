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

// Destruim l'objecte per si estava ja creat.
unset($frm);

switch ($accio) {
    case "Families":
        break;
    case "CiclesFormatius":
        break;
    case "ModulsProfessionals":
        break;
    case "UnitatsFormatives":
		// Obtenció de l'identificador, sinó registre nou.
		$Id = empty($_GET) ? -1 : $_GET['Id'];
		
		$NomesLectura = !($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis);

		$frm = new FormFitxa($conn, $Usuari);
		$frm->Titol = 'Edició UF';
		$frm->Taula = 'UNITAT_FORMATIVA';
		$frm->ClauPrimaria = 'unitat_formativa_id';
		$frm->Id = $Id;
		$frm->AfegeixText('nom', 'Nom', True, 200, $NomesLectura);
		$frm->AfegeixText('codi', 'Codi', True, 20, $NomesLectura);
		$frm->AfegeixEnter('hores', 'Hores', True, 20);
		$frm->AfegeixLookup('modul_professional_id', 'Mòdul professional', True, 200, 'FPRecerca.php?accio=ModulsProfessionals', 'MODUL_PROFESSIONAL', 'modul_professional_id', 'codi, nom', $NomesLectura);
		$frm->AfegeixEnter('nivell', 'Nivell (1 o 2)', True, 10, $NomesLectura);
		$frm->AfegeixData('data_inici', 'Data inici', False);
		$frm->AfegeixData('data_final', 'Data final', False);

		$frm->AfegeixCheckBox('orientativa', 'És orientativa?', False);
		$frm->EscriuHTML();
        break;
}

?>
