<?php

/** 
 * GeneraScriptExpedients.php
 *
 * Utilitat CLI per a la impressió dels expedients en PDF.
 * Genera un script per a tots els alumnes d'un curs.
 *
 * Ús: php GeneraScriptExpedients.php Curs Sufix
 * Ex: php GeneraScriptExpedients.php 1 1T >GeneraExpedients.sh
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

require_once('../Config.php');
require_once(ROOT.'/lib/LibExpedient.php');

// Obtenim els arguments
// https://stackoverflow.com/questions/6826718/pass-variable-to-php-script-running-from-command-line
if (defined('STDIN')) {
	if ($argc == 3) {
		$Curs = $argv[1];
		$Sufix = $argv[2];
	}
	else {
		die("Ús: GeneraScriptExpedients.php Curs Sufix\n");
	}
} 
else { 
	die("L'aplicació només es pot cridar des de la línia de comandes.");
	// Aquí es podria fer servir $_GET['...']
}

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
	die("ERROR: No ha estat possible connectar amb la base de dades: " . $conn->connect_error);
} 

$Expedient = new Expedient($conn);
$Expedient->EscriuScript($Curs, $Sufix);

?>
