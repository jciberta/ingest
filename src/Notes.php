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
require_once(ROOT.'/lib/LibURL.php');
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

RecuperaGET($_GET);

$CursId = $_GET['CursId'];
$Curs = new Curs($conn, $Usuari);
$Curs->CarregaRegistre($CursId);
$CicleId = $Curs->ObteCicleFormatiuId();
$Nivell = $Curs->ObteNivell();
//print '<br>CursId: '.$CursId.', CicleId: '.$CicleId.', Nivell: '.$Nivell.'<br>';

$ActivaAdministracio = (isset($_GET) && array_key_exists('ActivaAdministracio', $_GET)) ? $_GET['ActivaAdministracio'] : '';

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

// Inicialització de l'ajuda
// https://getbootstrap.com/docs/4.0/components/popovers/
echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

$Avaluacio = new Avaluacio($conn, $Usuari);
$Avaluacio->Carrega($CursId);
echo $Avaluacio->CreaDescripcio($CursId);

if ($Avaluacio->Estat() == Avaluacio::ExtraOrdinaria && $Curs->Estat() == Curs::Actiu) {
	// Missatge recordatori a l'avaluació extraordinària
	echo '<script>$(document).ready(function(){$("#RecordatoriAvExt").modal("show");});</script>';	
	Notes::CreaMissatgeInici();
}

//if ($Avaluacio->Estat() != Avaluacio::Tancada)
if ($Curs->Estat() == Curs::Actiu)
	echo "<P><font color=blue>S'ha de sortir de la cel·la per que la nota quedi desada. ".
		"Utilitza les fletxes per moure't lliurement per la graella. ".
		"Ctrl+rodeta_ratolí per fer zoom.</font></P>";

$Notes = new Notes($conn, $Usuari);
$Notes->CarregaRegistre($CursId, $Nivell);
//echo $Notes->Estadistiques($CursId, $Nivell);
//exit;

$Grup = new GrupClasse($conn, $Usuari);
$Tutoria = new GrupTutoria($conn, $Usuari);

// Filtres
$TextAjuda = 'Mostra els alumnes que estan matriculats i que tenen aprovades totes les UF en convocatòries anteriors.';
echo '<div>';
echo '<input type="checkbox" name="chbBaixes" onclick="MostraBaixes(this);">Mostra baixes &nbsp';
if ($Nivell == 2) {
	echo '<input type="checkbox" name="chbNivell1" checked onclick="MostraGraellaNotes(this, 1);">Notes 1r &nbsp';
	echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Notes 2n &nbsp';
	//echo '<input type="checkbox" name="chbAprovats" onclick="MostraTotAprovat(this);">Tot aprovat &nbsp';
	echo '<input type="checkbox" name="chbConvocatoriesAnteriors" onclick="MostraConvocatoriesAnteriors(this);">Convocatòries anteriors';
	echo $Notes->CreaAjuda('Convocatòries anteriors', $TextAjuda);
}
else {
	echo '<input type="checkbox" name="chbNivell2" checked onclick="MostraGraellaNotes(this, 2);">Alumnes de 2n &nbsp';
	//echo '<input type="checkbox" name="chbAprovats" onclick="MostraTotAprovat(this);">Tot aprovat &nbsp';
	echo '<input type="checkbox" name="chbConvocatoriesAnteriors" onclick="MostraConvocatoriesAnteriors(this);">Convocatòries anteriors';
	echo $Notes->CreaAjuda('Convocatòries anteriors', $TextAjuda);
	echo $Grup->GeneraMostraGrup($CursId);
	echo $Tutoria->GeneraMostraGrup($CursId);
}
echo '<span style="float:right;">';
echo $Notes->CreaBotoDescarregaCSV($CursId);
if ($Usuari->es_admin) {
	// Administració avançada
	echo '&nbsp';
	if ($ActivaAdministracio==1) {
		$URL = GeneraURL("Notes.php?CursId=$CursId");
		echo $Notes->CreaBoto('btnActivaAdministracio', 'Desactiva administració avançada', $URL);
		$Notes->Administracio = true;
	}
	else {
		$URL = GeneraURL("Notes.php?ActivaAdministracio=1&CursId=$CursId");
		echo $Notes->CreaBoto('btnActivaAdministracio', 'Activa administració avançada', $URL);
	}
}
echo '</span>';
echo '</div>';

// Graelles de notes
if ($Nivell == 2) {
	// Notes de 2n 
	$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre2, 2, $Professor, $Avaluacio);

	// Notes de 1r d'alumnes de 2n
	$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre1, 1, $Professor, $Avaluacio);
}
else {
	// Notes de 1r d'alumnes de 1r
	$Notes->EscriuFormulari($CicleId, 1, $Notes->Registre1, 1, $Professor, $Avaluacio);

	// Notes de 1r d'alumnes de 2n
	$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre1, 2, $Professor, $Avaluacio);
}

if ($Avaluacio->Avaluacio == Avaluacio::Ordinaria)
	Notes::CreaMenuContextual($Usuari);

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>