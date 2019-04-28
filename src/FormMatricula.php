<?php

/** 
 * FormMatricula.php
 *
 * Formulari de matriculació d'un alumne.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibDB.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error); 

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

CreaIniciHTML($Usuari, 'Matriculació');
echo '<script language="javascript" src="js/Forms.js?v1.5" type="text/javascript"></script>';

$frmMatricula = new Form($conn, $Usuari);

echo '<form action="Matricula.php" method="post" id="FormMatricula">';
echo '<TABLE>';
echo '<TR>';
echo $frmMatricula->CreaLookUp('alumne', 'Alumne', 100, 'UsuariRecerca.php?accio=Alumnes', 'USUARI', 'usuari_id', 'NomAlumne, Cognom1Alumne, Cognom2Alumne');
echo '</TR><TR>';
$aCurs = ObteCodiValorDesDeSQL($conn, "SELECT curs_id, nom FROM CURS", "curs_id", "nom");
echo $frmMatricula->CreaDesplegable('curs', 'Curs', 1000, $aCurs[0], $aCurs[1]);
echo '</TR><TR>';
echo $frmMatricula->CreaDesplegable('grup', 'Grup', 200, array("", "A", "B", "C"), array("sense grup", "A", "B", "C"));
echo '</TR><TR>';
echo $frmMatricula->CreaDesplegable('grup_tutoria', 'Grup tutoria', 200, array("", "AB", "BC"), array("sense grup", "AB", "BC"));
echo '</TR>';
echo '</TABLE>';
echo '</form>';
echo '<button class="btn btn-primary active" type="submit" form="FormMatricula" value="Submit">Matricula</button>';

$conn->close();

?>