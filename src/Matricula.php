<?php

/** 
 * Matricula.php
 *
 * Matriculació d’un alumne.
 *
 * Quan es crea la matrícula d’un alumne:
 * 1. Pel nivell que sigui, es creen les notes, una per cada UF d’aquell cicle
 * 2. Si l’alumne és a 2n, l’aplicació ha de buscar les que li han quedar de primer per afegir-les
 */

require_once('Config.php');
require_once('lib/LibDB.php');
require_once('lib/LibHTML.php');
require_once('lib/LibMatricula.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

CreaIniciHTML('Matrícula');

$curs = $_POST['curs'];
$alumne = $_POST['alumne'];
$cicle = $_POST['cicle'];
$nivell = $_POST['nivell'];
$grup = $_POST['grup'];

if (CreaMatricula($conn, $curs, $alumne, $cicle, $nivell, $grup) == -1) {
	echo "L'alumne ja està matriculat!";
}
else {
	// Llistem les UF del cicle/nivell
	$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, MP.nom AS NomMP, CF.nom AS NomCF, UF.*, MP.*, CF.* '.
		' FROM UNITAT_FORMATIVA UF '.
		' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
		' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
		' WHERE CF.cicle_formatiu_id='.$cicle.
		' AND UF.nivell='.$nivell;
	//print_r($SQL);

	$ResultSet = $conn->query($SQL);

	if ($ResultSet->num_rows > 0) {
		echo "<TABLE>";
		echo "<TH>Cicle</TH>";
		echo utf8_encode("<TH>Mòdul</TH>");
		echo "<TH>UF</TH>";
		echo "<TH>Hores</TH>";
		while($row = $ResultSet->fetch_assoc()) {
			echo "<TR>";
			echo utf8_encode("<TD>".$row["NomCF"]."</TD>");
			echo utf8_encode("<TD>".$row["NomMP"]."</TD>");
			echo utf8_encode("<TD>".$row["NomUF"]."</TD>");
			echo "<TD>".$row["HoresUF"]."</TD>";
			echo "</TR>";
		}
		echo "</TABLE>";
	};	
	$ResultSet->close();
}

$conn->close();

?>































