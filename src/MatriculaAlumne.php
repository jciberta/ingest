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
require_once('lib/LibExpedient.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

if (!empty($_POST))
	$alumne = $_POST['alumne'];
else
	$alumne = $_GET['AlumneId'];
$accio = $_GET['accio'];

// Si intenta manipular l'usuari des de la URL -> al carrer!
if (($Usuari->es_alumne) && ($Usuari->usuari_id != $alumne))
	header("Location: Surt.php");

CreaIniciHTML($Usuari, 'Visualitza matrícula');
echo '<script language="javascript" src="js/Matricula.js?v1.0" type="text/javascript"></script>';

$SQL = Expedient::SQL($alumne);
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	$row = $ResultSet->fetch_assoc();
	echo '<div class="alert alert-primary" role="alert">Alumne: <B>'.utf8_encode($row["NomAlumne"]." ".$row["Cognom1Alumne"]).'</B></div>';
	echo '<div class="alert alert-primary" role="alert">Cicle: <B>'.utf8_encode($row["NomCF"]).'</B></div>';
	
	echo '<TABLE class="table table-striped">';
	echo '<thead class="thead-dark">';
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
			for ($i=1; $i<6; $i++) {
				$style = 'width:2em;text-align:center';
				if ($row['convocatoria'] == $i) {
					// Marquem la convocatòria actual
					$style .= ';border-width:1px;border-color:blue';
					if ($row['orientativa'])
						$style .= ";background-color:yellow";
				}
				echo "<TD><input style='".$style."' type=text disabled name=edtNota1 value='".$row["Nota".$i]."'></TD>";
			}
		}
		else
			echo "<TD><input type=checkbox name=chbNotaId_".$row["NotaId"].$sChecked." onclick='MatriculaUF(this);'/></TD>";
		echo "</TR>";
		$row = $ResultSet->fetch_assoc();
	}
	echo "</TABLE>";
};	

if ($accio == 'MostraExpedient') {
	echo "<DIV id=DescarregaExpedientPDF>";
	echo '<a href="ExpedientPDF.php?AlumneId='.$alumne.'" class="btn btn-primary active" role="button" aria-pressed="true" id="btnDescarregaPDF" name="btnDescarregaPDF_'.$alumne.'">Descarrrega PDF</a>';
	echo "</DIV>";
}

echo "<DIV id=debug></DIV>";

$ResultSet->close();

$conn->close();

?>




















