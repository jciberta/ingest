<?php

/** 
 * GeneraPDF.php
 *
 * Genera diversos PDF de cop.
 *
 * GET:
 * - accio: Expedient, Programacio.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibURL.php');
require_once(ROOT.'/lib/LibSistema.php');
require_once(ROOT.'/lib/LibGeneraPDF.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: Surt.php");
$Usuari = unserialize($_SESSION['USUARI']);
$Sistema = unserialize($_SESSION['SISTEMA']);

if (!$Usuari->es_admin && !$Usuari->es_direccio && !$Usuari->es_cap_estudis)
	header("Location: Surt.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

RecuperaGET($_GET);

// Paràmetres de la URL
if (!isset($_GET))
	header("Location: Surt.php");
$accio = $_GET['accio'];

switch ($accio) {
    case "Expedient":
        // Paràmetres de la URL
        $CursId = (isset($_GET) && array_key_exists('CursId', $_GET)) ? $_GET['CursId'] : -1;
        $GP = GeneraPDFFactoria::Crea($conn, $Usuari, $Sistema, $accio, $CursId);
        $GP->EscriuHTML();
        break;
    case "Programacio":
        // Paràmetres de la URL
        $PlaEstudiId = !array_key_exists("PlaEstudiId", $_GET) ? -1 : $_GET['PlaEstudiId'];
        $GP = GeneraPDFFactoria::Crea($conn, $Usuari, $Sistema, $accio, $PlaEstudiId);
        $GP->EscriuHTML();
        break;
}

echo "<DIV id=debug></DIV>";
echo "<DIV id=debug2></DIV>";

$conn->close(); 
 
?>