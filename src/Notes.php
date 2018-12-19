<?php

/** 
 * Notes.php
 *
 * Mostra les notes d'un cicle i un nivell.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once('lib/LibStr.php');
require_once('lib/LibHTML.php');
require_once('lib/LibNotes.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

CreaIniciHTML($Usuari, 'Notes cicle/nivell');

// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Notes.js?v1.1" type="text/javascript"></script>';
echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. Utilitza les fletxes per moure't lliurement per la graella.</font></P>";

$CicleId = $_GET['CicleId'];
$Nivell = $_GET['Nivell'];

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

	if ($Nivell == 2) {
		echo '<input type="checkbox" name="chbNivell1" onclick="MostraGraellaNotes(this, 1);">Notes 1r &nbsp';
		echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Notes 2n';
		Notes::EscriuFormulari($CicleId, 2, $Notes2);
	}
	Notes::EscriuFormulari($CicleId, 1, $Notes1);

}

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$ResultSet->close();

$conn->close(); 
 
 ?>
