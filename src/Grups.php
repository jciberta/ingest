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
	header("Location: Surt.php");
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
	echo '<TABLE class="table table-fixed table-striped table-hover table-sm">';
	echo '<THEAD class="thead-dark">';
	echo '<TH width=200 style="text-align:left">Alumne</TH>';
	echo '<TH width=75 style="text-align:center">Grup</TH>';
	echo '<TH width=20>A</TH>';
	echo '<TH width=20>B</TH>';
	echo '<TH width=20>C</TH>';
	echo '<TH width=75 style="text-align:center">Tutoria</TH>';
	echo '<TH width=20>A</TH>';
	echo '<TH width=20>B</TH>';
	echo '<TH> </TH>';
	echo '</THEAD>';

	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		echo '<TR>';
		echo "<TD width=200 style='text-align:left'>".utf8_encode($row["nom"]." ".$row["cognom1"]." ".$row["cognom2"])."</TD>";
		echo "<TD width=75 style='text-align:center'>".$row["grup"]."</TD>";
		$aGrups = array('A', 'B', 'C');
		foreach($aGrups as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrup(this, ".$Valor.");'";
			$Checked = ($row["grup"] == $item) ? ' checked ' : '';
			echo '<TD width=20 style="text-align:center"><input type="radio" id="'.$item.'" name="Grup_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
		}
		echo "<TD width=75 style='text-align:center'>".$row["grup_tutoria"]."</TD>";
		$aTutoria = array('AB', 'BC');
		foreach($aTutoria as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrupTutoria(this, ".$Valor.");'";
			$Checked = ($row["grup_tutoria"] == $item) ? ' checked ' : '';
			echo '<TD width=20 style="text-align:center"><input type="radio" id="'.$item.'" name="Tutoria_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
		}
		echo '<TD> </TD>';
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