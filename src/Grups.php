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
//require_once(ROOT.'/lib/LibStr.php');
//require_once(ROOT.'/lib/LibHTML.php');
//require_once(ROOT.'/lib/LibNotes.php');
//require_once(ROOT.'/lib/LibAvaluacio.php');
//require_once(ROOT.'/lib/LibFP.php');
require_once(ROOT.'/lib/LibCurs.php');
//require_once(ROOT.'/lib/LibProfessor.php');

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






$CicleId = $Curs->ObteCicleFormatiuId();
$Nivell = $Curs->ObteNivell();
//print '<br>CursId: '.$CursId.', CicleId: '.$CicleId.', Nivell: '.$Nivell.'<br>';

// Comprovem que l'usuari té accés a aquesta pàgina per al paràmetres GET donats
// Si intenta manipular els paràmetres des de la URL -> al carrer!
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

//$cf = new CicleFormatiu($conn);
CreaIniciHTML($Usuari, 'Grups');
//CreaIniciHTML($Usuari, 'Grups '.$cf->ObteCodi($CicleId).' '.$Nivell);

//echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Matricula.js?v1.0" type="text/javascript"></script>';

/*$Avaluacio = new Avaluacio($conn, $Usuari);
echo $Avaluacio->CreaDescripcio($CursId);

$EstatAvaluacio = $Avaluacio->Estat($CursId);
if ($EstatAvaluacio != Avaluacio::Tancada)
	echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. Utilitza les fletxes per moure't lliurement per la graella.</font></P>";
*/
//$Notes = new Notes($conn, $Usuari);
//$SQL = $Notes->CreaSQL($CursId, $Nivell);
//$SQL = CreaSQLNotes($CursId, $Nivell);
//$SQL = CreaSQLNotes($CicleId, $Nivell);
//print_r($SQL.'<P>');

$SQL = ' SELECT * FROM MATRICULA M '.
	' LEFT JOIN USUARI U ON (M.alumne_id=U.usuari_id) '.
	' WHERE curs_id='.$CursId;

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
	echo '<TABLE class="table table-striped">';
	echo '<THEAD class="thead-dark">';
	echo '<TH>Alumne</TH>';
	echo '<TH>Grup</TH>';
	echo '<TH>A</TH>';
	echo '<TH>B</TH>';
	echo '<TH>C</TH>';
	echo '<TH>Tutoria</TH>';
	echo '<TH>AB</TH>';
	echo '<TH>BC</TH>';
	echo '</THEAD>';
//print_r($ResultSet);	

/*	// Creem 2 objectes per administrar les notes de 1r i de 2n respectivament
	$Notes1 = new stdClass();
	$Notes2 = new stdClass();
	$i = -1; 
	$j1 = 0;
	$j2 = 0;
	$AlumneId = -1;*/
	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		echo '<TR>';
		echo "<TD style='text-align:left'>".utf8_encode($row["nom"]." ".$row["cognom1"]." ".$row["cognom2"])."</TD>";
		echo "<TD>".$row["grup"]."</TD>";
		$aGrups = array('A', 'B', 'C');
		foreach($aGrups as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrup(this, ".$Valor.");'";
			$Checked = ($row["grup"] == $item) ? ' checked ' : '';
			echo '<TD><input type="radio" id="'.$item.'" name="Grup_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
		}
		echo "<TD>".$row["grup_tutoria"]."</TD>";
		$aTutoria = array('AB', 'BC');
		foreach($aTutoria as $item) {
			$Valor = '"'.$item.'"';
			$Funcio = "'AssignaGrupTutoria(this, ".$Valor.");'";
			$Checked = ($row["grup_tutoria"] == $item) ? ' checked ' : '';
			echo '<TD><input type="radio" id="'.$item.'" name="Tutoria_'.$CursId.'_'.$row["usuari_id"].'" value="'.$item.'" onclick='.$Funcio.$Checked.'></TD>';
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