<?php

/**
 * BorsaTreball.php
 * 
 * Pàgina principal de la borsa de treball.
 * Es permet l'accés a aquesta pàgina per a la consulta d'ofertes de treball sense estar identificat.
 * 
 * @author: shad0wstv, Josep Ciberta
 * @since: 1.13
 * @license: https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('Config.php');
require_once(ROOT.'/lib/LibSeguretat.php');
require_once(ROOT.'/lib/LibBorsaTreball.php');

session_start();
if (isset($_SESSION['usuari_id'])) {
    // Usuari identificat
    $Usuari = unserialize($_SESSION['USUARI']);
    $Sistema = unserialize($_SESSION['SISTEMA']);
}
else {
    // Usuari sense identificar
    $Usuari = null;
    $Sistema = null;
}

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error)
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);

if ($Usuari !== null) {
    // Usuari identificat
    $Professor = new Professor($conn, $Usuari, $Sistema);
    $EsGestorBorsa = $Professor->EsGestorBorsa();
    Seguretat::ComprovaAccessUsuari($Usuari, ['SU', 'DI', 'CE'], $EsGestorBorsa);
    $BorsaTreball = new BorsaTreball($conn, $Usuari, $Sistema);
    echo $BorsaTreball->EscriuFormulariRecerca();
}
else {
    $BorsaTreball = new BorsaTreball($conn, $Usuari, $Sistema);
    echo $BorsaTreball->EscriuHTML();
}

?>