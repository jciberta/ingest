<?php

/** 
 * ExpedientPDF.php
 *
 * Impressió de l'expedient en PDF per a un alumne.
 * Es pot cridar des de la línia de comandes o des de la web.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibExpedient.php');
require_once(ROOT.'/lib/LibMatricula.php');
require_once(ROOT.'/lib/LibHTML.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (defined('STDIN')) {
	// Via CLI
	if ($argc == 2) {
		$MatriculaId = $argv[1];
	}
	else {
		die("Ús: ExpedientPDF.php AlumneId\n");
	}
	$Expedient = new Expedient($conn);
	$Expedient->GeneraPDF($MatriculaId);
}
else {
	// Via web
	session_start();
	if (!isset($_SESSION['usuari_id'])) 
		header("Location: Surt.php");
	$Usuari = unserialize($_SESSION['USUARI']);
	$Sistema = unserialize($_SESSION['SISTEMA']);

	RecuperaGET($_GET);

	if (!empty($_GET))
		$MatriculaId = $_GET['MatriculaId'];
//		$alumne = $_GET['AlumneId'];
	else
		$MatriculaId = -1;
//		$alumne = -1;

	$Matricula = new Matricula($conn, $Usuari, $Sistema);
	$Matricula->Carrega($MatriculaId);
	$alumne = $Matricula->ObteAlumne();
//print "<br>";	
//print "<br>";	
//print "<br>";	
//print "alumne: $alumne<br>";	
//print "MatriculaId: $MatriculaId<br>";	

	// Si intenta manipular l'usuari des de la URL -> al carrer!
	if (($Usuari->es_alumne) && ($Usuari->usuari_id != $alumne))
		header("Location: Surt.php");
	
	$objUsuari = new Usuari($conn, $Usuari, $Sistema);
	if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor && !$Usuari->es_administratiu
		&& !$Usuari->es_alumne && !($Usuari->es_pare && $objUsuari->EsProgenitor($alumne)))
		header("Location: Surt.php");
//print "Usuari->es_pare: $Usuari->es_pare, objUsuari->EsProgenitor($alumne): $objUsuari->EsProgenitor($alumne)<br>";
	
	// L'alumne i el pare només poden veure les notes quan s'ha activat la visibilitat dels butlletins per a aquell curs
	$ButlletiVisible = True;
	if ($Usuari->es_alumne || $Usuari->es_pare) {
		$Expedient = new Expedient($conn, $Usuari, $Sistema);
		$ButlletiVisible = $Expedient->EsVisibleButlleti($MatriculaId);
	}
	
	if ($ButlletiVisible) {
		$Expedient = new Expedient($conn, $Usuari, $Sistema);
		$Expedient->GeneraPDF($MatriculaId);
	}
	else {
		CreaIniciHTML($Usuari, 'Visualitza expedient');
		echo 'El butlletí de notes no està disponible.';	
	}
}

?>