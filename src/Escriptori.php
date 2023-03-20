<?php

/** 
 * Escriptori.php
 *
 * Pàgina principal un cop autenticats.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibMaterial.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

// Pedaç per passar l'aplicació.
$Usuari->aplicacio = $Sistema->aplicacio;
$_SESSION['USUARI'] = serialize($Usuari);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

// L'escriptori està format per una llista de targetes (Bootstrap cards) depenent dels rols assignats.
//  - admin 
//  - direcció
//  - cap d'estudis: Notes (tots els nivells).
//  - cap departament 
//  - tutor 
//  - professor: Notes (nivells que imparteix).
//  - alumne: Expedient.
//  - pare: Expedient fills.

if ($Usuari->aplicacio == 'InGest') {
	if (($Usuari->es_admin) || ($Usuari->es_cap_estudis)) {
		$curs = new Curs($conn, $Usuari, $Sistema);
		$curs->EscriuFormulariRecerca();
	}
	else if ($Usuari->es_professor) {
		$Professor = new Professor($conn, $Usuari);
		$Professor->Escriptori();	
	}
	else if ($Usuari->es_alumne) {
		$Alumne = new Alumne($conn, $Usuari);
		$Alumne->Escriptori();	
	}
	else if ($Usuari->es_pare) {
		$Progenitor = new Progenitor($conn, $Usuari);
		$Progenitor->Escriptori();	
	}
	else if ($Usuari->es_administratiu) {
		$curs = new Curs($conn, $Usuari, $Sistema);
		$curs->EscriuFormulariRecerca();
	}
}
else if ($Usuari->aplicacio == 'CapGest') {
	if (($Usuari->es_admin)) {
		$mat = new Material($conn, $Usuari, $Sistema);
		$mat->EscriuFormulariRecerca();
	}
	else if ($Usuari->es_direccio) {
		// Membre de la junta
		$mj = new MembreJunta($conn, $Usuari);
		$mj->Escriptori();	
	}
//	else if ($Usuari->es_alumne) {
//		$Alumne = new Soci($conn, $Usuari);
//		$Alumne->Escriptori();	
//	}
}

echo '</div>';
echo "<DIV id=debug></DIV>";

$conn->close();

?>
