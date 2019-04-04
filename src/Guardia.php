<?php

/** 
 * Guardia.php
 *
 * Administració de les guàrdies de professors.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibGuardia.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

// Comprovem que l'usuari té accés a aquesta pàgina.
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

// Connexió a la base de dades.
$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

// Paràmetres de la URL (si n'hi ha).
$Dia = (isset($_GET) && array_key_exists('Dia', $_GET)) ? $_GET['Dia'] : 0;

CreaIniciHTML($Usuari, 'Guàrdies');
echo '<script language="javascript" src="js/Guardia.js?v1.1" type="text/javascript"></script>';

echo "<DIV id=taula>";
$Dia = 2;
$Guardia = new Guardia($conn);
//$Guardia->EscriuTaula();
$Guardia->EscriuTaula($Dia);
echo "</DIV>";

echo $Guardia->CreaBotoGeneraProperDia($Dia);

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>
