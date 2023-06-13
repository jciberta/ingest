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
require_once(ROOT.'/lib/LibUsuari.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

$CursId = mysqli_real_escape_string($conn, $_GET['CursId']);
$Curs = new Curs($conn, $Usuari, $Sistema);
$Curs->CarregaRegistre($CursId);
$CicleId = $Curs->ObteCicleFormatiuId();
$Nivell = $Curs->ObteNivell();
//print '<br>CursId: '.$CursId.', CicleId: '.$CicleId.', Nivell: '.$Nivell.'<br>';

$ActivaAdministracio = (isset($_GET) && array_key_exists('ActivaAdministracio', $_GET)) ? $_GET['ActivaAdministracio'] : '';

// Comprovem que l'usuari té accés a aquesta pàgina per al paràmetres GET donats
// Si intenta manipular els paràmetres des de la URL -> al carrer!
$Professor = new Professor($conn, $Usuari, $Sistema);
$Professor->CarregaUFAssignades();
//if (!$Professor->TeUFEnCicleNivell($CicleId, $Nivell) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
if (!$Professor->TeUFEnCicle($CicleId) && !$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_administratiu)
	header("Location: Surt.php");
$Professor->CarregaTutor($CursId);

$cf = new CicleFormatiu($conn, $Usuari, $Sistema);
//CreaIniciHTML($Usuari, 'Notes '.$cf->ObteCodi($CicleId).' '.$Nivell, True, True, True);
CreaIniciHTML_Notes($Usuari, 'Notes '.$cf->ObteCodi($CicleId).' '.$Nivell);

echo '<script language="javascript" src="vendor/keycode.min.js" type="text/javascript"></script>';
// Pedaç per forçar el navegador a regarregar el JavaScript i no usar la caché.
// https://stackoverflow.com/questions/44456644/javascript-function-not-working-due-to-cached-js-file
// https://community.esri.com/thread/187211-how-to-force-a-browser-cache-refresh-after-updating-wab-app
echo '<script language="javascript" src="js/Notes.js?v1.9" type="text/javascript"></script>';
//echo '<script language="javascript" type="text/javascript">let timerId = setInterval(ActualitzaTaulaNotes, 5000);</script>';

//$Columnes = ($Usuari->es_admin || $Usuari->es_direccio || $Usuari->es_cap_estudis) ? 5 : 3;

$Notes = new Notes($conn, $Usuari, $Sistema);
$Notes->CarregaRegistre($CursId, $Nivell);

if ($Notes->MetodeCongelaFilesComunes == Notes::tcMetodeNou) {
	echo "
		<script>
		$(document).ready(function() {
			$('#notes1').css('max-height', $(window).height()-300 + 'px');
			$('#notes2').css('max-height', $(window).height()-300 + 'px');
		} );
		</script>
	";
}

// Inicialització de l'ajuda
// https://getbootstrap.com/docs/4.0/components/popovers/
echo '<script>$(function(){$("[data-toggle=popover]").popover()});</script>';

$Avaluacio = new Avaluacio($conn, $Usuari, $Sistema);
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

$Grup = new GrupClasse($conn, $Usuari, $Sistema);
$Tutoria = new GrupTutoria($conn, $Usuari, $Sistema);

// Filtres
$TextAjuda = 'Mostra els alumnes que tenen UF pendents';
echo '<div>';
echo '<input type="checkbox" name="chbBaixes" onclick="MostraBaixes(this);">Mostra baixes &nbsp';
echo '<input type="checkbox" name="chbAlumnesUFPendents" onclick="MostraAlumnesUFPendents(this);">Alumnes UF Pendents &nbsp';
echo $Notes->CreaAjuda('Alumnes UF Pendents', $TextAjuda);
if ($Avaluacio->Estat() != Avaluacio::Tancada) {
	//echo "<input type='checkbox' name='chbConvocatoriesAnteriors' onclick='MostraConvocatoriesAnteriors(this);'>Convocatòries anteriors";
	//echo $Notes->CreaAjuda('Convocatòries anteriors', $TextAjuda);
}
if ($Nivell == 2) {
	//echo '<input type="checkbox" name="chbConvocatoriesAnteriors" onclick="MostraConvocatoriesAnteriors(this);">Convocatòries anteriors';
	//echo $Notes->CreaAjuda('Convocatòries anteriors', $TextAjuda);
}
else {
	//echo '<input type="checkbox" name="chbConvocatoriesAnteriors" onclick="MostraConvocatoriesAnteriors(this);">Convocatòries anteriors';
	//echo $Notes->CreaAjuda('Convocatòries anteriors', $TextAjuda);
	//echo '<input type="checkbox" name="chbAprovats" onclick="MostraTotAprovat(this);">Tot aprovat &nbsp';
	echo $Grup->GeneraMostraGrup($CursId);
	echo $Tutoria->GeneraMostraGrup($CursId);
}
echo '<span style="float:right;">';
echo $Notes->CreaBotoDescarrega($CursId);
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

echo '<br/>';

// Graelles de notes
if ($Nivell == 2) {
	echo '<nav>';
	echo '  <div class="nav nav-tabs" id="nav-tab" role="tablist">';
	echo '    <a class="nav-item nav-link active" id="nav1-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Matèries de 2n</a>';
	echo '    <a class="nav-item nav-link" id="nav2-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Matèries de 1r</a>';
	echo '  </div>';
	echo '</nav>';
	echo '<div class="tab-content" id="nav-tabContent">';
	echo '  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav1-tab">';
	// Notes de 2n 
	$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre2, 2, $Professor, $Avaluacio);
	echo '  </div>';
	echo '  <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav2-tab">';
	// Notes de 1r d'alumnes de 2n
	$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre1, 1, $Professor, $Avaluacio);
	echo '  </div>';
	echo '</div>';
}
else {
	echo '<nav>';
	echo '  <div class="nav nav-tabs" id="nav-tab" role="tablist">';
	echo '    <a class="nav-item nav-link active" id="nav1-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Alumnes de 1r</a>';
	echo '    <a class="nav-item nav-link" id="nav2-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Alumnes de 2n</a>';
	echo '  </div>';
	echo '</nav>';
	echo '<div class="tab-content" id="nav-tabContent">';
	echo '  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav1-tab">';
	// Notes de 1r d'alumnes de 1r
	$Notes->EscriuFormulari($CicleId, 1, $Notes->Registre1, 1, $Professor, $Avaluacio);
	echo '  </div>';
	echo '  <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav2-tab">';
	// Notes de 1r d'alumnes de 2n
	$Notes->EscriuFormulari($CicleId, 2, $Notes->Registre1, 2, $Professor, $Avaluacio);
	echo '  </div>';
	echo '</div>';
}

if ($Avaluacio->Avaluacio == Avaluacio::Ordinaria)
	Notes::CreaMenuContextual($Usuari);

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>