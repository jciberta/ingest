<?php

/** 
 * MatriculaAlumne.php
 *
 * Visualitza la matrícula d'un alumne.
 *
 * GET:
 * - AlumneId: Id de l'alumne.
 * - accio: {MatriculaUF, MostraExpedient}.
 * POST:
 * - alumne: Id de l'alumne.
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

CreaIniciHTML('Visualitza matrícula');
echo '<script language="javascript" src="js/Matricula.js" type="text/javascript"></script>';

if (!empty($_POST))
	$alumne = $_POST['alumne'];
else
	$alumne = $_GET['AlumneId'];

$accio = $_GET['accio'];

$SQL = ' SELECT UF.nom AS NomUF, UF.hores AS HoresUF, MP.codi AS CodiMP, MP.nom AS NomMP, CF.nom AS NomCF, '.
	' U.nom AS NomAlumne, U.cognom1 AS Cognom1Alumne, U.cognom2 AS Cognom2Alumne, '.
	' N.notes_id AS NotaId, N.baixa AS Baixa, '.
	' N.nota1 AS Nota1, N.nota2 AS Nota2, N.nota3 AS Nota3, N.nota4 AS Nota4, N.nota5 AS Nota5, '.
	' UF.*, MP.*, CF.*, N.* '.
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
	$row = $ResultSet->fetch_assoc();
	echo '<div class="alert alert-primary" role="alert">Alumne: <B>'.utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]).'</B></div>';
	echo '<div class="alert alert-primary" role="alert">Cicle: <B>'.utf8_encode($row["NomCF"]).'</B></div>';
	
	echo '<TABLE class="table table-striped">';
	echo '<thead class="thead-dark">';
//	echo "<TH>Cicle</TH>";
	echo "<TH>Mòdul</TH>";
	echo "<TH>UF</TH>";
	echo "<TH>Hores</TH>";
	if ($accio == 'MostraExpedient')
		echo "<TH colspan=5>Notes</TH>";
	else
		echo "<TH>Matrícula</TH>";
	echo '</thead>';

	$ModulAnterior = '';
	while($row) {
		echo "<TR>";
//		echo "<TD>".utf8_encode($row["NomCF"])."</TD>";
		if ($row["CodiMP"] != $ModulAnterior)
			echo "<TD>".utf8_encode($row["CodiMP"].'. '.$row["NomMP"])."</TD>";
		else 
			echo "<TD></TD>";
		$ModulAnterior = $row["CodiMP"];
		echo "<TD>".utf8_encode($row["NomUF"])."</TD>";
		echo "<TD>".$row["HoresUF"]."</TD>";
		if ($row["Baixa"] == True) 
			$sChecked = '';
		else
			$sChecked = ' checked';
		if ($accio == 'MostraExpedient') {
			echo "<TD><input style='width:2em;text-align:center' type=text disabled name=edtNota1 value='".$row["Nota1"]."'></TD>";
			echo "<TD><input style='width:2em;text-align:center' type=text disabled name=edtNota2 value='".$row["Nota2"]."'></TD>";
			echo "<TD><input style='width:2em;text-align:center' type=text disabled name=edtNota3 value='".$row["Nota3"]."'></TD>";
			echo "<TD><input style='width:2em;text-align:center' type=text disabled name=edtNota4 value='".$row["Nota4"]."'></TD>";
			echo "<TD><input style='width:2em;text-align:center' type=text disabled name=edtNota5 value='".$row["Nota5"]."'></TD>";
		}
		else
			echo "<TD><input type=checkbox name=chbNotaId_".$row["NotaId"].$sChecked." onclick='MatriculaUF(this);'/></TD>";
		echo "</TR>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	

echo "<DIV id=debug></DIV>";

$ResultSet->close();

$conn->close();

?>




















