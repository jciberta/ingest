<?php

/** 
 * Notes.php
 *
 * Mostra les notes d'un cicle i un nivell.
 */

require_once('Config.php');
require_once('LibHTML.php');

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

CreaIniciHTML('Notes cicle/nivell');
echo '<script language="javascript" src="js/jquery-3.3.1.min.js" type="text/javascript"></script>';
echo '<script language="javascript" src="js/Notes.js" type="text/javascript"></script>';

echo "<P><font color=blue>S'ha de sortir de la cel·la per que quedi desada.</font></P>";

$CicleId = $_GET['CicleId'];
$Nivell = $_GET['Nivell'];

$SQL = ' SELECT M.alumne_id AS AlumneId, '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
	' UF.codi AS CodiUF, '.
	' MP.codi AS CodiMP, '.
	' N.notes_id AS NotaId, N.baixa AS Baixa, N.convocatoria AS Convocatoria, '.
	' N.*, U.* '.
	' FROM NOTES N '.
	' LEFT JOIN MATRICULA M ON (M.matricula_id=N.matricula_id) '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' LEFT JOIN UNITAT_FORMATIVA UF ON (UF.unitat_formativa_id=N.uf_id) '.
	' LEFT JOIN MODUL_PROFESSIONAL MP ON (MP.modul_professional_id=UF.modul_professional_id) '.
	' WHERE M.cicle_formatiu_id='.$CicleId.' AND M.nivell='.$Nivell.
	' ORDER BY M.alumne_id, MP.codi, UF.codi ';
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
//print_r($ResultSet);	

	// Creem un objecte per administrar les notes
	$Notes = new stdClass();
	$i = -1; 
	$j = 0;
	$AlumneId = -1;
	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		if ($row["AlumneId"] != $AlumneId) {
			$AlumneId = $row["AlumneId"];
			$i++;
			$Notes->Alumne[$i] = $row;
			$j = 0; 
		}	
		$Notes->UF[$i][$j] = $row;
		$j++;
		$row = $ResultSet->fetch_assoc();
	}
//print_r($Notes);



	echo '<FORM method="post" action="">';
//	echo '<TABLE border=1 width='.(100*count($Notes->UF[0])+10*(count($Notes->UF[0])-1)).'>';
	echo '<TABLE border=0 width="100%">';

	// Capçalera de la taula
	echo "<TR><TD width=200></TD>";
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		echo "<TD width=25>".utf8_encode($row["CodiMP"])."</TD>";
	}
	echo "<TD></TD></TR>";
	echo "<TR><TD width=200></TD>";
	for($j = 0; $j < count($Notes->UF[0]); $j++) {
		$row = $Notes->UF[0][$j];
		echo "<TD width=25>".utf8_encode($row["CodiUF"])."</TD>";
	}
	echo "<TD></TD></TR>";

	for($i = 0; $i < count($Notes->Alumne); $i++) {
		echo "<TR>";
		$row = $Notes->Alumne[$i];
		echo "<TD width=200>".utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]." ".$row["Cognom2Alumne"])."</TD>";
		for($j = 0; $j < count($Notes->UF[$i]); $j++) {
			$row = $Notes->UF[$i][$j];
			$ValorNota = $row["nota".$row["Convocatoria"]];
			echo "<TD width=2><input type=text name=txtNotaId_".$row["NotaId"]."_".$row["Convocatoria"]." value='".$ValorNota."' size=1 onBlur='ActualitzaNota(this);'></TD>";
		}
		echo "<TD></TD></TR>";
	}
	echo "</TABLE>";

echo '<BR><input type="text" value="Hi!" name="nom" id="nom" size=4><BR>';


	echo "</FORM>";
	
	
echo '<form> <label for="ccnum">CC Number</label><br> <input size="16" name="ccnum" id="ccnum">
<br> <label for="ccv">CCV</label> <input id="ccv" name="ccv" size="4"> </form>';

	
	
	

}

echo "<DIV id=debug></DIV>";

$ResultSet->close();

$conn->close(); 
 
 ?>