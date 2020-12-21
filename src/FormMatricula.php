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
	header("Location: Surt.php");
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
//echo $frmMatricula->CreaLookUp('alumne', 'Alumne', 100, 'UsuariRecerca.php?accio=Alumnes', 'USUARI', 'usuari_id', 'NomAlumne, Cognom1Alumne, Cognom2Alumne');
echo $frmMatricula->CreaLookUp('alumne', 'Alumne', 100, 'UsuariRecerca.php?accio=Alumnes', 'USUARI', 'usuari_id', 'nom, cognom1, cognom2');
echo '</TR><TR>';
//$SQL = 'SELECT C.curs_id, C.nom '.
//	' FROM CURS C'.
//	' LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) '.
//	' WHERE actual=1';
$SQL = 'SELECT * FROM CURS_ACTUAL';
$aCurs = ObteCodiValorDesDeSQL($conn, $SQL, "curs_id", "nom");
echo $frmMatricula->CreaLlista('curs', 'Curs', 1000, $aCurs[0], $aCurs[1]);
echo '</TR><TR>';
echo $frmMatricula->CreaLlista('grup', 'Grup', 200, array("", "A", "B", "C", "D"), array("sense grup", "A", "B", "C", "D"));
echo '</TR><TR>';
echo $frmMatricula->CreaLlista('grup_tutoria', 'Grup tutoria', 200, array("", "AB", "BC", "CD"), array("sense grup", "AB", "BC", "CD"));
echo '</TR>';
echo '</TABLE>';
echo '</form>';
echo '<button class="btn btn-primary active" type="submit" form="FormMatricula" value="Submit">Matricula</button>';

$conn->close();

?>