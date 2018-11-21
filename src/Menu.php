<?php

/** 
 * index.php
 *
 * Pàgina principal.
 */

require_once('Config.php');
require_once('lib/LibDB.php');
require_once('lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

CreaIniciHTML('');

echo "<h3>Cicles formatius</h3>";

$SQL = ' SELECT * FROM CICLE_FORMATIU ORDER BY grau';
$ResultSet = $conn->query($SQL);
if ($ResultSet->num_rows > 0) {
	echo "<TABLE>";
	echo "<TH>Grau</TH>";
	echo "<TH>Codi</TH>";
	echo "<TH>Codi XTEC</TH>";
	echo "<TH>Nom</TH>";

	$row = $ResultSet->fetch_assoc();
	while($row) {
		echo "<TR>";
		echo "<TD>".$row["grau"]."</TD>";
		echo "<TD>".$row["codi"]."</TD>";
		echo "<TD>".$row["codi_xtec"]."</TD>";
		echo "<TD>".utf8_encode($row["nom"])."</TD>";
		echo "<TD><A HREF=AlumnesCicle.php?CicleId=".$row["cicle_formatiu_id"].">Alumnes</A></TD>";
		echo "<TD><A HREF=Notes.php?CicleId=".$row["cicle_formatiu_id"]."&Nivell=1>Notes 1r</A></TD>";
		echo "<TD><A HREF=Notes.php?CicleId=".$row["cicle_formatiu_id"]."&Nivell=2>Notes 2n</A></TD>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	

echo "<A HREF=FormMatricula.php>Matriculació alumnes</A>";

echo "<h3>Professors</h3>";
$SQL = ' SELECT * FROM USUARI WHERE es_professor=1 ORDER BY cognom1, cognom2, nom';
$ResultSet = $conn->query($SQL);
if ($ResultSet->num_rows > 0) {
	echo "<TABLE>";
	echo "<TH>Cognom</TH>";
	echo "<TH>Nom</TH>";
	echo "<TH>Usuari</TH>";

	$row = $ResultSet->fetch_assoc();
	while($row) {
		echo "<TR>";
		echo utf8_encode("<TD>".$row["cognom1"]." ".$row["cognom2"]."</TD>");
		echo utf8_encode("<TD>".$row["nom"]."</TD>");
		echo utf8_encode("<TD>".$row["username"]."</TD>");
		echo "<TD><A HREF=AssignaUFs.php?ProfessorId=".$row["usuari_id"].">Assigna UFs</A></TD>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	

echo "<DIV id=debug></DIV>";

$ResultSet->close();

$conn->close();

?>















