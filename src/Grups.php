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
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibProfessor.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

$CursId = $_GET['CursId'];

$Curs = new Curs($conn, $Usuari);
$Curs->CarregaRegistre($CursId);

// Comprovem que l'usuari té accés a aquesta pàgina per al paràmetres GET donats
// Si intenta manipular els paràmetres des de la URL -> al carrer!
$Professor = new Professor($conn, $Usuari);
$CursTutorId = $Professor->ObteCursTutorId();
if (!($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis || ($Usuari->es_professor && $CursId == $CursTutorId)))
//if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && )
	header("Location: Surt.php");

CreaIniciHTML($Usuari, 'Grups', True);
//CreaIniciHTML($Usuari, 'Grups '.$cf->ObteCodi($CicleId).' '.$Nivell);

//echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Matricula.js?v1.0" type="text/javascript"></script>';

$SQL = ' SELECT * FROM MATRICULA M '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' WHERE curs_id='.$CursId.
	' ORDER BY U.cognom1, U.cognom2, U.nom ';

$ResultSet = $conn->query($SQL);

$GrupClasse = new GrupClasse($conn, $Usuari);
$aGrups = $GrupClasse->ObteGrups($CursId);
//print_r($aGrups);
$GrupTutoria = new GrupTutoria($conn, $Usuari);
$aTutoria = $GrupTutoria->ObteGrups($CursId);
//$aGrups = array('A', 'B', 'C', 'D');
//$aTutoria = array('AB', 'BC', 'CD');

if ($ResultSet->num_rows > 0) {
	echo '<TABLE id="taula" class="table table-fixed table-striped table-hover table-sm">';
	echo '<THEAD class="thead-dark">';
	echo '<TH width=300 style="text-align:left">Alumne</TH>';
	echo '<TH width=75 style="text-align:center">Grup</TH>';
	foreach($aGrups as $item) 
		echo "<TH width=20>$item</TH>";
	echo '<TH width=75 style="text-align:center">Tutoria</TH>';
	foreach($aTutoria as $item) 
		echo "<TH width=30>$item</TH>";
	echo '<TH> </TH>';
	echo '</THEAD>';

	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		echo '<TR>';
		echo "<TD width=300 style='text-align:left'>".utf8_encode($row["nom"]." ".$row["cognom1"]." ".$row["cognom2"])."</TD>";
		echo "<TD width=75 style='text-align:center'>".$row["grup"]."</TD>";
		
		foreach($aGrups as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrup(this, ".$Valor.");'";
			$Checked = ($row["grup"] == $item) ? ' checked ' : '';
			echo '<TD width=20 style="text-align:center"><input type="radio" id="'.$item.'" name="Grup_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
		}
		echo "<TD width=75 style='text-align:center'>".$row["grup_tutoria"]."</TD>";
		foreach($aTutoria as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrupTutoria(this, ".$Valor.");'";
			$Checked = ($row["grup_tutoria"] == $item) ? ' checked ' : '';
			echo '<TD width=30 style="text-align:center"><input type="radio" id="'.$item.'" name="Tutoria_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
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