<?php

/** 
 * MatriculaAlumne.php
 *
 * Visualitza la matrícula d’un alumne.
 */

require_once('Config.php');
require_once('LibDB.php');
require_once('LibHTML.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

CreaIniciHTML('Visualitza matrícula');
echo '<script language="javascript" src="js/jquery-3.3.1.min.js" type="text/javascript"></script>';
echo '<script language="javascript" src="js/Matricula.js" type="text/javascript"></script>';

if (!empty($_POST))
	$alumne = $_POST['alumne'];
else
	$alumne = $_GET['AlumneId'];

$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, MP.nom AS NomMP, CF.nom AS NomCF, '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
	' N.notes_id AS NotaId, N.baixa AS Baixa, '.
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

echo "<FORM>";
if ($ResultSet->num_rows > 0) {
	echo "<TABLE>";
	echo "<TH>Cicle</TH>";
	echo utf8_encode("<TH>Mòdul</TH>");
	echo "<TH>UF</TH>";
	echo "<TH>Hores</TH>";
	echo utf8_encode("<TH>Matrícula</TH>");
	
	$row = $ResultSet->fetch_assoc();
	echo utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]);
	
	while($row) {
		echo "<TR>";
		echo "<TD>".utf8_encode($row["NomCF"])."</TD>";
		echo "<TD>".utf8_encode($row["NomMP"])."</TD>";
		echo "<TD>".utf8_encode($row["NomUF"])."</TD>";
		echo "<TD>".$row["HoresUF"]."</TD>";
		if ($row["Baixa"] == True) 
			$sChecked = '';
		else
			$sChecked = ' checked';
		echo "<TD><input type=checkbox name=chbNotaId_".$row["NotaId"].$sChecked." onclick='MatriculaUF(this);'/></TD>";
//		echo "<TD>".$row["NotaId"]."</TD>";
		echo "</TR>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	
echo "</FORM>";

echo "<DIV id=debug></DIV>";

$ResultSet->close();

$conn->close();

?>




















