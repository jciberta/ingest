<?php

/** 
 * MatriculaAlumne.php
 *
 * Visualitza la matrícula/expedient d'un alumne.
 *
 * GET:
 * - accio: {MatriculaUF, MostraExpedient}.
 * - MatriculaId: Id de la matrícula de l'alumne.
 * POST:
 * - MatriculaId: Id de la matrícula de l'alumne.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibUsuari.php');
require_once(ROOT.'/lib/LibNotes.php');
require_once(ROOT.'/lib/LibExpedient.php');
require_once(ROOT.'/lib/LibMatricula.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);
//print_r($_GET);
//exit;

if (!empty($_POST)) {
	$MatriculaId = mysqli_real_escape_string($conn, $_POST['MatriculaId']);
}
else {
	$MatriculaId = mysqli_real_escape_string($conn, $_GET['MatriculaId']);
}

$Matricula = new Matricula($conn, $Usuari, $Sistema);
$Matricula->Carrega($MatriculaId);
$alumne = $Matricula->ObteAlumne();
$nivell = $Matricula->ObteNivell();

//echo "<BR><BR><BR>";
//echo "alumne:".$alumne."<BR>";
//echo "MatriculaId:".$MatriculaId."<BR>";
//echo "Nivell:".$nivell."<BR>";

$accio = (isset($_GET) && array_key_exists('accio', $_GET)) ? $_GET['accio'] : '';
$ActivaEdicio = (isset($_GET) && array_key_exists('ActivaEdicio', $_GET)) ? $_GET['ActivaEdicio'] : '';

// Si intenta manipular l'usuari des de la URL -> al carrer!
if (($Usuari->es_alumne) && ($Usuari->usuari_id != $alumne))
	header("Location: Surt.php");

$objUsuari = new Usuari($conn, $Usuari, $Sistema);
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor && !$Usuari->es_administratiu && !$Usuari->es_alumne && !($Usuari->es_pare && $objUsuari->EsProgenitor($alumne)))
	header("Location: Surt.php");

// L'edició de l'expedient només la pot fer l'administrador
if (!$Usuari->es_admin && $ActivaEdicio==1)
	header("Location: Surt.php");

if ($accio == 'MostraExpedient') {
	$Expedient = new Expedient($conn, $Usuari, $Sistema);
	$Expedient->Id = $MatriculaId;
	$Expedient->ActivaEdicio = $ActivaEdicio;
	$Expedient->EscriuHTML();
}
else {
	$Matricula = new Matricula($conn, $Usuari, $Sistema);
	$Matricula->Id = $MatriculaId;
	$Matricula->EscriuHTML();
}

$conn->close();

?>