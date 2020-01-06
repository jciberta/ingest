<?php

/** 
 * NotesModul.php
 *
 * Mostra les notes d'un mòdul.
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
$ModulId = $_GET['ModulId'];
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
CreaIniciHTML($Usuari, 'Notes '.$cf->ObteCodi($CicleId).' '.$Nivell, True, True);

echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Notes.js?v1.1" type="text/javascript"></script>';
//echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

// Inicialització de l'ajuda
// https://getbootstrap.com/docs/4.0/components/popovers/
echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

$Avaluacio = new Avaluacio($conn, $Usuari);
$Avaluacio->Carrega($CursId);
echo $Avaluacio->CreaDescripcio($CursId);

if ($Avaluacio->Estat() != Avaluacio::Tancada)
	echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. Utilitza les fletxes per moure't lliurement per la graella.</font></P>";

$NotesModul = new NotesModul($conn, $Usuari);
$NotesModul->CarregaRegistre($CursId, $ModulId);
$NotesModul->CarregaRegistreMitjanes($CursId, $ModulId);

$Tutoria = new GrupTutoria($conn, $Usuari);
echo $Tutoria->GeneraMostraGrup($CursId);

echo '<span style="float:right;">';
echo $NotesModul->CreaBotoJS('btn', 'Calcula qualificacions finals del mòdul', 'CalculaQualificacionsFinalsModul();');
echo $NotesModul->CreaAjuda('Càlcul de les qualificacions finals del mòdul', 
	'Per al càlcul de les qualificació final del mòdul es té en compte els següents casos:<br>'.
	'<ol>'.
	'<li>Si hi ha alguna UF que no té nota, no es calcula.</li>'.
	'<li>Si totes les UF estan aprovades, es fa la mitja ponderada amb les hores.</li>'.
	'<li>Si totes les UF tenen notes i hi ha alguna suspesa, es fa la mitja ponderada amb les hores, i si aquesta és superior a 4, es queda un 4.</li>'.
	'</ol>'.
	'No obstant, la qualificació final es pot introduir també a mà.'
	);
echo $NotesModul->CreaBotoJS('btn', 'Esborra qualificacions finals del mòdul', 'EsborraQualificacionsFinalsModul();');
echo '</span>';

echo "<P>";

$NotesModul->EscriuFormulari($CicleId, 2, null, 2, $Professor, $Avaluacio);

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>