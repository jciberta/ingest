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
	header("Location: Surt.php");
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
$Professor->CarregaTutor($CursId);

$cf = new CicleFormatiu($conn);
CreaIniciHTML($Usuari, 'Notes '.$cf->ObteCodi($CicleId).' '.$Nivell);

echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Notes.js?v1.4" type="text/javascript"></script>';
//echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

$Avaluacio = new Avaluacio($conn, $Usuari);
$Avaluacio->Carrega($CursId);
echo $Avaluacio->CreaDescripcio($CursId);

//$EstatAvaluacio = $Avaluacio->Estat();
if ($Avaluacio->Estat() != Avaluacio::Tancada)
	echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. Utilitza les fletxes per moure't lliurement per la graella.</font></P>";

$Notes = new Notes($conn, $Usuari);
$Notes->CarregaRegistre($CursId, $Nivell);

$Grup = new GrupClasse($conn, $Usuari);
$Tutoria = new GrupTutoria($conn, $Usuari);

if (True) {
	echo '<input type="checkbox" name="chbBaixes" checked onclick="MostraBaixes(this);">Mostra baixes &nbsp';
	if ($Nivell == 2) {
		echo '<input type="checkbox" name="chbNivell1" checked onclick="MostraGraellaNotes(this, 1);">Notes 1r &nbsp';
		echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Notes 2n &nbsp';
		echo '<input type="checkbox" name="chbAprovats" onclick="MostraTotAprovat(this);">Tot aprovat &nbsp';

		// Notes de 2n 
		$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre2, 2, $Professor, $Avaluacio);

		// Notes de 1r d'alumnes de 2n
		$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre1, 1, $Professor, $Avaluacio);
	}
	else {
		echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Alumnes de 2n &nbsp';
		echo '<input type="checkbox" name="chbAprovats" onclick="MostraTotAprovat(this);">Tot aprovat &nbsp';
		echo $Grup->GeneraMostraGrup($CursId);
		echo $Tutoria->GeneraMostraGrup($CursId);

		// Notes de 1r d'alumnes de 1r
		$Notes->EscriuFormulari($CicleId, 1, $Notes->Registre1, 1, $Professor, $Avaluacio);

		// Notes de 1r d'alumnes de 2n
		$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre1, 2, $Professor, $Avaluacio);
	}
}

if ($Avaluacio->Avaluacio == Avaluacio::Ordinaria)
	Notes::CreaMenuContextual($Usuari);

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>