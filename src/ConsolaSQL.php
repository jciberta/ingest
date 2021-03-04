<?php

/** 
 * Administra.php
 *
 * Utilitats d'administració.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibStr.php');
require_once(ROOT.'/lib/LibHTML.php');
require_once(ROOT.'/lib/LibAdministracio.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if (!$Usuari->es_admin)
	header("Location: Surt.php");

CreaIniciHTML($Usuari, 'Consola SQL');
echo '<script language="javascript" src="js/ConsolaSQL.js?v1.0" type="text/javascript"></script>';

echo '<form action="" method="post" id="ConsolaSQL">';
echo '<textarea id="AreaText" rows="10" style="width:100%;"s>';
echo '</textarea>';
echo '<br><br>';
echo "<a href='#' class='btn btn-primary active' role='button' aria-pressed='true' name='Nom' onclick='ExecutaSQL(this)'>Executa</a>&nbsp;";
echo '<form>';
echo '<div id=taula></div>';

echo "<DIV id=debug></DIV>";

$conn->close(); 
 
 ?>
