<?php

/** 
 * AlumnesCicle.php
 *
 * Mostra els alumnes d'un cicle ordenats per nivell.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

RecuperaGET($_GET);

$CicleId = $_GET['CicleId'];

CreaIniciHTML($Usuari, 'Alumnes cicle');

$SQL = ' SELECT '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, M.curs_id, M.nivell, M.grup, M.alumne_id '.
	' FROM MATRICULA M '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' WHERE M.cicle_formatiu_id='.$CicleId.
	' ORDER BY nivell, grup';
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	echo "<TABLE>";
	echo "<TH>Curs</TH>";
	echo "<TH>Nivell</TH>";
	echo "<TH>Grup</TH>";
	echo "<TH>Alumne</TH>";
	while($row = $ResultSet->fetch_assoc()) {
		echo "<TR>";
		echo "<TD>".$row["curs_id"]."</TD>";
		echo "<TD>".$row["nivell"]."</TD>";
		echo "<TD>".$row["grup"]."</TD>";
		echo "<TD>".utf8_encodeX($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
		echo utf8_encodeX("<TD><A HREF=MatriculaAlumne.php?AlumneId=".$row["alumne_id"].">Matrícula</A></TD>");
		echo utf8_encodeX("<TD><A HREF=MatriculaAlumne.php?accio=MostraExpedient&AlumneId=".$row["alumne_id"].">Expedient</A></TD>");
		echo "</TR>";
	}
	echo "</TABLE>";
};
	
$ResultSet->close();

$conn->close();

?>














