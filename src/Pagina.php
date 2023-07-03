<?php

/** 
 * Pagina.php
 *
 * Pàgina de pròposit general.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!isset($_GET))
	header("Location: Surt.php");

RecuperaGET($_GET);
$accio = (array_key_exists('accio', $_GET)) ? $_GET['accio'] : ''; 

switch ($accio) {
    case "QuantA":
		if (!$Usuari->es_admin)
			header("Location: Surt.php");
		CreaIniciHTML($Usuari, "Quant a...");
		echo "Navegador: ".$_SERVER['HTTP_USER_AGENT']."<br>";
		echo "Sistema operatiu: ".PHP_OS."<br>";
		echo "Equip: ".php_uname()."<br>";
		$version = apache_get_version();
		echo "Servidor web: $version<br>";
		echo "MySQL server version: ".mysqli_get_server_version($conn);

		// https://stackoverflow.com/questions/52865732/how-to-embed-phpinfo-within-a-page-without-affecting-that-pages-css-styles#52865821
		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();
		$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
		echo "
			<style type='text/css'>
				#phpinfo {}
				#phpinfo pre {margin: 0; font-family: monospace;}
				#phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
				#phpinfo a:hover {text-decoration: underline;}
				#phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
				#phpinfo .center {text-align: center;}
				#phpinfo .center table {margin: 1em auto; text-align: left;}
				#phpinfo .center th {text-align: center !important;}
				#phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
				#phpinfo h1 {font-size: 150%;}
				#phpinfo h2 {font-size: 125%;}
				#phpinfo .p {text-align: left;}
				#phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
				#phpinfo .h {background-color: #99c; font-weight: bold;}
				#phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
				#phpinfo .v i {color: #999;}
				#phpinfo img {float: right; border: 0;}
				#phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
			</style>
			<div id='phpinfo'>
				$phpinfo
			</div>
		";
		CreaFinalHTML();	
        break;
	case "DialegImportaNotes":
		if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis && !$Usuari->es_professor)
			header("Location: Surt.php");
		CreaIniciHTML($Usuari, "Importació de notes");
		$UnitatPlaEstudiId = $_GET['UnitatPlaEstudiId'];
		echo "<P>Seleccioneu el fitxer XLSX a importar:</P>";
		echo '<form action="ImportaNotes.php" method="post" enctype="multipart/form-data">';
		echo '	<div class="form-group">';
		echo '		<input class="form-control-file" type="file" name="Fitxer" id="Fitxer" accept=".xlsx">';
		echo '		<input type="hidden" name="UnitatPlaEstudiId" id="UnitatPlaEstudiId" value="'.$UnitatPlaEstudiId.'">';
		echo '	</div>';
		echo '	<input type="submit" name="submit" value="Importa" class="btn btn-primary">';
		echo '</form>';		
		break;
	case 'ApacheErrorLog':
		Seguretat::ComprovaAccessUsuari($Usuari, ['SU']);
		CreaIniciHTML($Usuari, "Apache error.log");
		$Ordre = '';
		// https://sourceforge.net/projects/unxutils/: tail per Windows. Cal copiar-lo a C:\Windows\System
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
			$Ordre = 'tail F:\xampp\apache\logs\error.log';
		}
		else if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
			if ($Usuari->aplicacio == 'InGest')
				$Ordre = 'tail /var/log/apache2/error.log';
			else if ($Usuari->aplicacio == 'CapGest')
				$Ordre = 'tail ../../log/error.log';
		}
		$Result = shell_exec($Ordre);
		echo nl2br($Result);
		break;
}

$conn->close();

?>