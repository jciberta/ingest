﻿<?php

/** 
 * Alumnes.php
 *
 * Llistat d'alumnes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
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

CreaIniciHTML('Alumnes');

$SQL = ' SELECT * FROM USUARI WHERE es_alumne=1 ORDER BY cognom1, cognom2, nom';
$ResultSet = $conn->query($SQL);
if ($ResultSet->num_rows > 0) {
	echo '<TABLE class="table table-striped">';
	echo "<TH>Cognom</TH>";
	echo "<TH>Nom</TH>";
	echo "<TH>Usuari</TH>";

	$row = $ResultSet->fetch_assoc();
	while($row) {
		echo "<TR>";
		echo utf8_encode("<TD>".$row["cognom1"]." ".$row["cognom2"]."</TD>");
		echo utf8_encode("<TD>".$row["nom"]."</TD>");
		echo utf8_encode("<TD>".$row["username"]."</TD>");
//		echo "<TD><A HREF=AssignaUFs.php?accio=AssignaUF&ProfessorId=".$row["usuari_id"].">Assigna UFs</A></TD>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	

echo "<DIV id=debug></DIV>";

$ResultSet->close();

$conn->close();

?>