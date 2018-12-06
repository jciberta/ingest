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
require_once('lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");

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

		$frm = new FormFitxa($conn);
		$frm->Titol = 'Edició UF';
		$frm->Taula = 'UNITAT_FORMATIVA';
		$frm->ClauPrimaria = 'unitat_formativa_id';
		$frm->Id = $Id;
		$frm->AfegeixText('nom', 'Nom', True, 100);
		$frm->AfegeixText('codi', 'Codi', True, 40);
		$frm->AfegeixText('hores', 'Hores', True, 40);
		$frm->AfegeixText('nivell', 'Nivell (1 o 2)', True, 40);

		$frm->AfegeixCheckBox('orientativa', 'És orientativa?', False);
		$frm->EscriuHTML();
        break;
}

?>
