<?php

/** 
 * ClickEdu.php
 *
 * Utilitats ClickEdu.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibClickEdu.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

Seguretat::ComprovaAccessUsuari($Usuari, ['SU']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);
if (empty($_GET))
	header("Location: Surt.php");
$accio = (array_key_exists('accio', $_GET)) ? $_GET['accio'] : ''; 

switch ($accio) {
    case "Alumnes":
		CreaIniciHTML($Usuari, 'Alumnes ClickEdu');
		$ce = new ClickEdu($conn, $Usuari, $Sistema);
		//print_h($ce);
		$access_token = $ce->AuthToken();
		print_h($access_token);
		$Resposta = $ce->AuthTokenValidate($access_token);
		print_h($Resposta);
		$Alumnes = $ce->Students($access_token);
		print_h($Alumnes);
        break;
}

?>
