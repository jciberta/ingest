<?php

/** 
 * Surt.php
 *
 * Surt de la sessió i torna a mostrar la pàgina principal.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */
 
require_once('Config.php');
require_once(ROOT.'/lib/LibRegistre.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");
$Usuari = unserialize($_SESSION['USUARI']);

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) 
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

$log = new Registre($conn, $Usuari);
$log->Escriu(Registre::AUTH, 'Sortida del sistema');

session_destroy();
header('Location: index.html');

?>
