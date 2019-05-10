<?php

/** 
 * Notes.php
 *
 * Mostra les notes d'un curs.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibNotes.php');
require_once(ROOT.'/lib/LibAvaluacio.php');
require_once(ROOT.'/lib/LibFP.php');
require_once(ROOT.'/lib/LibCurs.php');
require_once(ROOT.'/lib/LibProfessor.php');

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
$Professor = new Professor($conn, $Usuari);
$Professor->CarregaUFAssignades();
if (!$Professor->TeUFEnCicleNivell($CicleId, $Nivell) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

$cf = new CicleFormatiu($conn);
CreaIniciHTML($Usuari, 'Notes '.$cf->ObteCodi($CicleId).' '.$Nivell);

echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Notes.js?v1.8" type="text/javascript"></script>';
echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

$Avaluacio = new Avaluacio($conn, $Usuari);
echo $Avaluacio->CreaDescripcio($CursId);

$EstatAvaluacio = $Avaluacio->Estat($CursId);
if ($EstatAvaluacio != Avaluacio::Tancada)
	echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. Utilitza les fletxes per moure't lliurement per la graella.</font></P>";

$SQL = CreaSQLNotes($CicleId, $Nivell);
//print_r($SQL);

$ResultSet = $conn->query($SQL);

if ($ResultSet->num_rows > 0) {
//print_r($ResultSet);	

	// Creem 2 objectes per administrar les notes de 1r i de 2n respectivament
	$Notes1 = new stdClass();
	$Notes2 = new stdClass();
	$i = -1; 
	$j1 = 0;
	$j2 = 0;
	$AlumneId = -1;
	$row = $ResultSet->fetch_assoc();
	while($row) {
//print_r($row);
		if ($row["NivellUF"] == 1) {
			if ($row["AlumneId"] != $AlumneId) {
				$AlumneId = $row["AlumneId"];
				$i++;
				$Notes1->Alumne[$i] = $row;
				$Notes2->Alumne[$i] = $row;
				$j1 = 0; 
				$j2 = 0; 
			}	
			$Notes1->UF[$i][$j1] = $row;
			$j1++;
		}
		else if ($row["NivellUF"] == 2) {
			if ($row["AlumneId"] != $AlumneId) {
				$AlumneId = $row["AlumneId"];
				$i++;
				$Notes1->Alumne[$i] = $row;
				$Notes2->Alumne[$i] = $row;
				$j1 = 0; 
				$j2 = 0; 
			}	
			$Notes2->UF[$i][$j2] = $row;
			$j2++;
		}
		$row = $ResultSet->fetch_assoc();
	}
//print_r($Notes1);
//print_r($Notes2);

	echo '<input type="checkbox" name="chbBaixes" checked onclick="MostraBaixes(this);">Mostra baixes &nbsp';
	if ($Nivell == 2) {
		echo '<input type="checkbox" name="chbNivell1" checked onclick="MostraGraellaNotes(this, 1);">Notes 1r &nbsp';
		echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Notes 2n';
		// Notes de 2n 
		Notes::EscriuFormulari($CicleId, 2, $Notes2, 2, $Professor, $EstatAvaluacio);
		// Notes de 1r d'alumnes de 2n
		Notes::EscriuFormulari($CicleId, 2, $Notes1, 1, $Professor, $EstatAvaluacio);
	}
	else {
		echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Alumnes de 2n';
		// Notes de 1r d'alumnes de 1r
		Notes::EscriuFormulari($CicleId, 1, $Notes1, 1, $Professor, $EstatAvaluacio);
		// Notes de 1r d'alumnes de 2n
		Notes::EscriuFormulari($CicleId, 2, $Notes1, 2, $Professor, $EstatAvaluacio);
	}

}

if ($Avaluacio->Avaluacio == Avaluacio::Ordinaria)
	Notes::CreaMenuContextual($Usuari);

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$ResultSet->close();

$conn->close(); 
 
 ?>
