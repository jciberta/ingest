<?php

/** 
 * UsuariFitxa.php
 *
 * Formulari de la fitxa de l'usuari.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibForms.php');
require_once(ROOT.'/lib/LibUsuari.php');
//require_once(ROOT.'/lib/LibProfessor.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
	header("Location: Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

// Obtenció de l'identificador, sinó registre nou.
$Id = empty($_GET) ? -1 : $_GET['Id'];

// El tutor no pot crear nous usuaris.
if ($Usuari->es_professor && $Id == -1)
	header("Location: Surt.php");

// Només poden veure la fitxa els tutors d'aquell alumne.
$Professor = new Professor($conn, $Usuari);
$ProfessorSenseCarrecDirectiu = ($Usuari->es_professor) && (!$Usuari->es_direccio) && (!$Usuari->es_cap_estudis);
if ($ProfessorSenseCarrecDirectiu && (!$Professor->EsTutorAlumne($Id) && !$Professor->EsTutorPare($Id)))
	header("Location: Surt.php");

// Creació del formulari de la fitxa de l'usuari.
$frm = new Usuari($conn, $Usuari);
$frm->EscriuFormulariFitxa($Id);

?>