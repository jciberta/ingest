<?php

/** 
 * MatriculaAlumne.php
 *
 * Visualitza la matrícula d’un alumne.
 */

require_once('LibDB.php');
require_once('LibHTML.php');

$conn = new mysqli("localhost", "root", "root", "InGest");
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

CreaIniciHTML('Visualitza matrícula');

$alumne = $_POST['alumne'];

$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, MP.nom AS NomMP, CF.nom AS NomCF, '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
	' UF.*, MP.*, CF.* '.
	' FROM UNITAT_FORMATIVA UF '.
	' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
	' LEFT JOIN CICLE_FORMATIU CF ON (CF.cicle_formatiu_id=MP.cicle_formatiu_id) '.
	' LEFT JOIN MATRICULA M ON (CF.cicle_formatiu_id=M.cicle_formatiu_id) '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' LEFT JOIN NOTES N ON (UF.unitat_formativa_id=N.uf_id AND N.matricula_id=M.matricula_id) '.
	' WHERE CF.cicle_formatiu_id=M.cicle_formatiu_id AND UF.nivell=M.nivell AND M.alumne_id= '.$alumne;
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	echo "<TABLE>";
	echo "<TH>Cicle</TH>";
	echo "<TH>Mòdul</TH>";
	echo "<TH>UF</TH>";
	echo "<TH>Hores</TH>";
	echo "<TH>Matrícula</TH>";
	
	$row = $ResultSet->fetch_assoc();
	echo $row["NomAlumne"]." ".$row["Cognom1Alumne"];
	
	while($row) {
		echo "<TR>";
		echo "<TD>".$row["NomCF"]."</TD>";
		echo "<TD>".$row["NomMP"]."</TD>";
		echo "<TD>".$row["NomUF"]."</TD>";
		echo "<TD>".$row["HoresUF"]."</TD>";
		echo "</TR>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	
$ResultSet->close();

$conn->close();

?>




















