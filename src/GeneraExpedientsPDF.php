<?php

/** 
 * GeneraExpedientsPDF.php
 *
 * Genera els expedient en PDF d'un curs.
 *
 * GET:
 * - CursId: Id del curs a administrar.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibSistema.php');
require_once(ROOT.'/lib/LibAvaluacio.php');
require_once(ROOT.'/lib/LibExpedient.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

// Comprovem que l'usuari té accés a aquesta pàgina.
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

// Paràmetres de la URL (si n'hi ha).
$CursId = (isset($_GET) && array_key_exists('CursId', $_GET)) ? $_GET['CursId'] : -1;
if ($CursId == -1)
	header("Location: Surt.php");

CreaIniciHTML($Usuari, "Generació d'expedients en PDF");
//echo '<script language="javascript" src="js/Avaluacio.js?v1.5" type="text/javascript"></script>';

$Avaluacio = new Avaluacio($conn, $Usuari);
$Avaluacio->Carrega($CursId);
//echo $Avaluacio->CreaMissatges();

echo "<h2>Avaluació actual</h2>";
$Avaluacio->EscriuTaula();

$Expedient = new Expedient($conn);
$Sufix = $Avaluacio->EstatText();

echo "<HR>";

echo "Preparant directori per als expedients... ";
EsborraDirectori(ROOT."/scripts/pdf");
mkdir(ROOT."/scripts/pdf");
echo "Ok.<BR>";

echo "Generant l'script per als expedients... ";
$Comanda = "";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
	$Comanda = "scripts/ScriptExpedients.bat";
else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') 
	$Comanda = "scripts/ScriptExpedients.sh";
$myfile = fopen($Comanda, "w") or die("Unable to open file!");
$Text = $Expedient->GeneraScript($CursId, $Sufix);
fwrite($myfile, $Text);
fclose($myfile);
echo "Ok.<BR>";

echo "Executant l'script per als expedients";
$aText = explode("\r\n",$Text);
//echo "<PRE>";
for ($i=0; $i<count($aText)-1; $i++) {
	//echo "  Executant $aText[$i]<BR>";
	echo ".";
	$Result = shell_exec($aText[$i]);
}
//echo "</PRE>";
echo " Ok.<BR>";

// https://stackoverflow.com/questions/17708562/zip-all-files-in-directory-and-download-generated-zip
$zipname = ROOT."/scripts/Expedients.zip";
echo "Comprimint els expedients";
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
if ($handle = opendir(ROOT."/scripts/pdf")) {
	//echo "<PRE>";
	while (false !== ($entry = readdir($handle))) {
		$ext = pathinfo($entry, PATHINFO_EXTENSION);
		if (strtoupper($ext) == "PDF") {
			//echo "  Comprimint $entry<br>";
			echo ".";
			$zip->addFile(ROOT."/scripts/pdf/".$entry, $entry);
		}
	}
	//echo "</PRE>";
	closedir($handle);
}
$zip->close();
echo " Ok.<BR>";

echo "Podeu descarregar els expedients comprimits <a href='scripts/Expedients.zip'>aquí</a>. Mida: ".FormataBytes(filesize($zipname));

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>