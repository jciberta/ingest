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
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibGuardia.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Festiu = unserialize($_SESSION['FESTIU']);

// Comprovem que l'usuari té accés a aquesta pàgina.
if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

// Connexió a la base de dades.
$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

RecuperaGET($_GET);

// Paràmetres de la URL (si n'hi ha).
$Dia = (isset($_GET) && array_key_exists('Dia', $_GET)) ? $_GET['Dia'] : 0;

CreaIniciHTML($Usuari, 'Guàrdies');
echo '<script language="javascript" src="js/Guardia.js?v1.1" type="text/javascript"></script>';

$Avui = date('d/m/Y');
$Dia = date('w'); /* 0:dg, 1:dl, 2:dm, ... */ 
if (!in_array($Dia, [1, 2, 3, 4, 5]))
	$Dia = 0;

$OPCIONS_DIES = ['Setmana', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres'];

echo '<label for="cmb_dia">Dia:</label>';
echo '  <select class="custom-select" style="width:200px" name="cmb_dia" onchange="CanviaDia(this)">';
for ($i=0; $i<=5; $i++) {
	$Selected = ($Dia == $i)? ' selected ' : '';
	echo '<option value='.$i.' '.$Selected.'>'.$OPCIONS_DIES[$i].'</option>';
}
echo '  </select>';

echo '<p>';

echo "<DIV id=taula>";
$Guardia = new Guardia($conn, $Festiu);
//$Guardia->EscriuTaula();
$Guardia->EscriuTaula($Dia);
echo "</DIV>";

//echo "<DIV id=boto>";
//echo $Guardia->CreaBotoGeneraProperDia($Dia);
//echo "</DIV>";

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>
