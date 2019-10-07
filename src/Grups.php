<?php

/** 
 * Grups.php
 *
 * Administració dels grups d'alumnes.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibCurs.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

$CursId = $_GET['CursId'];

$Curs = new Curs($conn, $Usuari);
$Curs->CarregaRegistre($CursId);

// Comprovem que l'usuari té accés a aquesta pàgina per al paràmetres GET donats
// Si intenta manipular els paràmetres des de la URL -> al carrer!
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

CreaIniciHTML($Usuari, 'Grups');
//CreaIniciHTML($Usuari, 'Grups '.$cf->ObteCodi($CicleId).' '.$Nivell);

//echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Matricula.js?v1.0" type="text/javascript"></script>';

$SQL = ' SELECT * FROM MATRICULA M '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' WHERE curs_id='.$CursId;

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	echo '<TABLE class="table table-sm table-fixed table-hover">';
//	echo '<TABLE class="table table-hover table-sm">'; // Taula petita
	echo '<THEAD class="thead-dark">';
	echo '<TH class="col-xs-6">Alumne</TH>';
	echo '<TH class="col-xs-1">Grup</TH>';
	echo '<TH class="col-xs-1">A</TH>';
	echo '<TH class="col-xs-1">B</TH>';
	echo '<TH class="col-xs-1">C</TH>';
	echo '<TH class="col-xs-1">Tutoria</TH>';
	echo '<TH class="col-xs-1">AB</TH>';
	echo '<TH class="col-xs-1">BC</TH>';
	echo '</THEAD>';

	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		echo '<TR>';
		echo "<TD class='col-xs-6' style='text-align:left'>".utf8_encode($row["nom"]." ".$row["cognom1"]." ".$row["cognom2"])."</TD>";
		echo "<TD class='col-xs-1'>".$row["grup"]."</TD>";
		$aGrups = array('A', 'B', 'C');
		foreach($aGrups as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrup(this, ".$Valor.");'";
			$Checked = ($row["grup"] == $item) ? ' checked ' : '';
			echo '<TD class="col-xs-1"><input type="radio" id="'.$item.'" name="Grup_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
		}
		echo "<TD class='col-xs-1'>".$row["grup_tutoria"]."</TD>";
		$aTutoria = array('AB', 'BC');
		foreach($aTutoria as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrupTutoria(this, ".$Valor.");'";
			$Checked = ($row["grup_tutoria"] == $item) ? ' checked ' : '';
			echo '<TD class="col-xs-1"><input type="radio" id="'.$item.'" name="Tutoria_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
		}
		echo '</TR>';
		$row = $ResultSet->fetch_assoc();
	}
	echo '</TABLE>';
}

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$ResultSet->close();

$conn->close(); 
 
?>