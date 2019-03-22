<?php

/** 
 * Fitxa.php
 *
 * Formularis de fitxa per a diferents taules:
 *  - Curs
 *  - 
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
		$frm->Id = $Id;
		$frm->AfegeixText('codi', 'Codi', True, 20);
		$frm->AfegeixText('nom', 'Nom', True, 200);
		$frm->AfegeixText('nivell', 'Nivell (1 o 2)', True, 10);

		$frm->AfegeixLlista('avaluacio', 'Avaluació', True, array('ORD', 'EXT'), array('Ordinària', 'Extraordinària'));
		$frm->AfegeixEnter('trimestre', 'Trimestre', True, 10);
		$frm->AfegeixCheckBox('butlleti_visible', 'Butlletí visible', True);
		$frm->AfegeixCheckBox('finalitzat', 'Curs finalitzat', True);
	
		
		
//		$frm->AfegeixText('any_inici', 'Any inici', True, 20);
//		$frm->AfegeixText('any_final', 'Any final', True, 20);
		$frm->EscriuHTML();
        break;
    case "Altre":
        break;
}

?>
